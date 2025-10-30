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
// Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);
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
$pdf->Cell(0, 5, 'Spécialiste du déménagement en Suisse', 0, 1, 'L');
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
$pdf->Cell(85, 6, 'ENVOYER À', 0, 1, 'L');

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
$pdf->Cell(0, 7, 'INFORMATIONS DU DÉMÉNAGEMENT', 0, 1, 'L');

$pdf->Ln(2);

// Date du déménagement (sur toute la largeur)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(50, 5, 'Date du déménagement :', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, date('d.m.Y', strtotime($devis->date_demenagement)), 0, 1, 'L');

$pdf->Ln(3);

// ============================================
// EN-TÊTES DÉPART / ARRIVÉE
// ============================================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(90, 6, 'DÉPART', 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 6, 'ARRIVÉE', 0, 1, 'L');

$pdf->Ln(2);

// ============================================
// ADRESSES (en gras, affichage complet)
// ============================================
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(60, 60, 60);

// Sauvegarder la position Y de départ
$start_y = $pdf->GetY();

// Adresse départ (colonne de gauche)
$pdf->SetXY(15, $start_y);
$pdf->MultiCell(90, 4, $devis->adresse_depart, 0, 'L');
$end_y_depart = $pdf->GetY();

// Adresse arrivée (colonne de droite)
$pdf->SetXY(110, $start_y);
$pdf->MultiCell(85, 4, $devis->adresse_arrivee, 0, 'L');
$end_y_arrivee = $pdf->GetY();

// Positionner le curseur après la plus longue des deux adresses
$max_y = max($end_y_depart, $end_y_arrivee);
$pdf->SetY($max_y);

$pdf->Ln(2);

// ============================================
// TYPE DE LOGEMENT
// ============================================
$pdf->SetFont('helvetica', '', 9);

$logement_depart = isset($devis->type_logement_depart) && !empty($devis->type_logement_depart) 
    ? ucfirst($devis->type_logement_depart) 
    : 'Non spécifié';
$logement_arrivee = isset($devis->type_logement_arrivee) && !empty($devis->type_logement_arrivee)
    ? ucfirst($devis->type_logement_arrivee)
    : 'Non spécifié';

$pdf->Cell(90, 5, 'Type : ' . $logement_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Type : ' . $logement_arrivee, 0, 1, 'L');

// ============================================
// ÉTAGES
// ============================================
$pdf->Cell(90, 5, 'Étage : ' . $devis->etages_depart, 0, 0, 'L');
$pdf->SetX(110);
$pdf->Cell(85, 5, 'Étage : ' . $devis->etages_arrivee, 0, 1, 'L');

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
// TABLEAU DES PRIX - EN-TÊTE BLEU NUIT
// ============================================
// Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);

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

$type_label = ($type === 'complet') ? 'Déménagement Complet' : 'Déménagement Standard';
$prix = ($type === 'complet') ? $devis->prix_complet : $devis->prix_standard;

// Récupérer les tarifs depuis la base de données
global $wpdb;
$table_tarifs = $wpdb->prefix . 'novalia_tarifs';
$tarifs = $wpdb->get_results("SELECT type_tarif, valeur FROM $table_tarifs", OBJECT_K);

// Extraire les valeurs des tarifs
$prix_base = isset($tarifs['prix_base']) ? floatval($tarifs['prix_base']->valeur) : 200.00;
$prix_km = isset($tarifs['prix_km']) ? floatval($tarifs['prix_km']->valeur) : 2.50;
$prix_m3 = isset($tarifs['prix_m3']) ? floatval($tarifs['prix_m3']->valeur) : 80.00;
$prix_etage_tarif = isset($tarifs['prix_etage_sans_ascenseur']) ? floatval($tarifs['prix_etage_sans_ascenseur']->valeur) : 50.00;
$prix_carton_tarif = isset($tarifs['prix_carton_emballage']) ? floatval($tarifs['prix_carton_emballage']->valeur) : 15.00;

// Calculer les composantes du prix
$prix_distance = $devis->distance * $prix_km;
$prix_volume = $devis->volume_total * $prix_m3;
$prix_etages = 0;

// Prix étages sans ascenseur
$etages_depart_count = 0;
$etages_arrivee_count = 0;

if (!$devis->ascenseur_depart && $devis->etages_depart > 0) {
    $etages_depart_count = $devis->etages_depart;
    $prix_etages += $devis->etages_depart * $prix_etage_tarif;
}
if (!$devis->ascenseur_arrivee && $devis->etages_arrivee > 0) {
    $etages_arrivee_count = $devis->etages_arrivee;
    $prix_etages += $devis->etages_arrivee * $prix_etage_tarif;
}

// Type de déménagement - SANS MONTANT (modification demandée)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(180, 6, $type_label, 1, 1, 'L', true);

$pdf->SetFont('helvetica', '', 9);

// Détail prix de base
$pdf->Cell(140, 5, '- Frais de déplacement', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_base, 2) . ' CHF', 1, 1, 'R', true);

