<?php
/**
 * Template Étape 2 : Objets
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
        <h3><?php _e('Que souhaitez-vous déménager ?', 'novalia-devis'); ?></h3>
        <p class="nd-step-description">
            <?php _e('Sélectionnez vos objets et indiquez les quantités', 'novalia-devis'); ?>
        </p>
    </div>
    
    <!-- Barre de recherche -->
    <div class="nd-search-bar">
        <input type="text" 
               id="nd-items-search" 
               class="nd-form-control" 
               placeholder="<?php _e('🔍 Rechercher un objet...', 'novalia-devis'); ?>">
    </div>
    
    <!-- Filtres par catégorie -->
    <div class="nd-category-filters">
        <button type="button" class="nd-category-btn nd-active" data-category="all">
            <?php _e('Tout', 'novalia-devis'); ?>
        </button>
        <?php foreach ($items_by_category as $category => $items): ?>
            <button type="button" 
                    class="nd-category-btn" 
                    data-category="<?php echo esc_attr(sanitize_title($category)); ?>">
                <?php echo esc_html($category); ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <!-- Liste des objets par catégorie -->
    <div class="nd-items-list" id="nd-items-list">
        <?php foreach ($items_by_category as $category => $items): ?>
            <div class="nd-category-section" data-category="<?php echo esc_attr(sanitize_title($category)); ?>">
                <h4 class="nd-category-title">
                    <span class="nd-category-icon">📦</span>
                    <?php echo esc_html($category); ?>
                </h4>
                
                <div class="nd-items-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="nd-item-card" 
                             data-item-id="<?php echo $item['id']; ?>"
                             data-item-name="<?php echo esc_attr($item['name']); ?>"
                             data-item-volume="<?php echo $item['volume']; ?>"
                             data-category="<?php echo esc_attr(sanitize_title($category)); ?>">
                            
                            <div class="nd-item-header">
                                <span class="nd-item-name"><?php echo esc_html($item['name']); ?></span>
                                <span class="nd-item-volume">
                                    <?php echo number_format($item['volume'], 2, ',', ' '); ?> m³
                                </span>
                            </div>
                            
                            <div class="nd-item-controls">
                                <button type="button" 
                                        class="nd-qty-btn nd-qty-minus" 
                                        data-item-id="<?php echo $item['id']; ?>">
                                    −
                                </button>
                                
                                <input type="number" 
                                       class="nd-qty-input" 
                                       id="qty_<?php echo $item['id']; ?>"
                                       data-item-id="<?php echo $item['id']; ?>"
                                       min="0" 
                                       max="999" 
                                       value="0"
                                       readonly>
                                
                                <button type="button" 
                                        class="nd-qty-btn nd-qty-plus" 
                                        data-item-id="<?php echo $item['id']; ?>">
                                    +
                                </button>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Section objets personnalisés -->
    <div class="nd-custom-items-section">
        <button type="button" class="nd-btn nd-btn-secondary" id="nd-add-custom-item">
            <span class="nd-btn-icon">+</span>
            <?php _e('Ajouter un objet personnalisé', 'novalia-devis'); ?>
        </button>
        
        <div id="nd-custom-items-list"></div>
    </div>
    
    <!-- Modal objet personnalisé -->
    <div class="nd-modal" id="nd-custom-item-modal" style="display: none;">
        <div class="nd-modal-overlay"></div>
        <div class="nd-modal-content">
            <div class="nd-modal-header">
                <h3><?php _e('Ajouter un objet personnalisé', 'novalia-devis'); ?></h3>
                <button type="button" class="nd-modal-close">&times;</button>
            </div>
            <div class="nd-modal-body">
                <div class="nd-form-group">
                    <label for="custom_item_name" class="nd-form-label">
                        <?php _e('Nom de l\'objet', 'novalia-devis'); ?>
                        <span class="nd-required">*</span>
                    </label>
                    <input type="text" 
                           id="custom_item_name" 
                           class="nd-form-control" 
                           placeholder="<?php _e('Ex: Piano à queue', 'novalia-devis'); ?>">
                </div>
                
                <div class="nd-form-group">
                    <label for="custom_item_volume" class="nd-form-label">
                        <?php _e('Volume estimé (m³)', 'novalia-devis'); ?>
                        <span class="nd-required">*</span>
                    </label>
                    <input type="number" 
                           id="custom_item_volume" 
                           class="nd-form-control" 
                           step="0.01" 
                           min="0.01" 
                           placeholder="<?php _e('Ex: 2.5', 'novalia-devis'); ?>">
                    <small class="nd-form-help">
                        <?php _e('Si vous ne connaissez pas le volume, estimez-le approximativement', 'novalia-devis'); ?>
                    </small>
                </div>
                
                <div class="nd-form-group">
                    <label for="custom_item_quantity" class="nd-form-label">
                        <?php _e('Quantité', 'novalia-devis'); ?>
                    </label>
                    <input type="number" 
                           id="custom_item_quantity" 
                           class="nd-form-control" 
                           min="1" 
                           value="1">
                </div>
            </div>
            <div class="nd-modal-footer">
                <button type="button" class="nd-btn nd-btn-secondary nd-modal-close">
                    <?php _e('Annuler', 'novalia-devis'); ?>
                </button>
                <button type="button" class="nd-btn nd-btn-primary" id="nd-save-custom-item">
                    <?php _e('Ajouter', 'novalia-devis'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Options supplémentaires -->
    <div class="nd-additional-options">
        <h4><?php _e('Options supplémentaires', 'novalia-devis'); ?></h4>
        
        <label class="nd-checkbox-label">
            <input type="checkbox" 
                   id="need_packing" 
                   name="need_packing" 
                   value="1">
            <span>
                <?php _e('Service d\'emballage', 'novalia-devis'); ?>
                <small>(<?php echo number_format($pricing['fee_packing'], 2, ',', ' '); ?> € / m³)</small>
            </span>
        </label>
        
        <label class="nd-checkbox-label">
            <input type="checkbox" 
                   id="need_insurance" 
                   name="need_insurance" 
                   value="1">
            <span>
                <?php _e('Assurance tous risques', 'novalia-devis'); ?>
                <small>(<?php echo number_format($pricing['fee_insurance'], 2, ',', ' '); ?> € / m³)</small>
            </span>
        </label>
    </div>
    
    <!-- Récapitulatif du volume -->
    <div class="nd-volume-summary" id="nd-volume-summary">
        <div class="nd-summary-card">
            <div class="nd-summary-icon">📦</div>
            <div class="nd-summary-content">
                <span class="nd-summary-label"><?php _e('Objets sélectionnés', 'novalia-devis'); ?></span>
                <span class="nd-summary-value" id="items_count">0</span>
            </div>
        </div>
        
        <div class="nd-summary-card">
            <div class="nd-summary-icon">📊</div>
            <div class="nd-summary-content">
                <span class="nd-summary-label"><?php _e('Volume total', 'novalia-devis'); ?></span>
                <span class="nd-summary-value" id="total_volume">0 m³</span>
            </div>
        </div>
    </div>
    
    <!-- Champ caché pour le volume total -->
    <input type="hidden" id="total_volume_hidden" name="total_volume" value="0">
    
</div>