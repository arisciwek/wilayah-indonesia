<?php
/**
 * File: PermissionModel.php
 * Path: /wilayah-indonesia/src/Models/Settings/PermissionModel.php
 * Description: Model untuk mengelola pengaturan hak akses dan role plugin Wilayah Indonesia
 * Version: 1.1.0
 * Last modified: 2024-11-25 06:20:00
 * 
 * Changelog:
 * v1.1.0 - 2024-11-25
 * - Added getRoleList method untuk mendapatkan daftar role yang tersedia
 * - Added getPermissionList method untuk mendapatkan daftar permission
 * - Fixed getPermissions untuk mengembalikan format yang sesuai
 * - Updated validation logic 
 * - Improved error handling
 * - Added consistent return types
 * - Added proper documentation
 * 
 * v1.0.0 - 2024-11-24
 * - Initial release
 */

namespace WilayahIndonesia\Models\Settings;

class PermissionModel {
    /**
     * Nama opsi di database WordPress
     */
    private $option_name = 'wilayah_indonesia_permissions';

    /**
     * Default hak akses per role
     */
    private $default_permissions = [
        'administrator' => [
            'manage_wilayah' => true,
            'view_wilayah' => true,
            'edit_wilayah' => true,
            'delete_wilayah' => true,
            'manage_settings' => true
        ],
        'editor' => [
            'view_wilayah' => true,
            'edit_wilayah' => true
        ],
        'author' => [
            'view_wilayah' => true
        ],
        'contributor' => [
            'view_wilayah' => true
        ],
        'subscriber' => [
            'view_wilayah' => true
        ]
    ];

    /**
     * Daftar kemampuan yang tersedia
     */
    private $capabilities = [
        'manage_wilayah' => 'Mengelola Wilayah',
        'view_wilayah' => 'Lihat Wilayah',
        'edit_wilayah' => 'Edit Wilayah',
        'delete_wilayah' => 'Hapus Wilayah',
        'manage_settings' => 'Kelola Pengaturan'
    ];

    /**
     * Get all permissions with roles and capabilities
     * Required by SettingsController
     */
    public function getPermissions(): array {
        $roles = $this->getRoleList();
        $permissions = $this->getSavedPermissions();
        $result = [];

        foreach ($roles as $role_id => $role_name) {
            $role_permissions = isset($permissions[$role_id]) 
                ? $permissions[$role_id] 
                : $this->getDefaultPermissions($role_id);

            $result[$role_id] = [
                'name' => $role_name,
                'permissions' => $role_permissions,
                'capabilities' => $this->getCapabilitiesForRole($role_id)
            ];
        }

        return $result;
    }

    /**
     * Get saved permissions from database
     */
    private function getSavedPermissions(): array {
        return wp_parse_args(
            get_option($this->option_name, []),
            $this->default_permissions
        );
    }

    /**
     * Get default permissions for a role
     */
    private function getDefaultPermissions(string $role): array {
        return isset($this->default_permissions[$role]) 
            ? $this->default_permissions[$role] 
            : ['view_wilayah' => true];
    }

    /**
     * Get all capabilities for a role
     */
    private function getCapabilitiesForRole(string $role): array {
        $wp_role = get_role($role);
        if (!$wp_role) {
            return [];
        }

        $caps = [];
        foreach ($this->capabilities as $cap_key => $cap_label) {
            $caps[$cap_key] = $wp_role->has_cap($cap_key);
        }

        return $caps;
    }

    /**
     * Get list of WordPress roles
     */
    public function getRoleList(): array {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        return $wp_roles->get_names();
    }

    /**
     * Get list of available capabilities with labels
     */
    public function getPermissionList(): array {
        return $this->capabilities;
    }

    /**
     * Get permissions for specific role
     */
    public function getRolePermissions(string $role): array {
        $permissions = $this->getSavedPermissions();
        return isset($permissions[$role]) ? $permissions[$role] : [];
    }

    /**
     * Get available roles
     */
    public function getRoles(): array {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        return $wp_roles->get_names();
    }

    /**
     * Get available capabilities
     */
    public function getCapabilities(): array {
        return $this->capabilities;
    }

    /**
     * Save permission settings
     */
    public function savePermissions(array $permissions): bool {
        try {
            error_log('Starting savePermissions with data: ' . print_r($permissions, true));
            
            if (empty($permissions)) {
                error_log('Empty permissions data');
                return false;
            }

            // Proses saving
            $result = update_option($this->option_name, $permissions);
            
            error_log('Save result: ' . ($result ? 'success' : 'failed'));
            
            return $result;
            
        } catch (\Exception $e) {
            error_log('Error in savePermissions: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save role settings
     */
    public function saveRoles(array $roles): bool {
        if (!is_array($roles)) {
            return false;
        }

        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($roles as $role_name => $capabilities) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($this->capabilities as $cap_key => $cap_label) {
                    if (isset($capabilities[$cap_key])) {
                        $role->add_cap($cap_key);
                    } else {
                        $role->remove_cap($cap_key);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Add capabilities to roles on plugin activation
     */
    public function addCapabilities(): void {
        $permissions = $this->getSavedPermissions();
        
        foreach ($permissions as $role_name => $caps) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap_key => $allowed) {
                    if ($allowed) {
                        $role->add_cap($cap_key);
                    }
                }
            }
        }
    }

    /**
     * Remove capabilities from roles on plugin deactivation
     */
    public function removeCapabilities(): void {
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($this->capabilities as $cap_key => $cap_label) {
                    $role->remove_cap($cap_key);
                }
            }
        }
    }

}
