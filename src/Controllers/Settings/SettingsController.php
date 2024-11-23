<?php
/**
 * File: SettingsController.php
 * Path: /wilayah-indonesia/src/Controllers/Settings/SettingsController.php
 * Description: Controller utama untuk halaman settings
 * Last modified: 2024-11-23 
 */

class SettingsController {
    private $tab_controllers;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function add_settings_menu() {
        add_submenu_page(
            'wilayah-indonesia', // parent slug
            'Settings', // page title
            'Settings', // menu title
            'manage_options', // capability
            'wilayah-indonesia-settings', // menu slug
            array($this, 'render_settings_page') // callback function
        );
    }
    
    public function register_settings() {
        // Register settings group
        register_setting('wilayah_indonesia_settings', 'wilayah_indonesia_general_options');
        register_setting('wilayah_indonesia_settings', 'wilayah_indonesia_permissions');
        
        // Add settings sections
        add_settings_section(
            'wilayah_indonesia_general_section',
            'General Settings',
            array($this, 'render_general_section'),
            'wilayah_indonesia_settings'
        );
        
        add_settings_section(
            'wilayah_indonesia_permissions_section',
            'Permissions',
            array($this, 'render_permissions_section'),
            'wilayah_indonesia_settings'
        );
    }
    
    public function render_settings_page() {
        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/settings/settings-page.php';
    }
}