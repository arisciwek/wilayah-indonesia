<?php
/**
 * File: ProvinceController.php
 * Path: /wilayah-indonesia/src/Controllers/ProvinceController.php
 * Description: Controller untuk mengelola provinsi dengan pengiriman data lengkap ke panel kanan
 * Version: 2.0.0
 * Last modified: 2024-12-03
 * 
 * Changelog:
 * 2.0.0 - 2024-12-03
 * - Revisi load data provinsi untuk panel kanan
 * - Optimasi pengiriman data setelah CRUD
 * - Penyesuaian format response untuk DataTables
 */

namespace WilayahIndonesia\Controllers;

use WilayahIndonesia\Models\ProvinceModel;
use WilayahIndonesia\Validators\ProvinceValidator;

class ProvinceController {
    private $model;
    private $validator;
    
    // Add cache constants
    private const CACHE_GROUP = 'wilayah_indonesia';
    private const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;

    public function __construct() {
        $this->model = new ProvinceModel();
        $this->validator = new ProvinceValidator();
    }

    /**
     * Get formatted province data with caching
     */
    private function getProvinceData($id) {
        // Try cache first
        $cache_key = "province_{$id}";
        $data = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if (false !== $data) {
            return $data;
        }

        // Cache miss, get from database
        $province = $this->model->find($id);
        if (!$province) {
            return null;
        }

        // Format data
        $data = $this->formatProvinceData($province);
        
        // Save to cache
        wp_cache_set($cache_key, $data, self::CACHE_GROUP, self::CACHE_EXPIRY);
        
        return $data;
    }

    /**
     * Invalidate cache after CRUD operations
     */
    private function invalidateCache($id) {
        wp_cache_delete("province_{$id}", self::CACHE_GROUP);
        wp_cache_delete('province_list', self::CACHE_GROUP);
    }

    public function show() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');
            $id = intval($_POST['id']);

            // Get data (from cache or database)
            $data = $this->getProvinceData($id);
            if (!$data) {
                wp_send_json_error([
                    'message' => __('Province not found', 'wilayah-indonesia')
                ]);
            }

