<?php
/**
 * Gestion de la tarification (Admin)
 *
 * @package NovaliaDevis
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Admin_Pricing {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    /**
     * Enregistrement des paramètres
     */
    public function register_settings() {
        register_setting(
            'nd_pricing_group',
            'nd_pricing',
            [$this, 'sanitize_settings']
        );
    }
    
    /**
     * Sanitization des paramètres
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        $fields = [
            'price_per_km',
            'price_per_m3',
            'fixed_fee',
            'fee_floor',
            'fee_elevator',
            'fee_packing',
            'fee_insurance',
            'min_quote_amount'
        ];
        
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = floatval($input[$field]);
                
                // Validation : pas de valeurs négatives
                if ($sanitized[$field] < 0) {
                    add_settings_error(
                        'nd_pricing',
                        $field,
                        sprintf(__('Le champ %s ne peut pas être négatif', 'novalia-devis'), $field),
                        'error'
                    );
                    $sanitized[$field] = 0;
                }
            }
        }
        
        add_settings_error(
            'nd_pricing',
            'pricing_updated',
            __('Tarification mise à jour avec succès', 'novalia-devis'),
            'success'
        );
        
        return $sanitized;
    }
    
    /**
     * Rendu de la page
     */
    public function render_page() {
        if (!current_user_can('edit_novalia_pricing')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'novalia-devis'));
        }
        
        // Récupération des paramètres
        $pricing = ND_Settings::get_pricing_settings();
        
        include ND_PLUGIN_DIR . 'admin/views/pricing-settings.php';
    }
}