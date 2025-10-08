<?php
/**
 * Gestion du shortcode [devis_demenagement]
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-shortcode.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Shortcode {
    
    /**
     * Enregistrer le shortcode
     */
    public static function register_shortcode() {
        add_shortcode('devis_demenagement', array('Devis_Shortcode', 'render_shortcode'));
    }
    
    /**
     * Rendu du shortcode
     */
    public static function render_shortcode($atts) {
        // Attributs par défaut
        $atts = shortcode_atts(array(
            'titre' => 'Estimation de votre déménagement',
            'couleur' => '#3498db'
        ), $atts);
        
        // Récupérer les objets
        $objets = Devis_Database::get_objets_by_category();
        
        // Démarrer la capture de sortie
        ob_start();
        
        // Inclure le template
        include DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'templates/formulaire-devis.php';
        
        return ob_get_clean();
    }
    
    /**
     * AJAX : Soumettre le formulaire
     */
    public static function ajax_submit_devis() {
        check_ajax_referer('devis_nonce', 'nonce');
        
        // Récupérer et nettoyer les données
        $data = array(
            'client_nom' => sanitize_text_field($_POST['client_nom']),
            'client_email' => sanitize_email($_POST['client_email']),
            'client_telephone' => sanitize_text_field($_POST['client_telephone']),
            'adresse_depart' => sanitize_text_field($_POST['adresse_depart']),
            'adresse_arrivee' => sanitize_text_field($_POST['adresse_arrivee']),
            'objets' => isset($_POST['objets']) ? $_POST['objets'] : array(),
            'volume_custom' => isset($_POST['volume_custom']) ? floatval($_POST['volume_custom']) : 0,
            'etages_depart' => isset($_POST['etages_depart']) ? intval($_POST['etages_depart']) : 0,
            'etages_arrivee' => isset($_POST['etages_arrivee']) ? intval($_POST['etages_arrivee']) : 0,
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        // Calculer le devis
        $calculator = new Devis_Calculator();
        $calcul = $calculator->calculate($data);
        
        // Fusionner les données
        $devis_data = array_merge($data, $calcul);
        
        // Récupérer les détails des objets
        $objets_details = $calculator->get_objets_details($data['objets']);
        $devis_data['objets_selectionnes'] = $objets_details;
        
        // Sauvegarder dans l'historique
        $devis_id = Devis_Database::save_devis($devis_data);
        
        // Générer le PDF
        $pdf_generator = new Devis_PDF();
        $pdf_content = $pdf_generator->generate($devis_data, $objets_details);
        
        // Envoyer les emails
        $email_sender = new Devis_Email();
        $email_result = $email_sender->send_all($devis_data, $pdf_content);
        
        // Sauvegarder le PDF
        $pdf_file = $pdf_generator->save_to_file($pdf_content, 'devis-' . $devis_id . '.pdf');
        
        // Retourner le résultat
        wp_send_json_success(array(
            'message' => 'Devis généré avec succès !',
            'devis_id' => $devis_id,
            'prix_total' => $calcul['prix_total'],
            'volume_total' => $calcul['volume_total'],
            'distance_km' => $calcul['distance_km'],
            'pdf_url' => $pdf_file['url'],
            'email_sent' => $email_result['success']
        ));
    }
}

// Enregistrer l'action AJAX pour la soumission du formulaire
add_action('wp_ajax_submit_devis', array('Devis_Shortcode', 'ajax_submit_devis'));
add_action('wp_ajax_nopriv_submit_devis', array('Devis_Shortcode', 'ajax_submit_devis'));