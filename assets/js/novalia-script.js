jQuery(document).ready(function($) {
    'use strict';
    
    let map = null;
    let currentStep = 1;
    let selectedItems = [];
    let departMarker = null;
    let arriveeMarker = null;
    let routeLayer = null;
    let departCoords = null;
    let arriveeCoords = null;
    let calculatedDistance = 0;
    
    // Initialisation de la carte
    function initMap() {
        if (!map) {
            map = L.map('map-container').setView([46.8182, 8.2275], 8);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        }
    }
    
    // Autocomplete adresse avec Nominatim
    function setupAddressAutocomplete(inputId, suggestionsId) {
        let timeout = null;
        let lastAddress = ''; // Mémoriser la dernière adresse validée
        
        $(inputId).on('input', function() {
            const query = $(this).val();
            const $suggestions = $(suggestionsId);
            
            clearTimeout(timeout);
            
            // Si l'adresse change, réinitialiser les coordonnées
            if (query !== lastAddress) {
                if (inputId === '#adresse-depart') {
                    departCoords = null;
                } else {
                    arriveeCoords = null;
                }
            }
            
            if (query.length < 3) {
                $suggestions.removeClass('show').empty();
                return;
            }
            
            timeout = setTimeout(function() {
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    data: {
                        q: query + ', Switzerland',
                        format: 'json',
                        countrycodes: 'ch',
                        limit: 5,
                        addressdetails: 1
                    },
                    success: function(data) {
                        $suggestions.empty();
                        
                        if (data.length > 0) {
                            data.forEach(function(item) {
                                const displayName = item.display_name;
                                const coords = {lat: parseFloat(item.lat), lon: parseFloat(item.lon)};
                                
                                $('<div>')
                                    .addClass('suggestion-item')
                                    .text(displayName)
                                    .on('click', function() {
                                        lastAddress = displayName;
                                        selectAddress(inputId, displayName, coords);
                                        $suggestions.removeClass('show').empty();
                                    })
                                    .appendTo($suggestions);
                            });
                            
                            $suggestions.addClass('show');
                        }
                    }
                });
            }, 300);
        });
        
        // Détection automatique au blur (quand l'utilisateur quitte le champ)
        $(inputId).on('blur', function() {
            setTimeout(function() {
                const address = $(inputId).val().trim();
                const isDepart = inputId === '#adresse-depart';
                const coords = isDepart ? departCoords : arriveeCoords;
                
                
                // Si l'adresse est différente de la dernière validée et qu'il n'y a pas de coordonnées
                if (address.length > 5 && (!coords || address !== lastAddress)) {
                    autoSelectFirstResult(inputId, address, function(displayName) {
                        lastAddress = displayName;
                    });
                } else if (coords && address === lastAddress) {
                } else {
                }
            }, 500);
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest(inputId + ', ' + suggestionsId).length) {
                $(suggestionsId).removeClass('show').empty();
            }
        });
    }
    
    // Fonction pour formater l'adresse de manière plus courte (style Google Maps)
    
    // Fonction pour sélectionner une adresse
    function selectAddress(inputId, displayName, coords) {
        $(inputId).val(displayName);
        
        if (inputId === '#adresse-depart') {
            departCoords = coords;
            updateMapMarker('depart');
        } else {
            arriveeCoords = coords;
            updateMapMarker('arrivee');
        }
        
        calculateRoute();
    }
    
    // Sélectionner automatiquement le premier résultat trouvé
    function autoSelectFirstResult(inputId, query, callback) {
        
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            data: {
                q: query,
                format: 'json',
                countrycodes: 'ch',
                limit: 1,
                addressdetails: 1
            },
            success: function(data) {
                
                if (data && data.length > 0) {
                    const item = data[0];
                    const coords = {lat: parseFloat(item.lat), lon: parseFloat(item.lon)};
                    selectAddress(inputId, item.display_name, coords);
                    if (callback) callback(item.display_name);
                } else {
                    // Réessayer sans ", Switzerland"
                    retryWithoutCountry(inputId, query, callback);
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Réessayer sans filtrer par pays
    function retryWithoutCountry(inputId, query, callback) {
        
        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            data: {
                q: query,
                format: 'json',
                limit: 1,
                addressdetails: 1
            },
            success: function(data) {
                
                if (data && data.length > 0) {
                    const item = data[0];
                    const coords = {lat: parseFloat(item.lat), lon: parseFloat(item.lon)};
                    selectAddress(inputId, item.display_name, coords);
                    if (callback) callback(item.display_name);
                } else {
                    alert('Impossible de trouver cette adresse. Veuillez utiliser l\'autocomplétion.');
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Mise à jour des marqueurs sur la carte
    function updateMapMarker(type) {
        if (!map) return;
        
        if (type === 'depart' && departCoords) {
            if (departMarker) {
                map.removeLayer(departMarker);
            }
            
            // Créer un marqueur vert pour le départ
            const departIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            departMarker = L.marker([departCoords.lat, departCoords.lon], {icon: departIcon})
                .addTo(map)
                .bindPopup(`
                    <div style="min-width: 200px;">
                        <strong style="color: #1A2332; display: block; margin-bottom: 8px; font-size: 14px;">📍 Départ</strong>
                        <p style="margin: 0; font-size: 12px; color: #555;">${$('#adresse-depart').val()}</p>
                    </div>
                `)
                .openPopup();
        }
        
        if (type === 'arrivee' && arriveeCoords) {
            if (arriveeMarker) {
                map.removeLayer(arriveeMarker);
            }
            
            // Créer un marqueur rouge pour l'arrivée
            const arriveeIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            arriveeMarker = L.marker([arriveeCoords.lat, arriveeCoords.lon], {icon: arriveeIcon})
                .addTo(map)
                .bindPopup(`
                    <div style="min-width: 200px;">
                        <strong style="color: #1A2332; display: block; margin-bottom: 8px; font-size: 14px;">🏁 Arrivée</strong>
                        <p style="margin: 0; font-size: 12px; color: #555;">${$('#adresse-arrivee').val()}</p>
                    </div>
                `)
                .openPopup();
        }
        
        if (departCoords && arriveeCoords) {
            const bounds = L.latLngBounds(
                [departCoords.lat, departCoords.lon],
                [arriveeCoords.lat, arriveeCoords.lon]
            );
            map.fitBounds(bounds, {padding: [50, 50]});
        } else if (departCoords) {
            map.setView([departCoords.lat, departCoords.lon], 13);
        } else if (arriveeCoords) {
            map.setView([arriveeCoords.lat, arriveeCoords.lon], 13);
        }
    }
    
    // Calcul de la route avec OSRM
    function calculateRoute() {
        if (!departCoords || !arriveeCoords) {
            return;
        }
        
        
        const url = `https://router.project-osrm.org/route/v1/driving/${departCoords.lon},${departCoords.lat};${arriveeCoords.lon},${arriveeCoords.lat}?overview=full&geometries=geojson`;
        
        
        $.ajax({
            url: url,
            success: function(data) {
                
                if (data.code === 'Ok' && data.routes.length > 0) {
                    const route = data.routes[0];
                    calculatedDistance = (route.distance / 1000).toFixed(2);
                    
                    
                    $('#distance-display').text(calculatedDistance + ' km');
                    
                    if (routeLayer) {
                        map.removeLayer(routeLayer);
                    }
                    
                    routeLayer = L.geoJSON(route.geometry, {
                        style: {
                            color: '#2BBBAD',
                            weight: 5,
                            opacity: 0.7
                        }
                    }).addTo(map);
                    
                    validateStep1();
                } else {
                    $('#distance-display').text('Calcul impossible');
                }
            },
            error: function(xhr, status, error) {
                $('#distance-display').text('Erreur de calcul');
            }
        });
    }
    
    // Validation étape 1
    function validateStep1() {
        const hasDepart = $('#adresse-depart').val().length > 0 && departCoords;
        const hasArrivee = $('#adresse-arrivee').val().length > 0 && arriveeCoords;
        const hasDate = $('#date-demenagement').val().length > 0;
        const hasDistance = calculatedDistance > 0;
        const hasTypeDepart = $('#type-logement-depart').val().length > 0;
        const hasTypeArrivee = $('#type-logement-arrivee').val().length > 0;
        
        if (hasDepart && hasArrivee && hasDate && hasDistance && hasTypeDepart && hasTypeArrivee) {
            $('#btn-step-1').prop('disabled', false);
        } else {
            $('#btn-step-1').prop('disabled', true);
        }
    }
    
    // Navigation entre étapes
    function goToStep(step) {
        $('.novalia-form-step').hide();
        $('#step-' + step).show();
        
        $('.novalia-step').removeClass('active completed');
        
        for (let i = 1; i < step; i++) {
            $('.novalia-step[data-step="' + i + '"]').addClass('completed');
        }
        
        $('.novalia-step[data-step="' + step + '"]').addClass('active');
        
        currentStep = step;
        
        // Scroll vers le haut du formulaire
        var $form = $('.novalia-demenagement-form');
        if ($form.length > 0) {
            $('html, body').animate({
                scrollTop: $form.offset().top - 100
            }, 500);
        }
        
        if (step === 2 && !map) {
            setTimeout(initMap, 100);
        }
    }
    
    // Gestion des catégories (accordéon)
    $(document).on('click', '.category-header', function() {
        $(this).parent('.novalia-category').toggleClass('open');
    });
    
    // Gestion des items
    $(document).on('click', '.item-plus', function() {
        const $item = $(this).closest('.novalia-item');
        const $quantity = $item.find('.item-quantity');
        const quantity = parseInt($quantity.text()) + 1;
        
        $quantity.text(quantity);
        $item.find('.item-minus').prop('disabled', false);
        
        updateSelectedItems($item, quantity);
        updateVolumeDisplay();
    });
    
    $(document).on('click', '.item-minus', function() {
        const $item = $(this).closest('.novalia-item');
        const $quantity = $item.find('.item-quantity');
        const quantity = parseInt($quantity.text()) - 1;
        
        if (quantity >= 0) {
            $quantity.text(quantity);
            
            if (quantity === 0) {
                $(this).prop('disabled', true);
            }
            
            updateSelectedItems($item, quantity);
            updateVolumeDisplay();
        }
    });
    
    // Mise à jour des items sélectionnés
    function updateSelectedItems($item, quantity) {
        const itemId = $item.data('item-id');
        const itemName = $item.find('.item-name').text();
        const itemVolume = parseFloat($item.data('volume'));
        const itemCategory = $item.data('category');
        
        const existingIndex = selectedItems.findIndex(item => item.id === itemId);
        
        if (quantity > 0) {
            if (existingIndex >= 0) {
                selectedItems[existingIndex].quantite = quantity;
            } else {
                selectedItems.push({
                    id: itemId,
                    nom: itemName,
                    volume: itemVolume,
                    quantite: quantity,
                    categorie: itemCategory
                });
            }
        } else {
            if (existingIndex >= 0) {
                selectedItems.splice(existingIndex, 1);
            }
        }
    }
    
    // Ajout d'un item personnalisé
    $('#btn-add-custom').on('click', function() {
        const nom = $('#custom-item-name').val().trim();
        const volume = parseFloat($('#custom-item-volume').val());
        const categorie = $('#custom-item-category').val();
        
        if (!nom || !volume || volume <= 0 || !categorie) {
            alert('Veuillez remplir tous les champs et sélectionner une pièce');
            return;
        }
        
        selectedItems.push({
            id: 'custom-' + Date.now(),
            nom: nom,
            volume: volume,
            quantite: 1,
            categorie: categorie,
            is_custom: true
        });
        
        $('#custom-item-name').val('');
        $('#custom-item-volume').val('');
        $('#custom-item-category').val('');
        
        updateVolumeDisplay();
    });
    
    // Mise à jour de l'affichage du volume
    function updateVolumeDisplay() {
        let totalVolume = 0;
        const volumeByCategory = {};
        
        selectedItems.forEach(function(item) {
            const itemTotalVolume = item.volume * item.quantite;
            totalVolume += itemTotalVolume;
            
            if (!volumeByCategory[item.categorie]) {
                volumeByCategory[item.categorie] = 0;
            }
            volumeByCategory[item.categorie] += itemTotalVolume;
        });
        
        $('#volume-total-display').text(totalVolume.toFixed(2));
        $('#items-count').text(selectedItems.length);
        
        const $volumeByCategory = $('#volume-by-category');
        $volumeByCategory.empty();
        
        for (const [categorie, volume] of Object.entries(volumeByCategory)) {
            $('<div>')
                .addClass('volume-category-item')
                .html('<span>' + categorie + '</span><span>' + volume.toFixed(2) + ' m³</span>')
                .appendTo($volumeByCategory);
        }
    }
    
    // Bouton suivant étape 1
    $('#btn-step-1').on('click', function() {
        goToStep(2);
    });
    
    // Bouton suivant étape 2
    $('#btn-step-2').on('click', function() {
        if (selectedItems.length === 0) {
            alert('Veuillez sélectionner au moins un objet');
            return;
        }
        
        generateRecap();
        goToStep(3);
    });
    
    // Boutons précédent
    $('.btn-prev').on('click', function() {
        const prevStep = parseInt($(this).data('prev'));
        goToStep(prevStep);
    });
    
    // Génération du récapitulatif
    function generateRecap() {
        $('#recap-depart').text($('#adresse-depart').val());
        $('#recap-arrivee').text($('#adresse-arrivee').val());
        $('#recap-distance').text(calculatedDistance + ' km');
        $('#recap-date').text(formatDate($('#date-demenagement').val()));
        
        // Infos départ
        const typeLogementDepart = $('#type-logement-depart').val() || 'Non spécifié';
        const etagesDepart = $('#etages-depart').val() || '0';
        const ascenseurDepart = $('#ascenseur-depart').is(':checked') ? 'Oui' : 'Non';
        
        $('#recap-type-depart').text(typeLogementDepart.charAt(0).toUpperCase() + typeLogementDepart.slice(1));
        $('#recap-etage-depart').text(etagesDepart);
        $('#recap-ascenseur-depart').text(ascenseurDepart);
        
        // Infos arrivée
        const typeLogementArrivee = $('#type-logement-arrivee').val() || 'Non spécifié';
        const etagesArrivee = $('#etages-arrivee').val() || '0';
        const ascenseurArrivee = $('#ascenseur-arrivee').is(':checked') ? 'Oui' : 'Non';
        
        $('#recap-type-arrivee').text(typeLogementArrivee.charAt(0).toUpperCase() + typeLogementArrivee.slice(1));
        $('#recap-etage-arrivee').text(etagesArrivee);
        $('#recap-ascenseur-arrivee').text(ascenseurArrivee);
        
        const totalVolume = selectedItems.reduce((sum, item) => sum + (item.volume * item.quantite), 0);
        $('#recap-volume').text(totalVolume.toFixed(2) + ' m³');
        
        const $itemsContainer = $('#recap-items-container');
        $itemsContainer.empty();
        
        const itemsByCategory = {};
        let totalItems = 0;
        
        selectedItems.forEach(function(item) {
            if (!itemsByCategory[item.categorie]) {
                itemsByCategory[item.categorie] = [];
            }
            itemsByCategory[item.categorie].push(item);
            totalItems += parseInt(item.quantite);
        });
        
        // Mettre à jour le badge de compteur
        $('#recap-items-count').text(totalItems + ' objet' + (totalItems > 1 ? 's' : ''));
        
        for (const [categorie, items] of Object.entries(itemsByCategory)) {
            const $categoryDiv = $('<div>').addClass('recap-category');
            $('<h4>').text(categorie).appendTo($categoryDiv);
            
            const $itemsList = $('<div>').addClass('recap-category-items');
            items.forEach(function(item) {
                $('<div>')
                    .addClass('recap-item-detail')
                    .text(item.quantite + 'x ' + item.nom + ' (' + (item.volume * item.quantite).toFixed(3) + ' m³)')
                    .appendTo($itemsList);
            });
            
            $itemsList.appendTo($categoryDiv);
            $categoryDiv.appendTo($itemsContainer);
        }
    }
    
    // Gestion du type de déménagement
    $('input[name="type_demenagement"]').on('change', function() {
        if ($(this).val() === 'complet') {
            $('#cartons-section').slideDown();
        } else {
            $('#cartons-section').slideUp();
        }
    });
    
    // Soumission du devis
    $('#btn-submit-devis').on('click', function() {
        const nomClient = $('#nom-client').val().trim();
        const emailClient = $('#email-client').val().trim();
        const telephoneClient = $('#telephone-client').val().trim();
        
        if (!nomClient || !emailClient || !telephoneClient) {
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        const typeDemenagement = $('input[name="type_demenagement"]:checked').val();
        const nombreCartons = typeDemenagement === 'complet' ? parseInt($('#nombre-cartons').val()) : 0;
        
        // Récupération des étages et ascenseurs (indépendants)
        const ascenseurDepart = $('#ascenseur-depart').is(':checked');
        const ascenseurArrivee = $('#ascenseur-arrivee').is(':checked');
        const etagesDepart = parseInt($('#etages-depart').val()) || 0;
        const etagesArrivee = parseInt($('#etages-arrivee').val()) || 0;
        
        // Nouvelles données logement
        const typeLogementDepart = $('#type-logement-depart').val();
        const typeLogementArrivee = $('#type-logement-arrivee').val();
        
        const data = {
            action: 'novalia_submit_devis',
            nonce: novaliaAjax.nonce,
            nom_client: nomClient,
            email_client: emailClient,
            telephone_client: telephoneClient,
            adresse_depart: $('#adresse-depart').val(),
            adresse_arrivee: $('#adresse-arrivee').val(),
            distance: calculatedDistance,
            date_demenagement: $('#date-demenagement').val(),
            type_demenagement: typeDemenagement,
            nombre_cartons: nombreCartons,
            etages_depart: etagesDepart,
            etages_arrivee: etagesArrivee,
            ascenseur_depart: ascenseurDepart,
            ascenseur_arrivee: ascenseurArrivee,
            type_logement_depart: typeLogementDepart,
            type_logement_arrivee: typeLogementArrivee,
            items: selectedItems.map(item => ({
                item_id: typeof item.id === 'string' && item.id.startsWith('custom-') ? null : item.id,
                nom: item.nom,
                categorie: item.categorie,
                volume: item.volume,
                quantite: item.quantite,
                is_custom: item.is_custom || false
            }))
        };
        
        $(this).prop('disabled', true).text('Envoi en cours...');
        
        $.ajax({
            url: novaliaAjax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('Réponse serveur:', response);
                
                if (response.success) {
                    // Mettre à jour le message de succès selon le type de déménagement
                    if (typeDemenagement === 'complet') {
                        $('#success-devis-type').text('vos devis');
                        $('#success-devis-details').html('Nous vous avons envoyé <strong>2 devis</strong>: un pour le déménagement standard et un pour le déménagement complet.');
                    } else {
                        $('#success-devis-type').text('votre devis');
                        $('#success-devis-details').html('Nous vous avons envoyé votre <strong>devis de déménagement standard</strong>.');
                    }
                    
                    goToStep('success');
                } else {
                    const errorMsg = response.data && response.data.message ? response.data.message : 'Erreur inconnue';
                    console.log('Erreur détectée:', errorMsg);
                    console.log('response.data:', response.data);
                    alert('Erreur: ' + errorMsg);
                    $('#btn-submit-devis').prop('disabled', false).text('Recevoir mon devis');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', {xhr, status, error});
                console.error('Réponse complète:', xhr.responseText);
                alert('Erreur lors de l\'envoi du devis. Veuillez réessayer.');
                $('#btn-submit-devis').prop('disabled', false).text('Recevoir mon devis');
            }
        });
    });
    
    // Format de date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('fr-CH', options);
    }
    
    // Gestion de l'accordéon des objets dans le récap - NOUVEAU
    $('#toggle-items-recap').on('click', function() {
        const $button = $(this);
        const $content = $('#recap-items-container');
        
        $button.toggleClass('active');
        
        if ($content.hasClass('open')) {
            $content.removeClass('open').css('max-height', '0');
        } else {
            $content.addClass('open');
            // Calcul de la hauteur réelle
            const scrollHeight = $content[0].scrollHeight;
            $content.css('max-height', scrollHeight + 'px');
        }
    });
    
    // Validation étape 1 au changement
    $('#adresse-depart, #adresse-arrivee, #date-demenagement, #type-logement-depart, #type-logement-arrivee').on('change', function() {
        // Vérifier si les adresses ont des coordonnées
        checkAndFixAddresses();
        validateStep1();
    });
    
    // Fonction pour vérifier et corriger les adresses sans coordonnées
    function checkAndFixAddresses() {
        const departAddress = $('#adresse-depart').val().trim();
        const arriveeAddress = $('#adresse-arrivee').val().trim();
        
        // Vérifier départ
        if (departAddress.length > 5 && !departCoords) {
            autoSelectFirstResult('#adresse-depart', departAddress);
        }
        
        // Vérifier arrivée
        if (arriveeAddress.length > 5 && !arriveeCoords) {
            autoSelectFirstResult('#adresse-arrivee', arriveeAddress);
        }
    }
    
    // Initialisation
    initMap();
    setupAddressAutocomplete('#adresse-depart', '#suggestions-depart');
    setupAddressAutocomplete('#adresse-arrivee', '#suggestions-arrivee');
    
    // Date minimum = aujourd'hui
    const today = new Date().toISOString().split('T')[0];
    $('#date-demenagement').attr('min', today);
    
    // Ouvrir le calendrier au clic sur le champ (pas seulement sur l'icône)
    $('#date-demenagement').on('click', function() {
        this.showPicker();
    });
    
    // Bouton "Refaire mon estimation"
    $('#btn-refaire-estimation').on('click', function() {
        // Réinitialiser le formulaire
        location.reload();
    });
});