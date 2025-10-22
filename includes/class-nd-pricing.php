<?php
/**
 * Gestion des tarifs et calculs de prix
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Pricing {
    
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
        // Validation et nettoyage des données
        $clean_settings = [];
        
        foreach ($settings as $key => $value) {
            $clean_settings[$key] = floatval($value);
        }
        
        return update_option('nd_pricing', $clean_settings);
    }
    
    /**
     * Calcul du prix total d'un déménagement
     */
    public static function calculate_quote($data) {
        $pricing = self::get_pricing_settings();
        
        // Données de base
        $distance = floatval($data['distance'] ?? 0);
        $volume = floatval($data['volume'] ?? 0);
        
        // Options supplémentaires
        $floors_from = intval($data['floors_from'] ?? 0);
        $floors_to = intval($data['floors_to'] ?? 0);
        $has_elevator_from = isset($data['has_elevator_from']) && $data['has_elevator_from'];
        $has_elevator_to = isset($data['has_elevator_to']) && $data['has_elevator_to'];
        $need_packing = isset($data['need_packing']) && $data['need_packing'];
        $need_insurance = isset($data['need_insurance']) && $data['need_insurance'];
        
        // Calculs de base
        $price_distance = $distance * $pricing['price_per_km'];
        $price_volume = $volume * $pricing['price_per_m3'];
        
        // Frais d'étages
        $price_floors = 0;
        
        // Départ sans ascenseur
        if ($floors_from > 0 && !$has_elevator_from) {
            $price_floors += $floors_from * $pricing['fee_floor'];
        }
        
        // Arrivée sans ascenseur
        if ($floors_to > 0 && !$has_elevator_to) {
            $price_floors += $floors_to * $pricing['fee_floor'];
        }
        
        // Frais d'emballage
        $price_packing = $need_packing ? ($volume * $pricing['fee_packing']) : 0;
        
        // Frais d'assurance
        $price_insurance = $need_insurance ? ($volume * $pricing['fee_insurance']) : 0;
        
        // Frais fixes
        $fixed_fee = $pricing['fixed_fee'];
        
        // Total
        $subtotal = $price_distance + $price_volume + $price_floors + $price_packing + $price_insurance + $fixed_fee;
        
        // Montant minimum
        $total = max($subtotal, $pricing['min_quote_amount']);
        
        return [
            'breakdown' => [
                'distance' => [
                    'value' => $distance,
                    'unit' => 'km',
                    'rate' => $pricing['price_per_km'],
                    'price' => $price_distance
                ],
                'volume' => [
                    'value' => $volume,
                    'unit' => 'm³',
                    'rate' => $pricing['price_per_m3'],
                    'price' => $price_volume
                ],
                'floors' => [
                    'from' => $floors_from,
                    'to' => $floors_to,
                    'price' => $price_floors
                ],
                'packing' => [
                    'enabled' => $need_packing,
                    'price' => $price_packing
                ],
                'insurance' => [
                    'enabled' => $need_insurance,
                    'price' => $price_insurance
                ],
                'fixed_fee' => $fixed_fee
            ],
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
            'min_amount_applied' => $subtotal < $pricing['min_quote_amount']
        ];
    }
    
    /**
     * Calcul simplifié (sans options)
     */
    public static function calculate_simple($distance, $volume) {
        return self::calculate_quote([
            'distance' => $distance,
            'volume' => $volume
        ]);
    }
    
    /**
     * Estimation rapide en temps réel
     */
    public static function estimate_price($distance, $volume) {
        $pricing = self::get_pricing_settings();
        
        $price = ($distance * $pricing['price_per_km']) + 
                 ($volume * $pricing['price_per_m3']) + 
                 $pricing['fixed_fee'];
        
        return max($price, $pricing['min_quote_amount']);
    }
    
    /**
     * Formatage du prix pour affichage
     */
    public static function format_price($amount, $currency = 'CHF') {
        return number_format($amount, 2, '.', '\'') . ' ' . $currency;
    }
    
    /**
     * Calcul de la TVA
     */
    public static function calculate_tax($amount, $tax_rate = 20) {
        $tax = ($amount * $tax_rate) / 100;
        
        return [
            'amount_ht' => round($amount, 2),
            'tax_rate' => $tax_rate,
            'tax_amount' => round($tax, 2),
            'amount_ttc' => round($amount + $tax, 2)
        ];
    }
    
    /**
     * Appliquer une remise
     */
    public static function apply_discount($amount, $discount_type, $discount_value) {
        if ($discount_type === 'percent') {
            $discount_amount = ($amount * $discount_value) / 100;
        } else {
            $discount_amount = $discount_value;
        }
        
        $new_amount = max($amount - $discount_amount, 0);
        
        return [
            'original_amount' => $amount,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'discount_amount' => round($discount_amount, 2),
            'final_amount' => round($new_amount, 2)
        ];
    }
    
    /**
     * Validation des données de calcul
     */
    public static function validate_calculation_data($data) {
        $errors = [];
        
        // Distance
        if (!isset($data['distance']) || floatval($data['distance']) <= 0) {
            $errors[] = 'La distance doit être supérieure à 0';
        }
        
        // Volume
        if (!isset($data['volume']) || floatval($data['volume']) <= 0) {
            $errors[] = 'Le volume doit être supérieur à 0';
        }
        
        // Étages (optionnel mais doivent être positifs)
        if (isset($data['floors_from']) && intval($data['floors_from']) < 0) {
            $errors[] = 'Le nombre d\'étages de départ ne peut pas être négatif';
        }
        
        if (isset($data['floors_to']) && intval($data['floors_to']) < 0) {
            $errors[] = 'Le nombre d\'étages d\'arrivée ne peut pas être négatif';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Récupération des tranches tarifaires pour affichage
     */
    public static function get_pricing_tiers() {
        $pricing = self::get_pricing_settings();
        
        return [
            [
                'label' => 'Distance',
                'value' => self::format_price($pricing['price_per_km']),
                'unit' => '/ km'
            ],
            [
                'label' => 'Volume',
                'value' => self::format_price($pricing['price_per_m3']),
                'unit' => '/ m³'
            ],
            [
                'label' => 'Étage sans ascenseur',
                'value' => self::format_price($pricing['fee_floor']),
                'unit' => '/ étage'
            ],
            [
                'label' => 'Emballage',
                'value' => self::format_price($pricing['fee_packing']),
                'unit' => '/ m³'
            ],
            [
                'label' => 'Assurance',
                'value' => self::format_price($pricing['fee_insurance']),
                'unit' => '/ m³'
            ],
            [
                'label' => 'Montant minimum',
                'value' => self::format_price($pricing['min_quote_amount']),
                'unit' => ''
            ]
        ];
    }
    
    /**
     * Comparaison de prix entre deux configurations
     */
    public static function compare_quotes($quote1_data, $quote2_data) {
        $quote1 = self::calculate_quote($quote1_data);
        $quote2 = self::calculate_quote($quote2_data);
        
        $difference = $quote2['total'] - $quote1['total'];
        $percentage = ($quote1['total'] > 0) 
            ? (($difference / $quote1['total']) * 100) 
            : 0;
        
        return [
            'quote1_total' => $quote1['total'],
            'quote2_total' => $quote2['total'],
            'difference' => round($difference, 2),
            'percentage' => round($percentage, 2),
            'cheaper' => $difference < 0 ? 'quote2' : 'quote1'
        ];
    }
}