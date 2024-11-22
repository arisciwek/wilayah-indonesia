<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin tables
global $wpdb;
$tables = array(
    $wpdb->prefix . 'wilayah_provinsi'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete plugin options
delete_option('wilayah_indonesia_version');
delete_option('wilayah_indonesia_db_version');
