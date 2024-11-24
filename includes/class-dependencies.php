<?php
/**
 * File: class-dependencies.php
 * Path: /wilayah-indonesia/includes/class-dependencies.php
 * Description: Menangani dependencies plugin seperti CSS, JavaScript, dan library eksternal
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 *   - 2024-11-23: Added asset enqueuing methods
 *   - 2024-11-23: Revised to use CDN for external libraries
 */

class Wilayah_Indonesia_Dependencies {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        // Bootstrap dari CDN
        wp_enqueue_style(
            $this->plugin_name . '-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            array(),
            '5.3.2'
        );

        // DataTables dari CDN
        wp_enqueue_style(
            $this->plugin_name . '-datatables',
            'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css',
            array(),
            '1.13.7'
        );

        // Local styles - hanya jika file ada
        if (file_exists(WILAYAH_INDONESIA_PATH . 'assets/css/province-style.css')) {
            wp_enqueue_style(
                $this->plugin_name,
                WILAYAH_INDONESIA_URL . 'assets/css/province.css',
                array(),
                $this->version,
                'all'
            );
        }

        wp_enqueue_style('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css');
    }

    public function enqueue_scripts() {
        // jQuery sudah include di WordPress
        
        // Bootstrap JS dari CDN
        wp_enqueue_script(
            $this->plugin_name . '-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.2',
            true
        );

        // DataTables dari CDN
        wp_enqueue_script(
            $this->plugin_name . '-datatables',
            'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
            array('jquery'),
            '1.13.7',
            true
        );

        // Local script - hanya jika file ada
        if (file_exists(WILAYAH_INDONESIA_PATH . 'assets/js/province-script.js')) {
            wp_enqueue_script(
                $this->plugin_name,
                WILAYAH_INDONESIA_URL . 'assets/js/province-script.js',
                array('jquery'),
                $this->version,
                true
            );

            wp_localize_script($this->plugin_name, 'wilayahData', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('wilayah_nonce'),
            ));
        }

        wp_enqueue_script('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js');
    }
}
