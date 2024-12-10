<?php
/**
 * Create Province Form Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/src/Views/templates/forms/create-province-form.php
 * 
 * Description: Template form untuk menambah provinsi baru.
 *              Menggunakan modal dialog untuk tampilan form.
 *              Includes validasi client-side dan permission check.
 *              Terintegrasi dengan AJAX submission dan toast notifications.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 18:30:00
 * - Initial release
 * - Added permission check
 * - Added nonce security
 * - Added form validation
 * - Added AJAX integration
 * 
 * Dependencies:
 * - WordPress admin styles
 * - province-toast.js for notifications
 * - province-form.css for styling
 * - province-form.js for handling
 */

defined('ABSPATH') || exit;
?>

<div id="create-province-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Tambah Provinsi</h3>
            <button type="button" class="modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal-content">
            <form id="create-province-form" method="post">
                <?php wp_nonce_field('wilayah_nonce'); ?>
                <input type="hidden" name="action" value="create_province">
                
                <div class="wi-form-group">
                    <label for="province-code" class="required-field">
                        <?php _e('Kode Provinsi', 'wilayah-indonesia'); ?>
                    </label>
                    <input type="text" 
                           id="province-code" 
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
                    <label for="province-name" class="required-field">
                        <?php _e('Nama Provinsi', 'wilayah-indonesia'); ?>
                    </label>
                    <input type="text" 
                           id="province-name" 
                           name="name" 
                           class="regular-text" 
                           maxlength="100" 
                           required>
                </div>
                
                <div class="submit-wrapper">
                    <button type="submit" class="button button-primary">
                        <?php _e('Simpan', 'wilayah-indonesia'); ?>
                    </button>
                    <button type="button" class="button cancel-create">
                        <?php _e('Batal', 'wilayah-indonesia'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>
</div>

