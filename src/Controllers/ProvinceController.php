<?php
/**
* Province Controller Class
*
* @package     Wilayah_Indonesia
* @subpackage  Controllers
* @version     1.0.0
* @author      arisciwek
*
* Path: /wilayah-indonesia/src/Controllers/ProvinceController.php
*
* Description: Controller untuk mengelola data provinsi.
*              Menangani operasi CRUD dengan integrasi cache.
*              Includes validasi input, permission checks,
*              dan response formatting untuk panel kanan.
*              Menyediakan endpoints untuk DataTables server-side.
*
* Changelog:
* 1.0.1 - 2024-12-08
* - Added view_own_province permission check in show method
* - Enhanced permission validation
* - Improved error handling for permission checks
*
* Changelog:
* 1.0.0 - 2024-12-03 14:30:00
* - Refactor CRUD responses untuk panel kanan
* - Added cache integration di semua endpoints
* - Added konsisten response format
* - Added validasi dan permission di semua endpoints
* - Improved error handling dan feedback
*/

namespace WilayahIndonesia\Controllers;

use WilayahIndonesia\Models\ProvinceModel;
use WilayahIndonesia\Validators\ProvinceValidator;
use WilayahIndonesia\Cache\CacheManager;

class ProvinceController {
    private ProvinceModel $model;
    private ProvinceValidator $validator;
    private CacheManager $cache;
    private string $log_file;

    /**
     * Default log file path
     */
    private const DEFAULT_LOG_FILE = 'logs/province.log';

    public function __construct() {
        $this->model = new ProvinceModel();
        $this->validator = new ProvinceValidator();
        $this->cache = new CacheManager();

        // Inisialisasi log file di dalam direktori plugin
        $this->log_file = WILAYAH_INDONESIA_PATH . self::DEFAULT_LOG_FILE;

        // Pastikan direktori logs ada
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_province_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_nopriv_handle_province_datatable', [$this, 'handleDataTableRequest']);

        // Register endpoint untuk update
        add_action('wp_ajax_update_province', [$this, 'update']);

        // Register endpoint lain yang diperlukan
        add_action('wp_ajax_get_province', [$this, 'show']);
        add_action('wp_ajax_create_province', [$this, 'store']);
        add_action('wp_ajax_delete_province', [$this, 'delete']);

    }

    /**
     * Inisialisasi direktori log jika belum ada
     */
    private function initLogDirectory(): void {
        $log_dir = dirname($this->log_file);

        // Buat direktori jika belum ada
        if (!file_exists($log_dir)) {
            // Coba buat direktori dengan izin 0755
            if (!wp_mkdir_p($log_dir)) {
                // Jika gagal, gunakan sys_get_temp_dir sebagai fallback
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . 'wilayah-indonesia.log';
                return;
            }

            // Set proper permissions
            chmod($log_dir, 0755);
        }

        // Buat file log jika belum ada
        if (!file_exists($this->log_file)) {
            touch($this->log_file);
            chmod($this->log_file, 0644);
        }

        // Pastikan file bisa ditulis
        if (!is_writable($this->log_file)) {
            // Gunakan fallback ke temporary directory
            $this->log_file = rtrim(sys_get_temp_dir(), '/') . 'wilayah-indonesia.log';
        }
    }

    /**
     * Log debug messages ke file
     *
     * @param mixed $message Pesan yang akan dilog
     * @return void
     */
    private function debug_log($message): void {
        // Hanya jalankan jika WP_DEBUG aktif
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $timestamp = current_time('mysql');

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $log_message = "[{$timestamp}] {$message}\n";

        // Gunakan error_log bawaan WordPress dengan custom log file
        error_log($log_message, 3, $this->log_file);
    }

