<?php
/**
 * Template du conteneur du wizard
 *
 * @package NovaliaDevis
 * @subpackage Public/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nd-wizard-container" id="nd-wizard">
    
    <?php if ($atts['show_title'] === 'yes'): ?>
        <div class="nd-wizard-header">
            <h2 class="nd-wizard-title"><?php echo esc_html($atts['title']); ?></h2>
            <p class="nd-wizard-subtitle">
                <?php _e('Obtenez une estimation gratuite et immédiate de votre déménagement', 'novalia-devis'); ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Stepper moderne -->
    <div class="nd-steps-indicator">
        <div class="nd-progress-step nd-active" data-step="1">
            <div class="nd-progress-step-circle">1</div>
            <div class="nd-progress-step-label"><?php _e('Adresses', 'novalia-devis'); ?></div>
        </div>
        
        <div class="nd-progress-step" data-step="2">
            <div class="nd-progress-step-circle">2</div>
            <div class="nd-progress-step-label"><?php _e('Objets', 'novalia-devis'); ?></div>
        </div>
        
        <div class="nd-progress-step" data-step="3">
            <div class="nd-progress-step-circle">3</div>
            <div class="nd-progress-step-label"><?php _e('Récapitulatif', 'novalia-devis'); ?></div>
        </div>
    </div>
    
    <!-- Formulaire -->
    <form id="nd-quote-form" class="nd-wizard-form">
        
        <!-- Étape 1 : Adresses -->
        <div class="nd-wizard-step nd-active" data-step="1">
            <?php include ND_PLUGIN_DIR . 'public/views/step-1-addresses.php'; ?>
        </div>
        
        <!-- Étape 2 : Objets -->
        <div class="nd-wizard-step" data-step="2" style="display: none;">
            <?php include ND_PLUGIN_DIR . 'public/views/step-2-items.php'; ?>
        </div>
        
        <!-- Étape 3 : Récapitulatif -->
        <div class="nd-wizard-step" data-step="3" style="display: none;">
            <?php include ND_PLUGIN_DIR . 'public/views/step-3-summary.php'; ?>
        </div>
        
    </form>
    
    <!-- Volume Sticky - Visible UNIQUEMENT au Step 2 -->
    <div id="nd-volume-sticky" style="position: fixed; top: 100px; right: 60px; width: 280px; display: none; z-index: 100;">
        <div style="background: linear-gradient(135deg, #2BBBAD 0%, #1A9688 100%); padding: 32px 24px; border-radius: 16px; box-shadow: 0 12px 32px rgba(43, 187, 173, 0.3); color: white;">
            <h3 style="margin: 0 0 24px 0; font-size: 1.5rem; font-weight: 700; color: white; text-align: center; font-family: 'Montserrat', sans-serif;">
                <?php _e('Résumé', 'novalia-devis'); ?>
            </h3>
            
            <div style="display: grid; gap: 20px;">
                <div style="text-align: center; padding: 20px; background: rgba(255, 255, 255, 0.15); border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">Objets</div>
                    <div style="font-size: 3rem; font-weight: 800; line-height: 1;" id="total_items_count">0</div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: rgba(255, 255, 255, 0.15); border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">Volume</div>
                    <div style="font-size: 3rem; font-weight: 800; line-height: 1;">
                        <span id="total_volume">0</span>
                        <span style="font-size: 1.5rem; opacity: 0.8;"> m³</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="nd-wizard-navigation">
        <button type="button" 
                class="nd-btn nd-btn-secondary" 
                id="nd-prev-btn" 
                style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            <?php _e('Précédent', 'novalia-devis'); ?>
        </button>
        
        <button type="button" 
                class="nd-btn nd-btn-primary" 
                id="nd-next-btn">
            <?php _e('Suivant', 'novalia-devis'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
        
        <button type="submit" 
                class="nd-btn nd-btn-accent" 
                id="nd-submit-btn" 
                style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <?php _e('Recevoir mon devis', 'novalia-devis'); ?>
        </button>
    </div>
    
    <!-- Loader -->
    <div id="nd-loader" class="nd-loader" style="display: none;">
        <div class="nd-spinner"></div>
        <p><?php _e('Génération de votre devis...', 'novalia-devis'); ?></p>
    </div>
    
    <!-- Message succès -->
    <div id="nd-success-message" class="nd-success-message" style="display: none;">
        <div class="nd-success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3><?php _e('Devis envoyé avec succès !', 'novalia-devis'); ?></h3>
        <p><?php _e('Vous allez recevoir votre devis détaillé par email dans quelques instants.', 'novalia-devis'); ?></p>
        <button type="button" class="nd-btn nd-btn-primary" id="nd-new-quote">
            <?php _e('Faire un nouveau devis', 'novalia-devis'); ?>
        </button>
    </div>
    
</div>
