<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_items = $wpdb->prefix . 'novalia_items';
        $table_tarifs = $wpdb->prefix . 'novalia_tarifs';
        $table_devis = $wpdb->prefix . 'novalia_devis';
        $table_devis_items = $wpdb->prefix . 'novalia_devis_items';
        
        $sql_items = "CREATE TABLE IF NOT EXISTS $table_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            nom varchar(255) NOT NULL,
            categorie varchar(100) NOT NULL,
            volume decimal(10,3) NOT NULL,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP,
            date_modification datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY categorie (categorie)
        ) $charset_collate;";
        
        $sql_tarifs = "CREATE TABLE IF NOT EXISTS $table_tarifs (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type_tarif varchar(50) NOT NULL,
            valeur decimal(10,2) NOT NULL,
            unite varchar(50) NOT NULL,
            description text,
            date_modification datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY type_tarif (type_tarif)
        ) $charset_collate;";
        
        $sql_devis = "CREATE TABLE IF NOT EXISTS $table_devis (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            numero_devis varchar(50) NOT NULL,
            nom_client varchar(255) NOT NULL,
            email_client varchar(255) NOT NULL,
            telephone_client varchar(50),
            adresse_depart text NOT NULL,
            adresse_arrivee text NOT NULL,
            type_logement_depart varchar(50),
            type_logement_arrivee varchar(50),
            etages_depart int(11) DEFAULT 0,
            etages_arrivee int(11) DEFAULT 0,
            ascenseur_depart tinyint(1) DEFAULT 0,
            ascenseur_arrivee tinyint(1) DEFAULT 0,
            distance decimal(10,2) NOT NULL,
            date_demenagement date NOT NULL,
            volume_total decimal(10,3) NOT NULL,
            type_demenagement varchar(20) NOT NULL,
            nombre_cartons int(11) DEFAULT 0,
            prix_standard decimal(10,2) NOT NULL,
            prix_complet decimal(10,2) NOT NULL,
            fiche_technique_pdf varchar(500),
            statut varchar(20) DEFAULT 'en_attente',
            date_creation datetime DEFAULT CURRENT_TIMESTAMP,
            date_modification datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY numero_devis (numero_devis),
            KEY statut (statut),
            KEY date_creation (date_creation)
        ) $charset_collate;";
        
        $sql_devis_items = "CREATE TABLE IF NOT EXISTS $table_devis_items (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            devis_id bigint(20) UNSIGNED NOT NULL,
            item_id bigint(20) UNSIGNED,
            nom_item varchar(255) NOT NULL,
            categorie varchar(100) NOT NULL,
            volume decimal(10,3) NOT NULL,
            quantite int(11) NOT NULL DEFAULT 1,
            is_custom tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY devis_id (devis_id),
            KEY item_id (item_id),
            CONSTRAINT fk_devis_items_devis FOREIGN KEY (devis_id) REFERENCES $table_devis (id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_items);
        dbDelta($sql_tarifs);
        dbDelta($sql_devis);
        dbDelta($sql_devis_items);
    }
    
    public static function get_items($categorie = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        if ($categorie) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE categorie = %s ORDER BY nom ASC",
                $categorie
            ));
        }
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY categorie ASC, nom ASC");
    }
    
    public static function get_items_by_category() {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        $items = $wpdb->get_results("SELECT * FROM $table ORDER BY categorie ASC, nom ASC");
        
        $categorized = array();
        foreach ($items as $item) {
            if (!isset($categorized[$item->categorie])) {
                $categorized[$item->categorie] = array();
            }
            $categorized[$item->categorie][] = $item;
        }
        
        return $categorized;
    }
    
    public static function get_item($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    public static function insert_item($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        $wpdb->insert($table, array(
            'nom' => sanitize_text_field($data['nom']),
            'categorie' => sanitize_text_field($data['categorie']),
            'volume' => floatval($data['volume'])
        ));
        
        return $wpdb->insert_id;
    }
    
    public static function update_item($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        return $wpdb->update(
            $table,
            array(
                'nom' => sanitize_text_field($data['nom']),
                'categorie' => sanitize_text_field($data['categorie']),
                'volume' => floatval($data['volume'])
            ),
            array('id' => $id)
        );
    }
    
    public static function delete_item($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_items';
        
        return $wpdb->delete($table, array('id' => $id));
    }
    
    public static function get_tarifs() {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_tarifs';
        
        $tarifs = $wpdb->get_results("SELECT * FROM $table");
        
        $formatted = array();
        foreach ($tarifs as $tarif) {
            $formatted[$tarif->type_tarif] = array(
                'valeur' => $tarif->valeur,
                'unite' => $tarif->unite,
                'description' => $tarif->description
            );
        }
        
        return $formatted;
    }
    
    public static function get_tarif($type) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_tarifs';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE type_tarif = %s",
            $type
        ));
    }
    
    public static function update_tarif($type, $valeur) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_tarifs';
        
        return $wpdb->update(
            $table,
            array('valeur' => floatval($valeur)),
            array('type_tarif' => $type)
        );
    }
    
    public static function insert_devis($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        $numero = 'DEV-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $wpdb->insert($table, array(
            'numero_devis' => $numero,
            'nom_client' => sanitize_text_field($data['nom_client']),
            'email_client' => sanitize_email($data['email_client']),
            'telephone_client' => sanitize_text_field($data['telephone_client']),
            'adresse_depart' => sanitize_textarea_field($data['adresse_depart']),
            'adresse_arrivee' => sanitize_textarea_field($data['adresse_arrivee']),
            'type_logement_depart' => isset($data['type_logement_depart']) ? sanitize_text_field($data['type_logement_depart']) : null,
            'type_logement_arrivee' => isset($data['type_logement_arrivee']) ? sanitize_text_field($data['type_logement_arrivee']) : null,
            'etages_depart' => isset($data['etages_depart']) ? intval($data['etages_depart']) : 0,
            'etages_arrivee' => isset($data['etages_arrivee']) ? intval($data['etages_arrivee']) : 0,
            'ascenseur_depart' => isset($data['ascenseur_depart']) ? 1 : 0,
            'ascenseur_arrivee' => isset($data['ascenseur_arrivee']) ? 1 : 0,
            'distance' => floatval($data['distance']),
            'date_demenagement' => sanitize_text_field($data['date_demenagement']),
            'volume_total' => floatval($data['volume_total']),
            'type_demenagement' => sanitize_text_field($data['type_demenagement']),
            'nombre_cartons' => intval($data['nombre_cartons']),
            'prix_standard' => floatval($data['prix_standard']),
            'prix_complet' => floatval($data['prix_complet']),
            'statut' => 'en_attente'
        ));
        
        return $wpdb->insert_id;
    }
    
    public static function update_devis_fiche_technique($devis_id, $pdf_path) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        return $wpdb->update(
            $table,
            array('fiche_technique_pdf' => $pdf_path),
            array('id' => $devis_id),
            array('%s'),
            array('%d')
        );
    }
    
    public static function insert_devis_items($devis_id, $items) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis_items';
        
        foreach ($items as $item) {
            $wpdb->insert($table, array(
                'devis_id' => $devis_id,
                'item_id' => isset($item['item_id']) ? intval($item['item_id']) : null,
                'nom_item' => sanitize_text_field($item['nom']),
                'categorie' => sanitize_text_field($item['categorie']),
                'volume' => floatval($item['volume']),
                'quantite' => intval($item['quantite']),
                'is_custom' => isset($item['is_custom']) ? 1 : 0
            ));
        }
    }
    
    public static function get_devis($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    public static function get_devis_items($devis_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis_items';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE devis_id = %d ORDER BY categorie ASC, nom_item ASC",
            $devis_id
        ));
    }
    
    public static function get_all_devis($statut = null, $limit = 50, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        if ($statut) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE statut = %s ORDER BY date_creation DESC LIMIT %d OFFSET %d",
                $statut, $limit, $offset
            ));
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY date_creation DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ));
    }
    
    public static function update_devis_statut($id, $statut) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        return $wpdb->update(
            $table,
            array('statut' => sanitize_text_field($statut)),
            array('id' => $id)
        );
    }
    
    public static function delete_devis($id) {
        global $wpdb;
        $table_devis = $wpdb->prefix . 'novalia_devis';
        $table_items = $wpdb->prefix . 'novalia_devis_items';
        
        $devis = self::get_devis($id);
        if ($devis && !empty($devis->fiche_technique_pdf)) {
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . $devis->fiche_technique_pdf;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        $wpdb->delete($table_items, array('devis_id' => $id));
        return $wpdb->delete($table_devis, array('id' => $id));
    }
    
    public static function count_devis($statut = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'novalia_devis';
        
        if ($statut) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE statut = %s",
                $statut
            ));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
}