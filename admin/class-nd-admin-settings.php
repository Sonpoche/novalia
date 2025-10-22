<?php
/**
 * Gestion des paramètres (Admin)
 *
 * @package NovaliaDevis
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Admin_Settings {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_nd_upload_logo', [$this, 'ajax_upload_logo']);
        add_action('wp_ajax_nd_delete_logo', [$this, 'ajax_delete_logo']);
        add_action('wp_ajax_nd_test_email', [$this, 'ajax_test_email']);
    }
    
    /**
     * Enregistrement des paramètres
     */
    public function register_settings() {
        // Paramètres entreprise
        register_setting('nd_company_group', 'nd_company', [
            'sanitize_callback' => [$this, 'sanitize_company']
        ]);
        
        // Paramètres email
        register_setting('nd_email_group', 'nd_email', [
            'sanitize_callback' => [$this, 'sanitize_email']
        ]);
        
        // Paramètres PDF
        register_setting('nd_pdf_group', 'nd_pdf', [
            'sanitize_callback' => [$this, 'sanitize_pdf']
        ]);
    }
    
    /**
     * Sanitization entreprise
     */
    public function sanitize_company($input) {
        $sanitized = [];
        
        $sanitized['name'] = sanitize_text_field($input['name'] ?? '');
        $sanitized['address'] = sanitize_text_field($input['address'] ?? '');
        $sanitized['zipcode'] = sanitize_text_field($input['zipcode'] ?? '');
        $sanitized['city'] = sanitize_text_field($input['city'] ?? '');
        $sanitized['phone'] = sanitize_text_field($input['phone'] ?? '');
        $sanitized['email'] = sanitize_email($input['email'] ?? '');
        $sanitized['siret'] = sanitize_text_field($input['siret'] ?? '');
        $sanitized['logo_url'] = esc_url_raw($input['logo_url'] ?? '');
        
        add_settings_error(
            'nd_company',
            'settings_updated',
            __('Paramètres de l\'entreprise mis à jour', 'novalia-devis'),
            'success'
        );
        
        return $sanitized;
    }
    
    /**
     * Sanitization email
     */
    public function sanitize_email($input) {
        $sanitized = [];
        
        $sanitized['customer_subject'] = sanitize_text_field($input['customer_subject'] ?? '');
        $sanitized['admin_subject'] = sanitize_text_field($input['admin_subject'] ?? '');
        $sanitized['admin_email'] = sanitize_email($input['admin_email'] ?? '');
        $sanitized['send_copy_to_admin'] = isset($input['send_copy_to_admin']) ? 1 : 0;
        
        add_settings_error(
            'nd_email',
            'settings_updated',
            __('Paramètres email mis à jour', 'novalia-devis'),
            'success'
        );
        
        return $sanitized;
    }
    
    /**
     * Sanitization PDF
     */
    public function sanitize_pdf($input) {
        $sanitized = [];
        
        $sanitized['footer_text'] = sanitize_text_field($input['footer_text'] ?? '');
        $sanitized['legal_mentions'] = sanitize_textarea_field($input['legal_mentions'] ?? '');
        
        add_settings_error(
            'nd_pdf',
            'settings_updated',
            __('Paramètres PDF mis à jour', 'novalia-devis'),
            'success'
        );
        
        return $sanitized;
    }
    
    /**
     * Rendu de la page
     */
    public function render_page() {
        if (!current_user_can('manage_novalia_devis')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'novalia-devis'));
        }
        
        // Récupération de l'onglet actif
        $active_tab = $_GET['tab'] ?? 'company';
        
        // Récupération des paramètres
        $company = ND_Settings::get_company_settings();
        $email = ND_Settings::get_email_settings();
        $pdf = ND_Settings::get_pdf_settings();
        
        include ND_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
    
    /**
     * AJAX : Upload du logo
     */
    public function ajax_upload_logo() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_novalia_devis')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        if (empty($_FILES['logo'])) {
            wp_send_json_error(['message' => 'Aucun fichier']);
        }
        
        $result = ND_Settings::upload_logo($_FILES['logo']);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX : Supprimer le logo
     */
    public function ajax_delete_logo() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_novalia_devis')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $result = ND_Settings::delete_logo();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX : Test email
     */
    public function ajax_test_email() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_novalia_devis')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $to = sanitize_email($_POST['email'] ?? '');
        
        if (empty($to)) {
            wp_send_json_error(['message' => 'Email invalide']);
        }
        
        $result = ND_Email::send_test_email($to);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}