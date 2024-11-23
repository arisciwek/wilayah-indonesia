<?php
/**
 * File: class-activator.php
 * Path: /wilayah-indonesia/includes/class-activator.php
 * Description: Handles plugin activation and database table creation
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 *   - 2024-11-23: Added database tables creation
 *   - 2024-11-23: Fixed foreign key data type mismatch
 *   - 2024-11-23: Added custom prefix wi_ for tables
 */

class Wilayah_Indonesia_Activator {
    public static function activate() {
        self::createTables();
        self::addVersion();
    }

    private static function createTables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // SQL untuk tabel provinces dengan prefix wi_
        $sql_provinces = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wi_provinces` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `created_by` bigint(20) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`),
            KEY `created_by_index` (`created_by`)
        ) $charset_collate;";

        // SQL untuk tabel regencies dengan prefix wi_
        $sql_regencies = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wi_regencies` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `province_id` bigint(20) UNSIGNED NOT NULL,
            `name` varchar(100) NOT NULL,
            `type` enum('kabupaten','kota') NOT NULL,
            `created_by` bigint(20) NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `province_name` (`province_id`, `name`),
            KEY `created_by_index` (`created_by`),
            CONSTRAINT `{$wpdb->prefix}wi_regencies_ibfk_1` 
            FOREIGN KEY (`province_id`) 
            REFERENCES `{$wpdb->prefix}wi_provinces` (`id`) 
            ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Jalankan CREATE TABLE dalam urutan yang benar
        dbDelta($sql_provinces);
        dbDelta($sql_regencies);
    }

    private static function addVersion() {
        add_option('wilayah_indonesia_version', WILAYAH_INDONESIA_VERSION);
    }
}
