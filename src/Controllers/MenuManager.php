<?php
/**
 * File: MenuManager.php
 * Path: /wilayah-indonesia/src/Controllers/MenuManager.php
 * Description: Handles plugin menu registration and rendering
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
 */

namespace WilayahIndonesia\Controllers;

class MenuManager {
    private $plugin_name;
    private $version;
    private $capability;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->capability = 'manage_options'; // Temporary, will be replaced with proper capabilities
    }

    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
    }

    public function registerMenus() {
        // Add main menu
        add_menu_page(
            'Wilayah Indonesia',           // Page title
            'Wilayah Indonesia',           // Menu title
            $this->capability,             // Capability
            'wilayah-indonesia',           // Menu slug
            [$this, 'renderMainPage'],     // Callback
            'dashicons-location',          // Icon
            30                             // Position
        );

        // Add submenu for provinces (same as main menu)
        add_submenu_page(
            'wilayah-indonesia',           // Parent slug
            'Daftar Provinsi',             // Page title
            'Daftar Provinsi',             // Menu title
            $this->capability,             // Capability
            'wilayah-indonesia',           // Menu slug (same as parent)
            [$this, 'renderMainPage']      // Callback
        );

        // Add settings submenu
        add_submenu_page(
            'wilayah-indonesia',           // Parent slug
            'Pengaturan',                  // Page title
            'Pengaturan',                  // Menu title
            $this->capability,             // Capability
            'wilayah-indonesia-settings',  // Menu slug
            [$this, 'renderSettingsPage']  // Callback
        );
    }

    public function renderMainPage() {
        // Check user capabilities
        if (!current_user_can($this->capability)) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.'));
        }

        // Include necessary templates
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-dashboard.php';
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-left-panel.php';
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-right-panel.php';
    }

    public function renderSettingsPage() {
        // Check user capabilities
        if (!current_user_can($this->capability)) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.'));
        }

        // Include settings template
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/settings/settings-page.php';
    }

    public function isPluginPage() {
        global $pagenow;
        if ($pagenow !== 'admin.php') {
            return false;
        }

        $page = $_GET['page'] ?? '';
        return in_array($page, ['wilayah-indonesia', 'wilayah-indonesia-settings']);
    }
}