<?php
/**
 * File: class-activator.php
 * Path: /wilayah-indonesia/includes/class-activator.php
 * Description: Handles plugin activation and database installation
 * 
 * @package     WilayahIndonesia
 * @subpackage  Includes
 * @version     1.0.1
 * @author      arisciwek
 * 
 * Description: Menangani proses aktivasi plugin dan instalasi database.
 *              Termasuk di dalamnya:
 *              - Instalasi tabel database melalui Database\Installer
 *              - Menambahkan versi plugin ke options table
 *              - Setup permission dan capabilities
 * 
 * Dependencies:
 * - WilayahIndonesia\Database\Installer untuk instalasi database
 * - WilayahIndonesia\Models\Settings\PermissionModel untuk setup capabilities
 * - WordPress Options API
 * 
 * Changelog:
 * 1.0.1 - 2024-01-21
 * - Refactored untuk menggunakan Database\Installer
 * - Enhanced error handling dan logging
 * - Added proper dependency management
 * 
 * 1.0.0 - 2024-11-23
 * - Initial creation
 * - Added basic activation handling
 */

use WilayahIndonesia\Models\Settings\PermissionModel;
use WilayahIndonesia\Database\Installer;

class Wilayah_Indonesia_Activator {
    private static function debug($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $timestamp = current_time('mysql');
            $data_str = $data ? print_r($data, true) : '';
            error_log("[{$timestamp}] Wilayah_Indonesia_Activator: {$message} {$data_str}");
        }
    }

    public static function activate() {
        self::debug('Starting plugin activation...');

        try {
            // 1. Run database installation
            self::debug('Installing database...');
            $installer = new Installer();
            if (!$installer->run()) {
                self::debug('Failed to install database tables');
                return;
            }

            // 2. Add capabilities
            self::debug('Adding capabilities...');
            try {
                $permission_model = new PermissionModel();
                $permission_model->addCapabilities();
                self::debug('Capabilities added successfully');
            } catch (\Exception $e) {
                self::debug('Error adding capabilities: ' . $e->getMessage());
            }

            // 3. Add version
            self::debug('Adding version...');
            self::addVersion();

            // 4. Flush rewrite rules jika diperlukan
            flush_rewrite_rules();

            self::debug('Plugin activation completed successfully');

        } catch (\Exception $e) {
            self::debug('Critical error during activation: ' . $e->getMessage());
            throw $e;
        }
    }

    private static function addVersion() {
        add_option('wilayah_indonesia_version', WILAYAH_INDONESIA_VERSION);
    }
}
