<?php
/**
 * Gestion des objets de déménagement (Admin)
 *
 * @package NovaliaDevis
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Admin_Items {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('wp_ajax_nd_add_item', [$this, 'ajax_add_item']);
        add_action('wp_ajax_nd_edit_item', [$this, 'ajax_edit_item']);
        add_action('wp_ajax_nd_delete_item', [$this, 'ajax_delete_item']);
        add_action('wp_ajax_nd_toggle_item', [$this, 'ajax_toggle_item']);
        add_action('wp_ajax_nd_export_items_csv', [$this, 'ajax_export_items_csv']);
    }
    
    /**
     * Rendu de la page
     */
    public function render_page() {
        // Gestion de l'import CSV
        if (isset($_POST['nd_import_csv']) && check_admin_referer('nd_import_csv_nonce')) {
            $this->handle_csv_import();
        }
        
        // Récupération des objets
        $items = ND_Items::get_all_items(false);
        $items_by_category = ND_Items::get_items_by_category(false);
        $categories = ND_Items::get_categories();
        
        include ND_PLUGIN_DIR . 'admin/views/items-list.php';
    }
    
    /**
     * AJAX : Ajouter un objet
     */
    public function ajax_add_item() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_novalia_items')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'volume' => floatval($_POST['volume'] ?? 0),
            'category' => sanitize_text_field($_POST['category'] ?? 'Divers'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validation
        $validation = ND_Items::validate_item_data($data);
        
        if (!$validation['valid']) {
            wp_send_json_error([
                'message' => implode('<br>', $validation['errors'])
            ]);
        }
        
        // Insertion
        $item_id = ND_Items::insert_item($data);
        
        if ($item_id) {
            wp_send_json_success([
                'message' => 'Objet ajouté avec succès',
                'item_id' => $item_id
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de l\'ajout']);
        }
    }
    
    /**
     * AJAX : Modifier un objet
     */
    public function ajax_edit_item() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_novalia_items')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $item_id = intval($_POST['item_id'] ?? 0);
        
        $data = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'volume' => floatval($_POST['volume'] ?? 0),
            'category' => sanitize_text_field($_POST['category'] ?? 'Divers'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Validation
        $validation = ND_Items::validate_item_data($data);
        
        if (!$validation['valid']) {
            wp_send_json_error([
                'message' => implode('<br>', $validation['errors'])
            ]);
        }
        
        // Mise à jour
        $result = ND_Items::update_item($item_id, $data);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Objet modifié avec succès'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la modification']);
        }
    }
    
    /**
     * AJAX : Supprimer un objet
     */
    public function ajax_delete_item() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_novalia_items')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $item_id = intval($_POST['item_id'] ?? 0);
        
        $result = ND_Items::delete_item($item_id);
        
        if ($result) {
            wp_send_json_success([
                'message' => 'Objet supprimé avec succès'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la suppression']);
        }
    }
    
    /**
     * AJAX : Activer/Désactiver un objet
     */
    public function ajax_toggle_item() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_novalia_items')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $item_id = intval($_POST['item_id'] ?? 0);
        
        $result = ND_Items::toggle_active($item_id);
        
        if ($result !== false) {
            wp_send_json_success([
                'message' => 'Statut modifié avec succès'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la modification du statut']);
        }
    }
    
    /**
     * AJAX : Export CSV
     */
    public function ajax_export_items_csv() {
        check_ajax_referer('nd_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_novalia_items')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }
        
        $csv = ND_Items::export_to_csv();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="objets-demenagement-' . date('Y-m-d') . '.csv"');
        
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo $csv;
        exit;
    }
    
    /**
     * Gestion de l'import CSV
     */
    private function handle_csv_import() {
        if (!current_user_can('edit_novalia_items')) {
            wp_die('Permissions insuffisantes');
        }
        
        if (empty($_FILES['csv_file']['tmp_name'])) {
            add_settings_error(
                'nd_items',
                'no_file',
                'Aucun fichier sélectionné',
                'error'
            );
            return;
        }
        
        $file = $_FILES['csv_file'];
        
        // Vérification du type
        if ($file['type'] !== 'text/csv' && 
            pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
            add_settings_error(
                'nd_items',
                'invalid_file',
                'Le fichier doit être au format CSV',
                'error'
            );
            return;
        }
        
        // Import
        $result = ND_Items::import_from_csv($file['tmp_name']);
        
        if ($result['success']) {
            add_settings_error(
                'nd_items',
                'import_success',
                sprintf(
                    '%d objets importés avec succès. %d erreurs.',
                    $result['imported'],
                    $result['errors']
                ),
                'success'
            );
        } else {
            add_settings_error(
                'nd_items',
                'import_error',
                $result['message'],
                'error'
            );
        }
    }
}