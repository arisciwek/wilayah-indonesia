<?php
/**
 * File: permission-tab.php
 * Path: /src/Views/templates/settings/tabs/permission-tab.php
 * Description: Template untuk tab permission di halaman settings
 * Version: 1.2.1
 * Last modified: 2024-11-27
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
        <form id="wilayah-permissions-form" method="post" action="javascript:void(0);">
            <?php wp_nonce_field('wilayah_settings_nonce', 'wilayah_nonce'); ?>
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
                                // Administrator selalu memiliki semua permissions
                                $has_permission = $is_admin ? true : ($role_data['permissions'][$permission] ?? false);
                            ?>
                                <tr>
                                    <td class="column-role">
                                        <strong><?php echo esc_html($role_data['name']); ?></strong>
                                    </td>
                                    
                                    <!-- View List Permission -->
                                    <td class="column-permission">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_id); ?>][view_province_list]" 
                                               <?php checked($is_admin || ($role_data['permissions']['view_province_list'] ?? false), true); ?>
                                               <?php disabled($is_admin, true); ?>>
                                    </td>
                                    
                                    <!-- View Detail Permission -->
                                    <td class="column-permission">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_id); ?>][view_province]" 
                                               <?php checked($is_admin || ($role_data['permissions']['view_province'] ?? false), true); ?>
                                               <?php disabled($is_admin, true); ?>>
                                    </td>
                                    
                                    <!-- Create Permission -->
                                    <td class="column-permission">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_id); ?>][create_province]" 
                                               <?php checked($is_admin || ($role_data['permissions']['create_province'] ?? false), true); ?>
                                               <?php disabled($is_admin, true); ?>>
                                    </td>
                                    
                                    <!-- Edit Permission -->
                                    <td class="column-permission">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_id); ?>][edit_province]" 
                                               <?php checked($is_admin || ($role_data['permissions']['edit_province'] ?? false), true); ?>
                                               <?php disabled($is_admin, true); ?>>
                                    </td>
                                    
                                    <!-- Delete Permission -->
                                    <td class="column-permission">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_id); ?>][delete_province]" 
                                               <?php checked($is_admin || ($role_data['permissions']['delete_province'] ?? false), true); ?>
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