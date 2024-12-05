/**
 * Province DataTable Handler
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     2.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/province-datatable.js
 * 
 * Description: Komponen untuk mengelola DataTables provinsi.
 *              Menangani server-side processing, panel kanan,
 *              dan integrasi dengan ProvinceForm.
 *              Mengatur state URL dan cache-aware refresh.
 *              Support row highlighting dan dynamic rendering.
 * 
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - ProvinceToast for notifications
 * - Province main component
 * - WordPress AJAX API
 * 
 * Changelog:
 * 2.0.0 - 2024-12-03
 * - Added panel kanan integration
 * - Added URL hash navigation 
 * - Added cache-aware refresh
 * - Added row highlighting
 * - Fixed toast dependency
 * - Improved error handling
 * - Added memory management
 * 
 * Last modified: 2024-12-03 17:30:00
 */

(function($) {
    'use strict';

    const indonesianLanguage = {
        "emptyTable": "Tidak ada data yang tersedia",
        "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
        "infoEmpty": "Menampilkan 0 hingga 0 dari 0 entri",
        "infoFiltered": "(disaring dari _MAX_ total entri)",
        "lengthMenu": "Tampilkan _MENU_ entri",
        "loadingRecords": "Memuat...",
        "processing": "Memproses...",
        "search": "Cari:",
        "zeroRecords": "Tidak ditemukan data yang sesuai",
        "paginate": {
            "first": "Pertama",
            "last": "Terakhir",
            "next": "Selanjutnya",
            "previous": "Sebelumnya"
        }
    };
    
    const ProvinceDataTable = {
        table: null,
        initialized: false,

        init() {
            // Initialize DataTable
            this.initDataTable();
            this.bindEvents();
            this.checkInitialHash();
        },

        initDataTable() {
            if ($.fn.DataTable.isDataTable('#provinces-table')) {
                $('#provinces-table').DataTable().destroy();
            }

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
                        console.error('DataTables Error:', error);
                        ProvinceToast.error('Gagal memuat data provinsi');
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
                language: indonesianLanguage,
                drawCallback: () => {
                    this.bindActionButtons();
                    this.highlightCurrentRow();
                }
            });
        },

        bindEvents() {
            $(window).on('hashchange.province', () => this.handleHashChange());
            
            $(document).on('panel:closed.province', () => {
                window.location.hash = '';
                this.refresh();
            });

            $(document).on('province:created.province province:updated.province', (e, data) => {
                this.refresh();
                this.loadProvincePanel(data.id, data);
            });

            $(document).on('province:deleted.province', () => {
                window.location.hash = '';
                this.refresh();
            });
        },

        bindActionButtons() {
            const $table = $('#provinces-table');
            
            // Remove existing handlers
            $table.off('click', '.view-province, .edit-province, .delete-province');
            
            // View button
            $table.on('click', '.view-province', (e) => {
                const id = $(e.currentTarget).data('id');
                this.loadProvincePanel(id);
            });
            
            // Edit button
            $table.on('click', '.edit-province', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                
                if (!window.EditProvinceForm) {
                    console.error('EditProvinceForm not found');
                    return;
                }
                
                $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_province',
                        id: id,
                        nonce: wilayahData.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            EditProvinceForm.showEditForm(response.data);
                        } else {
                            ProvinceToast.error(response.data?.message || 'Gagal memuat data provinsi');
                        }
                    },
                    error: () => ProvinceToast.error('Gagal menghubungi server')
                });
            });
            
            // Delete button 
            $table.on('click', '.delete-province', (e) => {
                const id = $(e.currentTarget).data('id');
                if (window.Province) {
                    Province.showDeleteConfirmation(id);
                }
            });
        },

        loadProvincePanel(id, data = null) {
            if (!id) return;

            window.location.hash = id;

            if (data) {
                Province.displayData(data);
                return;
            }

            $.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_province',
                    id: id,
                    nonce: wilayahData.nonce
                },
                beforeSend: () => Province.showLoading(),
                success: (response) => {
                    if (response.success) {
                        Province.displayData(response.data);
                    } else {
                        ProvinceToast.error(response.data?.message || 'Gagal memuat data provinsi');
                    }
                },
                error: () => ProvinceToast.error('Gagal menghubungi server'),
                complete: () => Province.hideLoading()
            });
        },

        handleHashChange() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const id = hash.substring(1);
                if (id) {
                    this.loadProvincePanel(id);
                }
            }
        },

        checkInitialHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                this.handleHashChange();
            }
        },

        refresh() {
            if (this.table) {
                this.table.ajax.reload(null, false);
            }
        },

        highlightCurrentRow() {
            const hash = window.location.hash;
            if (hash) {
                const id = hash.substring(1);
                const $row = $(`#provinces-table tr[data-id="${id}"]`);
                if ($row.length) {
                    $row.addClass('highlight');
                    setTimeout(() => $row.removeClass('highlight'), 2000);
                }
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.ProvinceDataTable = ProvinceDataTable;
        ProvinceDataTable.init();
    });

})(jQuery);
