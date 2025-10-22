<?php
/**
 * Template des paramètres de tarification
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-pricing-page">
    <h1><?php _e('Tarification', 'novalia-devis'); ?></h1>
    
    <?php settings_errors('nd_pricing'); ?>
    
    <div class="nd-pricing-container">
        <!-- Formulaire de tarification -->
        <div class="nd-pricing-form">
            <form method="post" action="options.php">
                <?php
                settings_fields('nd_pricing_group');
                ?>
                
                <div class="nd-card">
                    <h2>
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php _e('Tarifs de base', 'novalia-devis'); ?>
                    </h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="price_per_km">
                                    <?php _e('Prix au kilomètre', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="price_per_km" 
                                           name="nd_pricing[price_per_km]" 
                                           value="<?php echo esc_attr($pricing['price_per_km']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€ / km</span>
                                </div>
                                <p class="description">
                                    <?php _e('Prix facturé par kilomètre parcouru', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="price_per_m3">
                                    <?php _e('Prix au mètre cube', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="price_per_m3" 
                                           name="nd_pricing[price_per_m3]" 
                                           value="<?php echo esc_attr($pricing['price_per_m3']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€ / m³</span>
                                </div>
                                <p class="description">
                                    <?php _e('Prix facturé par mètre cube de marchandise', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="fixed_fee">
                                    <?php _e('Frais fixes', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="fixed_fee" 
                                           name="nd_pricing[fixed_fee]" 
                                           value="<?php echo esc_attr($pricing['fixed_fee']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€</span>
                                </div>
                                <p class="description">
                                    <?php _e('Frais fixes ajoutés à chaque devis (0 pour désactiver)', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="min_quote_amount">
                                    <?php _e('Montant minimum', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="min_quote_amount" 
                                           name="nd_pricing[min_quote_amount]" 
                                           value="<?php echo esc_attr($pricing['min_quote_amount']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€</span>
                                </div>
                                <p class="description">
                                    <?php _e('Montant minimum d\'un devis (le prix ne descendra jamais en dessous)', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="nd-card">
                    <h2>
                        <span class="dashicons dashicons-building"></span>
                        <?php _e('Frais additionnels', 'novalia-devis'); ?>
                    </h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="fee_floor">
                                    <?php _e('Frais par étage (sans ascenseur)', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="fee_floor" 
                                           name="nd_pricing[fee_floor]" 
                                           value="<?php echo esc_attr($pricing['fee_floor']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€ / étage</span>
                                </div>
                                <p class="description">
                                    <?php _e('Supplément par étage à monter/descendre sans ascenseur', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="fee_elevator">
                                    <?php _e('Frais ascenseur', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="fee_elevator" 
                                           name="nd_pricing[fee_elevator]" 
                                           value="<?php echo esc_attr($pricing['fee_elevator']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€</span>
                                </div>
                                <p class="description">
                                    <?php _e('Frais fixes si ascenseur disponible (0 pour gratuit)', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="fee_packing">
                                    <?php _e('Service d\'emballage', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="fee_packing" 
                                           name="nd_pricing[fee_packing]" 
                                           value="<?php echo esc_attr($pricing['fee_packing']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€ / m³</span>
                                </div>
                                <p class="description">
                                    <?php _e('Prix du service d\'emballage par mètre cube', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="fee_insurance">
                                    <?php _e('Assurance', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="nd-input-group">
                                    <input type="number" 
                                           id="fee_insurance" 
                                           name="nd_pricing[fee_insurance]" 
                                           value="<?php echo esc_attr($pricing['fee_insurance']); ?>" 
                                           step="0.01" 
                                           min="0"
                                           class="regular-text">
                                    <span class="nd-input-suffix">€ / m³</span>
                                </div>
                                <p class="description">
                                    <?php _e('Prix de l\'assurance tous risques par mètre cube', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(__('Enregistrer les modifications', 'novalia-devis'), 'primary large'); ?>
            </form>
        </div>
        
        <!-- Simulateur en temps réel -->
        <div class="nd-pricing-simulator">
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-calculator"></span>
                    <?php _e('Simulateur de prix', 'novalia-devis'); ?>
                </h2>
                
                <p class="description">
                    <?php _e('Testez vos tarifs en temps réel', 'novalia-devis'); ?>
                </p>
                
                <div class="nd-simulator-inputs">
                    <div class="nd-input-row">
                        <label><?php _e('Distance (km)', 'novalia-devis'); ?></label>
                        <input type="number" id="sim_distance" value="50" min="1" step="1">
                    </div>
                    
                    <div class="nd-input-row">
                        <label><?php _e('Volume (m³)', 'novalia-devis'); ?></label>
                        <input type="number" id="sim_volume" value="10" min="0.1" step="0.1">
                    </div>
                    
                    <div class="nd-input-row">
                        <label><?php _e('Étages départ', 'novalia-devis'); ?></label>
                        <input type="number" id="sim_floors_from" value="0" min="0" step="1">
                    </div>
                    
                    <div class="nd-input-row">
                        <label><?php _e('Étages arrivée', 'novalia-devis'); ?></label>
                        <input type="number" id="sim_floors_to" value="0" min="0" step="1">
                    </div>
                    
                    <div class="nd-input-row">
                        <label>
                            <input type="checkbox" id="sim_packing">
                            <?php _e('Service d\'emballage', 'novalia-devis'); ?>
                        </label>
                    </div>
                    
                    <div class="nd-input-row">
                        <label>
                            <input type="checkbox" id="sim_insurance">
                            <?php _e('Assurance', 'novalia-devis'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="nd-simulator-result">
                    <div class="nd-result-breakdown">
                        <div class="nd-result-line">
                            <span><?php _e('Distance', 'novalia-devis'); ?></span>
                            <span id="result_distance">0.00 €</span>
                        </div>
                        <div class="nd-result-line">
                            <span><?php _e('Volume', 'novalia-devis'); ?></span>
                            <span id="result_volume">0.00 €</span>
                        </div>
                        <div class="nd-result-line" id="result_floors_line" style="display: none;">
                            <span><?php _e('Étages', 'novalia-devis'); ?></span>
                            <span id="result_floors">0.00 €</span>
                        </div>
                        <div class="nd-result-line" id="result_packing_line" style="display: none;">
                            <span><?php _e('Emballage', 'novalia-devis'); ?></span>
                            <span id="result_packing">0.00 €</span>
                        </div>
                        <div class="nd-result-line" id="result_insurance_line" style="display: none;">
                            <span><?php _e('Assurance', 'novalia-devis'); ?></span>
                            <span id="result_insurance">0.00 €</span>
                        </div>
                        <div class="nd-result-line" id="result_fixed_line">
                            <span><?php _e('Frais fixes', 'novalia-devis'); ?></span>
                            <span id="result_fixed">0.00 €</span>
                        </div>
                    </div>
                    
                    <div class="nd-result-total">
                        <strong><?php _e('TOTAL', 'novalia-devis'); ?></strong>
                        <strong id="result_total">0.00 €</strong>
                    </div>
                </div>
            </div>
            
            <div class="nd-card nd-pricing-tips">
                <h3><?php _e('💡 Conseils de tarification', 'novalia-devis'); ?></h3>
                <ul>
                    <li><?php _e('Le prix au km couvre le carburant et l\'usure du véhicule', 'novalia-devis'); ?></li>
                    <li><?php _e('Le prix au m³ couvre la main d\'œuvre et le temps', 'novalia-devis'); ?></li>
                    <li><?php _e('Les frais d\'étage compensent l\'effort supplémentaire', 'novalia-devis'); ?></li>
                    <li><?php _e('Définissez un montant minimum pour couvrir vos frais fixes', 'novalia-devis'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Récupération des tarifs
    var pricing = {
        price_per_km: parseFloat($('#price_per_km').val()) || 0,
        price_per_m3: parseFloat($('#price_per_m3').val()) || 0,
        fixed_fee: parseFloat($('#fixed_fee').val()) || 0,
        fee_floor: parseFloat($('#fee_floor').val()) || 0,
        fee_packing: parseFloat($('#fee_packing').val()) || 0,
        fee_insurance: parseFloat($('#fee_insurance').val()) || 0,
        min_quote_amount: parseFloat($('#min_quote_amount').val()) || 0
    };
    
    // Mise à jour des tarifs en temps réel
    $('input[name^="nd_pricing"]').on('input', function() {
        var field = $(this).attr('id');
        pricing[field] = parseFloat($(this).val()) || 0;
        calculateSimulation();
    });
    
    // Mise à jour du simulateur
    $('#sim_distance, #sim_volume, #sim_floors_from, #sim_floors_to, #sim_packing, #sim_insurance').on('input change', function() {
        calculateSimulation();
    });
    
    // Fonction de calcul
    function calculateSimulation() {
        var distance = parseFloat($('#sim_distance').val()) || 0;
        var volume = parseFloat($('#sim_volume').val()) || 0;
        var floorsFrom = parseInt($('#sim_floors_from').val()) || 0;
        var floorsTo = parseInt($('#sim_floors_to').val()) || 0;
        var packing = $('#sim_packing').is(':checked');
        var insurance = $('#sim_insurance').is(':checked');
        
        // Calculs
        var priceDistance = distance * pricing.price_per_km;
        var priceVolume = volume * pricing.price_per_m3;
        var priceFloors = (floorsFrom + floorsTo) * pricing.fee_floor;
        var pricePacking = packing ? volume * pricing.fee_packing : 0;
        var priceInsurance = insurance ? volume * pricing.fee_insurance : 0;
        var priceFixed = pricing.fixed_fee;
        
        var subtotal = priceDistance + priceVolume + priceFloors + pricePacking + priceInsurance + priceFixed;
        var total = Math.max(subtotal, pricing.min_quote_amount);
        
        // Affichage
        $('#result_distance').text(priceDistance.toFixed(2) + ' €');
        $('#result_volume').text(priceVolume.toFixed(2) + ' €');
        $('#result_floors').text(priceFloors.toFixed(2) + ' €');
        $('#result_packing').text(pricePacking.toFixed(2) + ' €');
        $('#result_insurance').text(priceInsurance.toFixed(2) + ' €');
        $('#result_fixed').text(priceFixed.toFixed(2) + ' €');
        $('#result_total').text(total.toFixed(2) + ' €');
        
        // Affichage conditionnel
        $('#result_floors_line').toggle(priceFloors > 0);
        $('#result_packing_line').toggle(packing);
        $('#result_insurance_line').toggle(insurance);
        $('#result_fixed_line').toggle(priceFixed > 0);
    }
    
    // Calcul initial
    calculateSimulation();
});
</script>