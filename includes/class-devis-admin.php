<?php
/**
 * Interface d'administration
 * Chemin: /wp-content/plugins/devis-demenagement/includes/class-devis-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Devis_Admin {
    
    /**
     * Ajouter le menu dans l'admin WordPress
     */
    public static function add_admin_menu() {
        add_menu_page(
            'Devis Déménagement',
            'Devis Déménagement',
            'manage_options',
            'devis-demenagement',
            array('Devis_Admin', 'settings_page'),
            'dashicons-admin-home',
            30
        );
        
        add_submenu_page(
            'devis-demenagement',
            'Paramètres',
            'Paramètres',
            'manage_options',
            'devis-demenagement',
            array('Devis_Admin', 'settings_page')
        );
        
        add_submenu_page(
            'devis-demenagement',
            'Historique des devis',
            'Historique',
            'manage_options',
            'devis-historique',
            array('Devis_Admin', 'historique_page')
        );
        
        add_submenu_page(
            'devis-demenagement',
            'Gérer les objets',
            'Objets',
            'manage_options',
            'devis-objets',
            array('Devis_Admin', 'objets_page')
        );
    }
    
    /**
     * Page des paramètres
     */
    public static function settings_page() {
        // FORCER LA CRÉATION DE L'OPTION SI ELLE N'EXISTE PAS
        if (get_option('devis_demenagement_settings') === false) {
            $default_options = array(
                'price_per_m3' => 35,
                'price_per_km' => 2,
                'minimum_price' => 150,
                'api_key' => '',
                'company_name' => get_bloginfo('name'),
                'company_email' => get_option('admin_email'),
                'company_phone' => '',
                'company_address' => ''
            );
            add_option('devis_demenagement_settings', $default_options);
            echo '<div class="notice notice-success"><p>✅ Options créées automatiquement !</p></div>';
        }
        
        // Sauvegarder les paramètres si formulaire soumis
        if (isset($_POST['devis_save_settings'])) {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'devis_settings_nonce')) {
                wp_die('Erreur de sécurité');
            }
            
            $settings = array(
                'price_per_m3' => floatval($_POST['price_per_m3']),
                'price_per_km' => floatval($_POST['price_per_km']),
                'minimum_price' => floatval($_POST['minimum_price']),
                'api_key' => sanitize_text_field($_POST['api_key']),
                'company_name' => sanitize_text_field($_POST['company_name']),
                'company_email' => sanitize_email($_POST['company_email']),
                'company_phone' => sanitize_text_field($_POST['company_phone']),
                'company_address' => sanitize_textarea_field($_POST['company_address'])
            );
            
            update_option('devis_demenagement_settings', $settings);
            
            $redirect_url = admin_url('admin.php?page=devis-demenagement&settings-updated=true');
            wp_redirect($redirect_url);
            exit;
        }
        
        $settings = get_option('devis_demenagement_settings');
        ?>
        <div class="wrap">
            <h1>⚙️ Paramètres Devis Déménagement</h1>
            
            <?php if (isset($_GET['settings-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>✅ Paramètres sauvegardés avec succès !</p>
                </div>
            <?php endif; ?>
                
            <form method="post" action="">
                <?php wp_nonce_field('devis_settings_nonce', '_wpnonce'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th colspan="2"><h2>Tarification</h2></th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="price_per_m3">Prix par m³ (€)</label>
                            </th>
                            <td>
                                <input type="number" step="0.01" name="price_per_m3" id="price_per_m3" 
                                       value="<?php echo esc_attr($settings['price_per_m3']); ?>" 
                                       class="regular-text" required>
                                <p class="description">Prix appliqué par mètre cube de marchandise</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="price_per_km">Prix par km (€)</label>
                            </th>
                            <td>
                                <input type="number" step="0.01" name="price_per_km" id="price_per_km" 
                                       value="<?php echo esc_attr($settings['price_per_km']); ?>" 
                                       class="regular-text" required>
                                <p class="description">Prix appliqué par kilomètre parcouru</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="minimum_price">Prix minimum (€)</label>
                            </th>
                            <td>
                                <input type="number" step="0.01" name="minimum_price" id="minimum_price" 
                                       value="<?php echo esc_attr($settings['minimum_price']); ?>" 
                                       class="regular-text" required>
                                <p class="description">Prix minimum facturé pour un déménagement</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th colspan="2"><h2>API de calcul de distance</h2></th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_key">Clé API OpenRouteService</label>
                            </th>
                            <td>
                                <input type="text" name="api_key" id="api_key" 
                                       value="<?php echo esc_attr($settings['api_key']); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <strong>Optionnel</strong> - Pour un calcul précis de distance routière.<br>
                                    Créez un compte gratuit sur <a href="https://openrouteservice.org/dev/#/signup" target="_blank">OpenRouteService</a><br>
                                    Si vide, le plugin utilisera Nominatim (calcul approximatif gratuit)
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th colspan="2"><h2>Informations de l'entreprise</h2></th>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="company_name">Nom de l'entreprise</label>
                            </th>
                            <td>
                                <input type="text" name="company_name" id="company_name" 
                                       value="<?php echo esc_attr($settings['company_name']); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="company_email">Email de contact</label>
                            </th>
                            <td>
                                <input type="email" name="company_email" id="company_email" 
                                       value="<?php echo esc_attr($settings['company_email']); ?>" 
                                       class="regular-text" required>
                                <p class="description">Email qui recevra les notifications de nouveaux devis</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="company_phone">Téléphone</label>
                            </th>
                            <td>
                                <input type="text" name="company_phone" id="company_phone" 
                                       value="<?php echo esc_attr($settings['company_phone']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="company_address">Adresse complète</label>
                            </th>
                            <td>
                                <textarea name="company_address" id="company_address" 
                                          class="large-text" rows="3"><?php echo esc_textarea($settings['company_address']); ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="devis_save_settings" class="button button-primary" 
                           value="💾 Enregistrer les paramètres">
                </p>
            </form>
            
            <hr>
            
            <h2>📝 Comment utiliser le plugin ?</h2>
            <div style="background: #f0f0f1; padding: 20px; border-left: 4px solid #2271b1;">
                <h3>Méthode 1 : Avec Elementor (recommandé)</h3>
                <ol>
                    <li>Éditez votre page avec Elementor</li>
                    <li>Recherchez le widget <strong>"Devis Déménagement"</strong></li>
                    <li>Glissez-déposez le widget sur votre page</li>
                    <li>Publiez !</li>
                </ol>
                
                <h3>Méthode 2 : Avec un shortcode</h3>
                <p>Copiez ce shortcode dans n'importe quelle page ou article :</p>
                <code style="display: block; padding: 10px; background: white; font-size: 14px;">[devis_demenagement]</code>
            </div>
        </div>
        <?php
    }
    
    public static function historique_page() {
        global $wpdb;
        $table_historique = $wpdb->prefix . 'devis_historique';
        
        $per_page = 20;
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($page - 1) * $per_page;
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_historique");
        $total_pages = ceil($total / $per_page);
        
        $devis_list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_historique ORDER BY date_creation DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        ?>
        <div class="wrap">
            <h1>📋 Historique des devis</h1>
            
            <p>Total : <strong><?php echo $total; ?></strong> devis</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Trajet</th>
                        <th>Volume</th>
                        <th>Prix total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($devis_list)) : ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">Aucun devis pour le moment</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($devis_list as $devis) : ?>
                            <tr>
                                <td><?php echo $devis['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($devis['date_creation'])); ?></td>
                                <td><strong><?php echo esc_html($devis['client_nom']); ?></strong></td>
                                <td><a href="mailto:<?php echo esc_attr($devis['client_email']); ?>"><?php echo esc_html($devis['client_email']); ?></a></td>
                                <td><?php echo esc_html($devis['client_telephone']); ?></td>
                                <td>
                                    <small>
                                        De: <?php echo esc_html($devis['adresse_depart']); ?><br>
                                        À: <?php echo esc_html($devis['adresse_arrivee']); ?><br>
                                        <?php echo number_format($devis['distance_km'], 2, ',', ' '); ?> km
                                    </small>
                                </td>
                                <td><?php echo number_format($devis['volume_total_m3'], 2, ',', ' '); ?> m³</td>
                                <td><strong><?php echo number_format($devis['prix_total'], 2, ',', ' '); ?> €</strong></td>
                                <td>
                                    <span class="devis-status devis-status-<?php echo esc_attr($devis['statut']); ?>">
                                        <?php echo esc_html($devis['statut']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $page
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public static function objets_page() {
        global $wpdb;
        $table_objets = $wpdb->prefix . 'devis_objets';
        
        if (isset($_POST['devis_save_objets'])) {
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'devis_objets_nonce')) {
                wp_die('Erreur de sécurité');
            }
            
            if (isset($_POST['objets']) && is_array($_POST['objets'])) {
                foreach ($_POST['objets'] as $id => $data) {
                    $wpdb->update(
                        $table_objets,
                        array(
                            'nom' => sanitize_text_field($data['nom']),
                            'volume_m3' => floatval($data['volume_m3']),
                            'actif' => isset($data['actif']) ? 1 : 0
                        ),
                        array('id' => intval($id)),
                        array('%s', '%f', '%d'),
                        array('%d')
                    );
                }
            }
            
            $redirect_url = admin_url('admin.php?page=devis-objets&objets-updated=true');
            wp_redirect($redirect_url);
            exit;
        }
        
        $objets = Devis_Database::get_objets_by_category();
        ?>
        <div class="wrap">
            <h1>📦 Gérer les objets</h1>
            
            <?php if (isset($_GET['objets-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>✅ Objets mis à jour avec succès !</p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('devis_objets_nonce', '_wpnonce'); ?>
                
                <?php foreach ($objets as $categorie => $items) : ?>
                    <h2><?php echo esc_html($categorie); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Actif</th>
                                <th>Nom de l'objet</th>
                                <th style="width: 150px;">Volume (m³)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $objet) : ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="objets[<?php echo $objet['id']; ?>][actif]" 
                                               value="1" 
                                               <?php checked($objet['actif'], 1); ?>>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="objets[<?php echo $objet['id']; ?>][nom]" 
                                               value="<?php echo esc_attr($objet['nom']); ?>" 
                                               class="regular-text">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               step="0.01" 
                                               name="objets[<?php echo $objet['id']; ?>][volume_m3]" 
                                               value="<?php echo esc_attr($objet['volume_m3']); ?>" 
                                               style="width: 100px;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br>
                <?php endforeach; ?>
                
                <p class="submit">
                    <input type="submit" name="devis_save_objets" class="button button-primary" 
                           value="💾 Enregistrer les modifications">
                </p>
            </form>
        </div>
        <?php
    }
}