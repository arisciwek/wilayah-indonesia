/**
 * Regency DataTable Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Regency
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/regency/regency-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables kabupaten/kota.
 *              Menangani server-side processing, lazy loading,
 *              dan integrasi dengan komponen form terpisah.
 *              Terintegrasi dengan toast notifications.
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - ProvinceToast for notifications
 * - CreateRegencyForm for handling create operations
 * - EditRegencyForm for handling edit operations
 *
 * Last modified: 2024-12-10
 */
(function($) {
    'use strict';

    const RegencyDataTable = {
        table: null,
        initialized: false,
        currentHighlight: null,
        provinceId: null,

        init(provinceId) {
            if (this.initialized && this.provinceId === provinceId) {
                this.refresh();
                return;
            }

            this.provinceId = provinceId;
            this.initDataTable();
            this.bindEvents();
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
            `);

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
                        ProvinceToast.error('Gagal memuat data kabupaten/kota');
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
                order: [[0, 'asc']], // Default sort by code
                pageLength: wilayahData.perPage || 10,
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
        },

        bindActionButtons() {
            const $table = $('#regency-table');
            $table.off('click', '.view-regency, .edit-regency, .delete-regency');

            // View action
            $table.on('click', '.view-regency', (e) => {
                const id = $(e.currentTarget).data('id');
                this.loadRegencyForView(id);
            });

            // Edit action
            $table.on('click', '.edit-regency', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.loadRegencyForEdit(id);
            });

            // Delete action
            $table.on('click', '.delete-regency', (e) => {
                const id = $(e.currentTarget).data('id');
                this.handleDelete(id);
            });
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

        highlightRow(id) {
            if (this.currentHighlight) {
                $(`tr[data-id="${this.currentHighlight}"]`).removeClass('highlight');
            }

            const $row = $(`tr[data-id="${id}"]`);
            if ($row.length) {
                $row.addClass('highlight');
                this.currentHighlight = id;

                // Scroll into view if needed
                const container = this.table.table().container();
                const rowTop = $row.position().top;
                const containerHeight = $(container).height();
                const scrollTop = $(container).scrollTop();

                if (rowTop < scrollTop || rowTop > scrollTop + containerHeight) {
                    $row[0].scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }
        },

        refresh() {
            if (this.table) {
                this.table.ajax.reload(null, false);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.RegencyDataTable = RegencyDataTable;
    });

})(jQuery);
