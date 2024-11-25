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

// Prevent direct access
defined('ABSPATH') || exit;

// Get default WordPress roles for protection
$core_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
?>

<div class="wilayah-settings-tab roles-settings">
    <!-- Role Management Section -->
    <div class="role-management-section">
        <h3><?php _e('Kelola Role', 'wilayah-indonesia'); ?></h3>
        
        <!-- Add/Edit Role Form -->
        <div class="add-edit-role-form">
            <form id="roleForm" method="post">
                <input type="hidden" name="role_id" id="roleId">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="roleName"><?php _e('Nama Role', 'wilayah-indonesia'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="role_name" id="roleName" class="regular-text" 
                                   required pattern="[a-z0-9_-]+" 
                                   title="<?php esc_attr_e('Gunakan huruf kecil, angka, underscore, atau strip', 'wilayah-indonesia'); ?>">
                            <p class="description">
                                <?php _e('Contoh: regional_manager (gunakan huruf kecil dan underscore)', 'wilayah-indonesia'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="roleDisplayName"><?php _e('Nama Tampilan', 'wilayah-indonesia'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="role_display_name" id="roleDisplayName" class="regular-text" required>
                            <p class="description">
                                <?php _e('Nama yang akan ditampilkan di interface', 'wilayah-indonesia'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <h3><?php _e('Capability', 'wilayah-indonesia'); ?></h3>
                <div class="capabilities-grid">
                    <?php foreach ($capabilities as $cap_name => $cap_label): ?>
                        <div class="capability-item">
                            <label>
                                <input type="checkbox" name="capabilities[<?php echo esc_attr($cap_name); ?>]" 
                                       value="1" class="capability-checkbox">
                                <?php echo esc_html($cap_label); ?>
                            </label>
                            <span class="tooltip" title="<?php echo esc_attr($this->getCapabilityDescription($cap_name)); ?>">
                                <span class="dashicons dashicons-info"></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary" id="saveRole">
                        <?php _e('Simpan Role', 'wilayah-indonesia'); ?>
                    </button>
                    <button type="button" class="button" id="cancelEdit" style="display:none;">
                        <?php _e('Batal Edit', 'wilayah-indonesia'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Existing Roles List -->
        <div class="existing-roles-section">
            <h3><?php _e('Role yang Ada', 'wilayah-indonesia'); ?></h3>
            <table class="wp-list-table widefat fixed striped roles-table">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Nama Role', 'wilayah-indonesia'); ?></th>
                        <th scope="col"><?php _e('Nama Tampilan', 'wilayah-indonesia'); ?></th>
                        <th scope="col"><?php _e('Jumlah Capability', 'wilayah-indonesia'); ?></th>
                        <th scope="col"><?php _e('Aksi', 'wilayah-indonesia'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role_name => $role_info): ?>
                        <tr data-role="<?php echo esc_attr($role_name); ?>">
                            <td><?php echo esc_html($role_name); ?></td>
                            <td><?php echo esc_html($role_info['name']); ?></td>
                            <td><?php echo count($role_info['capabilities']); ?></td>
                            <td class="actions">
                                <button type="button" class="button action edit-role" 
                                        data-role="<?php echo esc_attr($role_name); ?>">
                                    <?php _e('Edit', 'wilayah-indonesia'); ?>
                                </button>
                                <?php if (!in_array($role_name, $core_roles)): ?>
                                    <button type="button" class="button action delete-role" 
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

<script type="text/javascript">
jQuery(document).ready(function($) {
    const $form = $('#roleForm');
    const $roleId = $('#roleId');
    const $roleName = $('#roleName');
    const $roleDisplayName = $('#roleDisplayName');
    const $saveButton = $('#saveRole');
    const $cancelButton = $('#cancelEdit');
    
    // Handle form submission
    $form.on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEdit = !!$roleId.val();
        
        $saveButton.prop('disabled', true)
                  .html('<span class="spinner is-active"></span> Menyimpan...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_roles',
                nonce: wilayahSettings.nonce,
                form_data: Object.fromEntries(formData)
            },
            success: function(response) {
                if (response.success) {
                    irToast.success(response.data);
                    resetForm();
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    irToast.error(response.data);
                }
            },
            error: function() {
                irToast.error(wilayahSettings.strings.saveError);
            },
            complete: function() {
                $saveButton.prop('disabled', false)
                          .text(wilayahSettings.strings.saveRole);
            }
        });
    });

    // Handle role edit
    $('.edit-role').on('click', function() {
        const roleName = $(this).data('role');
        loadRoleData(roleName);
    });

    // Handle role delete