<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_novalia_submit_devis', array($this, 'submit_devis'));
        add_action('wp_ajax_nopriv_novalia_submit_devis', array($this, 'submit_devis'));
        
        add_action('wp_ajax_novalia_update_statut', array($this, 'update_statut'));
        add_action('wp_ajax_novalia_delete_devis', array($this, 'delete_devis'));
        add_action('wp_ajax_novalia_add_item', array($this, 'add_item'));
        add_action('wp_ajax_novalia_update_item', array($this, 'update_item'));
        add_action('wp_ajax_novalia_delete_item', array($this, 'delete_item'));
    }
    
    public function submit_devis() {
        error_log('=== NOVALIA: Début submit_devis ===');
        
        check_ajax_referer('novalia_nonce', 'nonce');
        error_log('NOVALIA: Nonce vérifié');
        
        $data = array(
            'nom_client' => sanitize_text_field($_POST['nom_client']),
            'email_client' => sanitize_email($_POST['email_client']),
            'telephone_client' => sanitize_text_field($_POST['telephone_client']),
            'adresse_depart' => sanitize_textarea_field($_POST['adresse_depart']),
            'adresse_arrivee' => sanitize_textarea_field($_POST['adresse_arrivee']),
            'distance' => floatval($_POST['distance']),
            'date_demenagement' => sanitize_text_field($_POST['date_demenagement']),
            'type_demenagement' => sanitize_text_field($_POST['type_demenagement']),
            'nombre_cartons' => intval($_POST['nombre_cartons']),
            'etages_depart' => intval($_POST['etages_depart']),
            'etages_arrivee' => intval($_POST['etages_arrivee']),
            'ascenseur_depart' => isset($_POST['ascenseur_depart']) ? (bool)$_POST['ascenseur_depart'] : false,
            'ascenseur_arrivee' => isset($_POST['ascenseur_arrivee']) ? (bool)$_POST['ascenseur_arrivee'] : false,
            'type_logement_depart' => isset($_POST['type_logement_depart']) ? sanitize_text_field($_POST['type_logement_depart']) : '',
            'type_logement_arrivee' => isset($_POST['type_logement_arrivee']) ? sanitize_text_field($_POST['type_logement_arrivee']) : '',
            'items' => array()
        );
        
        error_log('NOVALIA DEBUG: Données préparées - Client: ' . $data['nom_client']);
        error_log('NOVALIA DEBUG: Type logement départ: ' . $data['type_logement_depart']);
        error_log('NOVALIA DEBUG: Type logement arrivée: ' . $data['type_logement_arrivee']);
        error_log('NOVALIA DEBUG: Étages départ: ' . $data['etages_depart']);
        error_log('NOVALIA DEBUG: Étages arrivée: ' . $data['etages_arrivee']);
        error_log('NOVALIA DEBUG: Ascenseur départ: ' . ($data['ascenseur_depart'] ? 'OUI' : 'NON'));
        error_log('NOVALIA DEBUG: Ascenseur arrivée: ' . ($data['ascenseur_arrivee'] ? 'OUI' : 'NON'));
        
        if (empty($data['nom_client']) || empty($data['email_client']) || empty($data['telephone_client'])) {
            error_log('NOVALIA: Validation échouée - champs manquants');
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires'));
            return;
        }
        
        if (!is_email($data['email_client'])) {
            error_log('NOVALIA: Validation échouée - email invalide');
            wp_send_json_error(array('message' => 'Email invalide'));
            return;
        }
        
        if (empty($_POST['items']) || !is_array($_POST['items'])) {
            error_log('NOVALIA: Validation échouée - pas d\'items');
            wp_send_json_error(array('message' => 'Veuillez sélectionner au moins un objet'));
            return;
        }
        
        error_log('NOVALIA: Traitement de ' . count($_POST['items']) . ' items');
        foreach ($_POST['items'] as $item) {
            $data['items'][] = array(
                'item_id' => isset($item['item_id']) ? intval($item['item_id']) : null,
                'nom' => sanitize_text_field($item['nom']),
                'categorie' => sanitize_text_field($item['categorie']),
                'volume' => floatval($item['volume']),
                'quantite' => intval($item['quantite']),
                'is_custom' => isset($item['is_custom']) ? true : false
            );
        }
        
        error_log('NOVALIA: Appel Novalia_Devis::create_devis');
        try {
            $devis_id = Novalia_Devis::create_devis($data);
            error_log('NOVALIA: Devis créé avec ID: ' . $devis_id);
        } catch (Exception $e) {
            error_log('NOVALIA: ERREUR création devis - ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Erreur création: ' . $e->getMessage()));
            return;
        }
        
        if ($devis_id) {
            error_log('NOVALIA: Devis ID valide, préparation envoi email');
            
            try {
                error_log('NOVALIA: Appel Novalia_Email::send_devis');
                $email_sent = Novalia_Email::send_devis($devis_id);
                error_log('NOVALIA: Email client envoyé: ' . ($email_sent ? 'OUI' : 'NON'));
                
                if ($email_sent) {
                    error_log('NOVALIA: SUCCESS - Tout OK');
                    wp_send_json_success(array(
                        'message' => 'Devis créé et envoyé avec succès',
                        'devis_id' => $devis_id
                    ));
                } else {
                    error_log('NOVALIA: Email non envoyé mais devis créé');
                    wp_send_json_error(array('message' => 'Devis créé mais erreur lors de l\'envoi de l\'email'));
                }
            } catch (Exception $e) {
                error_log('NOVALIA: EXCEPTION lors envoi email - ' . $e->getMessage());
                error_log('NOVALIA: Stack trace: ' . $e->getTraceAsString());
                wp_send_json_error(array('message' => 'Erreur PDF/Email: ' . $e->getMessage()));
            }
        } else {
            error_log('NOVALIA: ERREUR - Devis ID est NULL');
            wp_send_json_error(array('message' => 'Erreur lors de la création du devis'));
        }
        
        error_log('=== NOVALIA: Fin submit_devis ===');
    }
    
    public function update_statut() {
        check_ajax_referer('novalia_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
            return;
        }
        
        $devis_id = intval($_POST['devis_id']);
        $statut = sanitize_text_field($_POST['statut']);
        
        $result = Novalia_Devis::update_statut($devis_id, $statut);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Statut mis à jour',
                'statut' => $statut,
                'statut_label' => Novalia_Devis::get_statut_label($statut)
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la mise à jour'));
        }
    }
    
    public function delete_devis() {
        check_ajax_referer('novalia_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
            return;
        }
        
        $devis_id = intval($_POST['devis_id']);
        
        $result = Novalia_Devis::delete_devis($devis_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Devis supprimé'));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
        }
    }
    
    public function add_item() {
        check_ajax_referer('novalia_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
            return;
        }
        
        $nom = sanitize_text_field($_POST['nom']);
        $categorie = sanitize_text_field($_POST['categorie']);
        $volume = floatval($_POST['volume']);
        
        if (empty($nom) || empty($categorie) || $volume <= 0) {
            wp_send_json_error(array('message' => 'Données invalides'));
            return;
        }
        
        $item_id = Novalia_Items::add_item($nom, $categorie, $volume);
        
        if ($item_id) {
            $item = Novalia_Items::get_item($item_id);
            wp_send_json_success(array(
                'message' => 'Objet ajouté',
                'item' => $item
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de l\'ajout'));
        }
    }
    
    public function update_item() {
        check_ajax_referer('novalia_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        $nom = sanitize_text_field($_POST['nom']);
        $categorie = sanitize_text_field($_POST['categorie']);
        $volume = floatval($_POST['volume']);
        
        if (empty($nom) || empty($categorie) || $volume <= 0) {
            wp_send_json_error(array('message' => 'Données invalides'));
            return;
        }
        
        $result = Novalia_Items::update_item($item_id, $nom, $categorie, $volume);
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Objet mis à jour',
                'nom' => $nom,
                'volume' => $volume
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la mise à jour'));
        }
    }
    
    public function delete_item() {
        check_ajax_referer('novalia_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
            return;
        }
        
        $item_id = intval($_POST['item_id']);
        
        $result = Novalia_Items::delete_item($item_id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Objet supprimé'));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
        }
    }
}