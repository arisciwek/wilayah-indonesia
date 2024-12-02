<?php
/**
 * Province Right Panel Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: src/Views/templates/province-right-panel.php
 * 
 * Description: Template untuk panel kanan halaman provinsi.
 *              Menampilkan detail data provinsi dan kabupaten/kota terkait.
 *              Menyediakan interface untuk view, edit, dan delete provinsi
 *              dengan memperhatikan permission user.
 *              Includes konfirmasi modal untuk operasi delete.
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 15:30:00
 * - Initial release
 * - Added tabs for province details and regencies
 * - Added view/edit modes with permission checks
 * - Added delete confirmation modal
 * - Added responsive layout support
 * 
 * Dependencies:
 * - WordPress admin styles
 * - wilayah-toast.js for notifications
 * - province.css for styling
 * - province.js for interactions
 * - WordPress capability system
 */
defined('ABSPATH') || exit;
?>
<div class="wi-province-panel-header">
    <h2 id="panel-title">Detail Provinsi</h2>
    <button type="button" class="wi-province-close-panel" aria-label="Close panel">&times;</button>
</div>

<div class="wi-province-panel-content">
    <!-- Tab Navigation -->
    <div class="nav-tab-wrapper">
        <a href="#province-details" class="nav-tab nav-tab-active" data-tab="province-details">
            <?php _e('Data Provinsi', 'wilayah-indonesia'); ?>
        </a>
        <a href="#regency-list" class="nav-tab" data-tab="regency-list">
            <?php _e('Kabupaten/Kota', 'wilayah-indonesia'); ?>
        </a>
    </div>

    <!-- Province Details Tab -->
    <div id="province-details" class="tab-content active">
        <!-- View Mode -->
        <div id="view-mode" class="panel-mode">
            <div class="province-info">
                <table class="widefat">
                    <tbody>
                        <tr>
                            <th><?php _e('Nama Provinsi', 'wilayah-indonesia'); ?></th>
                            <td id="province-name"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Jumlah Kabupaten/Kota', 'wilayah-indonesia'); ?></th>
                            <td id="regency-count"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Dibuat Oleh', 'wilayah-indonesia'); ?></th>
                            <td id="created-by"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Tanggal Dibuat', 'wilayah-indonesia'); ?></th>
                            <td id="created-at"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Terakhir Diupdate', 'wilayah-indonesia'); ?></th>
                            <td id="updated-at"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="action-buttons">
                <?php if (current_user_can('edit_province') || current_user_can('edit_own_province')): ?>
                <button type="button" class="button edit-province">
                    <?php _e('Edit Provinsi', 'wilayah-indonesia'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (current_user_can('delete_province') || current_user_can('delete_own_province')): ?>
                <button type="button" class="button button-link-delete delete-province">
                    <?php _e('Hapus Provinsi', 'wilayah-indonesia'); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Mode -->
        <div id="edit-mode" class="panel-mode" style="display:none;">
            <form id="edit-province-form" method="post">
                <?php wp_nonce_field('wilayah_nonce'); ?>
                <input type="hidden" name="province_id" id="province-id">
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="edit-province-name">
                                    <?php _e('Nama Provinsi', 'wilayah-indonesia'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       name="name" 
                                       id="edit-province-name" 
                                       class="regular-text"
                                       required
                                       maxlength="100">
                                <p class="description">
                                    <?php _e('Masukkan nama provinsi (maksimal 100 karakter)', 'wilayah-indonesia'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="submit-wrapper">
                    <button type="submit" class="button button-primary">
                        <?php _e('Simpan Perubahan', 'wilayah-indonesia'); ?>
                    </button>
                    <button type="button" class="button cancel-edit">
                        <?php _e('Batal', 'wilayah-indonesia'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Regency List Tab -->
    <div id="regency-list" class="tab-content">
        <div class="tab-loading">
            <p><?php _e('Sedang Dikerjakan', 'wilayah-indonesia'); ?></p>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-province-modal" class="wi-modal" style="display:none;">
    <div class="wi-modal-content">
        <div class="wi-modal-header">
            <h3><?php _e('Konfirmasi Hapus', 'wilayah-indonesia'); ?></h3>
            <button type="button" class="wi-modal-close">&times;</button>
        </div>
        <div class="wi-modal-body">
            <p><?php _e('Anda yakin ingin menghapus provinsi ini?', 'wilayah-indonesia'); ?></p>
            <p class="warning">
                <?php _e('Perhatian: Semua kabupaten/kota yang terkait juga akan terhapus.', 'wilayah-indonesia'); ?>
            </p>
        </div>
        <div class="wi-modal-footer">
            <button type="button" class="button confirm-delete button-link-delete">
                <?php _e('Ya, Hapus', 'wilayah-indonesia'); ?>
            </button>
            <button type="button" class="button cancel-delete">
                <?php _e('Batal', 'wilayah-indonesia'); ?>
            </button>
            <span class="spinner"></span>
        </div>
    </div>
</div>

