<?php
/**
 * Template du tableau de bord
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-dashboard">
    <h1><?php _e('Tableau de bord - Novalia Devis', 'novalia-devis'); ?></h1>
    
    <!-- Statistiques principales -->
    <div class="nd-stats-grid">
        <div class="nd-stat-card">
            <div class="nd-stat-icon">
                <span class="dashicons dashicons-clipboard"></span>
            </div>
            <div class="nd-stat-content">
                <h3><?php echo number_format($stats['total_quotes'], 0, ',', ' '); ?></h3>
                <p><?php _e('Devis générés', 'novalia-devis'); ?></p>
            </div>
        </div>
        
        <div class="nd-stat-card">
            <div class="nd-stat-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="nd-stat-content">
                <h3><?php echo number_format($stats['current_month'], 0, ',', ' '); ?></h3>
                <p><?php _e('Ce mois-ci', 'novalia-devis'); ?></p>
            </div>
        </div>
        
        <div class="nd-stat-card">
            <div class="nd-stat-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="nd-stat-content">
                <h3><?php echo number_format($stats['total_amount'], 2, ',', ' '); ?> €</h3>
                <p><?php _e('Montant total', 'novalia-devis'); ?></p>
            </div>
        </div>
        
        <div class="nd-stat-card">
            <div class="nd-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="nd-stat-content">
                <h3><?php echo number_format($stats['average_amount'], 2, ',', ' '); ?> €</h3>
                <p><?php _e('Panier moyen', 'novalia-devis'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Statistiques secondaires -->
    <div class="nd-secondary-stats">
        <div class="nd-stat-box">
            <span class="dashicons dashicons-admin-multisite"></span>
            <strong><?php echo number_format($stats['total_distance'], 0, ',', ' '); ?> km</strong>
            <span><?php _e('Distance totale', 'novalia-devis'); ?></span>
        </div>
        
        <div class="nd-stat-box">
            <span class="dashicons dashicons-archive"></span>
            <strong><?php echo number_format($stats['total_volume'], 2, ',', ' '); ?> m³</strong>
            <span><?php _e('Volume total', 'novalia-devis'); ?></span>
        </div>
    </div>
    
    <!-- Graphique des statuts -->
    <div class="nd-charts-section">
        <div class="nd-chart-card">
            <h2><?php _e('Répartition des devis par statut', 'novalia-devis'); ?></h2>
            <canvas id="nd-status-chart"></canvas>
        </div>
    </div>
    
    <!-- Devis récents -->
    <div class="nd-recent-section">
        <div class="nd-section-header">
            <h2><?php _e('Devis récents', 'novalia-devis'); ?></h2>
            <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes'); ?>" class="button">
                <?php _e('Voir tous les devis', 'novalia-devis'); ?>
            </a>
        </div>
        
        <?php if (empty($recent_quotes)): ?>
            <div class="nd-empty-state">
                <span class="dashicons dashicons-clipboard"></span>
                <p><?php _e('Aucun devis généré pour le moment', 'novalia-devis'); ?></p>
                <p class="description">
                    <?php _e('Les devis générés par vos clients apparaîtront ici.', 'novalia-devis'); ?>
                </p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Numéro', 'novalia-devis'); ?></th>
                        <th><?php _e('Date', 'novalia-devis'); ?></th>
                        <th><?php _e('Client', 'novalia-devis'); ?></th>
                        <th><?php _e('Trajet', 'novalia-devis'); ?></th>
                        <th><?php _e('Distance', 'novalia-devis'); ?></th>
                        <th><?php _e('Volume', 'novalia-devis'); ?></th>
                        <th><?php _e('Montant', 'novalia-devis'); ?></th>
                        <th><?php _e('Statut', 'novalia-devis'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_quotes as $quote): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo admin_url('admin.php?page=novalia-devis-quotes&action=view&id=' . $quote['id']); ?>">
                                        <?php echo esc_html($quote['quote_number']); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo date_i18n('d/m/Y H:i', strtotime($quote['created_at'])); ?></td>
                            <td>
                                <?php echo esc_html($quote['customer_name']); ?>
                                <br>
                                <small><?php echo esc_html($quote['customer_email']); ?></small>
                            </td>
                            <td>
                                <small>
                                    <?php echo esc_html(substr($quote['address_from'], 0, 30)); ?>...
                                    <br>
                                    → <?php echo esc_html(substr($quote['address_to'], 0, 30)); ?>...
                                </small>
                            </td>
                            <td><?php echo number_format($quote['distance'], 0, ',', ' '); ?> km</td>
                            <td><?php echo number_format($quote['total_volume'], 2, ',', ' '); ?> m³</td>
                            <td><strong><?php echo number_format($quote['total_price'], 2, ',', ' '); ?> €</strong></td>
                            <td>
                                <?php
                                $status_labels = [
                                    'pending' => ['label' => __('En attente', 'novalia-devis'), 'color' => '#f0ad4e'],
                                    'accepted' => ['label' => __('Accepté', 'novalia-devis'), 'color' => '#5cb85c'],
                                    'rejected' => ['label' => __('Refusé', 'novalia-devis'), 'color' => '#d9534f'],
                                    'completed' => ['label' => __('Terminé', 'novalia-devis'), 'color' => '#0275d8'],
                                    'cancelled' => ['label' => __('Annulé', 'novalia-devis'), 'color' => '#6c757d']
                                ];
                                
                                $status = $quote['status'];
                                $status_info = $status_labels[$status] ?? ['label' => $status, 'color' => '#999'];
                                ?>
                                <span class="nd-badge" style="background-color: <?php echo $status_info['color']; ?>;">
                                    <?php echo $status_info['label']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Accès rapides -->
    <div class="nd-quick-actions">
        <h2><?php _e('Accès rapides', 'novalia-devis'); ?></h2>
        <div class="nd-actions-grid">
            <a href="<?php echo admin_url('admin.php?page=novalia-devis-items'); ?>" class="nd-action-card">
                <span class="dashicons dashicons-admin-page"></span>
                <strong><?php _e('Gérer les objets', 'novalia-devis'); ?></strong>
                <p><?php _e('Ajouter ou modifier les objets de déménagement', 'novalia-devis'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=novalia-devis-pricing'); ?>" class="nd-action-card">
                <span class="dashicons dashicons-tag"></span>
                <strong><?php _e('Tarification', 'novalia-devis'); ?></strong>
                <p><?php _e('Configurer les tarifs et les frais', 'novalia-devis'); ?></p>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=novalia-devis-settings'); ?>" class="nd-action-card">
                <span class="dashicons dashicons-admin-settings"></span>
                <strong><?php _e('Paramètres', 'novalia-devis'); ?></strong>
                <p><?php _e('Configurer l\'entreprise, emails et PDF', 'novalia-devis'); ?></p>
            </a>
            
            <a href="<?php echo home_url('/?page_id=YOUR_PAGE_ID'); ?>" class="nd-action-card" target="_blank">
                <span class="dashicons dashicons-visibility"></span>
                <strong><?php _e('Voir le formulaire', 'novalia-devis'); ?></strong>
                <p><?php _e('Tester le formulaire côté client', 'novalia-devis'); ?></p>
            </a>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Graphique des statuts
    <?php if (!empty($stats['by_status'])): ?>
    var ctx = document.getElementById('nd-status-chart').getContext('2d');
    
    var statusData = {
        labels: [
            <?php 
            $labels = [
                'pending' => 'En attente',
                'accepted' => 'Acceptés',
                'rejected' => 'Refusés',
                'completed' => 'Terminés',
                'cancelled' => 'Annulés'
            ];
            
            foreach ($stats['by_status'] as $status): 
                echo "'" . ($labels[$status['status']] ?? $status['status']) . "',";
            endforeach; 
            ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($stats['by_status'] as $status): ?>
                    <?php echo $status['count']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                '#f0ad4e',
                '#5cb85c',
                '#d9534f',
                '#0275d8',
                '#6c757d'
            ]
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>