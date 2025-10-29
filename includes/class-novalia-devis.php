<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Devis {
    
    public static function create_devis($data) {
        $items = $data['items'];
        $volume_total = Novalia_Items::calculate_total_volume($items);
        
        $prix_data = array(
            'distance' => floatval($data['distance']),
            'volume' => $volume_total,
            'etages_depart' => intval($data['etages_depart']),
            'etages_arrivee' => intval($data['etages_arrivee']),
            'nombre_cartons' => intval($data['nombre_cartons']),
            'date_demenagement' => $data['date_demenagement']
        );
        
        $prix = Novalia_Tarifs::calculate_both_prices($prix_data);
        
        $devis_data = array(
            'nom_client' => $data['nom_client'],
            'email_client' => $data['email_client'],
            'telephone_client' => $data['telephone_client'],
            'adresse_depart' => $data['adresse_depart'],
            'adresse_arrivee' => $data['adresse_arrivee'],
            'distance' => floatval($data['distance']),
            'date_demenagement' => $data['date_demenagement'],
            'volume_total' => $volume_total,
            'type_demenagement' => $data['type_demenagement'],
            'nombre_cartons' => intval($data['nombre_cartons']),
            'etages_depart' => intval($data['etages_depart']),
            'etages_arrivee' => intval($data['etages_arrivee']),
            'ascenseur_depart' => isset($data['ascenseur_depart']) ? (int)$data['ascenseur_depart'] : 0,
            'ascenseur_arrivee' => isset($data['ascenseur_arrivee']) ? (int)$data['ascenseur_arrivee'] : 0,
            'type_logement_depart' => isset($data['type_logement_depart']) ? $data['type_logement_depart'] : '',
            'type_logement_arrivee' => isset($data['type_logement_arrivee']) ? $data['type_logement_arrivee'] : '',
            'ascenseur_4pers_depart' => isset($data['ascenseur_4pers_depart']) ? (int)$data['ascenseur_4pers_depart'] : 0,
            'ascenseur_4pers_arrivee' => isset($data['ascenseur_4pers_arrivee']) ? (int)$data['ascenseur_4pers_arrivee'] : 0,
            'prix_standard' => $prix['standard'],
            'prix_complet' => $prix['complet']
        );
        
        $devis_id = Novalia_Database::insert_devis($devis_data);
        
        if ($devis_id) {
            Novalia_Database::insert_devis_items($devis_id, $items);
        }
        
        return $devis_id;
    }
    
    public static function get_devis($id) {
        $devis = Novalia_Database::get_devis($id);
        
        if ($devis) {
            $devis->items = Novalia_Database::get_devis_items($id);
            $devis->items_by_category = self::group_items_by_category($devis->items);
        }
        
        return $devis;
    }
    
    public static function get_all_devis($statut = null, $limit = 50, $offset = 0) {
        return Novalia_Database::get_all_devis($statut, $limit, $offset);
    }
    
    public static function update_statut($id, $statut) {
        $allowed_statuts = array('en_attente', 'accepte', 'refuse', 'annule');
        
        if (!in_array($statut, $allowed_statuts)) {
            return false;
        }
        
        return Novalia_Database::update_devis_statut($id, $statut);
    }
    
    public static function delete_devis($id) {
        return Novalia_Database::delete_devis($id);
    }
    
    public static function count_devis($statut = null) {
        return Novalia_Database::count_devis($statut);
    }
    
    public static function get_statut_label($statut) {
        $labels = array(
            'en_attente' => 'En attente',
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            'annule' => 'Annulé'
        );
        
        return isset($labels[$statut]) ? $labels[$statut] : $statut;
    }
    
    public static function get_statut_color($statut) {
        $colors = array(
            'en_attente' => '#FF7A00',
            'accepte' => '#2BBBAD',
            'refuse' => '#dc3545',
            'annule' => '#6c757d'
        );
        
        return isset($colors[$statut]) ? $colors[$statut] : '#1A2332';
    }
    
    private static function group_items_by_category($items) {
        $grouped = array();
        
        foreach ($items as $item) {
            $categorie = $item->categorie;
            if (!isset($grouped[$categorie])) {
                $grouped[$categorie] = array();
            }
            $grouped[$categorie][] = $item;
        }
        
        return $grouped;
    }
    
    public static function calculate_volume_by_category($items) {
        $volumes = array();
        
        foreach ($items as $item) {
            $categorie = $item->categorie;
            $volume = floatval($item->volume) * intval($item->quantite);
            
            if (!isset($volumes[$categorie])) {
                $volumes[$categorie] = 0;
            }
            $volumes[$categorie] += $volume;
        }
        
        return $volumes;
    }
    
    public static function get_devis_summary($id) {
        $devis = self::get_devis($id);
        
        if (!$devis) {
            return null;
        }
        
        $summary = array(
            'numero' => $devis->numero_devis,
            'client' => array(
                'nom' => $devis->nom_client,
                'email' => $devis->email_client,
                'telephone' => $devis->telephone_client
            ),
            'trajet' => array(
                'depart' => $devis->adresse_depart,
                'arrivee' => $devis->adresse_arrivee,
                'distance' => $devis->distance
            ),
            'date_demenagement' => $devis->date_demenagement,
            'volume_total' => $devis->volume_total,
            'nombre_items' => count($devis->items),
            'type_demenagement' => $devis->type_demenagement,
            'nombre_cartons' => $devis->nombre_cartons,
            'prix' => array(
                'standard' => $devis->prix_standard,
                'complet' => $devis->prix_complet
            ),
            'statut' => $devis->statut,
            'date_creation' => $devis->date_creation
        );
        
        return $summary;
    }
    
    public static function search_devis($search_term) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE numero_devis LIKE %s 
            OR nom_client LIKE %s 
            OR email_client LIKE %s 
            OR telephone_client LIKE %s
            ORDER BY date_creation DESC 
            LIMIT 50",
            $search_term, $search_term, $search_term, $search_term
        ));
    }
    
    public static function get_devis_stats() {
        $stats = array(
            'total' => self::count_devis(),
            'en_attente' => self::count_devis('en_attente'),
            'accepte' => self::count_devis('accepte'),
            'refuse' => self::count_devis('refuse'),
            'annule' => self::count_devis('annule')
        );
        
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        $stats['montant_total'] = $wpdb->get_var(
            "SELECT SUM(prix_standard) FROM $table WHERE statut = 'accepte'"
        );
        
        $stats['volume_total'] = $wpdb->get_var(
            "SELECT SUM(volume_total) FROM $table"
        );
        
        return $stats;
    }
    
    public static function get_recent_devis($limit = 10) {
        return Novalia_Database::get_all_devis(null, $limit, 0);
    }
    
    public static function format_date($date) {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = strtotime($date);
        return date('d.m.Y', $timestamp);
    }
    
    public static function format_datetime($datetime) {
        if (empty($datetime)) {
            return '';
        }
        
        $timestamp = strtotime($datetime);
        return date('d.m.Y H:i', $timestamp);
    }
}