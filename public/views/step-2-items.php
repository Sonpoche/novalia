<?php
/**
 * Étape 2 : Sélection des objets
 * VERSION 3 : Quantités visibles + liste collapsible + pas de volume dans step
 *
 * @package NovaliaDevis
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupération des catégories et objets
$categories = ND_Items_Manager::get_categories_with_items();
?>

<div class="nd-step-content">
    
    <h3 class="nd-step-title">
        <?php _e('Que souhaitez-vous déménager ?', 'novalia-devis'); ?>
    </h3>
    <p class="nd-step-description">
        <?php _e('Sélectionnez vos objets et indiquez les quantités', 'novalia-devis'); ?>
    </p>
    
    <!-- Grille d'objets -->
    <div class="nd-items-wrapper">
        <?php foreach ($categories as $category): ?>
            
            <div class="nd-category-section">
                <h4 class="nd-category-title">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <?php echo esc_html($category['name']); ?>
                </h4>
                
                <div class="nd-items-grid">
                    <?php foreach ($category['items'] as $item): ?>
                        
                        <div class="nd-item-card" 
                             data-item-id="<?php echo $item['id']; ?>"
                             data-item-name="<?php echo esc_attr($item['name']); ?>"
                             data-item-volume="<?php echo esc_attr($item['volume']); ?>">
                            
                            <!-- Icône -->
                            <div class="nd-item-icon">
                                <?php if (!empty($item['icon_url'])): ?>
                                    <img src="<?php echo esc_url($item['icon_url']); ?>" alt="<?php echo esc_attr($item['name']); ?>">
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Info -->
                            <div class="nd-item-info">
                                <h5 class="nd-item-name"><?php echo esc_html($item['name']); ?></h5>
                                <p class="nd-item-volume"><?php echo number_format($item['volume'], 2); ?> m³</p>
                            </div>
                            
                            <!-- Contrôles quantité -->
                            <div class="nd-qty-controls">
                                <button type="button" 
                                        class="nd-qty-btn nd-qty-minus" 
                                        data-item-id="<?php echo $item['id']; ?>">−</button>
                                
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
                                        data-item-id="<?php echo $item['id']; ?>">+</button>
                            </div>
                        </div>
                        
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php endforeach; ?>
    </div>
    
    <!-- Objets personnalisés -->
    <div class="nd-custom-items-section">
        <h4 class="nd-section-title">
            <?php _e('Ajouter un objet personnalisé', 'novalia-devis'); ?>
        </h4>
        
        <div style="display: grid; grid-template-columns: 1fr 120px 100px auto; gap: 16px; align-items: end; margin-bottom: 24px;">
            <div class="nd-form-group">
                <label for="custom_item_name" class="nd-form-label">
                    <?php _e('Nom de l\'objet', 'novalia-devis'); ?>
                </label>
                <input type="text" 
                       id="custom_item_name" 
                       class="nd-input" 
                       placeholder="Ex: Piano à queue">
            </div>
            
            <div class="nd-form-group">
                <label for="custom_item_volume" class="nd-form-label">
                    <?php _e('Volume (m³)', 'novalia-devis'); ?>
                </label>
                <input type="number" 
                       id="custom_item_volume" 
                       class="nd-input" 
                       step="0.01"
                       min="0.01"
                       placeholder="1.50">
            </div>
            
            <div class="nd-form-group">
                <label for="custom_item_qty" class="nd-form-label">
                    <?php _e('Quantité', 'novalia-devis'); ?>
                </label>
                <input type="number" 
                       id="custom_item_qty" 
                       class="nd-input" 
                       min="1"
                       value="1">
            </div>
            
            <button type="button" 
                    id="add-custom-item" 
                    class="nd-btn nd-btn-secondary"
                    style="height: 44px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <?php _e('Ajouter', 'novalia-devis'); ?>
            </button>
        </div>
    </div>
    
    <!-- Liste objets sélectionnés (collapsible) -->
    <details style="margin-top: 32px; background: white; padding: 24px; border-radius: 12px; border: 2px solid #E5E7EB;">
        <summary style="cursor: pointer; font-weight: 700; font-size: 1.125rem; color: #1A2332; display: flex; justify-content: space-between; align-items: center; padding: 4px;">
            <span>📦 Objets sélectionnés</span>
            <span style="font-size: 0.875rem; color: #6B7280; font-weight: 500;"><span id="items_count_label">0</span> objet(s)</span>
        </summary>
        <div id="selected-items-list" style="margin-top: 20px;">
            <p style="text-align: center; color: #9CA3AF; padding: 40px 20px;">Aucun objet sélectionné</p>
        </div>
    </details>
    
</div>