// Détail distance
$pdf->Cell(140, 5, '- Distance (' . number_format($devis->distance, 2) . ' km x ' . number_format($prix_km, 2) . ' CHF/km)', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_distance, 2) . ' CHF', 1, 1, 'R', true);

// Détail volume
$pdf->Cell(140, 5, '- Volume (' . number_format($devis->volume_total, 2) . ' m3 x ' . number_format($prix_m3, 2) . ' CHF/m3)', 1, 0, 'L', true);
$pdf->Cell(40, 5, number_format($prix_volume, 2) . ' CHF', 1, 1, 'R', true);

// Détail étages
if ($prix_etages > 0) {
    $detail_etages = '- Étages sans ascenseur (';
    if ($etages_depart_count > 0) {
        $detail_etages .= $etages_depart_count . ' étage' . ($etages_depart_count > 1 ? 's' : '') . ' départ';
    }
    if ($etages_arrivee_count > 0) {
        if ($etages_depart_count > 0) {
            $detail_etages .= ' + ';
        }
        $detail_etages .= $etages_arrivee_count . ' étage' . ($etages_arrivee_count > 1 ? 's' : '') . ' arrivée';
    }
    $detail_etages .= ' x ' . number_format($prix_etage_tarif, 2) . ' CHF/étage)';
    
    $pdf->Cell(140, 5, $detail_etages, 1, 0, 'L', true);
    $pdf->Cell(40, 5, number_format($prix_etages, 2) . ' CHF', 1, 1, 'R', true);
}

// Emballage si complet UNIQUEMENT
if ($type === 'complet' && $devis->nombre_cartons > 0) {
    $prix_emballage = $devis->nombre_cartons * $prix_carton_tarif;
    $pdf->Cell(140, 5, '- Emballage (' . $devis->nombre_cartons . ' carton' . ($devis->nombre_cartons > 1 ? 's' : '') . ' x ' . number_format($prix_carton_tarif, 2) . ' CHF/carton)', 1, 0, 'L', true);
    $pdf->Cell(40, 5, number_format($prix_emballage, 2) . ' CHF', 1, 1, 'R', true);
}

