<?php
/**
 * Template Étape 3 : Récapitulatif
 *
 * @package NovaliaDevis
 * @subpackage Public/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nd-step-content">
    
    <h3 class="nd-step-title">
        <?php _e('Récapitulatif et estimation', 'novalia-devis'); ?>
    </h3>
    <p class="nd-step-description">
        <?php _e('Vérifiez vos informations et recevez votre devis par email', 'novalia-devis'); ?>
    </p>
    
    <!-- Récapitulatif simplifié -->
    <div class="nd-recap-section">
        <h4 class="nd-recap-section-title" style="color: var(--primary); font-size: 1.25rem; margin-bottom: 24px;">
            <?php _e('Votre déménagement', 'novalia-devis'); ?>
        </h4>
        
        <div style="display: grid; gap: 16px; background: white; padding: 24px; border-radius: 12px; border: 2px solid #E5E7EB;">
            <!-- Trajet -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; border-bottom: 2px solid #F3F4F6;">
                <div>
                    <div style="font-size: 0.75rem; color: #6B7280; margin-bottom: 4px; text-transform: uppercase; font-weight: 600;">De</div>
                    <div style="font-weight: 600; color: #1A2332;" id="recap_address_from">-</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:24px;height:24px;color:#2BBBAD;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <div>
                    <div style="font-size: 0.75rem; color: #6B7280; margin-bottom: 4px; text-transform: uppercase; font-weight: 600;">Vers</div>
                    <div style="font-weight: 600; color: #1A2332;" id="recap_address_to">-</div>
                </div>
            </div>
            
            <!-- Distance -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #F3F4F6; border-radius: 8px;">
                <span style="font-weight: 600; color: #374151;">Distance</span>
                <span style="font-size: 1.5rem; font-weight: 700; color: #2BBBAD;" id="recap_distance">0 km</span>
            </div>
            
            <!-- Volume -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #F3F4F6; border-radius: 8px;">
                <span style="font-weight: 600; color: #374151;">Volume total</span>
                <span style="font-size: 1.5rem; font-weight: 700; color: #2BBBAD;" id="recap_total_volume">0 m³</span>
            </div>
            
            <!-- Nombre objets -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; color: #374151;">Nombre d'objets</span>
                <span style="font-weight: 700; color: #1A2332; font-size: 1.125rem;" id="recap_items_count">0</span>
            </div>
        </div>
        
        <!-- Liste objets (masqué par défaut) -->
        <details style="margin-top: 16px;">
            <summary style="cursor: pointer; padding: 12px; background: #F9FAFB; border-radius: 8px; font-weight: 600; color: #374151;">
                Voir le détail des objets
            </summary>
            <div id="recap_items" style="margin-top: 16px;">
                <!-- Rempli par JavaScript -->
            </div>
        </details>
    </div>
    
    <!-- Estimation du prix -->
    <div class="nd-price-estimation">
        <h4 style="font-size: 1.5rem; color: var(--primary); margin-bottom: 24px;">
            <?php _e('Estimation du prix', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-price-breakdown" style="background: white; padding: 32px; border-radius: 12px; border: 2px solid #E5E7EB;">
            <div class="nd-price-row" style="display:flex;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Transport</span>
                <span class="nd-price-value" id="price_distance" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            <div class="nd-price-row" style="display:flex;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Manutention</span>
                <span class="nd-price-value" id="price_volume" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            <div class="nd-price-row" id="price_floors_line" style="display:none;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Étages</span>
                <span class="nd-price-value" id="price_floors" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            <div class="nd-price-row" id="price_packing_line" style="display:none;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Emballage</span>
                <span class="nd-price-value" id="price_packing" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            <div class="nd-price-row" id="price_insurance_line" style="display:none;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Assurance</span>
                <span class="nd-price-value" id="price_insurance" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            <div class="nd-price-row" id="price_fixed_line" style="display:none;justify-content:space-between;padding:16px 0;border-bottom:1px solid #F3F4F6;">
                <span class="nd-price-label" style="color:#374151;font-weight:500;">Frais fixes</span>
                <span class="nd-price-value" id="price_fixed" style="font-weight:600;color:#1A2332;">0.00 CHF</span>
            </div>
            
            <div class="nd-price-row nd-price-total" style="display:flex;justify-content:space-between;padding:24px 0 0 0;margin-top:16px;">
                <span class="nd-price-label" style="color:#1A2332;font-weight:700;font-size:1.25rem;">TOTAL ESTIMÉ</span>
                <span class="nd-price-value" id="price_total" style="font-weight:800;color:#2BBBAD;font-size:2rem;">0.00 CHF</span>
            </div>
        </div>
        
        <p style="text-align: center; color: #6B7280; font-size: 0.875rem; margin-top: 16px;">
            * <?php _e('Prix indicatif - Un devis détaillé vous sera envoyé par email', 'novalia-devis'); ?>
        </p>
    </div>
    
    <!-- Formulaire contact -->
    <div class="nd-recap-section">
        <h4 class="nd-recap-section-title" style="color: var(--primary); font-size: 1.25rem; margin-bottom: 24px;">
            <?php _e('Vos coordonnées', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-form-row nd-form-row-2">
            <div class="nd-form-group">
                <label for="customer_firstname" class="nd-form-label required">
                    <?php _e('Prénom', 'novalia-devis'); ?>
                </label>
                <input type="text" 
                       id="customer_firstname" 
                       name="customer_firstname" 
                       class="nd-input" 
                       required>
            </div>
            
            <div class="nd-form-group">
                <label for="customer_name" class="nd-form-label required">
                    <?php _e('Nom', 'novalia-devis'); ?>
                </label>
                <input type="text" 
                       id="customer_name" 
                       name="customer_name" 
                       class="nd-input" 
                       required>
            </div>
        </div>
        
        <div class="nd-form-row nd-form-row-2">
            <div class="nd-form-group">
                <label for="customer_email" class="nd-form-label required">
                    <?php _e('Email', 'novalia-devis'); ?>
                </label>
                <input type="email" 
                       id="customer_email" 
                       name="customer_email" 
                       class="nd-input" 
                       required>
            </div>
            
            <div class="nd-form-group">
                <label for="customer_phone" class="nd-form-label">
                    <?php _e('Téléphone', 'novalia-devis'); ?>
                </label>
                <input type="tel" 
                       id="customer_phone" 
                       name="customer_phone" 
                       class="nd-input">
            </div>
        </div>
        
        <div class="nd-form-group">
            <label class="nd-checkbox-wrapper">
                <div class="nd-checkbox">
                    <input type="checkbox" 
                           id="consent_rgpd" 
                           name="consent_rgpd" 
                           required>
                    <div class="nd-checkbox-box"></div>
                </div>
                <span class="nd-checkbox-label">
                    <?php _e('J\'accepte que mes données soient utilisées pour recevoir mon devis * Vos données sont uniquement utilisées pour traiter votre demande de devis et ne seront jamais communiquées à des tiers.', 'novalia-devis'); ?>
                </span>
            </label>
        </div>
        
        <div style="background: #F3F4F6; border-left: 4px solid #2BBBAD; border-radius: 8px; padding: 20px; margin-top: 24px;">
            <h5 style="margin: 0 0 12px 0; font-family: 'Montserrat', sans-serif; font-size: 1.125rem; color: #1A2332; font-weight: 600;">
                <?php _e('Que se passe-t-il ensuite ?', 'novalia-devis'); ?>
            </h5>
            <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #374151;">
                <li><?php _e('Vous recevrez votre devis détaillé par email dans les minutes qui suivent', 'novalia-devis'); ?></li>
                <li><?php _e('Le devis est gratuit et sans engagement', 'novalia-devis'); ?></li>
                <li><?php _e('Notre équipe vous contactera pour confirmer les détails', 'novalia-devis'); ?></li>
            </ul>
        </div>
    </div>
    
</div>
