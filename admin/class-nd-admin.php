<?php
/**
 * Classe principale de l'administration
 *
 * @package NovaliaDevis
 * @subpackage Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Admin {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Initialisation des sous-pages
        new ND_Admin_Items();
        new ND_Admin_Pricing();
        new ND_Admin_Quotes();
        new ND_Admin_Settings();
    }
    
    /**
     * Ajout du menu dans l'administration WordPress
     */
    public function add_admin_menu() {
        // Menu principal
        add_menu_page(
            __('Novalia Devis', 'novalia-devis'),
            __('Novalia Devis', 'novalia-devis'),
            'manage_novalia_devis',
            'novalia-devis',
            [$this, 'dashboard_page'],
            'dashicons-clipboard',
            30
        );
        
        // Sous-menu : Tableau de bord
        add_submenu_page(
            'novalia-devis',
            __('Tableau de bord', 'novalia-devis'),
            __('Tableau de bord', 'novalia-devis'),
            'manage_novalia_devis',
            'novalia-devis',
            [$this, 'dashboard_page']
        );
        
        // Sous-menu : Devis
        add_submenu_page(
            'novalia-devis',
            __('Devis', 'novalia-devis'),
            __('Devis', 'novalia-devis'),
            'view_novalia_quotes',
            'novalia-devis-quotes',
            [new ND_Admin_Quotes(), 'render_page']
        );
        
        // Sous-menu : Objets
        add_submenu_page(
            'novalia-devis',
            __('Objets de déménagement', 'novalia-devis'),
            __('Objets', 'novalia-devis'),
            'edit_novalia_items',
            'novalia-devis-items',
            [new ND_Admin_Items(), 'render_page']
        );
        
        // Sous-menu : Tarification
        add_submenu_page(
            'novalia-devis',
            __('Tarification', 'novalia-devis'),
            __('Tarification', 'novalia-devis'),
            'edit_novalia_pricing',
            'novalia-devis-pricing',
            [new ND_Admin_Pricing(), 'render_page']
        );
        
        // Sous-menu : Paramètres
        add_submenu_page(
            'novalia-devis',
            __('Paramètres', 'novalia-devis'),
            __('Paramètres', 'novalia-devis'),
            'manage_novalia_devis',
            'novalia-devis-settings',
            [new ND_Admin_Settings(), 'render_page']
        );
    }
    
    /**
     * Page du tableau de bord
     */
    public function dashboard_page() {
        // Récupération des statistiques
        $stats = ND_Quotes::get_statistics();
        $recent_quotes = ND_Quotes::get_all_quotes(['limit' => 10]);
        
        include ND_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Chargement des assets admin (CSS/JS)
     */
    public function enqueue_admin_assets($hook) {
        // Charger uniquement sur les pages du plugin
        if (strpos($hook, 'novalia-devis') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'nd-admin-style',
            ND_PLUGIN_URL . 'assets/css/admin-style.css',
            [],
            ND_VERSION
        );
        
        // DataTables CSS
        wp_enqueue_style(
            'datatables',
            'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            [],
            '1.13.6'
        );
        
        // JavaScript
        wp_enqueue_script(
            'nd-admin-script',
            ND_PLUGIN_URL . 'assets/js/admin-script.js',
            ['jquery'],
            ND_VERSION,
            true
        );
        
        // DataTables JS
        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            ['jquery'],
            '1.13.6',
            true
        );
        
        // Chart.js pour les statistiques
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js',
            [],
            '4.4.0',
            true
        );
        
        // Localisation des scripts
        wp_localize_script('nd-admin-script', 'ndAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nd_admin_nonce'),
            'strings' => [
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer cet élément ?', 'novalia-devis'),
                'error' => __('Une erreur est survenue', 'novalia-devis'),
                'success' => __('Action effectuée avec succès', 'novalia-devis')
            ]
        ]);
    }
    
    /**
     * Enregistrement des paramètres WordPress
     */
    public function register_settings() {
        // Groupe de paramètres : Tarification
        register_setting('nd_pricing_group', 'nd_pricing', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_pricing_settings']
        ]);
        
        // Groupe de paramètres : Entreprise
        register_setting('nd_company_group', 'nd_company', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_company_settings']
        ]);
        
        // Groupe de paramètres : Email
        register_setting('nd_email_group', 'nd_email', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_email_settings']
        ]);
        
        // Groupe de paramètres : PDF
        register_setting('nd_pdf_group', 'nd_pdf', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_pdf_settings']
        ]);
    }
    
    /**
     * Sanitization des paramètres de tarification
     */
    public function sanitize_pricing_settings($input) {
        $sanitized = [];
        
        $float_fields = [
            'price_per_km',
            'price_per_m3',
            'fixed_fee',
            'fee_floor',
            'fee_elevator',
            'fee_packing',
            'fee_insurance',
            'min_quote_amount'
        ];
        
        foreach ($float_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = floatval($input[$field]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitization des paramètres entreprise
     */
    public function sanitize_company_settings($input) {
        $sanitized = [];
        
        $sanitized['name'] = sanitize_text_field($input['name'] ?? '');
        $sanitized['address'] = sanitize_text_field($input['address'] ?? '');
        $sanitized['zipcode'] = sanitize_text_field($input['zipcode'] ?? '');
        $sanitized['city'] = sanitize_text_field($input['city'] ?? '');
        $sanitized['phone'] = sanitize_text_field($input['phone'] ?? '');
        $sanitized['email'] = sanitize_email($input['email'] ?? '');
        $sanitized['siret'] = sanitize_text_field($input['siret'] ?? '');
        $sanitized['logo_url'] = esc_url_raw($input['logo_url'] ?? '');
        
        return $sanitized;
    }
    
    /**
     * Sanitization des paramètres email
     */
    public function sanitize_email_settings($input) {
        $sanitized = [];
        
        $sanitized['customer_subject'] = sanitize_text_field($input['customer_subject'] ?? '');
        $sanitized['admin_subject'] = sanitize_text_field($input['admin_subject'] ?? '');
        $sanitized['admin_email'] = sanitize_email($input['admin_email'] ?? '');
        $sanitized['send_copy_to_admin'] = isset($input['send_copy_to_admin']) ? 1 : 0;
        
        return $sanitized;
    }
    
    /**
     * Sanitization des paramètres PDF
     */
    public function sanitize_pdf_settings($input) {
        $sanitized = [];
        
        $sanitized['footer_text'] = sanitize_text_field($input['footer_text'] ?? '');
        $sanitized['legal_mentions'] = sanitize_textarea_field($input['legal_mentions'] ?? '');
        
        return $sanitized;
    }
    
    /**
     * Affichage d'une notice admin
     */
    public static function add_admin_notice($message, $type = 'success') {
        add_action('admin_notices', function() use ($message, $type) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($type),
                esc_html($message)
            );
        });
    }
}