<?php
/**
 * Regency List Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates/Regency/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/regency/partials/_regency_list.php
 *
 * Description: Template untuk menampilkan daftar kabupaten/kota.
 *              Includes DataTable, loading states, empty states,
 *              dan action buttons dengan permission checks.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added loading states
 * - Added empty state messages
 * - Added proper DataTable structure
 */

defined('ABSPATH') || exit;
?>

<div id="regency-list" class="tab-content">
    <div class="wi-regency-header">
        <div class="wi-header-title">
            <h3><?php _e('Daftar Kabupaten/Kota', 'wilayah-indonesia'); ?></h3>
        </div>
        <div class="wi-header-actions">
            <?php if (current_user_can('add_regency')): ?>
                <button type="button" class="button button-primary" id="add-regency-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Tambah Kabupaten/Kota', 'wilayah-indonesia'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="wi-regency-content">
        <!-- Loading State -->
        <div class="wi-loading-state" style="display: none;">
            <span class="spinner is-active"></span>
            <p><?php _e('Memuat data...', 'wilayah-indonesia'); ?></p>
        </div>

        <!-- Empty State -->
        <div class="wi-empty-state" style="display: none;">
            <div class="empty-state-content">
                <span class="dashicons dashicons-location"></span>
                <h4><?php _e('Belum Ada Data', 'wilayah-indonesia'); ?></h4>
                <p>
                    <?php
                    if (current_user_can('add_regency')) {
                        _e('Belum ada kabupaten/kota yang ditambahkan. Klik tombol "Tambah Kabupaten/Kota" untuk menambahkan data baru.', 'wilayah-indonesia');
                    } else {
                        _e('Belum ada kabupaten/kota yang ditambahkan.', 'wilayah-indonesia');
                    }
                    ?>
                </p>
            </div>
        </div>

        <!-- Data Table -->
        <div class="wi-table-container">
            <table id="regency-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Kode', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Nama', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Tipe', 'wilayah-indonesia'); ?></th>
                        <th class="text-center no-sort">
                            <?php _e('Aksi', 'wilayah-indonesia'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Kode', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Nama', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Tipe', 'wilayah-indonesia'); ?></th>
                        <th><?php _e('Aksi', 'wilayah-indonesia'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Error State -->
        <div class="wi-error-state" style="display: none;">
            <div class="error-state-content">
                <span class="dashicons dashicons-warning"></span>
                <h4><?php _e('Gagal Memuat Data', 'wilayah-indonesia'); ?></h4>
                <p><?php _e('Terjadi kesalahan saat memuat data. Silakan coba lagi.', 'wilayah-indonesia'); ?></p>
                <button type="button" class="button reload-table">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Muat Ulang', 'wilayah-indonesia'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Export Buttons (Optional, can be enabled via settings) -->
    <?php if (apply_filters('wilayah_indonesia_enable_export', false)): ?>
        <div class="wi-export-actions">
            <button type="button" class="button export-excel">
                <span class="dashicons dashicons-media-spreadsheet"></span>
                <?php _e('Export Excel', 'wilayah-indonesia'); ?>
            </button>
            <button type="button" class="button export-pdf">
                <span class="dashicons dashicons-pdf"></span>
                <?php _e('Export PDF', 'wilayah-indonesia'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
// Include related modals
require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/regency/forms/create-regency-form.php';
require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/regency/forms/edit-regency-form.php';
?>
