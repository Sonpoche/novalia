<?php
/**
 * Template de la liste des objets
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-items-page">
    <h1>
        <?php _e('Objets de déménagement', 'novalia-devis'); ?>
        <button class="button button-primary" id="nd-add-item-btn">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php _e('Ajouter un objet', 'novalia-devis'); ?>
        </button>
    </h1>
    
    <?php settings_errors('nd_items'); ?>
    
    <!-- Actions groupées -->
    <div class="nd-actions-bar">
        <div class="nd-actions-left">
            <button class="button" id="nd-export-csv">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Exporter en CSV', 'novalia-devis'); ?>
            </button>
            
            <button class="button" id="nd-import-csv-btn">
                <span class="dashicons dashicons-upload"></span>
                <?php _e('Importer depuis CSV', 'novalia-devis'); ?>
            </button>
        </div>
        
        <div class="nd-actions-right">
            <select id="nd-filter-category">
                <option value=""><?php _e('Toutes les catégories', 'novalia-devis'); ?></option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category); ?>">
                        <?php echo esc_html($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select id="nd-filter-status">
                <option value=""><?php _e('Tous les statuts', 'novalia-devis'); ?></option>
                <option value="1"><?php _e('Actifs', 'novalia-devis'); ?></option>
                <option value="0"><?php _e('Inactifs', 'novalia-devis'); ?></option>
            </select>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="nd-items-stats">
        <div class="nd-stat-box">
            <span class="dashicons dashicons-admin-page"></span>
            <strong><?php echo count($items); ?></strong>
            <span><?php _e('Objets au total', 'novalia-devis'); ?></span>
        </div>
        
        <div class="nd-stat-box">
            <span class="dashicons dashicons-category"></span>
            <strong><?php echo count($categories); ?></strong>
            <span><?php _e('Catégories', 'novalia-devis'); ?></span>
        </div>
    </div>
    
    <!-- Tableau des objets -->
    <table id="nd-items-table" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 40px;"><?php _e('ID', 'novalia-devis'); ?></th>
                <th><?php _e('Nom', 'novalia-devis'); ?></th>
                <th><?php _e('Catégorie', 'novalia-devis'); ?></th>
                <th><?php _e('Volume (m³)', 'novalia-devis'); ?></th>
                <th><?php _e('Statut', 'novalia-devis'); ?></th>
                <th><?php _e('Créé le', 'novalia-devis'); ?></th>
                <th style="width: 150px;"><?php _e('Actions', 'novalia-devis'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr data-item-id="<?php echo $item['id']; ?>">
                    <td><?php echo $item['id']; ?></td>
                    <td>
                        <strong><?php echo esc_html($item['name']); ?></strong>
                    </td>
                    <td>
                        <span class="nd-category-badge">
                            <?php echo esc_html($item['category']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($item['volume'], 3, ',', ' '); ?></td>
                    <td>
                        <?php if ($item['is_active']): ?>
                            <span class="nd-badge nd-badge-success">
                                <?php _e('Actif', 'novalia-devis'); ?>
                            </span>
                        <?php else: ?>
                            <span class="nd-badge nd-badge-secondary">
                                <?php _e('Inactif', 'novalia-devis'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date_i18n('d/m/Y', strtotime($item['created_at'])); ?></td>
                    <td>
                        <button class="button button-small nd-edit-item" 
                                data-id="<?php echo $item['id']; ?>"
                                data-name="<?php echo esc_attr($item['name']); ?>"
                                data-volume="<?php echo $item['volume']; ?>"
                                data-category="<?php echo esc_attr($item['category']); ?>"
                                data-active="<?php echo $item['is_active']; ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        
                        <button class="button button-small nd-toggle-item" 
                                data-id="<?php echo $item['id']; ?>">
                            <?php if ($item['is_active']): ?>
                                <span class="dashicons dashicons-hidden"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-visibility"></span>
                            <?php endif; ?>
                        </button>
                        
                        <button class="button button-small button-link-delete nd-delete-item" 
                                data-id="<?php echo $item['id']; ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal : Ajouter/Modifier un objet -->
<div id="nd-item-modal" class="nd-modal" style="display: none;">
    <div class="nd-modal-content">
        <div class="nd-modal-header">
            <h2 id="nd-modal-title"><?php _e('Ajouter un objet', 'novalia-devis'); ?></h2>
            <button class="nd-modal-close">&times;</button>
        </div>
        
        <div class="nd-modal-body">
            <form id="nd-item-form">
                <input type="hidden" id="item_id" name="item_id" value="">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="item_name"><?php _e('Nom', 'novalia-devis'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="item_name" 
                                   name="name" 
                                   class="regular-text" 
                                   required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="item_volume"><?php _e('Volume (m³)', 'novalia-devis'); ?> *</label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="item_volume" 
                                   name="volume" 
                                   step="0.001" 
                                   min="0.001" 
                                   class="regular-text" 
                                   required>
                            <p class="description">
                                <?php _e('Volume en mètres cubes (ex: 0.5 pour un lit simple)', 'novalia-devis'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="item_category"><?php _e('Catégorie', 'novalia-devis'); ?> *</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="item_category" 
                                   name="category" 
                                   class="regular-text" 
                                   list="categories-list"
                                   required>
                            <datalist id="categories-list">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo esc_attr($category); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <p class="description">
                                <?php _e('Choisissez une catégorie existante ou créez-en une nouvelle', 'novalia-devis'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="item_active"><?php _e('Statut', 'novalia-devis'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       id="item_active" 
                                       name="is_active" 
                                       value="1" 
                                       checked>
                                <?php _e('Actif (visible pour les clients)', 'novalia-devis'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <div class="nd-modal-footer">
            <button class="button button-secondary nd-modal-close">
                <?php _e('Annuler', 'novalia-devis'); ?>
            </button>
            <button class="button button-primary" id="nd-save-item">
                <?php _e('Enregistrer', 'novalia-devis'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal : Import CSV -->
<div id="nd-import-modal" class="nd-modal" style="display: none;">
    <div class="nd-modal-content">
        <div class="nd-modal-header">
            <h2><?php _e('Importer des objets depuis un CSV', 'novalia-devis'); ?></h2>
            <button class="nd-modal-close">&times;</button>
        </div>
        
        <div class="nd-modal-body">
            <p><?php _e('Le fichier CSV doit contenir les colonnes suivantes :', 'novalia-devis'); ?></p>
            <ul>
                <li><strong>Nom</strong> : Nom de l'objet</li>
                <li><strong>Volume (m³)</strong> : Volume en mètres cubes</li>
                <li><strong>Catégorie</strong> : Catégorie de l'objet</li>
                <li><strong>Actif</strong> : Oui/Non (optionnel)</li>
            </ul>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('nd_import_csv_nonce'); ?>
                <input type="file" name="csv_file" accept=".csv" required>
                <p class="submit">
                    <button type="submit" name="nd_import_csv" class="button button-primary">
                        <?php _e('Importer', 'novalia-devis'); ?>
                    </button>
                </p>
            </form>
            
            <hr>
            
            <p>
                <a href="#" id="nd-download-template" class="button">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Télécharger un modèle CSV', 'novalia-devis'); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // DataTable
    var table = $('#nd-items-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        order: [[1, 'asc']],
        pageLength: 25
    });
    
    // Filtres
    $('#nd-filter-category').on('change', function() {
        table.column(2).search(this.value).draw();
    });
    
    $('#nd-filter-status').on('change', function() {
        var val = this.value;
        if (val === '') {
            table.column(4).search('').draw();
        } else {
            var searchTerm = val === '1' ? 'Actif' : 'Inactif';
            table.column(4).search(searchTerm).draw();
        }
    });
    
    // Ouvrir modal ajout
    $('#nd-add-item-btn').on('click', function() {
        $('#nd-modal-title').text('<?php _e('Ajouter un objet', 'novalia-devis'); ?>');
        $('#nd-item-form')[0].reset();
        $('#item_id').val('');
        $('#item_active').prop('checked', true);
        $('#nd-item-modal').fadeIn();
    });
    
    // Ouvrir modal modification
    $(document).on('click', '.nd-edit-item', function() {
        var btn = $(this);
        $('#nd-modal-title').text('<?php _e('Modifier un objet', 'novalia-devis'); ?>');
        $('#item_id').val(btn.data('id'));
        $('#item_name').val(btn.data('name'));
        $('#item_volume').val(btn.data('volume'));
        $('#item_category').val(btn.data('category'));
        $('#item_active').prop('checked', btn.data('active') == 1);
        $('#nd-item-modal').fadeIn();
    });
    
    // Fermer modal
    $('.nd-modal-close').on('click', function() {
        $(this).closest('.nd-modal').fadeOut();
    });
    
    // Enregistrer objet
    $('#nd-save-item').on('click', function() {
        var formData = $('#nd-item-form').serialize();
        var itemId = $('#item_id').val();
        var action = itemId ? 'nd_edit_item' : 'nd_add_item';
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=' + action + '&nonce=' + ndAdmin.nonce,
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Supprimer objet
    $(document).on('click', '.nd-delete-item', function() {
        if (!confirm('<?php _e('Êtes-vous sûr de vouloir supprimer cet objet ?', 'novalia-devis'); ?>')) {
            return;
        }
        
        var itemId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_delete_item',
                item_id: itemId,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Toggle actif/inactif
    $(document).on('click', '.nd-toggle-item', function() {
        var itemId = $(this).data('id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_toggle_item',
                item_id: itemId,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Export CSV
    $('#nd-export-csv').on('click', function() {
        window.location.href = ajaxurl + '?action=nd_export_items_csv&nonce=' + ndAdmin.nonce;
    });
    
    // Import CSV modal
    $('#nd-import-csv-btn').on('click', function() {
        $('#nd-import-modal').fadeIn();
    });
    
    // Télécharger template CSV
    $('#nd-download-template').on('click', function(e) {
        e.preventDefault();
        var csv = 'Nom,Volume (m³),Catégorie,Actif\n';
        csv += 'Exemple Canapé,2.0,Salon,Oui\n';
        csv += 'Exemple Table,0.5,Salle à manger,Oui\n';
        
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'modele-objets.csv';
        link.click();
    });
});
</script>