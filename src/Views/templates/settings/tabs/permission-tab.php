<?php
/**
 * File: permission-tab.php 
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/permission-tab.php
 * Version: 2.0.0
 * Last modified: 2024-12-02
 */

defined('ABSPATH') || exit;
?>
<div class="permissions-section">
    <form method="post" id="wilayah-permissions-form">
        <?php wp_nonce_field('wilayah_permissions_nonce', 'security'); ?>
        
        <div class="permissions-header">
            <p class="description">
                <?php _e('Atur hak akses untuk setiap role dalam mengelola data wilayah. Administrator secara otomatis memiliki akses penuh.', 'wilayah-indonesia'); ?>
            </p>
            <div class="header-actions">
                <button type="button" name="reset_permissions" class="button" id="reset-permissions">
                    <?php _e('Reset ke Default', 'wilayah-indonesia'); ?>
                </button>
            </div>
        </div>

        <table class="widefat fixed permissions-matrix">
            <thead>
                <tr>
                    <th class="column-role"><?php _e('Role', 'wilayah-indonesia'); ?></th>
                    <?php foreach ($wilayah_permissions as $cap => $label): ?>
                        <th class="column-permission" title="<?php echo esc_attr($label); ?>">
                            <?php echo esc_html($label); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($all_roles as $role_name => $role_info):
                    if ($role_name === 'administrator') continue;
                ?>
                    <tr>
                        <td class="column-role">
                            <strong><?php echo translate_user_role($role_info['name']); ?></strong>
                        </td>
                        <?php foreach ($wilayah_permissions as $cap => $label): ?>
                            <td class="column-permission">
                                <input type="checkbox" 
                                       name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap); ?>]"
                                       value="1"
                                       <?php checked($role_capabilities[$role_name][$cap]); ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="submit-wrapper">
            <button type="submit" class="button button-primary" id="save-permissions">
                <?php _e('Simpan Perubahan', 'wilayah-indonesia'); ?>
            </button>
            <span class="spinner"></span>
        </div>
    </form>
</div>