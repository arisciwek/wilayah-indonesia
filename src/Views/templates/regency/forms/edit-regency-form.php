<?PHP
/**
 * Edit Regency Form Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates/Regency/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/regency/forms/edit-regency-form.php
 *
 * Description: Form modal untuk mengedit data kabupaten/kota.
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

<div id="edit-regency-modal" class="modal-overlay wi-province-modal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><?php _e('Edit Kabupaten/Kota', 'wilayah-indonesia'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="edit-regency-form" method="post">
            <?php wp_nonce_field('wilayah_nonce'); ?>
            <input type="hidden" name="id" id="regency-id">

            <div class="modal-content">
                <div class="wi-form-group">
                    <label for="edit-regency-code" class="required-field">
                        <?php _e('Kode Kabupaten/Kota', 'wilayah-indonesia'); ?>
                    </label>
                    <input type="text"
                           id="edit-regency-code"
                           name="code"
                           class="small-text"
                           maxlength="4"
                           pattern="\d{4}"
                           required>
                    <p class="description">
                        <?php _e('Masukkan 4 digit angka', 'wilayah-indonesia'); ?>
                    </p>
                </div>
                              
                <div class="wi-form-group">
                    <label for="edit-regency-name" class="required-field">
                        <?php _e('Nama Kabupaten/Kota', 'wilayah-indonesia'); ?>
                    </label>
                    <input type="text"
                           id="edit-regency-name"
                           name="name"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

                <div class="wi-form-group">
                    <label for="edit-regency-type" class="required-field">
                        <?php _e('Tipe', 'wilayah-indonesia'); ?>
                    </label>
                    <select id="edit-regency-type" name="type" required>
                        <option value=""><?php _e('Pilih Tipe', 'wilayah-indonesia'); ?></option>
                        <option value="kabupaten"><?php _e('Kabupaten', 'wilayah-indonesia'); ?></option>
                        <option value="kota"><?php _e('Kota', 'wilayah-indonesia'); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <div class="wi-form-actions">
                    <button type="button" class="button cancel-edit">
                        <?php _e('Batal', 'wilayah-indonesia'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Perbarui', 'wilayah-indonesia'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
