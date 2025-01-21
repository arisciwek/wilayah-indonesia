<?php
/**
 * Cache Management Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Cache
 * @version     1.1.0 
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
    private const KEY_REGENCY = 'regency_';
    private const KEY_REGENCY_LIST = 'regency_list_';

    public function get(string $key) {
        return wp_cache_get($key, self::CACHE_GROUP);
    }

    public function set(string $key, $data, int $expiry = null): bool {
        return wp_cache_set(
            $key,
            $data,
            self::CACHE_GROUP,
            $expiry ?? self::CACHE_EXPIRY
        );
    }

    public function delete(string $key): bool {
        return wp_cache_delete($key, self::CACHE_GROUP);
    }

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

    public function getRegency(int $id): ?object {
        return wp_cache_get(self::KEY_REGENCY . $id, self::CACHE_GROUP);
    }

    public function setRegency(int $id, object $data): bool {
        return wp_cache_set(
            self::KEY_REGENCY . $id,
            $data,
            self::CACHE_GROUP,
            self::CACHE_EXPIRY
        );
    }

    public function getRegencyList(int $province_id): ?array {
        return wp_cache_get(self::KEY_REGENCY_LIST . $province_id, self::CACHE_GROUP);
    }

    public function setRegencyList(int $province_id, array $data): bool {
        return wp_cache_set(
            self::KEY_REGENCY_LIST . $province_id,
            $data,
            self::CACHE_GROUP,
            self::CACHE_EXPIRY
        );
    }

    public function invalidateRegencyCache(int $id, int $province_id): void {
        wp_cache_delete(self::KEY_REGENCY . $id, self::CACHE_GROUP);
        wp_cache_delete(self::KEY_REGENCY_LIST . $province_id, self::CACHE_GROUP);
    }
}
