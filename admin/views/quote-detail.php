<?php
/**
 * Template du détail d'un devis
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-quote-detail">
    <h1>
        <?php printf(__('Devis %s', 'novalia-devis'), esc_html($quote['quote_number'])); ?>
        <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes'); ?>" class="button">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php _e('Retour à la liste', 'novalia-devis'); ?>
        </a>
    </h1>
    
    <!-- Actions rapides -->
    <div class="nd-detail-actions">
        <?php if (!empty($quote['pdf_path'])): ?>
            <?php
            $upload_dir = wp_upload_dir();
            $pdf_url = $upload_dir['baseurl'] . $quote['pdf_path'];
            ?>
            <a href="<?php echo esc_url($pdf_url); ?>" 
               class="button button-primary" 
               target="_blank">
                <span class="dashicons dashicons-pdf"></span>
                <?php _e('Télécharger le PDF', 'novalia-devis'); ?>
            </a>
        <?php endif; ?>
        
        <button class="button" id="nd-resend-email-btn">
            <span class="dashicons dashicons-email"></span>
            <?php _e('Renvoyer l\'email', 'novalia-devis'); ?>
        </button>
        
        <button class="button" id="nd-regenerate-pdf-btn">
            <span class="dashicons dashicons-update"></span>
            <?php _e('Régénérer le PDF', 'novalia-devis'); ?>
        </button>
        
        <button class="button button-link-delete" id="nd-delete-quote-btn">
            <span class="dashicons dashicons-trash"></span>
            <?php _e('Supprimer', 'novalia-devis'); ?>
        </button>
    </div>
    
    <div class="nd-detail-container">
        <!-- Colonne de gauche -->
        <div class="nd-detail-main">
            
            <!-- Informations générales -->
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Informations générales', 'novalia-devis'); ?>
                </h2>
                
                <table class="nd-info-table">
                    <tr>
                        <th><?php _e('Numéro de devis', 'novalia-devis'); ?></th>
                        <td><strong><?php echo esc_html($quote['quote_number']); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php _e('Date de création', 'novalia-devis'); ?></th>
                        <td><?php echo date_i18n('d/m/Y à H:i', strtotime($quote['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Statut', 'novalia-devis'); ?></th>
                        <td>
                            <select id="nd-quote-status" class="nd-status-select-large">
                                <option value="pending" <?php selected($quote['status'], 'pending'); ?>>
                                    <?php _e('En attente', 'novalia-devis'); ?>
                                </option>
                                <option value="accepted" <?php selected($quote['status'], 'accepted'); ?>>
                                    <?php _e('Accepté', 'novalia-devis'); ?>
                                </option>
                                <option value="rejected" <?php selected($quote['status'], 'rejected'); ?>>
                                    <?php _e('Refusé', 'novalia-devis'); ?>
                                </option>
                                <option value="completed" <?php selected($quote['status'], 'completed'); ?>>
                                    <?php _e('Terminé', 'novalia-devis'); ?>
                                </option>
                                <option value="cancelled" <?php selected($quote['status'], 'cancelled'); ?>>
                                    <?php _e('Annulé', 'novalia-devis'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Informations client -->
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-admin-users"></span>
                    <?php _e('Informations client', 'novalia-devis'); ?>
                </h2>
                
                <table class="nd-info-table">
                    <tr>
                        <th><?php _e('Nom complet', 'novalia-devis'); ?></th>
                        <td>
                            <strong>
                                <?php echo esc_html($quote['customer_firstname'] . ' ' . $quote['customer_name']); ?>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Email', 'novalia-devis'); ?></th>
                        <td>
                            <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>">
                                <?php echo esc_html($quote['customer_email']); ?>
                            </a>
                        </td>
                    </tr>
                    <?php if (!empty($quote['customer_phone'])): ?>
                    <tr>
                        <th><?php _e('Téléphone', 'novalia-devis'); ?></th>
                        <td>
                            <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>">
                                <?php echo esc_html($quote['customer_phone']); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Trajet -->
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-location"></span>
                    <?php _e('Itinéraire du déménagement', 'novalia-devis'); ?>
                </h2>
                
                <div class="nd-route-display">
                    <div class="nd-route-point nd-route-from">
                        <span class="nd-route-icon">📍</span>
                        <div>
                            <strong><?php _e('Adresse de départ', 'novalia-devis'); ?></strong>
                            <p><?php echo esc_html($quote['address_from']); ?></p>
                        </div>
                    </div>
                    
                    <div class="nd-route-arrow">
                        <span class="dashicons dashicons-arrow-down-alt"></span>
                        <span class="nd-distance-badge">
                            <?php echo number_format($quote['distance'], 2, ',', ' '); ?> km
                        </span>
                    </div>
                    
                    <div class="nd-route-point nd-route-to">
                        <span class="nd-route-icon">📍</span>
                        <div>
                            <strong><?php _e('Adresse d\'arrivée', 'novalia-devis'); ?></strong>
                            <p><?php echo esc_html($quote['address_to']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Objets à déménager -->
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-archive"></span>
                    <?php _e('Objets à déménager', 'novalia-devis'); ?>
                </h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Objet', 'novalia-devis'); ?></th>
                            <th style="text-align: center;"><?php _e('Quantité', 'novalia-devis'); ?></th>
                            <th style="text-align: right;"><?php _e('Volume unitaire', 'novalia-devis'); ?></th>
                            <th style="text-align: right;"><?php _e('Volume total', 'novalia-devis'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_volume = 0;
                        foreach ($quote['items'] as $item): 
                            $item_total = $item['item_volume'] * $item['quantity'];
                            $total_volume += $item_total;
                        ?>
                            <tr>
                                <td><?php echo esc_html($item['item_name']); ?></td>
                                <td style="text-align: center;">
                                    <strong><?php echo intval($item['quantity']); ?></strong>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo number_format($item['item_volume'], 3, ',', ' '); ?> m³
                                </td>
                                <td style="text-align: right;">
                                    <strong><?php echo number_format($item_total, 3, ',', ' '); ?> m³</strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="nd-total-row">
                            <td colspan="3" style="text-align: right;">
                                <strong><?php _e('VOLUME TOTAL', 'novalia-devis'); ?></strong>
                            </td>
                            <td style="text-align: right;">
                                <strong class="nd-price">
                                    <?php echo number_format($total_volume, 3, ',', ' '); ?> m³
                                </strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Détail du prix -->
            <div class="nd-card">
                <h2>
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php _e('Détail du prix', 'novalia-devis'); ?>
                </h2>
                
                <table class="nd-pricing-table">
                    <tr>
                        <td><?php _e('Transport', 'novalia-devis'); ?></td>
                        <td>
                            <?php echo number_format($quote['distance'], 2, ',', ' '); ?> km 
                            × 
                            <?php echo number_format($calculation['breakdown']['distance']['rate'], 2, ',', ' '); ?> €/km
                        </td>
                        <td class="nd-price-cell">
                            <?php echo number_format($calculation['breakdown']['distance']['price'], 2, ',', ' '); ?> €
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Manutention', 'novalia-devis'); ?></td>
                        <td>
                            <?php echo number_format($total_volume, 3, ',', ' '); ?> m³ 
                            × 
                            <?php echo number_format($calculation['breakdown']['volume']['rate'], 2, ',', ' '); ?> €/m³
                        </td>
                        <td class="nd-price-cell">
                            <?php echo number_format($calculation['breakdown']['volume']['price'], 2, ',', ' '); ?> €
                        </td>
                    </tr>
                    <?php if ($calculation['breakdown']['fixed_fee'] > 0): ?>
                    <tr>
                        <td><?php _e('Frais fixes', 'novalia-devis'); ?></td>
                        <td>-</td>
                        <td class="nd-price-cell">
                            <?php echo number_format($calculation['breakdown']['fixed_fee'], 2, ',', ' '); ?> €
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr class="nd-total-row">
                        <td colspan="2">
                            <strong><?php _e('MONTANT TOTAL', 'novalia-devis'); ?></strong>
                        </td>
                        <td class="nd-price-cell">
                            <strong class="nd-price-large">
                                <?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €
                            </strong>
                        </td>
                    </tr>
                </table>
            </div>
            
        </div>
        
        <!-- Sidebar -->
        <div class="nd-detail-sidebar">
            
            <!-- Récapitulatif -->
            <div class="nd-card nd-summary-card">
                <h3><?php _e('Récapitulatif', 'novalia-devis'); ?></h3>
                
                <div class="nd-summary-item">
                    <span class="dashicons dashicons-location-alt"></span>
                    <div>
                        <strong><?php echo number_format($quote['distance'], 0, ',', ' '); ?> km</strong>
                        <small><?php _e('Distance', 'novalia-devis'); ?></small>
                    </div>
                </div>
                
                <div class="nd-summary-item">
                    <span class="dashicons dashicons-archive"></span>
                    <div>
                        <strong><?php echo number_format($quote['total_volume'], 2, ',', ' '); ?> m³</strong>
                        <small><?php _e('Volume total', 'novalia-devis'); ?></small>
                    </div>
                </div>
                
                <div class="nd-summary-item">
                    <span class="dashicons dashicons-admin-page"></span>
                    <div>
                        <strong><?php echo count($quote['items']); ?></strong>
                        <small><?php _e('Objets', 'novalia-devis'); ?></small>
                    </div>
                </div>
                
                <div class="nd-summary-total">
                    <span><?php _e('Montant total', 'novalia-devis'); ?></span>
                    <strong><?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €</strong>
                </div>
            </div>
            
            <!-- Notes -->
            <div class="nd-card">
                <h3><?php _e('Notes', 'novalia-devis'); ?></h3>
                <textarea id="nd-quote-notes" 
                          rows="5" 
                          placeholder="<?php _e('Ajouter des notes sur ce devis...', 'novalia-devis'); ?>"
                          class="widefat"><?php echo esc_textarea($quote['notes'] ?? ''); ?></textarea>
                <button class="button button-small" id="nd-save-notes" style="margin-top: 10px;">
                    <?php _e('Enregistrer', 'novalia-devis'); ?>
                </button>
            </div>
            
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var quoteId = <?php echo intval($quote['id']); ?>;
    
    // Changement de statut
    $('#nd-quote-status').on('change', function() {
        var newStatus = $(this).val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_update_quote_status',
                quote_id: quoteId,
                status: newStatus,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Statut mis à jour', 'novalia-devis'); ?>');
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Renvoyer l'email
    $('#nd-resend-email-btn').on('click', function() {
        if (!confirm('<?php _e('Renvoyer l\'email au client ?', 'novalia-devis'); ?>')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_resend_quote_email',
                quote_id: quoteId,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Email renvoyé avec succès', 'novalia-devis'); ?>');
                } else {
                    alert(response.data.message);
                }
                btn.prop('disabled', false);
            }
        });
    });
    
    // Régénérer le PDF
    $('#nd-regenerate-pdf-btn').on('click', function() {
        if (!confirm('<?php _e('Régénérer le PDF ?', 'novalia-devis'); ?>')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).text('<?php _e('Génération...', 'novalia-devis'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_regenerate_pdf',
                quote_id: quoteId,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('PDF régénéré avec succès', 'novalia-devis'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message);
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php _e('Régénérer le PDF', 'novalia-devis'); ?>');
                }
            }
        });
    });
    
    // Supprimer le devis
    $('#nd-delete-quote-btn').on('click', function() {
        if (!confirm('<?php _e('Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.', 'novalia-devis'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_delete_quote',
                quote_id: quoteId,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=novalia-devis-quotes'); ?>';
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>