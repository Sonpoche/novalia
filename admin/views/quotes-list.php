<?php
/**
 * Template de la liste des devis
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-quotes-page">
    <h1>
        <?php _e('Devis de déménagement', 'novalia-devis'); ?>
        <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes&action=export'); ?>" 
           class="button">
            <span class="dashicons dashicons-download"></span>
            <?php _e('Exporter (CSV)', 'novalia-devis'); ?>
        </a>
    </h1>
    
    <!-- Barre de recherche -->
    <div class="nd-search-bar">
        <form method="get" action="">
            <input type="hidden" name="page" value="novalia-devis-quotes">
            <input type="search" 
                   name="s" 
                   value="<?php echo esc_attr($search); ?>" 
                   placeholder="<?php _e('Rechercher par numéro, nom, email...', 'novalia-devis'); ?>">
            <button type="submit" class="button">
                <span class="dashicons dashicons-search"></span>
                <?php _e('Rechercher', 'novalia-devis'); ?>
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes'); ?>" 
                   class="button">
                    <?php _e('Réinitialiser', 'novalia-devis'); ?>
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Statistiques rapides -->
    <div class="nd-stats-inline">
        <div class="nd-stat-item">
            <strong><?php echo number_format($total_quotes, 0, ',', ' '); ?></strong>
            <span><?php _e('devis au total', 'novalia-devis'); ?></span>
        </div>
    </div>
    
    <?php if (empty($quotes)): ?>
        <div class="nd-empty-state">
            <span class="dashicons dashicons-clipboard"></span>
            <h2><?php _e('Aucun devis trouvé', 'novalia-devis'); ?></h2>
            <?php if (!empty($search)): ?>
                <p><?php _e('Aucun résultat pour votre recherche.', 'novalia-devis'); ?></p>
            <?php else: ?>
                <p><?php _e('Les devis générés par vos clients apparaîtront ici.', 'novalia-devis'); ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Tableau des devis -->
        <table id="nd-quotes-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 120px;"><?php _e('Numéro', 'novalia-devis'); ?></th>
                    <th style="width: 140px;"><?php _e('Date', 'novalia-devis'); ?></th>
                    <th><?php _e('Client', 'novalia-devis'); ?></th>
                    <th><?php _e('Contact', 'novalia-devis'); ?></th>
                    <th><?php _e('Trajet', 'novalia-devis'); ?></th>
                    <th style="width: 80px;"><?php _e('Distance', 'novalia-devis'); ?></th>
                    <th style="width: 80px;"><?php _e('Volume', 'novalia-devis'); ?></th>
                    <th style="width: 100px;"><?php _e('Montant', 'novalia-devis'); ?></th>
                    <th style="width: 100px;"><?php _e('Statut', 'novalia-devis'); ?></th>
                    <th style="width: 200px;"><?php _e('Actions', 'novalia-devis'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr data-quote-id="<?php echo $quote['id']; ?>">
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes&action=view&id=' . $quote['id']); ?>">
                                    <?php echo esc_html($quote['quote_number']); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php echo date_i18n('d/m/Y', strtotime($quote['created_at'])); ?>
                            <br>
                            <small class="nd-text-muted">
                                <?php echo date_i18n('H:i', strtotime($quote['created_at'])); ?>
                            </small>
                        </td>
                        <td>
                            <strong>
                                <?php echo esc_html($quote['customer_firstname'] . ' ' . $quote['customer_name']); ?>
                            </strong>
                        </td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>">
                                <?php echo esc_html($quote['customer_email']); ?>
                            </a>
                            <?php if (!empty($quote['customer_phone'])): ?>
                                <br>
                                <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>">
                                    <?php echo esc_html($quote['customer_phone']); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                <strong><?php _e('Départ:', 'novalia-devis'); ?></strong>
                                <?php echo esc_html(mb_substr($quote['address_from'], 0, 40)); ?>...
                                <br>
                                <strong><?php _e('Arrivée:', 'novalia-devis'); ?></strong>
                                <?php echo esc_html(mb_substr($quote['address_to'], 0, 40)); ?>...
                            </small>
                        </td>
                        <td>
                            <strong><?php echo number_format($quote['distance'], 0, ',', ' '); ?></strong> km
                        </td>
                        <td>
                            <strong><?php echo number_format($quote['total_volume'], 2, ',', ' '); ?></strong> m³
                        </td>
                        <td>
                            <strong class="nd-price">
                                <?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €
                            </strong>
                        </td>
                        <td>
                            <?php
                            $status_config = [
                                'pending' => ['label' => __('En attente', 'novalia-devis'), 'class' => 'warning'],
                                'accepted' => ['label' => __('Accepté', 'novalia-devis'), 'class' => 'success'],
                                'rejected' => ['label' => __('Refusé', 'novalia-devis'), 'class' => 'danger'],
                                'completed' => ['label' => __('Terminé', 'novalia-devis'), 'class' => 'info'],
                                'cancelled' => ['label' => __('Annulé', 'novalia-devis'), 'class' => 'secondary']
                            ];
                            
                            $status = $quote['status'];
                            $status_info = $status_config[$status] ?? ['label' => $status, 'class' => 'secondary'];
                            ?>
                            <select class="nd-status-select" data-quote-id="<?php echo $quote['id']; ?>">
                                <?php foreach ($status_config as $key => $info): ?>
                                    <option value="<?php echo $key; ?>" 
                                            <?php selected($status, $key); ?>>
                                        <?php echo $info['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="nd-action-buttons">
                                <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes&action=view&id=' . $quote['id']); ?>" 
                                   class="button button-small" 
                                   title="<?php _e('Voir le détail', 'novalia-devis'); ?>">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                
                                <?php if (!empty($quote['pdf_path'])): ?>
                                    <?php
                                    $upload_dir = wp_upload_dir();
                                    $pdf_url = $upload_dir['baseurl'] . $quote['pdf_path'];
                                    ?>
                                    <a href="<?php echo esc_url($pdf_url); ?>" 
                                       class="button button-small" 
                                       target="_blank"
                                       title="<?php _e('Télécharger le PDF', 'novalia-devis'); ?>">
                                        <span class="dashicons dashicons-pdf"></span>
                                    </a>
                                <?php endif; ?>
                                
                                <button class="button button-small nd-resend-email" 
                                        data-quote-id="<?php echo $quote['id']; ?>"
                                        title="<?php _e('Renvoyer l\'email', 'novalia-devis'); ?>">
                                    <span class="dashicons dashicons-email"></span>
                                </button>
                                
                                <button class="button button-small button-link-delete nd-delete-quote" 
                                        data-quote-id="<?php echo $quote['id']; ?>"
                                        title="<?php _e('Supprimer', 'novalia-devis'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // DataTable
    var table = $('#nd-quotes-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        order: [[1, 'desc']], // Tri par date décroissante
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [9] } // Actions non triables
        ]
    });
    
    // Changement de statut
    $(document).on('change', '.nd-status-select', function() {
        var select = $(this);
        var quoteId = select.data('quote-id');
        var newStatus = select.val();
        
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
                    // Notification visuelle
                    select.addClass('nd-flash-success');
                    setTimeout(function() {
                        select.removeClass('nd-flash-success');
                    }, 1000);
                } else {
                    alert(response.data.message);
                    location.reload();
                }
            },
            error: function() {
                alert('<?php _e('Erreur lors de la mise à jour du statut', 'novalia-devis'); ?>');
                location.reload();
            }
        });
    });
    
    // Renvoyer l'email
    $(document).on('click', '.nd-resend-email', function() {
        var btn = $(this);
        var quoteId = btn.data('quote-id');
        
        if (!confirm('<?php _e('Renvoyer l\'email au client ?', 'novalia-devis'); ?>')) {
            return;
        }
        
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
            },
            error: function() {
                alert('<?php _e('Erreur lors de l\'envoi', 'novalia-devis'); ?>');
                btn.prop('disabled', false);
            }
        });
    });
    
    // Supprimer un devis
    $(document).on('click', '.nd-delete-quote', function() {
        if (!confirm('<?php _e('Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.', 'novalia-devis'); ?>')) {
            return;
        }
        
        var quoteId = $(this).data('quote-id');
        var row = $(this).closest('tr');
        
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
                    table.row(row).remove().draw();
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php _e('Erreur lors de la suppression', 'novalia-devis'); ?>');
            }
        });
    });
});
</script>