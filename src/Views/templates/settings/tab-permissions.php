<?php
/**
 * Permission Management Tab Template
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Views/Settings
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Views/templates/settings/tab-permissions.php
 *
 * Description: Template untuk mengelola hak akses plugin Wilayah Indonesia
 *              Menampilkan matrix permission untuk setiap role
 *
 * Changelog:
 * v1.0.0 - 2024-01-07
 * - Initial version
 * - Add permission matrix
 * - Add role management
 * - Add tooltips for permissions
 */

if (!defined('ABSPATH')) {
    die;
}

/**
 * Get description for each capability
 * 
 * @param string $capability The capability to get description for
 * @return string The capability description
 */
function get_capability_description($capability) {
    $descriptions = array(
        // Province capabilities
        'view_province_list' => __('Memungkinkan melihat daftar semua provinsi dalam format tabel', 'wilayah-indonesia'),
        'view_province_detail' => __('Memungkinkan melihat detail informasi provinsi', 'wilayah-indonesia'),
        'view_own_province' => __('Memungkinkan melihat provinsi yang ditugaskan ke pengguna', 'wilayah-indonesia'),
        'add_province' => __('Memungkinkan menambahkan data provinsi baru', 'wilayah-indonesia'),
        'edit_all_provinces' => __('Memungkinkan mengedit semua data provinsi', 'wilayah-indonesia'),
        'edit_own_province' => __('Memungkinkan mengedit hanya provinsi yang ditugaskan', 'wilayah-indonesia'),
        'delete_province' => __('Memungkinkan menghapus data provinsi', 'wilayah-indonesia'),
        
        // Regency capabilities
        'view_regency_list' => __('Memungkinkan melihat daftar semua kabupaten/kota', 'wilayah-indonesia'),
        'view_regency_detail' => __('Memungkinkan melihat detail kabupaten/kota', 'wilayah-indonesia'),
        'view_own_regency' => __('Memungkinkan melihat kabupaten/kota yang ditugaskan', 'wilayah-indonesia'),
        'add_regency' => __('Memungkinkan menambahkan data kabupaten/kota baru', 'wilayah-indonesia'),
        'edit_all_regencies' => __('Memungkinkan mengedit semua data kabupaten/kota', 'wilayah-indonesia'),
        'edit_own_regency' => __('Memungkinkan mengedit hanya kabupaten/kota yang ditugaskan', 'wilayah-indonesia'),
        'delete_regency' => __('Memungkinkan menghapus data kabupaten/kota', 'wilayah-indonesia')
    );

    return isset($descriptions[$capability]) ? $descriptions[$capability] : '';
}

// Get permission model instance
$permission_model = new \WilayahIndonesia\Models\Settings\PermissionModel();

// Get all capabilities
$permission_labels = $permission_model->getAllCapabilities();

// Get all editable roles
$all_roles = get_editable_roles();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role_permissions') {
    if (!check_admin_referer('wilayah_indonesia_permissions')) {
        wp_die(__('Token keamanan tidak valid.', 'wilayah-indonesia'));
    }

    $updated = false;
    foreach ($all_roles as $role_name => $role_info) {
        // Skip administrator as they have full access
        if ($role_name === 'administrator') {
            continue;
        }

        $role = get_role($role_name);
        if ($role) {
            foreach ($permission_labels as $cap => $label) {
                $has_cap = isset($_POST['permissions'][$role_name][$cap]);
                if ($role->has_cap($cap) !== $has_cap) {
                    if ($has_cap) {
                        $role->add_cap($cap);
                    } else {
                        $role->remove_cap($cap);
                    }
                    $updated = true;
                }
            }
        }
    }

    if ($updated) {
        add_settings_error(
            'wilayah_indonesia_messages', 
            'permissions_updated', 
            __('Hak akses role berhasil diperbarui.', 'wilayah-indonesia'), 
            'success'
        );
    }
}
?>

