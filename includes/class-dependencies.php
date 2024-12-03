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

        // Tambahkan action hook untuk DataTables di sini
        add_action('wp_ajax_handle_province_datatable', array($this, 'handleProvinceDataTable'));
    
        add_action('wp_ajax_create_province', array(new \WilayahIndonesia\Controllers\ProvinceController(), 'store'));
    }

    public function enqueue_styles() {
        // Get current screen
        $screen = get_current_screen();
        
        // Only load for our plugin pages
        if (!$screen || strpos($screen->id, 'wilayah-indonesia') === false) {
            return;
        }
        
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

        // Local styles
        wp_enqueue_style(
            $this->plugin_name . '-province',
            WILAYAH_INDONESIA_URL . 'assets/css/province.css',
            array(),
            $this->version
        );

        wp_enqueue_style(
            $this->plugin_name . '-province-form',
            WILAYAH_INDONESIA_URL . 'assets/css/province-form.css',
            array(),
            $this->version
        );

        // Settings styles
        wp_enqueue_style(
            'wilayah-settings',
            WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css',
            array(),
            $this->version
        );

        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        
        // Tab specific styles
        switch ($current_tab) {
            case 'permission':
                wp_enqueue_style(
                    $this->plugin_name . '-permission-style',
                    WILAYAH_INDONESIA_URL . 'assets/css/settings/permission-tab-style.css',
                    array(),
                    $this->version
                );
                break;

            case 'general':
                wp_enqueue_style(
                    $this->plugin_name . '-general-tab',
                    WILAYAH_INDONESIA_URL . 'assets/css/settings/general-tab-style.css',
                    array(),
                    $this->version
                );
                break;
        }
    }

    public function enqueue_scripts() {
        wp_add_inline_script('jquery-migrate', 'jQuery.migrateMute = true;', 'after');

        // Get current screen  
        $screen = get_current_screen();
        
        // Only load for our plugin pages
        if (!$screen || strpos($screen->id, 'wilayah-indonesia') === false) {
            return;
        }

        // Add jQuery Validator
        wp_enqueue_script(
            $this->plugin_name . '-validator',
            'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js',
            array('jquery'),
            '1.19.5',
            true
        );

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

        // Toast Components
        wp_enqueue_script(
            $this->plugin_name . '-toast',
            WILAYAH_INDONESIA_URL . 'assets/js/components/toast.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-province-toast',
            WILAYAH_INDONESIA_URL . 'assets/js/components/province-toast.js',
            array('jquery', $this->plugin_name . '-toast'),
            $this->version,
            true
        );

        // Province Components
        wp_enqueue_script(
            $this->plugin_name . '-province-datatable',
            WILAYAH_INDONESIA_URL . 'assets/js/components/province-datatable.js',
            array('jquery', $this->plugin_name . '-datatables', $this->plugin_name . '-province-toast'),
            $this->version,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-province-form',
            WILAYAH_INDONESIA_URL . 'assets/js/components/province-form.js',
            array('jquery', $this->plugin_name . '-province-toast'),
            $this->version,
            true
        );

        // Main Province Script
        wp_enqueue_script(
            $this->plugin_name . '-province',
            WILAYAH_INDONESIA_URL . 'assets/js/province.js',
            array(
                'jquery',
                $this->plugin_name . '-toast',
                $this->plugin_name . '-province-toast',
                $this->plugin_name . '-province-datatable',
                $this->plugin_name . '-province-form'
            ),
            $this->version,
            true
        );

        // Localized data untuk DataTables dan capabilities
        wp_localize_script(
            $this->plugin_name . '-province',
            'wilayahData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wilayah_nonce'),
                'perPage' => get_option('posts_per_page'),
                'caps' => [
                    'view' => current_user_can('view_province'),
                    'edit' => current_user_can('edit_province'),
                    'delete' => current_user_can('delete_province')
                ]
            ]
        );
    }
    
    
    // Tambahkan method callback untuk DataTables
    public function handleProvinceDataTable() {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        error_log('DataTables request received: ' . print_r($_POST, true));
        
        $controller = new \WilayahIndonesia\Controllers\ProvinceController();
        $controller->handleDataTableRequest();
    }
}
