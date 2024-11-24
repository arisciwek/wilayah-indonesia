<?php
/**
 * File: SettingsDependencyController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsDependencyController.php
 * Description: Handles settings page dependencies (CSS/JS)
 * Last modified: 2024-11-24
 */

namespace WilayahIndonesia\Controllers\Settings;

class SettingsDependencyController {
    private $plugin_name;
    private $version;
    private $settings_page;

    public function __construct($plugin_name, $version, $settings_page) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_page = $settings_page; // wilayah-indonesia-settings
    }

    public function init() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets($hook) {
        // Only load on settings page
        if($hook !== 'wilayah-indonesia_page_' . $this->settings_page) {
            return;
        }

        // Enqueue Settings CSS
        wp_enqueue_style(
            $this->plugin_name . '-settings-style',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css',
            [],
            $this->version
        );
        
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

        // Enqueue Settings JavaScript
        wp_enqueue_script(
            $this->plugin_name . '-settings-script',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-general-tab',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/general-tab-script.js',
            ['jquery'],
            $this->version,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-permission-tab',
            WILAYAH_INDONESIA_URL . 'assets/js/settings/permission-tab-script.js',
            ['jquery'],
            $this->version,
            true
        );

        // Localize script for settings
        wp_localize_script($this->plugin_name . '-settings-script', 'wilayahSettings', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wilayah_settings_nonce'),
            'strings' => [
                'saved' => __('Pengaturan berhasil disimpan.', 'wilayah-indonesia'),
                'saveError' => __('Gagal menyimpan pengaturan.', 'wilayah-indonesia'),
                'confirmDelete' => __('Anda yakin ingin menghapus ini?', 'wilayah-indonesia')
            ]
        ]);
    }
}