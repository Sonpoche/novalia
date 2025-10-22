<?php
/**
 * Gestion de la génération de PDF pour les devis
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Chargement de TCPDF
if (!class_exists('TCPDF')) {
    // Essai 1 : Autoload Composer dans le plugin
    if (file_exists(ND_PLUGIN_DIR . 'vendor/autoload.php')) {
        require_once ND_PLUGIN_DIR . 'vendor/autoload.php';
    }
    
    // Essai 2 : Chargement direct de TCPDF
    if (!class_exists('TCPDF') && file_exists(ND_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php')) {
        require_once ND_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php';
    }
    
    // Essai 3 : TCPDF dans wp-content/vendor
    if (!class_exists('TCPDF') && file_exists(WP_CONTENT_DIR . '/vendor/autoload.php')) {
        require_once WP_CONTENT_DIR . '/vendor/autoload.php';
    }
    
    // Essai 4 : TCPDF directement dans wp-content
    if (!class_exists('TCPDF') && file_exists(WP_CONTENT_DIR . '/vendor/tecnickcom/tcpdf/tcpdf.php')) {
        require_once WP_CONTENT_DIR . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    }
    
    // Si toujours pas disponible, afficher un message d'erreur détaillé
    if (!class_exists('TCPDF')) {
        $error_msg = '<h2>TCPDF n\'est pas trouvé</h2>';
        $error_msg .= '<p>Le plugin a cherché TCPDF dans les emplacements suivants :</p>';
        $error_msg .= '<ul>';
        $error_msg .= '<li>' . ND_PLUGIN_DIR . 'vendor/autoload.php - ' . (file_exists(ND_PLUGIN_DIR . 'vendor/autoload.php') ? '✅ Trouvé' : '❌ Non trouvé') . '</li>';
        $error_msg .= '<li>' . ND_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php - ' . (file_exists(ND_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php') ? '✅ Trouvé' : '❌ Non trouvé') . '</li>';
        $error_msg .= '</ul>';
        $error_msg .= '<p><strong>Solutions :</strong></p>';
        $error_msg .= '<ol>';
        $error_msg .= '<li>Exécuter <code>composer install</code> dans le dossier du plugin</li>';
        $error_msg .= '<li>Ou copier le dossier TCPDF dans <code>' . ND_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/</code></li>';
        $error_msg .= '</ol>';
        wp_die($error_msg);
    }
}

class ND_PDF {
    
    /**
     * Génération du PDF d'un devis
     */
    public static function generate_quote_pdf($quote) {
        try {
            // Création du dossier de stockage
            $upload_dir = wp_upload_dir();
            $pdf_dir = $upload_dir['basedir'] . '/novalia-devis/';
            
            if (!file_exists($pdf_dir)) {
                wp_mkdir_p($pdf_dir);
            }
            
            // Nom du fichier
            $filename = 'devis-' . $quote['quote_number'] . '.pdf';
            $file_path = $pdf_dir . $filename;
            $file_url = $upload_dir['baseurl'] . '/novalia-devis/' . $filename;
            
            // Création du PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // Configuration du document
            self::setup_pdf_config($pdf, $quote);
            
            // Ajout d'une page
            $pdf->AddPage();
            
            // Contenu du PDF
            $html = self::generate_pdf_content($quote);
            
            // Écriture du HTML
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Sauvegarde du fichier
            $pdf->Output($file_path, 'F');
            
            return [
                'success' => true,
                'file_path' => '/novalia-devis/' . $filename,
                'file_url' => $file_url,
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Configuration du PDF
     */
    private static function setup_pdf_config($pdf, $quote) {
        $company = get_option('nd_company');
        
        // Informations du document
        $pdf->SetCreator('Novalia Devis');
        $pdf->SetAuthor($company['name'] ?? 'Novalia');
        $pdf->SetTitle('Devis ' . $quote['quote_number']);
        $pdf->SetSubject('Devis de déménagement');
        
        // Marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        
        // Police par défaut
        $pdf->SetFont('helvetica', '', 10);
        
        // En-tête et pied de page personnalisés
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
    }
    
    /**
     * Génération du contenu HTML du PDF
     */
    private static function generate_pdf_content($quote) {
        $company = get_option('nd_company');
        $pdf_settings = get_option('nd_pdf');
        
        // Calcul du détail des prix
        $pricing_data = [
            'distance' => $quote['distance'],
            'volume' => $quote['total_volume']
        ];
        $calculation = ND_Pricing::calculate_quote($pricing_data);
        
        // Charger le template
        ob_start();
        include ND_PLUGIN_DIR . 'templates/pdf/quote-template.php';
        return ob_get_clean();
    }
    
    /**
     * Téléchargement direct d'un PDF
     */
    public static function download_pdf($quote_id) {
        $quote = ND_Quotes::get_quote_with_items($quote_id);
        
        if (!$quote) {
            wp_die('Devis introuvable');
        }
        
        // Vérification du PDF existant
        $upload_dir = wp_upload_dir();
        
        if (!empty($quote['pdf_path'])) {
            $pdf_file = $upload_dir['basedir'] . $quote['pdf_path'];
            
            if (file_exists($pdf_file)) {
                // Téléchargement du fichier existant
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdf_file) . '"');
                header('Content-Length: ' . filesize($pdf_file));
                readfile($pdf_file);
                exit;
            }
        }
        
        // Génération d'un nouveau PDF si inexistant
        $result = self::generate_quote_pdf($quote);
        
        if ($result['success']) {
            $pdf_file = $upload_dir['basedir'] . $result['file_path'];
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($pdf_file));
            readfile($pdf_file);
            exit;
        }
        
        wp_die('Erreur lors de la génération du PDF');
    }
}