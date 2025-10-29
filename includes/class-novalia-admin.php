<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_download_fiche'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Novalia Demenagement',
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
    
    public function handle_download_fiche() {
        if (!isset($_GET['novalia_download_fiche']) || !isset($_GET['devis_id'])) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Acces non autorise');
        }
        
        $devis_id = intval($_GET['devis_id']);
        $devis = Novalia_Devis::get_devis($devis_id);
        
        if (!$devis || empty($devis->fiche_technique_pdf)) {
            wp_die('Fiche technique introuvable');
        }
        
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . $devis->fiche_technique_pdf;
        
        if (!file_exists($file_path)) {
            wp_die('Fichier PDF introuvable');
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="fiche_technique_' . $devis->numero_devis . '.pdf"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
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
                    <h3>Acceptes</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis('accepte'); ?></p>
                </div>
                <div class="stat-card red">
                    <h3>Refuses</h3>
                    <p class="stat-number"><?php echo Novalia_Devis::count_devis('refuse'); ?></p>
                </div>
            </div>
            
            <div class="novalia-filters">
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement'); ?>" class="button <?php echo !$statut_filter ? 'button-primary' : ''; ?>">Tous</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=en_attente'); ?>" class="button <?php echo $statut_filter === 'en_attente' ? 'button-primary' : ''; ?>">En attente</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=accepte'); ?>" class="button <?php echo $statut_filter === 'accepte' ? 'button-primary' : ''; ?>">Acceptes</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=refuse'); ?>" class="button <?php echo $statut_filter === 'refuse' ? 'button-primary' : ''; ?>">Refuses</a>
                <a href="<?php echo admin_url('admin.php?page=novalia-demenagement&statut=annule'); ?>" class="button <?php echo $statut_filter === 'annule' ? 'button-primary' : ''; ?>">Annules</a>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Client</th>
                        <th>Date demenagement</th>
                        <th>Volume</th>
                        <th>Distance</th>
                        <th>Prix Standard</th>
                        <th>Prix Complet</th>
                        <th>Fiche technique</th>
                        <th>Statut</th>
                        <th>Date creation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($devis_list)) : ?>
                        <tr>
                            <td colspan="11" style="text-align: center;">Aucun devis trouve</td>
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
                                    <?php if (!empty($devis->fiche_technique_pdf)) : ?>
                                        <a href="<?php echo admin_url('admin.php?novalia_download_fiche=1&devis_id=' . $devis->id); ?>" class="button button-small button-primary">
                                            Telecharger PDF
                                        </a>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-minus" style="color: #ccc;"></span>
                                    <?php endif; ?>
                                </td>
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
            wp_die('Devis introuvable');
        }
        ?>
        <div class="wrap novalia-admin">
            <h1>Devis <?php echo esc_html($devis->numero_devis); ?></h1>
            
            <a href="<?php echo admin_url('admin.php?page=novalia-demenagement'); ?>" class="button">&larr; Retour a la liste</a>
            
            <div class="novalia-devis-detail">
                <div class="devis-section">
                    <h2>Informations Client</h2>
                    <table class="form-table">
                        <tr>
                            <th>Nom</th>
                            <td><?php echo esc_html($devis->nom_client); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><a href="mailto:<?php echo esc_attr($devis->email_client); ?>"><?php echo esc_html($devis->email_client); ?></a></td>
                        </tr>
                        <tr>
                            <th>Telephone</th>
                            <td><a href="tel:<?php echo esc_attr($devis->telephone_client); ?>"><?php echo esc_html($devis->telephone_client); ?></a></td>
                        </tr>
                    </table>
                </div>
                
                <div class="devis-section">
                    <h2>Details du Demenagement</h2>
                    <table class="form-table">
                        <tr>
                            <th>Date</th>
                            <td><?php echo Novalia_Devis::format_date($devis->date_demenagement); ?></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td><?php echo strtoupper(esc_html($devis->type_demenagement)); ?></td>
                        </tr>
                        <tr>
                            <th>Adresse de depart</th>
                            <td><?php echo nl2br(esc_html($devis->adresse_depart)); ?></td>
                        </tr>
                        <tr>
                            <th>Adresse d'arrivee</th>
                            <td><?php echo nl2br(esc_html($devis->adresse_arrivee)); ?></td>
                        </tr>
                        <tr>
                            <th>Distance</th>
                            <td><?php echo number_format($devis->distance, 2); ?> km</td>
                        </tr>
                        <tr>
                            <th>Volume total</th>
                            <td><?php echo number_format($devis->volume_total, 2); ?> m³</td>
                        </tr>
                        <tr>
                            <th>Nombre de cartons</th>
                            <td><?php echo intval($devis->nombre_cartons); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="devis-section">
                    <h2>Tarification</h2>
                    <table class="form-table">
                        <tr>
                            <th>Prix Standard</th>
                            <td><strong><?php echo Novalia_Tarifs::format_prix($devis->prix_standard); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Prix Complet</th>
                            <td><strong><?php echo Novalia_Tarifs::format_prix($devis->prix_complet); ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="devis-section">
                    <h2>Statut</h2>
                    <table class="form-table">
                        <tr>
                            <th>Statut actuel</th>
                            <td>
                                <select id="novalia-change-statut" data-devis-id="<?php echo $devis->id; ?>" class="regular-text">
                                    <option value="en_attente" <?php selected($devis->statut, 'en_attente'); ?>>En attente</option>
                                    <option value="accepte" <?php selected($devis->statut, 'accepte'); ?>>Accepte</option>
                                    <option value="refuse" <?php selected($devis->statut, 'refuse'); ?>>Refuse</option>
                                    <option value="annule" <?php selected($devis->statut, 'annule'); ?>>Annule</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="devis-section">
                    <h2>Liste des Objets</h2>
                    <?php if (isset($devis->items_by_category)) : ?>
                        <?php foreach ($devis->items_by_category as $categorie => $items) : ?>
                            <h3><?php echo esc_html($categorie); ?></h3>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Quantite</th>
                                        <th>Nom</th>
                                        <th>Volume unitaire</th>
                                        <th>Volume total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item) : ?>
                                        <tr>
                                            <td><?php echo intval($item->quantite); ?>x</td>
                                            <td><?php echo esc_html($item->nom_item); ?></td>
                                            <td><?php echo number_format($item->volume, 3); ?> m³</td>
                                            <td><?php echo number_format($item->volume * $item->quantite, 3); ?> m³</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function page_objets() {
        // CORRECTION : Traitement de l'ajout d'objet
        if (isset($_POST['novalia_add_item']) && check_admin_referer('novalia_add_item_nonce')) {
            $nom = sanitize_text_field($_POST['nom']);
            $categorie = sanitize_text_field($_POST['categorie']);
            $volume = floatval($_POST['volume']);
            
            if (!empty($nom) && !empty($categorie) && $volume > 0) {
                $item_id = Novalia_Items::add_item($nom, $categorie, $volume);
                
                if ($item_id) {
                    echo '<div class="notice notice-success is-dismissible"><p>Objet ajouté avec succès!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Erreur lors de l\'ajout de l\'objet.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Veuillez remplir tous les champs correctement.</p></div>';
            }
        }
        
        $items_by_category = Novalia_Items::get_items_by_category();
        ?>
        <div class="wrap novalia-admin">
            <h1>Gestion des Objets</h1>
            
            <div class="novalia-add-item-form">
                <h2>Ajouter un nouvel objet</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('novalia_add_item_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="nom">Nom de l'objet</label></th>
                            <td><input type="text" id="nom" name="nom" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="categorie">Categorie</label></th>
                            <td>
                                <select id="categorie" name="categorie" class="regular-text" required>
                                    <option value="">Choisir une categorie</option>
                                    <option value="Salon">Salon</option>
                                    <option value="Salle à manger">Salle à manger</option>
                                    <option value="Cuisine">Cuisine</option>
                                    <option value="Chambre principale">Chambre principale</option>
                                    <option value="Chambre enfant">Chambre enfant</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Salle de bain">Salle de bain</option>
                                    <option value="Entrée / Couloir">Entrée / Couloir</option>
                                    <option value="Cave / Garage">Cave / Garage</option>
                                    <option value="Extérieur">Extérieur</option>
                                    <option value="Cartons">Cartons</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="volume">Volume (m³)</label></th>
                            <td><input type="number" id="volume" name="volume" class="regular-text" step="0.001" min="0.001" required></td>
                        </tr>
                    </table>
                    <p>
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
            echo '<div class="notice notice-success"><p>Tarifs mis a jour avec succes!</p></div>';
        }
        
        $tarifs_data = Novalia_Tarifs::get_all_tarifs();
        
        $get_tarif_value = function($type) use ($tarifs_data) {
            return isset($tarifs_data[$type]['valeur']) ? $tarifs_data[$type]['valeur'] : 0;
        };
        ?>
        <div class="wrap novalia-admin">
            <h1>Configuration des Tarifs</h1>
            
            <form method="post" class="novalia-tarifs-form">
                <?php wp_nonce_field('novalia_update_tarifs'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label>Frais de deplacement (CHF)</label></th>
                        <td><input type="number" name="tarif[prix_base]" value="<?php echo $get_tarif_value('prix_base'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Prix par kilometre (CHF/km)</label></th>
                        <td><input type="number" name="tarif[prix_km]" value="<?php echo $get_tarif_value('prix_km'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Prix par m³ (CHF/m³)</label></th>
                        <td><input type="number" name="tarif[prix_m3]" value="<?php echo $get_tarif_value('prix_m3'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Prix etage sans ascenseur (CHF/etage)</label></th>
                        <td><input type="number" name="tarif[prix_etage_sans_ascenseur]" value="<?php echo $get_tarif_value('prix_etage_sans_ascenseur'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Prix emballage carton (CHF/carton)</label></th>
                        <td><input type="number" name="tarif[prix_carton_emballage]" value="<?php echo $get_tarif_value('prix_carton_emballage'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Majoration weekend (%)</label></th>
                        <td><input type="number" name="tarif[majoration_weekend]" value="<?php echo $get_tarif_value('majoration_weekend'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label>Reduction volume >70m³ (%)</label></th>
                        <td><input type="number" name="tarif[reduction_volume]" value="<?php echo $get_tarif_value('reduction_volume'); ?>" step="0.01" class="regular-text"></td>
                    </tr>
                </table>
                
                <p>
                    <input type="submit" name="novalia_update_tarifs" class="button button-primary button-large" value="Enregistrer les tarifs">
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
                    <h3>Total devis</h3>
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                </div>
                <div class="stat-card orange">
                    <h3>En attente</h3>
                    <p class="stat-number"><?php echo $stats['en_attente']; ?></p>
                </div>
                <div class="stat-card green">
                    <h3>Acceptes</h3>
                    <p class="stat-number"><?php echo $stats['accepte']; ?></p>
                </div>
                <div class="stat-card blue">
                    <h3>Volume total</h3>
                    <p class="stat-number"><?php echo number_format($stats['volume_total'], 2); ?> m³</p>
                </div>
                <div class="stat-card turquoise">
                    <h3>Montant total accepte</h3>
                    <p class="stat-number"><?php echo number_format($stats['montant_total'] ?: 0); ?> CHF</p>
                </div>
            </div>
            
            <h2>Derniers devis</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Client</th>
                        <th>Date demenagement</th>
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