<?php
/**
 * Gestion de la base de données
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Database {
    
    /**
     * Créer les tables nécessaires
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table des objets
        $table_objets = $wpdb->prefix . 'devis_objets';
        $sql_objets = "CREATE TABLE IF NOT EXISTS $table_objets (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nom varchar(100) NOT NULL,
            categorie varchar(50) NOT NULL,
            volume_m3 decimal(5,2) NOT NULL,
            actif tinyint(1) DEFAULT 1,
            ordre int(11) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY categorie (categorie)
        ) $charset_collate;";
        
        // Table de l'historique des devis
        $table_historique = $wpdb->prefix . 'devis_historique';
        $sql_historique = "CREATE TABLE IF NOT EXISTS $table_historique (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP,
            client_nom varchar(100),
            client_email varchar(100) NOT NULL,
            client_telephone varchar(20),
            adresse_depart text NOT NULL,
            adresse_arrivee text NOT NULL,
            distance_km decimal(8,2),
            volume_total_m3 decimal(8,2) NOT NULL,
            prix_volume decimal(10,2),
            prix_distance decimal(10,2),
            prix_supplements decimal(10,2) DEFAULT 0,
            prix_total decimal(10,2) NOT NULL,
            objets_selectionnes longtext,
            notes text,
            statut varchar(20) DEFAULT 'nouveau',
            PRIMARY KEY  (id),
            KEY client_email (client_email),
            KEY date_creation (date_creation)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_objets);
        dbDelta($sql_historique);
    }
    
    /**
     * Insérer les données par défaut (tous les objets avec leurs volumes)
     */
    public static function insert_default_data() {
        global $wpdb;
        
        $table_objets = $wpdb->prefix . 'devis_objets';
        
        // Vérifier si des données existent déjà
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_objets");
        if ($count > 0) {
            return; // Ne pas insérer si déjà des données
        }
        
        // Liste complète des objets avec leurs volumes en m³
        $objets = array(
            // SALON
            array('nom' => 'Bibliothèque 1 porte', 'categorie' => 'Salon', 'volume_m3' => 1.0, 'ordre' => 1),
            array('nom' => 'Bibliothèque 2 portes', 'categorie' => 'Salon', 'volume_m3' => 2.0, 'ordre' => 2),
            array('nom' => 'Bibliothèque 3 portes', 'categorie' => 'Salon', 'volume_m3' => 3.0, 'ordre' => 3),
            array('nom' => 'Bibliothèque 4 portes', 'categorie' => 'Salon', 'volume_m3' => 4.0, 'ordre' => 4),
            array('nom' => 'Bureau', 'categorie' => 'Salon', 'volume_m3' => 1.5, 'ordre' => 5),
            array('nom' => 'Canapé 2 places', 'categorie' => 'Salon', 'volume_m3' => 1.5, 'ordre' => 6),
            array('nom' => 'Canapé 3 places', 'categorie' => 'Salon', 'volume_m3' => 2.0, 'ordre' => 7),
            array('nom' => 'Canapé d\'angle', 'categorie' => 'Salon', 'volume_m3' => 3.5, 'ordre' => 8),
            array('nom' => 'Etagère', 'categorie' => 'Salon', 'volume_m3' => 0.8, 'ordre' => 9),
            array('nom' => 'Fauteuil', 'categorie' => 'Salon', 'volume_m3' => 0.8, 'ordre' => 10),
            array('nom' => 'Grande lampe', 'categorie' => 'Salon', 'volume_m3' => 0.3, 'ordre' => 11),
            array('nom' => 'Guéridon', 'categorie' => 'Salon', 'volume_m3' => 0.5, 'ordre' => 12),
            array('nom' => 'Hifi', 'categorie' => 'Salon', 'volume_m3' => 0.4, 'ordre' => 13),
            array('nom' => 'Imprimante', 'categorie' => 'Salon', 'volume_m3' => 0.2, 'ordre' => 14),
            array('nom' => 'Meuble living 1 porte', 'categorie' => 'Salon', 'volume_m3' => 1.2, 'ordre' => 15),
            array('nom' => 'Meuble living 2 portes', 'categorie' => 'Salon', 'volume_m3' => 2.0, 'ordre' => 16),
            array('nom' => 'Meuble living 3 portes', 'categorie' => 'Salon', 'volume_m3' => 3.0, 'ordre' => 17),
            array('nom' => 'Meuble living 4 portes', 'categorie' => 'Salon', 'volume_m3' => 4.0, 'ordre' => 18),
            array('nom' => 'Meuble TV', 'categorie' => 'Salon', 'volume_m3' => 1.0, 'ordre' => 19),
            array('nom' => 'Petit bureau', 'categorie' => 'Salon', 'volume_m3' => 0.8, 'ordre' => 20),
            array('nom' => 'Piano à queue', 'categorie' => 'Salon', 'volume_m3' => 8.0, 'ordre' => 21),
            array('nom' => 'Piano demi-queue', 'categorie' => 'Salon', 'volume_m3' => 5.0, 'ordre' => 22),
            array('nom' => 'Piano droit', 'categorie' => 'Salon', 'volume_m3' => 3.0, 'ordre' => 23),
            array('nom' => 'Table (grande)', 'categorie' => 'Salon', 'volume_m3' => 1.8, 'ordre' => 24),
            array('nom' => 'Table (moyenne)', 'categorie' => 'Salon', 'volume_m3' => 1.2, 'ordre' => 25),
            array('nom' => 'Table (petite)', 'categorie' => 'Salon', 'volume_m3' => 0.8, 'ordre' => 26),
            array('nom' => 'Table basse', 'categorie' => 'Salon', 'volume_m3' => 0.6, 'ordre' => 27),
            array('nom' => 'Télévision', 'categorie' => 'Salon', 'volume_m3' => 0.3, 'ordre' => 28),
            
            // CHAMBRE
            array('nom' => 'Abat-jour', 'categorie' => 'Chambre', 'volume_m3' => 0.1, 'ordre' => 29),
            array('nom' => 'Armoire 1 porte', 'categorie' => 'Chambre', 'volume_m3' => 2.0, 'ordre' => 30),
            array('nom' => 'Armoire 2 portes', 'categorie' => 'Chambre', 'volume_m3' => 3.5, 'ordre' => 31),
            array('nom' => 'Armoire 3 portes', 'categorie' => 'Chambre', 'volume_m3' => 5.0, 'ordre' => 32),
            array('nom' => 'Armoire 4 portes', 'categorie' => 'Chambre', 'volume_m3' => 6.5, 'ordre' => 33),
            array('nom' => 'Bonnetière', 'categorie' => 'Chambre', 'volume_m3' => 1.5, 'ordre' => 34),
            array('nom' => 'Carton penderie', 'categorie' => 'Chambre', 'volume_m3' => 0.5, 'ordre' => 35),
            array('nom' => 'Coffre à jouets (grand)', 'categorie' => 'Chambre', 'volume_m3' => 0.8, 'ordre' => 36),
            array('nom' => 'Coffre à jouets (moyen)', 'categorie' => 'Chambre', 'volume_m3' => 0.5, 'ordre' => 37),
            array('nom' => 'Coffre à jouets (petit)', 'categorie' => 'Chambre', 'volume_m3' => 0.3, 'ordre' => 38),
            array('nom' => 'Commode 3 tiroirs', 'categorie' => 'Chambre', 'volume_m3' => 1.0, 'ordre' => 39),
            array('nom' => 'Commode 4 tiroirs', 'categorie' => 'Chambre', 'volume_m3' => 1.3, 'ordre' => 40),
            array('nom' => 'Lit simple', 'categorie' => 'Chambre', 'volume_m3' => 1.5, 'ordre' => 41),
            array('nom' => 'Lit double', 'categorie' => 'Chambre', 'volume_m3' => 2.5, 'ordre' => 42),
            array('nom' => 'Matelas simple', 'categorie' => 'Chambre', 'volume_m3' => 0.8, 'ordre' => 43),
            array('nom' => 'Matelas double', 'categorie' => 'Chambre', 'volume_m3' => 1.2, 'ordre' => 44),
            array('nom' => 'Table de chevet', 'categorie' => 'Chambre', 'volume_m3' => 0.3, 'ordre' => 45),
            
            // CUISINE
            array('nom' => 'Chaise', 'categorie' => 'Cuisine', 'volume_m3' => 0.3, 'ordre' => 46),
            array('nom' => 'Congélateur coffre (petit)', 'categorie' => 'Cuisine', 'volume_m3' => 0.8, 'ordre' => 47),
            array('nom' => 'Congélateur coffre (moyen)', 'categorie' => 'Cuisine', 'volume_m3' => 1.2, 'ordre' => 48),
            array('nom' => 'Congélateur coffre (grand)', 'categorie' => 'Cuisine', 'volume_m3' => 1.8, 'ordre' => 49),
            array('nom' => 'Four', 'categorie' => 'Cuisine', 'volume_m3' => 0.5, 'ordre' => 50),
            array('nom' => 'Gazinière', 'categorie' => 'Cuisine', 'volume_m3' => 0.8, 'ordre' => 51),
            array('nom' => 'Lave-vaisselle', 'categorie' => 'Cuisine', 'volume_m3' => 0.7, 'ordre' => 52),
            array('nom' => 'Meuble de cuisine 1 porte', 'categorie' => 'Cuisine', 'volume_m3' => 0.6, 'ordre' => 53),
            array('nom' => 'Meuble de cuisine 2 portes', 'categorie' => 'Cuisine', 'volume_m3' => 1.0, 'ordre' => 54),
            array('nom' => 'Meuble de cuisine 3 portes', 'categorie' => 'Cuisine', 'volume_m3' => 1.5, 'ordre' => 55),
            array('nom' => 'Meuble de cuisine 4 portes', 'categorie' => 'Cuisine', 'volume_m3' => 2.0, 'ordre' => 56),
            array('nom' => 'Micro-ondes', 'categorie' => 'Cuisine', 'volume_m3' => 0.2, 'ordre' => 57),
            array('nom' => 'Plaque de cuisson', 'categorie' => 'Cuisine', 'volume_m3' => 0.3, 'ordre' => 58),
            array('nom' => 'Réfrigérateur', 'categorie' => 'Cuisine', 'volume_m3' => 1.0, 'ordre' => 59),
            array('nom' => 'Réfrigérateur américain', 'categorie' => 'Cuisine', 'volume_m3' => 2.0, 'ordre' => 60),
            array('nom' => 'Réfrigérateur standard', 'categorie' => 'Cuisine', 'volume_m3' => 1.2, 'ordre' => 61),
            array('nom' => 'Réfrigérateur top', 'categorie' => 'Cuisine', 'volume_m3' => 0.5, 'ordre' => 62),
            array('nom' => 'Sous-lavabo', 'categorie' => 'Cuisine', 'volume_m3' => 0.5, 'ordre' => 63),
            array('nom' => 'Table', 'categorie' => 'Cuisine', 'volume_m3' => 1.0, 'ordre' => 64),
            array('nom' => 'Vaisselier 1 porte', 'categorie' => 'Cuisine', 'volume_m3' => 1.5, 'ordre' => 65),
            array('nom' => 'Vaisselier 2 portes', 'categorie' => 'Cuisine', 'volume_m3' => 2.5, 'ordre' => 66),
            array('nom' => 'Vaisselier 3 portes', 'categorie' => 'Cuisine', 'volume_m3' => 3.5, 'ordre' => 67),
            array('nom' => 'Vaisselier 4 portes', 'categorie' => 'Cuisine', 'volume_m3' => 4.5, 'ordre' => 68),
            
            // AUTRES
            array('nom' => 'Aspirateur', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 69),
            array('nom' => 'Barbecue (grand)', 'categorie' => 'Autres', 'volume_m3' => 1.5, 'ordre' => 70),
            array('nom' => 'Barbecue (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.8, 'ordre' => 71),
            array('nom' => 'Barbecue (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.4, 'ordre' => 72),
            array('nom' => 'Brouette', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 73),
            array('nom' => 'Carton standard', 'categorie' => 'Autres', 'volume_m3' => 0.1, 'ordre' => 74),
            array('nom' => 'Etabli', 'categorie' => 'Autres', 'volume_m3' => 1.2, 'ordre' => 75),
            array('nom' => 'Lave-linge', 'categorie' => 'Autres', 'volume_m3' => 0.7, 'ordre' => 76),
            array('nom' => 'Lot d\'outils (grand)', 'categorie' => 'Autres', 'volume_m3' => 0.8, 'ordre' => 77),
            array('nom' => 'Lot d\'outils (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 78),
            array('nom' => 'Lot d\'outils (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 79),
            array('nom' => 'Meuble à chaussures (grand)', 'categorie' => 'Autres', 'volume_m3' => 0.8, 'ordre' => 80),
            array('nom' => 'Meuble à chaussures (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 81),
            array('nom' => 'Meuble à chaussures (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 82),
            array('nom' => 'Ordinateur', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 83),
            array('nom' => 'Outils de jardin (grand)', 'categorie' => 'Autres', 'volume_m3' => 0.8, 'ordre' => 84),
            array('nom' => 'Outils de jardin (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 85),
            array('nom' => 'Outils de jardin (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 86),
            array('nom' => 'Panière à linge', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 87),
            array('nom' => 'Parasol', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 88),
            array('nom' => 'Placard', 'categorie' => 'Autres', 'volume_m3' => 2.0, 'ordre' => 89),
            array('nom' => 'Plante (grande)', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 90),
            array('nom' => 'Plante (moyenne)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 91),
            array('nom' => 'Plante (petite)', 'categorie' => 'Autres', 'volume_m3' => 0.1, 'ordre' => 92),
            array('nom' => 'Porte-manteaux', 'categorie' => 'Autres', 'volume_m3' => 0.4, 'ordre' => 93),
            array('nom' => 'Poussette', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 94),
            array('nom' => 'Scooter', 'categorie' => 'Autres', 'volume_m3' => 1.5, 'ordre' => 95),
            array('nom' => 'Sèche-linge', 'categorie' => 'Autres', 'volume_m3' => 0.7, 'ordre' => 96),
            array('nom' => 'Table à repasser', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 97),
            array('nom' => 'Table de jardin (grande)', 'categorie' => 'Autres', 'volume_m3' => 1.5, 'ordre' => 98),
            array('nom' => 'Table de jardin (moyenne)', 'categorie' => 'Autres', 'volume_m3' => 1.0, 'ordre' => 99),
            array('nom' => 'Table de jardin (petite)', 'categorie' => 'Autres', 'volume_m3' => 0.6, 'ordre' => 100),
            array('nom' => 'Table de ping pong', 'categorie' => 'Autres', 'volume_m3' => 1.8, 'ordre' => 101),
            array('nom' => 'Tableau (grand)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 102),
            array('nom' => 'Tableau (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 103),
            array('nom' => 'Tableau (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.1, 'ordre' => 104),
            array('nom' => 'Tapis (grand)', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 105),
            array('nom' => 'Tapis (moyen)', 'categorie' => 'Autres', 'volume_m3' => 0.3, 'ordre' => 106),
            array('nom' => 'Tapis (petit)', 'categorie' => 'Autres', 'volume_m3' => 0.2, 'ordre' => 107),
            array('nom' => 'Tondeuse', 'categorie' => 'Autres', 'volume_m3' => 0.6, 'ordre' => 108),
            array('nom' => 'Tracteur tondeuse', 'categorie' => 'Autres', 'volume_m3' => 2.0, 'ordre' => 109),
            array('nom' => 'Transat', 'categorie' => 'Autres', 'volume_m3' => 0.4, 'ordre' => 110),
            array('nom' => 'Vélo', 'categorie' => 'Autres', 'volume_m3' => 0.5, 'ordre' => 111),
        );
        
        // Insérer tous les objets
        foreach ($objets as $objet) {
            $wpdb->insert(
                $table_objets,
                $objet,
                array('%s', '%s', '%f', '%d')
            );
        }
    }
    
    /**
     * Récupérer tous les objets actifs groupés par catégorie
     */
    public static function get_objets_by_category() {
        global $wpdb;
        $table_objets = $wpdb->prefix . 'devis_objets';
        
        $results = $wpdb->get_results(
            "SELECT * FROM $table_objets WHERE actif = 1 ORDER BY categorie, ordre, nom",
            ARRAY_A
        );
        
        // Grouper par catégorie
        $objets_grouped = array();
        foreach ($results as $objet) {
            $categorie = $objet['categorie'];
            if (!isset($objets_grouped[$categorie])) {
                $objets_grouped[$categorie] = array();
            }
            $objets_grouped[$categorie][] = $objet;
        }
        
        return $objets_grouped;
    }
    
    /**
     * Sauvegarder un devis dans l'historique
     */
    public static function save_devis($data) {
        global $wpdb;
        $table_historique = $wpdb->prefix . 'devis_historique';
        
        $wpdb->insert(
            $table_historique,
            array(
                'client_nom' => sanitize_text_field($data['client_nom']),
                'client_email' => sanitize_email($data['client_email']),
                'client_telephone' => sanitize_text_field($data['client_telephone']),
                'adresse_depart' => sanitize_text_field($data['adresse_depart']),
                'adresse_arrivee' => sanitize_text_field($data['adresse_arrivee']),
                'distance_km' => floatval($data['distance_km']),
                'volume_total_m3' => floatval($data['volume_total_m3']),
                'prix_volume' => floatval($data['prix_volume']),
                'prix_distance' => floatval($data['prix_distance']),
                'prix_supplements' => floatval($data['prix_supplements']),
                'prix_total' => floatval($data['prix_total']),
                'objets_selectionnes' => json_encode($data['objets_selectionnes']),
                'notes' => sanitize_textarea_field($data['notes'])
            ),
            array('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
}