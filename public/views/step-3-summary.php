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
    
    <div class="nd-step-header">
        <h3><?php _e('Récapitulatif et estimation', 'novalia-devis'); ?></h3>
        <p class="nd-step-description">
            <?php _e('Vérifiez vos informations et recevez votre devis par email', 'novalia-devis'); ?>
        </p>
    </div>
    
    <!-- Récapitulatif du trajet -->
    <div class="nd-recap-section">
        <h4 class="nd-recap-title">
            <span class="nd-recap-icon">🛣️</span>
            <?php _e('Votre trajet', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-recap-route">
            <div class="nd-route-item">
                <span class="nd-route-point">📍</span>
                <div class="nd-route-details">
                    <strong><?php _e('Départ', 'novalia-devis'); ?></strong>
                    <p id="recap_address_from">-</p>
                    <small id="recap_floors_from"></small>
                </div>
            </div>
            
            <div class="nd-route-distance">
                <span class="nd-distance-badge" id="recap_distance">0 km</span>
            </div>
            
            <div class="nd-route-item">
                <span class="nd-route-point">📍</span>
                <div class="nd-route-details">
                    <strong><?php _e('Arrivée', 'novalia-devis'); ?></strong>
                    <p id="recap_address_to">-</p>
                    <small id="recap_floors_to"></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Récapitulatif des objets -->
    <div class="nd-recap-section">
        <h4 class="nd-recap-title">
            <span class="nd-recap-icon">📦</span>
            <?php _e('Vos objets', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-items-recap" id="recap_items">
            <!-- Rempli dynamiquement par JavaScript -->
        </div>
        
        <div class="nd-recap-totals">
            <div class="nd-recap-total-row">
                <span><?php _e('Nombre d\'objets', 'novalia-devis'); ?></span>
                <strong id="recap_items_count">0</strong>
            </div>
            <div class="nd-recap-total-row nd-highlight">
                <span><?php _e('Volume total', 'novalia-devis'); ?></span>
                <strong id="recap_total_volume">0 m³</strong>
            </div>
        </div>
    </div>
    
    <!-- Estimation du prix -->
    <div class="nd-price-section">
        <h4 class="nd-recap-title">
            <span class="nd-recap-icon">💰</span>
            <?php _e('Estimation du prix', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-price-breakdown">
            <div class="nd-price-line">
                <span><?php _e('Transport', 'novalia-devis'); ?></span>
                <span id="price_distance">0.00 €</span>
            </div>
            <div class="nd-price-line">
                <span><?php _e('Manutention', 'novalia-devis'); ?></span>
                <span id="price_volume">0.00 €</span>
            </div>
            <div class="nd-price-line" id="price_floors_line" style="display: none;">
                <span><?php _e('Étages', 'novalia-devis'); ?></span>
                <span id="price_floors">0.00 €</span>
            </div>
            <div class="nd-price-line" id="price_packing_line" style="display: none;">
                <span><?php _e('Service d\'emballage', 'novalia-devis'); ?></span>
                <span id="price_packing">0.00 €</span>
            </div>
            <div class="nd-price-line" id="price_insurance_line" style="display: none;">
                <span><?php _e('Assurance', 'novalia-devis'); ?></span>
                <span id="price_insurance">0.00 €</span>
            </div>
            <div class="nd-price-line" id="price_fixed_line" style="display: none;">
                <span><?php _e('Frais fixes', 'novalia-devis'); ?></span>
                <span id="price_fixed">0.00 €</span>
            </div>
        </div>
        
        <div class="nd-price-total">
            <span><?php _e('TOTAL ESTIMÉ', 'novalia-devis'); ?></span>
            <strong id="price_total">0.00 €</strong>
        </div>
        
        <p class="nd-price-notice">
            <small>
                <?php _e('* Prix indicatif - Un devis détaillé vous sera envoyé par email', 'novalia-devis'); ?>
            </small>
        </p>
    </div>
    
    <!-- Formulaire de contact -->
    <div class="nd-contact-section">
        <h4 class="nd-recap-title">
            <span class="nd-recap-icon">👤</span>
            <?php _e('Vos coordonnées', 'novalia-devis'); ?>
        </h4>
        
        <div class="nd-form-row">
            <div class="nd-form-group nd-form-col-2">
                <label for="customer_firstname" class="nd-form-label">
                    <?php _e('Prénom', 'novalia-devis'); ?>
                    <span class="nd-required">*</span>
                </label>
                <input type="text" 
                       id="customer_firstname" 
                       name="customer_firstname" 
                       class="nd-form-control" 
                       placeholder="<?php _e('Jean', 'novalia-devis'); ?>"
                       required>
            </div>
            
            <div class="nd-form-group nd-form-col-2">
                <label for="customer_name" class="nd-form-label">
                    <?php _e('Nom', 'novalia-devis'); ?>
                    <span class="nd-required">*</span>
                </label>
                <input type="text" 
                       id="customer_name" 
                       name="customer_name" 
                       class="nd-form-control" 
                       placeholder="<?php _e('Dupont', 'novalia-devis'); ?>"
                       required>
            </div>
        </div>
        
        <div class="nd-form-row">
            <div class="nd-form-group nd-form-col-2">
                <label for="customer_email" class="nd-form-label">
                    <?php _e('Email', 'novalia-devis'); ?>
                    <span class="nd-required">*</span>
                </label>
                <input type="email" 
                       id="customer_email" 
                       name="customer_email" 
                       class="nd-form-control" 
                       placeholder="<?php _e('jean.dupont@example.com', 'novalia-devis'); ?>"
                       required>
            </div>
            
            <div class="nd-form-group nd-form-col-2">
                <label for="customer_phone" class="nd-form-label">
                    <?php _e('Téléphone', 'novalia-devis'); ?>
                </label>
                <input type="tel" 
                       id="customer_phone" 
                       name="customer_phone" 
                       class="nd-form-control" 
                       placeholder="<?php _e('06 12 34 56 78', 'novalia-devis'); ?>">
            </div>
        </div>
        
        <!-- Consentement RGPD -->
        <div class="nd-form-group">
            <label class="nd-checkbox-label nd-consent">
                <input type="checkbox" 
                       id="consent_rgpd" 
                       name="consent_rgpd" 
                       value="1"
                       required>
                <span>
                    <?php _e('J\'accepte que mes données soient utilisées pour recevoir mon devis', 'novalia-devis'); ?>
                    <span class="nd-required">*</span>
                </span>
            </label>
            <small class="nd-form-help">
                <?php _e('Vos données sont uniquement utilisées pour traiter votre demande de devis et ne seront jamais communiquées à des tiers.', 'novalia-devis'); ?>
            </small>
        </div>
    </div>
    
    <!-- Message de confirmation -->
    <div class="nd-info-box">
        <span class="nd-info-icon">ℹ️</span>
        <div class="nd-info-content">
            <strong><?php _e('Que se passe-t-il ensuite ?', 'novalia-devis'); ?></strong>
            <ul>
                <li><?php _e('Vous recevrez votre devis détaillé par email dans les minutes qui suivent', 'novalia-devis'); ?></li>
                <li><?php _e('Le devis est gratuit et sans engagement', 'novalia-devis'); ?></li>
                <li><?php _e('Notre équipe vous contactera pour confirmer les détails', 'novalia-devis'); ?></li>
            </ul>
        </div>
    </div>
    
</div>