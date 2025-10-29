<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Novalia Déménagement',
            'Novalia',
            'manage_options',
            'novalia-demenagement',
            array($this, 'page_devis'),
            'dashicons-migrate',
            30
        );
        
        add_submenu_page(
            'novalia-demenagement',
            'Devis',
            'Devis',
            'manage_options',
            'novalia-demenagement',
            array($this, 'page_devis')
        );
        
        add_submenu_page(
            'novalia-demenagement',
            'Objets',
            'Objets',
            'manage_options',
            'novalia-objets',
            array($this, 'page_objets')
        );
        
        add_submenu_page(
            'novalia-demenagement',
            'Tarifs',
            'Tarifs',
            'manage_options',
            'novalia-tarifs',
            array($this, 'page_tarifs')
        );
        
        add_submenu_page(
            'novalia-demenagement',
            'Statistiques',
            'Statistiques',
            'manage_options',
            'novalia-stats',
            array($this, 'page_stats')
        );
    }
    
    public function page_devis() {
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $this->view_devis(intval($_GET['id']));
            return;
        }
        
        $statut_filter = isset($_GET['statut']) ? sanitize_text_field($_GET['statut']) : null;
        $devis_list = Novalia_Devis::get_all_devis($statut_filter);
        ?>
        <div class="wrap novalia-admin">
            <h1>Gestion des Devis</h1>
            
            <div class="novalia-stats-cards">
                <div class="stat-card">
                    <h3>Total</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis(); ?></p>
                </div>
                <div class="stat-card orange">
                    <h3>En attente</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis('en_attente'); ?></p>
                </div>
                <div class="stat-card green">
                    <h3>Acceptés</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis('accepte'); ?></p>
                </div>
                <div class="stat-card red">
                    <h3>Refusés</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis('refuse'); ?></p>
                </div>
            </div>
            
            <div class="novalia-filters">
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement'); ?>" class="button <?php echo !$statut_filter ? 'button-primary' : ''; ?>">Tous</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=en_attente'); ?>" class="button <?php echo $statut_filter === 'en_attente' ? 'button-primary' : ''; ?>">En attente</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=accepte'); ?>" class="button <?php echo $statut_filter === 'accepte' ? 'button-primary' : ''; ?>">Acceptés</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=refuse'); ?>" class="button <?php echo $statut_filter === 'refuse' ? 'button-primary' : ''; ?>">Refusés</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=annule'); ?>" class="button <?php echo $statut_filter === 'annule' ? 'button-primary' : ''; ?>">Annulés</a>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Date déménagement</th>
                        <th>Volume</th>
                        <th>Distance</th>
                        <th>Prix Standard</th>
                        <th>Prix Complet</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($devis_list)) : ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">Aucun devis trouvé</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($devis_list as $devis) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($devis->numero_devis); ?></strong></td>
                                <td>
                                    <?php echo esc_html($devis->nom_client); ?><br>
                                    <small><?php echo esc_html($devis->email_client); ?></small>
                                </td>
                                <td><?php echo Novalia_Devis::format_date($devis->date_demenagement); ?></td>
                                <td><?php echo number_format($devis->volume_total, 2); ?> m³</td>
                                <td><?php echo number_format($devis->distance, 2); ?> km</td>
                                <td><?php echo Novalia_Tarifs::format_prix($devis->prix_standard); ?></td>
                                <td><?php echo Novalia_Tarifs::format_prix($devis->prix_complet); ?></td>
                                <td>
                                    <span class="novalia-statut novalia-statut-<?php echo esc_attr($devis->statut); ?>">
                                        <?php echo Novalia_Devis::get_statut_label($devis->statut); ?>
                                    </span>
                                </td>
                                <td><?php echo Novalia_Devis::format_datetime($devis->date_creation); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&action=view&id=' . $devis->id); ?>" class="button button-small">Voir</a>
                                    <button class="button button-small novalia-delete-devis" data-id="<?php echo $devis->id; ?>">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    private function view_devis($id) {
        $devis = Novalia_Devis::get_devis($id);
        
        if (!$devis) {
            echo '<div class="wrap"><h1>Devis introuvable</h1></div>';
            return;
        }
        ?>
        <div class="wrap novalia-admin">
            <h1>Devis <?php echo esc_html($devis->numero_devis); ?></h1>
            
            <a href="<?php echo admin_url('admin.php?page=novalia-demenagement'); ?>" class="button">← Retour à la liste</a>
            
            <div class="novalia-devis-detail">
                <div class="novalia-section">
                    <h2>Informations Client</h2>
                    <table class="form-table">
                        <tr>
                            <th>Nom:</th>
                            <td><?php echo esc_html($devis->nom_client); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><a href="mailto:<?php echo esc_attr($devis->email_client); ?>"><?php echo esc_html($devis->email_client); ?></a></td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td><?php echo esc_html($devis->telephone_client); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="novalia-section">
                    <h2>Trajet</h2>
                    <table class="form-table">
                        <tr>
                            <th>Départ:</th>
                            <td><?php echo esc_html($devis->adresse_depart); ?></td>
                        </tr>
                        <tr>
                            <th>Arrivée:</th>
                            <td><?php echo esc_html($devis->adresse_arrivee); ?></td>
                        </tr>
                        <tr>
                            <th>Distance:</th>
                            <td><?php echo number_format($devis->distance, 2); ?> km</td>
                        </tr>
                    </table>
                </div>
                
                <div class="novalia-section">
                    <h2>Détails Déménagement</h2>
                    <table class="form-table">
                        <tr>
                            <th>Date déménagement:</th>
                            <td><?php echo Novalia_Devis::format_date($devis->date_demenagement); ?></td>
                        </tr>
                        <tr>
                            <th>Volume total:</th>
                            <td><?php echo number_format($devis->volume_total, 2); ?> m³</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><?php echo esc_html($devis->type_demenagement); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre de cartons:</th>
                            <td><?php echo $devis->nombre_cartons; ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="novalia-section">
                    <h2>Prix</h2>
                    <table class="form-table">
                        <tr>
                            <th>Standard:</th>
                            <td><strong><?php echo Novalia_Tarifs::format_prix($devis->prix_standard); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Complet:</th>
                            <td><strong><?php echo Novalia_Tarifs::format_prix($devis->prix_complet); ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="novalia-section">
                    <h2>Statut</h2>
                    <select id="novalia-change-statut" data-devis-id="<?php echo $devis->id; ?>" class="regular-text">
                        <option value="en_attente" <?php selected($devis->statut, 'en_attente'); ?>>En attente</option>
                        <option value="accepte" <?php selected($devis->statut, 'accepte'); ?>>Accepté</option>
                        <option value="refuse" <?php selected($devis->statut, 'refuse'); ?>>Refusé</option>
                        <option value="annule" <?php selected($devis->statut, 'annule'); ?>>Annulé</option>
                    </select>
                </div>
                
                <div class="novalia-section">
                    <h2>Objets à déménager</h2>
                    <?php foreach ($devis->items_by_category as $categorie => $items) : ?>
                        <h3><?php echo esc_html($categorie); ?></h3>
                        <table class="wp-list-table widefat">
                            <thead>
                                <tr>
                                    <th>Quantité</th>
                                    <th>Objet</th>
                                    <th>Volume unitaire</th>
                                    <th>Volume total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) : ?>
                                    <tr>
                                        <td><?php echo $item->quantite; ?>x</td>
                                        <td><?php echo esc_html($item->nom_item); ?></td>
                                        <td><?php echo number_format($item->volume, 3); ?> m³</td>
                                        <td><?php echo number_format($item->volume * $item->quantite, 3); ?> m³</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function page_objets() {
        if (isset($_POST['novalia_add_item']) && check_admin_referer('novalia_add_item')) {
            Novalia_Items::add_item(
                sanitize_text_field($_POST['nom']),
                sanitize_text_field($_POST['categorie']),
                floatval($_POST['volume'])
            );
            echo '<div class="notice notice-success"><p>Objet ajouté avec succès!</p></div>';
        }
        
        $items_by_category = Novalia_Items::get_items_by_category();
        ?>
        <div class="wrap novalia-admin">
            <h1>Gestion des Objets</h1>
            
            <div class="novalia-add-form">
                <h2>Ajouter un objet</h2>
                <form method="post">
                    <?php wp_nonce_field('novalia_add_item'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="nom">Nom de l'objet</label></th>
                            <td><input type="text" name="nom" id="nom" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="categorie">Catégorie</label></th>
                            <td>
                                <input type="text" name="categorie" id="categorie" class="regular-text" list="categories-list" required>
                                <datalist id="categories-list">
                                    <?php foreach (array_keys($items_by_category) as $cat) : ?>
                                        <option value="<?php echo esc_attr($cat); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="volume">Volume (m³)</label></th>
                            <td><input type="number" name="volume" id="volume" step="0.001" min="0" class="regular-text" required></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="novalia_add_item" class="button button-primary" value="Ajouter l'objet">
                    </p>
                </form>
            </div>
            
            <h2>Liste des objets</h2>
            <?php foreach ($items_by_category as $categorie => $items) : ?>
                <h3><?php echo esc_html($categorie); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Nom</th>
                            <th style="width: 20%;">Volume (m³)</th>
                            <th style="width: 20%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                            <tr data-item-id="<?php echo $item->id; ?>">
                                <td class="item-nom"><?php echo esc_html($item->nom); ?></td>
                                <td class="item-volume"><?php echo number_format($item->volume, 3); ?></td>
                                <td>
                                    <button class="button button-small novalia-edit-item" data-id="<?php echo $item->id; ?>">Modifier</button>
                                    <button class="button button-small novalia-delete-item" data-id="<?php echo $item->id; ?>">Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    public function page_tarifs() {
        if (isset($_POST['novalia_update_tarifs']) && check_admin_referer('novalia_update_tarifs')) {
            foreach ($_POST['tarif'] as $type => $valeur) {
                Novalia_Tarifs::update_tarif($type, floatval($valeur));
            }
            echo '<div class="notice notice-success"><p>Tarifs mis à jour avec succès!</p></div>';
        }
        
        // Récupérer tous les tarifs
        $tarifs_data = Novalia_Tarifs::get_all_tarifs();
        
        // Fonction helper pour récupérer une valeur de tarif
        $get_tarif_value = function($type) use ($tarifs_data) {
            return isset($tarifs_data[$type]['valeur']) ? $tarifs_data[$type]['valeur'] : 0;
        };
        ?>
        <div class="wrap novalia-admin">
            <h1>Gestion des Tarifs</h1>
            
            <form method="post">
                <?php wp_nonce_field('novalia_update_tarifs'); ?>
                
                <h2>Tarifs de base</h2>
                <table class="form-table">
                    <tr>
                        <th><label>Prix de base</label></th>
                        <td>
                            <input type="number" name="tarif[prix_base]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_base')); ?>" class="regular-text">
                            <span class="description">CHF - Montant fixe de départ</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Prix par kilomètre</label></th>
                        <td>
                            <input type="number" name="tarif[prix_km]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_km')); ?>" class="regular-text">
                            <span class="description">CHF/km</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Prix par m³</label></th>
                        <td>
                            <input type="number" name="tarif[prix_m3]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_m3')); ?>" class="regular-text">
                            <span class="description">CHF/m³</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Prix étage sans ascenseur</label></th>
                        <td>
                            <input type="number" name="tarif[prix_etage_sans_ascenseur]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_etage_sans_ascenseur')); ?>" class="regular-text">
                            <span class="description">CHF/étage</span>
                        </td>
                    </tr>
                </table>
                
                <h2>Déménagement complet</h2>
                <table class="form-table">
                    <tr>
                        <th><label>Prix emballage carton</label></th>
                        <td>
                            <input type="number" name="tarif[prix_carton_emballage]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_carton_emballage')); ?>" class="regular-text">
                            <span class="description">CHF/carton</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Prix fourniture carton</label></th>
                        <td>
                            <input type="number" name="tarif[prix_fourniture_carton]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('prix_fourniture_carton')); ?>" class="regular-text">
                            <span class="description">CHF/carton</span>
                        </td>
                    </tr>
                </table>
                
                <h2>Majorations et réductions</h2>
                <table class="form-table">
                    <tr>
                        <th><label>Majoration weekend</label></th>
                        <td>
                            <input type="number" name="tarif[majoration_weekend]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('majoration_weekend')); ?>" class="regular-text">
                            <span class="description">% - Samedi et dimanche</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Réduction volume</label></th>
                        <td>
                            <input type="number" name="tarif[reduction_volume]" step="0.01" min="0" value="<?php echo esc_attr($get_tarif_value('reduction_volume')); ?>" class="regular-text">
                            <span class="description">% - Si volume > 50m³</span>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="novalia_update_tarifs" class="button button-primary" value="Enregistrer les tarifs">
                </p>
            </form>
        </div>
        <?php
    }
    
    public function page_stats() {
        $stats = Novalia_Devis::get_devis_stats();
        $recent_devis = Novalia_Devis::get_recent_devis(10);
        ?>
        <div class="wrap novalia-admin">
            <h1>Statistiques</h1>
            
            <div class="novalia-stats-cards">
                <div class="stat-card">
                    <h3>Total Devis</h3>
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                </div>
                <div class="stat-card orange">
                    <h3>En attente</h3>
                    <p class="stat-number"><?php echo $stats['en_attente']; ?></p>
                </div>
                <div class="stat-card green">
                    <h3>Acceptés</h3>
                    <p class="stat-number"><?php echo $stats['accepte']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Volume Total</h3>
                    <p class="stat-number"><?php echo number_format($stats['volume_total'], 2); ?> m³</p>
                </div>
                <div class="stat-card turquoise">
                    <h3>Montant Accepté</h3>
                    <p class="stat-number"><?php echo Novalia_Tarifs::format_prix($stats['montant_total'] ?: 0); ?></p>
                </div>
            </div>
            
            <h2>Derniers devis</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Date déménagement</th>
                        <th>Volume</th>
                        <th>Prix</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_devis as $devis) : ?>
                        <tr>
                            <td><?php echo esc_html($devis->numero_devis); ?></td>
                            <td><?php echo esc_html($devis->nom_client); ?></td>
                            <td><?php echo Novalia_Devis::format_date($devis->date_demenagement); ?></td>
                            <td><?php echo number_format($devis->volume_total, 2); ?> m³</td>
                            <td><?php echo Novalia_Tarifs::format_prix($devis->prix_standard); ?></td>
                            <td>
                                <span class="novalia-statut novalia-statut-<?php echo esc_attr($devis->statut); ?>">
                                    <?php echo Novalia_Devis::get_statut_label($devis->statut); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}