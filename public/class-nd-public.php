<?php
/**
 * Classe principale du frontend
 *
 * @package NovaliaDevis
 * @subpackage Public
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Public {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Chargement des assets frontend
     */
    public function enqueue_assets() {
        // CSS Frontend
        wp_enqueue_style(
            'nd-frontend-style',
            ND_PLUGIN_URL . 'assets/css/frontend-style.css',
            [],
            ND_VERSION
        );
        
        // Leaflet CSS (pour la carte)
        wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4'
        );
        
        // jQuery (inclus par WordPress)
        wp_enqueue_script('jquery');
        
        // Leaflet JS (pour la carte)
        wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true
        );
        
        // Script d'autocomplétion des adresses
        wp_enqueue_script(
            'nd-map-autocomplete',
            ND_PLUGIN_URL . 'assets/js/map-autocomplete.js',
            ['jquery', 'leaflet-js'],
            ND_VERSION,
            true
        );
        
        // Script principal du wizard
        wp_enqueue_script(
            'nd-frontend-wizard',
            ND_PLUGIN_URL . 'assets/js/frontend-wizard.js',
            ['jquery', 'nd-map-autocomplete'],
            ND_VERSION,
            true
        );
        
        // Localisation des scripts
        wp_localize_script('nd-frontend-wizard', 'novaliaDevis', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('novalia-devis/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => [
                'error' => __('Une erreur est survenue', 'novalia-devis'),
                'loading' => __('Chargement...', 'novalia-devis'),
                'calculating' => __('Calcul en cours...', 'novalia-devis'),
                'success' => __('Devis envoyé avec succès !', 'novalia-devis'),
                'required_field' => __('Ce champ est obligatoire', 'novalia-devis'),
                'invalid_email' => __('Email invalide', 'novalia-devis'),
                'min_distance' => __('La distance doit être supérieure à 0', 'novalia-devis'),
                'min_items' => __('Veuillez sélectionner au moins un objet', 'novalia-devis')
            ]
        ]);
    }
}