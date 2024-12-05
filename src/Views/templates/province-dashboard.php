<?php
/**
 * Province Dashboard Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/province-dashboard.php
 *
 * Description: Main dashboard template untuk manajemen provinsi.
 *              Includes statistics overview, DataTable listing,
 *              right panel details, dan modal forms.
 *              Mengatur layout dan component integration.
 *
 * Changelog:
 * 1.0.1 - 2024-12-05
 * - Added edit form modal integration
 * - Updated form templates loading
 * - Improved modal management
 *
 * 1.0.0 - 2024-12-03
 * - Initial dashboard implementation
 * - Added statistics display
 * - Added province listing
 * - Added panel navigation
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <!-- Dashboard Section -->
    <div class="wi-province-dashboard">
        <div class="postbox">
            <div class="inside">
                <div class="main">
                    <h2>Statistik Wilayah</h2>
                    <div class="wi-stats-container">
                        <div class="wi-stat-box province-stats">
                            <h3>Total Provinsi</h3>
                            <p class="wi-stat-number"><span id="total-provinces">0</span></p>
                        </div>
                        <div class="wi-stat-box">
                            <h3>Total Kabupaten/Kota</h3>
                            <p class="wi-stat-number" id="total-regencies">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="wi-province-content-area">
        <div id="wi-province-main-container" class="wi-province-container">
            <!-- Left Panel -->
            <?php require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-left-panel.php'; ?>

            <!-- Right Panel -->
            <div id="wi-province-right-panel" class="wi-province-right-panel hidden">
                <?php require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-right-panel.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal Forms -->
    <?php
    require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/forms/create-province-form.php';
    require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/forms/edit-province-form.php';
    ?>
