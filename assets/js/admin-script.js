/**
 * Scripts Admin - Novalia Devis
 * 
 * @package NovaliaDevis
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialisation des DataTables si présentes
        if ($.fn.DataTable) {
            $('.nd-datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                pageLength: 25,
                order: [[0, 'desc']]
            });
        }
        
        // Confirmation de suppression
        $('.nd-confirm-delete').on('click', function(e) {
            if (!confirm(ndAdmin.strings.confirm_delete)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Boutons de copie
        $('.nd-copy-btn').on('click', function() {
            const text = $(this).data('copy');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Copié !');
                });
            } else {
                // Fallback pour anciens navigateurs
                const $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                alert('Copié !');
            }
        });
    });
    
})(jQuery);