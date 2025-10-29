<?php

if (!defined('ABSPATH')) {
    exit;
}

class Novalia_Shortcode {
    
    public function __construct() {
        add_shortcode('novalia_devis', array($this, 'render_shortcode'));
    }
    
    public function render_shortcode($atts) {
        ob_start();
        ?>
        <div class="novalia-container">
            <div class="novalia-hero-title">
                <h1>Obtenez votre devis en 10 minutes</h1>
                <p>Remplissez le formulaire et recevez votre devis instantanément par email</p>
            </div>
            <div class="novalia-wizard">
                <div class="novalia-steps">
                    <div class="novalia-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Trajet</div>
                    </div>
                    <div class="novalia-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Inventaire</div>
                    </div>
                    <div class="novalia-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Récapitulatif</div>
                    </div>
                </div>
                
                <div class="novalia-form-container">
                    <!-- Étape 1: Trajet -->
                    <div class="novalia-form-step" id="step-1">
                        <h2>Calculez votre trajet</h2>
                        <p class="novalia-subtitle">Indiquez vos adresses de départ et d'arrivée</p>
                        
                        <div class="novalia-form-group">
                            <label for="adresse-depart">Adresse de départ *</label>
                            <input type="text" id="adresse-depart" class="novalia-input" placeholder="Ex: Rue de Lausanne 10, 1000 Lausanne" required>
                            <div id="suggestions-depart" class="novalia-suggestions"></div>
                        </div>
                        
                        <div class="novalia-form-group">
                            <label for="adresse-arrivee">Adresse d'arrivée *</label>
                            <input type="text" id="adresse-arrivee" class="novalia-input" placeholder="Ex: Avenue de la Gare 5, 1003 Lausanne" required>
                            <div id="suggestions-arrivee" class="novalia-suggestions"></div>
                        </div>
                        
                        <div class="novalia-form-group">
                            <label for="date-demenagement">Date du déménagement *</label>
                            <input type="date" id="date-demenagement" class="novalia-input" required>
                        </div>
                        
                        <div class="novalia-form-row">
                            <div class="novalia-form-group">
                                <label for="etages-depart">Étages départ</label>
                                <input type="number" id="etages-depart" class="novalia-input" min="0" value="0">
                                <label style="margin-top: 8px; font-weight: normal; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="ascenseur-depart" style="width: auto;">
                                    <span>Ascenseur disponible au départ</span>
                                </label>
                            </div>
                            <div class="novalia-form-group">
                                <label for="etages-arrivee">Étages arrivée</label>
                                <input type="number" id="etages-arrivee" class="novalia-input" min="0" value="0">
                                <label style="margin-top: 8px; font-weight: normal; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="ascenseur-arrivee" style="width: auto;">
                                    <span>Ascenseur disponible à l'arrivée</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="novalia-form-row">
                            <div class="novalia-form-group">
                                <label for="type-logement-depart">Type de logement - Départ *</label>
                                <select id="type-logement-depart" class="novalia-input" required>
                                    <option value="">Sélectionnez</option>
                                    <option value="maison">Maison</option>
                                    <option value="appartement">Appartement</option>
                                </select>
                            </div>
                            <div class="novalia-form-group">
                                <label for="type-logement-arrivee">Type de logement - Arrivée *</label>
                                <select id="type-logement-arrivee" class="novalia-input" required>
                                    <option value="">Sélectionnez</option>
                                    <option value="maison">Maison</option>
                                    <option value="appartement">Appartement</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="map-container" class="novalia-map"></div>
                        
                        <div class="novalia-distance-info">
                            <span class="distance-label">Distance calculée:</span>
                            <span class="distance-value" id="distance-display">-</span>
                        </div>
                        
                        <button type="button" class="novalia-btn novalia-btn-next novalia-btn-uniform" id="btn-step-1" disabled>
                            Suivant
                        </button>
                    </div>
                    
                    <!-- Étape 2: Inventaire -->
                    <div class="novalia-form-step" id="step-2" style="display: none;">
                        <div class="novalia-step2-layout">
                            <div class="novalia-inventory-section">
                                <h2>Sélectionnez vos objets</h2>
                                <p class="novalia-subtitle">Choisissez les objets à déménager par pièce</p>
                                
                                <div id="categories-container">
                                    <?php echo $this->render_categories(); ?>
                                </div>
                                
                                <div class="novalia-custom-item">
                                    <h3>Ajouter un objet personnalisé</h3>
                                    <div class="novalia-form-row">
                                        <input type="text" id="custom-item-name" placeholder="Nom de l'objet" class="novalia-input">
                                        <input type="number" id="custom-item-volume" placeholder="Volume (m³)" step="0.001" min="0" class="novalia-input">
                                        <select id="custom-item-category" class="novalia-input">
                                            <?php echo $this->render_category_options(); ?>
                                        </select>
                                        <button type="button" id="btn-add-custom" class="novalia-btn novalia-btn-secondary novalia-btn-uniform">Ajouter</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="novalia-volume-sticky">
                                <div class="novalia-volume-card">
                                    <h3>Volume total</h3>
                                    <div class="volume-total-display">
                                        <span class="volume-number" id="volume-total-display">0.00</span>
                                        <span class="volume-unit">m³</span>
                                    </div>
                                    
                                    <div class="volume-by-category" id="volume-by-category"></div>
                                    
                                    <div class="selected-items-count">
                                        <span id="items-count">0</span> objet(s) sélectionné(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="novalia-step-actions">
                            <button type="button" class="novalia-btn novalia-btn-secondary novalia-btn-uniform btn-prev" data-prev="1">
                                Précédent
                            </button>
                            <button type="button" class="novalia-btn novalia-btn-next novalia-btn-uniform" id="btn-step-2">
                                Suivant
                            </button>
                        </div>
                    </div>
                    
                    <!-- Étape 3: Récapitulatif -->
                    <div class="novalia-form-step" id="step-3" style="display: none;">
                        <h2>Récapitulatif de votre déménagement</h2>
                        <p class="novalia-subtitle">Vérifiez vos informations avant de recevoir votre devis</p>
                        
                        <div class="novalia-recap">
                            <div class="recap-section">
                                <h3>Trajet</h3>
                                <div class="recap-item">
                                    <span class="recap-label">Départ:</span>
                                    <span class="recap-value" id="recap-depart"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Type logement:</span>
                                    <span class="recap-value" id="recap-type-depart"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Étage:</span>
                                    <span class="recap-value" id="recap-etage-depart"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Ascenseur:</span>
                                    <span class="recap-value" id="recap-ascenseur-depart"></span>
                                </div>
                                
                                <div class="recap-item">
                                    <span class="recap-label">Arrivée:</span>
                                    <span class="recap-value" id="recap-arrivee"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Type logement:</span>
                                    <span class="recap-value" id="recap-type-arrivee"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Étage:</span>
                                    <span class="recap-value" id="recap-etage-arrivee"></span>
                                </div>
                                <div class="recap-item recap-sub-item">
                                    <span class="recap-label">Ascenseur:</span>
                                    <span class="recap-value" id="recap-ascenseur-arrivee"></span>
                                </div>
                                
                                <div class="recap-item">
                                    <span class="recap-label">Distance:</span>
                                    <span class="recap-value" id="recap-distance"></span>
                                </div>
                                <div class="recap-item">
                                    <span class="recap-label">Date:</span>
                                    <span class="recap-value" id="recap-date"></span>
                                </div>
                            </div>
                            
                            <div class="recap-section">
                                <h3>Volume</h3>
                                <div class="recap-item">
                                    <span class="recap-label">Volume total:</span>
                                    <span class="recap-value" id="recap-volume"></span>
                                </div>
                            </div>
                            
                            <div class="recap-section recap-items-modern">
                                <button type="button" class="recap-items-toggle" id="toggle-items-recap">
                                    <div class="toggle-left">
                                        <span class="toggle-title">Objets à déménager</span>
                                        <span class="items-count-badge" id="recap-items-count"></span>
                                    </div>
                                    <div class="toggle-right">
                                        <svg class="arrow-icon" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </button>
                                <div class="recap-items-content" id="recap-items-container"></div>
                            </div>
                            
                            <div class="recap-section">
                                <h3>Type de déménagement</h3>
                                <div class="novalia-demenagement-type">
                                    <label class="type-option">
                                        <input type="radio" name="type_demenagement" value="standard" checked>
                                        <div class="type-card">
                                            <h4>Standard</h4>
                                            <ul class="prestations-list">
                                                <li>✓ Mise à disposition des cartons</li>
                                                <li>✓ Chargement et déchargement par notre équipe</li>
                                                <li>✓ Transport sécurisé en véhicule adapté</li>
                                                <li>✓ Matériel de manutention fourni</li>
                                                <li>✓ Assistance au démontage si nécessaire</li>
                                                <li>✓ Assurance transport incluse</li>
                                            </ul>
                                        </div>
                                    </label>
                                    <label class="type-option">
                                        <input type="radio" name="type_demenagement" value="complet">
                                        <div class="type-card">
                                            <h4>Complet</h4>
                                            <p class="type-subtitle">Tranquillité totale — vous n'avez rien à gérer</p>
                                            <ul class="prestations-list">
                                                <li class="prestation-highlight">✓ Toutes les prestations Standard incluses</li>
                                                <li>✓ Emballage et protection du mobilier</li>
                                                <li>✓ Démontage et remontage du mobilier</li>
                                                <li>✓ Transport sécurisé avec véhicules capitonnés</li>
                                                <li>✓ Mise en place du mobilier à l'arrivée</li>
                                            </ul>
                                        </div>
                                    </label>
                                </div>
                                
                                <div id="cartons-section" style="display: none;">
                                    <div class="novalia-form-group">
                                        <label for="nombre-cartons">Estimation du nombre de cartons à emballer *</label>
                                        <input type="number" id="nombre-cartons" class="novalia-input" min="1" value="10">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="novalia-estimation-notice">
                                <h4>Important</h4>
                                <p>Le devis que vous allez recevoir par email est une <strong>estimation</strong>. Une visite sur place sera nécessaire pour finaliser un devis définitif et précis.</p>
                            </div>
                            
                            <div class="recap-section">
                                <h3>Vos coordonnées</h3>
                                <div class="novalia-form-group">
                                    <label for="nom-client">Nom complet *</label>
                                    <input type="text" id="nom-client" class="novalia-input" required>
                                </div>
                                <div class="novalia-form-group">
                                    <label for="email-client">Email *</label>
                                    <input type="email" id="email-client" class="novalia-input" required>
                                </div>
                                <div class="novalia-form-group">
                                    <label for="telephone-client">Téléphone *</label>
                                    <input type="tel" id="telephone-client" class="novalia-input" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="novalia-step-actions">
                            <button type="button" class="novalia-btn novalia-btn-secondary novalia-btn-uniform btn-prev" data-prev="2">
                                Précédent
                            </button>
                            <button type="button" class="novalia-btn novalia-btn-primary novalia-btn-uniform" id="btn-submit-devis">
                                Recevoir mon devis
                            </button>
                        </div>
                    </div>
                    
                    <!-- Message de confirmation -->
                    <div class="novalia-form-step" id="step-success" style="display: none;">
                        <div class="novalia-success">
                            <div class="success-icon">✓</div>
                            <h2>Devis envoyé avec succès!</h2>
                            <p>Vous allez recevoir <span id="success-devis-type">vos devis</span> par email dans quelques instants.</p>
                            <p id="success-devis-details">Nous vous avons envoyé <strong>2 devis</strong>: un pour le déménagement standard et un pour le déménagement complet.</p>
                            <p class="success-subtitle">Notre équipe vous contactera rapidement pour planifier une visite sur place.</p>
                            
                            <div class="success-actions">
                                <button class="novalia-btn novalia-btn-secondary novalia-btn-uniform" id="btn-refaire-estimation">
                                    Refaire mon estimation
                                </button>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="novalia-btn novalia-btn-primary novalia-btn-uniform">
                                    Retour à l'accueil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_categories() {
        $items_by_category = Novalia_Items::get_items_by_category();
        
        // Définir l'ordre souhaité des catégories
        $category_order = array(
            'Entrée / Couloir',
            'Salon',
            'Salle à manger',
            'Cuisine',
            'Chambre principale',
            'Chambre enfant',
            'Bureau',
            'Salle de bain',
            'Cartons',
            'Cave / Garage',
            'Extérieur'
        );
        
        $output = '';
        $first = true;
        
        // Parcourir les catégories dans l'ordre défini
        foreach ($category_order as $categorie) {
            // Vérifier si la catégorie existe dans les items
            if (!isset($items_by_category[$categorie])) {
                continue;
            }
            
            $items = $items_by_category[$categorie];
            $open_class = $first ? 'open' : '';
            $first = false;
            
            $output .= '<div class="novalia-category ' . $open_class . '">';
            $output .= '<div class="category-header">';
            $output .= '<h3>' . esc_html($categorie) . '</h3>';
            $output .= '<span class="category-toggle">▼</span>';
            $output .= '</div>';
            $output .= '<div class="category-items">';
            
            foreach ($items as $item) {
                $output .= '<div class="novalia-item" data-item-id="' . $item->id . '" data-volume="' . $item->volume . '" data-category="' . esc_attr($categorie) . '">';
                $output .= '<div class="item-info">';
                $output .= '<span class="item-name">' . esc_html($item->nom) . '</span>';
                $output .= '<span class="item-volume">' . number_format($item->volume, 3) . ' m³</span>';
                $output .= '</div>';
                $output .= '<div class="item-controls">';
                $output .= '<button type="button" class="item-btn item-minus" disabled>-</button>';
                $output .= '<span class="item-quantity">0</span>';
                $output .= '<button type="button" class="item-btn item-plus">+</button>';
                $output .= '</div>';
                $output .= '</div>';
            }
            
            $output .= '</div>';
            $output .= '</div>';
        }
        
        // Ajouter les catégories qui ne sont pas dans l'ordre défini (au cas où)
        foreach ($items_by_category as $categorie => $items) {
            if (!in_array($categorie, $category_order)) {
                $output .= '<div class="novalia-category">';
                $output .= '<div class="category-header">';
                $output .= '<h3>' . esc_html($categorie) . '</h3>';
                $output .= '<span class="category-toggle">▼</span>';
                $output .= '</div>';
                $output .= '<div class="category-items">';
                
                foreach ($items as $item) {
                    $output .= '<div class="novalia-item" data-item-id="' . $item->id . '" data-volume="' . $item->volume . '" data-category="' . esc_attr($categorie) . '">';
                    $output .= '<div class="item-info">';
                    $output .= '<span class="item-name">' . esc_html($item->nom) . '</span>';
                    $output .= '<span class="item-volume">' . number_format($item->volume, 3) . ' m³</span>';
                    $output .= '</div>';
                    $output .= '<div class="item-controls">';
                    $output .= '<button type="button" class="item-btn item-minus" disabled>-</button>';
                    $output .= '<span class="item-quantity">0</span>';
                    $output .= '<button type="button" class="item-btn item-plus">+</button>';
                    $output .= '</div>';
                    $output .= '</div>';
                }
                
                $output .= '</div>';
                $output .= '</div>';
            }
        }
        
        return $output;
    }
    
    private function render_category_options() {
        // Définir l'ordre souhaité des catégories
        $category_order = array(
            'Entrée / Couloir',
            'Salon',
            'Salle à manger',
            'Cuisine',
            'Chambre principale',
            'Chambre enfant',
            'Bureau',
            'Salle de bain',
            'Cartons',
            'Cave / Garage',
            'Extérieur'
        );
        
        $output = '<option value="">Sélectionnez une pièce</option>';
        
        foreach ($category_order as $categorie) {
            $output .= '<option value="' . esc_attr($categorie) . '">' . esc_html($categorie) . '</option>';
        }
        
        return $output;
    }
}