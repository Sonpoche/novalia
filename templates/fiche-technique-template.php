<?php
/**
 * Template FICHE TECHNIQUE Employé - Novalia Group
 * Variables: $devis, $type, $pdf
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// BARRE SUPÉRIEURE ORANGE (pour différencier)
// ============================================
$pdf->SetFillColor(255, 143, 0); // Orange
$pdf->Rect(0, 0, 210, 8, 'F');

$pdf->Ln(15);

// ============================================
// EN-TÊTE - LOGO + FICHE TECHNIQUE
// ============================================

// Logo à gauche
$logo_path = NOVALIA_PLUGIN_DIR . 'assets/images/logo-novalia.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 15, 18, 40, 0, 'PNG');
} else {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(26, 35, 50);
    $pdf->Cell(40, 10, 'NOVALIA GROUP', 0, 0, 'L');
}

// FICHE TECHNIQUE à droite
$pdf->SetXY(130, 18);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->SetTextColor(255, 143, 0); // Orange
$pdf->Cell(0, 12, 'FICHE TECHNIQUE', 0, 1, 'R');

$pdf->Ln(12);

// ============================================
// INFOS ENTREPRISE + DATE/DEVIS N°
// ============================================
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'Novalia Group', 0, 1, 'L');
$pdf->Cell(0, 5, 'Document interne - Equipe demenagement', 0, 1, 'L');

// DATE ET DEVIS N° À DROITE
$pdf->SetXY(140, 50);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'DATE', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, date('d.m.Y'), 0, 1, 'L');

$pdf->SetXY(140, 55);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 5, 'DEVIS N°', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $devis->numero_devis, 0, 1, 'L');

$pdf->Ln(10);

// ============================================
// LIGNE DE SÉPARATION
// ============================================
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.1);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

$pdf->Ln(5);

// ============================================
// INFORMATIONS CLIENT
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(255, 143, 0);
$pdf->Cell(0, 7, 'INFORMATIONS CLIENT', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 5, 'Nom :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(65, 5, $devis->nom_client, 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(35, 5, 'Telephone :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $devis->telephone_client, 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(40, 5, 'Email :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, $devis->email_client, 0, 1, 'L');

$pdf->Ln(5);

// ============================================
// TYPE DE DÉMÉNAGEMENT
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(255, 143, 0);
$pdf->Cell(0, 7, 'TYPE DE DEMENAGEMENT', 0, 1, 'L');

$pdf->Ln(2);

$type_label = ($type === 'complet') ? 'COMPLET (avec emballage)' : 'STANDARD (sans emballage)';
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 7, $type_label, 0, 1, 'L');

if ($type === 'complet' && $devis->nombre_cartons > 0) {
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, 'Nombre de cartons a emballer : ' . $devis->nombre_cartons, 0, 1, 'L');
}

$pdf->Ln(5);

// ============================================
// DÉTAILS DU DÉMÉNAGEMENT
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(255, 143, 0);
$pdf->Cell(0, 7, 'DETAILS DU DEMENAGEMENT', 0, 1, 'L');

$pdf->Ln(2);

// Date
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(50, 5, 'Date du demenagement :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, date('d.m.Y', strtotime($devis->date_demenagement)), 0, 1, 'L');

$pdf->Ln(3);

// ============================================
// EN-TÊTES DÉPART / ARRIVÉE
// ============================================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(90, 6, 'DEPART', 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 6, 'ARRIVEE', 0, 1, 'L');

$pdf->Ln(2);

// ============================================
// ADRESSES
// ============================================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);

// Adresse départ (MultiCell pour affichage complet)
$x_start = $pdf->GetX();
$y_start = $pdf->GetY();
$pdf->MultiCell(85, 4, $devis->adresse_depart, 0, 'L');
$y_after_depart = $pdf->GetY();

// Adresse arrivée
$pdf->SetXY(110, $y_start);
$pdf->MultiCell(85, 4, $devis->adresse_arrivee, 0, 'L');
$y_after_arrivee = $pdf->GetY();

// Prendre la plus grande hauteur
$y_max = max($y_after_depart, $y_after_arrivee);
$pdf->SetY($y_max);

$pdf->Ln(2);

// ============================================
// TYPE DE LOGEMENT
// ============================================
$pdf->SetFont('helvetica', '', 9);

$logement_depart = isset($devis->type_logement_depart) && !empty($devis->type_logement_depart) 
    ? ucfirst($devis->type_logement_depart) 
    : 'Non specifie';
$logement_arrivee = isset($devis->type_logement_arrivee) && !empty($devis->type_logement_arrivee)
    ? ucfirst($devis->type_logement_arrivee)
    : 'Non specifie';

$pdf->Cell(90, 5, 'Type : ' . $logement_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Type : ' . $logement_arrivee, 0, 1, 'L');

// ============================================
// ÉTAGES
// ============================================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(90, 5, 'Etage : ' . $devis->etages_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Etage : ' . $devis->etages_arrivee, 0, 1, 'L');

// ============================================
// ASCENSEURS (en gras pour attirer l'attention)
// ============================================
$ascenseur_depart = $devis->ascenseur_depart ? 'OUI' : 'NON';
$ascenseur_arrivee = $devis->ascenseur_arrivee ? 'OUI' : 'NON';

$pdf->Cell(90, 5, 'Ascenseur : ' . $ascenseur_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Ascenseur : ' . $ascenseur_arrivee, 0, 1, 'L');

$pdf->Ln(3);

// ============================================
// LIGNE DE SÉPARATION
// ============================================
$pdf->SetDrawColor(220, 220, 220);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

$pdf->Ln(3);

// ============================================
// DISTANCE ET VOLUME - ENCADRÉ
// ============================================
$pdf->SetFillColor(255, 250, 240); // Beige clair

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(90, 7, 'Distance totale : ' . number_format($devis->distance, 2) . ' km', 1, 0, 'C', true);
$pdf->Cell(90, 7, 'Volume total : ' . number_format($devis->volume_total, 2) . ' m3', 1, 1, 'C', true);

$pdf->Ln(8);

// ============================================
// LISTE DES OBJETS (Version condensée)
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(255, 143, 0);
$pdf->Cell(0, 7, 'INVENTAIRE DES OBJETS', 0, 1, 'L');

$pdf->Ln(2);

// Récupérer les items
global $wpdb;
$table_items = $wpdb->prefix . 'novalia_devis_items';
$items = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_items WHERE devis_id = %d ORDER BY categorie, nom_item",
    $devis->id
));

// Grouper par catégorie
$items_by_category = array();
foreach ($items as $item) {
    if (!isset($items_by_category[$item->categorie])) {
        $items_by_category[$item->categorie] = array();
    }
    $items_by_category[$item->categorie][] = $item;
}

// Afficher par catégorie (version compacte)
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(60, 60, 60);

foreach ($items_by_category as $categorie => $category_items) {
    // En-tête catégorie - ORANGE
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(255, 143, 0);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 6, strtoupper($categorie), 0, 1, 'L', true);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetFillColor(250, 250, 250);
    
    foreach ($category_items as $item) {
        $pdf->Cell(15, 5, $item->quantite . 'x', 1, 0, 'C');
        $pdf->Cell(135, 5, $item->nom_item, 1, 0, 'L');
        $pdf->Cell(30, 5, number_format($item->volume * $item->quantite, 2) . ' m3', 1, 1, 'R');
    }
    
    $pdf->Ln(2);
}

$pdf->Ln(5);

// ============================================
// REMARQUES IMPORTANTES
// ============================================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 6, 'REMARQUES IMPORTANTES', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(60, 60, 60);
$pdf->MultiCell(0, 4, '- Verifier les acces (largeur portes, escaliers, stationnement)' . "\n" .
                      '- Proteger les sols et murs' . "\n" .
                      '- Verifier l\'etat des objets fragiles avant chargement' . "\n" .
                      '- Prendre photos si necessaire', 0, 'L');

// ============================================
// BARRE INFÉRIEURE ORANGE
// ============================================
$pdf->SetY(-15);
$pdf->SetFillColor(255, 143, 0);
$pdf->Rect(0, $pdf->GetY(), 210, 8, 'F');