<?php
/**
 * Classe du widget Elementor (chargée uniquement si Elementor est actif)
 * Chemin: /wp-content/plugins/devis-demenagement/includes/elementor-widget-class.php
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ne charger que si Elementor est disponible
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

/**
 * Classe du widget Elementor
 */
class Devis_Demenagement_Elementor_Widget extends \Elementor\Widget_Base {
    
    /**
     * Nom du widget
     */
    public function get_name() {
        return 'devis_demenagement';
    }
    
    /**
     * Titre du widget
     */
    public function get_title() {
        return 'Devis Déménagement';
    }
    
    /**
     * Icône du widget
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }
    
    /**
     * Catégorie du widget
     */
    public function get_categories() {
        return ['general'];
    }
    
    /**
     * Mots-clés pour la recherche
     */
    public function get_keywords() {
        return ['devis', 'demenagement', 'formulaire', 'estimation', 'prix'];
    }
    
    /**
     * Enregistrer les contrôles (options du widget dans Elementor)
     */
    protected function register_controls() {
        
        // Section Contenu
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Contenu',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'titre',
            [
                'label' => 'Titre du formulaire',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Estimation de votre déménagement',
                'placeholder' => 'Entrez le titre',
            ]
        );
        
        $this->add_control(
            'show_subtitle',
            [
                'label' => 'Afficher le sous-titre',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Oui',
                'label_off' => 'Non',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style
        $this->start_controls_section(
            'style_section',
            [
                'label' => 'Style',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'couleur_principale',
            [
                'label' => 'Couleur principale',
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#3498db',
            ]
        );
        
        $this->add_control(
            'border_radius',
            [
                'label' => 'Arrondi des bords',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'titre_typography',
                'label' => 'Typographie du titre',
                'selector' => '{{WRAPPER}} .devis-header h2',
            ]
        );
        
        $this->end_controls_section();
        
        // Section Avancé
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => 'Avancé',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'custom_css_class',
            [
                'label' => 'Classe CSS personnalisée',
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'ma-classe-custom',
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Rendu du widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Préparer les attributs du shortcode
        $atts = array(
            'titre' => $settings['titre'],
            'couleur' => $settings['couleur_principale']
        );
        
        // Ajouter la classe CSS personnalisée si définie
        $custom_class = !empty($settings['custom_css_class']) ? ' ' . esc_attr($settings['custom_css_class']) : '';
        
        echo '<div class="elementor-devis-demenagement-widget' . $custom_class . '">';
        
        // Ajouter du CSS inline pour les styles personnalisés
        if (!empty($settings['couleur_principale']) || !empty($settings['border_radius'])) {
            echo '<style>';
            if (!empty($settings['couleur_principale'])) {
                echo '.elementor-devis-demenagement-widget .devis-header { border-bottom-color: ' . $settings['couleur_principale'] . '; }';
                echo '.elementor-devis-demenagement-widget .devis-section { border-left-color: ' . $settings['couleur_principale'] . '; }';
                echo '.elementor-devis-demenagement-widget .devis-field input:focus { border-color: ' . $settings['couleur_principale'] . '; }';
            }
            if (!empty($settings['border_radius']['size'])) {
                echo '.elementor-devis-demenagement-widget .devis-demenagement-container { border-radius: ' . $settings['border_radius']['size'] . 'px; }';
            }
            echo '</style>';
        }
        
        // Afficher le formulaire via le shortcode
        if (class_exists('Devis_Shortcode')) {
            echo Devis_Shortcode::render_shortcode($atts);
        }
        
        echo '</div>';
    }
    
    /**
     * Rendu pour l'éditeur Elementor (mode édition)
     */
    protected function content_template() {
        ?>
        <# 
        var customClass = settings.custom_css_class ? ' ' + settings.custom_css_class : '';
        #>
        <div class="elementor-devis-demenagement-widget{{{ customClass }}}">
            <div class="devis-demenagement-container">
                <div class="devis-header">
                    <h2>{{{ settings.titre }}}</h2>
                    <# if ( 'yes' === settings.show_subtitle ) { #>
                        <p class="devis-subtitle">Obtenez une estimation instantanée de votre déménagement</p>
                    <# } #>
                </div>
                <div style="padding: 40px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                    <p style="font-size: 16px; color: #7f8c8d;">
                        📋 Le formulaire de devis s'affichera ici sur le frontend
                    </p>
                    <p style="font-size: 14px; color: #95a5a6; margin-top: 10px;">
                        Prévisualisez votre page pour voir le formulaire complet en action
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}