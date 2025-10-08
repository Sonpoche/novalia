/**
 * Script JavaScript pour l'administration
 * Chemin: /wp-content/plugins/devis-demenagement/assets/js/admin-script.js
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Confirmation avant sauvegarde des paramètres
        $('#devis-demenagement-form, form[action=""]').on('submit', function(e) {
            const submitButton = $(this).find('input[type="submit"]');
            
            if (submitButton.attr('name') === 'devis_save_settings') {
                // Vérifier que les prix sont renseignés
                const pricePerM3 = $('input[name="price_per_m3"]').val();
                const pricePerKm = $('input[name="price_per_km"]').val();
                
                if (!pricePerM3 || !pricePerKm) {
                    alert('⚠️ Veuillez renseigner tous les tarifs obligatoires');
                    e.preventDefault();
                    return false;
                }
                
                // Afficher un message de confirmation
                submitButton.val('⏳ Enregistrement...');
                submitButton.prop('disabled', true);
            }
            
            if (submitButton.attr('name') === 'devis_save_objets') {
                if (!confirm('Êtes-vous sûr de vouloir modifier les objets ? Cette action affectera les futurs devis.')) {
                    e.preventDefault();
                    return false;
                }
                
                submitButton.val('⏳ Enregistrement...');
                submitButton.prop('disabled', true);
            }
        });
        
        // Filtrer les objets dans la page de gestion
        if ($('.devis-categorie').length > 0) {
            addSearchFilter();
        }
        
        // Ajouter des tooltips informatifs
        addTooltips();
        
        // Validation en temps réel des champs numériques
        $('input[type="number"]').on('input', function() {
            const min = parseFloat($(this).attr('min'));
            const max = parseFloat($(this).attr('max'));
            const val = parseFloat($(this).val());
            
            if (!isNaN(min) && val < min) {
                $(this).css('border-color', '#dc3545');
            } else if (!isNaN(max) && val > max) {
                $(this).css('border-color', '#dc3545');
            } else {
                $(this).css('border-color', '');
            }
        });
        
        // Copier le shortcode au clic
        $('.wrap code').on('click', function() {
            const text = $(this).text();
            copyToClipboard(text);
            
            // Feedback visuel
            const originalBg = $(this).css('background-color');
            $(this).css('background-color', '#d4edda');
            
            // Ajouter un message temporaire
            if (!$(this).next('.copy-message').length) {
                $(this).after('<span class="copy-message" style="color: #28a745; margin-left: 10px;">✓ Copié !</span>');
            }
            
            setTimeout(() => {
                $(this).css('background-color', originalBg);
                $('.copy-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 2000);
        });
        
        // Amélioration de l'interface historique
        enhanceHistorique();
        
    });
    
    /**
     * Ajouter un filtre de recherche pour les objets
     */
    function addSearchFilter() {
        // Créer le champ de recherche
        const searchHtml = `
            <div style="margin-bottom: 20px; padding: 15px; background: #f0f0f1; border-radius: 6px;">
                <label for="objet-search" style="font-weight: 600; margin-right: 10px;">
                    🔍 Rechercher un objet :
                </label>
                <input type="text" id="objet-search" placeholder="Ex: canapé, armoire..." 
                       style="width: 300px; padding: 8px;">
                <button type="button" id="reset-search" class="button" style="margin-left: 10px;">
                    Réinitialiser
                </button>
            </div>
        `;
        
        $('.devis-categorie').first().before(searchHtml);
        
        // Fonction de recherche
        $('#objet-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm === '') {
                $('.devis-categorie').show();
                $('.devis-categorie tbody tr').show();
                return;
            }
            
            $('.devis-categorie').each(function() {
                let hasVisibleRows = false;
                
                $(this).find('tbody tr').each(function() {
                    const objetNom = $(this).find('input[type="text"]').val().toLowerCase();
                    
                    if (objetNom.includes(searchTerm)) {
                        $(this).show();
                        hasVisibleRows = true;
                    } else {
                        $(this).hide();
                    }
                });
                
                if (hasVisibleRows) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Bouton reset
        $('#reset-search').on('click', function() {
            $('#objet-search').val('').trigger('input');
        });
    }
    
    /**
     * Ajouter des tooltips
     */
    function addTooltips() {
        // Ajouter des infobulles sur les champs importants
        $('label[for="price_per_m3"]').attr('title', 'Prix facturé par mètre cube de marchandise à déménager');
        $('label[for="price_per_km"]').attr('title', 'Prix facturé par kilomètre parcouru');
        $('label[for="minimum_price"]').attr('title', 'Prix minimum même pour les petits déménagements');
    }
    
    /**
     * Copier du texte dans le presse-papier
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback pour les navigateurs plus anciens
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }
    
    /**
     * Améliorer l'interface de l'historique
     */
    function enhanceHistorique() {
        // Ajouter des couleurs alternées aux lignes
        $('.wp-list-table tbody tr:even').css('background-color', '#f9f9f9');
        
        // Highlight au survol
        $('.wp-list-table tbody tr').hover(
            function() {
                $(this).css('background-color', '#e8f4f8');
            },
            function() {
                const isEven = $(this).index() % 2 === 0;
                $(this).css('background-color', isEven ? '#f9f9f9' : '');
            }
        );
        
        // Ajouter un compteur de résultats visible
        const totalRows = $('.wp-list-table tbody tr').length;
        if (totalRows > 0) {
            $('.wp-list-table').before(
                '<div style="margin-bottom: 10px; font-style: italic; color: #666;">' +
                'Affichage de ' + totalRows + ' résultat(s) sur cette page' +
                '</div>'
            );
        }
    }
    
    /**
     * Validation côté client avant soumission
     */
    function validateSettings() {
        let isValid = true;
        const errors = [];
        
        // Vérifier le prix par m³
        const priceM3 = parseFloat($('input[name="price_per_m3"]').val());
        if (isNaN(priceM3) || priceM3 <= 0) {
            errors.push('Le prix par m³ doit être supérieur à 0');
            isValid = false;
        }
        
        // Vérifier le prix par km
        const priceKm = parseFloat($('input[name="price_per_km"]').val());
        if (isNaN(priceKm) || priceKm <= 0) {
            errors.push('Le prix par km doit être supérieur à 0');
            isValid = false;
        }
        
        // Vérifier l'email
        const email = $('input[name="company_email"]').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push('L\'adresse email est invalide');
            isValid = false;
        }
        
        if (!isValid) {
            alert('⚠️ Erreurs détectées :\n\n' + errors.join('\n'));
        }
        
        return isValid;
    }
    
    /**
     * Sauvegarder automatiquement en local (brouillon)
     */
    function autoSaveDraft() {
        if (typeof(Storage) !== "undefined") {
            const formData = $('#devis-demenagement-form, form[action=""]').serialize();
            localStorage.setItem('devis_settings_draft', formData);
            
            // Afficher un indicateur
            if (!$('.auto-save-indicator').length) {
                $('.wrap h1').after(
                    '<span class="auto-save-indicator" style="margin-left: 15px; color: #46b450; font-size: 14px;">' +
                    '✓ Brouillon sauvegardé' +
                    '</span>'
                );
                
                setTimeout(function() {
                    $('.auto-save-indicator').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        }
    }
    
    // Auto-save toutes les 30 secondes si modification
    let formChanged = false;
    $('form input, form textarea, form select').on('change', function() {
        formChanged = true;
    });
    
    setInterval(function() {
        if (formChanged) {
            autoSaveDraft();
            formChanged = false;
        }
    }, 30000);
    
})(jQuery);