// Réduction volume >70m³
if ($devis->volume_total > 70) {
    $taux_reduction = isset($tarifs['reduction_volume']) ? floatval($tarifs['reduction_volume']->valeur) : 5.00;
    $montant_avant_reduction = $prix_base + $prix_distance + $prix_volume + $prix_etages;
    if ($type === 'complet' && $devis->nombre_cartons > 0) {
        $montant_avant_reduction += ($devis->nombre_cartons * $prix_carton_tarif);
    }
    $montant_reduction = $montant_avant_reduction * ($taux_reduction / 100);
    
    $pdf->SetTextColor(0, 128, 0); // Vert pour la réduction
    $pdf->Cell(140, 5, '- Réduction volume > 70m3 (' . number_format($taux_reduction, 2) . '%)', 1, 0, 'L', true);
    $pdf->Cell(40, 5, '- ' . number_format($montant_reduction, 2) . ' CHF', 1, 1, 'R', true);
    $pdf->SetTextColor(60, 60, 60); // Remettre la couleur normale
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
// PRESTATIONS INCLUSES
// ============================================
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(26, 35, 50);
$pdf->Cell(0, 6, 'Prestations incluses :', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(60, 60, 60);

if ($type === 'complet') {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(26, 35, 50);  // Bleu foncé au lieu de turquoise
    $pdf->Cell(0, 5, 'Déménagement Complet - Tranquillité totale, vous n\'avez rien à gérer', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(60, 60, 60);
    
    $prestations_complet = array(
        'Toutes les prestations Standard incluses',
        'Emballage des cartons et protection du mobilier (papier bulle, couvertures, cartons, penderies, housses de protection literie)',
        'Assurance transport incluse',
    );
    
    foreach ($prestations_complet as $prestation) {
        $pdf->Cell(5, 5, '', 0, 0, 'L');
        $pdf->SetFont('zapfdingbats', '', 8);
        $pdf->Cell(5, 5, '4', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, ' ' . $prestation, 0, 1, 'L');
    }
} else {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(26, 35, 50);
    $pdf->Cell(0, 5, 'Déménagement Standard', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(60, 60, 60);
    
    $prestations_standard = array(
        'Mise à disposition des cartons, scotch',
        'Emballage et protection du mobilier',
        'Chargement et déchargement par notre équipe professionnelle',
        'Transport sécurisé en véhicule adapté',
        'Démontage et remontage du mobilier',
        'Mise en place du mobilier dans le nouvel appartement',
        'Assurance transport incluse',
    );
    
    foreach ($prestations_standard as $prestation) {
        $pdf->Cell(5, 5, '', 0, 0, 'L');
        $pdf->SetFont('zapfdingbats', '', 8);
        $pdf->Cell(5, 5, '4', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, ' ' . $prestation, 0, 1, 'L');
    }
}

$pdf->Ln(5);


// ============================================
// REMARQUES
// ============================================
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 4, 'Ce devis est une estimation basée sur les informations fournies. Une visite sur place sera nécessaire pour établir un devis définitif et précis. Les prix peuvent varier en fonction des conditions réelles constatées lors de la visite. Durée de validité : 30 jours.', 0, 'L');

// ============================================
// BARRE INFÉRIEURE - COULEUR SELON TYPE
// ============================================
$pdf->SetY(-15);
// Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);
$pdf->Rect(0, $pdf->GetY(), 210, 8, 'F');

// ============================================
// PAGE 2 - RÉCAPITULATIF DES OBJETS
// ============================================
$pdf->AddPage();

// Barre supérieure - Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);
$pdf->Rect(0, 0, 210, 8, 'F');

$pdf->Ln(12);

$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'RÉCAPITULATIF DES OBJETS', 0, 1, 'C');

$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 5, 'Liste détaillée des objets à déménager', 0, 1, 'C');

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
    // Bleu nuit pour les deux types
    $pdf->SetFillColor(26, 35, 50);
    $pdf->SetTextColor(255, 255, 255);
    // Utilisation de mb_strtoupper pour gérer correctement les accents en majuscules
    $pdf->Cell(0, 7, mb_strtoupper($categorie, 'UTF-8'), 0, 1, 'L', true);
    
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
// Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(155, 8, 'VOLUME TOTAL', 1, 0, 'R', true);
$pdf->Cell(25, 8, number_format($devis->volume_total, 3) . ' m3', 1, 1, 'R', true);

// Barre inférieure
$pdf->SetY(-15);
// Bleu nuit pour les deux types
$pdf->SetFillColor(26, 35, 50);
$pdf->Rect(0, $pdf->GetY(), 210, 8, 'F');