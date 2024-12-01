<?php
/**
 * File: permission-tab.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/permission-tab.php
 * Description: Template yang ditingkatkan untuk matrix permission
 * Version: 2.0.0
 * Last modified: 2024-12-01
 */

defined('ABSPATH') || exit;
?>
<div class="permissions-section">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('wilayah_permissions_nonce', 'security'); ?>
        <input type="hidden" name="action" value="update_wilayah_permissions">

        <div class="permissions-header">
            <div class="header-content">
                <h2><?php _e('Manajemen Hak Akses', 'wilayah-indonesia'); ?></h2>
                <p class="description">
                    <?php _e('Atur hak akses untuk setiap role dalam mengelola data wilayah. Administrator secara otomatis memiliki akses penuh.', 'wilayah-indonesia'); ?>
                </p>
            </div>
            <div class="header-actions">
                <button type="submit" name="reset_permissions" value="1" class="button reset-button">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php _e('Reset ke Default', 'wilayah-indonesia'); ?>
                </button>
            </div>
        </div>

        <div class="matrix-container">
            <table class="widefat permissions-matrix">
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
                                <div class="role-description">
                                    <?php echo esc_html($role_info['capabilities_description'] ?? ''); ?>
                                </div>
                            </td>
                            <?php foreach ($wilayah_permissions as $cap => $label): ?>
                                <td class="column-permission">
                                    <label class="permission-toggle">
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap); ?>]"
                                               value="1"
                                               <?php checked($role_capabilities[$role_name][$cap]); ?>>
                                        <span class="screen-reader-text"><?php 
                                            echo esc_html(sprintf(
                                                __('%1$s untuk role %2$s', 'wilayah-indonesia'),
                                                $label,
                                                $role_info['name']
                                            )); 
                                        ?></span>
                                    </label>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="submit-wrapper">
            <div class="submit-content">
                <?php submit_button(__('Simpan Perubahan', 'wilayah-indonesia'), 'primary', 'submit', false); ?>
                <span class="spinner"></span>
            </div>
        </div>
    </form>
</div>
<?php
// Add tooltip styles
add_action('admin_footer', function() {
});

