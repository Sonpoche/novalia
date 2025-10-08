<?php
/**
 * Widget Elementor pour le formulaire de devis
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-elementor-widget.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Elementor_Widget {
    
    /**
     * Enregistrer le widget
     */
    public static function register_widget($widgets_manager) {
        // Vérifier si Elementor est actif et chargé
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Vérifier que la classe de base existe
        if (!class_exists('\Elementor\Widget_Base')) {
            return;
        }
        
        // Charger la classe du widget
        self::load_widget_class();
        
        // Enregistrer le widget
        if (class_exists('Devis_Demenagement_Elementor_Widget')) {
            $widgets_manager->register(new \Devis_Demenagement_Elementor_Widget());
        }
    }
    
    /**
     * Charger la classe du widget
     */
    private static function load_widget_class() {
        if (class_exists('Devis_Demenagement_Elementor_Widget')) {
            return;
        }
        
        // Inclure la définition de la classe seulement si Elementor est disponible
        require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'includes/elementor-widget-class.php';
    }
}