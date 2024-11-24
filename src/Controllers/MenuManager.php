<?php
/**
 * File: MenuManager.php
 * Path: /wilayah-indonesia/src/Controllers/MenuManager.php
 * Description: Handles plugin menu registration and rendering
 * Last modified: 2024-11-23
 */

namespace WilayahIndonesia\Controllers;

class MenuManager {
    private $plugin_name;
    private $version;
    private $settings_controller;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_controller = new Settings\SettingsController();
    }

    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
    }

    public function registerMenus() {
        // Add main menu
        add_menu_page(
            __('Wilayah Indonesia', 'wilayah-indonesia'),  
            __('Wilayah Indonesia', 'wilayah-indonesia'),  
            'manage_options',                    
            'wilayah-indonesia',                
            [$this, 'renderMainPage'],          
            'dashicons-location',               
            30                                  
        );

        // Add settings submenu
        add_submenu_page(
            'wilayah-indonesia',                
            __('Pengaturan', 'wilayah-indonesia'),     
            __('Pengaturan', 'wilayah-indonesia'),     
            'manage_options',                    
            'wilayah-indonesia-settings',        
            [$this->settings_controller, 'renderPage']  
        );
    }

    public function renderMainPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'wilayah-indonesia'));
        }

        // Will be implemented later for main province listing
        echo '<div class="wrap">';
        echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
        echo '</div>';
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
