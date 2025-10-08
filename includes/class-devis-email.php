<?php
/**
 * Gestion de l'envoi d'emails
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-email.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Email {
    
    /**
     * Envoyer le devis par email au client
     */
    public function send_to_client($devis_data, $pdf_content) {
        $settings = get_option('devis_demenagement_settings');
        
        // Informations du destinataire
        $to = $devis_data['client_email'];
        $subject = 'Votre devis de déménagement - ' . $settings['company_name'];
        
        // Corps de l'email en HTML
        $message = $this->get_email_template_client($devis_data, $settings);
        
        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $settings['company_name'] . ' <' . $settings['company_email'] . '>',
        );
        
        // Sauvegarder temporairement le PDF pour l'attacher
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/devis/temp/';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $temp_filename = 'devis-' . time() . '-' . uniqid() . '.pdf';
        $temp_filepath = $temp_dir . $temp_filename;
        file_put_contents($temp_filepath, $pdf_content);
        
        // Envoyer l'email avec la pièce jointe
        $sent = wp_mail($to, $subject, $message, $headers, array($temp_filepath));
        
        // Supprimer le fichier temporaire
        if (file_exists($temp_filepath)) {
            unlink($temp_filepath);
        }
        
        return $sent;
    }
    
    /**
     * Envoyer une copie à l'administrateur
     */
    public function send_to_admin($devis_data, $pdf_content) {
        $settings = get_option('devis_demenagement_settings');
        
        // Informations du destinataire
        $to = $settings['company_email'];
        $subject = 'Nouveau devis demandé - ' . $devis_data['client_nom'];
        
        // Corps de l'email en HTML
        $message = $this->get_email_template_admin($devis_data, $settings);
        
        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $devis_data['client_email']
        );
        
        // Sauvegarder temporairement le PDF pour l'attacher
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/devis/temp/';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $temp_filename = 'devis-admin-' . time() . '-' . uniqid() . '.pdf';
        $temp_filepath = $temp_dir . $temp_filename;
        file_put_contents($temp_filepath, $pdf_content);
        
        // Envoyer l'email avec la pièce jointe
        $sent = wp_mail($to, $subject, $message, $headers, array($temp_filepath));
        
        // Supprimer le fichier temporaire
        if (file_exists($temp_filepath)) {
            unlink($temp_filepath);
        }
        
        return $sent;
    }
    
    /**
     * Template d'email pour le client
     */
    private function get_email_template_client($devis_data, $settings) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #3498db; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; margin-top: 20px; }
                .info-box { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #3498db; }
                .highlight { background-color: #2ecc71; color: white; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #7f8c8d; font-size: 12px; }
                table { width: 100%; margin: 10px 0; }
                td { padding: 8px 0; }
                .label { font-weight: bold; color: #555; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . esc_html($settings['company_name']) . '</h1>
                    <p>Votre devis de déménagement</p>
                </div>
                
                <div class="content">
                    <p>Bonjour ' . esc_html($devis_data['client_nom']) . ',</p>
                    
                    <p>Merci d\'avoir utilisé notre outil d\'estimation en ligne. Vous trouverez ci-joint votre devis de déménagement personnalisé.</p>
                    
                    <div class="info-box">
                        <h3>Récapitulatif de votre déménagement</h3>
                        <table>
                            <tr>
                                <td class="label">De :</td>
                                <td>' . esc_html($devis_data['adresse_depart']) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Vers :</td>
                                <td>' . esc_html($devis_data['adresse_arrivee']) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Distance :</td>
                                <td>' . number_format($devis_data['distance_km'], 2, ',', ' ') . ' km</td>
                            </tr>
                            <tr>
                                <td class="label">Volume :</td>
                                <td>' . number_format($devis_data['volume_total_m3'], 2, ',', ' ') . ' m³</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="highlight">
                        Prix estimé : ' . number_format($devis_data['prix_total'], 2, ',', ' ') . ' € TTC
                    </div>
                    
                    <p><strong>Ce devis est valable 30 jours.</strong></p>
                    
                    <p>Pour toute question ou pour confirmer votre déménagement, n\'hésitez pas à nous contacter :</p>
                    <ul>
                        ' . (!empty($settings['company_phone']) ? '<li>Téléphone : ' . esc_html($settings['company_phone']) . '</li>' : '') . '
                        <li>Email : ' . esc_html($settings['company_email']) . '</li>
                    </ul>
                    
                    <p>Nous serons ravis de vous accompagner dans votre déménagement !</p>
                    
                    <p>Cordialement,<br><strong>' . esc_html($settings['company_name']) . '</strong></p>
                </div>
                
                <div class="footer">
                    <p>' . esc_html($settings['company_name']) . '</p>
                    ' . (!empty($settings['company_address']) ? '<p>' . esc_html($settings['company_address']) . '</p>' : '') . '
                    <p>Cet email a été généré automatiquement, merci de ne pas y répondre directement.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $html;
    }
    
    /**
     * Template d'email pour l'administrateur
     */
    private function get_email_template_admin($devis_data, $settings) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #e74c3c; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; margin-top: 20px; }
                .info-box { background-color: white; padding: 20px; margin: 20px 0; border-left: 4px solid #e74c3c; }
                .highlight { background-color: #2ecc71; color: white; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; }
                table { width: 100%; margin: 10px 0; }
                td { padding: 8px 0; }
                .label { font-weight: bold; color: #555; width: 40%; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚚 Nouveau devis demandé</h1>
                </div>
                
                <div class="content">
                    <p><strong>Un client vient de demander un devis via votre site web.</strong></p>
                    
                    <div class="info-box">
                        <h3>Informations du client</h3>
                        <table>
                            <tr>
                                <td class="label">Nom :</td>
                                <td>' . esc_html($devis_data['client_nom']) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Email :</td>
                                <td><a href="mailto:' . esc_attr($devis_data['client_email']) . '">' . esc_html($devis_data['client_email']) . '</a></td>
                            </tr>
                            <tr>
                                <td class="label">Téléphone :</td>
                                <td>' . esc_html($devis_data['client_telephone']) . '</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="info-box">
                        <h3>Détails du déménagement</h3>
                        <table>
                            <tr>
                                <td class="label">Adresse de départ :</td>
                                <td>' . esc_html($devis_data['adresse_depart']) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Adresse d\'arrivée :</td>
                                <td>' . esc_html($devis_data['adresse_arrivee']) . '</td>
                            </tr>
                            <tr>
                                <td class="label">Distance :</td>
                                <td>' . number_format($devis_data['distance_km'], 2, ',', ' ') . ' km</td>
                            </tr>
                            <tr>
                                <td class="label">Volume total :</td>
                                <td>' . number_format($devis_data['volume_total_m3'], 2, ',', ' ') . ' m³</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="highlight">
                        Montant du devis : ' . number_format($devis_data['prix_total'], 2, ',', ' ') . ' € TTC
                    </div>
                    
                    ' . (!empty($devis_data['notes']) ? '
                    <div class="info-box">
                        <h3>Notes du client</h3>
                        <p>' . nl2br(esc_html($devis_data['notes'])) . '</p>
                    </div>
                    ' : '') . '
                    
                    <p><strong>Action requise :</strong> Contactez ce client pour finaliser le devis et planifier le déménagement.</p>
                    
                    <p>Le devis PDF complet est joint à cet email.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $html;
    }
    
    /**
     * Envoyer les deux emails (client + admin)
     */
    public function send_all($devis_data, $pdf_content) {
        $client_sent = $this->send_to_client($devis_data, $pdf_content);
        $admin_sent = $this->send_to_admin($devis_data, $pdf_content);
        
        return array(
            'client' => $client_sent,
            'admin' => $admin_sent,
            'success' => ($client_sent && $admin_sent)
        );
    }
}