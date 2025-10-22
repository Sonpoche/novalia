<?php
/**
 * Plugin Name: Novalia Devis
 * Plugin URI: https://novalia.fr/devis
 * Description: Plugin complet d'estimation de devis de déménagement avec calcul automatique, génération PDF et envoi par email
 * Version: 1.0.0
 * Author: Novalia
 * Author URI: https://novalia.fr
 * Text Domain: novalia-devis
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Sécurité : empêche l'accès direct
}

// Constantes du plugin
define('ND_VERSION', '1.0.0');
define('ND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ND_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ND_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Classe principale du plugin Novalia Devis
 */
class Novalia_Devis {
    
    private static $instance = null;
    
    /**
     * Singleton - Instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->init_hooks();
    }
    
    /**
     * Chargement des dépendances
     */
    private function load_dependencies() {
        
        // Classes de base
        require_once ND_PLUGIN_DIR . 'includes/class-nd-activator.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-deactivator.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-database.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-items.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-pricing.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-quotes.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-pdf.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-email.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-api.php';
        require_once ND_PLUGIN_DIR . 'includes/class-nd-settings.php';
        
        // Administration
        if (is_admin()) {
            require_once ND_PLUGIN_DIR . 'admin/class-nd-admin.php';
            require_once ND_PLUGIN_DIR . 'admin/class-nd-admin-items.php';
            require_once ND_PLUGIN_DIR . 'admin/class-nd-admin-pricing.php';
            require_once ND_PLUGIN_DIR . 'admin/class-nd-admin-quotes.php';
            require_once ND_PLUGIN_DIR . 'admin/class-nd-admin-settings.php';
        }
        
        // Frontend
        require_once ND_PLUGIN_DIR . 'public/class-nd-public.php';
        require_once ND_PLUGIN_DIR . 'public/class-nd-shortcode.php';
    }
    
    /**
     * Configuration des traductions
     */
    private function set_locale() {
        add_action('plugins_loaded', function() {
            load_plugin_textdomain(
                'novalia-devis',
                false,
                dirname(ND_PLUGIN_BASENAME) . '/languages/'
            );
        });
    }
    
    /**
     * Initialisation des hooks WordPress
     */
    private function init_hooks() {
        
        // Hooks d'activation / désactivation
        register_activation_hook(__FILE__, ['ND_Activator', 'activate']);
        register_deactivation_hook(__FILE__, ['ND_Deactivator', 'deactivate']);
        
        // Initialisation des composants
        add_action('init', [$this, 'init_components']);
        
        // Administration
        if (is_admin()) {
            new ND_Admin();
        }
        
        // Frontend
        new ND_Public();
        new ND_Shortcode();
        
        // REST API
        add_action('rest_api_init', function() {
            $api = new ND_API();
            $api->register_routes();
        });
    }
    
    /**
     * Initialisation des composants principaux
     */
    public function init_components() {
        // Vérification et mise à jour de la version de la base de données
        $db_version = get_option('nd_db_version');
        
        if ($db_version !== ND_VERSION) {
            ND_Database::create_tables();
            update_option('nd_db_version', ND_VERSION);
        }
    }
    
    /**
     * Méthodes utilitaires statiques
     */
    public static function get_plugin_dir() {
        return ND_PLUGIN_DIR;
    }
    
    public static function get_plugin_url() {
        return ND_PLUGIN_URL;
    }
    
    public static function get_version() {
        return ND_VERSION;
    }
}

/**
 * Fonction d'initialisation du plugin
 */
function novalia_devis_init() {
    return Novalia_Devis::get_instance();
}

// Lancement du plugin
novalia_devis_init();

/**
 * Fonction helper pour accéder à l'instance
 */
function ND() {
    return Novalia_Devis::get_instance();
}