/**
 * Initialisation automatique des DataTables pour le projet
 * Version: 1.0.0
 * Compatible avec Bootstrap 5, RTL et multilingue
 */

(function($) {
    'use strict';
    
    // Configuration globale
    const DT_CONFIG = {
        // Langue arabe
        language: {
            url: 'assets/datatable/lang/Arabic.json'
        },
        
        // Direction RTL
        dir: 'rtl',
        
        // Options par défaut
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "الكل"]],
        order: [[0, 'desc']],
        stateSave: true,
        stateDuration: 60 * 60 * 24, // 24 heures
        
        // DOM Layout personnalisé pour Bootstrap 5
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        
        // Boutons d'export
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-outline-secondary btn-sm',
                text: '<i class="fas fa-copy"></i> نسخ',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excel',
                className: 'btn btn-outline-success btn-sm',
                text: '<i class="fas fa-file-excel"></i> Excel',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdf',
                className: 'btn btn-outline-danger btn-sm',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                exportOptions: {
                    columns: ':visible'
                },
                customize: function(doc) {
                    doc.content[1].table.widths = 
                        Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    doc.defaultStyle.alignment = 'right';
                    doc.styles.tableHeader.alignment = 'right';
                }
            },
            {
                extend: 'print',
                className: 'btn btn-outline-info btn-sm',
                text: '<i class="fas fa-print"></i> طباعة',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'colvis',
                className: 'btn btn-outline-primary btn-sm',
                text: '<i class="fas fa-columns"></i> الأعمدة'
            }
        ],
        
        // Styles Bootstrap 5
        drawCallback: function(settings) {
            // Ajout des classes Bootstrap aux éléments de pagination
            $(this).closest('.dataTables_wrapper').find('.dataTables_paginate')
                .addClass('pagination justify-content-center');
            
            $(this).closest('.dataTables_wrapper').find('.dataTables_paginate .paginate_button')
                .addClass('page-item')
                .find('a').addClass('page-link');
            
            // Ajout des classes aux champs de recherche et select
            $(this).closest('.dataTables_wrapper').find('.dataTables_filter input')
                .addClass('form-control form-control-sm');
            
            $(this).closest('.dataTables_wrapper').find('.dataTables_length select')
                .addClass('form-select form-select-sm');
        }
    };

    /**
     * Initialise une DataTable avec des options personnalisées
     * @param {string} tableId - ID de la table
     * @param {object} customOptions - Options personnalisées
     * @returns {DataTable} Instance DataTable
     */
    $.fn.initDataTable = function(customOptions = {}) {
        const table = this;
        
        // Fusion des options
        const options = $.extend(true, {}, DT_CONFIG, customOptions);
        
        // Initialisation avec les boutons
        if (options.buttons && options.buttons.length > 0) {
            options.dom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'pB>>";
        }
        
        // Initialisation de la DataTable
        const dataTable = table.DataTable(options);
        
        // Ajout des boutons d'export à la barre d'outils
        if (options.buttons && options.buttons.length > 0) {
            dataTable.buttons().container()
                .appendTo('#export-buttons-' + table.attr('id'));
        }
        
        // Gestion de la sélection multiple
        table.on('click', 'th.select-checkbox', function() {
            const isChecked = $(this).find('input[type="checkbox"]').prop('checked');
            table.find('td.select-checkbox input[type="checkbox"]').prop('checked', isChecked);
            toggleBulkActions(table);
        });
        
        table.on('click', 'td.select-checkbox input[type="checkbox"]', function() {
            const allChecked = table.find('td.select-checkbox input[type="checkbox"]:checked').length === 
                             table.find('td.select-checkbox input[type="checkbox"]').length;
            table.find('th.select-checkbox input[type="checkbox"]').prop('checked', allChecked);
            toggleBulkActions(table);
        });
        
        return dataTable;
    };
    
    /**
     * Affiche/masque les actions groupées
     * @param {jQuery} table - Table jQuery
     */
    function toggleBulkActions(table) {
        const checkedCount = table.find('td.select-checkbox input[type="checkbox"]:checked').length;
        const bulkActions = $('#bulk-actions-' + table.attr('id'));
        
        if (checkedCount > 0) {
            bulkActions.removeClass('d-none');
            bulkActions.find('.selected-count').text(checkedCount);
        } else {
            bulkActions.addClass('d-none');
        }
    }
    
    /**
     * Initialise automatiquement toutes les tables avec la classe .datatable
     */
    function autoInitDataTables() {
        $('.datatable').each(function() {
            const table = $(this);
            const tableId = table.attr('id');
            const options = {};
            
            // Récupérer les options depuis les attributs data
            if (table.data('page-length')) {
                options.pageLength = parseInt(table.data('page-length'));
            }
            
            if (table.data('ordering') === 'false') {
                options.ordering = false;
            }
            
            if (table.data('searching') === 'false') {
                options.searching = false;
            }
            
            if (table.data('ajax')) {
                options.ajax = table.data('ajax');
                options.serverSide = table.data('server-side') || false;
                options.processing = true;
            }
            
            if (table.data('columns')) {
                options.columns = JSON.parse(table.data('columns'));
            }
            
            // Créer un conteneur pour les boutons d'export
            if (!table.parent().find('#export-buttons-' + tableId).length) {
                table.before(
                    '<div class="row mb-3">' +
                    '   <div class="col-md-6">' +
                    '       <div id="export-buttons-' + tableId + '" class="btn-group"></div>' +
                    '   </div>' +
                    '   <div class="col-md-6 text-md-end">' +
                    '       <div id="bulk-actions-' + tableId + '" class="bulk-actions d-none">' +
                    '           <span class="selected-count me-2">0</span> عنصر محدد' +
                    '           <button class="btn btn-sm btn-outline-success ms-2">' +
                    '               <i class="fas fa-check"></i> تفعيل' +
                    '           </button>' +
                    '           <button class="btn btn-sm btn-outline-warning ms-2">' +
                    '               <i class="fas fa-ban"></i> تعطيل' +
                    '           </button>' +
                    '           <button class="btn btn-sm btn-outline-danger ms-2">' +
                    '               <i class="fas fa-trash"></i> حذف' +
                    '           </button>' +
                    '       </div>' +
                    '   </div>' +
                    '</div>'
                );
            }
            
            // Initialiser la DataTable
            table.initDataTable(options);
            
            // Ajouter la recherche personnalisée
            const searchInput = table.parent().find('.datatable-search');
            if (searchInput.length) {
                searchInput.on('keyup', function() {
                    table.DataTable().search(this.value).draw();
                });
            }
        });
    }
    
    /**
     * Recharger une DataTable spécifique
     * @param {string} tableId - ID de la table
     */
    window.reloadDataTable = function(tableId) {
        const table = $('#' + tableId);
        if ($.fn.DataTable.isDataTable('#' + tableId)) {
            table.DataTable().ajax.reload(null, false);
        }
    };
    
    /**
     * Détruire et réinitialiser une DataTable
     * @param {string} tableId - ID de la table
     */
    window.resetDataTable = function(tableId) {
        const table = $('#' + tableId);
        if ($.fn.DataTable.isDataTable('#' + tableId)) {
            table.DataTable().destroy();
            table.initDataTable();
        }
    };
    
    // Initialisation automatique au chargement du document
    $(document).ready(function() {
        autoInitDataTables();
    });
    
    // Re-initialisation après les requêtes AJAX
    $(document).ajaxComplete(function() {
        autoInitDataTables();
    });
    
})(jQuery);