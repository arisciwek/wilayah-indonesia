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

        // Register handler untuk admin_post
        add_action('admin_post_update_wilayah_permissions', [$this, 'handlePermissionUpdate']);
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

    public function handlePermissionUpdate() {
        try {
            if (!check_admin_referer('wilayah_permissions_nonce', 'security')) {
                wp_die(__('Invalid security token sent.', 'wilayah-indonesia'));
            }

            if (!current_user_can('manage_options')) {
                wp_die(__('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia'));
            }

            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
            $updated = false;

            foreach (get_editable_roles() as $role_name => $role_info) {
                if ($role_name === 'administrator') continue;

                $role_permissions = isset($permissions[$role_name]) ? $permissions[$role_name] : [];
                if ($this->permission_model->updateRoleCapabilities($role_name, $role_permissions)) {
                    $updated = true;
                }
            }

            if ($updated) {
                add_settings_error(
                    'wilayah_messages',
                    'permissions_updated',
                    __('Hak akses berhasil diperbarui.', 'wilayah-indonesia'),
                    'success'
                );
            }

        } catch (\Exception $e) {
            add_settings_error(
                'wilayah_messages',
                'permissions_error',
                $e->getMessage(),
                'error'
            );
        }

        // Redirect back to settings page
        wp_redirect(add_query_arg(['tab' => 'permission'], wp_get_referer()));
        exit;
    }
}
