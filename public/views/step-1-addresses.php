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
    
    <h3 class="nd-step-title">
        <?php _e('Adresses de départ et d\'arrivée', 'novalia-devis'); ?>
    </h3>
    <p class="nd-step-description">
        <?php _e('Saisissez vos adresses pour calculer la distance', 'novalia-devis'); ?>
    </p>
    
    <!-- Adresse de départ -->
    <div class="nd-form-group">
        <label for="address_from" class="nd-form-label required">
            <?php _e('Adresse de départ', 'novalia-devis'); ?>
        </label>
        <div class="nd-input-wrapper">
            <input type="text" 
                   id="address_from" 
                   name="address_from" 
                   class="nd-input nd-address-autocomplete" 
                   placeholder="<?php _e('Ex: 123 Rue de la République, Paris', 'novalia-devis'); ?>"
                   autocomplete="off"
                   required>
            <div class="nd-autocomplete-results" id="autocomplete_from"></div>
        </div>
    </div>
    
    <!-- Options départ -->
    <div class="nd-form-row" style="display: flex; gap: 24px; align-items: flex-end;">
        <div class="nd-form-group" style="flex: 0 0 120px;">
            <label for="floors_from" class="nd-form-label">
                <?php _e('Étage', 'novalia-devis'); ?>
            </label>
            <input type="number" 
                   id="floors_from" 
                   name="floors_from" 
                   class="nd-input" 
                   min="0" 
                   max="50" 
                   value="0">
        </div>
        
        <div class="nd-form-group" style="flex: 1;">
            <label class="nd-checkbox-wrapper" style="margin-top: 8px;">
                <div class="nd-checkbox">
                    <input type="checkbox" 
                           id="has_elevator_from" 
                           name="has_elevator_from" 
                           value="1">
                    <div class="nd-checkbox-box"></div>
                </div>
                <span class="nd-checkbox-label"><?php _e('Ascenseur disponible', 'novalia-devis'); ?></span>
            </label>
        </div>
    </div>
    
    <!-- Adresse d'arrivée -->
    <div class="nd-form-group">
        <label for="address_to" class="nd-form-label required">
            <?php _e('Adresse d\'arrivée', 'novalia-devis'); ?>
        </label>
        <div class="nd-input-wrapper">
            <input type="text" 
                   id="address_to" 
                   name="address_to" 
                   class="nd-input nd-address-autocomplete" 
                   placeholder="<?php _e('Ex: 45 Avenue des Champs, Lyon', 'novalia-devis'); ?>"
                   autocomplete="off"
                   required>
            <div class="nd-autocomplete-results" id="autocomplete_to"></div>
        </div>
    </div>
    
    <!-- Options arrivée -->
    <div class="nd-form-row" style="display: flex; gap: 24px; align-items: flex-end;">
        <div class="nd-form-group" style="flex: 0 0 120px;">
            <label for="floors_to" class="nd-form-label">
                <?php _e('Étage', 'novalia-devis'); ?>
            </label>
            <input type="number" 
                   id="floors_to" 
                   name="floors_to" 
                   class="nd-input" 
                   min="0" 
                   max="50" 
                   value="0">
        </div>
        
        <div class="nd-form-group" style="flex: 1;">
            <label class="nd-checkbox-wrapper" style="margin-top: 8px;">
                <div class="nd-checkbox">
                    <input type="checkbox" 
                           id="has_elevator_to" 
                           name="has_elevator_to" 
                           value="1">
                    <div class="nd-checkbox-box"></div>
                </div>
                <span class="nd-checkbox-label"><?php _e('Ascenseur disponible', 'novalia-devis'); ?></span>
            </label>
        </div>
    </div>
    
    <!-- Résultat distance -->
    <div class="nd-distance-result" id="distance_result" style="display: none; margin-bottom: 24px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span><?php _e('Distance calculée', 'novalia-devis'); ?> <strong id="distance_value">0 km</strong></span>
    </div>
    
    <!-- Carte -->
    <div id="nd-map" style="display: none; margin-top: 24px;">
        <button type="button" class="nd-toggle-map" id="nd-toggle-map" style="font-size: 0.9375rem; padding: 10px 20px;">
            <span class="nd-show-map">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <?php _e('Afficher la carte', 'novalia-devis'); ?>
            </span>
            <span class="nd-hide-map" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <?php _e('Masquer la carte', 'novalia-devis'); ?>
            </span>
        </button>
        <div class="nd-map-wrapper" style="display: none;">
            <div id="nd-leaflet-map"></div>
        </div>
    </div>
    
    <!-- Champs cachés -->
    <input type="hidden" id="lat_from" name="lat_from">
    <input type="hidden" id="lon_from" name="lon_from">
    <input type="hidden" id="lat_to" name="lat_to">
    <input type="hidden" id="lon_to" name="lon_to">
    <input type="hidden" id="distance" name="distance">
    
</div>
