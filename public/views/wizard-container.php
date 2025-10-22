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
    
    <!-- Barre de progression -->
    <div class="nd-wizard-progress">
        <div class="nd-progress-step nd-active" data-step="1">
            <div class="nd-step-number">1</div>
            <div class="nd-step-label"><?php _e('Adresses', 'novalia-devis'); ?></div>
        </div>
        <div class="nd-progress-line"></div>
        <div class="nd-progress-step" data-step="2">
            <div class="nd-step-number">2</div>
            <div class="nd-step-label"><?php _e('Objets', 'novalia-devis'); ?></div>
        </div>
        <div class="nd-progress-line"></div>
        <div class="nd-progress-step" data-step="3">
            <div class="nd-step-number">3</div>
            <div class="nd-step-label"><?php _e('Récapitulatif', 'novalia-devis'); ?></div>
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
    
    <!-- Navigation -->
    <div class="nd-wizard-navigation">
        <button type="button" 
                class="nd-btn nd-btn-secondary nd-btn-prev" 
                id="nd-prev-btn" 
                style="display: none;">
            <span class="nd-btn-icon">←</span>
            <?php _e('Précédent', 'novalia-devis'); ?>
        </button>
        
        <button type="button" 
                class="nd-btn nd-btn-primary nd-btn-next" 
                id="nd-next-btn">
            <?php _e('Suivant', 'novalia-devis'); ?>
            <span class="nd-btn-icon">→</span>
        </button>
        
        <button type="submit" 
                class="nd-btn nd-btn-success nd-btn-submit" 
                id="nd-submit-btn" 
                style="display: none;">
            <span class="nd-btn-icon">✓</span>
            <?php _e('Recevoir mon devis', 'novalia-devis'); ?>
        </button>
    </div>
    
    <!-- Loader -->
    <div class="nd-loader" id="nd-loader" style="display: none;">
        <div class="nd-spinner"></div>
        <p><?php _e('Génération de votre devis en cours...', 'novalia-devis'); ?></p>
    </div>
    
    <!-- Message de succès -->
    <div class="nd-success-message" id="nd-success-message" style="display: none;">
        <div class="nd-success-icon">✓</div>
        <h3><?php _e('Devis envoyé avec succès !', 'novalia-devis'); ?></h3>
        <p><?php _e('Vous allez recevoir votre devis par email dans quelques instants.', 'novalia-devis'); ?></p>
        <p class="nd-success-details">
            <?php _e('Vérifiez également vos courriers indésirables si vous ne le trouvez pas.', 'novalia-devis'); ?>
        </p>
        <button type="button" class="nd-btn nd-btn-primary" id="nd-new-quote">
            <?php _e('Faire un nouveau devis', 'novalia-devis'); ?>
        </button>
    </div>
    
</div>