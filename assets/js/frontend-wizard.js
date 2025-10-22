/**
 * Script principal du wizard de devis
 * 
 * @package NovaliaDevis
 */

(function($) {
    'use strict';
    
    let currentStep = 1;
    const totalSteps = 3;
    let selectedItems = [];
    let customItemId = 1000;
    
    /**
     * Initialisation
     */
    $(document).ready(function() {
        initWizardNavigation();
        initItemsSelection();
        initCustomItems();
        initFormSubmit();
    });
    
    /**
     * Navigation du wizard
     */
    function initWizardNavigation() {
        // Bouton suivant
        $('#nd-next-btn').on('click', function() {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });
        
        // Bouton précédent
        $('#nd-prev-btn').on('click', function() {
            goToStep(currentStep - 1);
        });
        
        // Nouveau devis
        $('#nd-new-quote').on('click', function() {
            location.reload();
        });
    }
    
    /**
     * Aller à une étape
     */
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        
        // Masquer l'étape actuelle
        $('.nd-wizard-step[data-step="' + currentStep + '"]').fadeOut(300, function() {
            // Afficher la nouvelle étape
            $('.nd-wizard-step[data-step="' + step + '"]').fadeIn(300);
        });
        
        // Mettre à jour la progression
        $('.nd-progress-step').removeClass('nd-active nd-completed');
        $('.nd-progress-step[data-step="' + step + '"]').addClass('nd-active');
        
        for (let i = 1; i < step; i++) {
            $('.nd-progress-step[data-step="' + i + '"]').addClass('nd-completed');
        }
        
        // Mettre à jour les boutons
        if (step === 1) {
            $('#nd-prev-btn').hide();
        } else {
            $('#nd-prev-btn').show();
        }
        
        if (step === totalSteps) {
            $('#nd-next-btn').hide();
            $('#nd-submit-btn').show();
            
            // Afficher le récapitulatif
            displaySummary();
        } else {
            $('#nd-next-btn').show();
            $('#nd-submit-btn').hide();
        }
        
        currentStep = step;
        
        // Scroll vers le haut
        $('html, body').animate({
            scrollTop: $('#nd-wizard').offset().top - 50
        }, 300);
    }
    
    /**
     * Validation d'une étape
     */
    function validateStep(step) {
        let isValid = true;
        let errorMessage = '';
        
        if (step === 1) {
            // Validation des adresses
            const addressFrom = $('#address_from').val().trim();
            const addressTo = $('#address_to').val().trim();
            const distance = parseFloat($('#distance').val());
            
            if (!addressFrom) {
                errorMessage = 'Veuillez saisir l\'adresse de départ';
                isValid = false;
            } else if (!addressTo) {
                errorMessage = 'Veuillez saisir l\'adresse d\'arrivée';
                isValid = false;
            } else if (!distance || distance <= 0) {
                errorMessage = 'Veuillez sélectionner des adresses valides pour calculer la distance';
                isValid = false;
            }
        } else if (step === 2) {
            // Validation des objets
            updateSelectedItems();
            
            if (selectedItems.length === 0) {
                errorMessage = 'Veuillez sélectionner au moins un objet à déménager';
                isValid = false;
            }
        }
        
        if (!isValid && errorMessage) {
            showNotification(errorMessage, 'error');
        }
        
        return isValid;
    }
    
    /**
     * Gestion de la sélection des objets
     */
    function initItemsSelection() {
        // Boutons quantité +/-
        $(document).on('click', '.nd-qty-minus', function() {
            const itemId = $(this).data('item-id');
            const $input = $('#qty_' + itemId);
            const currentQty = parseInt($input.val()) || 0;
            
            if (currentQty > 0) {
                $input.val(currentQty - 1);
                updateItemCard(itemId, currentQty - 1);
                updateVolumeSummary();
            }
        });
        
        $(document).on('click', '.nd-qty-plus', function() {
            const itemId = $(this).data('item-id');
            const $input = $('#qty_' + itemId);
            const currentQty = parseInt($input.val()) || 0;
            
            $input.val(currentQty + 1);
            updateItemCard(itemId, currentQty + 1);
            updateVolumeSummary();
        });
        
        // Recherche d'objets
        $('#nd-items-search').on('input', function() {
            const search = $(this).val().toLowerCase().trim();
            
            if (search.length === 0) {
                $('.nd-item-card').show();
                $('.nd-category-section').show();
            } else {
                $('.nd-item-card').each(function() {
                    const itemName = $(this).data('item-name').toLowerCase();
                    if (itemName.includes(search)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Masquer les catégories vides
                $('.nd-category-section').each(function() {
                    const visibleItems = $(this).find('.nd-item-card:visible').length;
                    if (visibleItems > 0) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Filtres par catégorie
        $('.nd-category-btn').on('click', function() {
            const category = $(this).data('category');
            
            $('.nd-category-btn').removeClass('nd-active');
            $(this).addClass('nd-active');
            
            if (category === 'all') {
                $('.nd-category-section').show();
            } else {
                $('.nd-category-section').hide();
                $('.nd-category-section[data-category="' + category + '"]').show();
            }
        });
    }
    
    /**
     * Mettre à jour l'apparence d'une carte objet
     */
    function updateItemCard(itemId, quantity) {
        const $card = $('.nd-item-card[data-item-id="' + itemId + '"]');
        
        if (quantity > 0) {
            $card.addClass('nd-selected');
        } else {
            $card.removeClass('nd-selected');
        }
    }
    
    /**
     * Mise à jour du récapitulatif de volume
     */
    function updateVolumeSummary() {
        updateSelectedItems();
        
        const itemsCount = selectedItems.length;
        let totalVolume = 0;
        
        // Calcul correct du volume total
        selectedItems.forEach(item => {
            const volume = parseFloat(item.volume) || 0;
            const quantity = parseInt(item.quantity) || 0;
            totalVolume += (volume * quantity);
        });
        
        // Affichage avec vérification
        $('#items_count').text(itemsCount);
        $('#total_volume').text(isNaN(totalVolume) ? '0.00' : totalVolume.toFixed(2) + ' m³');
        $('#total_volume_hidden').val(isNaN(totalVolume) ? 0 : totalVolume.toFixed(3));
        
        // Debug dans la console
        console.log('Volume total calculé:', totalVolume, 'Items:', itemsCount);
    }
    
    /**
     * Mettre à jour la liste des objets sélectionnés
     */
    function updateSelectedItems() {
        selectedItems = [];
        
        // Objets standards
        $('.nd-qty-input').each(function() {
            const quantity = parseInt($(this).val()) || 0;
            
            if (quantity > 0) {
                const itemId = $(this).data('item-id');
                const $card = $('.nd-item-card[data-item-id="' + itemId + '"]');
                
                selectedItems.push({
                    id: itemId,
                    name: $card.data('item-name'),
                    volume: parseFloat($card.data('item-volume')),
                    quantity: quantity
                });
            }
        });
        
        // Objets personnalisés
        $('.nd-custom-item').each(function() {
            const quantity = parseInt($(this).find('.nd-custom-qty').val()) || 0;
            
            if (quantity > 0) {
                selectedItems.push({
                    id: $(this).data('custom-id'),
                    name: $(this).data('custom-name'),
                    volume: parseFloat($(this).data('custom-volume')),
                    quantity: quantity,
                    custom: true
                });
            }
        });
    }
    
    /**
     * Gestion des objets personnalisés
     */
    function initCustomItems() {
        // Ouvrir le modal
        $('#nd-add-custom-item').on('click', function() {
            $('#nd-custom-item-modal').fadeIn();
            $('#custom_item_name').val('');
            $('#custom_item_volume').val('');
            $('#custom_item_quantity').val(1);
        });
        
        // Fermer le modal
        $('.nd-modal-close').on('click', function() {
            $(this).closest('.nd-modal').fadeOut();
        });
        
        // Sauvegarder l'objet personnalisé
        $('#nd-save-custom-item').on('click', function() {
            const name = $('#custom_item_name').val().trim();
            const volume = parseFloat($('#custom_item_volume').val());
            const quantity = parseInt($('#custom_item_quantity').val()) || 1;
            
            if (!name || !volume || volume <= 0) {
                showNotification('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            addCustomItem(name, volume, quantity);
            $('#nd-custom-item-modal').fadeOut();
        });
    }
    
    /**
     * Ajouter un objet personnalisé
     */
    function addCustomItem(name, volume, quantity) {
        const id = customItemId++;
        
        const $customItem = $('<div>')
            .addClass('nd-custom-item')
            .attr('data-custom-id', id)
            .attr('data-custom-name', name)
            .attr('data-custom-volume', volume)
            .html(`
                <div class="nd-custom-item-header">
                    <span class="nd-custom-item-name">${escapeHtml(name)}</span>
                    <span class="nd-custom-item-volume">${volume.toFixed(2)} m³</span>
                    <button type="button" class="nd-custom-item-remove" data-custom-id="${id}">×</button>
                </div>
                <div class="nd-custom-item-controls">
                    <button type="button" class="nd-qty-btn nd-custom-qty-minus" data-custom-id="${id}">−</button>
                    <input type="number" class="nd-qty-input nd-custom-qty" data-custom-id="${id}" min="1" value="${quantity}" readonly>
                    <button type="button" class="nd-qty-btn nd-custom-qty-plus" data-custom-id="${id}">+</button>
                </div>
            `);
        
        $('#nd-custom-items-list').append($customItem);
        updateVolumeSummary();
    }
    
    /**
     * Gestion des objets personnalisés (quantité et suppression)
     */
    $(document).on('click', '.nd-custom-qty-minus', function() {
        const id = $(this).data('custom-id');
        const $input = $(`.nd-custom-qty[data-custom-id="${id}"]`);
        const currentQty = parseInt($input.val()) || 1;
        
        if (currentQty > 1) {
            $input.val(currentQty - 1);
            updateVolumeSummary();
        }
    });
    
    $(document).on('click', '.nd-custom-qty-plus', function() {
        const id = $(this).data('custom-id');
        const $input = $(`.nd-custom-qty[data-custom-id="${id}"]`);
        const currentQty = parseInt($input.val()) || 1;
        
        $input.val(currentQty + 1);
        updateVolumeSummary();
    });
    
    $(document).on('click', '.nd-custom-item-remove', function() {
        if (confirm('Supprimer cet objet ?')) {
            $(this).closest('.nd-custom-item').remove();
            updateVolumeSummary();
        }
    });
    
    /**
     * Afficher le récapitulatif
     */
    function displaySummary() {
        // Adresses
        $('#recap_address_from').text($('#address_from').val());
        $('#recap_address_to').text($('#address_to').val());
        
        // Étages
        const floorsFrom = parseInt($('#floors_from').val()) || 0;
        const floorsTo = parseInt($('#floors_to').val()) || 0;
        const hasElevatorFrom = $('#has_elevator_from').is(':checked');
        const hasElevatorTo = $('#has_elevator_to').is(':checked');
        
        let floorsTextFrom = '';
        let floorsTextTo = '';
        
        if (floorsFrom > 0) {
            floorsTextFrom = `Étage ${floorsFrom}${hasElevatorFrom ? ' (avec ascenseur)' : ' (sans ascenseur)'}`;
            $('#recap_floors_from').text(floorsTextFrom);
        } else {
            $('#recap_floors_from').text('Rez-de-chaussée');
        }
        
        if (floorsTo > 0) {
            floorsTextTo = `Étage ${floorsTo}${hasElevatorTo ? ' (avec ascenseur)' : ' (sans ascenseur)'}`;
            $('#recap_floors_to').text(floorsTextTo);
        } else {
            $('#recap_floors_to').text('Rez-de-chaussée');
        }
        
        // Distance
        const distance = parseFloat($('#distance').val());
        $('#recap_distance').text(distance.toFixed(2) + ' km');
        
        // Liste des objets
        updateSelectedItems();
        const totalVolume = selectedItems.reduce((sum, item) => sum + (item.volume * item.quantity), 0);
        
        let itemsHtml = '<div class="nd-recap-items-grid">';
        selectedItems.forEach(item => {
            itemsHtml += `
                <div class="nd-recap-item">
                    <span class="nd-recap-item-name">${escapeHtml(item.name)}</span>
                    <span class="nd-recap-item-qty">× ${item.quantity}</span>
                    <span class="nd-recap-item-volume">${(item.volume * item.quantity).toFixed(2)} m³</span>
                </div>
            `;
        });
        itemsHtml += '</div>';
        
        $('#recap_items').html(itemsHtml);
        $('#recap_items_count').text(selectedItems.length);
        $('#recap_total_volume').text(totalVolume.toFixed(2) + ' m³');
        
        // Calcul du prix
        calculatePrice();
    }
    
    /**
     * Calcul du prix
     */
    function calculatePrice() {
        const data = {
            distance: parseFloat($('#distance').val()),
            volume: parseFloat($('#total_volume_hidden').val()),
            floors_from: parseInt($('#floors_from').val()) || 0,
            floors_to: parseInt($('#floors_to').val()) || 0,
            has_elevator_from: $('#has_elevator_from').is(':checked'),
            has_elevator_to: $('#has_elevator_to').is(':checked'),
            need_packing: $('#need_packing').is(':checked'),
            need_insurance: $('#need_insurance').is(':checked')
        };
        
        $.ajax({
            url: novaliaDevis.rest_url + 'calculate',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', novaliaDevis.nonce);
            },
            success: function(response) {
                if (response.success) {
                    const calc = response.calculation;
                    
                    $('#price_distance').text(calc.breakdown.distance.price.toFixed(2) + ' €');
                    $('#price_volume').text(calc.breakdown.volume.price.toFixed(2) + ' €');
                    
                    if (calc.breakdown.floors.price > 0) {
                        $('#price_floors').text(calc.breakdown.floors.price.toFixed(2) + ' €');
                        $('#price_floors_line').show();
                    } else {
                        $('#price_floors_line').hide();
                    }
                    
                    if (calc.breakdown.packing.enabled) {
                        $('#price_packing').text(calc.breakdown.packing.price.toFixed(2) + ' €');
                        $('#price_packing_line').show();
                    } else {
                        $('#price_packing_line').hide();
                    }
                    
                    if (calc.breakdown.insurance.enabled) {
                        $('#price_insurance').text(calc.breakdown.insurance.price.toFixed(2) + ' €');
                        $('#price_insurance_line').show();
                    } else {
                        $('#price_insurance_line').hide();
                    }
                    
                    if (calc.breakdown.fixed_fee > 0) {
                        $('#price_fixed').text(calc.breakdown.fixed_fee.toFixed(2) + ' €');
                        $('#price_fixed_line').show();
                    } else {
                        $('#price_fixed_line').hide();
                    }
                    
                    $('#price_total').text(calc.total.toFixed(2) + ' €');
                }
            },
            error: function() {
                showNotification('Erreur lors du calcul du prix', 'error');
            }
        });
    }
    
    /**
     * Soumission du formulaire
     */
    function initFormSubmit() {
        $('#nd-quote-form').on('submit', function(e) {
            e.preventDefault();
            submitQuote();
        });
        
        $('#nd-submit-btn').on('click', function(e) {
            e.preventDefault();
            submitQuote();
        });
    }
    
    /**
     * Envoyer le devis
     */
    function submitQuote() {
        // Validation finale
        if (!validateStep(3)) {
            return;
        }
        
        const customerName = $('#customer_name').val().trim();
        const customerFirstname = $('#customer_firstname').val().trim();
        const customerEmail = $('#customer_email').val().trim();
        const customerPhone = $('#customer_phone').val().trim();
        const consentRgpd = $('#consent_rgpd').is(':checked');
        
        if (!customerName || !customerFirstname || !customerEmail) {
            showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }
        
        if (!consentRgpd) {
            showNotification('Veuillez accepter la politique de confidentialité', 'error');
            return;
        }
        
        // Préparer les données
        updateSelectedItems();
        
        const quoteData = {
            customer_name: customerName,
            customer_firstname: customerFirstname,
            customer_email: customerEmail,
            customer_phone: customerPhone,
            address_from: $('#address_from').val(),
            address_to: $('#address_to').val(),
            distance: parseFloat($('#distance').val()),
            total_volume: parseFloat($('#total_volume_hidden').val()),
            items: selectedItems,
            floors_from: parseInt($('#floors_from').val()) || 0,
            floors_to: parseInt($('#floors_to').val()) || 0,
            has_elevator_from: $('#has_elevator_from').is(':checked'),
            has_elevator_to: $('#has_elevator_to').is(':checked'),
            need_packing: $('#need_packing').is(':checked'),
            need_insurance: $('#need_insurance').is(':checked')
        };
        
        // Afficher le loader
        $('#nd-loader').fadeIn();
        $('.nd-wizard-form, .nd-wizard-navigation').hide();
        
        // Envoyer la requête
        $.ajax({
            url: novaliaDevis.rest_url + 'quote',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(quoteData),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', novaliaDevis.nonce);
            },
            success: function(response) {
                $('#nd-loader').fadeOut(function() {
                    $('#nd-success-message').fadeIn();
                });
            },
            error: function(xhr) {
                $('#nd-loader').fadeOut();
                $('.nd-wizard-form, .nd-wizard-navigation').show();
                
                let errorMsg = 'Une erreur est survenue';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
            }
        });
    }
    
    /**
     * Afficher une notification
     */
    function showNotification(message, type) {
        const $notification = $('<div>')
            .addClass('nd-notification nd-notification-' + type)
            .html(message);
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.addClass('nd-show');
        }, 100);
        
        setTimeout(function() {
            $notification.removeClass('nd-show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 4000);
    }
    
    /**
     * Échapper le HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
})(jQuery);