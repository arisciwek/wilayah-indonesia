<?php
/**
* Province Validator Class
*
* @package     Wilayah_Indonesia
* @subpackage  Validators
* @version     1.0.0
* @author      arisciwek
*
* Path: src/Validators/ProvinceValidator.php
*
* Description: Validator untuk operasi CRUD Provinsi.
*              Memastikan semua input data valid sebelum diproses model.
*              Menyediakan validasi untuk create, update, dan delete.
*              Includes validasi permission dan ownership.
*
* Changelog:
* 1.0.1 - 2024-12-08
* - Added view_own_province validation in validateView method
* - Updated permission validation messages
* - Enhanced error handling for permission checks
*
* Changelog:
* 1.0.0 - 2024-12-02 15:00:00
* - Initial release
* - Added create validation
* - Added update validation
* - Added delete validation
* - Added permission validation
*
* Dependencies:
* - WilayahIndonesia\Models\ProvinceModel for data checks
* - WordPress sanitization functions
*/

namespace WilayahIndonesia\Validators;

use WilayahIndonesia\Models\ProvinceModel;

class ProvinceValidator {
   private $province_model;

   public function __construct() {
       $this->province_model = new ProvinceModel();
   }

   /**
    * Validate create operation
    */
   public function validateCreate(array $data): array {
       $errors = [];

       // Permission check
       if (!current_user_can('add_province')) {
           $errors['permission'] = __('Anda tidak memiliki izin untuk menambah provinsi.', 'wilayah-indonesia');
           return $errors;
       }

       // Basic validation
       $name = trim(sanitize_text_field($data['name'] ?? ''));
       if (empty($name)) {
           $errors['name'] = __('Nama provinsi wajib diisi.', 'wilayah-indonesia');
           return $errors;
       }

       // Length check
       if (mb_strlen($name) > 100) {
           $errors['name'] = __('Nama provinsi maksimal 100 karakter.', 'wilayah-indonesia');
           return $errors;
       }

       // Unique check
       if ($this->province_model->existsByName($name)) {
           $errors['name'] = __('Nama provinsi sudah ada.', 'wilayah-indonesia');
           return $errors;
       }

       return $errors;
   }

   /**
    * Validate update operation
    */
   public function validateUpdate(array $data, int $id): array {
       $errors = [];

       // Check if province exists
       $province = $this->province_model->find($id);
       if (!$province) {
           $errors['id'] = __('Provinsi tidak ditemukan.', 'wilayah-indonesia');
           return $errors;
       }

       // Permission check
       if (!current_user_can('edit_all_provinces') &&
           (!current_user_can('edit_own_province') || $province->created_by !== get_current_user_id())) {
           $errors['permission'] = __('Anda tidak memiliki izin untuk mengedit provinsi ini.', 'wilayah-indonesia');
           return $errors;
       }

       // Basic validation
       $name = trim(sanitize_text_field($data['name'] ?? ''));
       if (empty($name)) {
           $errors['name'] = __('Nama provinsi wajib diisi.', 'wilayah-indonesia');
           return $errors;
       }

       // Length check
       if (mb_strlen($name) > 100) {
           $errors['name'] = __('Nama provinsi maksimal 100 karakter.', 'wilayah-indonesia');
           return $errors;
       }

       // Unique check excluding current ID
       if ($this->province_model->existsByName($name, $id)) {
           $errors['name'] = __('Nama provinsi sudah ada.', 'wilayah-indonesia');
           return $errors;
       }

       return $errors;
   }

   /**
    * Validate delete operation
    */
   public function validateDelete(int $id): array {
       $errors = [];

       // Check if province exists
       $province = $this->province_model->find($id);
       if (!$province) {
           $errors['id'] = __('Provinsi tidak ditemukan.', 'wilayah-indonesia');
           return $errors;
       }

       // Permission check
       if (!current_user_can('delete_province') &&
           (!current_user_can('delete_own_province') || $province->created_by !== get_current_user_id())) {
           $errors['permission'] = __('Anda tidak memiliki izin untuk menghapus provinsi ini.', 'wilayah-indonesia');
           return $errors;
       }

       // Check for existing regencies
       if ($this->province_model->getRegencyCount($id) > 0) {
           $errors['dependencies'] = __('Provinsi tidak dapat dihapus karena masih memiliki kabupaten/kota.', 'wilayah-indonesia');
       }

       return $errors;
   }

   /**
    * Validate view operation
    */
    public function validateView(int $id): array {
        $errors = [];

        // Check if province exists
        $province = $this->province_model->find($id);
        if (!$province) {
            $errors['id'] = __('Provinsi tidak ditemukan.', 'wilayah-indonesia');
            return $errors;
        }

        // Permission check - update ini
        if (!current_user_can('view_province_detail') &&
            (!current_user_can('view_own_province') || $province->created_by !== get_current_user_id())) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk melihat detail provinsi ini.', 'wilayah-indonesia');
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

       return $sanitized;
   }
}
