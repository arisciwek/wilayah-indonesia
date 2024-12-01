<?php
/**
 * File: SettingsDependencyController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsDependencyController.php
 * Description: Controller untuk mengelola asset dependencies halaman settings
 * Version: 2.0.0
 * Last modified: 2024-12-01
 */

namespace WilayahIndonesia\Controllers\Settings;

class SettingsDependencyController {
    private $plugin_name;
    private $version;
    private $settings_page;
    private $current_tab;

    public function __construct($plugin_name, $version, $settings_page) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_page = $settings_page;
        
        // Get current tab if set
        $this->current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
    }

    public function init() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets($hook) {
        // Only load on our settings page
        if ($hook !== 'wilayah-indonesia_page_' . $this->settings_page) {
            return;
        }

        // Core styles that always needed
        $this->enqueueCommonStyles();
        
        // Tab specific styles and scripts
        $this->enqueueTabSpecificAssets();

        // Core scripts that always needed
        $this->enqueueCommonScripts();
        
        // Localize scripts
        $this->localizeScripts();
    }

    private function enqueueCommonStyles() {
        // Common styles for all settings pages
        wp_enqueue_style(
            $this->plugin_name . '-common-style',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/common-style.css',
            [],
            $this->get_asset_version()
        );

        wp_enqueue_style(
            $this->plugin_name . '-settings-style',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css',
            [],
            $this->get_asset_version()
        );
    }

    private function enqueueTabSpecificAssets() {
        switch ($this->current_tab) {
            case 'permission':
                // Permission tab specific styles
                wp_enqueue_style(
                    $this->plugin_name . '-permission-style',
                    WILAYAH_INDONESIA_URL . 'assets/css/settings/permission--tab-style.css',
                    [],
                    $this->get_asset_version()
                );

                // Permission tab specific scripts
                wp_enqueue_script(
                    $this->plugin_name . '-permission-script',
                    WILAYAH_INDONESIA_URL . 'assets/js/settings/permissions--script.js',
                    ['jquery'],
                    $this->get_asset_version(),
                    true
                );
                break;

            case 'general':
                // General tab specific assets if any
                wp_enqueue_style(
                    $this->plugin_name . '-general-tab',
                    WILAYAH_INDONESIA_URL . 'assets/css/settings/general-tab-style.css',
                    [],
                    $this->get_asset_version()
                );
                break;
                
            // Add other tabs as needed
        }
    }

    private function enqueueCommonScripts() {
        // jQuery UI tabs
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-tabs');

        // Settings core script
        wp_enqueue_script(
            $this->plugin_name . '-settings-script',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js',
            ['jquery', 'jquery-ui-tabs'],
            $this->get_asset_version(),
            true
        );
    }

    private function localizeScripts() {
        wp_localize_script(
            $this->plugin_name . '-settings-script',
            'wilayahSettings',
            [
                'nonce' => wp_create_nonce('wilayah_settings_nonce'),
                'strings' => [
                    'saved' => __('Pengaturan berhasil disimpan.', 'wilayah-indonesia'),
                    'saveError' => __('Gagal menyimpan pengaturan.', 'wilayah-indonesia'),
                    'confirmReset' => __('Anda yakin ingin mereset pengaturan ke default?', 'wilayah-indonesia'),
                    'unauthorized' => __('Anda tidak memiliki izin untuk ini.', 'wilayah-indonesia')
                ],
                'currentTab' => $this->current_tab
            ]
        );
    }

    private function get_asset_version() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return time(); // For development
        }
        return $this->version;
    }
}
