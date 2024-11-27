<?php
/**
 * File: SettingsController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsController.php
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Admin/Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Description: Controller untuk menangani halaman pengaturan plugin
 * 
 * Dependencies:
 * - SettingsModel untuk interaksi dengan database
 * - settings-page.php sebagai template utama
 * - tab templates untuk konten tiap tab
 * 
 * Last modified: 2024-11-26
 * 
 * Changelog:
 * v1.0.0 - 2024-11-26
 * - Initial implementation
 * - Added basic settings page functionality
 * - Added tab handling
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
        
        $this->tabs = [
            'general' => __('Pengaturan Umum', 'wilayah-indonesia'),
            'permission' => __('Hak Akses', 'wilayah-indonesia'),
            'role' => __('Role', 'wilayah-indonesia')
        ];

        $this->dependencies = new SettingsDependencyController(
            'wilayah-indonesia',
            WILAYAH_INDONESIA_VERSION,
            'wilayah-indonesia-settings'
        );

        $this->init();
    }
    public function init() {
        $this->dependencies->init();
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_post_wilayah_save_settings', [$this, 'handleFormSubmit']);
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
        
        if (!file_exists($template_path)) {
            echo '<div class="notice notice-error"><p>';
            echo sprintf(
                __('Error: Template file not found: %s', 'wilayah-indonesia'),
                esc_html($template_path)
            );
            echo '</p></div>';
            return;
        }
        
        // Prepare data for template
        $data = $this->getTabData($tab);
        extract($data);
        
        require $template_path;
    }

    protected function getTabData($tab) {
        switch ($tab) {
            case 'general':
                return ['settings' => $this->settings_model->getSettings()];
            case 'permission':
                return [
                    'permissions' => $this->permission_model->getPermissions(),
                    'roles' => $this->permission_model->getRoles(),
                    'capabilities' => $this->permission_model->getCapabilities()
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
    /**
     * Handle AJAX request untuk menyimpan pengaturan permission
     */
    public function handlePermissionsSave() {
        try {
            check_ajax_referer('wilayah_settings_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Anda tidak memiliki izin untuk ini.');
                return;
            }

            $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
            
            if (empty($permissions)) {
                wp_send_json_error('Data permissions kosong');
                return;
            }

            $result = $this->permission_model->savePermissions($permissions);
            
            if ($result === false) {
                wp_send_json_error('Gagal menyimpan ke database');
                return;
            }
            wp_send_json_success('Hak akses berhasil disimpan.');
            
        } catch (\Exception $e) {
            wp_send_json_error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Handle AJAX request untuk menyimpan pengaturan role
     */
    public function handleRolesSave() {
        check_ajax_referer('wilayah_settings_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia'));
        }

        $roles = isset($_POST['roles']) ? $_POST['roles'] : [];
        
        // Sanitize roles data
        $sanitized_roles = [];
        foreach ($roles as $role => $data) {
            $role = sanitize_key($role);
            $sanitized_roles[$role] = [
                'name' => sanitize_text_field($data['name'] ?? ''),
                'capabilities' => array_map('boolval', $data['capabilities'] ?? [])
            ];
        }

        // Save roles using the model
        $result = $this->permission_model->saveRoles($sanitized_roles);

        if ($result) {
            wp_send_json_success(__('Role berhasil disimpan.', 'wilayah-indonesia'));
        } else {
            wp_send_json_error(__('Gagal menyimpan role.', 'wilayah-indonesia'));
        }
    }

    public function handleAjaxSave() {
        check_ajax_referer('wilayah_settings_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia')]);
        }

        $settings = [];
        parse_str($_POST['formData'], $settings);
        
        // Validate
        $errors = $this->validateSettings($settings);
        if (!empty($errors)) {
            wp_send_json_error(['message' => $errors[0]]);
        }

        $result = $this->settings_model->saveGeneralSettings($settings);
        if ($result) {
            wp_send_json_success(['message' => __('Pengaturan berhasil disimpan.', 'wilayah-indonesia')]);
        } else {
            wp_send_json_error(['message' => __('Gagal menyimpan pengaturan.', 'wilayah-indonesia')]);
        }
    }

    private function validateSettings($settings) {
        $errors = [];
        
        // Validate records per page
        $records = intval($settings['records_per_page']);
        if ($records < 5 || $records > 100) {
            $errors[] = __('Data per halaman harus antara 5-100', 'wilayah-indonesia');
        }

        // Validate cache duration if enabled
        if (!empty($settings['enable_caching'])) {
            $duration = intval($settings['cache_duration']);
            if ($duration < 3600) {
                $errors[] = __('Durasi cache minimal 1 jam', 'wilayah-indonesia');
            }
        }

        return $errors;
    }
}
