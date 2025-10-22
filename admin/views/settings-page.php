<?php
/**
 * Template de la page des paramètres
 *
 * @package NovaliaDevis
 * @subpackage Admin/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap nd-settings-page">
    <h1><?php _e('Paramètres - Novalia Devis', 'novalia-devis'); ?></h1>
    
    <?php settings_errors(); ?>
    
    <!-- Onglets -->
    <nav class="nav-tab-wrapper">
        <a href="?page=novalia-devis-settings&tab=company" 
           class="nav-tab <?php echo $active_tab === 'company' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-building"></span>
            <?php _e('Entreprise', 'novalia-devis'); ?>
        </a>
        <a href="?page=novalia-devis-settings&tab=email" 
           class="nav-tab <?php echo $active_tab === 'email' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-email"></span>
            <?php _e('Emails', 'novalia-devis'); ?>
        </a>
        <a href="?page=novalia-devis-settings&tab=pdf" 
           class="nav-tab <?php echo $active_tab === 'pdf' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-media-document"></span>
            <?php _e('PDF', 'novalia-devis'); ?>
        </a>
        <a href="?page=novalia-devis-settings&tab=advanced" 
           class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-tools"></span>
            <?php _e('Avancé', 'novalia-devis'); ?>
        </a>
    </nav>
    
    <div class="nd-tab-content">
        
        <!-- Onglet : Entreprise -->
        <?php if ($active_tab === 'company'): ?>
            <form method="post" action="options.php">
                <?php settings_fields('nd_company_group'); ?>
                
                <div class="nd-card">
                    <h2><?php _e('Informations de l\'entreprise', 'novalia-devis'); ?></h2>
                    <p class="description">
                        <?php _e('Ces informations apparaîtront sur les devis PDF et dans les emails.', 'novalia-devis'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="company_name"><?php _e('Nom de l\'entreprise', 'novalia-devis'); ?> *</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="company_name" 
                                       name="nd_company[name]" 
                                       value="<?php echo esc_attr($company['name']); ?>" 
                                       class="regular-text" 
                                       required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_address"><?php _e('Adresse', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="company_address" 
                                       name="nd_company[address]" 
                                       value="<?php echo esc_attr($company['address']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_zipcode"><?php _e('Code postal', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="company_zipcode" 
                                       name="nd_company[zipcode]" 
                                       value="<?php echo esc_attr($company['zipcode']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_city"><?php _e('Ville', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="company_city" 
                                       name="nd_company[city]" 
                                       value="<?php echo esc_attr($company['city']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_phone"><?php _e('Téléphone', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <input type="tel" 
                                       id="company_phone" 
                                       name="nd_company[phone]" 
                                       value="<?php echo esc_attr($company['phone']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_email"><?php _e('Email', 'novalia-devis'); ?> *</label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="company_email" 
                                       name="nd_company[email]" 
                                       value="<?php echo esc_attr($company['email']); ?>" 
                                       class="regular-text" 
                                       required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="company_siret"><?php _e('SIRET', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="company_siret" 
                                       name="nd_company[siret]" 
                                       value="<?php echo esc_attr($company['siret']); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php _e('Numéro SIRET de votre entreprise (optionnel)', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label><?php _e('Logo', 'novalia-devis'); ?></label>
                            </th>
                            <td>
                                <div id="nd-logo-preview">
                                    <?php if (!empty($company['logo_url'])): ?>
                                        <img src="<?php echo esc_url($company['logo_url']); ?>" 
                                             style="max-width: 200px; display: block; margin-bottom: 10px;">
                                        <button type="button" class="button" id="nd-delete-logo">
                                            <?php _e('Supprimer le logo', 'novalia-devis'); ?>
                                        </button>
                                    <?php else: ?>
                                        <p class="description">
                                            <?php _e('Aucun logo uploadé', 'novalia-devis'); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="margin-top: 10px;">
                                    <input type="file" 
                                           id="nd-logo-upload" 
                                           accept="image/*" 
                                           style="display: none;">
                                    <button type="button" class="button" id="nd-upload-logo-btn">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php _e('Uploader un logo', 'novalia-devis'); ?>
                                    </button>
                                </div>
                                
                                <input type="hidden" 
                                       name="nd_company[logo_url]" 
                                       id="company_logo_url" 
                                       value="<?php echo esc_attr($company['logo_url']); ?>">
                                
                                <p class="description">
                                    <?php _e('Format accepté : JPG, PNG, GIF, WEBP (max 2 MB)', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
        
        <!-- Onglet : Emails -->
        <?php if ($active_tab === 'email'): ?>
            <form method="post" action="options.php">
                <?php settings_fields('nd_email_group'); ?>
                
                <div class="nd-card">
                    <h2><?php _e('Configuration des emails', 'novalia-devis'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_customer_subject">
                                    <?php _e('Sujet de l\'email client', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="email_customer_subject" 
                                       name="nd_email[customer_subject]" 
                                       value="<?php echo esc_attr($email['customer_subject']); ?>" 
                                       class="large-text">
                                <p class="description">
                                    <?php _e('Variables disponibles : {quote_number}', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email_admin_subject">
                                    <?php _e('Sujet de l\'email admin', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="email_admin_subject" 
                                       name="nd_email[admin_subject]" 
                                       value="<?php echo esc_attr($email['admin_subject']); ?>" 
                                       class="large-text">
                                <p class="description">
                                    <?php _e('Variables disponibles : {quote_number}', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email_admin_email">
                                    <?php _e('Email administrateur', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="email_admin_email" 
                                       name="nd_email[admin_email]" 
                                       value="<?php echo esc_attr($email['admin_email']); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php _e('Email qui recevra les notifications de nouveaux devis', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Options', 'novalia-devis'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           name="nd_email[send_copy_to_admin]" 
                                           value="1" 
                                           <?php checked($email['send_copy_to_admin'], 1); ?>>
                                    <?php _e('Envoyer une copie à l\'administrateur pour chaque devis', 'novalia-devis'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="nd-card">
                    <h3><?php _e('Tester l\'envoi d\'emails', 'novalia-devis'); ?></h3>
                    <p class="description">
                        <?php _e('Envoyez un email de test pour vérifier votre configuration.', 'novalia-devis'); ?>
                    </p>
                    
                    <div class="nd-test-email">
                        <input type="email" 
                               id="test-email-address" 
                               placeholder="<?php _e('Votre email', 'novalia-devis'); ?>" 
                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" 
                               class="regular-text">
                        <button type="button" class="button" id="nd-send-test-email">
                            <?php _e('Envoyer un test', 'novalia-devis'); ?>
                        </button>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
        
        <!-- Onglet : PDF -->
        <?php if ($active_tab === 'pdf'): ?>
            <form method="post" action="options.php">
                <?php settings_fields('nd_pdf_group'); ?>
                
                <div class="nd-card">
                    <h2><?php _e('Configuration du PDF', 'novalia-devis'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="pdf_footer_text">
                                    <?php _e('Texte du pied de page', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="pdf_footer_text" 
                                       name="nd_pdf[footer_text]" 
                                       value="<?php echo esc_attr($pdf['footer_text']); ?>" 
                                       class="large-text">
                                <p class="description">
                                    <?php _e('Texte affiché en bas du PDF', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="pdf_legal_mentions">
                                    <?php _e('Mentions légales', 'novalia-devis'); ?>
                                </label>
                            </th>
                            <td>
                                <textarea id="pdf_legal_mentions" 
                                          name="nd_pdf[legal_mentions]" 
                                          rows="5" 
                                          class="large-text"><?php echo esc_textarea($pdf['legal_mentions']); ?></textarea>
                                <p class="description">
                                    <?php _e('Mentions légales affichées sur le devis', 'novalia-devis'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
        
        <!-- Onglet : Avancé -->
        <?php if ($active_tab === 'advanced'): ?>
            <div class="nd-card">
                <h2><?php _e('Shortcode', 'novalia-devis'); ?></h2>
                <p><?php _e('Utilisez ce shortcode pour afficher le formulaire de devis sur n\'importe quelle page :', 'novalia-devis'); ?></p>
                <div class="nd-shortcode-box">
                    <code>[novalia_devis_form]</code>
                    <button class="button button-small nd-copy-shortcode" data-clipboard="[novalia_devis_form]">
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php _e('Copier', 'novalia-devis'); ?>
                    </button>
                </div>
            </div>
            
            <div class="nd-card">
                <h2><?php _e('Informations système', 'novalia-devis'); ?></h2>
                <table class="nd-system-info">
                    <tr>
                        <th><?php _e('Version du plugin', 'novalia-devis'); ?></th>
                        <td><?php echo ND_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Version WordPress', 'novalia-devis'); ?></th>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Version PHP', 'novalia-devis'); ?></th>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Nombre de devis', 'novalia-devis'); ?></th>
                        <td><?php echo ND_Database::count_quotes(); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Nombre d\'objets', 'novalia-devis'); ?></th>
                        <td><?php echo count(ND_Items::get_all_items(false)); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="nd-card nd-danger-zone">
                <h2><?php _e('⚠️ Zone dangereuse', 'novalia-devis'); ?></h2>
                <p><?php _e('Ces actions sont irréversibles !', 'novalia-devis'); ?></p>
                
                <button class="button button-large" id="nd-reset-settings">
                    <?php _e('Réinitialiser tous les paramètres', 'novalia-devis'); ?>
                </button>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Upload du logo
    $('#nd-upload-logo-btn').on('click', function() {
        $('#nd-logo-upload').click();
    });
    
    $('#nd-logo-upload').on('change', function() {
        var file = this.files[0];
        
        if (!file) return;
        
        var formData = new FormData();
        formData.append('action', 'nd_upload_logo');
        formData.append('logo', file);
        formData.append('nonce', ndAdmin.nonce);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#company_logo_url').val(response.data.url);
                    $('#nd-logo-preview').html(
                        '<img src="' + response.data.url + '" style="max-width: 200px; display: block; margin-bottom: 10px;">' +
                        '<button type="button" class="button" id="nd-delete-logo"><?php _e('Supprimer le logo', 'novalia-devis'); ?></button>'
                    );
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Supprimer le logo
    $(document).on('click', '#nd-delete-logo', function() {
        if (!confirm('<?php _e('Supprimer le logo ?', 'novalia-devis'); ?>')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_delete_logo',
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#company_logo_url').val('');
                    $('#nd-logo-preview').html('<p class="description"><?php _e('Aucun logo uploadé', 'novalia-devis'); ?></p>');
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Test email
    $('#nd-send-test-email').on('click', function() {
        var email = $('#test-email-address').val();
        var btn = $(this);
        
        if (!email) {
            alert('<?php _e('Veuillez saisir une adresse email', 'novalia-devis'); ?>');
            return;
        }
        
        btn.prop('disabled', true).text('<?php _e('Envoi...', 'novalia-devis'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nd_test_email',
                email: email,
                nonce: ndAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Email de test envoyé !', 'novalia-devis'); ?>');
                } else {
                    alert(response.data.message);
                }
                btn.prop('disabled', false).text('<?php _e('Envoyer un test', 'novalia-devis'); ?>');
            }
        });
    });
    
    // Copier le shortcode
    $('.nd-copy-shortcode').on('click', function() {
        var text = $(this).data('clipboard');
        navigator.clipboard.writeText(text).then(function() {
            alert('<?php _e('Shortcode copié !', 'novalia-devis'); ?>');
        });
    });
});
</script>