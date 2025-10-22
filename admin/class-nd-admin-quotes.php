<?php
/**
 * Gestion des devis (Admin)
 *
 * @package NovaliaDevis
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Admin_Quotes {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('wp_ajax_nd_update_quote_status', [$this, 'ajax_update_status']);
        add_action('wp_ajax_nd_delete_quote', [$this, 'ajax_delete_quote']);
        add_action('wp_ajax_nd_resend_quote_email', [$this, 'ajax_resend_email']);
        add_action('wp_ajax_nd_regenerate_pdf', [$this, 'ajax_regenerate_pdf']);
    }
    
    /**
     * Rendu de la page
     */
    public function render_page() {
        $action = $_GET['action'] ?? 'list';
        $quote_id = intval($_GET['id'] ?? 0);
        
        switch ($action) {
            case 'view':
                $this->view_quote($quote_id);
                break;
                
            case 'export':
                $this->export_quotes();
                break;
                
            default:
                $this->list_quotes();
                break;
        }
    }
    
    /**
     * Liste des devis
     */
    private function list_quotes() {
        // Recherche
        $search = sanitize_text_field($_GET['s'] ?? '');
        
        if (!empty($search)) {
            $quotes = ND_Quotes::search_quotes($search);
        } else {
            $quotes = ND_Quotes::get_all_quotes(['limit' => 100]);
        }
        
        $total_quotes = ND_Database::count_quotes();
        
        include ND_PLUGIN_DIR . 'admin/views/quotes-list.php';
    }
    
    /**
     * Vue détaillée d'un devis
     */
    private function view_quote($quote_id) {
        if (!$quote_id) {
            wp_die(__('ID de devis invalide', 'novalia-devis'));
        }
        
        $quote = ND_Quotes::get_quote_with_items($quote_id);
        
        if (!$quote) {
            wp_die(__('Devis introuvable', 'novalia-devis'));
        }
        
        // Calcul du détail des prix
        $calculation = ND_Pricing::calculate_quote([
            'distance' => $quote['distance'],
            'volume' => $quote['total_volume']
        ]);
        
        include ND_PLUGIN_DIR . 'admin/views/quote-detail.php';
    }
    
    /**
     * Export des devis
     */
    private function export_quotes() {
        $csv = ND_Quotes::export_quotes_csv();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="devis-' . date('Y-m-d') . '.csv"');
        
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo $csv;
        exit;
    }
    
    /**
     * AJAX : Mise à jour du statut
     */
    public function ajax_update_status() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('view_novalia_quotes')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $quote_id = intval($_POST['quote_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        $result = ND_Quotes::update_quote_status($quote_id, $status);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Statut mis à jour'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la mise à jour']);
        }
    }
    
    /**
     * AJAX : Suppression d'un devis
     */
    public function ajax_delete_quote() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_novalia_devis')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $quote_id = intval($_POST['quote_id'] ?? 0);
        
        $result = ND_Quotes::delete_quote($quote_id);
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Devis supprimé'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la suppression']);
        }
    }
    
    /**
     * AJAX : Renvoyer l'email
     */
    public function ajax_resend_email() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('view_novalia_quotes')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $quote_id = intval($_POST['quote_id'] ?? 0);
        
        $result = ND_Quotes::resend_email($quote_id);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'Email renvoyé avec succès'
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * AJAX : Régénérer le PDF
     */
    public function ajax_regenerate_pdf() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('view_novalia_quotes')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $quote_id = intval($_POST['quote_id'] ?? 0);
        
        $result = ND_Quotes::regenerate_pdf($quote_id);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => 'PDF régénéré avec succès',
                'pdf_url' => $result['file_url']
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
}