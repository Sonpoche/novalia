<?php
/**
 * Template du formulaire de devis
 * Chemin: /wp-content/plugins/devis-demenagement/templates/formulaire-devis.php
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="devis-demenagement-container" id="devis-form-container">
    
    <div class="devis-header">
        <h2><?php echo esc_html($atts['titre']); ?></h2>
        <p class="devis-subtitle">Obtenez une estimation instantanée de votre déménagement</p>
    </div>
    
    <form id="devis-demenagement-form" class="devis-form">
        
        <!-- Section 1: Informations de contact -->
        <div class="devis-section">
            <h3 class="devis-section-title">👤 Vos informations</h3>
            <div class="devis-row">
                <div class="devis-field">
                    <label for="client_nom">Nom complet *</label>
                    <input type="text" id="client_nom" name="client_nom" required>
                </div>
                <div class="devis-field">
                    <label for="client_email">Email *</label>
                    <input type="email" id="client_email" name="client_email" required>
                </div>
                <div class="devis-field">
                    <label for="client_telephone">Téléphone *</label>
                    <input type="tel" id="client_telephone" name="client_telephone" required>
                </div>
            </div>
        </div>
        
        <!-- Section 2: Adresses -->
        <div class="devis-section">
            <h3 class="devis-section-title">📍 Votre déménagement</h3>
            <div class="devis-row">
                <div class="devis-field">
                    <label for="adresse_depart">Adresse de départ *</label>
                    <input type="text" id="adresse_depart" name="adresse_depart" 
                           placeholder="Ex: 10 Rue de la Paix, 75002 Paris" required>
                </div>
                <div class="devis-field">
                    <label for="adresse_arrivee">Adresse d'arrivée *</label>
                    <input type="text" id="adresse_arrivee" name="adresse_arrivee" 
                           placeholder="Ex: 25 Avenue Victor Hugo, 69003 Lyon" required>
                </div>
            </div>
            
            <div class="devis-distance-result" id="distance-result" style="display: none;">
                <span class="distance-icon">🚚</span>
                <span class="distance-text">Distance calculée : <strong id="distance-value">0</strong> km</span>
            </div>
        </div>
        
        <!-- Section 3: Sélection des objets -->
        <div class="devis-section">
            <h3 class="devis-section-title">📦 Sélectionnez vos objets</h3>
            <p class="devis-help-text">Indiquez la quantité de chaque objet à déménager</p>
            
            <div class="devis-objets-container">
                <?php foreach ($objets as $categorie => $items) : ?>
                    <div class="devis-categorie">
                        <h4 class="devis-categorie-title"><?php echo esc_html($categorie); ?></h4>
                        <div class="devis-objets-grid">
                            <?php foreach ($items as $objet) : ?>
                                <div class="devis-objet-item">
                                    <label for="objet_<?php echo $objet['id']; ?>">
                                        <span class="objet-nom"><?php echo esc_html($objet['nom']); ?></span>
                                        <span class="objet-volume">(<?php echo number_format($objet['volume_m3'], 2, ',', ' '); ?> m³)</span>
                                    </label>
                                    <input type="number" 
                                           id="objet_<?php echo $objet['id']; ?>" 
                                           name="objets[<?php echo $objet['id']; ?>]" 
                                           min="0" 
                                           max="99" 
                                           value="0" 
                                           data-volume="<?php echo $objet['volume_m3']; ?>"
                                           class="objet-quantite">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Volume personnalisé -->
            <div class="devis-custom-volume">
                <label for="volume_custom">Volume supplémentaire (m³)</label>
                <input type="number" id="volume_custom" name="volume_custom" 
                       min="0" step="0.1" value="0" placeholder="0">
                <p class="devis-help-text">Si vous avez d'autres objets non listés</p>
            </div>
        </div>
        
        <!-- Section 4: Options supplémentaires -->
        <div class="devis-section">
            <h3 class="devis-section-title">⚙️ Options supplémentaires</h3>
            <div class="devis-row">
                <div class="devis-field">
                    <label for="etages_depart">Étages au départ (sans ascenseur)</label>
                    <input type="number" id="etages_depart" name="etages_depart" 
                           min="0" max="20" value="0">
                    <p class="devis-help-text-small">30€ par étage</p>
                </div>
                <div class="devis-field">
                    <label for="etages_arrivee">Étages à l'arrivée (sans ascenseur)</label>
                    <input type="number" id="etages_arrivee" name="etages_arrivee" 
                           min="0" max="20" value="0">
                    <p class="devis-help-text-small">30€ par étage</p>
                </div>
            </div>
            
            <div class="devis-field">
                <label for="notes">Informations complémentaires</label>
                <textarea id="notes" name="notes" rows="4" 
                          placeholder="Ex: objets fragiles, parking difficile, date souhaitée..."></textarea>
            </div>
        </div>
        
        <!-- Résumé en temps réel -->
        <div class="devis-summary" id="devis-summary">
            <h3 class="devis-summary-title">💰 Estimation en temps réel</h3>
            <div class="devis-summary-content">
                <div class="summary-row">
                    <span class="summary-label">Volume total :</span>
                    <span class="summary-value"><strong id="summary-volume">0</strong> m³</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Distance :</span>
                    <span class="summary-value"><strong id="summary-distance">0</strong> km</span>
                </div>
                <div class="summary-row summary-total">
                    <span class="summary-label">Prix estimé :</span>
                    <span class="summary-value"><strong id="summary-total">0</strong> €</span>
                </div>
            </div>
        </div>
        
        <!-- Bouton de soumission -->
        <div class="devis-submit-container">
            <button type="submit" class="devis-submit-btn" id="devis-submit-btn">
                <span class="btn-icon">📄</span>
                <span class="btn-text">Générer mon devis gratuit</span>
            </button>
            <p class="devis-help-text">Vous recevrez votre devis par email en PDF</p>
        </div>
        
    </form>
    
    <!-- Message de succès -->
    <div class="devis-success-message" id="devis-success" style="display: none;">
        <div class="success-icon">✅</div>
        <h3>Devis envoyé avec succès !</h3>
        <p>Votre devis a été généré et envoyé à votre adresse email.</p>
        <div class="success-details">
            <p><strong>Prix total :</strong> <span id="success-price">0</span> €</p>
            <p><strong>Volume :</strong> <span id="success-volume">0</span> m³</p>
            <p><strong>Distance :</strong> <span id="success-distance">0</span> km</p>
        </div>
        <a href="#" class="devis-download-btn" id="devis-download-link" target="_blank">
            📥 Télécharger le PDF
        </a>
        <button type="button" class="devis-reset-btn" id="devis-reset-btn">
            🔄 Faire un nouveau devis
        </button>
    </div>
    
    <!-- Message d'erreur -->
    <div class="devis-error-message" id="devis-error" style="display: none;">
        <div class="error-icon">❌</div>
        <h3>Une erreur est survenue</h3>
        <p id="error-message-text"></p>
        <button type="button" class="devis-retry-btn" id="devis-retry-btn">
            🔄 Réessayer
        </button>
    </div>
    
    <!-- Loader -->
    <div class="devis-loader" id="devis-loader" style="display: none;">
        <div class="loader-spinner"></div>
        <p>Génération de votre devis en cours...</p>
    </div>
    
</div>