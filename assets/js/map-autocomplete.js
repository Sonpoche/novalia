/**
 * Autocomplétion des adresses et gestion de la carte
 * VERSION CORRIGÉE - Carte fonctionne dès le premier affichage
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
        const type = inputId.replace('address_', '');
        
        $input.val(data.label);
        $('#lat_' + type).val(data.lat);
        $('#lon_' + type).val(data.lon);
        $input.siblings('.nd-autocomplete-results').hide();
        
        calculateDistance();
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
                    $('#distance').val(distance);
                    $('#distance_value').text(distance.toFixed(2) + ' km');
                    $('#distance_result').slideDown();
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
     * FIX : Initialiser la carte AVANT le premier toggle
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
                // FIX : Initialiser la carte AVANT de l'afficher
                if (!map) {
                    initMap();
                }
                
                $mapWrapper.slideDown(400, function() {
                    // FIX : Forcer le refresh de la carte après l'animation
                    if (map) {
                        setTimeout(function() {
                            map.invalidateSize();
                            updateMap();
                        }, 50);
                    }
                });
                
                $showText.hide();
                $hideText.show();
            }
        });
    }
    
    /**
     * Initialisation de la carte Leaflet
     * FIX : Meilleure gestion de l'initialisation
     */
    function initMap() {
        try {
            // Vérifier que Leaflet est chargé
            if (typeof L === 'undefined') {
                console.error('Leaflet non chargé');
                return;
            }
            
            // Vérifier que le container existe
            const container = document.getElementById('nd-leaflet-map');
            if (!container) {
                console.error('Container carte introuvable');
                return;
            }
            
            // Créer la carte
            map = L.map('nd-leaflet-map', {
                center: [46.2044, 6.1432],
                zoom: 12,
                scrollWheelZoom: false
            });
            
            // Ajouter le layer OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 18
            }).addTo(map);
            
            console.log('Carte initialisée avec succès');
            
            // FIX : Invalider la taille après un court délai
            setTimeout(function() {
                if (map) {
                    map.invalidateSize();
                    updateMap();
                }
            }, 250);
            
        } catch(error) {
            console.error('Erreur initialisation carte:', error);
        }
    }
    
    /**
     * Mise à jour de la carte avec routing réel
     */
    function updateMap() {
        if (!map) return;
        
        const latFrom = parseFloat($('#lat_from').val());
        const lonFrom = parseFloat($('#lon_from').val());
        const latTo = parseFloat($('#lat_to').val());
        const lonTo = parseFloat($('#lon_to').val());
        
        // Supprimer anciens marqueurs
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
        
        // Route réelle via OSRM (évite le lac)
        if (latFrom && lonFrom && latTo && lonTo) {
            // Appel API OSRM pour routing routier
            $.get('https://router.project-osrm.org/route/v1/driving/' + 
                  lonFrom + ',' + latFrom + ';' + lonTo + ',' + latTo + 
                  '?overview=full&geometries=geojson', 
            function(response) {
                if (response.code === 'Ok' && response.routes.length > 0) {
                    const coords = response.routes[0].geometry.coordinates;
                    const latlngs = coords.map(c => [c[1], c[0]]);
                    
                    routeLine = L.polyline(latlngs, {
                        color: '#2BBBAD',
                        weight: 4,
                        opacity: 0.7
                    }).addTo(map);
                    
                    map.fitBounds(routeLine.getBounds(), { padding: [50, 50] });
                }
            }).fail(function() {
                // Fallback : ligne droite si OSRM échoue
                routeLine = L.polyline([
                    [latFrom, lonFrom],
                    [latTo, lonTo]
                ], {
                    color: '#2BBBAD',
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '10, 10'
                }).addTo(map);
                
                map.fitBounds(bounds, { padding: [50, 50] });
            });
        }
    }
    
    /**
     * Échapper HTML
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
