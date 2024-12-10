<?php
/**
* Regency Validator Class
*
* @package     Wilayah_Indonesia
* @subpackage  Validators/Regency
* @version     1.0.0
* @author      arisciwek
*
* Path: src/Validators/Regency/RegencyValidator.php
*
* Description: Validator untuk operasi CRUD Kabupaten/Kota.
*              Memastikan semua input data valid sebelum diproses model.
*              Menyediakan validasi untuk create, update, dan delete.
*              Includes validasi permission dan ownership.
*
* Changelog:
* 1.0.0 - 2024-12-10
* - Initial release
* - Added create validation
* - Added update validation
* - Added delete validation
* - Added permission validation
*/

namespace WilayahIndonesia\Validators\Regency;

use WilayahIndonesia\Models\Regency\RegencyModel;
use WilayahIndonesia\Models\ProvinceModel;

class RegencyValidator {
    private $regency_model;
    private $province_model;

    public function __construct() {
        $this->regency_model = new RegencyModel();
        $this->province_model = new ProvinceModel();
    }

    public function validateCreate(array $data): array {
        $errors = [];

        // Permission check
        if (!current_user_can('add_regency')) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk menambah kabupaten/kota.', 'wilayah-indonesia');
            return $errors;
        }

        // Province exists check
        $province_id = intval($data['province_id'] ?? 0);
        if (!$province_id || !$this->province_model->find($province_id)) {
            $errors['province_id'] = __('Provinsi tidak valid.', 'wilayah-indonesia');
            return $errors;
        }

        // Name validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama kabupaten/kota wajib diisi.', 'wilayah-indonesia');
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama kabupaten/kota maksimal 100 karakter.', 'wilayah-indonesia');
        } elseif ($this->regency_model->existsByNameInProvince($name, $province_id)) {
            $errors['name'] = __('Nama kabupaten/kota sudah ada di provinsi ini.', 'wilayah-indonesia');
        }

        // Type validation
        $type = trim(sanitize_text_field($data['type'] ?? ''));
        if (empty($type)) {
            $errors['type'] = __('Tipe kabupaten/kota wajib diisi.', 'wilayah-indonesia');
        } elseif (!in_array($type, ['kabupaten', 'kota'])) {
            $errors['type'] = __('Tipe kabupaten/kota tidak valid.', 'wilayah-indonesia');
        }

        return $errors;
    }

    public function validateUpdate(array $data, int $id): array {
        $errors = [];

        // Check if regency exists
        $regency = $this->regency_model->find($id);
        if (!$regency) {
            $errors['id'] = __('Kabupaten/kota tidak ditemukan.', 'wilayah-indonesia');
            return $errors;
        }

        // Permission check
        if (!current_user_can('edit_all_regencies') &&
            (!current_user_can('edit_own_regency') || $regency->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk mengedit kabupaten/kota ini.', 'wilayah-indonesia');
            return $errors;
        }

        // Basic validation
        $name = trim(sanitize_text_field($data['name'] ?? ''));
        if (empty($name)) {
            $errors['name'] = __('Nama kabupaten/kota wajib diisi.', 'wilayah-indonesia');
        }

        // Length check
        if (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama kabupaten/kota maksimal 100 karakter.', 'wilayah-indonesia');
        }

        // Unique check excluding current ID
        if ($this->regency_model->existsByNameInProvince($name, $regency->province_id, $id)) {
            $errors['name'] = __('Nama kabupaten/kota sudah ada di provinsi ini.', 'wilayah-indonesia');
        }

        // Type validation if provided
        if (isset($data['type'])) {
            $type = trim(sanitize_text_field($data['type']));
            if (!in_array($type, ['kabupaten', 'kota'])) {
                $errors['type'] = __('Tipe kabupaten/kota tidak valid.', 'wilayah-indonesia');
            }
        }

        return $errors;
    }

    public function validateDelete(int $id): array {
        $errors = [];

        // Check if regency exists
        $regency = $this->regency_model->find($id);
        if (!$regency) {
            $errors['id'] = __('Kabupaten/kota tidak ditemukan.', 'wilayah-indonesia');
            return $errors;
        }

        // Permission check
        if (!current_user_can('delete_regency') &&
            (!current_user_can('delete_own_regency') || $regency->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk menghapus kabupaten/kota ini.', 'wilayah-indonesia');
        }

        return $errors;
    }

    /**
     * Validate view operation
     */
    public function validateView(int $id): array {
        $errors = [];

        // Check if regency exists
        $regency = $this->regency_model->find($id);
        if (!$regency) {
            $errors['id'] = __('Kabupaten/kota tidak ditemukan.', 'wilayah-indonesia');
            return $errors;
        }

        // Permission check
        if (!current_user_can('view_regency_detail') &&
            (!current_user_can('view_own_regency') || $regency->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk melihat detail kabupaten/kota ini.', 'wilayah-indonesia');
        }

        return $errors;
    }

    /**
     * Helper function to sanitize input data
     */
    public function sanitizeInput(array $data): array {
        $sanitized = [];

        if (isset($data['name'])) {
            $sanitized['name'] = trim(sanitize_text_field($data['name']));
        }

        if (isset($data['type'])) {
            $sanitized['type'] = trim(sanitize_text_field($data['type']));
        }

        if (isset($data['province_id'])) {
            $sanitized['province_id'] = intval($data['province_id']);
        }

        return $sanitized;
    }
}
