<?php
/**
 * Template Étape 1 : Adresses
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
        <h3><?php _e('D\'où à où déménagez-vous ?', 'novalia-devis'); ?></h3>
        <p class="nd-step-description">
            <?php _e('Saisissez vos adresses de départ et d\'arrivée pour calculer la distance', 'novalia-devis'); ?>
        </p>
    </div>
    
    <!-- Adresse de départ -->
    <div class="nd-form-group">
        <label for="address_from" class="nd-form-label">
            <span class="nd-label-icon">📍</span>
            <?php _e('Adresse de départ', 'novalia-devis'); ?>
            <span class="nd-required">*</span>
        </label>
        <div class="nd-input-wrapper">
            <input type="text" 
                   id="address_from" 
                   name="address_from" 
                   class="nd-form-control nd-address-autocomplete" 
                   placeholder="<?php _e('Ex: 123 Rue de la République, Paris', 'novalia-devis'); ?>"
                   autocomplete="off"
                   required>
            <div class="nd-autocomplete-results" id="autocomplete_from"></div>
        </div>
        <small class="nd-form-help">
            <?php _e('Commencez à taper pour voir les suggestions', 'novalia-devis'); ?>
        </small>
    </div>
    
    <!-- Options départ -->
    <div class="nd-options-group">
        <h4><?php _e('Options de départ', 'novalia-devis'); ?></h4>
        
        <div class="nd-option-row">
            <label for="floors_from" class="nd-option-label">
                <?php _e('Étage', 'novalia-devis'); ?>
            </label>
            <input type="number" 
                   id="floors_from" 
                   name="floors_from" 
                   class="nd-form-control nd-small" 
                   min="0" 
                   max="50" 
                   value="0"
                   placeholder="0">
        </div>
        
        <div class="nd-option-row">
            <label class="nd-checkbox-label">
                <input type="checkbox" 
                       id="has_elevator_from" 
                       name="has_elevator_from" 
                       value="1">
                <span><?php _e('Ascenseur disponible', 'novalia-devis'); ?></span>
            </label>
        </div>
    </div>
    
    <div class="nd-separator">
        <span class="nd-separator-icon">⬇</span>
    </div>
    
    <!-- Adresse d'arrivée -->
    <div class="nd-form-group">
        <label for="address_to" class="nd-form-label">
            <span class="nd-label-icon">📍</span>
            <?php _e('Adresse d\'arrivée', 'novalia-devis'); ?>
            <span class="nd-required">*</span>
        </label>
        <div class="nd-input-wrapper">
            <input type="text" 
                   id="address_to" 
                   name="address_to" 
                   class="nd-form-control nd-address-autocomplete" 
                   placeholder="<?php _e('Ex: 45 Avenue des Champs, Lyon', 'novalia-devis'); ?>"
                   autocomplete="off"
                   required>
            <div class="nd-autocomplete-results" id="autocomplete_to"></div>
        </div>
        <small class="nd-form-help">
            <?php _e('Commencez à taper pour voir les suggestions', 'novalia-devis'); ?>
        </small>
    </div>
    
    <!-- Options arrivée -->
    <div class="nd-options-group">
        <h4><?php _e('Options d\'arrivée', 'novalia-devis'); ?></h4>
        
        <div class="nd-option-row">
            <label for="floors_to" class="nd-option-label">
                <?php _e('Étage', 'novalia-devis'); ?>
            </label>
            <input type="number" 
                   id="floors_to" 
                   name="floors_to" 
                   class="nd-form-control nd-small" 
                   min="0" 
                   max="50" 
                   value="0"
                   placeholder="0">
        </div>
        
        <div class="nd-option-row">
            <label class="nd-checkbox-label">
                <input type="checkbox" 
                       id="has_elevator_to" 
                       name="has_elevator_to" 
                       value="1">
                <span><?php _e('Ascenseur disponible', 'novalia-devis'); ?></span>
            </label>
        </div>
    </div>
    
    <!-- Résultat de la distance -->
    <div class="nd-distance-result" id="distance_result" style="display: none;">
        <div class="nd-result-card">
            <div class="nd-result-icon">🛣️</div>
            <div class="nd-result-content">
                <span class="nd-result-label"><?php _e('Distance calculée', 'novalia-devis'); ?></span>
                <span class="nd-result-value" id="distance_value">0 km</span>
            </div>
        </div>
    </div>
    
    <!-- Carte (optionnelle) -->
    <div class="nd-map-container" id="nd-map" style="display: none;">
        <div class="nd-map-wrapper">
            <div id="nd-leaflet-map"></div>
        </div>
        <button type="button" class="nd-map-toggle" id="nd-toggle-map">
            <span class="nd-show-map"><?php _e('📍 Afficher la carte', 'novalia-devis'); ?></span>
            <span class="nd-hide-map" style="display: none;"><?php _e('✕ Masquer la carte', 'novalia-devis'); ?></span>
        </button>
    </div>
    
    <!-- Champs cachés pour les coordonnées -->
    <input type="hidden" id="lat_from" name="lat_from">
    <input type="hidden" id="lon_from" name="lon_from">
    <input type="hidden" id="lat_to" name="lat_to">
    <input type="hidden" id="lon_to" name="lon_to">
    <input type="hidden" id="distance" name="distance">
    
</div>