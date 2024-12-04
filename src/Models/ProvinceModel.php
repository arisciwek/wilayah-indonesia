<?php
/**
 * Province Model Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Models
 * @version     2.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/src/Models/ProvinceModel.php
 * 
 * Description: Model untuk mengelola data provinsi di database.
 *              Handles operasi CRUD dengan caching terintegrasi.
 *              Includes query optimization dan data formatting.
 *              Menyediakan metode untuk DataTables server-side.
 * 
 * Changelog:
 * 2.0.0 - 2024-12-03 15:00:00
 * - Refactor create/update untuk return complete data
 * - Added proper error handling dan validasi
 * - Improved cache integration
 * - Added method untuk DataTables server-side
 */

namespace WilayahIndonesia\Models;

class ProvinceModel {
    private $table;
    private $regency_table;
    
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'wi_provinces';
        $this->regency_table = $wpdb->prefix . 'wi_regencies';
    }

    public function create(array $data): ?int {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table,
            [
                'name' => $data['name'],
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s']
        );
        
        if ($result === false) {
            return null;
        }
        
        return (int) $wpdb->insert_id;
    }

    public function find(int $id): ?object {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT p.*, COUNT(r.id) as regency_count 
            FROM {$this->table} p 
            LEFT JOIN {$this->regency_table} r ON p.id = r.province_id 
            WHERE p.id = %d 
            GROUP BY p.id
        ", $id));
    }

    public function update(int $id, array $data): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table,
            array_merge($data, ['updated_at' => current_time('mysql')]),
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );
        
        return $result !== false;
    }

    public function delete(int $id): bool {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        ) !== false;
    }

    public function getRegencyCount(int $id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$this->regency_table} 
            WHERE province_id = %d
        ", $id));
    }

	public function getDataTableData(int $start, int $length, string $search, string $orderColumn, string $orderDir): array {
		global $wpdb;
		
		// Base query parts
		$select = "SELECT SQL_CALC_FOUND_ROWS p.*, COUNT(r.id) as regency_count";
		$from = " FROM {$this->table} p";
		$join = " LEFT JOIN {$this->regency_table} r ON p.id = r.province_id";
		$where = " WHERE 1=1";
		$group = " GROUP BY p.id";
		
		// Add search if provided
		if (!empty($search)) {
			$where .= $wpdb->prepare(
				" AND p.name LIKE %s",
				'%' . $wpdb->esc_like($search) . '%'
			);
		}
		
		// Validate order column
		$validColumns = ['name', 'regency_count'];
		if (!in_array($orderColumn, $validColumns)) {
			$orderColumn = 'name';
		}
		
		// Validate order direction
		$orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
		
		// Build order clause
		$order = " ORDER BY " . esc_sql($orderColumn) . " " . esc_sql($orderDir);
		
		// Add limit
		$limit = $wpdb->prepare(" LIMIT %d, %d", $start, $length);
		
		// Complete query
		$sql = $select . $from . $join . $where . $group . $order . $limit;
		
		// Get paginated results
		$results = $wpdb->get_results($sql);
		
		if ($results === null) {
			throw new Exception($wpdb->last_error);
		}
		
		// Get total filtered count
		$filtered = $wpdb->get_var("SELECT FOUND_ROWS()");
		
		// Get total count
		$total = $wpdb->get_var("SELECT COUNT(DISTINCT id) FROM {$this->table}");
		
		return [
			'data' => $results,
			'total' => (int) $total,
			'filtered' => (int) $filtered
		];
	}
    public function existsByName(string $name, ?int $excludeId = null): bool {
        global $wpdb;
        
        $sql = "SELECT EXISTS (SELECT 1 FROM {$this->table} WHERE name = %s";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != %d";
            $params[] = $excludeId;
        }
        
        $sql .= ") as result";
        
        return (bool) $wpdb->get_var($wpdb->prepare($sql, $params));
    }
}
