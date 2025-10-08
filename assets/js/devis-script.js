/**
 * Script frontend pour le formulaire de devis
 * Chemin: /wp-content/plugins/devis-demenagement/assets/js/devis-script.js
 */

(function($) {
    'use strict';
    
    let distanceKm = 0;
    let volumeTotal = 0;
    let prixTotal = 0;
    
    // Au chargement de la page
    $(document).ready(function() {
        initializeForm();
    });
    
    /**
     * Initialiser le formulaire
     */
    function initializeForm() {
        // Calcul en temps réel du volume
        $('.objet-quantite, #volume_custom').on('input', function() {
            calculateVolume();
            calculatePrice();
        });
        
        // Calcul des options supplémentaires
        $('#etages_depart, #etages_arrivee').on('input', function() {
            calculatePrice();
        });
        
        // Calcul de la distance quand les adresses changent
        $('#adresse_depart, #adresse_arrivee').on('blur', function() {
            const depart = $('#adresse_depart').val();
            const arrivee = $('#adresse_arrivee').val();
            
            if (depart && arrivee) {
                calculateDistance(depart, arrivee);
            }
        });
        
        // Soumission du formulaire
        $('#devis-demenagement-form').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
        
        // Bouton reset
        $('#devis-reset-btn').on('click', function() {
            resetForm();
        });
        
        // Bouton retry
        $('#devis-retry-btn').on('click', function() {
            hideError();
        });
    }
    
    /**
     * Calculer le volume total
     */
    function calculateVolume() {
        volumeTotal = 0;
        
        // Additionner tous les objets sélectionnés
        $('.objet-quantite').each(function() {
            const quantite = parseInt($(this).val()) || 0;
            const volumeUnitaire = parseFloat($(this).data('volume')) || 0;
            volumeTotal += quantite * volumeUnitaire;
        });
        
        // Ajouter le volume personnalisé
        const volumeCustom = parseFloat($('#volume_custom').val()) || 0;
        volumeTotal += volumeCustom;
        
        // Mettre à jour l'affichage
        $('#summary-volume').text(volumeTotal.toFixed(2));
    }
    
    /**
     * Calculer la distance entre deux adresses
     */
    function calculateDistance(depart, arrivee) {
        // Afficher un loader pendant le calcul
        $('#distance-result').html('<span class="distance-loading">⏳ Calcul en cours...</span>').show();
        
        $.ajax({
            url: devisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'calculate_distance',
                nonce: devisAjax.nonce,
                from: depart,
                to: arrivee
            },
            success: function(response) {
                if (response.success && response.data.distance) {
                    distanceKm = response.data.distance;
                    $('#distance-value').text(distanceKm.toFixed(2));
                    $('#summary-distance').text(distanceKm.toFixed(2));
                    $('#distance-result').html(
                        '<span class="distance-icon">🚚</span>' +
                        '<span class="distance-text">Distance calculée : <strong>' + 
                        distanceKm.toFixed(2) + '</strong> km</span>'
                    ).show();
                    calculatePrice();
                } else {
                    $('#distance-result').html(
                        '<span class="distance-warning">⚠️ Impossible de calculer la distance automatiquement</span>'
                    ).show();
                }
            },
            error: function() {
                $('#distance-result').html(
                    '<span class="distance-warning">⚠️ Erreur lors du calcul de la distance</span>'
                ).show();
            }
        });
    }
    
    /**
     * Calculer le prix total
     */
    function calculatePrice() {
        // Récupérer les tarifs (vous pouvez les passer via wp_localize_script)
        // Pour l'instant, on fait un calcul approximatif
        const prixM3 = 35; // Prix par m³ par défaut
        const prixKm = 2;  // Prix par km par défaut
        const prixEtage = 30; // Prix par étage
        
        let prix = 0;
        
        // Prix du volume
        prix += volumeTotal * prixM3;
        
        // Prix de la distance
        prix += distanceKm * prixKm;
        
        // Prix des étages
        const etagesDepart = parseInt($('#etages_depart').val()) || 0;
        const etagesArrivee = parseInt($('#etages_arrivee').val()) || 0;
        prix += (etagesDepart + etagesArrivee) * prixEtage;
        
        // Prix minimum de 150€
        if (prix < 150 && (volumeTotal > 0 || distanceKm > 0)) {
            prix = 150;
        }
        
        prixTotal = prix;
        
        // Mettre à jour l'affichage
        $('#summary-total').text(prixTotal.toFixed(2));
    }
    
    /**
     * Soumettre le formulaire
     */
    function submitForm() {
        // Validation
        if (!validateForm()) {
            return;
        }
        
        // Afficher le loader
        showLoader();
        hideError();
        
        // Récupérer les données du formulaire
        const formData = $('#devis-demenagement-form').serialize();
        
        // Envoyer la requête AJAX
        $.ajax({
            url: devisAjax.ajaxurl,
            type: 'POST',
            data: formData + '&action=submit_devis&nonce=' + devisAjax.nonce,
            success: function(response) {
                hideLoader();
                
                if (response.success) {
                    showSuccess(response.data);
                } else {
                    showError(response.data.message || 'Une erreur est survenue');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                showError('Erreur de connexion au serveur. Veuillez réessayer.');
                console.error('Erreur AJAX:', error);
            }
        });
    }
    
    /**
     * Valider le formulaire
     */
    function validateForm() {
        // Vérifier les champs obligatoires
        const nom = $('#client_nom').val().trim();
        const email = $('#client_email').val().trim();
        const telephone = $('#client_telephone').val().trim();
        const depart = $('#adresse_depart').val().trim();
        const arrivee = $('#adresse_arrivee').val().trim();
        
        if (!nom || !email || !telephone || !depart || !arrivee) {
            showError('Veuillez remplir tous les champs obligatoires');
            return false;
        }
        
        // Vérifier qu'au moins un objet est sélectionné ou volume custom
        if (volumeTotal === 0) {
            showError('Veuillez sélectionner au moins un objet ou indiquer un volume personnalisé');
            return false;
        }
        
        return true;
    }
    
    /**
     * Afficher le loader
     */
    function showLoader() {
        $('#devis-loader').fadeIn(300);
        $('#devis-demenagement-form').hide();
    }
    
    /**
     * Masquer le loader
     */
    function hideLoader() {
        $('#devis-loader').fadeOut(300);
    }
    
    /**
     * Afficher le message de succès
     */
    function showSuccess(data) {
        $('#devis-demenagement-form').hide();
        
        // Remplir les données
        $('#success-price').text(data.prix_total.toFixed(2));
        $('#success-volume').text(data.volume_total.toFixed(2));
        $('#success-distance').text(data.distance_km.toFixed(2));
        $('#devis-download-link').attr('href', data.pdf_url);
        
        // Afficher le message
        $('#devis-success').fadeIn(500);
        
        // Scroll vers le haut
        $('html, body').animate({
            scrollTop: $('#devis-form-container').offset().top - 100
        }, 500);
    }
    
    /**
     * Afficher le message d'erreur
     */
    function showError(message) {
        $('#error-message-text').text(message);
        $('#devis-error').fadeIn(300);
        
        // Scroll vers le haut
        $('html, body').animate({
            scrollTop: $('#devis-form-container').offset().top - 100
        }, 500);
    }
    
    /**
     * Masquer le message d'erreur
     */
    function hideError() {
        $('#devis-error').fadeOut(300);
        $('#devis-demenagement-form').show();
    }
    
    /**
     * Réinitialiser le formulaire
     */
    function resetForm() {
        // Masquer les messages
        $('#devis-success').hide();
        $('#devis-error').hide();
        
        // Réinitialiser le formulaire
        $('#devis-demenagement-form')[0].reset();
        
        // Réinitialiser les variables
        distanceKm = 0;
        volumeTotal = 0;
        prixTotal = 0;
        
        // Réinitialiser l'affichage
        $('#summary-volume').text('0');
        $('#summary-distance').text('0');
        $('#summary-total').text('0');
        $('#distance-result').hide();
        
        // Afficher le formulaire
        $('#devis-demenagement-form').fadeIn(300);
        
        // Scroll vers le haut
        $('html, body').animate({
            scrollTop: $('#devis-form-container').offset().top - 100
        }, 500);
    }
    
})(jQuery);