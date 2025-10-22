/**
 * Autocomplétion des adresses et gestion de la carte
 * Utilise l'API Nominatim (OpenStreetMap) et Leaflet
 * 
 * @package NovaliaDevis
 */

(function($) {
    'use strict';
    
    let map = null;
    let markers = {
        from: null,
        to: null
    };
    let routeLine = null;
    
    /**
     * Initialisation
     */
    $(document).ready(function() {
        initAddressAutocomplete();
        initMapToggle();
    });
    
    /**
     * Autocomplétion des adresses
     */
    function initAddressAutocomplete() {
        let searchTimeout;
        
        // Gestion de l'autocomplétion pour les deux champs
        $('.nd-address-autocomplete').each(function() {
            const $input = $(this);
            const inputId = $input.attr('id');
            const $resultsContainer = $('#autocomplete_' + inputId.replace('address_', ''));
            
            $input.on('input', function() {
                const query = $(this).val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 3) {
                    $resultsContainer.hide().empty();
                    return;
                }
                
                searchTimeout = setTimeout(function() {
                    searchAddress(query, $resultsContainer, $input);
                }, 300);
            });
            
            // Fermer les résultats si on clique ailleurs
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.nd-input-wrapper').length) {
                    $('.nd-autocomplete-results').hide();
                }
            });
        });
    }
    
    /**
     * Recherche d'adresse via Nominatim
     */
    function searchAddress(query, $container, $input) {
        const apiUrl = novaliaDevis.rest_url + 'autocomplete';
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: {
                query: query
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', novaliaDevis.nonce);
            },
            success: function(response) {
                if (response.success && response.suggestions.length > 0) {
                    displaySuggestions(response.suggestions, $container, $input);
                } else {
                    $container.hide().empty();
                }
            },
            error: function() {
                console.error('Erreur lors de la recherche d\'adresse');
            }
        });
    }
    
    /**
     * Affichage des suggestions
     */
    function displaySuggestions(suggestions, $container, $input) {
        $container.empty();
        
        suggestions.forEach(function(suggestion) {
            const $item = $('<div>')
                .addClass('nd-autocomplete-item')
                .html('<strong>' + escapeHtml(suggestion.label) + '</strong>')
                .data('suggestion', suggestion);
            
            $item.on('click', function() {
                const data = $(this).data('suggestion');
                selectAddress(data, $input);
            });
            
            $container.append($item);
        });
        
        $container.show();
    }
    
    /**
     * Sélection d'une adresse
     */
    function selectAddress(data, $input) {
        const inputId = $input.attr('id');
        const type = inputId.replace('address_', ''); // 'from' ou 'to'
        
        // Remplir le champ
        $input.val(data.label);
        
        // Stocker les coordonnées
        $('#lat_' + type).val(data.lat);
        $('#lon_' + type).val(data.lon);
        
        // Fermer les suggestions
        $input.siblings('.nd-autocomplete-results').hide();
        
        // Calculer la distance si les deux adresses sont renseignées
        calculateDistance();
        
        // Mettre à jour la carte
        updateMap();
    }
    
    /**
     * Calcul de la distance
     */
    function calculateDistance() {
        const latFrom = parseFloat($('#lat_from').val());
        const lonFrom = parseFloat($('#lon_from').val());
        const latTo = parseFloat($('#lat_to').val());
        const lonTo = parseFloat($('#lon_to').val());
        
        if (!latFrom || !lonFrom || !latTo || !lonTo) {
            return;
        }
        
        const apiUrl = novaliaDevis.rest_url + 'distance';
        
        $.ajax({
            url: apiUrl,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                address_from: $('#address_from').val(),
                address_to: $('#address_to').val()
            }),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', novaliaDevis.nonce);
            },
            success: function(response) {
                if (response.success) {
                    const distance = response.distance;
                    
                    // Afficher la distance
                    $('#distance').val(distance);
                    $('#distance_value').text(distance.toFixed(2) + ' km');
                    $('#distance_result').slideDown();
                    
                    // Afficher le bouton de la carte
                    $('#nd-map').show();
                }
            },
            error: function() {
                console.error('Erreur lors du calcul de la distance');
            }
        });
    }
    
    /**
     * Initialisation du toggle de la carte
     */
    function initMapToggle() {
        $('#nd-toggle-map').on('click', function() {
            const $mapWrapper = $('.nd-map-wrapper');
            const $showText = $(this).find('.nd-show-map');
            const $hideText = $(this).find('.nd-hide-map');
            
            if ($mapWrapper.is(':visible')) {
                $mapWrapper.slideUp();
                $showText.show();
                $hideText.hide();
            } else {
                $mapWrapper.slideDown();
                $showText.hide();
                $hideText.show();
                
                // Initialiser la carte si nécessaire
                if (!map) {
                    initMap();
                }
            }
        });
    }
    
    /**
     * Initialisation de la carte Leaflet (lazy loading)
     */
    function initMap() {
        // Créer la carte centrée sur Genève, Suisse
        map = L.map('nd-leaflet-map').setView([46.2044, 6.1432], 12);
        
        // Ajouter le layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
            loading: 'lazy' // Lazy loading des tuiles
        }).addTo(map);
        
        // Forcer le redimensionnement après chargement
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
        
        // Mettre à jour avec les adresses actuelles
        updateMap();
    }
    
    /**
     * Mise à jour de la carte
     */
    function updateMap() {
        if (!map) return;
        
        const latFrom = parseFloat($('#lat_from').val());
        const lonFrom = parseFloat($('#lon_from').val());
        const latTo = parseFloat($('#lat_to').val());
        const lonTo = parseFloat($('#lon_to').val());
        
        // Supprimer les anciens marqueurs et ligne
        if (markers.from) map.removeLayer(markers.from);
        if (markers.to) map.removeLayer(markers.to);
        if (routeLine) map.removeLayer(routeLine);
        
        const bounds = [];
        
        // Marqueur départ
        if (latFrom && lonFrom) {
            markers.from = L.marker([latFrom, lonFrom], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            markers.from.bindPopup('<strong>Départ</strong><br>' + $('#address_from').val());
            bounds.push([latFrom, lonFrom]);
        }
        
        // Marqueur arrivée
        if (latTo && lonTo) {
            markers.to = L.marker([latTo, lonTo], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            markers.to.bindPopup('<strong>Arrivée</strong><br>' + $('#address_to').val());
            bounds.push([latTo, lonTo]);
        }
        
        // Tracer la ligne entre les deux points
        if (latFrom && lonFrom && latTo && lonTo) {
            routeLine = L.polyline([
                [latFrom, lonFrom],
                [latTo, lonTo]
            ], {
                color: '#3498db',
                weight: 3,
                opacity: 0.7
            }).addTo(map);
            
            // Ajuster la vue pour afficher les deux points
            map.fitBounds(bounds, { padding: [50, 50] });
        }
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
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
})(jQuery);