<?php
/**
 * File: SettingsDependencyController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsDependencyController.php
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Admin/Controllers/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Description: Handles all dependencies (CSS/JS) for the settings pages.
 *              Loads assets conditionally based on current admin page.
 *              Also handles script localization and version management.
 * 
 * Dependencies:
 * - jQuery
 * - jQuery UI (Tabs)
 * - irToast for notifications
 * - WordPress admin enqueue functions
 * 
 * Usage:
 * Instantiated by SettingsController to manage all asset loading
 * for settings pages including the main settings page and all tabs.
 * 
 * Last modified: 2024-11-26
 * 
 * Changelog:
 * v1.0.0 - 2024-11-26
 * - Initial implementation
 * - Added conditional asset loading
 * - Added script localization
 * - Added version management
 * - Added toast fallback
 */

namespace WilayahIndonesia\Controllers\Settings;

class SettingsDependencyController {
    private $plugin_name;
    private $version;
    private $settings_page;

    public function __construct($plugin_name, $version, $settings_page) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_page = $settings_page;
    }

    public function init() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets($hook) {
        if ($hook !== 'wilayah-indonesia_page_' . $this->settings_page) {
            return;
        }

        // CSS
        
        wp_enqueue_style(
            $this->plugin_name . '-general-tab',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/general-tab-style.css',
            [],
            $this->version
        );

        wp_enqueue_style(
            $this->plugin_name . '-permission-tab',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/permission-tab-style.css',
            [],
            $this->version
        );

        wp_enqueue_style(
            $this->plugin_name . '-settings-style',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css',
            [],
            $this->version
        );

        // Toast fallback if needed
        if (!wp_script_is('ir-toast', 'registered')) {
            wp_enqueue_script(
                'ir-toast',
                WILAYAH_INDONESIA_URL . 'assets/js/components/toast.js',
                ['jquery'],
                $this->version,
                true
            );
        }

        // JS Dependencies
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-tabs');

        wp_enqueue_script(
            $this->plugin_name . '-permissions-script',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/permissions-script.js',
            ['jquery', 'ir-toast'],
            $this->version,
            true
        );
        wp_enqueue_script(
            $this->plugin_name . '-settings-script',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js',
            ['jquery', 'jquery-ui-tabs'],
            $this->version,
            true
        );

        // Localize// Di SettingsDependencyController.php
        wp_localize_script(
            $this->plugin_name . '-settings-script',
            'wilayahSettings',
            [
                'nonce' => wp_create_nonce('wilayah_settings_nonce'),
                'strings' => [
                    'saved' => __('Pengaturan berhasil disimpan.', 'wilayah-indonesia'),
                    'saveError' => __('Gagal menyimpan pengaturan.', 'wilayah-indonesia'),
                    'permissionSaved' => __('Hak akses berhasil disimpan.', 'wilayah-indonesia'),
                    'permissionError' => __('Gagal menyimpan hak akses.', 'wilayah-indonesia'),
                    'unauthorized' => __('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia')
                ]
            ]
        );
    }

    private function get_asset_version() {
        return defined('WP_DEBUG') && WP_DEBUG ? time() : $this->version;
    }
}
