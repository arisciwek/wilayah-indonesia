<?php
/**
 * Settings Page Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Templates/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/settings/settings-page.php
 *
 * Description: Main settings page template that includes tab navigation
 *              Handles tab switching and settings error notices
 *
 * Changelog:
 * 1.0.1 - 2024-12-08
 * - Added WIModal template integration
 * - Enhanced template structure for modals
 * - Improved documentation
 *
 * Changelog:
 * v1.0.0 - 2024-11-25
 * - Initial version
 * - Add main settings page layout
 * - Add tab navigation
 * - Add settings error notices support
 * - Add tab content rendering
 */

defined('ABSPATH') || exit;

// Get reference to SettingsController
$controller = isset($this) ? $this : null;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <nav class="nav-tab-wrapper">
        <?php
        // Use controller's tabs if available, otherwise fallback to passed tabs
        $tabs = isset($controller) ? $controller->tabs : (isset($tabs) ? $tabs : []);

        foreach ($tabs as $tab_id => $tab_name) {
            $active = $current_tab === $tab_id ? 'nav-tab-active' : '';
            $url = add_query_arg([
                'page' => 'wilayah-indonesia-settings',
                'tab' => $tab_id
            ], admin_url('admin.php'));

            printf(
                '<a href="%s" class="nav-tab %s">%s</a>',
                esc_url($url),
                esc_attr($active),
                esc_html($tab_name)
            );
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php
        if (isset($controller)) {
            $controller->renderTab($current_tab);
        } else {
            // Fallback render
            require WILAYAH_INDONESIA_PATH . "src/Views/templates/settings/tabs/{$current_tab}-tab.php";
        }
        ?>
    </div>
</div>

<?php
// Tambahkan ini di bagian bawah file
if (function_exists('wilayah_render_confirmation_modal')) {
    wilayah_render_confirmation_modal();
}
?>
