<?php
/**
 * Gestion des paramètres du plugin
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Settings {
    
    /**
     * Récupération de tous les paramètres
     */
    public static function get_all_settings() {
        return [
            'pricing' => self::get_pricing_settings(),
            'company' => self::get_company_settings(),
            'email' => self::get_email_settings(),
            'pdf' => self::get_pdf_settings()
        ];
    }
    
    /**
     * Récupération des paramètres de tarification
     */
    public static function get_pricing_settings() {
        $default = [
            'price_per_km' => 1.50,
            'price_per_m3' => 35.00,
            'fixed_fee' => 0.00,
            'fee_floor' => 25.00,
            'fee_elevator' => 0.00,
            'fee_packing' => 15.00,
            'fee_insurance' => 2.50,
            'min_quote_amount' => 150.00,
        ];
        
        return get_option('nd_pricing', $default);
    }
    
    /**
     * Mise à jour des paramètres de tarification
     */
    public static function update_pricing_settings($settings) {
        $clean_settings = [];
        
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
            if (isset($settings[$field])) {
                $clean_settings[$field] = floatval($settings[$field]);
            }
        }
        
        return update_option('nd_pricing', $clean_settings);
    }
    
    /**
     * Récupération des paramètres de l'entreprise
     */
    public static function get_company_settings() {
        $default = [
            'name' => 'Novalia Déménagement',
            'address' => '',
            'zipcode' => '',
            'city' => '',
            'phone' => '',
            'email' => get_option('admin_email'),
            'siret' => '',
            'logo_url' => '',
        ];
        
        return get_option('nd_company', $default);
    }
    
    /**
     * Mise à jour des paramètres de l'entreprise
     */
    public static function update_company_settings($settings) {
        $clean_settings = [];
        
        $fields = [
            'name' => 'sanitize_text_field',
            'address' => 'sanitize_text_field',
            'zipcode' => 'sanitize_text_field',
            'city' => 'sanitize_text_field',
            'phone' => 'sanitize_text_field',
            'email' => 'sanitize_email',
            'siret' => 'sanitize_text_field',
            'logo_url' => 'esc_url_raw'
        ];
        
        foreach ($fields as $field => $sanitize) {
            if (isset($settings[$field])) {
                $clean_settings[$field] = call_user_func($sanitize, $settings[$field]);
            }
        }
        
        return update_option('nd_company', $clean_settings);
    }
    
    /**
     * Récupération des paramètres email
     */
    public static function get_email_settings() {
        $default = [
            'customer_subject' => 'Votre devis de déménagement Novalia - {quote_number}',
            'admin_subject' => 'Nouveau devis {quote_number}',
            'admin_email' => get_option('admin_email'),
            'send_copy_to_admin' => 1,
        ];
        
        return get_option('nd_email', $default);
    }
    
    /**
     * Mise à jour des paramètres email
     */
    public static function update_email_settings($settings) {
        $clean_settings = [];
        
        if (isset($settings['customer_subject'])) {
            $clean_settings['customer_subject'] = sanitize_text_field($settings['customer_subject']);
        }
        
        if (isset($settings['admin_subject'])) {
            $clean_settings['admin_subject'] = sanitize_text_field($settings['admin_subject']);
        }
        
        if (isset($settings['admin_email'])) {
            $clean_settings['admin_email'] = sanitize_email($settings['admin_email']);
        }
        
        if (isset($settings['send_copy_to_admin'])) {
            $clean_settings['send_copy_to_admin'] = intval($settings['send_copy_to_admin']);
        }
        
        return update_option('nd_email', $clean_settings);
    }
    
    /**
     * Récupération des paramètres PDF
     */
    public static function get_pdf_settings() {
        $default = [
            'footer_text' => 'Novalia Déménagement - Votre partenaire déménagement de confiance',
            'legal_mentions' => 'Devis gratuit et sans engagement. Valable 30 jours.',
        ];
        
        return get_option('nd_pdf', $default);
    }
    
    /**
     * Mise à jour des paramètres PDF
     */
    public static function update_pdf_settings($settings) {
        $clean_settings = [];
        
        if (isset($settings['footer_text'])) {
            $clean_settings['footer_text'] = sanitize_text_field($settings['footer_text']);
        }
        
        if (isset($settings['legal_mentions'])) {
            $clean_settings['legal_mentions'] = sanitize_textarea_field($settings['legal_mentions']);
        }
        
        return update_option('nd_pdf', $clean_settings);
    }
    
    /**
     * Upload du logo
     */
    public static function upload_logo($file) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Vérification du type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            return [
                'success' => false,
                'message' => 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP'
            ];
        }
        
        // Vérification de la taille (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'Fichier trop volumineux. Taille maximum : 2 MB'
            ];
        }
        
        // Upload
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Mise à jour du paramètre
            $company = self::get_company_settings();
            $company['logo_url'] = $movefile['url'];
            self::update_company_settings($company);
            
            return [
                'success' => true,
                'message' => 'Logo uploadé avec succès',
                'url' => $movefile['url']
            ];
        } else {
            return [
                'success' => false,
                'message' => $movefile['error'] ?? 'Erreur lors de l\'upload'
            ];
        }
    }
    
    /**
     * Suppression du logo
     */
    public static function delete_logo() {
        $company = self::get_company_settings();
        
        if (!empty($company['logo_url'])) {
            // Suppression du fichier
            $upload_dir = wp_upload_dir();
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $company['logo_url']);
            
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Mise à jour du paramètre
            $company['logo_url'] = '';
            self::update_company_settings($company);
            
            return [
                'success' => true,
                'message' => 'Logo supprimé avec succès'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Aucun logo à supprimer'
        ];
    }
    
    /**
     * Réinitialisation des paramètres par défaut
     */
    public static function reset_settings($section = 'all') {
        $success = true;
        
        switch ($section) {
            case 'pricing':
                delete_option('nd_pricing');
                ND_Activator::set_default_options();
                break;
                
            case 'company':
                delete_option('nd_company');
                ND_Activator::set_default_options();
                break;
                
            case 'email':
                delete_option('nd_email');
                ND_Activator::set_default_options();
                break;
                
            case 'pdf':
                delete_option('nd_pdf');
                ND_Activator::set_default_options();
                break;
                
            case 'all':
                delete_option('nd_pricing');
                delete_option('nd_company');
                delete_option('nd_email');
                delete_option('nd_pdf');
                ND_Activator::set_default_options();
                break;
                
            default:
                $success = false;
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Paramètres réinitialisés' : 'Section invalide'
        ];
    }
    
    /**
     * Validation des paramètres de tarification
     */
    public static function validate_pricing_settings($settings) {
        $errors = [];
        
        // Tarif au kilomètre
        if (!isset($settings['price_per_km']) || floatval($settings['price_per_km']) < 0) {
            $errors[] = 'Le tarif au kilomètre doit être positif';
        }
        
        // Tarif au m³
        if (!isset($settings['price_per_m3']) || floatval($settings['price_per_m3']) < 0) {
            $errors[] = 'Le tarif au m³ doit être positif';
        }
        
        // Montant minimum
        if (isset($settings['min_quote_amount']) && floatval($settings['min_quote_amount']) < 0) {
            $errors[] = 'Le montant minimum doit être positif';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Export des paramètres en JSON
     */
    public static function export_settings() {
        $settings = self::get_all_settings();
        
        return json_encode($settings, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import des paramètres depuis JSON
     */
    public static function import_settings($json) {
        $settings = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'JSON invalide'
            ];
        }
        
        if (isset($settings['pricing'])) {
            self::update_pricing_settings($settings['pricing']);
        }
        
        if (isset($settings['company'])) {
            self::update_company_settings($settings['company']);
        }
        
        if (isset($settings['email'])) {
            self::update_email_settings($settings['email']);
        }
        
        if (isset($settings['pdf'])) {
            self::update_pdf_settings($settings['pdf']);
        }
        
        return [
            'success' => true,
            'message' => 'Paramètres importés avec succès'
        ];
    }
    
    /**
     * Récupération d'une option spécifique
     */
    public static function get_option($section, $key, $default = null) {
        $settings = get_option('nd_' . $section, []);
        
        return $settings[$key] ?? $default;
    }
    
    /**
     * Mise à jour d'une option spécifique
     */
    public static function update_option($section, $key, $value) {
        $settings = get_option('nd_' . $section, []);
        $settings[$key] = $value;
        
        return update_option('nd_' . $section, $settings);
    }
}