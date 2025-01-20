<?php
/**
 * Database Installer
 *
 * @package     WilayahIndonesia
 * @subpackage  Database
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Database/Installer.php
 *
 * Description: Mengelola instalasi database plugin.
 *              Handles table creation dengan foreign keys.
 *              Menggunakan transaction untuk data consistency.
 *
 * Tables Created:
 * - wi_provinces
 * - wi_regencies
 *
 * Foreign Keys:
 * - fk_regency_province
 *
 * Changelog:
 * 1.0.0 - 2024-01-21
 * - Initial version
 */
namespace WilayahIndonesia\Database;

defined('ABSPATH') || exit;

class Installer {
    private static function debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[WilayahIndonesia_Installer] {$message}");
        }
    }

    private static $tables = [
        'wi_provinces',
        'wi_regencies'
    ];

    private static function verify_tables() {
        global $wpdb;
        foreach (self::$tables as $table) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
            if (!$table_exists) {
                throw new \Exception("Failed to create table: {$wpdb->prefix}{$table}");
            }
        }
    }
    
    public static function run() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        
        try {
            self::debug('Starting database installation...');
            $wpdb->query('START TRANSACTION');

            // Database Tables
            require_once WILAYAH_INDONESIA_PATH . 'src/Database/Tables/ProvincesDB.php';
            require_once WILAYAH_INDONESIA_PATH . 'src/Database/Tables/RegenciesDB.php';

            // Create tables in correct order
            self::debug('Creating provinces table...');
            dbDelta(Tables\ProvincesDB::get_schema());
            
            self::debug('Creating regencies table...');
            dbDelta(Tables\RegenciesDB::get_schema());

            // Verify tables were created
            self::debug('Verifying tables...');
            self::verify_tables();

            // Drop any existing foreign keys for clean slate
            self::debug('Cleaning up existing foreign keys...');
            self::ensure_no_foreign_keys();
            
            // Add foreign key constraints
            self::debug('Adding foreign key constraints...');
            self::add_foreign_keys();

            $wpdb->query('COMMIT');
            self::debug('Database installation completed successfully');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            self::debug('Database installation failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function ensure_no_foreign_keys() {
        global $wpdb;
        
        $tables_with_fk = ['wi_regencies'];
        
        foreach ($tables_with_fk as $table) {
            $foreign_keys = $wpdb->get_results("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '{$wpdb->prefix}{$table}' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            foreach ($foreign_keys as $key) {
                $wpdb->query("
                    ALTER TABLE {$wpdb->prefix}{$table} 
                    DROP FOREIGN KEY {$key->CONSTRAINT_NAME}
                ");
            }
        }
    }

    private static function add_foreign_keys() {
        global $wpdb;

        $constraints = [
            // Regencies constraints
            [
                'name' => 'fk_regency_province',
                'sql' => "ALTER TABLE {$wpdb->prefix}wi_regencies
                         ADD CONSTRAINT fk_regency_province
                         FOREIGN KEY (province_id)
                         REFERENCES {$wpdb->prefix}wi_provinces(id)
                         ON DELETE CASCADE"
            ]
        ];

        foreach ($constraints as $constraint) {
            $result = $wpdb->query($constraint['sql']);
            if ($result === false) {
                throw new \Exception("Failed to add foreign key {$constraint['name']}: " . $wpdb->last_error);
            }
        }
    }
}
