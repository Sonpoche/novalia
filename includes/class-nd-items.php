<?php
/**
 * Gestion des objets de déménagement
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Items {
    
    /**
     * Récupération de tous les objets actifs
     */
    public static function get_all_items($active_only = true) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        if ($active_only) {
            $query = "SELECT * FROM $table WHERE is_active = 1 ORDER BY category ASC, name ASC";
        } else {
            $query = "SELECT * FROM $table ORDER BY category ASC, name ASC";
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Récupération des objets groupés par catégorie
     */
    public static function get_items_by_category($active_only = true) {
        $items = self::get_all_items($active_only);
        $grouped = [];
        
        foreach ($items as $item) {
            $category = $item['category'] ?? 'Autres';
            
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            
            $grouped[$category][] = $item;
        }
        
        return $grouped;
    }
    
    /**
     * Récupération d'un objet par ID
     */
    public static function get_item($item_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $item_id),
            ARRAY_A
        );
    }
    
    /**
     * Insertion d'un nouvel objet
     */
    public static function insert_item($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        $insert_data = [
            'name' => sanitize_text_field($data['name']),
            'volume' => floatval($data['volume']),
            'category' => sanitize_text_field($data['category'] ?? 'Divers'),
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
            'created_at' => current_time('mysql')
        ];
        
        $format = ['%s', '%f', '%s', '%d', '%s'];
        
        $result = $wpdb->insert($table, $insert_data, $format);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Mise à jour d'un objet
     */
    public static function update_item($item_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        $update_data = [
            'name' => sanitize_text_field($data['name']),
            'volume' => floatval($data['volume']),
            'category' => sanitize_text_field($data['category'] ?? 'Divers'),
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
        ];
        
        $format = ['%s', '%f', '%s', '%d'];
        
        return $wpdb->update(
            $table,
            $update_data,
            ['id' => $item_id],
            $format,
            ['%d']
        );
    }
    
    /**
     * Suppression d'un objet
     */
    public static function delete_item($item_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        return $wpdb->delete($table, ['id' => $item_id], ['%d']);
    }
    
    /**
     * Activation/Désactivation d'un objet
     */
    public static function toggle_active($item_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        $item = self::get_item($item_id);
        
        if (!$item) {
            return false;
        }
        
        $new_status = $item['is_active'] ? 0 : 1;
        
        return $wpdb->update(
            $table,
            ['is_active' => $new_status],
            ['id' => $item_id],
            ['%d'],
            ['%d']
        );
    }
    
    /**
     * Récupération de toutes les catégories
     */
    public static function get_categories() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        $categories = $wpdb->get_col(
            "SELECT DISTINCT category FROM $table WHERE category IS NOT NULL ORDER BY category ASC"
        );
        
        return $categories;
    }
    
    /**
     * Comptage des objets par catégorie
     */
    public static function count_by_category() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        return $wpdb->get_results(
            "SELECT category, COUNT(*) as count 
            FROM $table 
            WHERE is_active = 1 
            GROUP BY category 
            ORDER BY category ASC",
            ARRAY_A
        );
    }
    
    /**
     * Export CSV des objets
     */
    public static function export_to_csv() {
        $items = self::get_all_items(false);
        
        $csv_data = "Nom,Volume (m³),Catégorie,Actif\n";
        
        foreach ($items as $item) {
            $csv_data .= sprintf(
                '"%s",%s,"%s",%s' . "\n",
                str_replace('"', '""', $item['name']),
                $item['volume'],
                str_replace('"', '""', $item['category']),
                $item['is_active'] ? 'Oui' : 'Non'
            );
        }
        
        return $csv_data;
    }
    
    /**
     * Import CSV des objets
     */
    public static function import_from_csv($file_path) {
        if (!file_exists($file_path)) {
            return ['success' => false, 'message' => 'Fichier introuvable'];
        }
        
        $handle = fopen($file_path, 'r');
        
        if ($handle === false) {
            return ['success' => false, 'message' => 'Impossible d\'ouvrir le fichier'];
        }
        
        $imported = 0;
        $errors = 0;
        $line = 0;
        
        // Ignorer la première ligne (en-têtes)
        fgetcsv($handle, 1000, ',');
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $line++;
            
            // Validation basique
            if (count($data) < 3) {
                $errors++;
                continue;
            }
            
            $item_data = [
                'name' => trim($data[0]),
                'volume' => floatval(str_replace(',', '.', $data[1])),
                'category' => trim($data[2]),
                'is_active' => isset($data[3]) && strtolower($data[3]) === 'non' ? 0 : 1
            ];
            
            // Validation
            if (empty($item_data['name']) || $item_data['volume'] <= 0) {
                $errors++;
                continue;
            }
            
            // Insertion
            if (self::insert_item($item_data)) {
                $imported++;
            } else {
                $errors++;
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_lines' => $line
        ];
    }
    
    /**
     * Recherche d'objets
     */
    public static function search_items($search_term, $category = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nd_items';
        
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        if ($category) {
            $query = $wpdb->prepare(
                "SELECT * FROM $table 
                WHERE name LIKE %s 
                AND category = %s 
                AND is_active = 1 
                ORDER BY name ASC",
                $search_term,
                $category
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM $table 
                WHERE name LIKE %s 
                AND is_active = 1 
                ORDER BY name ASC",
                $search_term
            );
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Calcul du volume total d'une liste d'objets
     */
    public static function calculate_total_volume($items) {
        $total = 0;
        
        foreach ($items as $item) {
            $volume = floatval($item['volume']);
            $quantity = intval($item['quantity'] ?? 1);
            $total += $volume * $quantity;
        }
        
        return round($total, 3);
    }
    
    /**
     * Validation des données d'un objet
     */
    public static function validate_item_data($data) {
        $errors = [];
        
        // Nom obligatoire
        if (empty($data['name'])) {
            $errors[] = 'Le nom est obligatoire';
        }
        
        // Volume obligatoire et positif
        if (!isset($data['volume']) || floatval($data['volume']) <= 0) {
            $errors[] = 'Le volume doit être supérieur à 0';
        }
        
        // Catégorie
        if (empty($data['category'])) {
            $errors[] = 'La catégorie est obligatoire';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}