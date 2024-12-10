<?php
/**
 * Regency Controller Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Controllers/Regency
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Controllers/Regency/RegencyController.php
 *
 * Description: Controller untuk mengelola data kabupaten/kota.
 *              Menangani operasi CRUD dengan integrasi cache.
 *              Includes validasi input, permission checks,
 *              dan response formatting untuk DataTables.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial implementation
 * - Added CRUD endpoints
 * - Added DataTables integration
 * - Added permission checks
 * - Added cache support
 */

namespace WilayahIndonesia\Controllers\Regency;

use WilayahIndonesia\Models\Regency\RegencyModel;
use WilayahIndonesia\Validators\Regency\RegencyValidator;
use WilayahIndonesia\Cache\CacheManager;

class RegencyController {
    private RegencyModel $model;
    private RegencyValidator $validator;
    private CacheManager $cache;
    private string $log_file;

    /**
     * Default log file path
     */
    private const DEFAULT_LOG_FILE = 'logs/regency.log';

    public function __construct() {
        $this->model = new RegencyModel();
        $this->validator = new RegencyValidator();
        $this->cache = new CacheManager();

        // Initialize log file inside plugin directory
        $this->log_file = WILAYAH_INDONESIA_PATH . self::DEFAULT_LOG_FILE;

        // Ensure logs directory exists
        $this->initLogDirectory();

        // Register AJAX handlers
        add_action('wp_ajax_handle_regency_datatable', [$this, 'handleDataTableRequest']);
        add_action('wp_ajax_nopriv_handle_regency_datatable', [$this, 'handleDataTableRequest']);

        // Register other endpoints
        add_action('wp_ajax_get_regency', [$this, 'show']);
        add_action('wp_ajax_create_regency', [$this, 'store']);
        add_action('wp_ajax_update_regency', [$this, 'update']);
        add_action('wp_ajax_delete_regency', [$this, 'delete']);
    }

    /**
     * Initialize log directory if it doesn't exist
     */
    private function initLogDirectory(): void {
        $log_dir = dirname($this->log_file);

        if (!file_exists($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wilayah-indonesia-regency.log';
                return;
            }
            chmod($log_dir, 0755);
        }

        if (!file_exists($this->log_file)) {
            touch($this->log_file);
            chmod($this->log_file, 0644);
        }

        if (!is_writable($this->log_file)) {
            $this->log_file = rtrim(sys_get_temp_dir(), '/') . '/wilayah-indonesia-regency.log';
        }
    }

