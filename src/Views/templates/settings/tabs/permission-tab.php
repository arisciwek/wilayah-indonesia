<?php
/**
 * File: permission-tab.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/permission-tab.php
 * Description: Template untuk tab permission di halaman settings
 * Version: 1.2.0
 * Last modified: 2024-11-25
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Ensure we have the required data
if (!isset($permissions) || !is_array($permissions)) {
    return;
}
?>

<div class="wrap">
    <div class="wilayah-permissions-wrapper">
        <form id="wilayah-permissions-form">
            <?php wp_nonce_field('wilayah_settings_nonce'); ?>
            
            <!-- Matrix Header -->
            <div class="permissions-matrix">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-role"><?php _e('Role', 'wilayah-indonesia'); ?></th>
                            <th class="column-permission"><?php _e('Melihat Daftar', 'wilayah-indonesia'); ?></th>
                            <th class="column-permission"><?php _e('Melihat Detail', 'wilayah-indonesia'); ?></th>
                            <th class="column-permission"><?php _e('Menambah', 'wilayah-indonesia'); ?></th>
                            <th class="column-permission"><?php _e('Mengubah', 'wilayah-indonesia'); ?></th>
                            <th class="column-permission"><?php _e('Menghapus', 'wilayah-indonesia'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($permissions as $role_id => $role_data): 
                            $is_admin = $role_id === 'administrator';
                        ?>
                            <tr>
                                <td class="column-role">
                                    <strong><?php echo esc_html($role_data['name']); ?></strong>
                                </td>
                                
                                <!-- View List Permission -->
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_id); ?>][view_province_list]" 
                                           <?php checked(true, $role_data['permissions']['view_province_list'] ?? false); ?>
                                           <?php disabled($is_admin, true); ?>>
                                </td>
                                
                                <!-- View Detail Permission -->
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_id); ?>][view_province]" 
                                           <?php checked(true, $role_data['permissions']['view_province'] ?? false); ?>
                                           <?php disabled($is_admin, true); ?>>
                                </td>
                                
                                <!-- Create Permission -->
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_id); ?>][create_province]" 
                                           <?php checked(true, $role_data['permissions']['create_province'] ?? false); ?>
                                           <?php disabled($is_admin, true); ?>>
                                </td>
                                
                                <!-- Edit Permission -->
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_id); ?>][edit_province]" 
                                           <?php checked(true, $role_data['permissions']['edit_province'] ?? false); ?>
                                           <?php disabled($is_admin, true); ?>>
                                </td>
                                
                                <!-- Delete Permission -->
                                <td class="column-permission">
                                    <input type="checkbox" 
                                           name="permissions[<?php echo esc_attr($role_id); ?>][delete_province]" 
                                           <?php checked(true, $role_data['permissions']['delete_province'] ?? false); ?>
                                           <?php disabled($is_admin, true); ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="submit">
                <button type="submit" class="button button-primary" id="save-permissions">
                    <?php _e('Simpan Perubahan', 'wilayah-indonesia'); ?>
                </button>
                <span class="spinner"></span>
            </p>
        </form>
    </div>
</div>

<style>
.wilayah-permissions-wrapper {
    margin: 20px 0;
}

.wilayah-permissions-wrapper .permissions-matrix {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.wilayah-permissions-wrapper table {
    border-collapse: collapse;
    width: 100%;
}

.wilayah-permissions-wrapper th {
    font-weight: 600;
    text-align: left;
    padding: 8px;
    border-bottom: 1px solid #ccd0d4;
}

.wilayah-permissions-wrapper td {
    padding: 8px;
    vertical-align: middle;
}

.wilayah-permissions-wrapper .column-role {
    width: 200px;
}

.wilayah-permissions-wrapper .column-permission {
    text-align: center;
}

.wilayah-permissions-wrapper .spinner {
    float: none;
    margin: 4px 10px;
}

/* Improve checkbox visibility */
.wilayah-permissions-wrapper input[type="checkbox"] {
    margin: 0;
}

.wilayah-permissions-wrapper input[type="checkbox"]:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Add hover effect */
.wilayah-permissions-wrapper tbody tr:hover {
    background-color: #f5f5f5;
}

/* Style submit button area */
.wilayah-permissions-wrapper .submit {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}
</style>

<script>
jQuery(document).ready(function($) {
    const $form = $('#wilayah-permissions-form');
    const $submitButton = $('#save-permissions');
    const $spinner = $form.find('.spinner');

    $form.on('submit', function(e) {
        e.preventDefault();
        
        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');

        const formData = {};
        $(this).find('input[type="checkbox"]').each(function() {
            const $checkbox = $(this);
            const name = $checkbox.attr('name');
            if (name) {
                const matches = name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                if (matches) {
                    const role = matches[1];
                    const cap = matches[2];
                    if (!formData[role]) {
                        formData[role] = {};
                    }
                    formData[role][cap] = $checkbox.is(':checked');
                }
            }
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_permissions',
                nonce: wilayahSettings.nonce,
                permissions: formData
            },
            success: function(response) {
                if (response.success) {
                    irToast.success(response.data || wilayahSettings.strings.saved);
                } else {
                    irToast.error(response.data || wilayahSettings.strings.saveError);
                }
            },
            error: function() {
                irToast.error(wilayahSettings.strings.saveError);
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
</script>
