<?php
/**
 * Plugin Name: Devis Déménagement
 * Plugin URI: https://votre-site.com
 * Description: Plugin d'estimation de devis en ligne pour déménagement avec calcul de distance et volume
 * Version: 1.0.0
 * Author: webdevfred
 * Text Domain: devis-demenagement
 * Domain Path: /languages
 */

// Sécurité : empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('DEVIS_DEMENAGEMENT_VERSION', '1.0.0');
define('DEVIS_DEMENAGEMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEVIS_DEMENAGEMENT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Classe principale du plugin
 */
class Devis_Demenagement {
    
    /**
     * Instance unique du plugin (Singleton)
     */
    private static $instance = null;
    
    /**
     * Retourne l'instance unique du plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur
     */
    private function __construct() {
        // Activation du plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Désactivation du plugin
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Charger les fichiers nécessaires
        $this->load_dependencies();
        
        // Initialiser les hooks
        $this->init_hooks();
    }
    
    /**
     * Charger les dépendances du plugin
     */
    private function load_dependencies() {
        // Charger les fichiers de classes
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-database.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-calculator.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-pdf.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-email.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-admin.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-shortcode.php';
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/class-devis-elementor-widget.php';
    }
    
    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        // Charger les scripts et styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Ajouter le menu admin
        add_action('admin_menu', array('Devis_Admin', 'add_admin_menu'));
        
        // Enregistrer le shortcode
        add_action('init', array('Devis_Shortcode', 'register_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_calculate_devis', array($this, 'ajax_calculate_devis'));
        add_action('wp_ajax_nopriv_calculate_devis', array($this, 'ajax_calculate_devis'));
        
        add_action('wp_ajax_calculate_distance', array($this, 'ajax_calculate_distance'));
        add_action('wp_ajax_nopriv_calculate_distance', array($this, 'ajax_calculate_distance'));
        
        // Intégration Elementor
        add_action('elementor/widgets/register', array('Devis_Elementor_Widget', 'register_widget'));
    }
    
    /**
     * Charger les scripts et styles frontend
     */
    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'devis-demenagement-style',
            DEVIS_DEMENAGEMENT_PLUGIN_URL . 'assets/css/devis-style.css',
            array(),
            DEVIS_DEMENAGEMENT_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'devis-demenagement-script',
            DEVIS_DEMENAGEMENT_PLUGIN_URL . 'assets/js/devis-script.js',
            array('jquery'),
            DEVIS_DEMENAGEMENT_VERSION,
            true
        );
        
        // Passer des variables PHP à JavaScript
        wp_localize_script('devis-demenagement-script', 'devisAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('devis_nonce')
        ));
    }
    
    /**
     * Charger les scripts et styles admin
     */
    public function admin_enqueue_scripts($hook) {
        // Charger uniquement sur les pages du plugin
        if (strpos($hook, 'devis-demenagement') === false) {
            return;
        }
        
        wp_enqueue_style(
            'devis-demenagement-admin-style',
            DEVIS_DEMENAGEMENT_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            DEVIS_DEMENAGEMENT_VERSION
        );
        
        wp_enqueue_script(
            'devis-demenagement-admin-script',
            DEVIS_DEMENAGEMENT_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            DEVIS_DEMENAGEMENT_VERSION,
            true
        );
    }
    
    /**
     * AJAX : Calculer le devis
     */
    public function ajax_calculate_devis() {
        check_ajax_referer('devis_nonce', 'nonce');
        
        $data = $_POST;
        $calculator = new Devis_Calculator();
        $result = $calculator->calculate($data);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX : Calculer la distance
     */
    public function ajax_calculate_distance() {
        check_ajax_referer('devis_nonce', 'nonce');
        
        $from = sanitize_text_field($_POST['from']);
        $to = sanitize_text_field($_POST['to']);
        
        $calculator = new Devis_Calculator();
        $distance = $calculator->calculate_distance($from, $to);
        
        wp_send_json_success(array('distance' => $distance));
    }
    
    /**
     * Activation du plugin
     */
    public function activate() {
        // Créer les tables en base de données
        Devis_Database::create_tables();
        
        // Insérer les données par défaut
        Devis_Database::insert_default_data();
        
        // IMPORTANT : Ne créer les options QUE si elles n'existent pas déjà
        $existing_settings = get_option('devis_demenagement_settings');
        
        if ($existing_settings === false) {
            // L'option n'existe pas, on la crée
            $default_options = array(
                'price_per_m3' => 35,
                'price_per_km' => 2,
                'minimum_price' => 150,
                'api_key' => '',
                'company_name' => get_bloginfo('name'),
                'company_email' => get_option('admin_email'),
                'company_phone' => '',
                'company_address' => ''
            );
            
            add_option('devis_demenagement_settings', $default_options);
        }
        // SINON : on ne touche pas aux valeurs existantes !
        
        // Flush les règles de réécriture
        flush_rewrite_rules();
    }
    
    /**
     * Désactivation du plugin
     */
    public function deactivate() {
        // Flush les règles de réécriture
        flush_rewrite_rules();
    }
}

// Initialiser le plugin
function devis_demenagement_init() {
    return Devis_Demenagement::get_instance();
}

// Lancer le plugin
add_action('plugins_loaded', 'devis_demenagement_init');