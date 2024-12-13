<?php
/**
 * Dashboard Controller Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Controllers
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Controllers/DashboardController.php
 *
 * Description: Controller untuk mengelola statistik dashboard.
 *              Menyediakan endpoint untuk mendapatkan total regency
 *              secara global untuk tampilan statistik.
 */

namespace WilayahIndonesia\Controllers;

use WilayahIndonesia\Models\Regency\RegencyModel;

class DashboardController {
    private RegencyModel $regency_model;

    public function __construct() {
        $this->regency_model = new RegencyModel();

        // Register AJAX endpoint
        add_action('wp_ajax_get_dashboard_stats', [$this, 'getDashboardStats']);
        add_action('wp_ajax_nopriv_get_dashboard_stats', [$this, 'getDashboardStats']);
    }

    public function getDashboardStats() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $stats = [
                'total_regencies' => $this->regency_model->getTotalCount()
            ];

            wp_send_json_success($stats);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
}
