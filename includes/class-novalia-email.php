<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Email {
    
    public static function send_devis($devis_id) {
        error_log('NOVALIA EMAIL: Debut send_devis ID=' . $devis_id);
        
        $devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$devis) {
            error_log('NOVALIA EMAIL: Devis introuvable');
            return false;
        }
        
        error_log('NOVALIA EMAIL: Devis trouve - ' . $devis->numero_devis . ' - Type: ' . $devis->type_demenagement);
        
        $pdf_standard = null;
        $pdf_complet = null;
        
        try {
            error_log('NOVALIA EMAIL: Creation instance unique PDF generator');
            $pdf_generator = new Novalia_PDF($devis_id);
            
            if ($devis->type_demenagement === 'complet') {
                error_log('NOVALIA EMAIL: Type COMPLET - Generation des 2 PDFs');
                
                $pdf_standard = $pdf_generator->generate_pdf_standard();
                error_log('NOVALIA EMAIL: PDF standard genere');
                
                $pdf_complet = $pdf_generator->generate_pdf_complet();
                error_log('NOVALIA EMAIL: PDF complet genere');
                
            } else {
                error_log('NOVALIA EMAIL: Type STANDARD - Generation d\'un seul PDF');
                
                $pdf_standard = $pdf_generator->generate_pdf_standard();
                error_log('NOVALIA EMAIL: PDF standard genere');
            }
            
            unset($pdf_generator);
            error_log('NOVALIA EMAIL: Instance PDF generator liberee');
            
            error_log('NOVALIA EMAIL: PDFs devis generes avec succes');
        } catch (Exception $e) {
            error_log('NOVALIA EMAIL: ERREUR generation PDF devis - ' . $e->getMessage());
            error_log('NOVALIA EMAIL: Stack trace: ' . $e->getTraceAsString());
            return false;
        }
        
        try {
            error_log('NOVALIA EMAIL: Debut generation fiche technique');
            $fiche_generator = new Novalia_Fiche_Technique($devis_id);
            $fiche_relative_path = $fiche_generator->generate_and_save();
            unset($fiche_generator);
            error_log('NOVALIA EMAIL: Fiche technique sauvegardee: ' . $fiche_relative_path);
        } catch (Exception $e) {
            error_log('NOVALIA EMAIL: ERREUR generation fiche technique (non bloquante) - ' . $e->getMessage());
            error_log('NOVALIA EMAIL: Stack trace fiche: ' . $e->getTraceAsString());
        }
        
        $to = $devis->email_client;
        $subject = 'Votre devis de demenagement - ' . $devis->numero_devis;
        $message = self::get_email_template_html($devis);
        $headers = self::get_email_headers_html();
        
        $attachments = array();
        
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/novalia-temp/';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $filename_standard = $temp_dir . 'devis_standard_' . $devis->numero_devis . '.pdf';
        file_put_contents($filename_standard, $pdf_standard);
        $attachments[] = $filename_standard;
        
        if ($devis->type_demenagement === 'complet') {
            $filename_complet = $temp_dir . 'devis_complet_' . $devis->numero_devis . '.pdf';
            file_put_contents($filename_complet, $pdf_complet);
            $attachments[] = $filename_complet;
        }
        
        error_log('NOVALIA EMAIL: ' . count($attachments) . ' fichier(s) PDF cree(s) dans ' . $temp_dir);
        
        error_log('NOVALIA EMAIL: Envoi email a ' . $to);
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        error_log('NOVALIA EMAIL: Email principal envoye=' . ($sent ? 'OK' : 'ECHEC'));
        
        if ($sent) {
            $admin_email = get_option('admin_email');
            $subject_admin = 'Nouveau devis - ' . $devis->numero_devis;
            $message_admin = self::get_admin_email_template($devis);
            
            wp_mail($admin_email, $subject_admin, $message_admin, $headers, $attachments);
            error_log('NOVALIA EMAIL: Email admin envoye');
        }
        
        foreach ($attachments as $file) {
            @unlink($file);
        }
        error_log('NOVALIA EMAIL: Fichiers temp supprimes');
        
        error_log('NOVALIA EMAIL: Fin send_devis - Resultat=' . ($sent ? 'SUCCESS' : 'ECHEC'));
        return $sent;
    }
    
    private static function get_email_template_html($devis) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Votre devis Novalia Group</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                }
                .header {
                    background-color: #1A2332;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    color: #ffffff;
                    margin: 0;
                    font-size: 28px;
                    font-weight: 300;
                }
                .logo {
                    color: #2BBBAD;
                    font-weight: bold;
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                .content {
                    padding: 40px 30px;
                }
                .greeting {
                    font-size: 16px;
                    color: #333333;
                    margin-bottom: 20px;
                }
                .devis-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #2BBBAD;
                    padding: 20px;
                    margin: 30px 0;
                }
                .devis-box h2 {
                    color: #1A2332;
                    margin-top: 0;
                    font-size: 20px;
                }
                .devis-info {
                    margin: 15px 0;
                }
                .devis-info strong {
                    color: #1A2332;
                }
                .warning-box {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffa500;
                    padding: 15px;
                    margin: 20px 0;
                }
                .warning-box strong {
                    color: #856404;
                }
                .footer {
                    background-color: #1A2332;
                    color: #ffffff;
                    padding: 20px 30px;
                    text-align: center;
                    font-size: 14px;
                }
                .footer a {
                    color: #2BBBAD;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">Novalia Group</div>
                    <h1>Votre devis de demenagement</h1>
                </div>
                
                <div class="content">
                    <p class="greeting">Bonjour <strong><?php echo esc_html($devis->nom_client); ?></strong>,</p>
                    
                    <p>Nous vous remercions pour votre demande de devis.</p>
                    
                    <div class="devis-box">
                        <h2>Votre devis N° <?php echo esc_html($devis->numero_devis); ?></h2>
                        
                        <div class="devis-info">
                            <strong>Date du demenagement :</strong> <?php echo date('d/m/Y', strtotime($devis->date_demenagement)); ?>
                        </div>
                        
                        <p>Vous trouverez ci-joint votre devis au format PDF.</p>
                    </div>
                    
                    <div class="warning-box">
                        <strong>⚠ Important :</strong> Ce devis est une estimation basee sur les informations que vous nous avez transmises. Une visite technique sera necessaire pour etablir un devis definitif.
                    </div>
                    
                    <p>Notre equipe vous contactera dans les plus brefs delais pour convenir d'un rendez-vous.</p>
                    
                    <p style="margin-top: 30px;">Cordialement,</p>
                    <p><strong>L'equipe Novalia Group</strong></p>
                </div>
                
                <div class="footer">
                    <p>Novalia Group - Votre confiance, notre energie</p>
                    <p>
                        Email : <a href="mailto:info@novaliagroup.ch">info@novaliagroup.ch</a><br>
                        Web : <a href="https://novaliagroup.ch">www.novaliagroup.ch</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private static function get_admin_email_template($devis) {
        $message = "Un nouveau devis a ete genere.\n\n";
        $message .= "=== INFORMATIONS CLIENT ===\n";
        $message .= "Nom : " . $devis->nom_client . "\n";
        $message .= "Email : " . $devis->email_client . "\n";
        $message .= "Telephone : " . $devis->telephone_client . "\n\n";
        
        $message .= "=== DETAILS DEMENAGEMENT ===\n";
        $message .= "Numero devis : " . $devis->numero_devis . "\n";
        $message .= "Type : " . strtoupper($devis->type_demenagement) . "\n";
        $message .= "Date : " . date('d/m/Y', strtotime($devis->date_demenagement)) . "\n";
        $message .= "De : " . $devis->adresse_depart . "\n";
        $message .= "Vers : " . $devis->adresse_arrivee . "\n";
        $message .= "Distance : " . $devis->distance . " km\n";
        $message .= "Volume : " . $devis->volume_total . " m3\n\n";
        
        $message .= "Prix standard : " . number_format($devis->prix_standard, 2) . " CHF\n";
        $message .= "Prix complet : " . number_format($devis->prix_complet, 2) . " CHF\n\n";
        
        $message .= "La fiche technique est disponible dans l'administration WordPress.\n";
        $message .= "Accedez a l'admin pour telecharger la fiche technique.";
        
        return $message;
    }
    
    private static function get_email_headers_html() {
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: Novalia Group <info@novaliagroup.ch>';
        $headers[] = 'Reply-To: info@novaliagroup.ch';
        
        return $headers;
    }
}