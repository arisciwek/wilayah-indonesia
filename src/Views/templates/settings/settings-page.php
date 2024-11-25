<?php
/**
 * File: settings-page.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/settings-page.php
 * Description: Main settings page template that includes tab navigation
 * Version: 1.0.0
 * Last modified: 2024-11-25 06:15:00
 * 
 * Changelog:
 * v1.0.0 - 2024-11-25
 * - Initial version
 * - Add main settings page layout
 * - Add tab navigation
 * - Add settings error notices support
 * - Add tab content rendering
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>

    <nav class="nav-tab-wrapper">
        <?php
        foreach ($this->tabs as $tab_id => $tab_name) {
            $active = $current_tab === $tab_id ? 'nav-tab-active' : '';
            $url = add_query_arg('tab', $tab_id);
            echo sprintf(
                '<a href="%s" class="nav-tab %s">%s</a>',
                esc_url($url),
                esc_attr($active),
                esc_html($tab_name)
            );
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php $this->renderTab($current_tab); ?>
    </div>
</div>