<div class="permissions-section">
    <form method="post" action="<?php echo add_query_arg('tab', 'permissions'); ?>">
        <?php wp_nonce_field('wilayah_indonesia_permissions'); ?>
        <input type="hidden" name="action" value="update_role_permissions">

        <p class="description">
            <?php _e('Konfigurasikan hak akses untuk setiap role dalam mengelola data wilayah. Administrator secara otomatis memiliki akses penuh.', 'wilayah-indonesia'); ?>
        </p>

        <table class="widefat fixed permissions-matrix">
            <thead>
                <tr>
                    <th class="column-role"><?php _e('Role', 'wilayah-indonesia'); ?></th>
                    <?php foreach ($permission_labels as $cap => $label): ?>
                        <th class="column-permission">
                            <?php echo esc_html($label); ?>
                            <span class="dashicons dashicons-info tooltip-icon" 
                                  title="<?php echo esc_attr(get_capability_description($cap)); ?>">
                            </span>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($all_roles as $role_name => $role_info):
                    // Skip administrator
                    if ($role_name === 'administrator') continue;
                    
                    $role = get_role($role_name);
                ?>
                    <tr>
                        <td class="column-role">
                            <strong><?php echo translate_user_role($role_info['name']); ?></strong>
                        </td>
                        <?php foreach ($permission_labels as $cap => $label): ?>
                            <td class="column-permission">
                                <label class="screen-reader-text">
                                    <?php echo esc_html(sprintf(
                                        /* translators: 1: permission name, 2: role name */
                                        __('%1$s untuk role %2$s', 'wilayah-indonesia'),
                                        $label,
                                        $role_info['name']
                                    )); ?>
                                </label>
                                <input type="checkbox" 
                                       name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap); ?>]" 
                                       value="1"
                                       <?php checked($role->has_cap($cap)); ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(__('Simpan Perubahan', 'wilayah-indonesia')); ?>
    </form>

    <div class="role-descriptions">
        <h3><?php _e('Gambaran Role Default', 'wilayah-indonesia'); ?></h3>
        <dl>
            <dt><?php _e('Administrator', 'wilayah-indonesia'); ?></dt>
            <dd><?php _e('Memiliki akses penuh ke semua fitur dan pengaturan.', 'wilayah-indonesia'); ?></dd>
            
            <dt><?php _e('Editor', 'wilayah-indonesia'); ?></dt>
            <dd><?php _e('Dapat melihat dan mengedit data provinsi dan kabupaten/kota yang ditugaskan.', 'wilayah-indonesia'); ?></dd>
            
            <dt><?php _e('Author', 'wilayah-indonesia'); ?></dt>
            <dd><?php _e('Dapat melihat data provinsi dan kabupaten/kota yang ditugaskan.', 'wilayah-indonesia'); ?></dd>
            
            <dt><?php _e('Contributor', 'wilayah-indonesia'); ?></dt>
            <dd><?php _e('Hanya dapat melihat data wilayah yang ditugaskan.', 'wilayah-indonesia'); ?></dd>
        </dl>
    </div>
</div>

<style>
.permissions-matrix {
    margin-top: 20px;
    border-collapse: collapse;
}

.permissions-matrix th {
    text-align: left;
    padding: 10px;
    background: #f5f5f5;
}

.permissions-matrix .column-role {
    width: 200px;
}

.permissions-matrix .column-permission {
    text-align: center;
    width: 100px;
}

.permissions-matrix tbody td {
    padding: 10px;
    vertical-align: middle;
}

.tooltip-icon {
    color: #666;
    font-size: 16px;
    vertical-align: middle;
    margin-left: 5px;
    cursor: help;
}

.role-descriptions {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.role-descriptions dt {
    font-weight: bold;
    margin-top: 15px;
}

.role-descriptions dd {
    margin-left: 20px;
    color: #666;
}
</style>
