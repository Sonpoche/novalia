<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once(NOVALIA_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php');

class Novalia_Fiche_Technique {
    
    private $pdf;
    private $devis;
    
    public function __construct($devis_id) {
        error_log('NOVALIA FICHE: Constructeur ID=' . $devis_id);
        
        $this->devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$this->devis) {
            error_log('NOVALIA FICHE: ERREUR - Devis introuvable');
            throw new Exception('Devis introuvable (ID: ' . $devis_id . ')');
        }
        
        error_log('NOVALIA FICHE: Devis charge - ' . $this->devis->numero_devis);
        $this->init_pdf();
        error_log('NOVALIA FICHE: PDF initialise');
    }
    
    private function init_pdf() {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        $this->pdf->SetCreator('Novalia Group');
        $this->pdf->SetAuthor('Novalia Group');
        $this->pdf->SetTitle('Fiche Technique - ' . $this->devis->numero_devis);
        $this->pdf->SetSubject('Fiche technique demenagement');
        
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);
        
        $this->pdf->SetFont('helvetica', '', 10);
    }
    
    public function generate_and_save() {
        error_log('NOVALIA FICHE: Generation fiche technique ID=' . $this->devis->id);
        
        $template_path = NOVALIA_PLUGIN_DIR . 'templates/fiche-technique-template.php';
        
        if (!file_exists($template_path)) {
            error_log('NOVALIA FICHE: Template introuvable: ' . $template_path);
            throw new Exception('Template fiche technique introuvable');
        }
        
        $this->pdf->AddPage();
        
        $devis = $this->devis;
        $type = $devis->type_demenagement;
        $pdf = $this->pdf;
        
        ob_start();
        include $template_path;
        ob_end_clean();
        
        $upload_dir = wp_upload_dir();
        $fiches_dir = $upload_dir['basedir'] . '/novalia-fiches-techniques/';
        
        if (!file_exists($fiches_dir)) {
            wp_mkdir_p($fiches_dir);
        }
        
        $filename = 'fiche_' . $devis->numero_devis . '_' . time() . '.pdf';
        $file_path = $fiches_dir . $filename;
        
        try {
            $this->pdf->Output($file_path, 'F');
            error_log('NOVALIA FICHE: PDF genere avec succes: ' . $file_path);
            
            $relative_path = '/novalia-fiches-techniques/' . $filename;
            
            Novalia_Database::update_devis_fiche_technique($this->devis->id, $relative_path);
            error_log('NOVALIA FICHE: Chemin enregistre en BDD: ' . $relative_path);
            
            return $relative_path;
            
        } catch (Exception $e) {
            error_log('NOVALIA FICHE: ERREUR generation - ' . $e->getMessage());
            throw $e;
        }
    }
}