<?php
/**
 * Provinces Table Schema
 *
 * @package     WilayahIndonesia
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Database/Tables/ProvincesDB.php
 *
 * Description: Mendefinisikan struktur tabel provinces.
 *              Table prefix yang digunakan adalah 'wi_'.
 *              Includes field untuk tracking creation dan updates.
 *
 * Fields:
 * - id             : Primary key
 * - code           : Kode provinsi (2 karakter)
 * - name           : Nama provinsi
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 *
 * Changelog:
 * 1.0.0 - 2024-01-21
 * - Initial version
 * - Added code field with unique constraint
 * - Added created_by tracking
 */

namespace WilayahIndonesia\Database\Tables;

defined('ABSPATH') || exit;

class ProvincesDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wi_provinces';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            code varchar(2) NOT NULL,
            name varchar(100) NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            UNIQUE KEY name (name),
            KEY created_by_index (created_by)
        ) $charset_collate;";
    }
}
