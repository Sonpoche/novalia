<?php
/**
 * Calculateur de devis
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-calculator.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Calculator {
    
    /**
     * Calculer le devis complet
     */
    public function calculate($data) {
        // Récupérer les paramètres
        $settings = get_option('devis_demenagement_settings');
        
        // Calculer le volume total
        $volume_total = $this->calculate_volume($data['objets']);
        
        // Ajouter le volume personnalisé si présent
        if (isset($data['volume_custom']) && !empty($data['volume_custom'])) {
            $volume_total += floatval($data['volume_custom']);
        }
        
        // Calculer la distance
        $distance = 0;
        if (!empty($data['adresse_depart']) && !empty($data['adresse_arrivee'])) {
            $distance = $this->calculate_distance($data['adresse_depart'], $data['adresse_arrivee']);
        }
        
        // Calculer les prix
        $prix_volume = $volume_total * floatval($settings['price_per_m3']);
        $prix_distance = $distance * floatval($settings['price_per_km']);
        
        // Prix supplémentaires (étages, etc.)
        $prix_supplements = 0;
        if (isset($data['etages_depart']) && intval($data['etages_depart']) > 0) {
            $prix_supplements += intval($data['etages_depart']) * 30; // 30€ par étage
        }
        if (isset($data['etages_arrivee']) && intval($data['etages_arrivee']) > 0) {
            $prix_supplements += intval($data['etages_arrivee']) * 30;
        }
        
        // Prix total
        $prix_total = $prix_volume + $prix_distance + $prix_supplements;
        
        // Appliquer le prix minimum
        if ($prix_total < floatval($settings['minimum_price'])) {
            $prix_total = floatval($settings['minimum_price']);
        }
        
        return array(
            'volume_total' => round($volume_total, 2),
            'distance_km' => round($distance, 2),
            'prix_volume' => round($prix_volume, 2),
            'prix_distance' => round($prix_distance, 2),
            'prix_supplements' => round($prix_supplements, 2),
            'prix_total' => round($prix_total, 2),
            'details' => array(
                'price_per_m3' => floatval($settings['price_per_m3']),
                'price_per_km' => floatval($settings['price_per_km'])
            )
        );
    }
    
    /**
     * Calculer le volume total à partir des objets sélectionnés
     */
    public function calculate_volume($objets) {
        global $wpdb;
        $table_objets = $wpdb->prefix . 'devis_objets';
        
        $volume_total = 0;
        
        if (empty($objets) || !is_array($objets)) {
            return 0;
        }
        
        foreach ($objets as $objet_id => $quantite) {
            $quantite = intval($quantite);
            if ($quantite <= 0) {
                continue;
            }
            
            // Récupérer le volume de l'objet depuis la BDD
            $objet = $wpdb->get_row(
                $wpdb->prepare("SELECT volume_m3 FROM $table_objets WHERE id = %d", $objet_id),
                ARRAY_A
            );
            
            if ($objet) {
                $volume_total += floatval($objet['volume_m3']) * $quantite;
            }
        }
        
        return $volume_total;
    }
    
    /**
     * Calculer la distance entre deux adresses
     * Utilise l'API OpenRouteService (gratuite)
     */
    public function calculate_distance($adresse_depart, $adresse_arrivee) {
        // Vérifier si on a une clé API configurée
        $settings = get_option('devis_demenagement_settings');
        $api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        
        // Si pas de clé API, utiliser calcul approximatif
        if (empty($api_key)) {
            return $this->calculate_distance_approximate($adresse_depart, $adresse_arrivee);
        }
        
        // Géocoder les adresses (convertir en coordonnées GPS)
        $coords_depart = $this->geocode_address($adresse_depart, $api_key);
        $coords_arrivee = $this->geocode_address($adresse_arrivee, $api_key);
        
        if (!$coords_depart || !$coords_arrivee) {
            return $this->calculate_distance_approximate($adresse_depart, $adresse_arrivee);
        }
        
        // Calculer la distance routière via OpenRouteService
        $url = 'https://api.openrouteservice.org/v2/directions/driving-car';
        
        $args = array(
            'headers' => array(
                'Authorization' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'coordinates' => array(
                    array($coords_depart['lon'], $coords_depart['lat']),
                    array($coords_arrivee['lon'], $coords_arrivee['lat'])
                )
            )),
            'timeout' => 15
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return $this->calculate_distance_approximate($adresse_depart, $adresse_arrivee);
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['routes'][0]['summary']['distance'])) {
            // Distance en mètres, convertir en km
            $distance_km = $body['routes'][0]['summary']['distance'] / 1000;
            return $distance_km;
        }
        
        return $this->calculate_distance_approximate($adresse_depart, $adresse_arrivee);
    }
    
    /**
     * Géocoder une adresse (convertir en coordonnées GPS)
     */
    private function geocode_address($address, $api_key) {
        $url = 'https://api.openrouteservice.org/geocode/search';
        
        $args = array(
            'headers' => array(
                'Authorization' => $api_key
            ),
            'timeout' => 15
        );
        
        $url .= '?text=' . urlencode($address);
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['features'][0]['geometry']['coordinates'])) {
            $coords = $body['features'][0]['geometry']['coordinates'];
            return array(
                'lon' => $coords[0],
                'lat' => $coords[1]
            );
        }
        
        return false;
    }
    
    /**
     * Calcul approximatif de distance (à vol d'oiseau * 1.3)
     * Utilisé en fallback si pas d'API ou erreur
     */
    private function calculate_distance_approximate($adresse_depart, $adresse_arrivee) {
        // Essayer d'utiliser Nominatim (OpenStreetMap) - gratuit sans clé API
        $coords_depart = $this->geocode_nominatim($adresse_depart);
        $coords_arrivee = $this->geocode_nominatim($adresse_arrivee);
        
        if (!$coords_depart || !$coords_arrivee) {
            // Si échec total, retourner 0 (l'utilisateur devra entrer manuellement)
            return 0;
        }
        
        // Calcul à vol d'oiseau (formule de Haversine)
        $earth_radius = 6371; // Rayon de la Terre en km
        
        $lat1 = deg2rad($coords_depart['lat']);
        $lon1 = deg2rad($coords_depart['lon']);
        $lat2 = deg2rad($coords_arrivee['lat']);
        $lon2 = deg2rad($coords_arrivee['lon']);
        
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        $distance_vol_oiseau = $earth_radius * $c;
        
        // Multiplier par 1.3 pour approximer la distance routière
        return $distance_vol_oiseau * 1.3;
    }
    
    /**
     * Géocoder avec Nominatim (OpenStreetMap) - Gratuit, pas de clé API
     */
    private function geocode_nominatim($address) {
        $url = 'https://nominatim.openstreetmap.org/search';
        
        $args = array(
            'headers' => array(
                'User-Agent' => 'DevisDemenagement-WordPress-Plugin'
            ),
            'timeout' => 15
        );
        
        $url .= '?q=' . urlencode($address) . '&format=json&limit=1';
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body[0]['lat']) && isset($body[0]['lon'])) {
            return array(
                'lat' => floatval($body[0]['lat']),
                'lon' => floatval($body[0]['lon'])
            );
        }
        
        return false;
    }
    
    /**
     * Obtenir le détail des objets sélectionnés pour l'affichage
     */
    public function get_objets_details($objets_data) {
        global $wpdb;
        $table_objets = $wpdb->prefix . 'devis_objets';
        
        $details = array();
        
        foreach ($objets_data as $objet_id => $quantite) {
            $quantite = intval($quantite);
            if ($quantite <= 0) {
                continue;
            }
            
            $objet = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_objets WHERE id = %d", $objet_id),
                ARRAY_A
            );
            
            if ($objet) {
                $details[] = array(
                    'nom' => $objet['nom'],
                    'quantite' => $quantite,
                    'volume_unitaire' => floatval($objet['volume_m3']),
                    'volume_total' => floatval($objet['volume_m3']) * $quantite,
                    'categorie' => $objet['categorie']
                );
            }
        }
        
        return $details;
    }
}