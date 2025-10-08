<?php
/**
 * Générateur de PDF pour les devis
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-pdf.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Charger TCPDF
require_once DEVIS_DEMENAGEMENT_PLUGIN_DIR . 'libs/tcpdf/tcpdf.php';

class Devis_PDF {
    
    /**
     * Générer le PDF du devis
     */
    public function generate($devis_data, $objets_details) {
        $settings = get_option('devis_demenagement_settings');
        
        // Créer un nouveau PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Informations du document
        $pdf->SetCreator('Devis Déménagement Plugin');
        $pdf->SetAuthor($settings['company_name']);
        $pdf->SetTitle('Devis de déménagement');
        $pdf->SetSubject('Estimation de déménagement');
        
        // Supprimer header et footer par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Ajouter une page
        $pdf->AddPage();
        
        // Police
        $pdf->SetFont('helvetica', '', 10);
        
        // Générer le contenu HTML
        $html = $this->generate_html($devis_data, $objets_details, $settings);
        
        // Écrire le HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Retourner le PDF en string (pour sauvegarde ou envoi email)
        return $pdf->Output('devis-demenagement.pdf', 'S');
    }
    
    /**
     * Générer le contenu HTML du PDF
     */
    private function generate_html($devis_data, $objets_details, $settings) {
        $date = date('d/m/Y');
        
        $html = '
        <style>
            h1 { color: #2c3e50; font-size: 24px; text-align: center; margin-bottom: 20px; }
            h2 { color: #34495e; font-size: 16px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
            .info-block { margin-bottom: 20px; }
            .info-label { font-weight: bold; color: #555; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #3498db; color: white; padding: 8px; text-align: left; font-weight: bold; }
            td { padding: 8px; border-bottom: 1px solid #ddd; }
            .total-row { background-color: #ecf0f1; font-weight: bold; font-size: 14px; }
            .highlight { background-color: #2ecc71; color: white; font-size: 18px; padding: 10px; text-align: center; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #7f8c8d; }
            .company-info { text-align: center; margin-bottom: 20px; color: #7f8c8d; }
        </style>
        
        <div class="company-info">
            <h1>' . esc_html($settings['company_name']) . '</h1>
            ' . (!empty($settings['company_address']) ? '<p>' . esc_html($settings['company_address']) . '</p>' : '') . '
            ' . (!empty($settings['company_phone']) ? '<p>Tél : ' . esc_html($settings['company_phone']) . '</p>' : '') . '
            ' . (!empty($settings['company_email']) ? '<p>Email : ' . esc_html($settings['company_email']) . '</p>' : '') . '
        </div>
        
        <h1>DEVIS DE DÉMÉNAGEMENT</h1>
        
        <p style="text-align: right; color: #7f8c8d;">Date : ' . $date . '</p>
        
        <div class="info-block">
            <h2>Informations client</h2>
            <p><span class="info-label">Nom :</span> ' . esc_html($devis_data['client_nom']) . '</p>
            <p><span class="info-label">Email :</span> ' . esc_html($devis_data['client_email']) . '</p>
            <p><span class="info-label">Téléphone :</span> ' . esc_html($devis_data['client_telephone']) . '</p>
        </div>
        
        <div class="info-block">
            <h2>Informations du déménagement</h2>
            <p><span class="info-label">Adresse de départ :</span> ' . esc_html($devis_data['adresse_depart']) . '</p>
            <p><span class="info-label">Adresse d\'arrivée :</span> ' . esc_html($devis_data['adresse_arrivee']) . '</p>
            <p><span class="info-label">Distance :</span> ' . number_format($devis_data['distance_km'], 2, ',', ' ') . ' km</p>
            <p><span class="info-label">Volume total :</span> ' . number_format($devis_data['volume_total_m3'], 2, ',', ' ') . ' m³</p>
        </div>
        ';
        
        // Liste des objets
        if (!empty($objets_details)) {
            $html .= '<h2>Détail des objets à déménager</h2>';
            $html .= '<table>';
            $html .= '<thead><tr><th>Objet</th><th>Quantité</th><th>Volume unitaire</th><th>Volume total</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($objets_details as $objet) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($objet['nom']) . '</td>';
                $html .= '<td>' . $objet['quantite'] . '</td>';
                $html .= '<td>' . number_format($objet['volume_unitaire'], 2, ',', ' ') . ' m³</td>';
                $html .= '<td>' . number_format($objet['volume_total'], 2, ',', ' ') . ' m³</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Détail des prix
        $html .= '<h2>Détail du devis</h2>';
        $html .= '<table>';
        $html .= '<tr><td>Transport (Volume : ' . number_format($devis_data['volume_total_m3'], 2, ',', ' ') . ' m³ × ' . number_format($devis_data['details']['price_per_m3'], 2, ',', ' ') . ' €/m³)</td><td style="text-align: right;">' . number_format($devis_data['prix_volume'], 2, ',', ' ') . ' €</td></tr>';
        
        if ($devis_data['distance_km'] > 0) {
            $html .= '<tr><td>Distance (' . number_format($devis_data['distance_km'], 2, ',', ' ') . ' km × ' . number_format($devis_data['details']['price_per_km'], 2, ',', ' ') . ' €/km)</td><td style="text-align: right;">' . number_format($devis_data['prix_distance'], 2, ',', ' ') . ' €</td></tr>';
        }
        
        if ($devis_data['prix_supplements'] > 0) {
            $html .= '<tr><td>Suppléments (étages, etc.)</td><td style="text-align: right;">' . number_format($devis_data['prix_supplements'], 2, ',', ' ') . ' €</td></tr>';
        }
        
        $html .= '<tr class="total-row"><td>TOTAL TTC</td><td style="text-align: right;">' . number_format($devis_data['prix_total'], 2, ',', ' ') . ' €</td></tr>';
        $html .= '</table>';
        
        $html .= '<div class="highlight">Prix total : ' . number_format($devis_data['prix_total'], 2, ',', ' ') . ' € TTC</div>';
        
        // Notes supplémentaires
        if (!empty($devis_data['notes'])) {
            $html .= '<h2>Notes</h2>';
            $html .= '<p>' . nl2br(esc_html($devis_data['notes'])) . '</p>';
        }
        
        // Footer
        $html .= '<div class="footer">';
        $html .= '<p>Ce devis est valable 30 jours à compter de sa date d\'émission.</p>';
        $html .= '<p>Devis généré automatiquement par ' . esc_html($settings['company_name']) . '</p>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Sauvegarder le PDF dans un fichier
     */
    public function save_to_file($pdf_content, $filename = null) {
        if (!$filename) {
            $filename = 'devis-' . time() . '.pdf';
        }
        
        // Créer le dossier uploads/devis si n'existe pas
        $upload_dir = wp_upload_dir();
        $devis_dir = $upload_dir['basedir'] . '/devis/';
        
        if (!file_exists($devis_dir)) {
            wp_mkdir_p($devis_dir);
        }
        
        $filepath = $devis_dir . $filename;
        file_put_contents($filepath, $pdf_content);
        
        return array(
            'path' => $filepath,
            'url' => $upload_dir['baseurl'] . '/devis/' . $filename
        );
    }
    
    /**
     * Envoyer le PDF en téléchargement direct au navigateur
     */
    public function download($devis_data, $objets_details) {
        $pdf_content = $this->generate($devis_data, $objets_details);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="devis-demenagement.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        
        echo $pdf_content;
        exit;
    }
}