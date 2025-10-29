<?php
/**
 * Template PDF Professionnel - Novalia Group
 * Variables: $devis, $type, $pdf
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// BARRE SUPÉRIEURE - COULEUR SELON TYPE
// ============================================
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173); // Turquoise pour complet
} else {
    $pdf->SetFillColor(26, 35, 50); // Bleu nuit pour standard
}
$pdf->Rect(0, 0, 210, 8, 'F');

$pdf->Ln(15);

// ============================================
// EN-TÊTE - LOGO + ESTIMATION
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

// ESTIMATION à droite
$pdf->SetXY(140, 18);
$pdf->SetFont('helvetica', 'B', 28);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 12, 'ESTIMATION', 0, 1, 'R');

$pdf->Ln(12);

// ============================================
// INFOS ENTREPRISE + DATE/DEVIS N°
// ============================================
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'Novalia Group', 0, 1, 'L');
$pdf->Cell(0, 5, 'Specialiste du demenagement en Suisse', 0, 1, 'L');
$pdf->Cell(0, 5, 'info@novaliagroup.ch', 0, 1, 'L');
$pdf->Cell(0, 5, 'www.novaliagroup.ch', 0, 1, 'L');

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
// FACTURER / ENVOYER À
// ============================================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(90, 6, 'FACTURER', 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 6, 'ENVOYER A', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(90, 5, $devis->nom_client, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, $devis->nom_client, 0, 1, 'L');

$pdf->Cell(90, 5, $devis->email_client, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, $devis->email_client, 0, 1, 'L');

$pdf->Cell(90, 5, $devis->telephone_client, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, $devis->telephone_client, 0, 1, 'L');

$pdf->Ln(8);

// ============================================
// INFORMATIONS DU DÉMÉNAGEMENT (hors tableau)
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(26, 35, 50);
$pdf->Cell(0, 7, 'INFORMATIONS DU DEMENAGEMENT', 0, 1, 'L');

$pdf->Ln(2);

// Date du déménagement (sur toute la largeur)
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
// ADRESSES (en gras et tronqué si trop long)
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(60, 60, 60);

// Adresse départ
$adresse_depart_short = strlen($devis->adresse_depart) > 50 
    ? substr($devis->adresse_depart, 0, 47) . '...' 
    : $devis->adresse_depart;
$pdf->Cell(90, 5, $adresse_depart_short, 0, 0, 'L');

$pdf->SetX(110);

// Adresse arrivée
$adresse_arrivee_short = strlen($devis->adresse_arrivee) > 50 
    ? substr($devis->adresse_arrivee, 0, 47) . '...' 
    : $devis->adresse_arrivee;
$pdf->Cell(85, 5, $adresse_arrivee_short, 0, 1, 'L');

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
$pdf->Cell(90, 5, 'Etage : ' . $devis->etages_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Etage : ' . $devis->etages_arrivee, 0, 1, 'L');

// ============================================
// ASCENSEURS
// ============================================
$ascenseur_depart = $devis->ascenseur_depart ? 'Oui' : 'Non';
$ascenseur_arrivee = $devis->ascenseur_arrivee ? 'Oui' : 'Non';

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
// DISTANCE ET VOLUME
// ============================================
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(50, 5, 'Distance totale :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(40, 5, number_format($devis->distance, 2) . ' km', 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(45, 5, 'Volume total :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, number_format($devis->volume_total, 2) . ' m3', 0, 1, 'L');

$pdf->Ln(8);

// ============================================
// TABLEAU DES PRIX - EN-TÊTE BLEU NUIT OU TURQUOISE
// ============================================
// Turquoise pour COMPLET, Bleu nuit pour STANDARD
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173); // Turquoise
} else {
    $pdf->SetFillColor(26, 35, 50); // Bleu nuit
}

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);

$pdf->Cell(140, 7, 'DESCRIPTION', 1, 0, 'L', true);
$pdf->Cell(40, 7, 'MONTANT', 1, 1, 'R', true);

// ============================================
// LIGNES DU TABLEAU - SEULEMENT LES PRIX
// ============================================
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetFillColor(255, 255, 255);

$type_label = ($type === 'complet') ? 'Demenagement Complet' : 'Demenagement Standard';
$prix = ($type === 'complet') ? $devis->prix_complet : $devis->prix_standard;

// Calculer les composantes du prix selon vos tarifs
$prix_base = 100; // Frais de déplacement
$prix_distance = $devis->distance * 2.50; // 2.50 CHF/km
$prix_volume = $devis->volume_total * 80; // 80 CHF/m³
$prix_etages = 0;

// Prix étages sans ascenseur
if (!$devis->ascenseur_depart && $devis->etages_depart > 0) {
    $prix_etages += $devis->etages_depart * 25;
}
if (!$devis->ascenseur_arrivee && $devis->etages_arrivee > 0) {
    $prix_etages += $devis->etages_arrivee * 25;
}

// Type de déménagement
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(140, 6, $type_label, 1, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 6, number_format($prix, 2) . ' CHF', 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 9);

// Détail prix de base
$pdf->Cell(140, 5, '- Frais de deplacement', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_base, 2) . ' CHF', 1, 1, 'R', true);

// Détail distance
$pdf->Cell(140, 5, '- Distance (' . number_format($devis->distance, 2) . ' km x 2.50 CHF/km)', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_distance, 2) . ' CHF', 1, 1, 'R', true);

// Détail volume
$pdf->Cell(140, 5, '- Volume (' . number_format($devis->volume_total, 2) . ' m3 x 80 CHF/m3)', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_volume, 2) . ' CHF', 1, 1, 'R', true);

// Détail étages
if ($prix_etages > 0) {
    $detail_etages = '- Etages sans ascenseur (';
    if (!$devis->ascenseur_depart && $devis->etages_depart > 0) {
        $detail_etages .= $devis->etages_depart . ' etages depart';
    }
    if (!$devis->ascenseur_arrivee && $devis->etages_arrivee > 0) {
        if (!$devis->ascenseur_depart && $devis->etages_depart > 0) {
            $detail_etages .= ' + ';
        }
        $detail_etages .= $devis->etages_arrivee . ' etages arrivee';
    }
    $detail_etages .= ' x 25 CHF/etage)';
    
    $pdf->Cell(140, 5, $detail_etages, 1, 0, 'L', true);
    $pdf->Cell(40, 5, number_format($prix_etages, 2) . ' CHF', 1, 1, 'R', true);
}

// Emballage si complet
if ($type === 'complet' && $devis->nombre_cartons > 0) {
    $prix_emballage = $devis->nombre_cartons * 15; // 15 CHF/carton
    $pdf->Cell(140, 5, '- Emballage (' . $devis->nombre_cartons . ' cartons x 15 CHF/carton)', 1, 0, 'L', true);
    $pdf->Cell(40, 5, number_format($prix_emballage, 2) . ' CHF', 1, 1, 'R', true);
}

// ============================================
// TOTAL
// ============================================
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(220, 255, 220);
$pdf->Cell(140, 8, 'Total du devis', 1, 0, 'R', true);
$pdf->Cell(40, 8, number_format($prix, 2) . ' CHF', 1, 1, 'R', true);

$pdf->Ln(5);

// ============================================
// REMARQUES
// ============================================
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 4, 'Ce devis est une estimation basee sur les informations fournies. Une visite sur place sera necessaire pour etablir un devis definitif et precis. Les prix peuvent varier en fonction des conditions reelles constatees lors de la visite. Duree de validite: 30 jours.', 0, 'L');

// ============================================
// BARRE INFÉRIEURE - COULEUR SELON TYPE
// ============================================
$pdf->SetY(-15);
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173); // Turquoise
} else {
    $pdf->SetFillColor(26, 35, 50); // Bleu nuit
}
$pdf->Rect(0, $pdf->GetY(), 210, 8, 'F');

// ============================================
// PAGE 2 - RÉCAPITULATIF DES OBJETS
// ============================================
$pdf->AddPage();

// Barre supérieure conditionnelle
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173);
} else {
    $pdf->SetFillColor(26, 35, 50);
}
$pdf->Rect(0, 0, 210, 8, 'F');

$pdf->Ln(12);

$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'RECAPITULATIF DES OBJETS', 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 5, 'Liste detaillee des objets a demenager', 0, 1, 'C');

$pdf->Ln(8);

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

// Afficher par catégorie
foreach ($items_by_category as $categorie => $category_items) {
    $pdf->SetFont('helvetica', 'B', 10);
    if ($type === 'complet') {
        $pdf->SetFillColor(43, 187, 173);
    } else {
        $pdf->SetFillColor(26, 35, 50);
    }
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 7, strtoupper($categorie), 0, 1, 'L', true);
    
    $pdf->Ln(1);
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetFillColor(245, 245, 245);
    
    $volume_categorie = 0;
    $row_count = 0;
    
    foreach ($category_items as $item) {
        $volume_item = floatval($item->volume) * intval($item->quantite);
        $volume_categorie += $volume_item;
        
        $fill = ($row_count % 2 == 0) ? true : false;
        
        $pdf->Cell(20, 6, $item->quantite . 'x', 1, 0, 'C', $fill);
        $pdf->Cell(110, 6, $item->nom_item, 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, number_format($item->volume, 3) . ' m3', 1, 0, 'R', $fill);
        $pdf->Cell(25, 6, number_format($volume_item, 3) . ' m3', 1, 1, 'R', $fill);
        
        $row_count++;
    }
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(155, 6, 'Sous-total ' . $categorie, 1, 0, 'R', true);
    $pdf->Cell(25, 6, number_format($volume_categorie, 3) . ' m3', 1, 1, 'R', true);
    
    $pdf->Ln(5);
}

// VOLUME TOTAL FINAL
$pdf->SetFont('helvetica', 'B', 11);
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173);
} else {
    $pdf->SetFillColor(26, 35, 50);
}
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(155, 8, 'VOLUME TOTAL', 1, 0, 'R', true);
$pdf->Cell(25, 8, number_format($devis->volume_total, 3) . ' m3', 1, 1, 'R', true);

// Barre inférieure
$pdf->SetY(-15);
if ($type === 'complet') {
    $pdf->SetFillColor(43, 187, 173);
} else {
    $pdf->SetFillColor(26, 35, 50);
}
$pdf->Rect(0, $pdf->GetY(), 210, 8, 'F');