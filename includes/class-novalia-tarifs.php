<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Tarifs {
    
    public static function insert_default_tarifs() {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_tarifs';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) {
            return;
        }
        
        $default_tarifs = array(
            array(
                'type_tarif' => 'prix_km',
                'valeur' => 2.50,
                'unite' => 'CHF/km',
                'description' => 'Prix par kilomètre parcouru'
            ),
            array(
                'type_tarif' => 'prix_m3',
                'valeur' => 80.00,
                'unite' => 'CHF/m³',
                'description' => 'Prix par mètre cube de volume'
            ),
            array(
                'type_tarif' => 'prix_etage_sans_ascenseur',
                'valeur' => 50.00,
                'unite' => 'CHF/étage',
                'description' => 'Supplément par étage sans ascenseur'
            ),
            array(
                'type_tarif' => 'prix_base',
                'valeur' => 200.00,
                'unite' => 'CHF',
                'description' => 'Prix de base du déménagement'
            ),
            array(
                'type_tarif' => 'prix_carton_emballage',
                'valeur' => 15.00,
                'unite' => 'CHF/carton',
                'description' => 'Prix emballage par carton (déménagement complet)'
            ),
            array(
                'type_tarif' => 'prix_fourniture_carton',
                'valeur' => 5.00,
                'unite' => 'CHF/carton',
                'description' => 'Prix fourniture carton (déménagement complet)'
            ),
            array(
                'type_tarif' => 'majoration_weekend',
                'valeur' => 20.00,
                'unite' => '%',
                'description' => 'Majoration weekend (samedi/dimanche)'
            ),
            array(
                'type_tarif' => 'reduction_volume',
                'valeur' => 5.00,
                'unite' => '%',
                'description' => 'Réduction si volume > 50m³'
            ),
            array(
                'type_tarif' => 'etages_depart',
                'valeur' => 0.00,
                'unite' => 'nombre',
                'description' => 'Nombre d\'étages au départ (sans ascenseur)'
            ),
            array(
                'type_tarif' => 'etages_arrivee',
                'valeur' => 0.00,
                'unite' => 'nombre',
                'description' => 'Nombre d\'étages à l\'arrivée (sans ascenseur)'
            )
        );
        
        foreach ($default_tarifs as $tarif) {
            $wpdb->insert($table, $tarif);
        }
    }
    
    public static function get_all_tarifs() {
        return Novalia_Database::get_tarifs();
    }
    
    public static function get_tarif($type) {
        return Novalia_Database::get_tarif($type);
    }
    
    public static function update_tarif($type, $valeur) {
        return Novalia_Database::update_tarif($type, $valeur);
    }
    
    public static function calculate_prix_standard($data) {
        $tarifs = self::get_all_tarifs();
        
        $distance = floatval($data['distance']);
        $volume = floatval($data['volume']);
        $etages_depart = intval($data['etages_depart']);
        $etages_arrivee = intval($data['etages_arrivee']);
        $date_demenagement = $data['date_demenagement'];
        
        // Prix de base
        $prix = floatval($tarifs['prix_base']['valeur']);
        
        // Prix kilométrique
        $prix += $distance * floatval($tarifs['prix_km']['valeur']);
        
        // Prix au volume
        $prix += $volume * floatval($tarifs['prix_m3']['valeur']);
        
        // Supplément étages sans ascenseur
        $total_etages = $etages_depart + $etages_arrivee;
        if ($total_etages > 0) {
            $prix += $total_etages * floatval($tarifs['prix_etage_sans_ascenseur']['valeur']);
        }
        
        // Réduction si volume important
        if ($volume > 50) {
            $reduction = floatval($tarifs['reduction_volume']['valeur']) / 100;
            $prix = $prix * (1 - $reduction);
        }
        
        // Majoration weekend
        if (!empty($date_demenagement)) {
            $timestamp = strtotime($date_demenagement);
            $jour_semaine = date('N', $timestamp);
            if ($jour_semaine == 6 || $jour_semaine == 7) {
                $majoration = floatval($tarifs['majoration_weekend']['valeur']) / 100;
                $prix = $prix * (1 + $majoration);
            }
        }
        
        return round($prix, 2);
    }
    
    public static function calculate_prix_complet($data) {
        $tarifs = self::get_all_tarifs();
        
        // Prix standard
        $prix = self::calculate_prix_standard($data);
        
        // Ajout emballage cartons
        $nombre_cartons = intval($data['nombre_cartons']);
        if ($nombre_cartons > 0) {
            $prix_emballage = floatval($tarifs['prix_carton_emballage']['valeur']);
            $prix_fourniture = floatval($tarifs['prix_fourniture_carton']['valeur']);
            $prix += $nombre_cartons * ($prix_emballage + $prix_fourniture);
        }
        
        return round($prix, 2);
    }
    
    public static function calculate_both_prices($data) {
        return array(
            'standard' => self::calculate_prix_standard($data),
            'complet' => self::calculate_prix_complet($data)
        );
    }
    
    public static function get_tarif_detail($data) {
        $tarifs = self::get_all_tarifs();
        
        $distance = floatval($data['distance']);
        $volume = floatval($data['volume']);
        $etages_depart = intval($data['etages_depart']);
        $etages_arrivee = intval($data['etages_arrivee']);
        $nombre_cartons = intval($data['nombre_cartons']);
        
        $detail = array(
            'prix_base' => floatval($tarifs['prix_base']['valeur']),
            'prix_distance' => $distance * floatval($tarifs['prix_km']['valeur']),
            'prix_volume' => $volume * floatval($tarifs['prix_m3']['valeur']),
            'prix_etages' => ($etages_depart + $etages_arrivee) * floatval($tarifs['prix_etage_sans_ascenseur']['valeur']),
            'prix_emballage' => $nombre_cartons * floatval($tarifs['prix_carton_emballage']['valeur']),
            'prix_fourniture' => $nombre_cartons * floatval($tarifs['prix_fourniture_carton']['valeur'])
        );
        
        return $detail;
    }
    
    public static function format_prix($prix) {
        return number_format($prix, 2, '.', '\'') . ' CHF';
    }
    
    public static function get_tarif_value($type) {
        $tarif = self::get_tarif($type);
        return $tarif ? floatval($tarif->valeur) : 0;
    }
}