<?php
/**
 * Gestion de l'activation du plugin
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Activator {
    
    /**
     * Actions lors de l'activation du plugin
     */
    public static function activate() {
        
        // Vérification de la version PHP
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            deactivate_plugins(ND_PLUGIN_BASENAME);
            wp_die(__('Ce plugin nécessite PHP 8.0 ou supérieur.', 'novalia-devis'));
        }
        
        // Vérification de la version WordPress
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            deactivate_plugins(ND_PLUGIN_BASENAME);
            wp_die(__('Ce plugin nécessite WordPress 6.0 ou supérieur.', 'novalia-devis'));
        }
        
        // Création des tables
        self::create_tables();
        
        // Insertion des données par défaut
        self::insert_default_data();
        
        // Configuration des options par défaut
        self::set_default_options();
        
        // Ajout des capacités
        self::add_capabilities();
        
        // Flush des règles de réécriture
        flush_rewrite_rules();
        
        // Enregistrement de la version
        update_option('nd_version', ND_VERSION);
        update_option('nd_db_version', ND_VERSION);
        update_option('nd_activation_date', current_time('mysql'));
    }
    
    /**
     * Création des tables de la base de données
     */
    private static function create_tables() {
        require_once ND_PLUGIN_DIR . 'includes/class-nd-database.php';
        ND_Database::create_tables();
    }
    
    /**
     * Insertion des objets de déménagement par défaut
     */
    private static function insert_default_data() {
        global $wpdb;
        
        $table_items = $wpdb->prefix . 'nd_items';
        
        // Vérifier si des données existent déjà
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_items");
        
        if ($count > 0) {
            return; // Les données existent déjà
        }
        
        // Liste des objets par défaut avec leurs volumes en m³
        $default_items = [
            // Chambres
            ['name' => 'Lit simple (90x190)', 'volume' => 0.5, 'category' => 'Chambre'],
            ['name' => 'Lit double (140x190)', 'volume' => 0.8, 'category' => 'Chambre'],
            ['name' => 'Lit Queen (160x200)', 'volume' => 1.0, 'category' => 'Chambre'],
            ['name' => 'Lit King (180x200)', 'volume' => 1.2, 'category' => 'Chambre'],
            ['name' => 'Armoire 2 portes', 'volume' => 1.5, 'category' => 'Chambre'],
            ['name' => 'Armoire 3 portes', 'volume' => 2.0, 'category' => 'Chambre'],
            ['name' => 'Commode 3 tiroirs', 'volume' => 0.4, 'category' => 'Chambre'],
            ['name' => 'Commode 5 tiroirs', 'volume' => 0.6, 'category' => 'Chambre'],
            ['name' => 'Table de chevet', 'volume' => 0.2, 'category' => 'Chambre'],
            ['name' => 'Sommier simple', 'volume' => 0.3, 'category' => 'Chambre'],
            ['name' => 'Sommier double', 'volume' => 0.5, 'category' => 'Chambre'],
            
            // Salon
            ['name' => 'Canapé 2 places', 'volume' => 1.5, 'category' => 'Salon'],
            ['name' => 'Canapé 3 places', 'volume' => 2.0, 'category' => 'Salon'],
            ['name' => 'Canapé d\'angle', 'volume' => 3.0, 'category' => 'Salon'],
            ['name' => 'Fauteuil', 'volume' => 0.8, 'category' => 'Salon'],
            ['name' => 'Table basse', 'volume' => 0.3, 'category' => 'Salon'],
            ['name' => 'Meuble TV', 'volume' => 0.6, 'category' => 'Salon'],
            ['name' => 'Bibliothèque', 'volume' => 1.2, 'category' => 'Salon'],
            ['name' => 'Étagère murale', 'volume' => 0.4, 'category' => 'Salon'],
            ['name' => 'Table de salon', 'volume' => 0.5, 'category' => 'Salon'],
            
            // Salle à manger
            ['name' => 'Table à manger 4 personnes', 'volume' => 0.6, 'category' => 'Salle à manger'],
            ['name' => 'Table à manger 6 personnes', 'volume' => 0.9, 'category' => 'Salle à manger'],
            ['name' => 'Table à manger 8 personnes', 'volume' => 1.2, 'category' => 'Salle à manger'],
            ['name' => 'Chaise', 'volume' => 0.2, 'category' => 'Salle à manger'],
            ['name' => 'Buffet', 'volume' => 1.0, 'category' => 'Salle à manger'],
            ['name' => 'Vaisselier', 'volume' => 1.5, 'category' => 'Salle à manger'],
            
            // Cuisine
            ['name' => 'Réfrigérateur', 'volume' => 0.8, 'category' => 'Cuisine'],
            ['name' => 'Réfrigérateur américain', 'volume' => 1.2, 'category' => 'Cuisine'],
            ['name' => 'Congélateur', 'volume' => 0.6, 'category' => 'Cuisine'],
            ['name' => 'Four', 'volume' => 0.3, 'category' => 'Cuisine'],
            ['name' => 'Micro-ondes', 'volume' => 0.1, 'category' => 'Cuisine'],
            ['name' => 'Lave-vaisselle', 'volume' => 0.4, 'category' => 'Cuisine'],
            ['name' => 'Lave-linge', 'volume' => 0.5, 'category' => 'Cuisine'],
            ['name' => 'Sèche-linge', 'volume' => 0.5, 'category' => 'Cuisine'],
            ['name' => 'Table de cuisine', 'volume' => 0.4, 'category' => 'Cuisine'],
            ['name' => 'Chaise de cuisine', 'volume' => 0.15, 'category' => 'Cuisine'],
            
            // Bureau
            ['name' => 'Bureau', 'volume' => 0.8, 'category' => 'Bureau'],
            ['name' => 'Chaise de bureau', 'volume' => 0.3, 'category' => 'Bureau'],
            ['name' => 'Caisson de bureau', 'volume' => 0.2, 'category' => 'Bureau'],
            ['name' => 'Bibliothèque bureau', 'volume' => 1.0, 'category' => 'Bureau'],
            ['name' => 'Armoire de bureau', 'volume' => 1.5, 'category' => 'Bureau'],
            
            // Divers
            ['name' => 'Carton petit (30x20x20)', 'volume' => 0.012, 'category' => 'Cartons'],
            ['name' => 'Carton moyen (40x30x30)', 'volume' => 0.036, 'category' => 'Cartons'],
            ['name' => 'Carton grand (50x40x40)', 'volume' => 0.08, 'category' => 'Cartons'],
            ['name' => 'Carton penderie', 'volume' => 0.5, 'category' => 'Cartons'],
            ['name' => 'Vélo', 'volume' => 0.3, 'category' => 'Divers'],
            ['name' => 'Trottinette', 'volume' => 0.1, 'category' => 'Divers'],
            ['name' => 'Plante verte', 'volume' => 0.2, 'category' => 'Divers'],
            ['name' => 'Tableau / Miroir', 'volume' => 0.1, 'category' => 'Divers'],
            ['name' => 'Aspirateur', 'volume' => 0.1, 'category' => 'Divers'],
            ['name' => 'Lampadaire', 'volume' => 0.2, 'category' => 'Divers'],
        ];
        
        // Insertion en base de données
        foreach ($default_items as $item) {
            $wpdb->insert(
                $table_items,
                [
                    'name' => $item['name'],
                    'volume' => $item['volume'],
                    'category' => $item['category'],
                    'is_active' => 1,
                    'created_at' => current_time('mysql')
                ],
                ['%s', '%f', '%s', '%d', '%s']
            );
        }
    }
    
    /**
     * Configuration des options par défaut
     */
    private static function set_default_options() {
        
        // Options de tarification
        $default_pricing = [
            'price_per_km' => 1.50,           // 1,50€ par kilomètre
            'price_per_m3' => 35.00,          // 35€ par m³
            'fixed_fee' => 0.00,              // Frais fixes
            'fee_floor' => 25.00,             // Frais par étage
            'fee_elevator' => 0.00,           // Avec ascenseur
            'fee_packing' => 15.00,           // Service d'emballage par m³
            'fee_insurance' => 2.50,          // Assurance par m³
            'min_quote_amount' => 150.00,     // Montant minimum d'un devis
        ];
        
        add_option('nd_pricing', $default_pricing);
        
        // Options de l'entreprise
        $default_company = [
            'name' => 'Novalia Déménagement',
            'address' => '123 Rue du Commerce',
            'zipcode' => '75001',
            'city' => 'Paris',
            'phone' => '01 23 45 67 89',
            'email' => 'contact@novalia.fr',
            'siret' => '123 456 789 00012',
            'logo_url' => '',
        ];
        
        add_option('nd_company', $default_company);
        
        // Options des emails
        $default_email = [
            'customer_subject' => 'Votre devis de déménagement Novalia',
            'admin_subject' => 'Nouveau devis généré',
            'admin_email' => get_option('admin_email'),
            'send_copy_to_admin' => 1,
        ];
        
        add_option('nd_email', $default_email);
        
        // Options du PDF
        $default_pdf = [
            'footer_text' => 'Novalia Déménagement - Votre partenaire déménagement de confiance',
            'legal_mentions' => 'Devis gratuit et sans engagement. Valable 30 jours.',
        ];
        
        add_option('nd_pdf', $default_pdf);
    }
    
    /**
     * Ajout des capacités pour gérer le plugin
     */
    private static function add_capabilities() {
        
        $role = get_role('administrator');
        
        if ($role) {
            $role->add_cap('manage_novalia_devis');
            $role->add_cap('view_novalia_quotes');
            $role->add_cap('edit_novalia_items');
            $role->add_cap('edit_novalia_pricing');
        }
    }
}