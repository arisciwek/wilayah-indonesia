<?php
/**
 * Regencies Table Schema
 *
 * @package     WilayahIndonesia
 * @subpackage  Database/Tables
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Database/Tables/RegenciesDB.php
 *
 * Description: Mendefinisikan struktur tabel regencies (kabupaten/kota).
 *              Table prefix yang digunakan adalah 'wi_'.
 *              Includes field untuk tracking creation dan updates.
 *              Menyediakan foreign key ke provinces table.
 *
 * Fields:
 * - id             : Primary key
 * - province_id    : Foreign key ke provinces
 * - code           : Kode kabupaten/kota (4 karakter)
 * - name           : Nama kabupaten/kota
 * - type           : Tipe wilayah (kabupaten/kota)
 * - created_by     : User ID pembuat
 * - created_at     : Timestamp pembuatan
 * - updated_at     : Timestamp update terakhir
 *
 * Foreign Keys:
 * - province_id    : REFERENCES wi_provinces(id) ON DELETE CASCADE
 *
 * Changelog:
 * 1.0.0 - 2024-01-21
 * - Initial version
 * - Added code field with unique constraint
 * - Added province_id foreign key
 */

namespace WilayahIndonesia\Database\Tables;

defined('ABSPATH') || exit;

class RegenciesDB {
    public static function get_schema() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wi_regencies';
        $charset_collate = $wpdb->get_charset_collate();

        return "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL auto_increment,
            province_id bigint(20) UNSIGNED NOT NULL,
            code varchar(4) NOT NULL,
            name varchar(100) NOT NULL,
            type enum('kabupaten','kota') NOT NULL,
            created_by bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            UNIQUE KEY province_name (province_id, name),
            KEY created_by_index (created_by),
            CONSTRAINT `{$wpdb->prefix}wi_regencies_ibfk_1` 
                FOREIGN KEY (province_id) 
                REFERENCES `{$wpdb->prefix}wi_provinces` (id) 
                ON DELETE CASCADE
        ) $charset_collate;";
    }
}