    /**
     * Log debug messages to file
     */
    private function debug_log($message): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $timestamp = current_time('mysql');

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $log_message = "[{$timestamp}] {$message}\n";
        error_log($log_message, 3, $this->log_file);
    }

    public function handleDataTableRequest() {
        try {
            // Verify nonce
            if (!check_ajax_referer('wilayah_nonce', 'nonce', false)) {
                throw new \Exception('Security check failed');
            }

            // Get and validate province_id
            $province_id = isset($_POST['province_id']) ? intval($_POST['province_id']) : 0;
            if (!$province_id) {
                throw new \Exception('Invalid province ID');
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
            $columns = ['name', 'type', 'actions'];
            $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'name';

            if ($orderBy === 'actions') {
                $orderBy = 'name'; // Default sort if actions column
            }

            try {
                $result = $this->model->getDataTableData(
                    $province_id,
                    $start,
                    $length,
                    $search,
                    $orderBy,
                    $orderDir
                );

                if (!$result) {
                    throw new \Exception('No data returned from model');
                }

                $data = [];
                foreach ($result['data'] as $regency) {
                    $data[] = [
                        'id' => $regency->id,
                        'name' => esc_html($regency->name),
                        'type' => esc_html($regency->type),
                        'province_name' => esc_html($regency->province_name),
                        'actions' => $this->generateActionButtons($regency)
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

    private function generateActionButtons($regency) {
        $actions = '';

        if (current_user_can('view_regency_detail')) {
            $actions .= sprintf(
                '<button type="button" class="button view-regency" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-visibility"></i></button> ',
                $regency->id,
                __('Lihat', 'wilayah-indonesia')
            );
        }

        if (current_user_can('edit_all_regencies') ||
            (current_user_can('edit_own_regency') && $regency->created_by === get_current_user_id())) {
            $actions .= sprintf(
                '<button type="button" class="button edit-regency" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-edit"></i></button> ',
                $regency->id,
                __('Edit', 'wilayah-indonesia')
            );
        }

        if (current_user_can('delete_regency')) {
            $actions .= sprintf(
                '<button type="button" class="button delete-regency" data-id="%d" title="%s">' .
                '<i class="dashicons dashicons-trash"></i></button>',
                $regency->id,
                __('Hapus', 'wilayah-indonesia')
            );
        }

        return $actions;
    }

    public function store() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            if (!current_user_can('add_regency')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
                return;
            }

            $data = [
                'province_id' => intval($_POST['province_id']),
                'name' => sanitize_text_field($_POST['name']),
                'type' => sanitize_text_field($_POST['type']),
                'created_by' => get_current_user_id()
            ];

            // Validate input
            $errors = $this->validator->validateCreate($data);
            if (!empty($errors)) {
                $this->debug_log('Validation errors found: ' . print_r($errors, true));
                wp_send_json_error([
                    'message' => is_array($errors) ? implode(', ', $errors) : $errors,
                    'errors' => $errors
                ]);
                return;
            }

            // Get ID from creation
            $id = $this->model->create($data);
            if (!$id) {
                $this->debug_log('Failed to create regency');
                wp_send_json_error([
                    'message' => __('Failed to create regency', 'wilayah-indonesia')
                ]);
                return;
            }

            $this->debug_log('Regency created with ID: ' . $id);

            // Get fresh data for response
            $regency = $this->model->find($id);
            if (!$regency) {
                $this->debug_log('Failed to retrieve created regency');
                wp_send_json_error([
                    'message' => __('Failed to retrieve created regency', 'wilayah-indonesia')
                ]);
                return;
            }

            wp_send_json_success([
                'message' => __('Regency created successfully', 'wilayah-indonesia'),
                'regency' => $regency
            ]);

        } catch (\Exception $e) {
            $this->debug_log('Store error: ' . $e->getMessage());
            $this->debug_log('Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error([
                'message' => $e->getMessage() ?: __('Failed to add regency', 'wilayah-indonesia'),
                'error_details' => WP_DEBUG ? $e->getTraceAsString() : null
            ]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                throw new \Exception('Invalid regency ID');
            }

            // Validate input
            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'type' => sanitize_text_field($_POST['type'])
            ];

            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => implode(', ', $errors)]);
                return;
            }

            // Update data
            $updated = $this->model->update($id, $data);
            if (!$updated) {
                throw new \Exception('Failed to update regency');
            }

            // Get updated data
            $regency = $this->model->find($id);
            if (!$regency) {
                throw new \Exception('Failed to retrieve updated regency');
            }

            wp_send_json_success([
                'message' => __('Regency updated successfully', 'wilayah-indonesia'),
                'regency' => $regency
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function show() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if (!$id) {
                throw new \Exception('Invalid regency ID');
            }

            $regency = $this->model->find($id);
            if (!$regency) {
                throw new \Exception('Regency not found');
            }

            // Add permission check
            if (!current_user_can('view_regency_detail') &&
                (!current_user_can('view_own_regency') || $regency->created_by !== get_current_user_id())) {
                throw new \Exception('You do not have permission to view this regency');
            }

            wp_send_json_success([
                'regency' => $regency
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function delete() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            if (!$id) {
                throw new \Exception('Invalid regency ID');
            }

            // Validate delete operation
            $errors = $this->validator->validateDelete($id);
            if (!empty($errors)) {
                throw new \Exception(reset($errors));
            }

            // Perform delete
            if (!$this->model->delete($id)) {
                throw new \Exception('Failed to delete regency');
            }

            wp_send_json_success([
                'message' => __('Regency deleted successfully', 'wilayah-indonesia')
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
