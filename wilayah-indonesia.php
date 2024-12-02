<?php
/**
 * Plugin Name: Wilayah Indonesia
 * Plugin URI: 
 * Description: Plugin untuk mengelola data wilayah Indonesia
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

defined('ABSPATH') || exit;
define('WILAYAH_INDONESIA_VERSION', '1.0.0');

class WilayahIndonesia {
    private static $instance = null;
    private $loader;
    private $plugin_name;
    private $version;
    
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function defineConstants() {
        define('WILAYAH_INDONESIA_FILE', __FILE__);
        define('WILAYAH_INDONESIA_PATH', plugin_dir_path(__FILE__));
        define('WILAYAH_INDONESIA_URL', plugin_dir_url(__FILE__));
    }

    private function __construct() {
        $this->plugin_name = 'wilayah-indonesia'; 
        $this->version = WILAYAH_INDONESIA_VERSION;

        $this->defineConstants();
        $this->includeDependencies();
        $this->initHooks();
    }
    
    private function initHooks() {
        register_activation_hook(__FILE__, array('Wilayah_Indonesia_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('Wilayah_Indonesia_Deactivator', 'deactivate'));
        
        // Inisialisasi menu
        $menu_manager = new \WilayahIndonesia\Controllers\MenuManager($this->plugin_name, $this->version);
        $this->loader->add_action('init', $menu_manager, 'init');

        // Tambahkan ini untuk menangani dependencies
        $dependencies = new Wilayah_Indonesia_Dependencies($this->plugin_name, $this->version);
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_scripts');
    }

    private function includeDependencies() {
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-loader.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-activator.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-deactivator.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-dependencies.php'; // Tambahkan ini

        require_once WILAYAH_INDONESIA_PATH . 'src/Controllers/Settings/SettingsController.php';        
        require_once WILAYAH_INDONESIA_PATH . 'src/Controllers/MenuManager.php';

        require_once WILAYAH_INDONESIA_PATH . 'src/Models/Settings/SettingsModel.php';
        require_once WILAYAH_INDONESIA_PATH . 'src/Models/Settings/PermissionModel.php';

        new \WilayahIndonesia\Controllers\Settings\SettingsController();

        $this->loader = new Wilayah_Indonesia_Loader();
        
        // Add autoloader
        spl_autoload_register(function ($class) {
            $prefix = 'WilayahIndonesia\\';
            $base_dir = __DIR__ . '/src/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require $file;
            }
        });
    }

    public function run() {
        $this->loader->run();
    }
}

// Initialize plugin
function wilayah_indonesia() {
    return WilayahIndonesia::getInstance();
}

// Start the plugin
wilayah_indonesia()->run();
    