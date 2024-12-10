
<div class="wi-province-panel-header">
    <h2>Detail Provinsi: <span id="province-header-name"></span></h2>
    <button type="button" class="wi-province-close-panel">×</button>
</div>

<div class="wi-province-panel-content">
    <div class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-province-details nav-tab-active" data-tab="province-details">Data Provinsi</a>
        <a href="#" class="nav-tab" data-tab="regency-list">Kabupaten/Kota</a>
    </div>

    <?php
    // Include partial templates
    include WILAYAH_INDONESIA_PATH . 'src/Views/templates/province/partials/_province_details.php';
    include WILAYAH_INDONESIA_PATH . 'src/Views/templates/regency/partials/_regency_list.php';
    ?>
</div>
