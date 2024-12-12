<?php
/**
 * File: class-activator.php
 * Path: /wilayah-indonesia/includes/class-activator.php
 * Description: Handles plugin activation and database table creation
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 *   - 2024-11-23: Added database tables creation
 *   - 2024-11-23: Fixed foreign key data type mismatch
 *   - 2024-11-23: Added custom prefix wi_ for tables
 *   - 2024-12-09: Added comprehensive debugging
 */

use WilayahIndonesia\Models\Settings\PermissionModel;

class Wilayah_Indonesia_Activator {
    private static function debug($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $timestamp = current_time('mysql');
            $data_str = $data ? print_r($data, true) : '';
            error_log("[{$timestamp}] Wilayah_Indonesia_Activator: {$message} {$data_str}");
        }
    }

    private static function logDbError($wpdb, $context) {
        if ($wpdb->last_error) {
            self::debug("Database error in {$context}: " . $wpdb->last_error);
            return false;
        }
        return true;
    }
    public static function activate() {
        self::debug('Starting plugin activation...');

        try {
            self::debug('Creating tables...');
            $tables_result = self::createTables();
            if (!$tables_result) {
                self::debug('Failed to create tables');
                return;
            }

            self::debug('Upgrading database...');
            $upgrade_result = self::upgradeDatabase();
            if (!$upgrade_result) {
                self::debug('Failed to upgrade database');
                return;
            }

            // Tambahkan ini
            self::debug('Upgrading regencies database...');
            $upgrade_regencies_result = self::upgradeRegenciesDatabase();
            if (!$upgrade_regencies_result) {
                self::debug('Failed to upgrade regencies database');
                return;
            }

            self::debug('Adding version...');
            self::addVersion();

            self::debug('Adding capabilities...');
            try {
                $permission_model = new PermissionModel();
                $permission_model->addCapabilities();
                self::debug('Capabilities added successfully');
            } catch (\Exception $e) {
                self::debug('Error adding capabilities: ' . $e->getMessage());
            }

            self::debug('Plugin activation completed successfully');

        } catch (\Exception $e) {
            self::debug('Critical error during activation: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private static function createTables() {
        global $wpdb;
        self::debug('Starting table creation...');

        $charset_collate = $wpdb->get_charset_collate();
        self::debug('Using charset_collate: ' . $charset_collate);

        // SQL untuk tabel provinces dengan prefix wi_
        $sql_provinces = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wi_provinces` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `code` varchar(2) NOT NULL,
            `name` varchar(100) NOT NULL,
            `created_by` bigint(20) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`),
            UNIQUE KEY `name` (`name`),
            KEY `created_by_index` (`created_by`)
        ) $charset_collate;";

        // SQL untuk tabel regencies dengan prefix wi_
        $sql_regencies = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wi_regencies` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `province_id` bigint(20) UNSIGNED NOT NULL,
            //`code` varchar(4) NOT NULL,               // Tambah ini
            `name` varchar(100) NOT NULL,
            `type` enum('kabupaten','kota') NOT NULL,
            `created_by` bigint(20) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `province_name` (`province_id`, `name`),
            KEY `created_by_index` (`created_by`),
            CONSTRAINT `{$wpdb->prefix}wi_regencies_ibfk_1`
            FOREIGN KEY (`province_id`)
            REFERENCES `{$wpdb->prefix}wi_provinces` (`id`)
            ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        self::debug('Creating provinces table with query:', $sql_provinces);
        $provinces_result = dbDelta($sql_provinces);
        self::debug('Provinces table creation result:', $provinces_result);

        self::debug('Creating regencies table with query:', $sql_regencies);
        $regencies_result = dbDelta($sql_regencies);
        self::debug('Regencies table creation result:', $regencies_result);

        // Verify tables were created
        $provinces_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wi_provinces'");
        $regencies_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}wi_regencies'");

        if (!$provinces_exists || !$regencies_exists) {
            self::debug('Table creation failed. Provinces exists: ' . ($provinces_exists ? 'yes' : 'no') .
                       ', Regencies exists: ' . ($regencies_exists ? 'yes' : 'no'));
            return false;
        }

        self::debug('Tables created successfully');
        return true;
    }

    private static function upgradeRegenciesDatabase() {
        global $wpdb;
        self::debug('Starting regencies database upgrade...');

        try {
            $table_name = $wpdb->prefix . 'wi_regencies';
            self::debug("Checking for 'code' column in table: {$table_name}");

            $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                      WHERE TABLE_NAME = '{$table_name}'
                                      AND COLUMN_NAME = 'code'");

            if (empty($row)) {
                self::debug("'code' column not found in regencies, starting upgrade process...");

                // 1. Tambahkan kolom tanpa constraint
                $add_column_sql = "ALTER TABLE {$table_name} ADD COLUMN `code` varchar(4) NULL";
                self::debug("Adding code column with query:", $add_column_sql);
                $wpdb->query($add_column_sql);
                if (!self::logDbError($wpdb, 'add regency code column')) {
                    return false;
                }

                // 2. Update dengan format: provinceCode + autoincrement
                $update_sql = "UPDATE {$table_name} r
                              JOIN {$wpdb->prefix}wi_provinces p ON r.province_id = p.id
                              SET r.code = CONCAT(p.code, LPAD(r.id, 2, '0'))
                              WHERE r.code IS NULL OR r.code = ''";
                self::debug("Updating regency codes with query:", $update_sql);
                $wpdb->query($update_sql);
                if (!self::logDbError($wpdb, 'update regency codes')) {
                    return false;
                }

                // 3. Verifikasi tidak ada duplikat
                $duplicate_check = $wpdb->get_results("
                    SELECT code, COUNT(*) as count
                    FROM {$table_name}
                    GROUP BY code
                    HAVING count > 1
                ");

                if (!empty($duplicate_check)) {
                    self::debug("Found duplicate regency codes:", $duplicate_check);
                    return false;
                }

                // 4. Tambahkan constraint
                self::debug("Adding regency code constraints...");
                $constraint_sql = "ALTER TABLE {$table_name}
                                 MODIFY code varchar(4) NOT NULL,
                                 ADD UNIQUE KEY `code` (`code`)";
                $wpdb->query($constraint_sql);
                if (!self::logDbError($wpdb, 'add regency code constraints')) {
                    return false;
                }

                self::debug("'code' column upgrade for regencies completed successfully");
            } else {
                self::debug("'code' column already exists in regencies table");
            }

            return true;

        } catch (\Exception $e) {
            self::debug('Error during regencies database upgrade: ' . $e->getMessage());
            return false;
        }
    }

    private static function upgradeDatabase() {
        global $wpdb;
        self::debug('Starting database upgrade...');

        try {
            $table_name = $wpdb->prefix . 'wi_provinces';
            self::debug("Checking for 'code' column in table: {$table_name}");

            $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                      WHERE TABLE_NAME = '{$table_name}'
                                      AND COLUMN_NAME = 'code'");

            if (empty($row)) {
                self::debug("'code' column not found, starting upgrade process...");

                // 1. Tambahkan kolom tanpa constraint
                $add_column_sql = "ALTER TABLE {$table_name} ADD COLUMN `code` varchar(2) NULL";
                self::debug("Adding code column with query:", $add_column_sql);
                $wpdb->query($add_column_sql);
                if (!self::logDbError($wpdb, 'add code column')) {
                    return false;
                }

                // 2. Update dengan RIGHT untuk mengambil 2 digit terakhir
                $update_sql = "UPDATE {$table_name}
                              SET code = RIGHT(CONCAT('P', id), 2)
                              WHERE code IS NULL OR code = ''";
                self::debug("Updating codes with query:", $update_sql);
                $wpdb->query($update_sql);
                if (!self::logDbError($wpdb, 'update codes')) {
                    return false;
                }

                // 3. Verifikasi tidak ada duplikat
                $duplicate_check = $wpdb->get_results("
                    SELECT code, COUNT(*) as count
                    FROM {$table_name}
                    GROUP BY code
                    HAVING count > 1
                ");

                if (!empty($duplicate_check)) {
                    self::debug("Found duplicate codes:", $duplicate_check);
                    return false;
                }

                // 4. Tambahkan constraint
                self::debug("Adding constraints...");
                $constraint_sql = "ALTER TABLE {$table_name}
                                 MODIFY code varchar(2) NOT NULL,
                                 ADD UNIQUE KEY `code` (`code`)";
                $wpdb->query($constraint_sql);
                if (!self::logDbError($wpdb, 'add code constraints')) {
                    return false;
                }

                self::debug("'code' column upgrade completed successfully");
            } else {
                self::debug("'code' column already exists");
            }

            return true;

        } catch (\Exception $e) {
            self::debug('Error during database upgrade: ' . $e->getMessage());
            return false;
        }
    }

    private static function addVersion() {
        self::debug('Adding plugin version to options...');
        $result = add_option('wilayah_indonesia_version', WILAYAH_INDONESIA_VERSION);
        self::debug('Version option added: ' . ($result ? 'success' : 'failed or already exists'));
    }
}
