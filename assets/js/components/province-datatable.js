/**
 * Province DataTables Manager
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.1.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/province-datatable.js
 * 
 * Description: Komponen untuk mengelola DataTables provinsi.
 *              Handle server-side processing, formatting,
 *              dan integrasi dengan panel UI.
 * 
 * Changelog:
 * 1.1.0 - 2024-12-03
 * - Added cache-aware refresh mechanism
 * - Updated reload behavior to handle cached data
 * - Optimized data refresh after CRUD operations
 * 
 * Dependencies:
 * - jQuery 3.6+
 * - DataTables 1.13+
 * - wilayahToast.js
 */

(function($) {
    'use strict';

    window.ProvinceDataTable = {
        table: null,
        
        init() {
            this.initDataTable();
            this.bindEvents();
        },

        initDataTable() {
            this.table = $('#provinces-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        return {
                            ...d,
                            action: 'handle_province_datatable',
                            nonce: wilayahData.nonce
                        };
                    },
                    error: (xhr, error, thrown) => {
                        wilayahToast.error('Gagal memuat data provinsi');
                        console.error('DataTables Error:', error);
                    }
                },
                columns: [
                    { 
                        data: 'name',
                        title: 'Nama Provinsi'
                    },
                    { 
                        data: 'regency_count',
                        title: 'Jumlah Kab/Kota',
                        className: 'text-center',
                        searchable: false
                    },
                    {
                        data: 'actions',
                        title: 'Aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center nowrap'
                    }
                ],
                order: [[0, 'asc']],
                pageLength: wilayahData.perPage || 10,
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                language: {
                    "processing": "Sedang memproses...",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ data keseluruhan)",
                    "search": "Cari:",
                    "emptyTable": "Tidak ada data yang tersedia",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                responsive: true,
                drawCallback: function(settings) {
                    // Re-bind any necessary events after table redraw
                    // This ensures event handlers work after cache updates
                    if (typeof Province !== 'undefined') {
                        Province.bindActionButtons();
                    }
                }
            });
        },

        bindEvents() {
            const $table = $('#provinces-table');
            
            // View button
            $table.on('click', '.view-province', (e) => {
                const id = $(e.currentTarget).data('id');
                window.location.hash = id;
            });
            
            // Edit button
            $table.on('click', '.edit-province', (e) => {
                const id = $(e.currentTarget).data('id');
                window.location.hash = id;
                Province.switchToEditMode();
            });
            
            // Delete button
            $table.on('click', '.delete-province', (e) => {
                const id = $(e.currentTarget).data('id');
                Province.showDeleteConfirmation(id);
            });

            // Refresh after CRUD operations
            $(document).on('province:created province:updated province:deleted', () => {
                this.refresh();
            });

            // Refresh on panel close to ensure data consistency
            $(document).on('panel:closed', () => {
                this.refresh();
            });
        },

        refresh() {
            // Force reload from server, ignoring cache
            this.table.ajax.reload(null, false);
        },

        /**
         * Refresh with specific row focus
         * Useful after CRUD operations to highlight changed row
         */
        refreshWithFocus(rowId) {
            this.table.ajax.reload((json) => {
                if (rowId) {
                    // Find and highlight updated row
                    const rows = this.table.rows();
                    const indexes = rows.indexes().toArray();
                    for (let i = 0; i < indexes.length; i++) {
                        const row = this.table.row(indexes[i]);
                        const data = row.data();
                        if (data && data.id === rowId) {
                            $(row.node()).addClass('highlight');
                            setTimeout(() => {
                                $(row.node()).removeClass('highlight');
                            }, 2000);
                            break;
                        }
                    }
                }
            }, false);
        },

        getSelectedIds() {
            return this.table.rows({ selected: true }).data()
                .map(item => item.id)
                .toArray();
        },

        destroy() {
            if (this.table) {
                this.table.destroy();
                this.table = null;
            }
        }
    };

})(jQuery);

// Export for modular use
window.ProvinceDataTable = ProvinceDataTable;
