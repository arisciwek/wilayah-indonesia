<?php
/**
 * File: SettingsController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsController.php
 * Description: Main settings controller that handles settings page functionality
 * Last modified: 2024-11-24
 */

namespace WilayahIndonesia\Controllers\Settings;

use WilayahIndonesia\Models\Settings\SettingsModel;
use WilayahIndonesia\Models\Settings\PermissionModel;

class SettingsController {
    private $settings_model;
    private $permission_model;
    private $dependencies;
    private $current_tab = 'general';
    private $tabs = [];

    public function __construct() {
        $this->settings_model = new SettingsModel();
        $this->permission_model = new PermissionModel();
        
        // Initialize tabs
        $this->tabs = [
            'general' => __('Pengaturan Umum', 'wilayah-indonesia'),
            'permission' => __('Hak Akses', 'wilayah-indonesia'),
            'role' => __('Role', 'wilayah-indonesia')
        ];

        // Setup dependencies
        $this->dependencies = new SettingsDependencyController(
            'wilayah-indonesia',
            WILAYAH_INDONESIA_VERSION,
            'wilayah-indonesia-settings'
        );
    }

    public function init() {
        $this->dependencies->init();
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Handle form submissions
        add_action('admin_post_wilayah_save_settings', [$this, 'handleFormSubmit']);
        
        // AJAX handlers
        add_action('wp_ajax_save_permissions', [$this, 'handlePermissionsSave']);
        add_action('wp_ajax_save_roles', [$this, 'handleRolesSave']);
    }

    public function registerSettings() {
        $this->settings_model->registerSettings();
    }

    public function getCurrentTab() {
        return isset($_GET['tab']) && array_key_exists($_GET['tab'], $this->tabs) 
               ? sanitize_key($_GET['tab']) 
               : 'general';
    }

    public function renderPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'wilayah-indonesia'));
        }

        $current_tab = $this->getCurrentTab();
        
        // Load main settings template
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/settings/settings-page.php';
    }

    protected function renderTab($tab) {
        $template_path = WILAYAH_INDONESIA_PATH . "src/Views/templates/settings/tabs/{$tab}-tab.php";
        
        if (file_exists($template_path)) {
            // Prepare data for template
            $data = $this->getTabData($tab);
            
            // Extract data untuk digunakan di template
            extract($data);
            
            require $template_path;
        }
    }

    protected function getTabData($tab) {
        switch ($tab) {
            case 'general':
                return [
                    'settings' => $this->settings_model->getSettings()
                ];
            case 'permission':
                return [
                    'permissions' => $this->permission_model->getPermissions(),
                    'roles' => $this->permission_model->getRoles()
                ];
            case 'role':
                return [
                    'roles' => $this->permission_model->getRoles(),
                    'capabilities' => $this->permission_model->getCapabilities()
                ];
            default:
                return [];
        }
    }

    public function handleFormSubmit() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia'));
        }

        check_admin_referer('wilayah_settings_nonce');

        $tab = $this->getCurrentTab();
        $redirect_url = add_query_arg('tab', $tab, admin_url('admin.php?page=wilayah-indonesia-settings'));

        switch ($tab) {
            case 'general':
                $this->settings_model->saveGeneralSettings($_POST);
                $message = 'success';
                break;
            default:
                $message = 'error';
        }

        wp_redirect(add_query_arg('message', $message, $redirect_url));
        exit;
    }

    public function handlePermissionsSave() {
        check_ajax_referer('wilayah_settings_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia'));
        }

        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
        $result = $this->permission_model->savePermissions($permissions);

        if ($result) {
            wp_send_json_success(__('Hak akses berhasil disimpan.', 'wilayah-indonesia'));
        } else {
            wp_send_json_error(__('Gagal menyimpan hak akses.', 'wilayah-indonesia'));
        }
    }

    public function handleRolesSave() {
        check_ajax_referer('wilayah_settings_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia'));
        }

        $roles = isset($_POST['roles']) ? $_POST['roles'] : [];
        $result = $this->permission_model->saveRoles($roles);

        if ($result) {
            wp_send_json_success(__('Role berhasil disimpan.', 'wilayah-indonesia'));
        } else {
            wp_send_json_error(__('Gagal menyimpan role.', 'wilayah-indonesia'));
        }
    }
}