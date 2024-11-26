<?php
/**
 * File: MenuManager.php
 * Path: /wilayah-indonesia/src/Controllers/MenuManager.php
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Admin/Controllers
 * @version     1.0.1
 * @author      arisciwek
 */

namespace WilayahIndonesia\Controllers;

use WilayahIndonesia\Controllers\Settings\SettingsController;

class MenuManager {
    private $plugin_name;
    private $version;
    private $settings_controller;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings_controller = new SettingsController();
    }

    public function init() {
        add_action('admin_menu', [$this, 'registerMenus']);
        $this->settings_controller->init();
    }

    public function registerMenus() {
        add_menu_page(
            __('Wilayah Indonesia', 'wilayah-indonesia'),
            __('Wilayah Indonesia', 'wilayah-indonesia'),
            'manage_options',
            'wilayah-indonesia',
            [$this, 'renderMainPage'],
            'dashicons-location',
            30
        );

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

        require_once WILAYAH_INDONESIA_PATH . 'src/Views/templates/province-dashboard.php';
    }
}
