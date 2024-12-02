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
        // Get current screen  
        $screen = get_current_screen();
        
        // Only load for our plugin pages
        if (!$screen || strpos($screen->id, 'wilayah-indonesia') === false) {
            return;
        }

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

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

        // Load toast component
        wp_enqueue_script(
            $this->plugin_name . '-toast',
            WILAYAH_INDONESIA_URL . 'assets/js/components/toast.js',
            array('jquery'),
            $this->version,
            true
        );

        // Settings scripts
        if (strpos($screen->id, 'wilayah-indonesia-settings') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-settings',
                WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js',
                array('jquery', $this->plugin_name . '-toast'),
                $this->version,
                true
            );

            // Tab specific scripts
            if ($current_tab === 'permission') {
                wp_enqueue_script(
                    $this->plugin_name . '-permissions',
                    WILAYAH_INDONESIA_URL . 'assets/js/settings/permissions-script.js',
                    array('jquery', $this->plugin_name . '-toast', $this->plugin_name . '-settings'),
                    $this->version,
                    true
                );

                wp_localize_script(
                    $this->plugin_name . '-permissions',
                    'wilayahSettings',
                    array(
                        'strings' => array(
                            'confirmReset' => __('Yakin ingin mereset semua hak akses ke default?', 'wilayah-indonesia'),
                            'saveSuccess' => __('Hak akses berhasil diperbarui.', 'wilayah-indonesia'),
                            'saveError' => __('Terjadi kesalahan saat menyimpan hak akses.', 'wilayah-indonesia'),
                            'networkError' => __('Gagal menghubungi server. Silakan coba lagi.', 'wilayah-indonesia')
                        )
                    )
                );

                // Localized data untuk DataTables
                wp_localize_script(
                    'wilayah-province', // handle yang sama dengan yang digunakan saat enqueue
                    'wilayahData',      // nama variable yang akan diakses di JavaScript
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
        }
    }
    
    // Tambahkan method callback untuk DataTables
    public function handleProvinceDataTable() {
        $controller = new \WilayahIndonesia\Controllers\ProvinceController();
        $controller->handleDataTableRequest();
    }
}
