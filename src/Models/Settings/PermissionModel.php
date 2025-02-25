<?php
/**
 * Permission Model Class
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Models/Settings
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/src/Models/Settings/PermissionModel.php
 *
 * Description: Model untuk mengelola hak akses plugin
 *
 * Changelog:
 * 1.1.0 - 2024-12-08
 * - Added view_own_province capability
 * - Updated default role capabilities for editor and author roles
 * - Added documentation for view_own_province permission
 *
 * 1.0.0 - 2024-11-28
 * - Initial release
 * - Basic permission management
 * - Default capabilities setup
 */
namespace WilayahIndonesia\Models\Settings;

class PermissionModel {
    private $default_capabilities = [
        // Province capabilities
        'view_province_list' => 'Lihat Daftar Provinsi',
        'view_province_detail' => 'Lihat Detail Provinsi',
        'view_own_province' => 'Lihat Provinsi Sendiri',
        'add_province' => 'Tambah Provinsi',
        'edit_all_provinces' => 'Edit Semua Provinsi',
        'edit_own_province' => 'Edit Provinsi Sendiri',
        'delete_province' => 'Hapus Provinsi',

        // Regency capabilities
        'view_regency_list' => 'Lihat Daftar Kabupaten/Kota',
        'view_regency_detail' => 'Lihat Detail Kabupaten/Kota',
        'view_own_regency' => 'Lihat Kabupaten/Kota Sendiri',
        'add_regency' => 'Tambah Kabupaten/Kota',
        'edit_all_regencies' => 'Edit Semua Kabupaten/Kota',
        'edit_own_regency' => 'Edit Kabupaten/Kota Sendiri',
        'delete_regency' => 'Hapus Kabupaten/Kota'
    ];

    private $default_role_caps = [
        'editor' => [
            'view_province_list',
            'view_province_detail',
            'view_own_province',
            'edit_own_province',
            'view_regency_list',
            'view_regency_detail',
            'view_own_regency',
            'edit_own_regency'
        ],
        'author' => [
            'view_province_list',
            'view_province_detail',
            'view_own_province',
            'view_regency_list',
            'view_regency_detail',
            'view_own_regency'
        ],
        'contributor' => [
            'view_own_province',
            'view_own_regency'
        ]
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
