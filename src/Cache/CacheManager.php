<?php
/**
 * Cache Management Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Cache
 * @version     1.2.0 
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Cache/CacheManager.php
 */

namespace WilayahIndonesia\Cache;

class CacheManager {
    // Make constants public so they can be accessed by other classes
    public const CACHE_GROUP = 'wilayah_indonesia';
    public const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;
    
    // Cache keys - make public for external reference
    public const KEY_PROVINCE = 'province_';
    public const KEY_PROVINCE_LIST = 'province_list';
    public const KEY_REGENCY = 'regency_';
    public const KEY_REGENCY_LIST = 'regency_list_';

    /**
     * Get data from cache
     * 
     * @param string $key
     * @return mixed|null Returns null if key not found
     */
    public function get(string $key) {
        if (empty($key)) {
            return null;
        }
        $result = wp_cache_get($key, self::CACHE_GROUP);
        return $result === false ? null : $result;
    }

    /**
     * Set data in cache
     * 
     * @param string $key
     * @param mixed $data
     * @param int|null $expiry Optional expiry time
     * @return bool Success/failure
     */
    public function set(string $key, $data, ?int $expiry = null): bool {
        if (empty($key)) {
            return false;
        }
        return wp_cache_set(
            $key,
            $data,
            self::CACHE_GROUP,
            $expiry ?? self::CACHE_EXPIRY
        );
    }
    
    /**
     * Delete data from cache
     * 
     * @param string $key
     * @return bool Success/failure
     */
    public function delete(string $key): bool {
        if (empty($key)) {
            return false;
        }
        return wp_cache_delete($key, self::CACHE_GROUP);
    }

    /**
     * Get province data from cache
     * 
     * @param int $id
     * @return object|null
     */
    public function getProvince(int $id): ?object {
        $result = $this->get(self::KEY_PROVINCE . $id);
        return $result instanceof \stdClass ? $result : null;
    }

    /**
     * Set province data in cache
     * 
     * @param int $id
     * @param object $data
     * @return bool
     */
    public function setProvince(int $id, object $data): bool {
        return $this->set(self::KEY_PROVINCE . $id, $data);
    }

    /**
     * Invalidate province cache
     * Both single province and province list
     * 
     * @param int $id
     * @return void
     */
    public function invalidateProvinceCache(int $id): void {
        // Delete individual province cache
        $this->delete(self::KEY_PROVINCE . $id);
        // Delete province list cache
        $this->delete(self::KEY_PROVINCE_LIST);
        
        // Log cache invalidation if debug mode is on
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Cache invalidated for province ID: {$id}");
        }
    }

    /**
     * Get province list from cache
     * 
     * @return array|null
     */
    public function getProvinceList(): ?array {
        $result = $this->get(self::KEY_PROVINCE_LIST);
        return is_array($result) ? $result : null;
    }

    /**
     * Set province list in cache
     * 
     * @param array $data
     * @return bool
     */
    public function setProvinceList(array $data): bool {
        return $this->set(self::KEY_PROVINCE_LIST, $data);
    }

    /**
     * Get regency data from cache
     * 
     * @param int $id
     * @return object|null
     */
    public function getRegency(int $id): ?object {
        $result = $this->get(self::KEY_REGENCY . $id);
        return $result instanceof \stdClass ? $result : null;
    }

    /**
     * Set regency data in cache
     * 
     * @param int $id
     * @param object $data
     * @return bool
     */
    public function setRegency(int $id, object $data): bool {
        return $this->set(self::KEY_REGENCY . $id, $data);
    }

    /**
     * Get regency list for a province from cache
     * 
     * @param int $province_id
     * @return array|null
     */
    public function getRegencyList(int $province_id): ?array {
        $result = $this->get(self::KEY_REGENCY_LIST . $province_id);
        return is_array($result) ? $result : null;
    }

    /**
     * Set regency list for a province in cache
     * 
     * @param int $province_id
     * @param array $data
     * @return bool
     */
    public function setRegencyList(int $province_id, array $data): bool {
        return $this->set(self::KEY_REGENCY_LIST . $province_id, $data);
    }

    /**
     * Invalidate regency cache
     * Both single regency and regency list for province
     * 
     * @param int $id
     * @param int $province_id
     * @return void
     */
    public function invalidateRegencyCache(int $id, int $province_id): void {
        // Delete individual regency cache
        $this->delete(self::KEY_REGENCY . $id);
        // Delete regency list cache for the province
        $this->delete(self::KEY_REGENCY_LIST . $province_id);
        
        // Log cache invalidation if debug mode is on
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Cache invalidated for regency ID: {$id} in province ID: {$province_id}");
        }
    }
}
