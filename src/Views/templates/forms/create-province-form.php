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

<div id="create-province-modal" class="wi-modal">
    <div class="wi-modal-content">
        <div class="wi-modal-header">
            <h3><?php _e('Tambah Provinsi', 'wilayah-indonesia'); ?></h3>
            <button type="button" class="wi-modal-close" aria-label="Close">&times;</button>
        </div>

        <div class="wi-modal-body">
            <form id="create-province-form" method="post">
                <?php 
                // Security check
                wp_nonce_field('wilayah_nonce'); 
                
                // Permission check
                if (!current_user_can('add_province')) {
                    wp_die(__('Anda tidak memiliki izin untuk menambah provinsi.', 'wilayah-indonesia'));
                }
                ?>

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
                    <p class="field-description">
                        <?php _e('Masukkan nama provinsi (maksimal 100 karakter)', 'wilayah-indonesia'); ?>
                    </p>
                </div>

                <div class="wi-modal-footer">
                    <div class="submit-wrapper">
                        <button type="submit" class="button button-primary">
                            <?php _e('Simpan', 'wilayah-indonesia'); ?>
                        </button>
                        <button type="button" class="button cancel-create">
                            <?php _e('Batal', 'wilayah-indonesia'); ?>
                        </button>
                        <span class="spinner"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
