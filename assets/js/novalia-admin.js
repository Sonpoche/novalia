jQuery(document).ready(function($) {
    'use strict';
    
    // Changement de statut devis
    $('#novalia-change-statut').on('change', function() {
        const devisId = $(this).data('devis-id');
        const newStatut = $(this).val();
        
        if (!confirm('Voulez-vous vraiment changer le statut de ce devis ?')) {
            return;
        }
        
        $.ajax({
            url: novaliaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'novalia_update_statut',
                nonce: novaliaAdmin.nonce,
                devis_id: devisId,
                statut: newStatut
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Statut mis à jour avec succès');
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'Erreur lors de la mise à jour du statut');
            }
        });
    });
    
    // Suppression devis
    $('.novalia-delete-devis').on('click', function(e) {
        e.preventDefault();
        
        const devisId = $(this).data('id');
        const $row = $(this).closest('tr');
        
        if (!confirm('Voulez-vous vraiment supprimer ce devis ? Cette action est irréversible.')) {
            return;
        }
        
        $.ajax({
            url: novaliaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'novalia_delete_devis',
                nonce: novaliaAdmin.nonce,
                devis_id: devisId
            },
            beforeSend: function() {
                $row.addClass('novalia-loading');
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        showNotice('success', 'Devis supprimé avec succès');
                    });
                } else {
                    $row.removeClass('novalia-loading');
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                $row.removeClass('novalia-loading');
                showNotice('error', 'Erreur lors de la suppression');
            }
        });
    });
    
    // Modification item
    $('.novalia-edit-item').on('click', function(e) {
        e.preventDefault();
        
        const itemId = $(this).data('id');
        const $row = $(this).closest('tr');
        const currentNom = $row.find('.item-nom').text();
        const currentVolume = parseFloat($row.find('.item-volume').text());
        
        const newNom = prompt('Nouveau nom de l\'objet:', currentNom);
        if (!newNom) return;
        
        const newVolume = prompt('Nouveau volume (m³):', currentVolume);
        if (!newVolume || isNaN(parseFloat(newVolume))) return;
        
        $.ajax({
            url: novaliaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'novalia_update_item',
                nonce: novaliaAdmin.nonce,
                item_id: itemId,
                nom: newNom,
                categorie: $row.closest('table').prev('h3').text(),
                volume: parseFloat(newVolume)
            },
            beforeSend: function() {
                $row.addClass('novalia-loading');
            },
            success: function(response) {
                $row.removeClass('novalia-loading');
                
                if (response.success) {
                    $row.find('.item-nom').text(response.data.nom);
                    $row.find('.item-volume').text(parseFloat(response.data.volume).toFixed(3));
                    $row.addClass('highlight');
                    
                    setTimeout(function() {
                        $row.removeClass('highlight');
                    }, 2000);
                    
                    showNotice('success', 'Objet mis à jour avec succès');
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                $row.removeClass('novalia-loading');
                showNotice('error', 'Erreur lors de la mise à jour');
            }
        });
    });
    
    // Suppression item
    $('.novalia-delete-item').on('click', function(e) {
        e.preventDefault();
        
        const itemId = $(this).data('id');
        const $row = $(this).closest('tr');
        
        if (!confirm('Voulez-vous vraiment supprimer cet objet ?')) {
            return;
        }
        
        $.ajax({
            url: novaliaAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'novalia_delete_item',
                nonce: novaliaAdmin.nonce,
                item_id: itemId
            },
            beforeSend: function() {
                $row.addClass('novalia-loading');
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        showNotice('success', 'Objet supprimé avec succès');
                    });
                } else {
                    $row.removeClass('novalia-loading');
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                $row.removeClass('novalia-loading');
                showNotice('error', 'Erreur lors de la suppression');
            }
        });
    });
    
    // Confirmation avant suppression en masse
    $('form[action*="delete"]').on('submit', function(e) {
        if (!confirm('Voulez-vous vraiment effectuer cette action ?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-save tarifs après 2 secondes d'inactivité
    let tarifTimeout = null;
    $('.form-table input[name^="tarif"]').on('input', function() {
        const $input = $(this);
        const $form = $input.closest('form');
        
        clearTimeout(tarifTimeout);
        
        tarifTimeout = setTimeout(function() {
            // Optionnel: auto-save via AJAX
            // Pour l'instant on laisse la sauvegarde manuelle
        }, 2000);
    });
    
    // Validation des champs numériques
    $('input[type="number"]').on('change', function() {
        const min = parseFloat($(this).attr('min'));
        const max = parseFloat($(this).attr('max'));
        const value = parseFloat($(this).val());
        
        if (!isNaN(min) && value < min) {
            $(this).val(min);
            showNotice('warning', 'La valeur a été ajustée au minimum autorisé');
        }
        
        if (!isNaN(max) && value > max) {
            $(this).val(max);
            showNotice('warning', 'La valeur a été ajustée au maximum autorisé');
        }
    });
    
    // Recherche en temps réel dans les tableaux
    let searchTimeout = null;
    $('#novalia-search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(function() {
            $('.wp-list-table tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                
                if (rowText.indexOf(searchTerm) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // Statistiques - Animation des chiffres
    function animateValue(element, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.text(value);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Animer les stats au chargement
    if ($('.stat-number').length > 0) {
        $('.stat-number').each(function() {
            const $this = $(this);
            const text = $this.text().trim();
            const numberMatch = text.match(/[\d,.']+/);
            
            if (numberMatch) {
                const number = parseFloat(numberMatch[0].replace(/[,']/g, ''));
                if (!isNaN(number)) {
                    $this.text('0');
                    setTimeout(function() {
                        animateValue($this, 0, number, 1000);
                    }, 100);
                }
            }
        });
    }
    
    // Export CSV (optionnel)
    $('#export-csv').on('click', function(e) {
        e.preventDefault();
        
        let csv = [];
        const rows = $('.wp-list-table tr:visible');
        
        rows.each(function() {
            const row = [];
            $(this).find('th, td').each(function() {
                row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
            });
            csv.push(row.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'novalia-export-' + Date.now() + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
    
    // Confirmation de sortie si formulaire modifié
    let formModified = false;
    
    $('.novalia-admin form input, .novalia-admin form select, .novalia-admin form textarea').on('change', function() {
        formModified = true;
    });
    
    $('.novalia-admin form').on('submit', function() {
        formModified = false;
    });
    
    $(window).on('beforeunload', function() {
        if (formModified) {
            return 'Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter cette page ?';
        }
    });
    
    // Tooltips
    $('[data-tooltip]').on('mouseenter', function() {
        const tooltip = $(this).attr('data-tooltip');
        $(this).attr('title', tooltip);
    });
    
    // Copy to clipboard
    $('.copy-to-clipboard').on('click', function(e) {
        e.preventDefault();
        
        const text = $(this).data('text');
        const $temp = $('<textarea>');
        
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        
        showNotice('success', 'Copié dans le presse-papier');
    });
    
    // Afficher les notices
    function showNotice(type, message) {
        const noticeClass = 'notice-' + type;
        const $notice = $('<div>')
            .addClass('notice ' + noticeClass + ' is-dismissible')
            .html('<p>' + message + '</p>');
        
        $('.wrap').first().prepend($notice);
        
        setTimeout(function() {
            $notice.fadeOut(400, function() {
                $(this).remove();
            });
        }, 4000);
        
        // Bouton de fermeture
        $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Fermer</span></button>');
        
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(400, function() {
                $(this).remove();
            });
        });
    }
    
    // Tri des tableaux
    $('.wp-list-table th').on('click', function() {
        const $table = $(this).closest('table');
        const columnIndex = $(this).index();
        const $rows = $table.find('tbody tr').toArray();
        const isAscending = $(this).hasClass('sorted-asc');
        
        $table.find('th').removeClass('sorted-asc sorted-desc');
        
        $rows.sort(function(a, b) {
            const aValue = $(a).find('td').eq(columnIndex).text().trim();
            const bValue = $(b).find('td').eq(columnIndex).text().trim();
            
            // Essayer de comparer comme des nombres
            const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? bNum - aNum : aNum - bNum;
            }
            
            // Sinon comparer comme des chaînes
            if (isAscending) {
                return bValue.localeCompare(aValue);
            } else {
                return aValue.localeCompare(bValue);
            }
        });
        
        $.each($rows, function(index, row) {
            $table.find('tbody').append(row);
        });
        
        $(this).addClass(isAscending ? 'sorted-desc' : 'sorted-asc');
    });
    
    // Sélection multiple dans les tableaux
    let lastChecked = null;
    
    $('.wp-list-table tbody input[type="checkbox"]').on('click', function(e) {
        if (!lastChecked) {
            lastChecked = this;
            return;
        }
        
        if (e.shiftKey) {
            const $checkboxes = $('.wp-list-table tbody input[type="checkbox"]');
            const start = $checkboxes.index(this);
            const end = $checkboxes.index(lastChecked);
            
            $checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                .prop('checked', lastChecked.checked);
        }
        
        lastChecked = this;
    });
    
    // Raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S pour sauvegarder
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            $('.novalia-admin form').first().submit();
        }
        
        // Escape pour fermer les modals
        if (e.key === 'Escape') {
            $('.novalia-modal').removeClass('show');
        }
    });
    
    // Charger plus de résultats (pagination infinie - optionnel)
    let loading = false;
    let page = 1;
    
    $(window).on('scroll', function() {
        if ($('.novalia-load-more').length === 0) return;
        if (loading) return;
        
        const scrollPosition = $(window).scrollTop() + $(window).height();
        const documentHeight = $(document).height();
        
        if (scrollPosition > documentHeight - 200) {
            loading = true;
            page++;
            
            $.ajax({
                url: novaliaAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'novalia_load_more_devis',
                    nonce: novaliaAdmin.nonce,
                    page: page
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        $('.wp-list-table tbody').append(response.data.html);
                        loading = false;
                        
                        if (!response.data.has_more) {
                            $('.novalia-load-more').remove();
                        }
                    }
                },
                error: function() {
                    loading = false;
                }
            });
        }
    });
    
    // Validation en temps réel
    $('input[type="email"]').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('error');
            showNotice('error', 'Format d\'email invalide');
        } else {
            $(this).removeClass('error');
        }
    });
    
    $('input[type="tel"]').on('blur', function() {
        const tel = $(this).val();
        const telRegex = /^[\d\s\+\-\(\)]+$/;
        
        if (tel && !telRegex.test(tel)) {
            $(this).addClass('error');
            showNotice('error', 'Format de téléphone invalide');
        } else {
            $(this).removeClass('error');
        }
    });
    
    // Auto-refresh stats toutes les 60 secondes (optionnel)
    if ($('.novalia-stats-cards').length > 0) {
        setInterval(function() {
            // Refresh silencieux des statistiques via AJAX
            // À implémenter si nécessaire
        }, 60000);
    }
    
    // Initialisation
    console.log('Novalia Admin JS loaded');
});