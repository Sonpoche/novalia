<?php
/**
 * Template Email Admin
 * Variables disponibles : $quote, $company
 *
 * @package NovaliaDevis
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- En-tête -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">🔔 Nouveau devis généré</h1>
                        </td>
                    </tr>
                    
                    <!-- Alerte -->
                    <tr>
                        <td style="padding: 20px 30px;">
                            <table width="100%" cellpadding="15" cellspacing="0" style="background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 6px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 15px; color: #856404;">
                                            <strong>⚡ Action requise :</strong> Un nouveau client vient de demander un devis de déménagement.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 0 30px 30px 30px;">
                            
                            <!-- Infos devis -->
                            <h2 style="color: #2d3748; font-size: 18px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;">
                                📋 Informations du devis
                            </h2>
                            
                            <table width="100%" cellpadding="12" cellspacing="0" style="background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px; width: 40%;">Numéro de devis</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <strong style="color: #667eea;"><?php echo esc_html($quote['quote_number']); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Date</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <?php echo date_i18n('d/m/Y à H:i', strtotime($quote['created_at'])); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Montant</td>
                                    <td style="color: #2d3748; font-size: 18px;">
                                        <strong style="color: #48bb78;"><?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €</strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Infos client -->
                            <h2 style="color: #2d3748; font-size: 18px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;">
                                👤 Informations client
                            </h2>
                            
                            <table width="100%" cellpadding="12" cellspacing="0" style="background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px; width: 40%;">Nom</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <strong><?php echo esc_html($quote['customer_firstname'] . ' ' . $quote['customer_name']); ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Email</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>" style="color: #667eea; text-decoration: none;">
                                            <?php echo esc_html($quote['customer_email']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Téléphone</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>" style="color: #667eea; text-decoration: none;">
                                            <?php echo esc_html($quote['customer_phone'] ?: 'Non renseigné'); ?>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Trajet -->
                            <h2 style="color: #2d3748; font-size: 18px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;">
                                📍 Trajet
                            </h2>
                            
                            <table width="100%" cellpadding="12" cellspacing="0" style="background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px; width: 40%;">Départ</td>
                                    <td style="color: #2d3748; font-size: 13px;">
                                        <?php echo esc_html($quote['address_from']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Arrivée</td>
                                    <td style="color: #2d3748; font-size: 13px;">
                                        <?php echo esc_html($quote['address_to']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Distance</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <strong><?php echo number_format($quote['distance'], 2, ',', ' '); ?> km</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #4a5568; font-size: 14px;">Volume total</td>
                                    <td style="color: #2d3748; font-size: 14px;">
                                        <strong><?php echo number_format($quote['total_volume'], 2, ',', ' '); ?> m³</strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Objets -->
                            <h2 style="color: #2d3748; font-size: 18px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0;">
                                📦 Objets à déménager (<?php echo count($quote['items']); ?>)
                            </h2>
                            
                            <table width="100%" cellpadding="10" cellspacing="0" style="background-color: #f8f9fa; border-radius: 6px; margin-bottom: 30px;">
                                <?php foreach ($quote['items'] as $item): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="color: #2d3748; font-size: 13px; width: 60%;">
                                        <?php echo esc_html($item['item_name']); ?>
                                    </td>
                                    <td style="color: #718096; font-size: 13px; text-align: right;">
                                        Qté: <?php echo intval($item['quantity']); ?>
                                    </td>
                                    <td style="color: #667eea; font-size: 13px; text-align: right; font-weight: bold;">
                                        <?php echo number_format($item['item_volume'], 3, ',', ' '); ?> m³
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                            
                            <!-- Bouton action -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes&action=view&id=' . $quote['id']); ?>" 
                                           style="display: inline-block; padding: 15px 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px;">
                                            📄 Voir le devis complet
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Pied de page -->
                    <tr>
                        <td style="background-color: #2d3748; color: #a0aec0; padding: 20px; text-align: center;">
                            <p style="margin: 0; font-size: 12px;">
                                Ce message a été généré automatiquement par le plugin Novalia Devis
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>