<?php
/**
 * File: role-tab.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/role-tab.php
 * Description: Template untuk manajemen role dan capability
 * Version: 1.0.0
 * Last modified: 2024-11-25 06:30:00
 * 
 * Changelog:
 * v1.0.0 - 2024-11-25
 * - Initial version
 * - Add role management interface
 * - Add capability assignment per role
 * - Add AJAX-based role operations
 * - Add role deletion protection for core roles
 */


defined('ABSPATH') || exit;

$core_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
?>

<div class="wilayah-settings-tab roles-settings">
    <div class="role-management-section">
        <div class="heading">
            <h2><?php _e('Kelola Role', 'wilayah-indonesia'); ?></h2>
            <p class="description"><?php _e('Tambah, edit, atau hapus role kustom untuk plugin.', 'wilayah-indonesia'); ?></p>
        </div>
        
        <div class="add-edit-role-form">
            <form id="roleForm" method="post">
                <?php wp_nonce_field('wilayah_settings_nonce'); ?>
                <input type="hidden" name="role_id" id="roleId">
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="roleName"><?php _e('Nama Role', 'wilayah-indonesia'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="role_name" 
                                   id="roleName" 
                                   class="regular-text" 
                                   pattern="[a-z0-9_-]+" 
                                   required 
                                   title="<?php esc_attr_e('Gunakan huruf kecil, angka, underscore, atau strip', 'wilayah-indonesia'); ?>">
                            <p class="description">
                                <?php _e('Contoh: regional_manager', 'wilayah-indonesia'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="roleDisplayName"><?php _e('Nama Tampilan', 'wilayah-indonesia'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="role_display_name" 
                                   id="roleDisplayName" 
                                   class="regular-text" 
                                   required>
                            <p class="description">
                                <?php _e('Nama yang akan ditampilkan di interface', 'wilayah-indonesia'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="capability-selection">
                    <h3><?php _e('Capability', 'wilayah-indonesia'); ?></h3>
                    <div class="capabilities-grid">
                        <?php foreach ($capability_groups as $group_key => $group): ?>
                            <div class="capability-group">
                                <h4><?php echo esc_html($group['group_label']); ?></h4>
                                <?php foreach ($group['capabilities'] as $cap_name => $cap_data): ?>
                                    <div class="capability-item">
                                        <label>
                                            <input type="checkbox" 
                                                   name="capabilities[<?php echo esc_attr($cap_name); ?>]" 
                                                   value="1" 
                                                   data-deps="<?php echo esc_attr(json_encode($cap_data['dependencies'])); ?>">
                                            <?php echo esc_html($cap_data['label']); ?>
                                        </label>
                                        <?php if (!empty($cap_data['dependencies'])): ?>
                                            <span class="dependency-indicator dashicons dashicons-info-outline" 
                                                  title="<?php 
                                                      echo esc_attr(sprintf(
                                                          __('Membutuhkan: %s', 'wilayah-indonesia'),
                                                          implode(', ', $cap_data['dependencies'])
                                                      )); 
                                                  ?>">
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary" id="saveRole">
                        <?php _e('Simpan Role', 'wilayah-indonesia'); ?>
                    </button>
                    <button type="button" class="button" id="cancelEdit" style="display:none;">
                        <?php _e('Batal Edit', 'wilayah-indonesia'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>
            </form>
        </div>

        <div class="existing-roles-section">
            <h3><?php _e('Role yang Ada', 'wilayah-indonesia'); ?></h3>
            <table class="wp-list-table widefat fixed striped roles-table">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Nama Role', 'wilayah-indonesia'); ?></th>
                        <th scope="col"><?php _e('Nama Tampilan', 'wilayah-indonesia'); ?></th>
                        <th scope="col"><?php _e('Jumlah Capability', 'wilayah-indonesia'); ?></th>
                        <th scope="col" class="column-actions"><?php _e('Aksi', 'wilayah-indonesia'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role_name => $role_data): ?>
                        <tr data-role="<?php echo esc_attr($role_name); ?>">
                            <td><?php echo esc_html($role_name); ?></td>
                            <td><?php echo esc_html($role_data['name']); ?></td>
                            <td><?php echo count($role_data['capabilities']); ?></td>
                            <td class="column-actions">
                                <button type="button" 
                                        class="button action edit-role" 
                                        data-role="<?php echo esc_attr($role_name); ?>">
                                    <?php _e('Edit', 'wilayah-indonesia'); ?>
                                </button>
                                <?php if (!in_array($role_name, $core_roles)): ?>
                                    <button type="button" 
                                            class="button action delete-role" 
                                            data-role="<?php echo esc_attr($role_name); ?>">
                                        <?php _e('Hapus', 'wilayah-indonesia'); ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
