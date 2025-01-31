<?php
/**
 * File: class-deactivator.php
 * Path: /wilayah-indonesia/includes/class-deactivator.php
 * Description: Menangani proses deaktivasi plugin
 * 
 * @package     WilayahIndonesia
 * @subpackage  Includes
 * @version     1.0.1
 * @author      arisciwek
 * 
 * Description: Menangani proses deaktivasi plugin:
 *              - Menghapus seluruh tabel (fase development)
 *              - Membersihkan cache 
 *              - Menghapus capabilities
 *
 * Changelog:
 * 1.0.1 - 2024-01-21
 * - Added table cleanup during deactivation
 * - Added proper logging
 * - Added capabilities removal
 * 
 * 1.0.0 - 2024-11-23  
 * - Initial creation
 * - Added cache cleanup
 */
use WilayahIndonesia\Cache\CacheManager;


class Wilayah_Indonesia_Deactivator {
    private static function debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[Wilayah_Indonesia_Deactivator] {$message}");
        }
    }

    public static function deactivate() {
        global $wpdb;

        try {
            // Remove capabilities first
            self::debug('Removing capabilities...');
            self::remove_capabilities();

            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Daftar tabel yang akan dihapus (urutan penting - child tables first)
            $tables = [
                'wi_regencies',
                'wi_provinces'
            ];

            // Hapus tabel secara terurut
            foreach ($tables as $table) {
                $table_name = $wpdb->prefix . $table;
                $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
                self::debug("Dropping table: {$table_name}");
            }

            // Bersihkan cache menggunakan CacheManager
            $cache = new CacheManager();
            $cache->delete(CacheManager::KEY_PROVINCE_LIST);

            // Bersihkan cache regency untuk semua provinsi
            $provinces = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}wi_provinces");
            if ($provinces) {
                foreach ($provinces as $province) {
                    $cache->delete(CacheManager::KEY_REGENCY_LIST . $province->id);
                }
            }
            
            // Hapus opsi versi
            delete_option('wilayah_indonesia_version');

            // Commit transaction
            $wpdb->query('COMMIT');
            
            self::debug("Plugin deactivation completed successfully");

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            self::debug("Error during deactivation: " . $e->getMessage());
        }
    }

    private static function remove_capabilities() {
        try {
            // Get the list of all capabilities from PermissionModel
            $permission_model = new \WilayahIndonesia\Models\Settings\PermissionModel();
            $capabilities = array_keys($permission_model->getAllCapabilities());

            // Remove capabilities from all roles
            foreach (get_editable_roles() as $role_name => $role_info) {
                $role = get_role($role_name);
                if (!$role) continue;

                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap);
                }
            }

            self::debug("Capabilities removed successfully");
        } catch (\Exception $e) {
            self::debug("Error removing capabilities: " . $e->getMessage());
            throw $e;
        }
    }
}
