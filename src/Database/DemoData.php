<?php
/**
 * Demo Data Generator
 *
 * @package     WilayahIndonesia
 * @subpackage  Database
 * @version     1.0.0
 * @author      arisciwek
 *
 * Description: Menyediakan data awal untuk testing.
 *              Includes data provinsi yang ada di Indonesia.
 *              Menggunakan transaction untuk data consistency.
 */

namespace WilayahIndonesia\Database;

defined('ABSPATH') || exit;

class DemoData {
    private static function debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("[WilayahIndonesia_DemoData] {$message}");
        }
    }

    public static function load() {
        global $wpdb;
        
        try {
            self::debug('Starting demo data insertion...');
            $wpdb->query('START TRANSACTION');

            self::clearTables();
            self::generateProvinces();
            self::generateRegencies();

            $wpdb->query('COMMIT');
            self::debug('Demo data inserted successfully');
            return true;

        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');
            self::debug('Demo data insertion failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function clearTables() {
        global $wpdb;
        // Delete in correct order (child tables first)
        $wpdb->query("DELETE FROM {$wpdb->prefix}wi_regencies");
        $wpdb->query("DELETE FROM {$wpdb->prefix}wi_provinces");
    }

    private static function generateProvinces() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wi_provinces';
        $current_user_id = get_current_user_id();

        $provinces = [
            ['code' => '11', 'name' => 'Aceh'],
            ['code' => '12', 'name' => 'Sumatera Utara'],
            ['code' => '13', 'name' => 'Sumatera Barat'],
            ['code' => '14', 'name' => 'Riau'],
            ['code' => '15', 'name' => 'Jambi'],
            ['code' => '16', 'name' => 'Sumatera Selatan'],
            ['code' => '17', 'name' => 'Bengkulu'],
            ['code' => '18', 'name' => 'Lampung'],
            ['code' => '19', 'name' => 'Kepulauan Bangka Belitung'],
            ['code' => '21', 'name' => 'Kepulauan Riau'],
            ['code' => '31', 'name' => 'DKI Jakarta'],
            ['code' => '32', 'name' => 'Jawa Barat'],
            ['code' => '33', 'name' => 'Jawa Tengah'],
            ['code' => '34', 'name' => 'DI Yogyakarta'],
            ['code' => '35', 'name' => 'Jawa Timur'],
            ['code' => '36', 'name' => 'Banten'],
            ['code' => '51', 'name' => 'Bali'],
            ['code' => '52', 'name' => 'Nusa Tenggara Barat'],
            ['code' => '53', 'name' => 'Nusa Tenggara Timur'],
            ['code' => '61', 'name' => 'Kalimantan Barat'],
            ['code' => '62', 'name' => 'Kalimantan Tengah'],
            ['code' => '63', 'name' => 'Kalimantan Selatan'],
            ['code' => '64', 'name' => 'Kalimantan Timur'],
            ['code' => '65', 'name' => 'Kalimantan Utara'],
            ['code' => '71', 'name' => 'Sulawesi Utara'],
            ['code' => '72', 'name' => 'Sulawesi Tengah'],
            ['code' => '73', 'name' => 'Sulawesi Selatan'],
            ['code' => '74', 'name' => 'Sulawesi Tenggara'],
            ['code' => '75', 'name' => 'Gorontalo'],
            ['code' => '76', 'name' => 'Sulawesi Barat'],
            ['code' => '81', 'name' => 'Maluku'],
            ['code' => '82', 'name' => 'Maluku Utara'],
            ['code' => '91', 'name' => 'Papua Barat'],
            ['code' => '92', 'name' => 'Papua'],
            ['code' => '93', 'name' => 'Papua Selatan'],
            ['code' => '94', 'name' => 'Papua Tengah'],
            ['code' => '95', 'name' => 'Papua Pegunungan'],
            ['code' => '96', 'name' => 'Papua Barat Daya']
        ];

        self::debug('Inserting ' . count($provinces) . ' provinces...');

        foreach ($provinces as $province) {
            $result = $wpdb->insert(
                $table_name,
                [
                    'code' => $province['code'],
                    'name' => $province['name'],
                    'created_by' => $current_user_id
                ],
                ['%s', '%s', '%d']
            );

            if ($result === false) {
                throw new \Exception("Failed to insert province: {$province['name']}");
            }
        }

        self::debug('Provinces inserted successfully');
    }

    private static function generateRegencies() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wi_regencies';
        $current_user_id = get_current_user_id();

        $regencies = [
            // Aceh (11)
            ['province_code' => '11', 'code' => '1101', 'name' => 'Kabupaten Aceh Selatan', 'type' => 'kabupaten'],
            ['province_code' => '11', 'code' => '1102', 'name' => 'Kabupaten Aceh Tenggara', 'type' => 'kabupaten'],
            ['province_code' => '11', 'code' => '1103', 'name' => 'Kabupaten Aceh Timur', 'type' => 'kabupaten'],
            ['province_code' => '11', 'code' => '1171', 'name' => 'Kota Banda Aceh', 'type' => 'kota'],
            ['province_code' => '11', 'code' => '1172', 'name' => 'Kota Sabang', 'type' => 'kota'],

            // Sumatera Utara (12)
            ['province_code' => '12', 'code' => '1201', 'name' => 'Kabupaten Tapanuli Tengah', 'type' => 'kabupaten'],
            ['province_code' => '12', 'code' => '1202', 'name' => 'Kabupaten Tapanuli Utara', 'type' => 'kabupaten'],
            ['province_code' => '12', 'code' => '1203', 'name' => 'Kabupaten Tapanuli Selatan', 'type' => 'kabupaten'],
            ['province_code' => '12', 'code' => '1271', 'name' => 'Kota Medan', 'type' => 'kota'],
            ['province_code' => '12', 'code' => '1272', 'name' => 'Kota Pematang Siantar', 'type' => 'kota'],

            // Sumatera Barat (13)
            ['province_code' => '13', 'code' => '1301', 'name' => 'Kabupaten Pesisir Selatan', 'type' => 'kabupaten'],
            ['province_code' => '13', 'code' => '1302', 'name' => 'Kabupaten Solok', 'type' => 'kabupaten'],
            ['province_code' => '13', 'code' => '1303', 'name' => 'Kabupaten Sijunjung', 'type' => 'kabupaten'],
            ['province_code' => '13', 'code' => '1371', 'name' => 'Kota Padang', 'type' => 'kota'],
            ['province_code' => '13', 'code' => '1372', 'name' => 'Kota Bukittinggi', 'type' => 'kota'],

            // DKI Jakarta (31)
			['province_code' => '31', 'code' => '3171', 'name' => 'Kota Jakarta Pusat', 'type' => 'kota'],
			['province_code' => '31', 'code' => '3172', 'name' => 'Kota Jakarta Utara', 'type' => 'kota'], 
			['province_code' => '31', 'code' => '3173', 'name' => 'Kota Jakarta Barat', 'type' => 'kota'],
			['province_code' => '31', 'code' => '3174', 'name' => 'Kota Jakarta Selatan', 'type' => 'kota'],
			['province_code' => '31', 'code' => '3175', 'name' => 'Kota Jakarta Timur', 'type' => 'kota'],

			// Jawa Barat (32)
			['province_code' => '32', 'code' => '3201', 'name' => 'Kabupaten Bogor', 'type' => 'kabupaten'],
			['province_code' => '32', 'code' => '3202', 'name' => 'Kabupaten Sukabumi', 'type' => 'kabupaten'],
			['province_code' => '32', 'code' => '3273', 'name' => 'Kota Bandung', 'type' => 'kota'],
			['province_code' => '32', 'code' => '3276', 'name' => 'Kota Depok', 'type' => 'kota'],
			['province_code' => '32', 'code' => '3277', 'name' => 'Kota Cimahi', 'type' => 'kota'],

			// Jawa Tengah (33)
			['province_code' => '33', 'code' => '3301', 'name' => 'Kabupaten Cilacap', 'type' => 'kabupaten'],
			['province_code' => '33', 'code' => '3302', 'name' => 'Kabupaten Banyumas', 'type' => 'kabupaten'],
			['province_code' => '33', 'code' => '3303', 'name' => 'Kabupaten Purbalingga', 'type' => 'kabupaten'],
			['province_code' => '33', 'code' => '3371', 'name' => 'Kota Magelang', 'type' => 'kota'],
			['province_code' => '33', 'code' => '3374', 'name' => 'Kota Semarang', 'type' => 'kota'],
			
			// Banten (36)
			['province_code' => '36', 'code' => '3601', 'name' => 'Kabupaten Pandeglang', 'type' => 'kabupaten'],
			['province_code' => '36', 'code' => '3602', 'name' => 'Kabupaten Lebak', 'type' => 'kabupaten'], 
			['province_code' => '36', 'code' => '3603', 'name' => 'Kabupaten Tangerang', 'type' => 'kabupaten'],
			['province_code' => '36', 'code' => '3604', 'name' => 'Kabupaten Serang', 'type' => 'kabupaten'],
			['province_code' => '36', 'code' => '3671', 'name' => 'Kota Tangerang', 'type' => 'kota'],
			['province_code' => '36', 'code' => '3672', 'name' => 'Kota Cilegon', 'type' => 'kota'],
			['province_code' => '36', 'code' => '3673', 'name' => 'Kota Serang', 'type' => 'kota'],
			['province_code' => '36', 'code' => '3674', 'name' => 'Kota Tangerang Selatan', 'type' => 'kota'],

			// Sulawesi Selatan (73)
			['province_code' => '73', 'code' => '7301', 'name' => 'Kabupaten Kepulauan Selayar', 'type' => 'kabupaten'],
			['province_code' => '73', 'code' => '7302', 'name' => 'Kabupaten Bulukumba', 'type' => 'kabupaten'],
			['province_code' => '73', 'code' => '7303', 'name' => 'Kabupaten Bantaeng', 'type' => 'kabupaten'],
			['province_code' => '73', 'code' => '7371', 'name' => 'Kota Makassar', 'type' => 'kota'],
			['province_code' => '73', 'code' => '7373', 'name' => 'Kota Palopo', 'type' => 'kota'],

			// Maluku (81) 
			['province_code' => '81', 'code' => '8101', 'name' => 'Kabupaten Maluku Tengah', 'type' => 'kabupaten'],
			['province_code' => '81', 'code' => '8102', 'name' => 'Kabupaten Maluku Tenggara', 'type' => 'kabupaten'],
			['province_code' => '81', 'code' => '8103', 'name' => 'Kabupaten Maluku Tenggara Barat', 'type' => 'kabupaten'],
			['province_code' => '81', 'code' => '8171', 'name' => 'Kota Ambon', 'type' => 'kota'],
			['province_code' => '81', 'code' => '8172', 'name' => 'Kota Tual', 'type' => 'kota'],

			// Papua (92)
			['province_code' => '92', 'code' => '9201', 'name' => 'Kabupaten Merauke', 'type' => 'kabupaten'],
			['province_code' => '92', 'code' => '9202', 'name' => 'Kabupaten Jayawijaya', 'type' => 'kabupaten'],
			['province_code' => '92', 'code' => '9203', 'name' => 'Kabupaten Jayapura', 'type' => 'kabupaten'],
			['province_code' => '92', 'code' => '9271', 'name' => 'Kota Jayapura', 'type' => 'kota']


        ];

        // Get all province IDs first
        $province_ids = [];
        $unique_province_codes = array_unique(array_column($regencies, 'province_code'));
        
        foreach ($unique_province_codes as $province_code) {
            $province_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wi_provinces WHERE code = %s",
                $province_code
            ));
            
            if (!$province_id) {
                throw new \Exception("Province not found for code: {$province_code}");
            }
            
            $province_ids[$province_code] = $province_id;
        }

        self::debug('Inserting ' . count($regencies) . ' regencies...');

        // Insert regencies
        foreach ($regencies as $regency) {
            if (!isset($province_ids[$regency['province_code']])) {
                throw new \Exception("Province ID not found for code: {$regency['province_code']}");
            }

            $result = $wpdb->insert(
                $table_name,
                [
                    'province_id' => $province_ids[$regency['province_code']],
                    'code' => $regency['code'],
                    'name' => $regency['name'],
                    'type' => $regency['type'],
                    'created_by' => $current_user_id
                ],
                ['%d', '%s', '%s', '%s', '%d']
            );

            if ($result === false) {
                throw new \Exception("Failed to insert regency: {$regency['name']}");
            }
        }

        self::debug('Regencies inserted successfully');
    }
}
