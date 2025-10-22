<?php
/**
 * Template Email Client
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
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <?php if (!empty($company['logo_url'])): ?>
                                <img src="<?php echo esc_url($company['logo_url']); ?>" alt="Logo" style="max-width: 150px; margin-bottom: 20px;">
                            <?php endif; ?>
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">✅ Votre devis est prêt !</h1>
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-size: 16px; color: #333333; margin: 0 0 20px 0;">
                                Bonjour <strong><?php echo esc_html($quote['customer_firstname'] . ' ' . $quote['customer_name']); ?></strong>,
                            </p>
                            
                            <p style="font-size: 15px; color: #666666; line-height: 1.6; margin: 0 0 30px 0;">
                                Merci d'avoir utilisé notre outil d'estimation en ligne. Nous avons le plaisir de vous transmettre votre devis de déménagement.
                            </p>
                            
                            <!-- Box devis -->
                            <table width="100%" cellpadding="20" cellspacing="0" style="background-color: #f8f9fa; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td>
                                        <h2 style="color: #667eea; text-align: center; margin: 0 0 20px 0; font-size: 24px;">
                                            Devis N° <?php echo esc_html($quote['quote_number']); ?>
                                        </h2>
                                        
                                        <table width="100%" cellpadding="8" cellspacing="0">
                                            <tr>
                                                <td style="color: #666666; font-size: 14px;">📅 Date:</td>
                                                <td style="color: #333333; font-size: 14px; text-align: right;">
                                                    <strong><?php echo date_i18n('d/m/Y à H:i', strtotime($quote['created_at'])); ?></strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666666; font-size: 14px; padding-top: 10px;">📍 Départ:</td>
                                                <td style="color: #333333; font-size: 14px; text-align: right; padding-top: 10px;">
                                                    <?php echo esc_html($quote['address_from']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666666; font-size: 14px;">📍 Arrivée:</td>
                                                <td style="color: #333333; font-size: 14px; text-align: right;">
                                                    <?php echo esc_html($quote['address_to']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666666; font-size: 14px; padding-top: 10px;">🛣️ Distance:</td>
                                                <td style="color: #333333; font-size: 14px; text-align: right; padding-top: 10px;">
                                                    <strong><?php echo number_format($quote['distance'], 2, ',', ' '); ?> km</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="color: #666666; font-size: 14px;">📦 Volume:</td>
                                                <td style="color: #333333; font-size: 14px; text-align: right;">
                                                    <strong><?php echo number_format($quote['total_volume'], 2, ',', ' '); ?> m³</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Prix -->
                            <table width="100%" cellpadding="20" cellspacing="0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="color: rgba(255,255,255,0.9); margin: 0 0 10px 0; font-size: 16px;">💰 MONTANT ESTIMÉ</p>
                                        <p style="color: #ffffff; margin: 0; font-size: 36px; font-weight: bold;">
                                            <?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 14px; color: #666666; text-align: center; font-style: italic; margin: 0 0 30px 0;">
                                Ce devis est <strong>gratuit et sans engagement</strong>. Il est valable 30 jours.
                            </p>
                            
                            <p style="font-size: 15px; color: #666666; line-height: 1.6; margin: 0 0 20px 0;">
                                📎 Vous trouverez votre devis détaillé en pièce jointe au format PDF.
                            </p>
                            
                            <p style="font-size: 15px; color: #333333; margin: 0 0 10px 0;"><strong>Prochaines étapes :</strong></p>
                            <ul style="color: #666666; font-size: 14px; line-height: 1.8; margin: 0 0 30px 0;">
                                <li>Consultez le devis PDF ci-joint</li>
                                <li>Contactez-nous pour toute question ou modification</li>
                                <li>Confirmez votre réservation en nous contactant directement</li>
                            </ul>
                            
                            <!-- Bouton -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="tel:<?php echo esc_attr($company['phone']); ?>" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                            📞 Nous contacter
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Pied de page -->
                    <tr>
                        <td style="background-color: #2d3748; color: #ffffff; padding: 30px; text-align: center;">
                            <p style="margin: 0 0 10px 0; font-size: 16px; font-weight: bold;">
                                <?php echo esc_html($company['name']); ?>
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #a0aec0; line-height: 1.6;">
                                <?php echo esc_html($company['address']); ?><br>
                                <?php echo esc_html($company['zipcode'] . ' ' . $company['city']); ?><br>
                                📞 <?php echo esc_html($company['phone']); ?> | 
                                📧 <a href="mailto:<?php echo esc_attr($company['email']); ?>" style="color: #667eea; text-decoration: none;">
                                    <?php echo esc_html($company['email']); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>