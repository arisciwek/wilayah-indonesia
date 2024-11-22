<?php
/**
 * Plugin Name: Wilayah Indonesia
 * Plugin URI: https://github.com/yourusername/wilayah-indonesia
 * Description: Plugin untuk manajemen data wilayah Indonesia
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: wilayah-indonesia
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WILAYAH_VERSION', '1.0.0');
define('WILAYAH_PATH', plugin_dir_path(__FILE__));
define('WILAYAH_URL', plugin_dir_url(__FILE__));
define('WILAYAH_PROVINSI_TABLE', 'wilayah_provinsi');

// Autoloader for classes
spl_autoload_register(function ($class) {
    $prefix = 'WilayahIndonesia\\';
    $base_dir = WILAYAH_PATH . 'includes/';

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

// Main plugin class
class Wilayah_Indonesia {
    private static $instance = null;
    private $admin;
    private $helper;

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {
        // Core classes
        require_once WILAYAH_PATH . 'includes/class-activator.php';
        require_once WILAYAH_PATH . 'includes/class-deactivator.php';
        require_once WILAYAH_PATH . 'includes/class-admin.php';
        require_once WILAYAH_PATH . 'includes/class-helper.php';

        // Initialize main components
        $this->admin = new WilayahIndonesia\Admin();
        $this->helper = WilayahIndonesia\Helper::get_instance();
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array('WilayahIndonesia\Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('WilayahIndonesia\Deactivator', 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function init() {
        $this->check_version();
    }

    private function check_version() {
        if (get_option('wilayah_indonesia_version') !== WILAYAH_VERSION) {
            WilayahIndonesia\Activator::activate();
            update_option('wilayah_indonesia_version', WILAYAH_VERSION);
        }
    }

    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'wilayah-indonesia') === false) {
            return;
        }

        // Enqueue Tailwind CSS
        wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), WILAYAH_VERSION);

        // Enqueue DataTables CSS
        wp_enqueue_style(
            'datatables', 
            'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css', 
            array(), 
            '1.11.5'
        );

        // Enqueue custom plugin CSS
        wp_enqueue_style(
            'wilayah-indonesia-admin',
            WILAYAH_URL . 'assets/css/admin.css',
            array('datatables'),
            WILAYAH_VERSION
        );

        // Enqueue DataTables JS
        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
            array('jquery'),
            '1.11.5',
            true
        );

        // Enqueue custom plugin JavaScript
        wp_enqueue_script(
            'wilayah-indonesia-admin',
            WILAYAH_URL . 'assets/js/admin.js',
            array('jquery', 'datatables'),
            WILAYAH_VERSION,
            true
        );

        // Localize script
        wp_localize_script('wilayah-indonesia-admin', 'wilayahIndonesia', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wilayah_indonesia_nonce'),
            'messages' => array(
                'delete_confirm' => __('Apakah Anda yakin ingin menghapus provinsi ini?', 'wilayah-indonesia'),
                'error' => __('Terjadi kesalahan', 'wilayah-indonesia')
            )
        ));
    }

    public function load_textdomain() {
        load_plugin_textdomain('wilayah-indonesia', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

// Initialize plugin
function run_wilayah_indonesia() {
    return Wilayah_Indonesia::get_instance();
}

run_wilayah_indonesia();
