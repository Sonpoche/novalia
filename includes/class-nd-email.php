<?php
/**
 * Gestion des emails
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Email {
    
    /**
     * Envoi de l'email au client avec le devis
     */
    public static function send_customer_quote($quote) {
        $email_settings = get_option('nd_email');
        $company = get_option('nd_company');
        
        // Destinataire
        $to = $quote['customer_email'];
        
        // Sujet
        $subject = $email_settings['customer_subject'] ?? 'Votre devis de déménagement Novalia';
        $subject = str_replace('{quote_number}', $quote['quote_number'], $subject);
        
        // Corps de l'email HTML
        $message = self::get_customer_email_template($quote);
        
        // En-têtes
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . ($company['name'] ?? 'Novalia') . ' <' . ($company['email'] ?? get_option('admin_email')) . '>'
        ];
        
        // Pièce jointe (PDF)
        $attachments = [];
        if (!empty($quote['pdf_path'])) {
            $upload_dir = wp_upload_dir();
            $pdf_file = $upload_dir['basedir'] . $quote['pdf_path'];
            
            if (file_exists($pdf_file)) {
                $attachments[] = $pdf_file;
            }
        }
        
        // Envoi
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        
        return [
            'success' => $sent,
            'message' => $sent ? 'Email envoyé avec succès' : 'Erreur lors de l\'envoi de l\'email'
        ];
    }
    
    /**
     * Envoi de la notification à l'administrateur
     */
    public static function send_admin_notification($quote) {
        $email_settings = get_option('nd_email');
        $company = get_option('nd_company');
        
        // Destinataire
        $to = $email_settings['admin_email'] ?? get_option('admin_email');
        
        // Sujet
        $subject = $email_settings['admin_subject'] ?? 'Nouveau devis généré';
        $subject = str_replace('{quote_number}', $quote['quote_number'], $subject);
        
        // Corps de l'email HTML
        $message = self::get_admin_email_template($quote);
        
        // En-têtes
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $quote['customer_email']
        ];
        
        // Pièce jointe (PDF)
        $attachments = [];
        if (!empty($quote['pdf_path'])) {
            $upload_dir = wp_upload_dir();
            $pdf_file = $upload_dir['basedir'] . $quote['pdf_path'];
            
            if (file_exists($pdf_file)) {
                $attachments[] = $pdf_file;
            }
        }
        
        // Envoi
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        
        return [
            'success' => $sent,
            'message' => $sent ? 'Notification envoyée' : 'Erreur notification admin'
        ];
    }
    
    /**
     * Template email client
     */
    private static function get_customer_email_template($quote) {
        $company = get_option('nd_company');
        
        ob_start();
        include ND_PLUGIN_DIR . 'templates/email/quote-customer.php';
        return ob_get_clean();
    }
    
    /**
     * Template email administrateur
     */
    private static function get_admin_email_template($quote) {
        $company = get_option('nd_company');
        
        ob_start();
        include ND_PLUGIN_DIR . 'templates/email/quote-admin.php';
        return ob_get_clean();
    }
    
    /**
     * Test d'envoi d'email
     */
    public static function send_test_email($to) {
        $subject = 'Test email - Novalia Devis';
        $message = '<h2>✅ Test réussi !</h2><p>Si vous recevez cet email, la configuration email de Novalia Devis fonctionne correctement.</p>';
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        return [
            'success' => $sent,
            'message' => $sent ? 'Email de test envoyé avec succès' : 'Erreur lors de l\'envoi du test'
        ];
    }
}