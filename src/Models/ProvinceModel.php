<?php
/**
 * Province Model Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Models
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: src/Models/ProvinceModel.php
 * 
 * Description: Model untuk mengelola data provinsi di Indonesia.
 *              Handles operasi CRUD untuk tabel provinces.
 *              Menyediakan interface untuk manipulasi data provinsi
 *              dengan memperhatikan permission dan ownership.
 *              Includes caching untuk optimasi performa.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 14:30:00
 * - Initial release
 * - Added CRUD operations with permission checks
 * - Added cache integration
 * - Added ownership validation
 * - Added regency count methods
 * 
 * Dependencies:
 * - WordPress wpdb for database operations
 * - WordPress capabilities system
 * - WilayahIndonesia\Models\Settings\PermissionModel for access control 
 * - wp_cache functions for data caching
 */

namespace WilayahIndonesia\Models;

use WilayahIndonesia\Models\Settings\PermissionModel;

class ProvinceModel {
    private $table;
    private $regency_table;
    private $cache_group = 'wilayah_indonesia';
    private $permission_model;
    
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wi_provinces';
        $this->regency_table = $wpdb->prefix . 'wi_regencies';
        $this->permission_model = new PermissionModel();
    }

    /**
     * Get all provinces with permission check
     */
    public function getAll(): array {
        global $wpdb;
        
        $cache_key = 'province_list';
        $provinces = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $provinces) {
            $provinces = $wpdb->get_results("
                SELECT p.*, COUNT(r.id) as regency_count 
                FROM {$this->table} p 
                LEFT JOIN {$this->regency_table} r ON p.id = r.province_id 
                GROUP BY p.id
                ORDER BY p.name ASC
            ");
            
            if ($provinces) {
                wp_cache_set($cache_key, $provinces, $this->cache_group);
            }
        }
        
        // Filter berdasarkan permission
        if (!current_user_can('view_province_detail')) {
            $provinces = array_filter($provinces, function($province) {
                return current_user_can('view_own_province') && 
                       $province->created_by === get_current_user_id();
            });
        }
        
        return $provinces ?: [];
    }

    /**
     * Get single province by ID
     */
    public function find(int $id): ?object {
        global $wpdb;
        
        $cache_key = "province_{$id}";
        $province = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $province) {
            $province = $wpdb->get_row($wpdb->prepare("
                SELECT p.*, COUNT(r.id) as regency_count 
                FROM {$this->table} p 
                LEFT JOIN {$this->regency_table} r ON p.id = r.province_id 
                WHERE p.id = %d 
                GROUP BY p.id
            ", $id));
            
            if ($province) {
                wp_cache_set($cache_key, $province, $this->cache_group);
            }
        }
        
        if ($province) {
            // Check permission
            if (!current_user_can('view_province_detail') && 
                (!current_user_can('view_own_province') || $province->created_by !== get_current_user_id())) {
                return null;
            }
        }
        
        return $province ?: null;
    }

    /**
     * Create new province
     */
    public function create(array $data): ?int {
        global $wpdb;
        
        if (!current_user_can('add_province')) {
            return null;
        }
        
        $result = $wpdb->insert(
            $this->table,
            [
                'name' => $data['name'],
                'created_by' => get_current_user_id()
            ],
            ['%s', '%d']
        );
        
        if ($result) {
            $id = $wpdb->insert_id;
            $this->invalidateCache();
            return $id;
        }
        
        return null;
    }

    /**
     * Update province
     */
    public function update(int $id, array $data): bool {
        global $wpdb;
        
        $province = $this->find($id);
        if (!$province) {
            return false;
        }
        
        // Check permission
        if (!current_user_can('edit_all_provinces') && 
            (!current_user_can('edit_own_province') || $province->created_by !== get_current_user_id())) {
            return false;
        }
        
        $result = $wpdb->update(
            $this->table,
            ['name' => $data['name']],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
        
        if ($result !== false) {
            $this->invalidateCache();
            return true;
        }
        
        return false;
    }

    /**
     * Delete province
     */
    public function delete(int $id): bool {
        global $wpdb;
        
        $province = $this->find($id);
        if (!$province) {
            return false;
        }
        
        // Check permission
        if (!current_user_can('delete_province') && 
            (!current_user_can('delete_own_province') || $province->created_by !== get_current_user_id())) {
            return false;
        }
        
        // Delete will cascade to regencies due to foreign key
        $result = $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
        
        if ($result) {
            $this->invalidateCache();
            return true;
        }
        
        return false;
    }

    /**
     * Get regency count for province
     */
    public function getRegencyCount(int $id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$this->regency_table} 
            WHERE province_id = %d
        ", $id));
    }

    /**
     * Check if province exists by name (for validation)
     */
    public function existsByName(string $name, ?int $exclude_id = null): bool {
        global $wpdb;
        
        $query = "SELECT EXISTS (SELECT 1 FROM {$this->table} WHERE name = %s";
        $params = [$name];
        
        if ($exclude_id) {
            $query .= " AND id != %d";
            $params[] = $exclude_id;
        }
        
        $query .= ") as result";
        
        return (bool) $wpdb->get_var($wpdb->prepare($query, $params));
    }

    /**
     * Clear all cache
     */
    private function invalidateCache(): void {
        wp_cache_delete('province_list', $this->cache_group);
        
        // Get all province IDs and invalidate individual caches
        global $wpdb;
        $ids = $wpdb->get_col("SELECT id FROM {$this->table}");
        foreach ($ids as $id) {
            wp_cache_delete("province_{$id}", $this->cache_group);
        }
    }

    /**
	 * Get paginated and filtered data for DataTables
	 */
	public function getDataTableData(
	    int $start,
	    int $length,
	    string $search,
	    string $order_column,
	    string $order_dir
	): array {
	    global $wpdb;
	    
	    // Base query parts
	    $sql_select = "SELECT p.*, COUNT(r.id) as regency_count";
	    $sql_from = " FROM {$this->table} p";
	    $sql_join = " LEFT JOIN {$this->regency_table} r ON p.id = r.province_id";
	    $sql_where = " WHERE 1=1";
	    $sql_group = " GROUP BY p.id";
	    
	    // Add search condition
	    if (!empty($search)) {
	        $sql_where .= $wpdb->prepare(
	            " AND (p.name LIKE %s)",
	            '%' . $wpdb->esc_like($search) . '%'
	        );
	    }

	    // Get total filtered count
	    $filtered_count = $wpdb->get_var(
	        "SELECT COUNT(DISTINCT p.id) " . 
	        $sql_from . 
	        $sql_join . 
	        $sql_where
	    );

	    // Add ordering and limit
	    $sql = $sql_select . 
	           $sql_from . 
	           $sql_join . 
	           $sql_where . 
	           $sql_group . 
	           " ORDER BY {$order_column} {$order_dir}" .
	           " LIMIT %d, %d";
	    
	    $results = $wpdb->get_results($wpdb->prepare(
	        $sql,
	        $start,
	        $length
	    ));

	    return [
	        'data' => $results,
	        'filtered_count' => $filtered_count
	    ];
	}

	/**
	 * Get total number of provinces
	 */
	public function getTotalCount(): int {
	    global $wpdb;
	    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
	}
}
