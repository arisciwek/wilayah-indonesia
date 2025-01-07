<?php
/**
 * Dependencies Handler Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Includes
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/includes/class-dependencies.php
 *
 * Description: Menangani dependencies plugin seperti CSS, JavaScript,
 *              dan library eksternal
 *
 * Changelog:
 * 1.1.0 - 2024-12-10
 * - Added regency management dependencies
 * - Added regency CSS and JS files
 * - Updated screen checks for regency assets
 * - Fixed path inconsistencies
 * - Added common-style.css
 *
 * 1.0.0 - 2024-11-23
 * - Initial creation
 * - Added asset enqueuing methods
 * - Added CDN dependencies
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
            wp_enqueue_style('wilayah-common', WILAYAH_INDONESIA_URL . 'assets/css/settings/common-style.css', [], $this->version);
            wp_enqueue_style('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/css/settings/settings-style.css', ['wilayah-common'], $this->version);
            wp_enqueue_style('wilayah-modal', WILAYAH_INDONESIA_URL . 'assets/css/components/confirmation-modal.css', [], $this->version);

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

        // Province and Regency pages styles
        if ($screen->id === 'toplevel_page_wilayah-indonesia') {
            // Core styles
            wp_enqueue_style('wilayah-toast', WILAYAH_INDONESIA_URL . 'assets/css/components/toast.css', [], $this->version);
            wp_enqueue_style('wilayah-modal', WILAYAH_INDONESIA_URL . 'assets/css/components/confirmation-modal.css', [], $this->version);
            // Regency toast - terpisah
            wp_enqueue_style('regency-toast', WILAYAH_INDONESIA_URL . 'assets/css/regency/regency-toast.css', [], $this->version);

            // DataTables
            wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css', [], '1.13.7');

            // Province styles
            wp_enqueue_style('wilayah-province', WILAYAH_INDONESIA_URL . 'assets/css/province.css', [], $this->version);
            wp_enqueue_style('wilayah-province-form', WILAYAH_INDONESIA_URL . 'assets/css/province-form.css', [], $this->version);

            // Regency styles
            wp_enqueue_style('wilayah-regency', WILAYAH_INDONESIA_URL . 'assets/css/regency/regency.css', [], $this->version);
        }
    }

    public function enqueue_scripts() {
        $screen = get_current_screen();
        if (!$screen) return;

        // Settings page scripts
        if ($screen->id === 'wilayah-indonesia_page_wilayah-indonesia-settings') {
            wp_enqueue_script('wilayah-toast', WILAYAH_INDONESIA_URL . 'assets/js/components/toast.js', ['jquery'], $this->version, true);
            wp_enqueue_script('confirmation-modal', WILAYAH_INDONESIA_URL . 'assets/js/components/confirmation-modal.js', ['jquery'], $this->version, true);
            wp_enqueue_script('wilayah-settings', WILAYAH_INDONESIA_URL . 'assets/js/settings/settings-script.js', ['jquery', 'wilayah-toast'], $this->version, true);

            if (isset($_GET['tab']) && $_GET['tab'] === 'permission') {
                wp_enqueue_script('wilayah-permissions', WILAYAH_INDONESIA_URL . 'assets/js/settings/permissions-script.js', ['jquery', 'wilayah-toast'], $this->version, true);
            }
            return;
        }

        // Province and Regency pages scripts
        if ($screen->id === 'toplevel_page_wilayah-indonesia') {
            // Core dependencies
            wp_enqueue_script('jquery-validate', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', ['jquery'], '1.19.5', true);
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js', ['jquery'], '1.13.7', true);

            // Components
            wp_enqueue_script('province-toast', WILAYAH_INDONESIA_URL . 'assets/js/components/province-toast.js', ['jquery'], $this->version, true);
            wp_enqueue_script('confirmation-modal', WILAYAH_INDONESIA_URL . 'assets/js/components/confirmation-modal.js', ['jquery'], $this->version, true);
            // Regency toast
            wp_enqueue_script('regency-toast', WILAYAH_INDONESIA_URL . 'assets/js/regency/regency-toast.js', ['jquery'], $this->version, true);

            // Province scripts - path fixed according to tree.md
            wp_enqueue_script('province-datatable', WILAYAH_INDONESIA_URL . 'assets/js/components/province-datatable.js', ['jquery', 'datatables', 'province-toast'], $this->version, true);
            wp_enqueue_script('create-province-form', WILAYAH_INDONESIA_URL . 'assets/js/components/create-province-form.js', ['jquery', 'jquery-validate', 'province-toast'], $this->version, true);
            wp_enqueue_script('edit-province-form', WILAYAH_INDONESIA_URL . 'assets/js/components/edit-province-form.js', ['jquery', 'jquery-validate', 'province-toast'], $this->version, true);

            wp_enqueue_script('wilayah-dashboard',
                WILAYAH_INDONESIA_URL . 'assets/js/dashboard.js',
                ['jquery'],
                $this->version,
                true
            );

            wp_enqueue_script('province',
                WILAYAH_INDONESIA_URL . 'assets/js/province.js',
                [
                    'jquery',
                    'province-toast',
                    'province-datatable',
                    'create-province-form',
                    'edit-province-form',
                    'wilayah-dashboard' // Tambahkan dependency
                ],
                $this->version,
                true
            );

            // Regency scripts
            wp_enqueue_script('regency-datatable', WILAYAH_INDONESIA_URL . 'assets/js/regency/regency-datatable.js', ['jquery', 'datatables', 'province-toast', 'province'], $this->version, true);
            wp_enqueue_script('regency-toast', WILAYAH_INDONESIA_URL . 'assets/js/regency/regency-toast.js', ['jquery'], $this->version, true);
            // Update dependencies untuk form
            wp_enqueue_script('create-regency-form', WILAYAH_INDONESIA_URL . 'assets/js/regency/create-regency-form.js', ['jquery', 'jquery-validate', 'regency-toast', 'regency-datatable'], $this->version, true);
            wp_enqueue_script('edit-regency-form', WILAYAH_INDONESIA_URL . 'assets/js/regency/edit-regency-form.js', ['jquery', 'jquery-validate', 'regency-toast', 'regency-datatable'], $this->version, true);
            // Localize script
            wp_localize_script('province', 'wilayahData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wilayah_nonce')
            ]);

        }
    }

    public function enqueue_select_handler() {
        // Cek apakah sudah di-enqueue sebelumnya
        if (wp_script_is('wilayah-select-handler', 'enqueued')) {
            return;
        }

        wp_enqueue_script('wilayah-select-handler', 
            WILAYAH_INDONESIA_URL . 'assets/js/components/select-handler.js', 
            ['jquery'], 
            $this->version, 
            true
        );

        wp_localize_script('wilayah-select-handler', 'wilayahSelectData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wilayah_nonce'),
            'texts' => [
                'select_province' => __('Pilih Provinsi', 'wilayah-indonesia'),
                'select_regency' => __('Pilih Kabupaten/Kota', 'wilayah-indonesia'),
                'loading' => __('Memuat...', 'wilayah-indonesia')
            ]
        ]);
    }

}
