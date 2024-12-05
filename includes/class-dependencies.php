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
        $screen = get_current_screen();
        if (!$screen) return;

        // Settings page styles
        if ($screen->id === 'wilayah-indonesia_page_wilayah-indonesia-settings') {
            wp_enqueue_style('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css', [], $this->version);
            
            $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
            switch ($current_tab) {
                case 'permission':
                    wp_enqueue_style('wilayah-permission-tab', WILAYAH_INDONESIA_URL . 'assets/css/settings/permission-tab-style.css', [], $this->version);
                    break;
                case 'general':
                    wp_enqueue_style('wilayah-general-tab', WILAYAH_INDONESIA_URL . 'assets/css/settings/general-tab-style.css', [], $this->version);
                    break;
            }
            return;
        }

        // Province page styles
        if ($screen->id === 'toplevel_page_wilayah-indonesia') {
            wp_enqueue_style('wilayah-province', WILAYAH_INDONESIA_URL . 'assets/css/province.css', [], $this->version);
            wp_enqueue_style('wilayah-province-form', WILAYAH_INDONESIA_URL . 'assets/css/province-form.css', [], $this->version);
            wp_enqueue_style('wilayah-toast', WILAYAH_INDONESIA_URL . 'assets/css/components/toast.css', [], $this->version);
            
            // DataTables
            wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', [], '1.13.7');
        }
    }
    public function enqueue_scripts() {
       $screen = get_current_screen();
       if (!$screen) return;

       // Settings page scripts
       if ($screen->id === 'wilayah-indonesia_page_wilayah-indonesia-settings') {
           wp_enqueue_script('wilayah-toast', WILAYAH_INDONESIA_URL . 'assets/js/components/toast.js', ['jquery'], $this->version, true);
           wp_enqueue_script('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js', ['jquery', 'wilayah-toast'], $this->version, true);
           
           if (isset($_GET['tab']) && $_GET['tab'] === 'permission') {
               wp_enqueue_script('wilayah-permissions', WILAYAH_INDONESIA_URL . 'assets/js/settings/permissions-script.js', ['jquery', 'wilayah-toast'], $this->version, true);
           }
           return;
       }

       // Province page scripts  
        if ($screen->id === 'toplevel_page_wilayah-indonesia') {
            // Core dependencies
            wp_enqueue_script('jquery-validate', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', ['jquery'], '1.19.5', true);
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', ['jquery'], '1.13.7', true);
            
            // Components
            wp_enqueue_script('province-toast', WILAYAH_INDONESIA_URL . 'assets/js/components/province-toast.js', ['jquery'], $this->version, true);
            wp_enqueue_script('province-datatable', WILAYAH_INDONESIA_URL . 'assets/js/components/province-datatable.js', ['jquery', 'datatables', 'province-toast'], $this->version, true);
            
            // Form handlers - dengan handle yang berbeda
            wp_enqueue_script('create-province-form', WILAYAH_INDONESIA_URL . 'assets/js/components/create-province-form.js', ['jquery', 'jquery-validate', 'province-toast'], $this->version, true);
            wp_enqueue_script('edit-province-form', WILAYAH_INDONESIA_URL . 'assets/js/components/edit-province-form.js', ['jquery', 'jquery-validate', 'province-toast'], $this->version, true);
            
            // Main province script - update dependencies
            wp_enqueue_script('province', WILAYAH_INDONESIA_URL . 'assets/js/province.js', [
                'jquery', 
                'province-toast', 
                'province-datatable',
                'create-province-form',
                'edit-province-form'
            ], $this->version, true);

            // Localize script
            wp_localize_script('province', 'wilayahData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wilayah_nonce')
            ]);
        }


    }
}
