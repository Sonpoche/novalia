<?php
/**
 * Gestion des devis
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Quotes {
    
    /**
     * Création d'un nouveau devis complet
     */
    public static function create_quote($data) {
        // Validation des données
        $validation = self::validate_quote_data($data);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }
        
        // Calcul du prix total
        $pricing_data = [
            'distance' => $data['distance'],
            'volume' => $data['total_volume'],
            'floors_from' => $data['floors_from'] ?? 0,
            'floors_to' => $data['floors_to'] ?? 0,
            'has_elevator_from' => $data['has_elevator_from'] ?? false,
            'has_elevator_to' => $data['has_elevator_to'] ?? false,
            'need_packing' => $data['need_packing'] ?? false,
            'need_insurance' => $data['need_insurance'] ?? false,
        ];
        
        $calculation = ND_Pricing::calculate_quote($pricing_data);
        
        // Préparation des données du devis
        $quote_data = [
            'customer_name' => $data['customer_name'],
            'customer_firstname' => $data['customer_firstname'] ?? '',
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? '',
            'address_from' => $data['address_from'],
            'address_to' => $data['address_to'],
            'distance' => $data['distance'],
            'total_volume' => $data['total_volume'],
            'total_price' => $calculation['total']
        ];
        
        // Insertion du devis en base
        $quote_id = ND_Database::insert_quote($quote_data);
        
        if (!$quote_id) {
            return [
                'success' => false,
                'errors' => ['Erreur lors de la création du devis']
            ];
        }
        
        // Insertion des objets du devis
        if (!empty($data['items'])) {
            ND_Database::insert_quote_items($quote_id, $data['items']);
        }
        
        // Récupération du devis complet
        $quote = self::get_quote_with_items($quote_id);
        
        // Génération du PDF
        $pdf_result = ND_PDF::generate_quote_pdf($quote);
        
        if ($pdf_result['success']) {
            ND_Database::update_quote_pdf_path($quote_id, $pdf_result['file_path']);
            $quote['pdf_path'] = $pdf_result['file_path'];
            $quote['pdf_url'] = $pdf_result['file_url'];
        }
        
        // Envoi de l'email au client
        ND_Email::send_customer_quote($quote);
        
        // Envoi de l'email à l'admin
        $email_settings = get_option('nd_email');
        if (!empty($email_settings['send_copy_to_admin'])) {
            ND_Email::send_admin_notification($quote);
        }
        
        return [
            'success' => true,
            'quote_id' => $quote_id,
            'quote_number' => $quote['quote_number'],
            'quote' => $quote,
            'calculation' => $calculation
        ];
    }
    
    /**
     * Récupération d'un devis avec ses objets
     */
    public static function get_quote_with_items($quote_id) {
        $quote = ND_Database::get_quote($quote_id);
        
        if (!$quote) {
            return null;
        }
        
        $quote['items'] = ND_Database::get_quote_items($quote_id);
        
        return $quote;
    }
    
    /**
     * Récupération de tous les devis avec pagination
     */
    public static function get_all_quotes($args = []) {
        $defaults = [
            'limit' => 50,
            'offset' => 0,
            'order_by' => 'created_at',
            'order' => 'DESC',
            'status' => null,
            'search' => null
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        return ND_Database::get_all_quotes(
            $args['limit'],
            $args['offset'],
            $args['order_by'],
            $args['order']
        );
    }
    
    /**
     * Recherche de devis
     */
    public static function search_quotes($search_term) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        $search = '%' . $wpdb->esc_like($search_term) . '%';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table 
            WHERE quote_number LIKE %s 
            OR customer_name LIKE %s 
            OR customer_email LIKE %s 
            OR customer_phone LIKE %s
            ORDER BY created_at DESC
            LIMIT 50",
            $search, $search, $search, $search
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Filtrage des devis par statut
     */
    public static function get_quotes_by_status($status, $limit = 50) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table 
            WHERE status = %s 
            ORDER BY created_at DESC 
            LIMIT %d",
            $status,
            $limit
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Mise à jour du statut d'un devis
     */
    public static function update_quote_status($quote_id, $status) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        $allowed_statuses = ['pending', 'accepted', 'rejected', 'completed', 'cancelled'];
        
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }
        
        return $wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $quote_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Suppression d'un devis
     */
    public static function delete_quote($quote_id) {
        // Récupération du devis pour supprimer le PDF
        $quote = ND_Database::get_quote($quote_id);
        
        if ($quote && !empty($quote['pdf_path'])) {
            $upload_dir = wp_upload_dir();
            $pdf_file = $upload_dir['basedir'] . $quote['pdf_path'];
            
            if (file_exists($pdf_file)) {
                unlink($pdf_file);
            }
        }
        
        // Suppression du devis et de ses objets
        return ND_Database::delete_quote($quote_id);
    }
    
    /**
     * Regénération du PDF d'un devis
     */
    public static function regenerate_pdf($quote_id) {
        $quote = self::get_quote_with_items($quote_id);
        
        if (!$quote) {
            return [
                'success' => false,
                'message' => 'Devis introuvable'
            ];
        }
        
        // Suppression de l'ancien PDF
        if (!empty($quote['pdf_path'])) {
            $upload_dir = wp_upload_dir();
            $old_pdf = $upload_dir['basedir'] . $quote['pdf_path'];
            
            if (file_exists($old_pdf)) {
                unlink($old_pdf);
            }
        }
        
        // Génération du nouveau PDF
        $pdf_result = ND_PDF::generate_quote_pdf($quote);
        
        if ($pdf_result['success']) {
            ND_Database::update_quote_pdf_path($quote_id, $pdf_result['file_path']);
        }
        
        return $pdf_result;
    }
    
    /**
     * Renvoi de l'email au client
     */
    public static function resend_email($quote_id) {
        $quote = self::get_quote_with_items($quote_id);
        
        if (!$quote) {
            return [
                'success' => false,
                'message' => 'Devis introuvable'
            ];
        }
        
        return ND_Email::send_customer_quote($quote);
    }
    
    /**
     * Statistiques des devis
     */
    public static function get_statistics() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        // Total des devis
        $total_quotes = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        // Devis par statut
        $by_status = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table GROUP BY status",
            ARRAY_A
        );
        
        // Montant total
        $total_amount = $wpdb->get_var("SELECT SUM(total_price) FROM $table");
        
        // Moyenne
        $average_amount = $wpdb->get_var("SELECT AVG(total_price) FROM $table");
        
        // Volume total
        $total_volume = $wpdb->get_var("SELECT SUM(total_volume) FROM $table");
        
        // Distance totale
        $total_distance = $wpdb->get_var("SELECT SUM(distance) FROM $table");
        
        // Devis du mois en cours
        $current_month = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        );
        
        return [
            'total_quotes' => intval($total_quotes),
            'by_status' => $by_status,
            'total_amount' => floatval($total_amount),
            'average_amount' => floatval($average_amount),
            'total_volume' => floatval($total_volume),
            'total_distance' => floatval($total_distance),
            'current_month' => intval($current_month)
        ];
    }
    
    /**
     * Validation des données d'un devis
     */
    public static function validate_quote_data($data) {
        $errors = [];
        
        // Informations client
        if (empty($data['customer_name'])) {
            $errors[] = 'Le nom du client est obligatoire';
        }
        
        if (empty($data['customer_email']) || !is_email($data['customer_email'])) {
            $errors[] = 'L\'email du client est invalide';
        }
        
        // Adresses
        if (empty($data['address_from'])) {
            $errors[] = 'L\'adresse de départ est obligatoire';
        }
        
        if (empty($data['address_to'])) {
            $errors[] = 'L\'adresse d\'arrivée est obligatoire';
        }
        
        // Distance et volume
        if (!isset($data['distance']) || floatval($data['distance']) <= 0) {
            $errors[] = 'La distance doit être supérieure à 0';
        }
        
        if (!isset($data['total_volume']) || floatval($data['total_volume']) <= 0) {
            $errors[] = 'Le volume total doit être supérieur à 0';
        }
        
        // Objets
        if (empty($data['items']) || !is_array($data['items'])) {
            $errors[] = 'Aucun objet sélectionné';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Export CSV des devis
     */
    public static function export_quotes_csv($quote_ids = null) {
        if ($quote_ids) {
            $quotes = array_map([__CLASS__, 'get_quote_with_items'], $quote_ids);
        } else {
            $quotes = self::get_all_quotes(['limit' => 1000]);
        }
        
        $csv = "Numéro,Date,Client,Email,Téléphone,Départ,Arrivée,Distance (km),Volume (m³),Prix (€),Statut\n";
        
        foreach ($quotes as $quote) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s",%s,%s,%s,"%s"' . "\n",
                $quote['quote_number'],
                $quote['created_at'],
                str_replace('"', '""', $quote['customer_name']),
                $quote['customer_email'],
                $quote['customer_phone'],
                str_replace('"', '""', $quote['address_from']),
                str_replace('"', '""', $quote['address_to']),
                $quote['distance'],
                $quote['total_volume'],
                $quote['total_price'],
                $quote['status']
            );
        }
        
        return $csv;
    }
}