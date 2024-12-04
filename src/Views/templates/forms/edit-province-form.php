<?php
/**
 * Edit Province Form Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates/Forms
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/src/Views/templates/forms/edit-province-form.php
 * 
 * Description: Modal form template untuk edit provinsi.
 *              Includes validation, security checks,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan ProvinceForm component.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-05
 * - Initial implementation
 * - Added nonce security
 * - Added form validation
 * - Added permission checks
 * - Added AJAX integration
 */
?>
<?php
// File: edit-province-form.php
defined('ABSPATH') || exit;
?>



<div id="edit-province-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Tambah Provinsi</h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-content">
            <form id="edit-province-form" method="post">
                <?php wp_nonce_field('wilayah_nonce'); ?>
                <input type="hidden" id="province-id" name="id" value="">

                <div class="wi-form-group">
                    <label for="province-name-edit" class="required-field">
                        <?php _e('Nama Provinsi', 'wilayah-indonesia'); ?>
                    </label>
                    <input type="text" 
                           id="province-name-edit" 
                           name="name" 
                           class="regular-text" 
                           maxlength="100" 
                           required>
                    <p class="field-description">
                        <?php _e('Masukkan nama provinsi (maksimal 100 karakter)', 'wilayah-indonesia'); ?>
                    </p>
                </div>

                <div class="wi-modal-footer">
                    <div class="submit-wrapper">
                        <button type="submit" class="button button-primary">
                            <?php _e('Simpan', 'wilayah-indonesia'); ?>
                        </button>
                        <button type="button" class="button cancel-edit">
                            <?php _e('Batal', 'wilayah-indonesia'); ?>
                        </button>
                        <span class="spinner"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
