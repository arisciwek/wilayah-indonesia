<?php
namespace WilayahIndonesia;

class Helper {
    private static $instance = null;

    private function __construct() {}

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Format kode provinsi dengan leading zero jika perlu
     */
    public function format_kode_provinsi($kode) {
        return sprintf('%02d', $kode);
    }

    /**
     * Validasi format kode provinsi
     */
    public function validate_kode_provinsi($kode) {
        return preg_match('/^\d{2}$/', $kode);
    }

    /**
     * Sanitize input provinsi
     */
    public function sanitize_provinsi_data($data) {
        $sanitized = array();
        $errors = array();

        // Kode Provinsi
        if (empty($data['kode_provinsi'])) {
            $errors[] = __('Kode provinsi wajib diisi', 'wilayah-indonesia');
        } elseif (!$this->validate_kode_provinsi($data['kode_provinsi'])) {
            $errors[] = __('Format kode provinsi tidak valid (harus 2 digit angka)', 'wilayah-indonesia');
        } else {
            $sanitized['kode_provinsi'] = $this->format_kode_provinsi($data['kode_provinsi']);
        }

        // Nama Provinsi
        if (empty($data['nama_provinsi'])) {
            $errors[] = __('Nama provinsi wajib diisi', 'wilayah-indonesia');
        } else {
            $sanitized['nama_provinsi'] = sanitize_text_field($data['nama_provinsi']);
        }

        return array(
            'data' => $sanitized,
            'errors' => $errors
        );
    }

    /**
     * Check if provinsi exists by kode
     */
    public function is_kode_exists($kode, $exclude_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        $query = $wpdb->prepare(
            "SELECT id FROM $table WHERE kode_provinsi = %s AND id != %d",
            $kode,
            $exclude_id
        );

        return $wpdb->get_var($query) !== null;
    }

    /**
     * Get provinsi by ID
     */
    public function get_provinsi($id) {
        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }

    /**
     * Get all provinsi
     */
    public function get_all_provinsi() {
        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        return $wpdb->get_results(
            "SELECT * FROM $table ORDER BY kode_provinsi ASC"
        );
    }
}
