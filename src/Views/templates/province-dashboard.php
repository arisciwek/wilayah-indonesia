<?php
/**
 * File: province-dashboard.php
 * Path: /wilayah-indonesia/src/Views/templates/province-dashboard.php
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
                        <div class="wi-stat-box">
                            <h3>Total Provinsi</h3>
                            <p class="wi-stat-number" id="total-provinces">0</p>
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
            
            <!-- Right Panel (Initially Hidden) -->
            <div id="wi-province-right-panel" class="wi-province-right-panel hidden">
                <?php require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-right-panel.php'; ?>
            </div>
        </div>
    </div>
</div>
