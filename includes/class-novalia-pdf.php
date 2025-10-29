<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once(NOVALIA_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php');

class Novalia_PDF {
    
    private $pdf;
    private $devis;
    
    public function __construct($devis_id) {
        error_log('NOVALIA PDF: Constructeur ID=' . $devis_id);
        
        $this->devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$this->devis) {
            error_log('NOVALIA PDF: ERREUR - Devis introuvable');
            throw new Exception('Devis introuvable (ID: ' . $devis_id . ')');
        }
        
        error_log('NOVALIA PDF: Devis charge - ' . $this->devis->numero_devis);
        $this->init_pdf();
        error_log('NOVALIA PDF: PDF initialise');
    }
    
    private function init_pdf() {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        $this->pdf->SetCreator('Novalia Group');
        $this->pdf->SetAuthor('Novalia Group');
        $this->pdf->SetTitle('Devis Demenagement - ' . $this->devis->numero_devis);
        $this->pdf->SetSubject('Devis de demenagement');
        
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
        
        $this->pdf->SetFont('helvetica', '', 10);
    }
    
    public function generate_pdf_standard() {
        error_log('NOVALIA PDF: Generation standard avec template');
        $this->init_pdf();
        return $this->generate_with_template('standard');
    }
    
    public function generate_pdf_complet() {
        error_log('NOVALIA PDF: Generation complet avec template');
        $this->init_pdf();
        return $this->generate_with_template('complet');
    }
    
    private function generate_with_template($type) {
        $template_path = NOVALIA_PLUGIN_DIR . 'templates/pdf-template.php';
        
        if (!file_exists($template_path)) {
            error_log('NOVALIA PDF: Template introuvable: ' . $template_path);
            return $this->generate_without_template($type);
        }
        
        $this->pdf->AddPage();
        
        $devis = $this->devis;
        $pdf = $this->pdf;
        
        ob_start();
        include $template_path;
        ob_end_clean();
        
        return $this->pdf->Output('', 'S');
    }
    
    private function generate_without_template($type) {
        $this->pdf->AddPage();
        
        $this->add_header();
        
        $this->pdf->Ln(10);
        
        $this->pdf->SetFont('helvetica', 'B', 20);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(0, 10, 'DEVIS DE DEMENAGEMENT', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 12);
        $this->pdf->SetTextColor(43, 187, 173);
        $this->pdf->Cell(0, 8, 'Numero: ' . $this->devis->numero_devis, 0, 1, 'C');
        
        $this->pdf->Ln(5);
        
        $this->add_client_info();
        
        $this->pdf->Ln(5);
        
        $this->add_trajet_info();
        
        $this->pdf->Ln(5);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(50, 7, 'Date du demenagement:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 7, Novalia_Devis::format_date($this->devis->date_demenagement), 0, 1, 'L');
        
        $this->pdf->Ln(5);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(50, 7, 'Volume total:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 7, number_format($this->devis->volume_total, 2, '.', ' ') . ' m3', 0, 1, 'L');
        
        $this->pdf->Ln(10);
        
        $this->add_prix_box($type);
        
        $this->pdf->Ln(10);
        
        $this->add_estimation_notice();
        
        $this->add_page_two();
        
        return $this->pdf->Output('', 'S');
    }
    
    private function add_page_two() {
        $this->pdf->AddPage();
        
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(0, 10, 'RECAPITULATIF DES OBJETS', 0, 1, 'C');
        
        $this->pdf->Ln(5);
        
        if (isset($this->devis->items_by_category)) {
            foreach ($this->devis->items_by_category as $categorie => $items) {
                $this->add_category_items($categorie, $items);
            }
        }
    }
    
    private function add_header() {
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(0, 8, 'NOVALIA GROUP', 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'info@novaliagroup.ch', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'www.novaliagroup.ch', 0, 1, 'L');
        
        $this->pdf->Ln(3);
        $this->pdf->SetDrawColor(43, 187, 173);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
    }
    
    private function add_client_info() {
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->Rect(15, $this->pdf->GetY(), 180, 35, 'F');
        
        $this->pdf->Ln(3);
        
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(0, 7, 'INFORMATIONS CLIENT', 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(60, 60, 60);
        
        $this->pdf->Cell(30, 6, 'Nom:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, $this->devis->nom_client, 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(30, 6, 'Email:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, $this->devis->email_client, 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(30, 6, 'Telephone:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, $this->devis->telephone_client, 0, 1, 'L');
        
        $this->pdf->Ln(2);
    }
    
    private function add_trajet_info() {
        $this->pdf->SetFillColor(245, 245, 245);
        $this->pdf->Rect(15, $this->pdf->GetY(), 180, 30, 'F');
        
        $this->pdf->Ln(3);
        
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(26, 35, 50);
        $this->pdf->Cell(0, 7, 'TRAJET', 0, 1, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(60, 60, 60);
        
        $this->pdf->Cell(30, 6, 'Depart:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->MultiCell(0, 6, $this->devis->adresse_depart, 0, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(30, 6, 'Arrivee:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->MultiCell(0, 6, $this->devis->adresse_arrivee, 0, 'L');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(30, 6, 'Distance:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, number_format($this->devis->distance, 2, '.', ' ') . ' km', 0, 1, 'L');
        
        $this->pdf->Ln(2);
    }
    
    private function add_prix_box($type) {
        $prix = ($type === 'complet') ? $this->devis->prix_complet : $this->devis->prix_standard;
        $type_label = ($type === 'complet') ? 'DEMENAGEMENT COMPLET' : 'DEMENAGEMENT STANDARD';
        
        $this->pdf->SetFillColor(43, 187, 173);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Rect(15, $this->pdf->GetY(), 180, 25, 'F');
        
        $this->pdf->Ln(3);
        
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 8, $type_label, 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', 'B', 18);
        $this->pdf->Cell(0, 10, Novalia_Tarifs::format_prix($prix), 0, 1, 'C');
        
        if ($type === 'complet' && $this->devis->nombre_cartons > 0) {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->Cell(0, 5, 'Incluant l\'emballage de ' . $this->devis->nombre_cartons . ' carton(s)', 0, 1, 'C');
        }
    }
    
    private function add_estimation_notice() {
        $this->pdf->SetFillColor(255, 122, 0);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Rect(15, $this->pdf->GetY(), 180, 30, 'F');
        
        $this->pdf->Ln(3);
        
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 6, 'IMPORTANT - ESTIMATION', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->MultiCell(0, 5, 'Ce devis est une estimation basee sur les informations fournies. Une visite sur place sera necessaire pour etablir un devis definitif et precis. Les prix peuvent varier en fonction des conditions reelles constatees lors de la visite.', 0, 'C');
    }
    
    private function add_category_items($categorie, $items) {
        $this->pdf->SetFillColor(26, 35, 50);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 8, strtoupper($categorie), 0, 1, 'L', true);
        
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetTextColor(60, 60, 60);
        
        $volume_categorie = 0;
        
        foreach ($items as $item) {
            $volume_item = floatval($item->volume) * intval($item->quantite);
            $volume_categorie += $volume_item;
            
            $this->pdf->SetFillColor(250, 250, 250);
            
            $this->pdf->Cell(10, 6, $item->quantite . 'x', 0, 0, 'C', true);
            $this->pdf->Cell(130, 6, $item->nom_item, 0, 0, 'L', true);
            $this->pdf->Cell(25, 6, number_format($item->volume, 3) . ' m3', 0, 0, 'R', true);
            $this->pdf->Cell(25, 6, number_format($volume_item, 3) . ' m3', 0, 1, 'R', true);
        }
        
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(43, 187, 173);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell(165, 6, 'Sous-total ' . $categorie, 0, 0, 'R', true);
        $this->pdf->Cell(25, 6, number_format($volume_categorie, 3) . ' m3', 0, 1, 'R', true);
        
        $this->pdf->Ln(5);
    }
    
    public function save_pdf_standard($filename) {
        $this->generate_pdf_standard();
        return $this->pdf->Output($filename, 'F');
    }
    
    public function save_pdf_complet($filename) {
        $this->generate_pdf_complet();
        return $this->pdf->Output($filename, 'F');
    }
}