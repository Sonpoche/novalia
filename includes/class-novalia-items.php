<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Items {
    
    public static function insert_default_items() {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) {
            return;
        }
        
        $default_items = array(
            // Salon
            array('nom' => 'Canapé 2 places', 'categorie' => 'Salon', 'volume' => 1.500),
            array('nom' => 'Canapé 3 places', 'categorie' => 'Salon', 'volume' => 2.000),
            array('nom' => 'Fauteuil', 'categorie' => 'Salon', 'volume' => 0.800),
            array('nom' => 'Table basse', 'categorie' => 'Salon', 'volume' => 0.400),
            array('nom' => 'Meuble TV', 'categorie' => 'Salon', 'volume' => 0.600),
            array('nom' => 'Télévision', 'categorie' => 'Salon', 'volume' => 0.200),
            array('nom' => 'Bibliothèque', 'categorie' => 'Salon', 'volume' => 1.200),
            array('nom' => 'Étagère', 'categorie' => 'Salon', 'volume' => 0.500),
            array('nom' => 'Lampadaire', 'categorie' => 'Salon', 'volume' => 0.150),
            array('nom' => 'Plante', 'categorie' => 'Salon', 'volume' => 0.100),
            array('nom' => 'Tapis', 'categorie' => 'Salon', 'volume' => 0.200),
            
            // Salle à manger
            array('nom' => 'Table à manger 4 places', 'categorie' => 'Salle à manger', 'volume' => 1.000),
            array('nom' => 'Table à manger 6 places', 'categorie' => 'Salle à manger', 'volume' => 1.500),
            array('nom' => 'Chaise', 'categorie' => 'Salle à manger', 'volume' => 0.300),
            array('nom' => 'Buffet', 'categorie' => 'Salle à manger', 'volume' => 1.500),
            array('nom' => 'Vaisselier', 'categorie' => 'Salle à manger', 'volume' => 1.800),
            array('nom' => 'Desserte', 'categorie' => 'Salle à manger', 'volume' => 0.400),
            
            // Cuisine
            array('nom' => 'Réfrigérateur', 'categorie' => 'Cuisine', 'volume' => 1.000),
            array('nom' => 'Congélateur', 'categorie' => 'Cuisine', 'volume' => 0.800),
            array('nom' => 'Four', 'categorie' => 'Cuisine', 'volume' => 0.300),
            array('nom' => 'Micro-ondes', 'categorie' => 'Cuisine', 'volume' => 0.100),
            array('nom' => 'Lave-vaisselle', 'categorie' => 'Cuisine', 'volume' => 0.600),
            array('nom' => 'Machine à café', 'categorie' => 'Cuisine', 'volume' => 0.050),
            array('nom' => 'Petits électroménagers', 'categorie' => 'Cuisine', 'volume' => 0.200),
            array('nom' => 'Table de cuisine', 'categorie' => 'Cuisine', 'volume' => 0.600),
            array('nom' => 'Chaise de cuisine', 'categorie' => 'Cuisine', 'volume' => 0.250),
            array('nom' => 'Meuble de cuisine', 'categorie' => 'Cuisine', 'volume' => 0.800),
            array('nom' => 'Carton vaisselle', 'categorie' => 'Cuisine', 'volume' => 0.080),
            
            // Chambre principale
            array('nom' => 'Lit double 160x200', 'categorie' => 'Chambre principale', 'volume' => 2.000),
            array('nom' => 'Lit double 180x200', 'categorie' => 'Chambre principale', 'volume' => 2.500),
            array('nom' => 'Matelas double', 'categorie' => 'Chambre principale', 'volume' => 1.000),
            array('nom' => 'Sommier double', 'categorie' => 'Chambre principale', 'volume' => 1.000),
            array('nom' => 'Armoire 2 portes', 'categorie' => 'Chambre principale', 'volume' => 2.000),
            array('nom' => 'Armoire 3 portes', 'categorie' => 'Chambre principale', 'volume' => 3.000),
            array('nom' => 'Commode', 'categorie' => 'Chambre principale', 'volume' => 0.800),
            array('nom' => 'Table de chevet', 'categorie' => 'Chambre principale', 'volume' => 0.200),
            array('nom' => 'Coiffeuse', 'categorie' => 'Chambre principale', 'volume' => 0.600),
            array('nom' => 'Miroir', 'categorie' => 'Chambre principale', 'volume' => 0.150),
            
            // Chambre enfant
            array('nom' => 'Lit simple 90x200', 'categorie' => 'Chambre enfant', 'volume' => 1.000),
            array('nom' => 'Lit superposé', 'categorie' => 'Chambre enfant', 'volume' => 2.200),
            array('nom' => 'Matelas simple', 'categorie' => 'Chambre enfant', 'volume' => 0.500),
            array('nom' => 'Bureau enfant', 'categorie' => 'Chambre enfant', 'volume' => 0.600),
            array('nom' => 'Chaise de bureau', 'categorie' => 'Chambre enfant', 'volume' => 0.300),
            array('nom' => 'Armoire enfant', 'categorie' => 'Chambre enfant', 'volume' => 1.200),
            array('nom' => 'Commode enfant', 'categorie' => 'Chambre enfant', 'volume' => 0.500),
            array('nom' => 'Coffre à jouets', 'categorie' => 'Chambre enfant', 'volume' => 0.300),
            array('nom' => 'Étagère murale', 'categorie' => 'Chambre enfant', 'volume' => 0.200),
            
            // Bureau
            array('nom' => 'Bureau', 'categorie' => 'Bureau', 'volume' => 1.000),
            array('nom' => 'Chaise de bureau', 'categorie' => 'Bureau', 'volume' => 0.400),
            array('nom' => 'Caisson de bureau', 'categorie' => 'Bureau', 'volume' => 0.300),
            array('nom' => 'Bibliothèque bureau', 'categorie' => 'Bureau', 'volume' => 1.500),
            array('nom' => 'Ordinateur de bureau', 'categorie' => 'Bureau', 'volume' => 0.200),
            array('nom' => 'Imprimante', 'categorie' => 'Bureau', 'volume' => 0.100),
            array('nom' => 'Carton archives', 'categorie' => 'Bureau', 'volume' => 0.080),
            
            // Salle de bain
            array('nom' => 'Machine à laver', 'categorie' => 'Salle de bain', 'volume' => 0.800),
            array('nom' => 'Sèche-linge', 'categorie' => 'Salle de bain', 'volume' => 0.800),
            array('nom' => 'Meuble de salle de bain', 'categorie' => 'Salle de bain', 'volume' => 0.600),
            array('nom' => 'Armoire de toilette', 'categorie' => 'Salle de bain', 'volume' => 0.200),
            array('nom' => 'Panier à linge', 'categorie' => 'Salle de bain', 'volume' => 0.100),
            
            // Entrée / Couloir
            array('nom' => 'Meuble à chaussures', 'categorie' => 'Entrée / Couloir', 'volume' => 0.400),
            array('nom' => 'Porte-manteau', 'categorie' => 'Entrée / Couloir', 'volume' => 0.200),
            array('nom' => 'Console', 'categorie' => 'Entrée / Couloir', 'volume' => 0.300),
            array('nom' => 'Miroir d\'entrée', 'categorie' => 'Entrée / Couloir', 'volume' => 0.100),
            
            // Cave / Garage
            array('nom' => 'Étagère de rangement', 'categorie' => 'Cave / Garage', 'volume' => 0.800),
            array('nom' => 'Vélo', 'categorie' => 'Cave / Garage', 'volume' => 0.500),
            array('nom' => 'Trottinette', 'categorie' => 'Cave / Garage', 'volume' => 0.200),
            array('nom' => 'Outils de jardinage', 'categorie' => 'Cave / Garage', 'volume' => 0.300),
            array('nom' => 'Tondeuse à gazon', 'categorie' => 'Cave / Garage', 'volume' => 0.600),
            array('nom' => 'Échelle', 'categorie' => 'Cave / Garage', 'volume' => 0.400),
            array('nom' => 'Carton divers', 'categorie' => 'Cave / Garage', 'volume' => 0.080),
            array('nom' => 'Valise', 'categorie' => 'Cave / Garage', 'volume' => 0.150),
            
            // Extérieur
            array('nom' => 'Table de jardin', 'categorie' => 'Extérieur', 'volume' => 0.800),
            array('nom' => 'Chaise de jardin', 'categorie' => 'Extérieur', 'volume' => 0.200),
            array('nom' => 'Parasol', 'categorie' => 'Extérieur', 'volume' => 0.300),
            array('nom' => 'Barbecue', 'categorie' => 'Extérieur', 'volume' => 0.500),
            array('nom' => 'Salon de jardin', 'categorie' => 'Extérieur', 'volume' => 2.000),
            array('nom' => 'Bac à fleurs', 'categorie' => 'Extérieur', 'volume' => 0.200),
            
            // Cartons standards
            array('nom' => 'Carton standard', 'categorie' => 'Cartons', 'volume' => 0.080),
            array('nom' => 'Carton livre', 'categorie' => 'Cartons', 'volume' => 0.050),
            array('nom' => 'Carton penderie', 'categorie' => 'Cartons', 'volume' => 0.300),
            array('nom' => 'Carton fragile', 'categorie' => 'Cartons', 'volume' => 0.080),
        );
        
        foreach ($default_items as $item) {
            $wpdb->insert($table, $item);
        }
    }
    
    public static function get_all_items() {
        return Novalia_Database::get_items();
    }
    
    public static function get_items_by_category() {
        return Novalia_Database::get_items_by_category();
    }
    
    public static function get_categories() {
        $items = self::get_items_by_category();
        return array_keys($items);
    }
    
    public static function add_item($nom, $categorie, $volume) {
        return Novalia_Database::insert_item(array(
            'nom' => $nom,
            'categorie' => $categorie,
            'volume' => $volume
        ));
    }
    
    public static function update_item($id, $nom, $categorie, $volume) {
        return Novalia_Database::update_item($id, array(
            'nom' => $nom,
            'categorie' => $categorie,
            'volume' => $volume
        ));
    }
    
    public static function delete_item($id) {
        return Novalia_Database::delete_item($id);
    }
    
    public static function get_item($id) {
        return Novalia_Database::get_item($id);
    }
    
    public static function calculate_total_volume($items) {
        $total = 0;
        foreach ($items as $item) {
            $volume = floatval($item['volume']);
            $quantite = intval($item['quantite']);
            $total += ($volume * $quantite);
        }
        return round($total, 3);
    }
    
    public static function group_items_by_category($items) {
        $grouped = array();
        
        foreach ($items as $item) {
            $categorie = $item['categorie'];
            if (!isset($grouped[$categorie])) {
                $grouped[$categorie] = array();
            }
            $grouped[$categorie][] = $item;
        }
        
        return $grouped;
    }
}