    public function handleDataTableRequest() {
        try {
            // Verify nonce
            if (!check_ajax_referer('wilayah_nonce', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            // Get and validate parameters
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';

            // Get order parameters
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? sanitize_text_field($_POST['order'][0]['dir']) : 'asc';

            // Map column index to column name
            $columns = ['code', 'name', 'regency_count', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'code';

            if ($orderBy === 'actions') {
                $orderBy = 'code'; // Default sort jika kolom actions
            }

            try {
                $result = $this->model->getDataTableData($start, $length, $search, $orderBy, $orderDir);

                if (!$result) {
                    throw new \Exception('No data returned from model');
                }

                $data = [];
                foreach ($result['data'] as $province) {
                    $data[] = [
                        'id' => $province->id,
                        'code' => esc_html($province->code),
                        'name' => esc_html($province->name),
                        'regency_count' => intval($province->regency_count),
                        'actions' => $this->generateActionButtons($province)
                    ];
                }

                $response = [
                    'draw' => $draw,
                    'recordsTotal' => $result['total'],
                    'recordsFiltered' => $result['filtered'],
                    'data' => $data,
                ];

                wp_send_json($response);

            } catch (\Exception $modelException) {
                throw new \Exception('Database error: ' . $modelException->getMessage());
            }

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 400);
        }
    }

    private function generateActionButtons($province) {
        $actions = '';

        if (current_user_can('view_province_detail')) {
            $actions .= sprintf(
                '<button type="button" class="button view-province" data-id="%d" title="%s"><i class="dashicons dashicons-visibility"></i></button> ',
                $province->id,
                __('Lihat', 'wilayah-indonesia')
            );
        }

        if (current_user_can('edit_province') ||
            (current_user_can('edit_own_province') && $province->created_by === get_current_user_id())) {
            $actions .= sprintf(
                '<button type="button" class="button edit-province" data-id="%d" title="%s"><i class="dashicons dashicons-edit"></i></button> ',
                $province->id,
                __('Edit', 'wilayah-indonesia')
            );
        }

        if (current_user_can('delete_province')) {
            $actions .= sprintf(
                '<button type="button" class="button delete-province" data-id="%d" title="%s"><i class="dashicons dashicons-trash"></i></button>',
                $province->id,
                __('Hapus', 'wilayah-indonesia')
            );
        }

        return $actions;
    }

    public function store() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            if (!current_user_can('create_province')) {
                wp_send_json_error(['message' => __('Insufficient permissions', 'wilayah-indonesia')]);
                return;
            }

            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'code' => sanitize_text_field($_POST['code']),
                'created_by' => get_current_user_id()
            ];

            // Validasi input
            $errors = $this->validator->validateCreate($data);
            if (!empty($errors)) {
                error_log('Validation errors found: ' . print_r($errors, true));
                wp_send_json_error([
                    'message' => is_array($errors) ? implode(', ', $errors) : $errors,
                    'errors' => $errors
                ]);
                return;
            }

            // Get ID from creation
            $id = $this->model->create($data);
            if (!$id) {
                error_log('Failed to create province');
                wp_send_json_error([
                    'message' => __('Failed to create province', 'wilayah-indonesia')
                ]);
                return;
            }

            error_log('Province created with ID: ' . $id);

            // Get fresh data for response
            $province = $this->model->find($id);
            if (!$province) {
                error_log('Failed to retrieve created province');
                wp_send_json_error([
                    'message' => __('Failed to retrieve created province', 'wilayah-indonesia')
                ]);
                return;
            }

            wp_send_json_success([
                'id' => $id,
                'province' => $province,
                'regency_count' => 0,
                'message' => __('Province created successfully', 'wilayah-indonesia')
            ]);

        } catch (\Exception $e) {
            error_log('Store error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error([
                'message' => $e->getMessage() ?: 'Terjadi kesalahan saat menambah provinsi',
                'error_details' => WP_DEBUG ? $e->getTraceAsString() : null
            ]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid province ID');
            }

            // Validasi input
            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'code' => sanitize_text_field($_POST['code'])  // Tambahkan ini
            ];

            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Update data
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update province');
            }

            // Get updated data
            $province = $this->model->find($id);
            if (!$province) {
                throw new \Exception('Failed to retrieve updated province');
            }

            wp_send_json_success([
                'message' => 'Province updated successfully',
                'data' => [
                    'province' => $province,
                    'regency_count' => $this->model->getRegencyCount($id)
                ]
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function show($id) {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

            if (!$id) {
                throw new \Exception('Invalid province ID');
            }

            $province = $this->model->find($id);
            if (!$province) {
                throw new \Exception('Province not found');
            }

            // Add permission check
            if (!current_user_can('view_province_detail') &&
                (!current_user_can('view_own_province') || $province->created_by !== get_current_user_id())) {
                throw new \Exception('You do not have permission to view this province');
            }

            wp_send_json_success([
                'province' => $province,
                'regency_count' => $this->model->getRegencyCount($id)
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function delete() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            // Ensure we have an ID and it's properly cast to integer
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if (!$id) {
                throw new \Exception('Invalid province ID');
            }

            // Validate delete operation
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                throw new \Exception(reset($errors));
            }

            // Perform delete
            if (!$this->model->delete($id)) {
                throw new \Exception('Failed to delete province');
            }

            // Clear cache
            $this->cache->invalidateProvinceCache($id);

            wp_send_json_success(['message' => 'Province deleted successfully']);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