            // Check permissions after getting data
            if (!$this->canViewProvince($data['province'])) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
            }

            wp_send_json_success(['data' => $data]);

        } catch (Exception $e) {
            error_log('Get province error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('An error occurred', 'wilayah-indonesia')
            ]);
        }
    }

    public function store() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            if (!current_user_can('create_province')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
            }

            $data = [
                'name' => sanitize_text_field($_POST['name']),
                'created_by' => get_current_user_id()
            ];

            $errors = $this->validator->validateCreate($data);
            if (!empty($errors)) {
                wp_send_json_error(['message' => $errors]);
            }

            $id = $this->model->create($data);
            if (!$id) {
                wp_send_json_error([
                    'message' => __('Failed to create province', 'wilayah-indonesia')
                ]);
            }

            // Get fresh data using cache-aware method
            $formattedData = $this->getProvinceData($id);

            wp_send_json_success([
                'message' => __('Province created successfully', 'wilayah-indonesia'),
                'id' => $id,
                'data' => $formattedData
            ]);

        } catch (Exception $e) {
            error_log('Create province error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('An error occurred', 'wilayah-indonesia')
            ]);
        }
    }

    public function update() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = intval($_POST['id']);
            $province = $this->model->find($id);

            if (!$province) {
                wp_send_json_error([
                    'message' => __('Province not found', 'wilayah-indonesia')
                ]);
            }

            if (!$this->canEditProvince($province)) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
            }

            $data = [
                'name' => sanitize_text_field($_POST['name'])
            ];

            $errors = $this->validator->validateUpdate($data, $id);
            if (!empty($errors)) {
                wp_send_json_error(['message' => $errors]);
            }

            if (!$this->model->update($id, $data)) {
                wp_send_json_error([
                    'message' => __('Failed to update province', 'wilayah-indonesia')
                ]);
            }

            // Invalidate cache
            $this->invalidateCache($id);

            // Get fresh data using cache-aware method
            $formattedData = $this->getProvinceData($id);

            wp_send_json_success([
                'message' => __('Province updated successfully', 'wilayah-indonesia'),
                'id' => $id,
                'data' => $formattedData
            ]);

        } catch (Exception $e) {
            error_log('Update province error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('An error occurred', 'wilayah-indonesia')
            ]);
        }
    }

    public function delete() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');

            $id = intval($_POST['id']);
            $province = $this->model->find($id);

            if (!$province) {
                wp_send_json_error([
                    'message' => __('Province not found', 'wilayah-indonesia')
                ]);
            }

            if (!$this->canDeleteProvince($province)) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
            }

            if (!$this->model->delete($id)) {
                wp_send_json_error([
                    'message' => __('Failed to delete province', 'wilayah-indonesia')
                ]);
            }

            // Invalidate cache after successful delete
            $this->invalidateCache($id);

            wp_send_json_success([
                'message' => __('Province deleted successfully', 'wilayah-indonesia')
            ]);

        } catch (Exception $e) {
            error_log('Delete province error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('An error occurred', 'wilayah-indonesia')
            ]);
        }
    }

    /**
     * Format data provinsi untuk panel kanan
     */
    private function formatProvinceData($province) {
        if (!$province) return null;

        return [
            'province' => [
                'id' => $province->id,
                'name' => esc_html($province->name),
                'regency_count' => $this->model->getRegencyCount($province->id),
                'created_by' => get_user_by('id', $province->created_by)->display_name,
                'created_at' => date('d M Y H:i', strtotime($province->created_at)),
                'updated_at' => date('d M Y H:i', strtotime($province->updated_at))
            ]
        ];
    }

    /**
     * Handle DataTables request
     */
    public function handleDataTableRequest() {
        try {
            check_ajax_referer('wilayah_nonce', 'nonce');
            
            if (!current_user_can('view_province_list')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'wilayah-indonesia')
                ]);
            }

            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';

            $result = $this->model->getDataTableData(
                $start,
                $length,
                $search,
                'name',
                'ASC'
            );

            // Format data untuk DataTables dengan action buttons
            $formatted_data = array_map(function($row) {
                return [
                    'id' => $row->id,
                    'name' => esc_html($row->name),
                    'regency_count' => (int)$row->regency_count,
                    'actions' => $this->generateActionButtons($row)
                ];
            }, $result['data']);

            wp_send_json([
                'draw' => $draw,
                'recordsTotal' => $this->model->getTotalCount(),
                'recordsFiltered' => $result['filtered_count'],
                'data' => $formatted_data
            ]);

        } catch (Exception $e) {
            error_log('DataTables error: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Handle direct URL access with province#id
     */
    public function handleDirectAccess() {
        try {
            // Get ID from URL hash via AJAX post data
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id) {
                wp_send_json_error(['message' => __('Invalid province ID', 'wilayah-indonesia')]);
            }

            // Check user permission
            if (!current_user_can('view_province_list')) {
                wp_send_json_error(['message' => __('Insufficient permissions', 'wilayah-indonesia')]);
            }

            // Load province data
            $province = $this->model->find($id);
            if (!$province) {
                wp_send_json_error(['message' => __('Province not found', 'wilayah-indonesia')]);
            }

            // Check specific view permission
            if (!$this->canViewProvince($province)) {
                wp_send_json_error(['message' => __('You do not have permission to view this province', 'wilayah-indonesia')]);
            }

            // Format and return data
            $formattedData = $this->formatProvinceData($province);
            wp_send_json_success(['data' => $formattedData]);

        } catch (Exception $e) {
            error_log('Direct access error: ' . $e->getMessage());
            wp_send_json_error(['message' => __('An error occurred while loading province', 'wilayah-indonesia')]);
        }
    }

    /**
     * Permission checks
     */
    private function canViewProvince($province) {
        return current_user_can('view_province') || 
               (current_user_can('view_own_province') && $province->created_by === get_current_user_id());
    }

    private function canEditProvince($province) {
        return current_user_can('edit_province') || 
               (current_user_can('edit_own_province') && $province->created_by === get_current_user_id());
    }

    private function canDeleteProvince($province) {
        return current_user_can('delete_province') || 
               (current_user_can('delete_own_province') && $province->created_by === get_current_user_id());
    }

    /**
     * Generate action buttons for DataTables
     */
    private function generateActionButtons($province) {
        $buttons = [];
        
        if (current_user_can('view_province')) {
            $buttons[] = sprintf(
                '<button type="button" class="button button-small view-province" data-id="%d" title="%s">
                    <span class="dashicons dashicons-visibility"></span>
                </button>',
                $province->id,
                esc_attr__('View Details', 'wilayah-indonesia')
            );
        }
        
        if ($this->canEditProvince($province)) {
            $buttons[] = sprintf(
                '<button type="button" class="button button-small edit-province" data-id="%d" title="%s">
                    <span class="dashicons dashicons-edit"></span>
                </button>',
                $province->id,
                esc_attr__('Edit', 'wilayah-indonesia')
            );
        }
        
        if ($this->canDeleteProvince($province)) {
            $buttons[] = sprintf(
                '<button type="button" class="button button-small delete-province" data-id="%d" title="%s">
                    <span class="dashicons dashicons-trash"></span>
                </button>',
                $province->id,
                esc_attr__('Delete', 'wilayah-indonesia')
            );
        }
        
        return implode(' ', $buttons);
    }
}