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

<div id="regency-list" class="tab-content">
    <div class="wi-regency-header">
        <div class="wi-header-actions">
            <?php if (current_user_can('add_regency')): ?>
                <button type="button" class="button button-primary" id="add-regency-btn">
                    <i class="dashicons dashicons-plus"></i>
                    <?php _e('Tambah Kabupaten/Kota', 'wilayah-indonesia'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="wi-regency-content">
        <div class="wi-table-container">
            <table id="regency-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Kode', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Nama', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Tipe', 'wilayah-indonesia'); ?></th>
                        <th class="no-sort text-center"><?php _e('Aksi', 'wilayah-indonesia'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
