<?php
/**
 * Cache Management Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Cache
 * @version     1.0.0 
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Cache/CacheManager.php
 */

namespace WilayahIndonesia\Cache;

class CacheManager {
    private const CACHE_GROUP = 'wilayah_indonesia';
    private const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;
    
    // Cache keys
    private const KEY_PROVINCE = 'province_';
    private const KEY_PROVINCE_LIST = 'province_list';

    public function getProvince(int $id): ?object {
        return wp_cache_get(self::KEY_PROVINCE . $id, self::CACHE_GROUP);
    }

    public function setProvince(int $id, object $data): bool {
        return wp_cache_set(
            self::KEY_PROVINCE . $id, 
            $data, 
            self::CACHE_GROUP, 
            self::CACHE_EXPIRY
        );
    }

    public function invalidateProvinceCache(int $id): void {
        wp_cache_delete(self::KEY_PROVINCE . $id, self::CACHE_GROUP);
        wp_cache_delete(self::KEY_PROVINCE_LIST, self::CACHE_GROUP);
    }

    public function getProvinceList(): ?array {
        return wp_cache_get(self::KEY_PROVINCE_LIST, self::CACHE_GROUP);
    }

    public function setProvinceList(array $data): bool {
        return wp_cache_set(
            self::KEY_PROVINCE_LIST,
            $data,
            self::CACHE_GROUP,
            self::CACHE_EXPIRY
        );
    }
}