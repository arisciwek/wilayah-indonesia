<?php
/**
 * File: province-right-panel.php
 * Path: /wilayah-indonesia/src/Views/templates/province-right-panel.php
 */
?>
<div class="wi-province-panel-header">
    <h2>Detail Provinsi</h2>
    <button type="button" class="wi-province-close-panel">×</button>
</div>

<div class="wi-province-panel-content">
    <div class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-province-details nav-tab-active" data-tab="province-details">Data Provinsi</a>
        <a href="#" class="nav-tab" data-tab="regency-list">Kabupaten/Kota</a>
    </div>

    <div id="province-details" class="tab-content active">
        <!-- Province details will be loaded here -->
    </div>

    <div id="regency-list" class="tab-content">
        <p>Sedang Dikerjakan</p>
    </div>
</div>
