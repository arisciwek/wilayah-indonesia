<?php
/**
 * File: permission-tab.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/permission-tab.php
 * Description: Template untuk pengaturan hak akses per role
 * Version: 1.0.0
 * Last modified: 2024-11-25 06:25:00
 * 
 * Changelog:
 * v1.0.0 - 2024-11-25
 * - Initial version
 * - Add role capabilities matrix
 * - Add AJAX-based save functionality
 * - Add responsive table layout
 * - Add role-based permission settings
 */

// Prevent direct access
defined('ABSPATH') || exit;
?>

<div class="wilayah-settings-tab permissions-settings">
    <div class="tablenav top">
        <div class="alignleft actions">
            <button type="button" class="button action save-permissions">
                <?php _e('Simpan Perubahan', 'wilayah-indonesia'); ?>
            </button>
        </div>
        <br class="clear">
    </div>

    <table class="wp-list-table widefat fixed striped permissions-table">
        <thead>
            <tr>
                <th scope="col" class="column-role"><?php _e('Role', 'wilayah-indonesia'); ?></th>
                <?php foreach ($capabilities as $cap_name => $cap_label): ?>
                    <th scope="col" class="column-capability">
                        <?php echo esc_html($cap_label); ?>
                        <span class="tooltip" title="<?php echo esc_attr($this->getCapabilityDescription($cap_name)); ?>">
                            <span class="dashicons dashicons-info"></span>
                        </span>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role_name => $role_display_name): ?>
                <tr>
                    <th scope="row" class="column-role">
                        <?php echo esc_html($role_display_name); ?>
                        <?php if ($role_name === 'administrator'): ?>
                            <br><small><?php _e('(Full Access)', 'wilayah-indonesia'); ?></small>
                        <?php endif; ?>
                    </th>
                    <?php foreach ($capabilities as $cap_name => $cap_label): 
                        $is_checked = isset($permissions[$role_name][$cap_name]) && $permissions[$role_name][$cap_name];
                        $is_disabled = $role_name === 'administrator';
                    ?>
                        <td class="column-capability">
                            <label class="screen-reader-text" 
                                   for="<?php echo esc_attr("cap_{$role_name}_{$cap_name}"); ?>">
                                <?php echo esc_html(sprintf(
                                    __('Grant %1$s capability to %2$s role', 'wilayah-indonesia'),
                                    $cap_label,
                                    $role_display_name
                                )); ?>
                            </label>
                            <input type="checkbox" 
                                   id="<?php echo esc_attr("cap_{$role_name}_{$cap_name}"); ?>"
                                   name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap_name); ?>]"
                                   value="1"
                                   <?php checked($is_checked); ?>
                                   <?php disabled($is_disabled); ?>>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="alignleft actions">
            <button type="button" class="button action save-permissions">
                <?php _e('Simpan Perubahan', 'wilayah-indonesia'); ?>
            </button>
        </div>
        <br class="clear">
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.save-permissions').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $('.permissions-table');
        
        // Disable button and show loading state
        $button.prop('disabled', true)
               .html('<span class="spinner is-active"></span> Menyimpan...');

        // Collect permissions data
        const permissions = {};
        $form.find('input[type="checkbox"]').each(function() {
            const $cb = $(this);
            const name = $cb.attr('name');
            const matches = name.match(/permissions\[([^\]]+)\]\[([^\]]+)\]/);
            
            if (matches) {
                const role = matches[1];
                const cap = matches[2];
                
                if (!permissions[role]) {
                    permissions[role] = {};
                }
                permissions[role][cap] = $cb.is(':checked');
            }
        });

        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_permissions',
                nonce: wilayahSettings.nonce,
                permissions: permissions
            },
            success: function(response) {
                if (response.success) {
                    irToast.success(response.data);
                } else {
                    irToast.error(response.data);
                }
            },
            error: function() {
                irToast.error(wilayahSettings.strings.saveError);
            },
            complete: function() {
                // Reset button state
                $button.prop('disabled', false)
                       .text(wilayahSettings.strings.saveChanges);
            }
        });
    });

    // Initialize tooltips
    $('.tooltip').tipTip({
        defaultPosition: 'top',
        fadeIn: 50,
        fadeOut: 50,
        delay: 200
    });
});
</script>
