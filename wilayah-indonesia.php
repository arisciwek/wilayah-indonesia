<?php
/**
 * Plugin Name: Wilayah Indonesia
 * Plugin URI:
 * Description: Plugin untuk mengelola data wilayah Indonesia
 *   
 * @package     WilayahIndonesia
 * @subpackage  Views/Settings
 * @version     1.0.0
 * @author      arisciwek
 * 
 * License: GPL v2 or later
 */

defined('ABSPATH') || exit;
define('WILAYAH_INDONESIA_VERSION', '1.0.0');

// Di luar class WilayahIndonesia
if (!function_exists('wilayah_indonesia_plugin_url')) {
    function wilayah_indonesia_plugin_url() {
        return WILAYAH_INDONESIA_URL;
    }
}

if (!function_exists('wilayah_indonesia_is_active')) {
    function wilayah_indonesia_is_active() {
        return class_exists('WilayahIndonesia');
    }
}

class WilayahIndonesia {
    private static $instance = null;
    private $loader;
    private $plugin_name;
    private $version;
    private $province_controller;
    private $select_list_hooks; // Tambahkan ini

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
        
        // Register autoloader first
        spl_autoload_register(function ($class) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                //error_log("Autoloader attempting to load: " . $class);
            }

            $prefix = 'WilayahIndonesia\\';
            $base_dir = plugin_dir_path(__FILE__) . 'src/';
            
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }
            
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                //error_log("Looking for file: " . $file);
                //error_log("File exists: " . (file_exists($file) ? 'yes' : 'no'));
            }

            if (file_exists($file)) {
                require $file;
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    //error_log("Successfully loaded: " . $file);
                    return true;
                }
            }
        });
        
        $this->includeDependencies();
        $this->initHooks();
    }

    private function initHooks() {
        register_activation_hook(__FILE__, array('Wilayah_Indonesia_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('Wilayah_Indonesia_Deactivator', 'deactivate'));

        // Inisialisasi dependencies
        $dependencies = new Wilayah_Indonesia_Dependencies($this->plugin_name, $this->version);

        // Register hooks
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $dependencies, 'enqueue_scripts');

        // Inisialisasi menu
        $menu_manager = new \WilayahIndonesia\Controllers\MenuManager($this->plugin_name, $this->version);
        $this->loader->add_action('init', $menu_manager, 'init');

        // Inisialisasi SelectListHooks
        $this->select_list_hooks = new \WilayahIndonesia\Hooks\SelectListHooks();

        $this->initControllers(); 

      new \WilayahIndonesia\Controllers\Regency\RegencyController();
      new \WilayahIndonesia\Controllers\DashboardController();
        }

    private function initControllers() {
        // Inisialisasi ProvinceController
        $this->province_controller = new \WilayahIndonesia\Controllers\ProvinceController();

        // Register AJAX hooks
        add_action('wp_ajax_handle_province_datatable', [$this->province_controller, 'handleDataTableRequest']);
        add_action('wp_ajax_nopriv_handle_province_datatable', [$this->province_controller, 'handleDataTableRequest']);
        // Tambahkan ini
        add_action('wp_ajax_get_province', [$this->province_controller, 'show']);
        add_action('wp_ajax_nopriv_get_province', [$this->province_controller, 'show']);
    }

    private function includeDependencies() {
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-loader.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-activator.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-deactivator.php';
        require_once WILAYAH_INDONESIA_PATH . 'includes/class-dependencies.php'; // Tambahkan ini
        $this->loader = new Wilayah_Indonesia_Loader();
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
