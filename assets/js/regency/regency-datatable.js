/**
 * Regency DataTable Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Regency
 * @version     1.1.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/regency/regency-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables kabupaten/kota.
 *              Includes state management, export functions,
 *              dan error handling yang lebih baik.
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - ProvinceToast for notifications
 *
 * Changelog:
 * 1.1.0 - 2024-12-10
 * - Added state management
 * - Added export functionality
 * - Enhanced error handling
 * - Improved loading states
 */

(function($) {
    'use strict';

    const RegencyDataTable = {
        table: null,
        initialized: false,
        currentHighlight: null,
        provinceId: null,
        $container: null,
        $tableContainer: null,
        $loadingState: null,
        $emptyState: null,
        $errorState: null,

        init(provinceId) {
            // Cache DOM elements
            this.$container = $('#regency-list');
            this.$tableContainer = this.$container.find('.wi-table-container');
            this.$loadingState = this.$container.find('.wi-loading-state');
            this.$emptyState = this.$container.find('.wi-empty-state');
            this.$errorState = this.$container.find('.wi-error-state');

            if (this.initialized && this.provinceId === provinceId) {
                this.refresh();
                return;
            }

            this.provinceId = provinceId;
            this.showLoading();
            this.initDataTable();
            this.bindEvents();
        },

        showLoading() {
            this.$tableContainer.hide();
            this.$emptyState.hide();
            this.$errorState.hide();
            this.$loadingState.show();
        },

        showEmpty() {
            this.$tableContainer.hide();
            this.$loadingState.hide();
            this.$errorState.hide();
            this.$emptyState.show();
        },

        showError() {
            this.$tableContainer.hide();
            this.$loadingState.hide();
            this.$emptyState.hide();
            this.$errorState.show();
        },

        showTable() {
            this.$loadingState.hide();
            this.$emptyState.hide();
            this.$errorState.hide();
            this.$tableContainer.show();
        },

        initDataTable() {
            if ($.fn.DataTable.isDataTable('#regency-table')) {
                $('#regency-table').DataTable().destroy();
            }

            // Initialize clean table structure
            $('#regency-table').empty().html(`
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Aksi</th>
                    </tr>
                </tfoot>
            `);

            const self = this;
            this.table = $('#regency-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: (d) => {
                        return {
                            ...d,
                            action: 'handle_regency_datatable',
                            province_id: this.provinceId,
                            nonce: wilayahData.nonce
                        };
                    },
                    error: (xhr, error, thrown) => {
                        console.error('DataTables Error:', error);
                        this.showError();
                    },
                    dataSrc: function(response) {
                        if (!response.data || response.data.length === 0) {
                            self.showEmpty();
                        } else {
                            self.showTable();
                        }
                        return response.data;
                    }
                },
                columns: [
                    {
                        data: 'code',
                        title: 'Kode',
                        width: '20px'
                    },
                    {
                        data: 'name',
                        title: 'Nama'
                    },
                    {
                        data: 'type',
                        title: 'Tipe',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1);
                        }
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
                dom: 'lfrtip',
                language: {
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
                },
                drawCallback: (settings) => {
                    this.bindActionButtons();
                },
                createdRow: (row, data) => {
                    $(row).attr('data-id', data.id);
                }
            });

            this.initialized = true;
        },

        bindEvents() {
            // CRUD event listeners
            $(document)
                .off('regency:created.datatable regency:updated.datatable regency:deleted.datatable')
                .on('regency:created.datatable regency:updated.datatable regency:deleted.datatable',
                    () => this.refresh());

            // Add button handler
            $('#add-regency-btn').off('click').on('click', () => {
                if (window.CreateRegencyForm) {
                    window.CreateRegencyForm.showModal(this.provinceId);
                }
            });

            // Reload button handler
            this.$errorState.find('.reload-table').off('click').on('click', () => {
                this.refresh();
            });

            // Export handlers
            $('.export-excel').off('click').on('click', () => this.exportData('excel'));
            $('.export-pdf').off('click').on('click', () => this.exportData('pdf'));
        },

        async exportData(type) {
            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'export_regency',
                        province_id: this.provinceId,
                        type: type,
                        nonce: wilayahData.nonce
                    }
                });

                if (response.success && response.data.url) {
                    window.location.href = response.data.url;
                } else {
                    ProvinceToast.error('Gagal mengekspor data');
                }
            } catch (error) {
                console.error('Export error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            }
        },

        async loadRegencyForView(id) {
            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_regency',
                        id: id,
                        nonce: wilayahData.nonce
                    }
                });

                if (response.success) {
                    // Trigger event for view handling
                    $(document).trigger('regency:view', [response.data]);
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memuat data kabupaten/kota');
                }
            } catch (error) {
                console.error('Load regency error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            }
        },

        async loadRegencyForEdit(id) {
            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_regency',
                        id: id,
                        nonce: wilayahData.nonce
                    }
                });

                if (response.success) {
                    if (window.EditRegencyForm) {
                        window.EditRegencyForm.showEditForm(response.data);
                    } else {
                        ProvinceToast.error('Komponen form edit tidak tersedia');
                    }
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memuat data kabupaten/kota');
                }
            } catch (error) {
                console.error('Load regency error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            }
        },

        async handleDelete(id) {
            if (!id) return;

            // Show confirmation modal
            WIModal.show({
                title: 'Konfirmasi Hapus',
                message: 'Yakin ingin menghapus kabupaten/kota ini? Aksi ini tidak dapat dibatalkan.',
                icon: 'trash',
                type: 'danger',
                confirmText: 'Hapus',
                confirmClass: 'button-danger',
                cancelText: 'Batal',
                onConfirm: async () => {
                    try {
                        const response = await $.ajax({
                            url: wilayahData.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'delete_regency',
                                id: id,
                                nonce: wilayahData.nonce
                            }
                        });

                        if (response.success) {
                            ProvinceToast.success('Kabupaten/kota berhasil dihapus');
                            this.refresh();
                            $(document).trigger('regency:deleted', [id]);
                        } else {
                            ProvinceToast.error(response.data?.message || 'Gagal menghapus kabupaten/kota');
                        }
                    } catch (error) {
                        console.error('Delete regency error:', error);
                        ProvinceToast.error('Gagal menghubungi server');
                    }
                }
            });
        },

        refresh() {
            this.showLoading();
            if (this.table) {
                this.table.ajax.reload(() => {
                    // Check if there's data after reload
                    const info = this.table.page.info();
                    if (info.recordsTotal === 0) {
                        this.showEmpty();
                    } else {
                        this.showTable();
                    }
                }, false);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.RegencyDataTable = RegencyDataTable;
    });

})(jQuery);
