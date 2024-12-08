<?php
/**
 * File: PermissionModel.php
 * Path: /wilayah-indonesia/src/Models/Settings/PermissionModel.php
 * Description: Model untuk mengelola hak akses plugin
 * Version: 1.0.0
 * Last modified: 2024-11-28 08:45:00
 */

namespace WilayahIndonesia\Models\Settings;

class PermissionModel {
    private $default_capabilities = [
        'view_province_list' => 'Lihat Daftar Provinsi',
        'view_province_detail' => 'Lihat Detail Provinsi',
        'view_own_province' => 'Lihat Provinsi Sendiri',  // Tambahkan ini
        'add_province' => 'Tambah Provinsi',
        'edit_all_provinces' => 'Edit Semua Provinsi',
        'edit_own_province' => 'Edit Provinsi Sendiri',
        'delete_province' => 'Hapus Provinsi'
    ];

    private $default_role_caps = [
        'editor' => ['view_province_list', 'view_province_detail', 'view_own_province', 'edit_own_province'],
        'author' => ['view_province_list', 'view_province_detail', 'view_own_province'],
        'contributor' => ['view_own_province']
    ];

    public function getAllCapabilities(): array {
        return $this->default_capabilities;
    }

    public function roleHasCapability(string $role_name, string $capability): bool {
        $role = get_role($role_name);
        if (!$role) {
            error_log("Role not found: $role_name");
            return false;
        }

        $has_cap = $role->has_cap($capability);
        return $has_cap;
    }

    public function updateRoleCapabilities(string $role_name, array $capabilities): bool {
        if ($role_name === 'administrator') {
            return false;
        }

        $role = get_role($role_name);
        if (!$role) {
            return false;
        }

        // Reset existing capabilities
        foreach (array_keys($this->default_capabilities) as $cap) {
            $role->remove_cap($cap);
        }

        // Add new capabilities
        foreach ($this->default_capabilities as $cap => $label) {
            if (isset($capabilities[$cap]) && $capabilities[$cap]) {
                $role->add_cap($cap);
            }
        }

        return true;
    }

    public function addCapabilities(): void {
        // Set administrator capabilities
        $admin = get_role('administrator');
        if ($admin) {
            foreach (array_keys($this->default_capabilities) as $cap) {
                $admin->add_cap($cap);
            }
        }

        // Set default role capabilities
        foreach ($this->default_role_caps as $role_name => $caps) {
            $role = get_role($role_name);
            if ($role) {
                // Reset capabilities first
                foreach (array_keys($this->default_capabilities) as $cap) {
                    $role->remove_cap($cap);
                }
                // Add default capabilities
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    public function resetToDefault(): bool {
    try {
        // Reset semua role ke default
        foreach (get_editable_roles() as $role_name => $role_info) {
            $role = get_role($role_name);
            if (!$role) continue;

            // Hapus semua capability yang ada
            foreach (array_keys($this->default_capabilities) as $cap) {
                $role->remove_cap($cap);
            }

            // Jika administrator, berikan semua capability
            if ($role_name === 'administrator') {
                foreach (array_keys($this->default_capabilities) as $cap) {
                    $role->add_cap($cap);
                }
                continue;
            }

            // Untuk role lain, berikan sesuai default jika ada
            if (isset($this->default_role_caps[$role_name])) {
                foreach ($this->default_role_caps[$role_name] as $cap) {
                    $role->add_cap($cap);
                }
            }
        }

        return true;

    } catch (\Exception $e) {
        error_log('Error resetting permissions: ' . $e->getMessage());
        return false;
    }
}
}
