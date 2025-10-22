<?php
/**
 * Gestion de la base de données
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Database {
    
    /**
     * Création des tables du plugin
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Table des objets de déménagement
        $table_items = $wpdb->prefix . 'nd_items';
        $sql_items = "CREATE TABLE $table_items (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            volume decimal(10,3) NOT NULL DEFAULT 0,
            category varchar(100) DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Table des devis
        $table_quotes = $wpdb->prefix . 'nd_quotes';
        $sql_quotes = "CREATE TABLE $table_quotes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quote_number varchar(50) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_firstname varchar(255) DEFAULT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(50) DEFAULT NULL,
            address_from text NOT NULL,
            address_to text NOT NULL,
            distance decimal(10,2) NOT NULL DEFAULT 0,
            total_volume decimal(10,3) NOT NULL DEFAULT 0,
            total_price decimal(10,2) NOT NULL DEFAULT 0,
            status varchar(50) NOT NULL DEFAULT 'pending',
            pdf_path varchar(500) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_number (quote_number),
            KEY customer_email (customer_email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Table des objets par devis
        $table_quote_items = $wpdb->prefix . 'nd_quote_items';
        $sql_quote_items = "CREATE TABLE $table_quote_items (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quote_id bigint(20) unsigned NOT NULL,
            item_name varchar(255) NOT NULL,
            item_volume decimal(10,3) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY quote_id (quote_id),
            CONSTRAINT fk_quote_items_quote 
                FOREIGN KEY (quote_id) 
                REFERENCES $table_quotes(id) 
                ON DELETE CASCADE
        ) $charset_collate;";
        
        // Exécution des requêtes
        dbDelta($sql_items);
        dbDelta($sql_quotes);
        dbDelta($sql_quote_items);
    }
    
    /**
     * Génération d'un numéro de devis unique
     */
    public static function generate_quote_number() {
        global $wpdb;
        
        $prefix = 'ND';
        $date = date('Ymd');
        $table = $wpdb->prefix . 'nd_quotes';
        
        // Recherche du dernier numéro du jour
        $last_number = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT quote_number FROM $table 
                WHERE quote_number LIKE %s 
                ORDER BY id DESC LIMIT 1",
                $prefix . $date . '%'
            )
        );
        
        if ($last_number) {
            // Extraction du compteur et incrémentation
            $counter = intval(substr($last_number, -4)) + 1;
        } else {
            $counter = 1;
        }
        
        // Format: ND-YYYYMMDD-0001
        return sprintf('%s-%s-%04d', $prefix, $date, $counter);
    }
    
    /**
     * Insertion d'un nouveau devis
     */
    public static function insert_quote($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        // Génération du numéro de devis
        $quote_number = self::generate_quote_number();
        
        $insert_data = [
            'quote_number' => $quote_number,
            'customer_name' => sanitize_text_field($data['customer_name']),
            'customer_firstname' => sanitize_text_field($data['customer_firstname'] ?? ''),
            'customer_email' => sanitize_email($data['customer_email']),
            'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
            'address_from' => sanitize_textarea_field($data['address_from']),
            'address_to' => sanitize_textarea_field($data['address_to']),
            'distance' => floatval($data['distance']),
            'total_volume' => floatval($data['total_volume']),
            'total_price' => floatval($data['total_price']),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ];
        
        $format = ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s'];
        
        $result = $wpdb->insert($table, $insert_data, $format);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Insertion des objets d'un devis
     */
    public static function insert_quote_items($quote_id, $items) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quote_items';
        
        foreach ($items as $item) {
            $wpdb->insert(
                $table,
                [
                    'quote_id' => $quote_id,
                    'item_name' => sanitize_text_field($item['name']),
                    'item_volume' => floatval($item['volume']),
                    'quantity' => intval($item['quantity'])
                ],
                ['%d', '%s', '%f', '%d']
            );
        }
        
        return true;
    }
    
    /**
     * Récupération d'un devis par ID
     */
    public static function get_quote($quote_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $quote_id),
            ARRAY_A
        );
    }
    
    /**
     * Récupération des objets d'un devis
     */
    public static function get_quote_items($quote_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quote_items';
        
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE quote_id = %d", $quote_id),
            ARRAY_A
        );
    }
    
    /**
     * Mise à jour du chemin PDF d'un devis
     */
    public static function update_quote_pdf_path($quote_id, $pdf_path) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        return $wpdb->update(
            $table,
            ['pdf_path' => $pdf_path],
            ['id' => $quote_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Récupération de tous les devis
     */
    public static function get_all_quotes($limit = 50, $offset = 0, $order_by = 'created_at', $order = 'DESC') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        $order_by = sanitize_sql_orderby($order_by);
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table 
            ORDER BY $order_by $order 
            LIMIT %d OFFSET %d",
            $limit,
            $offset
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Comptage total des devis
     */
    public static function count_quotes() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_quotes';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
    
    /**
     * Suppression d'un devis
     */
    public static function delete_quote($quote_id) {
        global $wpdb;
        
        $table_quotes = $wpdb->prefix . 'nd_quotes';
        $table_items = $wpdb->prefix . 'nd_quote_items';
        
        // Suppression des objets (CASCADE devrait le faire automatiquement)
        $wpdb->delete($table_items, ['quote_id' => $quote_id], ['%d']);
        
        // Suppression du devis
        return $wpdb->delete($table_quotes, ['id' => $quote_id], ['%d']);
    }
}