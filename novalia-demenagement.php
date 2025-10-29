<?php
/**
 * Plugin Name: Novalia Déménagement
 * Plugin URI: https://novaliagroup.ch
 * Description: Plugin professionnel de devis de déménagement pour Novalia Group
 * Version: 1.0.0
 * Author: Novalia Group
 * Author URI: https://novaliagroup.ch
 * Text Domain: novalia-demenagement
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NOVALIA_VERSION', '1.0.0');
define('NOVALIA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NOVALIA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOVALIA_PLUGIN_BASENAME', plugin_basename(__FILE__));

class Novalia_Demenagement {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-database.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-items.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-tarifs.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-devis.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-pdf.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-fiche-technique.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-email.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-admin.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-shortcode.php';
        require_once NOVALIA_PLUGIN_DIR . 'includes/class-novalia-ajax.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function activate() {
        Novalia_Database::create_tables();
        Novalia_Items::insert_default_items();
        Novalia_Tarifs::insert_default_tarifs();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function init() {
        load_plugin_textdomain('novalia-demenagement', false, dirname(NOVALIA_PLUGIN_BASENAME) . '/languages');
        
        new Novalia_Admin();
        new Novalia_Shortcode();
        new Novalia_Ajax();
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('novalia-style', NOVALIA_PLUGIN_URL . 'assets/css/novalia-style.css', array(), NOVALIA_VERSION);
        
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
        
        wp_enqueue_script('novalia-script', NOVALIA_PLUGIN_URL . 'assets/js/novalia-script.js', array('jquery', 'leaflet-js'), NOVALIA_VERSION, true);
        
        wp_localize_script('novalia-script', 'novaliaAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('novalia_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'novalia') === false) {
            return;
        }
        
        wp_enqueue_style('novalia-admin-style', NOVALIA_PLUGIN_URL . 'assets/css/novalia-admin.css', array(), NOVALIA_VERSION);
        wp_enqueue_script('novalia-admin-script', NOVALIA_PLUGIN_URL . 'assets/js/novalia-admin.js', array('jquery'), NOVALIA_VERSION, true);
        
        wp_localize_script('novalia-admin-script', 'novaliaAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('novalia_admin_nonce')
        ));
    }
}

function novalia_demenagement() {
    return Novalia_Demenagement::get_instance();
}

novalia_demenagement();