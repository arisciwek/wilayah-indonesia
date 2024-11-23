<?php

// File: src/Views/templates/settings/settings-page.php
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">General</a>
        <a href="#permissions" class="nav-tab" data-tab="permissions">Permissions</a>
    </h2>
    
    <form method="post" action="options.php" id="wilayah-settings-form">
        <?php settings_fields('wilayah_indonesia_settings'); ?>
        
        <div class="tab-content" id="general-tab">
            <table class="form-table">
                <!-- General settings fields will go here -->
            </table>
        </div>
        
        <div class="tab-content hidden" id="permissions-tab">
            <!-- Permissions matrix will go here -->
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>