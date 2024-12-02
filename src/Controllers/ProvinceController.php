<?php
/**
 * File: ProvinceController.php
 * Path: /wilayah-indonesia/src/Controllers/ProvinceController.php
 * Description: Controller for handling all province-related operations
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 */

namespace WilayahIndonesia\Controllers;

use WilayahIndonesia\Models\Province;
use WilayahIndonesia\Validators\ProvinceValidator;
use WilayahIndonesia\Cache\CacheManager;

class ProvinceController {
    private $model;
    private $validator;
    private $cache;
    
    public function __construct() {
        $this->model = new Province();
        $this->validator = new ProvinceValidator();
        $this->cache = new CacheManager();
    }
    
    /**
     * Display the main province listing page
     */
    public function index() {
        // Check permission
        if (!current_user_can('view_province_list')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Load main template
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/dashboard.php';
    }
    
    /**
     * Get province data for DataTables
     */
    public function loadData() {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        if (!current_user_can('view_province_list')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $provinces = $this->model->getAll();
        
        // Filter based on permissions
        if (!current_user_can('view_province')) {
            $provinces = array_filter($provinces, function($province) {
                return $province->created_by === get_current_user_id();
            });
        }
        
        // Format for DataTables
        $data = array_map(function($province) {
            return [
                'id' => $province->id,
                'name' => esc_html($province->name),
                'regency_count' => $this->model->getRegencyCount($province->id),
                'actions' => $this->generateActionButtons($province)
            ];
        }, $provinces);
        
        wp_send_json_success($data);
    }
    
    /**
     * Show province details
     */
    public function show($id) {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        if (!$this->validator->validateView($id)) {
            wp_send_json_error('Invalid province ID');
        }
        
        // Try cache first
        $province = $this->cache->getProvince($id);
        if (!$province) {
            $province = $this->model->find($id);
            if ($province) {
                $this->cache->setProvince($id, $province);
            }
        }
        
        if (!$province) {
            wp_send_json_error('Province not found');
        }
        
        // Check permission
        if (!$this->canViewProvince($province)) {
            wp_send_json_error('Insufficient permissions');
        }
        
        wp_send_json_success([
            'province' => $province,
            'regency_count' => $this->model->getRegencyCount($id)
        ]);
    }
    
    /**
     * Create new province
     */
    public function store() {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        if (!current_user_can('create_province')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $data = [
            'name' => sanitize_text_field($_POST['name']),
            'created_by' => get_current_user_id()
        ];
        
        $errors = $this->validator->validateCreate($data);
        if (!empty($errors)) {
            wp_send_json_error($errors);
        }
        
        $id = $this->model->create($data);
        if (!$id) {
            wp_send_json_error('Failed to create province');
        }
        
        // Clear cache
        $this->cache->invalidateProvinceCache($id);
        
        wp_send_json_success([
            'message' => 'Province created successfully',
            'id' => $id
        ]);
    }
    
    /**
     * Update province
     */
    public function update($id) {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        $province = $this->model->find($id);
        if (!$province) {
            wp_send_json_error('Province not found');
        }
        
        if (!$this->canEditProvince($province)) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $data = [
            'name' => sanitize_text_field($_POST['name'])
        ];
        
        $errors = $this->validator->validateUpdate($data, $id);
        if (!empty($errors)) {
            wp_send_json_error($errors);
        }
        
        if (!$this->model->update($id, $data)) {
            wp_send_json_error('Failed to update province');
        }
        
        // Clear cache
        $this->cache->invalidateProvinceCache($id);
        
        wp_send_json_success([
            'message' => 'Province updated successfully',
            'id' => $id
        ]);
    }
    
    /**
     * Delete province
     */
    public function delete($id) {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        $province = $this->model->find($id);
        if (!$province) {
            wp_send_json_error('Province not found');
        }
        
        if (!$this->canDeleteProvince($province)) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!$this->model->delete($id)) {
            wp_send_json_error('Failed to delete province');
        }
        
        // Clear cache
        $this->cache->invalidateProvinceCache($id);
        
        wp_send_json_success('Province deleted successfully');
    }
    
    /**
     * Generate action buttons HTML
     */
    private function generateActionButtons($province) {
        $buttons = [];
        
        if ($this->canViewProvince($province)) {
            $buttons[] = sprintf(
                '<button class="view-province" data-id="%d" title="View"><i class="fas fa-eye"></i></button>',
                $province->id
            );
        }
        
        if ($this->canEditProvince($province)) {
            $buttons[] = sprintf(
                '<button class="edit-province" data-id="%d" title="Edit"><i class="fas fa-edit"></i></button>',
                $province->id
            );
        }
        
        if ($this->canDeleteProvince($province)) {
            $buttons[] = sprintf(
                '<button class="delete-province" data-id="%d" title="Delete"><i class="fas fa-trash"></i></button>',
                $province->id
            );
        }
        
        return implode(' ', $buttons);
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
     * Handle DataTables AJAX request for province listing
     */
    public function handleDataTableRequest() {
        check_ajax_referer('wilayah_nonce', 'nonce');
        
        if (!current_user_can('view_province_list')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'wilayah-indonesia')
            ]);
        }

        // Get DataTables parameters
        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';
        
        // Get ordering
        $order_column = 'name'; // default
        $order_dir = 'ASC';
        
        if (isset($_POST['order'][0])) {
            $columns = ['name', 'regency_count'];
            $column_index = intval($_POST['order'][0]['column']);
            if (isset($columns[$column_index])) {
                $order_column = $columns[$column_index];
                $order_dir = strtoupper($_POST['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
            }
        }

        try {
            // Get data from model
            $result = $this->model->getDataTableData(
                $start,
                $length,
                $search,
                $order_column,
                $order_dir
            );

            // Format data for DataTables
            $data = [];
            foreach ($result['data'] as $province) {
                if ($this->canViewProvince($province)) {
                    $data[] = [
                        'id' => $province->id,
                        'name' => esc_html($province->name),
                        'regency_count' => $this->model->getRegencyCount($province->id),
                        'actions' => $this->generateActionButtons($province)
                    ];
                }
            }

            wp_send_json_success([
                'draw' => $draw,
                'recordsTotal' => $this->model->getTotalCount(),
                'recordsFiltered' => $result['filtered_count'],
                'data' => $data
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
    
}