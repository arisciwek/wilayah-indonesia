<?php
/**
 * File: src/Models/Settings/PermissionModel.php
 * Version: 1.0.0
 * Last Updated: 2024-11-24 11:00:00
 * Description: Handles plugin permission management and role capabilities
 */

namespace WilayahIndonesia\Models\Settings;

class PermissionModel {
    /**
     * Capability prefix for the plugin
     */
    const CAP_PREFIX = 'wilayah_indonesia_';

    /**
     * Default capabilities array
     */
    private $default_capabilities = array(
        'manage_settings' => array(
            'display_name' => 'Manage Plugin Settings',
            'roles' => array('administrator')
        ),
        'view_data' => array(
            'display_name' => 'View Regional Data',
            'roles' => array('administrator', 'editor', 'author')
        ),
        'edit_data' => array(
            'display_name' => 'Edit Regional Data',
            'roles' => array('administrator', 'editor')
        ),
        'delete_data' => array(
            'display_name' => 'Delete Regional Data',
            'roles' => array('administrator')
        ),
        'import_data' => array(
            'display_name' => 'Import Regional Data',
            'roles' => array('administrator')
        ),
        'export_data' => array(
            'display_name' => 'Export Regional Data',
            'roles' => array('administrator', 'editor')
        )
    );

    /**
     * Stored capabilities in database
     */
    private $capabilities;

    /**
     * Constructor
     */
    public function __construct() {
        $this->capabilities = get_option('wilayah_indonesia_capabilities', array());
        
        if (empty($this->capabilities)) {
            $this->initialize_capabilities();
        }
    }

    /**
     * Initialize default capabilities
     */
    public function initialize_capabilities() {
        $this->capabilities = $this->default_capabilities;
        $this->assign_capabilities_to_roles();
        $this->save_capabilities();
    }

    /**
     * Assign capabilities to WordPress roles
     */
    private function assign_capabilities_to_roles() {
        foreach ($this->capabilities as $capability => $config) {
            $full_cap = self::CAP_PREFIX . $capability;
            
            // First remove capability from all roles
            $roles = \wp_roles();
            foreach ($roles->role_objects as $role) {
                $role->remove_cap($full_cap);
            }

            // Then assign to specified roles
            if (!empty($config['roles'])) {
                foreach ($config['roles'] as $role_name) {
                    $role = get_role($role_name);
                    if ($role) {
                        $role->add_cap($full_cap);
                    }
                }
            }
        }
    }

    /**
     * Save capabilities to database
     */
    private function save_capabilities() {
        update_option('wilayah_indonesia_capabilities', $this->capabilities);
    }

    /**
     * Get all capabilities
     */
    public function get_capabilities() {
        return $this->capabilities;
    }

    /**
     * Get specific capability
     */
    public function get_capability($capability) {
        return isset($this->capabilities[$capability]) ? $this->capabilities[$capability] : null;
    }

    /**
     * Update capability configuration
     */
    public function update_capability($capability, $config) {
        if (!isset($this->capabilities[$capability])) {
            return false;
        }

        $this->capabilities[$capability] = wp_parse_args($config, $this->capabilities[$capability]);
        $this->assign_capabilities_to_roles();
        $this->save_capabilities();

        return true;
    }

    /**
     * Check if user has capability
     */
    public function current_user_can($capability) {
        return current_user_can(self::CAP_PREFIX . $capability);
    }

    /**
     * Check if role has capability
     */
    public function role_has_capability($role_name, $capability) {
        $role = get_role($role_name);
        if (!$role) {
            return false;
        }

        return $role->has_cap(self::CAP_PREFIX . $capability);
    }

    /**
     * Get available WordPress roles
     */
    public function get_available_roles() {
        $roles = array();
        foreach (wp_roles()->role_objects as $role_name => $role) {
            $roles[$role_name] = array(
                'name' => translate_user_role($role->name),
                'capabilities' => array()
            );

            foreach ($this->capabilities as $capability => $config) {
                $roles[$role_name]['capabilities'][$capability] = $this->role_has_capability($role_name, $capability);
            }
        }
        return $roles;
    }

    /**
     * Reset capabilities to default
     */
    public function reset_to_default() {
        $this->capabilities = $this->default_capabilities;
        $this->assign_capabilities_to_roles();
        $this->save_capabilities();
    }

    /**
     * Plugin activation handler
     */
    public function activate() {
        $this->initialize_capabilities();
    }

    /**
     * Plugin deactivation handler
     */
    public function deactivate() {
        // Remove capabilities from all roles
        $roles = wp_roles();
        foreach ($this->capabilities as $capability => $config) {
            $full_cap = self::CAP_PREFIX . $capability;
            foreach ($roles->role_objects as $role) {
                $role->remove_cap($full_cap);
            }
        }

        // Clean up options
        delete_option('wilayah_indonesia_capabilities');
    }

    /**
     * Get required capability for specific action
     */
    public static function get_required_capability($action) {
        $capability_map = array(
            'view' => 'view_data',
            'edit' => 'edit_data',
            'delete' => 'delete_data',
            'import' => 'import_data',
            'export' => 'export_data',
            'settings' => 'manage_settings'
        );

        return isset($capability_map[$action]) ? self::CAP_PREFIX . $capability_map[$action] : null;
    }

    /**
     * Validate user access
     */
    public static function validate_access($action) {
        $capability = self::get_required_capability($action);
        if (!$capability || !current_user_can($capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wilayah-indonesia'));
        }
        return true;
    }
}
