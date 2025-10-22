<?php
/**
 * Template PDF du devis
 * Variables disponibles : $quote, $company, $pdf_settings, $calculation
 *
 * @package NovaliaDevis
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ce fichier génère le contenu HTML qui sera converti en PDF par TCPDF
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333333;
            line-height: 1.4;
        }
        h1 {
            color: #667eea;
            font-size: 24pt;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        h2 {
            color: #2d3748;
            font-size: 14pt;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #667eea;
            font-weight: bold;
        }
        h3 {
            color: #4a5568;
            font-size: 11pt;
            margin: 15px 0 8px 0;
            font-weight: bold;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            padding: 5px;
        }
        .logo {
            text-align: left;
        }
        .company-info {
            text-align: right;
            font-size: 8pt;
            color: #666666;
        }
        .quote-info-box {
            background-color: #f7fafc;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .quote-info-box table {
            width: 100%;
            font-size: 9pt;
        }
        .quote-info-box td {
            padding: 5px 0;
        }
        .customer-section,
        .addresses-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .section-title {
            font-weight: bold;
            color: #2d3748;
            font-size: 11pt;
            margin-bottom: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .data-table th {
            background-color: #667eea;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
        }
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
        }
        .data-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .total-row {
            background-color: #2d3748 !important;
            color: white !important;
            font-weight: bold;
            font-size: 10pt;
        }
        .total-row td {
            border-bottom: none !important;
            padding: 12px 8px !important;
        }
        .price-detail-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-left: 3px solid #667eea;
        }
        .price-line {
            display: table;
            width: 100%;
            margin: 8px 0;
            font-size: 9pt;
        }
        .price-line-label {
            display: table-cell;
            width: 70%;
        }
        .price-line-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
        }
        .final-total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            margin: 20px 0;
            text-align: right;
            font-size: 14pt;
            font-weight: bold;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #718096;
            text-align: center;
            line-height: 1.6;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #667eea;
            color: white;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .mt-20 {
            margin-top: 20px;
        }
        .route-arrow {
            color: #667eea;
            font-size: 16pt;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<!-- En-tête -->
<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="logo" style="width: 50%;">
            <?php if (!empty($company['logo_url'])): ?>
                <img src="<?php echo esc_url($company['logo_url']); ?>" style="height: 50px;" alt="Logo">
            <?php endif; ?>
        </td>
        <td class="company-info" style="width: 50%;">
            <h1 style="margin: 0;">DEVIS</h1>
            <div style="margin-top: 10px;">
                <strong><?php echo esc_html($company['name']); ?></strong><br>
                <?php echo esc_html($company['address']); ?><br>
                <?php echo esc_html($company['zipcode']); ?> <?php echo esc_html($company['city']); ?><br>
                Tél: <?php echo esc_html($company['phone']); ?><br>
                Email: <?php echo esc_html($company['email']); ?><br>
                <?php if (!empty($company['siret'])): ?>
                    SIRET: <?php echo esc_html($company['siret']); ?>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>

<!-- Informations du devis -->
<div class="quote-info-box">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 50%;"><strong>N° Devis:</strong> <?php echo esc_html($quote['quote_number']); ?></td>
            <td style="width: 50%; text-align: right;"><strong>Date:</strong> <?php echo date_i18n('d/m/Y', strtotime($quote['created_at'])); ?></td>
        </tr>
    </table>
</div>

<!-- Informations client -->
<div class="customer-section">
    <div class="section-title">CLIENT</div>
    <strong><?php echo esc_html($quote['customer_firstname']); ?> <?php echo esc_html($quote['customer_name']); ?></strong><br>
    Email: <?php echo esc_html($quote['customer_email']); ?><br>
    <?php if (!empty($quote['customer_phone'])): ?>
        Téléphone: <?php echo esc_html($quote['customer_phone']); ?><br>
    <?php endif; ?>
</div>

<!-- Adresses et itinéraire -->
<div class="addresses-section">
    <div class="section-title">ITINÉRAIRE</div>
    
    <table cellpadding="5" cellspacing="0" style="width: 100%; font-size: 9pt;">
        <tr>
            <td style="width: 30%;"><strong>Départ:</strong></td>
            <td><?php echo esc_html($quote['address_from']); ?></td>
        </tr>
        <tr>
            <td><strong>Arrivée:</strong></td>
            <td><?php echo esc_html($quote['address_to']); ?></td>
        </tr>
        <tr>
            <td><strong>Distance:</strong></td>
            <td><span class="badge"><?php echo number_format($quote['distance'], 2, ',', ' '); ?> km</span></td>
        </tr>
    </table>
</div>

<!-- Objets à déménager -->
<h2>DÉTAIL DES OBJETS</h2>

<table class="data-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 50%;">Objet</th>
            <th style="width: 15%; text-align: center;">Quantité</th>
            <th style="width: 17%; text-align: right;">Volume unitaire</th>
            <th style="width: 18%; text-align: right;">Volume total</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total_volume = 0;
        foreach ($quote['items'] as $item): 
            $item_total = $item['item_volume'] * $item['quantity'];
            $total_volume += $item_total;
        ?>
            <tr>
                <td><?php echo esc_html($item['item_name']); ?></td>
                <td style="text-align: center;"><?php echo intval($item['quantity']); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['item_volume'], 3, ',', ' '); ?> m³</td>
                <td style="text-align: right;"><strong><?php echo number_format($item_total, 3, ',', ' '); ?> m³</strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3" style="text-align: right;"><strong>VOLUME TOTAL</strong></td>
            <td style="text-align: right;"><strong><?php echo number_format($total_volume, 3, ',', ' '); ?> m³</strong></td>
        </tr>
    </tfoot>
</table>

<!-- Détail du prix -->
<h2>DÉTAIL DU PRIX</h2>

<div class="price-detail-box">
    <div class="price-line">
        <div class="price-line-label">
            Transport (<?php echo number_format($quote['distance'], 2, ',', ' '); ?> km × <?php echo number_format($calculation['breakdown']['distance']['rate'], 2, ',', ' '); ?> €/km)
        </div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['distance']['price'], 2, ',', ' '); ?> €
        </div>
    </div>
    
    <div class="price-line">
        <div class="price-line-label">
            Manutention (<?php echo number_format($total_volume, 3, ',', ' '); ?> m³ × <?php echo number_format($calculation['breakdown']['volume']['rate'], 2, ',', ' '); ?> €/m³)
        </div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['volume']['price'], 2, ',', ' '); ?> €
        </div>
    </div>
    
    <?php if ($calculation['breakdown']['floors']['price'] > 0): ?>
    <div class="price-line">
        <div class="price-line-label">Frais d'étages</div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['floors']['price'], 2, ',', ' '); ?> €
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($calculation['breakdown']['packing']['enabled']): ?>
    <div class="price-line">
        <div class="price-line-label">Service d'emballage</div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['packing']['price'], 2, ',', ' '); ?> €
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($calculation['breakdown']['insurance']['enabled']): ?>
    <div class="price-line">
        <div class="price-line-label">Assurance tous risques</div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['insurance']['price'], 2, ',', ' '); ?> €
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($calculation['breakdown']['fixed_fee'] > 0): ?>
    <div class="price-line">
        <div class="price-line-label">Frais fixes</div>
        <div class="price-line-value">
            <?php echo number_format($calculation['breakdown']['fixed_fee'], 2, ',', ' '); ?> €
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Prix total -->
<table class="data-table mt-20" cellpadding="0" cellspacing="0">
    <tr class="total-row">
        <td style="text-align: right; font-size: 16pt;">
            <strong>MONTANT TOTAL</strong>
        </td>
        <td style="text-align: right; font-size: 16pt; width: 30%;">
            <strong><?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €</strong>
        </td>
    </tr>
</table>

<!-- Mentions légales -->
<div class="footer">
    <?php echo nl2br(esc_html($pdf_settings['legal_mentions'])); ?><br><br>
    <?php echo esc_html($pdf_settings['footer_text']); ?>
</div>

</body>
</html>