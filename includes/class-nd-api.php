<?php
/**
 * Gestion de l'API REST WordPress
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_API {
    
    /**
     * Namespace de l'API
     */
    private $namespace = 'novalia-devis/v1';
    
    /**
     * Enregistrement des routes de l'API
     */
    public function register_routes() {
        
        // Route : Calcul de distance entre deux adresses
        register_rest_route($this->namespace, '/distance', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_distance'],
            'permission_callback' => '__return_true',
            'args' => [
                'address_from' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'address_to' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // Route : Autocomplétion d'adresses
        register_rest_route($this->namespace, '/autocomplete', [
            'methods' => 'GET',
            'callback' => [$this, 'autocomplete_address'],
            'permission_callback' => '__return_true',
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // Route : Récupération des objets actifs
        register_rest_route($this->namespace, '/items', [
            'methods' => 'GET',
            'callback' => [$this, 'get_items'],
            'permission_callback' => '__return_true'
        ]);
        
        // Route : Calcul du prix estimé
        register_rest_route($this->namespace, '/calculate', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_price'],
            'permission_callback' => '__return_true',
            'args' => [
                'distance' => [
                    'required' => true,
                    'type' => 'number'
                ],
                'volume' => [
                    'required' => true,
                    'type' => 'number'
                ]
            ]
        ]);
        
        // Route : Création d'un devis
        register_rest_route($this->namespace, '/quote', [
            'methods' => 'POST',
            'callback' => [$this, 'create_quote'],
            'permission_callback' => '__return_true'
        ]);
        
        // Route : Récupération d'un devis
        register_rest_route($this->namespace, '/quote/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_quote'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer'
                ]
            ]
        ]);
    }
    
    /**
     * Calcul de distance entre deux adresses via API Nominatim + OSRM
     */
    public function calculate_distance($request) {
        $address_from = $request->get_param('address_from');
        $address_to = $request->get_param('address_to');
        
        // Géocodage des adresses
        $coords_from = $this->geocode_address($address_from);
        $coords_to = $this->geocode_address($address_to);
        
        if (!$coords_from || !$coords_to) {
            return new WP_Error(
                'geocoding_failed',
                'Impossible de localiser une ou plusieurs adresses',
                ['status' => 400]
            );
        }
        
        // Calcul de la distance par itinéraire routier avec OSRM
        $route_distance = $this->get_route_distance($coords_from, $coords_to);
        
        // Si l'API échoue, utiliser la distance à vol d'oiseau
        if (!$route_distance) {
            $route_distance = $this->haversine_distance(
                $coords_from['lat'],
                $coords_from['lon'],
                $coords_to['lat'],
                $coords_to['lon']
            );
        }
        
        return rest_ensure_response([
            'success' => true,
            'distance' => round($route_distance, 2),
            'from' => $coords_from,
            'to' => $coords_to,
            'method' => $route_distance > 0 ? 'route' : 'haversine'
        ]);
    }
    
    /**
     * Calcul de distance par itinéraire routier avec OSRM
     */
    private function get_route_distance($from, $to) {
        $url = sprintf(
            'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s?overview=false',
            $from['lon'],
            $from['lat'],
            $to['lon'],
            $to['lat']
        );
        
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'NovaliaDevis/1.0 (Geneve, Suisse)'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['routes'][0]['distance'])) {
            // OSRM retourne en mètres, on convertit en km
            return $data['routes'][0]['distance'] / 1000;
        }
        
        return false;
    }
    
    /**
     * Autocomplétion d'adresses via Nominatim
     */
    public function autocomplete_address($request) {
        $query = $request->get_param('query');
        
        if (strlen($query) < 3) {
            return rest_ensure_response([
                'success' => false,
                'suggestions' => []
            ]);
        }
        
        // Appel à l'API Nominatim avec focus sur la Suisse
        $url = sprintf(
            'https://nominatim.openstreetmap.org/search?format=json&q=%s&countrycodes=ch&limit=10&addressdetails=1',
            urlencode($query)
        );
        
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'NovaliaDevis/1.0 (Geneve, Suisse)'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                'Erreur lors de la recherche d\'adresses',
                ['status' => 500]
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $suggestions = [];
        
        if (!empty($data)) {
            foreach ($data as $item) {
                $suggestions[] = [
                    'label' => $item['display_name'],
                    'value' => $item['display_name'],
                    'lat' => $item['lat'],
                    'lon' => $item['lon']
                ];
            }
        }
        
        return rest_ensure_response([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }
    
    /**
     * Récupération des objets de déménagement
     */
    public function get_items($request) {
        $items = ND_Items::get_items_by_category(true);
        
        return rest_ensure_response([
            'success' => true,
            'items' => $items
        ]);
    }
    
    /**
     * Calcul du prix estimé
     */
    public function calculate_price($request) {
        $distance = floatval($request->get_param('distance'));
        $volume = floatval($request->get_param('volume'));
        $floors_from = intval($request->get_param('floors_from') ?? 0);
        $floors_to = intval($request->get_param('floors_to') ?? 0);
        $has_elevator_from = filter_var($request->get_param('has_elevator_from'), FILTER_VALIDATE_BOOLEAN);
        $has_elevator_to = filter_var($request->get_param('has_elevator_to'), FILTER_VALIDATE_BOOLEAN);
        $need_packing = filter_var($request->get_param('need_packing'), FILTER_VALIDATE_BOOLEAN);
        $need_insurance = filter_var($request->get_param('need_insurance'), FILTER_VALIDATE_BOOLEAN);
        
        // Validation
        if ($distance <= 0 || $volume <= 0) {
            return new WP_Error(
                'invalid_params',
                'Distance et volume doivent être supérieurs à 0',
                ['status' => 400]
            );
        }
        
        // Calcul
        $calculation = ND_Pricing::calculate_quote([
            'distance' => $distance,
            'volume' => $volume,
            'floors_from' => $floors_from,
            'floors_to' => $floors_to,
            'has_elevator_from' => $has_elevator_from,
            'has_elevator_to' => $has_elevator_to,
            'need_packing' => $need_packing,
            'need_insurance' => $need_insurance
        ]);
        
        return rest_ensure_response([
            'success' => true,
            'calculation' => $calculation
        ]);
    }
    
    /**
     * Création d'un devis complet
     */
    public function create_quote($request) {
        // Récupération des paramètres
        $data = [
            'customer_name' => sanitize_text_field($request->get_param('customer_name')),
            'customer_firstname' => sanitize_text_field($request->get_param('customer_firstname')),
            'customer_email' => sanitize_email($request->get_param('customer_email')),
            'customer_phone' => sanitize_text_field($request->get_param('customer_phone')),
            'address_from' => sanitize_textarea_field($request->get_param('address_from')),
            'address_to' => sanitize_textarea_field($request->get_param('address_to')),
            'distance' => floatval($request->get_param('distance')),
            'total_volume' => floatval($request->get_param('total_volume')),
            'items' => $request->get_param('items'),
            'floors_from' => intval($request->get_param('floors_from') ?? 0),
            'floors_to' => intval($request->get_param('floors_to') ?? 0),
            'has_elevator_from' => filter_var($request->get_param('has_elevator_from'), FILTER_VALIDATE_BOOLEAN),
            'has_elevator_to' => filter_var($request->get_param('has_elevator_to'), FILTER_VALIDATE_BOOLEAN),
            'need_packing' => filter_var($request->get_param('need_packing'), FILTER_VALIDATE_BOOLEAN),
            'need_insurance' => filter_var($request->get_param('need_insurance'), FILTER_VALIDATE_BOOLEAN)
        ];
        
        // Création du devis
        $result = ND_Quotes::create_quote($data);
        
        if (!$result['success']) {
            return new WP_Error(
                'quote_creation_failed',
                implode(', ', $result['errors']),
                ['status' => 400]
            );
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Devis créé avec succès',
            'quote_id' => $result['quote_id'],
            'quote_number' => $result['quote_number']
        ]);
    }
    
    /**
     * Récupération d'un devis
     */
    public function get_quote($request) {
        $quote_id = intval($request->get_param('id'));
        
        $quote = ND_Quotes::get_quote_with_items($quote_id);
        
        if (!$quote) {
            return new WP_Error(
                'quote_not_found',
                'Devis introuvable',
                ['status' => 404]
            );
        }
        
        return rest_ensure_response([
            'success' => true,
            'quote' => $quote
        ]);
    }
    
    /**
     * Géocodage d'une adresse via Nominatim (focus Suisse)
     */
    private function geocode_address($address) {
        // Vérification du cache
        $cache_key = 'nd_geocode_' . md5($address);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Appel API avec focus sur la Suisse
        $url = sprintf(
            'https://nominatim.openstreetmap.org/search?format=json&q=%s&countrycodes=ch&limit=1&addressdetails=1',
            urlencode($address)
        );
        
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'NovaliaDevis/1.0 (Geneve, Suisse)'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data)) {
            return false;
        }
        
        $coords = [
            'lat' => floatval($data[0]['lat']),
            'lon' => floatval($data[0]['lon']),
            'display_name' => $data[0]['display_name']
        ];
        
        // Mise en cache pour 30 jours
        set_transient($cache_key, $coords, 30 * DAY_IN_SECONDS);
        
        return $coords;
    }
    
    /**
     * Calcul de distance avec la formule de Haversine (en km)
     */
    private function haversine_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Rayon de la Terre en km
        
        $lat1_rad = deg2rad($lat1);
        $lat2_rad = deg2rad($lat2);
        $delta_lat = deg2rad($lat2 - $lat1);
        $delta_lon = deg2rad($lon2 - $lon1);
        
        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) *
             sin($delta_lon / 2) * sin($delta_lon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        $distance = $earth_radius * $c;
        
        return $distance;
    }
}