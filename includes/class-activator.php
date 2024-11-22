<?php
namespace WilayahIndonesia;

class Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create provinsi table
        $table_name = $wpdb->prefix . 'wilayah_provinsi';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            kode_provinsi varchar(10) NOT NULL,
            nama_provinsi varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY kode_provinsi (kode_provinsi)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set version
        update_option('wilayah_indonesia_version', WILAYAH_VERSION);
        update_option('wilayah_indonesia_db_version', '1.0.0');
    }
}
