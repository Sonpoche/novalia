<?php
/**
 * Gestion du shortcode
 *
 * @package NovaliaDevis
 * @subpackage Public
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Shortcode {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_shortcode('novalia_devis_form', [$this, 'render_form']);
    }
    
    /**
     * Rendu du formulaire via shortcode
     */
    public function render_form($atts) {
        // Attributs par défaut
        $atts = shortcode_atts([
            'show_title' => 'yes',
            'title' => __('Estimez votre déménagement', 'novalia-devis')
        ], $atts, 'novalia_devis_form');
        
        // Récupération des objets par catégorie
        $items_by_category = ND_Items::get_items_by_category(true);
        
        // Récupération des tarifs pour affichage
        $pricing = ND_Settings::get_pricing_settings();
        
        // Buffer de sortie
        ob_start();
        
        include ND_PLUGIN_DIR . 'public/views/wizard-container.php';
        
        return ob_get_clean();
    }
}