<?php
/**
 * Province Left Panel Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/src/Views/templates/province-left-panel.php
 * 
 * Description: Template untuk panel kiri halaman provinsi.
 *              Menampilkan DataTables daftar provinsi.
 *              Includes tombol aksi dengan permission check.
 *              Terintegrasi dengan modal create province.
 * 
 * Dependencies:
 * - DataTables 1.13+
 * - province-datatable.js
 * - province-toast.js
 * - create-province-form.php
 */

defined('ABSPATH') || exit;
?>

<div id="wi-province-left-panel" class="wi-province-left-panel">
    <div class="wi-panel-header">
        <h2><?php _e('Daftar Provinsi', 'wilayah-indonesia'); ?></h2>
        
        <?php if (current_user_can('add_province')): ?>
            <button type="button" 
                    class="button button-primary" 
                    id="add-province-btn">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php _e('Tambah Provinsi', 'wilayah-indonesia'); ?>
            </button>
        <?php endif; ?>
    </div>
    
    <div class="wi-panel-content">
        <!-- DataTables Container -->
        <table id="provinces-table" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th><?php _e('Nama Provinsi', 'wilayah-indonesia'); ?></th>
                    <th class="text-center"><?php _e('Jumlah Kab/Kota', 'wilayah-indonesia'); ?></th>
                    <th class="text-center no-sort"><?php _e('Aksi', 'wilayah-indonesia'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be populated by DataTables -->
            </tbody>
        </table>
    </div>
</div>

<?php 
// Include create form modal if user has permission
if (current_user_can('add_province')) {
    require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/forms/create-province-form.php';
}
?>
