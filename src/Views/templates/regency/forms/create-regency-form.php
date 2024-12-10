<?php
/**
 * Create Regency Form Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates/Regency/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/regency/forms/create-regency-form.php
 *
 * Description: Form modal untuk menambah kabupaten/kota baru.
 *              Includes input validation, error handling,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan komponen toast notification.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added form structure
 * - Added validation markup
 * - Added AJAX integration
 */
 defined('ABSPATH') || exit;
 ?>
 <div id="create-regency-modal" class="modal-overlay wi-province-modal" style="display: none;">
     <div class="modal-container">
         <div class="modal-header">
             <h3><?php _e('Tambah Kabupaten/Kota', 'wilayah-indonesia'); ?></h3>
             <button type="button" class="modal-close" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
         </div>

         <form id="create-regency-form" method="post">
             <?php wp_nonce_field('wilayah_nonce'); ?>
             <input type="hidden" name="province_id" id="province_id">

             <div class="modal-content">
                 <div class="wi-form-group">
                     <label for="regency-name" class="required-field">
                         <?php _e('Nama Kabupaten/Kota', 'wilayah-indonesia'); ?>
                     </label>
                     <input type="text"
                            id="regency-name"
                            name="name"
                            class="regular-text"
                            maxlength="100"
                            required>
                 </div>

                 <div class="wi-form-group">
                     <label for="regency-type" class="required-field">
                         <?php _e('Tipe', 'wilayah-indonesia'); ?>
                     </label>
                     <select id="regency-type" name="type" required>
                         <option value=""><?php _e('Pilih Tipe', 'wilayah-indonesia'); ?></option>
                         <option value="kabupaten"><?php _e('Kabupaten', 'wilayah-indonesia'); ?></option>
                         <option value="kota"><?php _e('Kota', 'wilayah-indonesia'); ?></option>
                     </select>
                 </div>
             </div>

             <div class="modal-footer">
                 <div class="wi-form-actions">
                     <button type="button" class="button cancel-create">
                         <?php _e('Batal', 'wilayah-indonesia'); ?>
                     </button>
                     <button type="submit" class="button button-primary">
                         <?php _e('Simpan', 'wilayah-indonesia'); ?>
                     </button>
                     <span class="spinner"></span>
                 </div>
             </div>
         </form>
     </div>
 </div>
