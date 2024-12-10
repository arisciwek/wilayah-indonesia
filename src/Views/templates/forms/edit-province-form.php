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
            <h3>Edit Provinsi</h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-content">
            <div id="edit-mode">
                <form id="edit-province-form" class="wi-form">
                    <input type="hidden" id="province-id" name="id" value="">
                    
                    <div class="wi-form-group">
                        <label for="edit-code" class="required-field">Kode Provinsi</label>
                        <input type="text" 
                               id="edit-code" 
                               name="code" 
                               class="small-text" 
                               maxlength="2"
                               pattern="\d{2}"
                               required>
                        <p class="description">
                            <?php _e('Masukkan 2 digit angka', 'wilayah-indonesia'); ?>
                        </p>
                    </div>

                    <div class="wi-form-group">
                        <label for="edit-name" class="required-field">Nama Provinsi</label>
                        <input type="text" 
                               id="edit-name" 
                               name="name" 
                               class="regular-text"
                               maxlength="100" 
                               required>
                    </div>

                    <div class="submit-wrapper">
                        <button type="submit" class="button button-primary">Update</button>
                        <button type="button" class="button cancel-edit">Batal</button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

