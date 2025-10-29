<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Email {
    
    public static function send_devis($devis_id) {
        error_log('NOVALIA EMAIL: Début send_devis ID=' . $devis_id);
        
        $devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$devis) {
            error_log('NOVALIA EMAIL: Devis introuvable');
            return false;
        }
        
        error_log('NOVALIA EMAIL: Devis trouvé - ' . $devis->numero_devis . ' - Type: ' . $devis->type_demenagement);
        
        // Génération des PDFs selon le type de déménagement choisi
        $pdf_standard = null;
        $pdf_complet = null;
        
        try {
            if ($devis->type_demenagement === 'complet') {
                // Si complet : envoyer les DEUX devis
                error_log('NOVALIA EMAIL: Type COMPLET - Génération des 2 PDFs');
                
                $pdf_generator_standard = new Novalia_PDF($devis_id);
                $pdf_standard = $pdf_generator_standard->generate_pdf_standard();
                unset($pdf_generator_standard);
                
                $pdf_generator_complet = new Novalia_PDF($devis_id);
                $pdf_complet = $pdf_generator_complet->generate_pdf_complet();
                unset($pdf_generator_complet);
                
            } else {
                // Si standard : envoyer SEULEMENT le devis standard
                error_log('NOVALIA EMAIL: Type STANDARD - Génération d\'un seul PDF');
                
                $pdf_generator_standard = new Novalia_PDF($devis_id);
                $pdf_standard = $pdf_generator_standard->generate_pdf_standard();
                unset($pdf_generator_standard);
            }
            
            error_log('NOVALIA EMAIL: PDFs générés avec succès');
        } catch (Exception $e) {
            error_log('NOVALIA EMAIL: ERREUR génération PDF - ' . $e->getMessage());
            return false;
        }
        
        // Préparation de l'email avec template HTML
        $to = $devis->email_client;
        $subject = 'Votre devis de déménagement - ' . $devis->numero_devis;
        $message = self::get_email_template_html($devis);
        $headers = self::get_email_headers_html();
        
        // Attachments - selon le type
        $attachments = array();
        
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/novalia-temp/';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        // Toujours créer le PDF standard
        $filename_standard = $temp_dir . 'devis_standard_' . $devis->numero_devis . '.pdf';
        file_put_contents($filename_standard, $pdf_standard);
        $attachments[] = $filename_standard;
        
        // Ajouter le PDF complet uniquement si type complet
        if ($devis->type_demenagement === 'complet') {
            $filename_complet = $temp_dir . 'devis_complet_' . $devis->numero_devis . '.pdf';
            file_put_contents($filename_complet, $pdf_complet);
            $attachments[] = $filename_complet;
        }
        
        error_log('NOVALIA EMAIL: ' . count($attachments) . ' fichier(s) PDF créé(s) dans ' . $temp_dir);
        
        // Envoi de l'email
        error_log('NOVALIA EMAIL: Envoi email à ' . $to);
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        error_log('NOVALIA EMAIL: Email principal envoyé=' . ($sent ? 'OK' : 'ECHEC'));
        
        // Copie à l'entreprise
        if ($sent) {
            $admin_email = get_option('admin_email');
            $subject_admin = 'Nouveau devis - ' . $devis->numero_devis;
            $message_admin = self::get_admin_email_template($devis);
            
            wp_mail($admin_email, $subject_admin, $message_admin, $headers, $attachments);
            error_log('NOVALIA EMAIL: Email admin envoyé');
        }
        
        // Nettoyage des fichiers temporaires
        foreach ($attachments as $file) {
            @unlink($file);
        }
        error_log('NOVALIA EMAIL: Fichiers temp supprimés');
        
        error_log('NOVALIA EMAIL: Fin send_devis - Résultat=' . ($sent ? 'SUCCESS' : 'ECHEC'));
        return $sent;
    }
    
    private static function get_email_template_html($devis) {
        // Charger le template HTML
        $template_path = NOVALIA_PLUGIN_DIR . 'templates/email-template.html';
        
        if (!file_exists($template_path)) {
            error_log('NOVALIA EMAIL: Template HTML introuvable, utilisation template texte');
            error_log('NOVALIA EMAIL: Chemin cherche: ' . $template_path);
            return self::get_email_template_text($devis);
        }
        
        $template = file_get_contents($template_path);
        
        // Remplacer les variables
        $replacements = array(
            '{{NOM_CLIENT}}' => $devis->nom_client,
            '{{NUMERO_DEVIS}}' => $devis->numero_devis,
            '{{DATE_DEMENAGEMENT}}' => Novalia_Devis::format_date($devis->date_demenagement),
        );
        
        // Adapter le message selon le type
        if ($devis->type_demenagement === 'standard') {
            $template = str_replace(
                '<p>Vous trouverez ci-joint vos deux propositions de devis en PDF :</p>',
                '<p>Vous trouverez ci-joint votre devis de déménagement standard en PDF :</p>',
                $template
            );
            // Cacher le bouton "Déménagement Complet"
            $template = str_replace(
                '<a href="#" class="pdf-button complet">Déménagement Complet</a>',
                '',
                $template
            );
        }
        
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        return $message;
    }
    
    private static function get_email_template_text($devis) {
        // Fallback en texte brut
        $message = "Bonjour " . $devis->nom_client . ",\n\n";
        $message .= "Merci pour votre demande de devis de déménagement.\n\n";
        
        if ($devis->type_demenagement === 'complet') {
            $message .= "Vous trouverez ci-joint deux devis :\n";
            $message .= "- Déménagement standard\n";
            $message .= "- Déménagement complet (avec emballage)\n\n";
        } else {
            $message .= "Vous trouverez ci-joint votre devis de déménagement standard.\n\n";
        }
        
        $message .= "DÉTAILS DE VOTRE DÉMÉNAGEMENT :\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Numéro de devis : " . $devis->numero_devis . "\n";
        $message .= "Date : " . Novalia_Devis::format_date($devis->date_demenagement) . "\n";
        $message .= "Départ : " . $devis->adresse_depart . "\n";
        $message .= "Arrivée : " . $devis->adresse_arrivee . "\n";
        $message .= "Distance : " . number_format($devis->distance, 2) . " km\n";
        $message .= "Volume total : " . number_format($devis->volume_total, 2) . " m³\n\n";
        
        if ($devis->type_demenagement === 'complet') {
            $message .= "TARIFS :\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "Déménagement standard : " . Novalia_Tarifs::format_prix($devis->prix_standard) . "\n";
            $message .= "Déménagement complet : " . Novalia_Tarifs::format_prix($devis->prix_complet) . "\n\n";
        } else {
            $message .= "TARIF :\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "Déménagement standard : " . Novalia_Tarifs::format_prix($devis->prix_standard) . "\n\n";
        }
        
        $message .= "⚠️ IMPORTANT : Ce" . ($devis->type_demenagement === 'complet' ? "s prix sont des estimations" : " prix est une estimation") . " basée sur les informations fournies.\n";
        $message .= "Une visite sur place sera nécessaire pour établir un devis définitif.\n\n";
        $message .= "Notre équipe vous contactera rapidement pour planifier cette visite.\n\n";
        $message .= "Cordialement,\n";
        $message .= "L'équipe Novalia Group\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Novalia Group\n";
        $message .= "Email : info@novaliagroup.ch\n";
        $message .= "Web : www.novaliagroup.ch\n";
        
        return $message;
    }
    
    private static function get_admin_email_template($devis) {
        $message = "NOUVEAU DEVIS REÇU\n\n";
        $message .= "Numéro : " . $devis->numero_devis . "\n";
        $message .= "Date de création : " . date('d/m/Y H:i') . "\n";
        $message .= "Type demandé : " . strtoupper($devis->type_demenagement) . "\n\n";
        $message .= "CLIENT :\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Nom : " . $devis->nom_client . "\n";
        $message .= "Email : " . $devis->email_client . "\n";
        $message .= "Téléphone : " . $devis->telephone_client . "\n\n";
        $message .= "DÉMÉNAGEMENT :\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Date : " . Novalia_Devis::format_date($devis->date_demenagement) . "\n";
        $message .= "Départ : " . $devis->adresse_depart . "\n";
        $message .= "Arrivée : " . $devis->adresse_arrivee . "\n";
        $message .= "Distance : " . number_format($devis->distance, 2) . " km\n";
        $message .= "Volume total : " . number_format($devis->volume_total, 2) . " m³\n\n";
        $message .= "TARIFS :\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "Standard : " . Novalia_Tarifs::format_prix($devis->prix_standard) . "\n";
        $message .= "Complet : " . Novalia_Tarifs::format_prix($devis->prix_complet) . "\n\n";
        
        if ($devis->type_demenagement === 'complet') {
            $message .= "Les 2 PDF sont joints à cet email.\n";
        } else {
            $message .= "Le PDF standard est joint à cet email.\n";
        }
        
        return $message;
    }
    
    private static function get_email_headers_html() {
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: Novalia Group <info@novaliagroup.ch>';
        
        return $headers;
    }
    
    public static function send_notification_admin($devis_id, $action) {
        $devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$devis) {
            return false;
        }
        
        $admin_email = get_option('admin_email');
        $subject = 'Action sur devis - ' . $devis->numero_devis;
        
        $message = "Une action a été effectuée sur un devis.\n\n";
        $message .= "Action : " . $action . "\n";
        $message .= "Devis : " . $devis->numero_devis . "\n";
        $message .= "Client : " . $devis->nom_client . "\n";
        $message .= "Date : " . date('d/m/Y H:i') . "\n";
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Envoyer notification à l'entreprise avec fiche technique
     */
    public static function send_entreprise_notification($devis_id) {
        error_log('NOVALIA EMAIL: Envoi notification entreprise ID=' . $devis_id);
        
        $devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$devis) {
            error_log('NOVALIA EMAIL: Devis introuvable pour notification');
            return false;
        }
        
        // Email de l'entreprise
        $admin_email = get_option('admin_email');
        $entreprise_email = get_option('novalia_email_entreprise', 'info@novaliagroup.ch');
        
        // Sujet
        $subject = '[NOUVEAU DEVIS] ' . $devis->numero_devis . ' - ' . $devis->nom_client;
        
        // Message
        $message = "Un nouveau devis a été généré sur votre site.\n\n";
        $message .= "=== INFORMATIONS CLIENT ===\n";
        $message .= "Nom : " . $devis->nom_client . "\n";
        $message .= "Email : " . $devis->email_client . "\n";
        $message .= "Téléphone : " . $devis->telephone_client . "\n\n";
        
        $message .= "=== DÉTAILS DÉMÉNAGEMENT ===\n";
        $message .= "Numéro devis : " . $devis->numero_devis . "\n";
        $message .= "Type : " . strtoupper($devis->type_demenagement) . "\n";
        $message .= "Date : " . date('d/m/Y', strtotime($devis->date_demenagement)) . "\n";
        $message .= "De : " . $devis->adresse_depart . "\n";
        $message .= "Vers : " . $devis->adresse_arrivee . "\n";
        $message .= "Distance : " . $devis->distance . " km\n";
        $message .= "Volume : " . $devis->volume_total . " m³\n\n";
        
        $message .= "Prix " . $devis->type_demenagement . " : " . 
                   ($devis->type_demenagement === 'complet' ? $devis->prix_complet : $devis->prix_standard) . " CHF\n\n";
        
        $message .= "La fiche technique pour vos employés est en pièce jointe.\n\n";
        $message .= "Cordialement,\n";
        $message .= "Système Novalia";
        
        // Génération de la fiche technique
        require_once(NOVALIA_PLUGIN_DIR . 'class-novalia-pdf.php');
        
        try {
            $pdf_fiche = new Novalia_PDF($devis_id);
            $fiche_path = $pdf_fiche->generate_fiche_technique();
            
            if (!$fiche_path || !file_exists($fiche_path)) {
                error_log('NOVALIA EMAIL: Erreur génération fiche technique');
                return false;
            }
            
            error_log('NOVALIA EMAIL: Fiche technique générée: ' . $fiche_path);
            
            // Envoi email avec pièce jointe
            $headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'From: Novalia System <' . $admin_email . '>'
            );
            
            $sent = wp_mail($entreprise_email, $subject, $message, $headers, array($fiche_path));
            
            error_log('NOVALIA EMAIL: Notification entreprise ' . ($sent ? 'envoyée' : 'ÉCHOUÉE'));
            
            // Nettoyage
            @unlink($fiche_path);
            
            return $sent;
            
        } catch (Exception $e) {
            error_log('NOVALIA EMAIL: Erreur notification entreprise: ' . $e->getMessage());
            return false;
        }
    }
}