<?php
/**
 * File: SettingsController.php 
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsController.php
 * Description: Controller untuk mengelola halaman pengaturan plugin termasuk matrix permission
 * Version: 3.0.0
 * Last modified: 2024-11-28 08:45:00
 * 
 * Changelog:
 * v3.0.0 - 2024-11-28
 * - Perbaikan handling permission matrix
 * - Penambahan validasi dan error handling
 * - Optimasi performa loading data
 * - Penambahan logging aktivitas
 * 
 * v2.0.0 - 2024-11-27
 * - Integrasi dengan WordPress Roles API
 * 
 * Dependencies:
 * - PermissionModel
 * - SettingsModel 
 * - WordPress admin functions
 */
namespace WilayahIndonesia\Controllers\Settings;

use WilayahIndonesia\Models\Settings\SettingsModel;
use WilayahIndonesia\Models\Settings\PermissionModel;

class SettingsController {
    const SETTINGS_PAGE_SLUG = 'wilayah-indonesia-settings';

    private $settings_model;
    private $permission_model;
    private $tabs = [];
    private $settings_page;

    public function __construct() {
        $this->settings_model = new SettingsModel();
        $this->permission_model = new PermissionModel();
        $this->tabs = [
            'general' => __('Pengaturan Umum', 'wilayah-indonesia'),
            'permission' => __('Hak Akses', 'wilayah-indonesia')
        ];


        // Register handler untuk admin_post dan ajax
        add_action('wp_ajax_update_wilayah_permissions', [$this, 'handleAjaxPermissionUpdate']);

        // Register handler untuk admin_post
        // add_action('admin_post_update_wilayah_permissions', [$this, 'handlePermissionUpdate']);
    }


    public function init() {
        if (!current_user_can('manage_options')) {
            return;
        }
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings() {
        register_setting(
            'wilayah_permissions', 
            'wilayah_role_permissions',
            [
                'sanitize_callback' => [$this, 'sanitizePermissions']
            ]
        );
        
        $this->settings_model->registerSettings();
    }

    public function renderPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'wilayah-indonesia'));
        }

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        if (!array_key_exists($current_tab, $this->tabs)) {
            $current_tab = 'general';
        }

        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/settings/settings-page.php';
    }

    protected function renderTab($tab) {
        $data = [];
        
        if ($tab === 'permission') {
            $data = $this->getPermissionTabData();
        }

        $template_path = WILAYAH_INDONESIA_PATH . "src/Views/templates/settings/tabs/{$tab}-tab.php";
        
        if (file_exists($template_path)) {
            extract($data);
            require $template_path;
        }
    }

    protected function getPermissionTabData(): array {
        $permissions = $this->permission_model->getAllCapabilities();
        $roles = get_editable_roles();
        
        $role_capabilities = [];
        foreach ($roles as $role_name => $role_info) {
            if ($role_name === 'administrator') continue;
            
            $capabilities = [];
            foreach ($permissions as $cap => $label) {
                $capabilities[$cap] = $this->permission_model->roleHasCapability($role_name, $cap);
            }
            $role_capabilities[$role_name] = $capabilities;
        }
        
        return [
            'wilayah_permissions' => $permissions,
            'all_roles' => $roles,
            'role_capabilities' => $role_capabilities
        ];
    }

    /**
     * Handle AJAX permission update
     */
    public function handleAjaxPermissionUpdate() {
        try {
            check_ajax_referer('wilayah_permissions_nonce', 'security');

            if (!current_user_can('manage_options')) {
                wp_send_json_error([
                    'message' => __('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia')
                ]);
                return;
            }

            // Handle reset action
            if (isset($_POST['reset_permissions'])) {
                $reset = $this->permission_model->resetToDefault();
                if ($reset) {
                    wp_send_json_success([
                        'message' => __('Hak akses berhasil direset ke default.', 'wilayah-indonesia'),
                        'reload' => true
                    ]);
                } else {
                    wp_send_json_error([
                        'message' => __('Gagal mereset hak akses.', 'wilayah-indonesia')
                    ]);
                }
                return;
            }

            // Handle normal update
            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
            if (empty($permissions)) {
                wp_send_json_error([
                    'message' => __('Data permissions tidak valid.', 'wilayah-indonesia')
                ]);
                return;
            }

            $updated = false;
            $errors = [];

            foreach ($permissions as $role_name => $role_permissions) {
                if ($role_name === 'administrator') {
                    continue;
                }

                try {
                    $result = $this->permission_model->updateRoleCapabilities($role_name, $role_permissions);
                    if ($result) {
                        $updated = true;
                    }
                } catch (\Exception $e) {
                    $errors[] = sprintf(
                        __('Gagal update role %s: %s', 'wilayah-indonesia'),
                        $role_name,
                        $e->getMessage()
                    );
                }
            }

            if ($updated) {
                $response = [
                    'message' => __('Hak akses berhasil diperbarui.', 'wilayah-indonesia'),
                    'reload' => false
                ];
                
                if (!empty($errors)) {
                    $response['warnings'] = $errors;
                }
                
                wp_send_json_success($response);
            } else {
                wp_send_json_error([
                    'message' => !empty($errors) 
                        ? implode("\n", $errors)
                        : __('Tidak ada perubahan yang perlu disimpan.', 'wilayah-indonesia')
                ]);
            }

        } catch (\Exception $e) {
            error_log('Wilayah Permission Update Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
    
}
