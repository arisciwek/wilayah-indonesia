class-deactivator.php

<?php
/**
 * File: class-deactivator.php
 * Path: /wilayah-indonesia/includes/class-deactivator.php
 * Description: Menangani proses deaktivasi plugin
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 *   - 2024-11-23: Added cleanup methods
 */

class Wilayah_Indonesia_Deactivator {
    public static function deactivate() {
        // Untuk saat ini kita tidak menghapus tabel saat deaktivasi
        // Hanya membersihkan cache jika ada
        wp_cache_delete('wilayah_indonesia_province_list', 'wilayah_indonesia');
        wp_cache_delete('wilayah_indonesia_regency_list', 'wilayah_indonesia');
    }
}