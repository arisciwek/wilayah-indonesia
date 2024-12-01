<?php
/**
 * File: permission-tab.php 
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/permission-tab.php
 */



/**
 * @var array $wilayah_permissions
 * @var array $all_roles
 * @var array $role_capabilities
 */
defined('ABSPATH') || exit;
?>

<div class="permissions-section">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('wilayah_permissions_nonce', 'security'); ?>
        <input type="hidden" name="action" value="update_wilayah_permissions">
        <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url(admin_url('admin.php?page=wilayah-indonesia-settings&tab=permission')); ?>">

        <div class="permissions-header">
            <p class="description">
                <?php _e('Atur hak akses untuk setiap role dalam mengelola data wilayah. Administrator secara otomatis memiliki akses penuh.', 'wilayah-indonesia'); ?>
            </p>
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
            <?php submit_button(__('Simpan Perubahan', 'wilayah-indonesia')); ?>
        </div>
    </form>
</div>
