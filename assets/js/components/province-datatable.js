
/**
 * Province DataTable Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.2
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/components/province-datatable.js
 *
 * Description: Komponen untuk mengelola DataTables provinsi.
 *              Menangani server-side processing, panel kanan,
 *              dan integrasi dengan komponen form terpisah.
 *
 * Form Integration:
 * - Create form handling sudah dipindahkan ke create-province-form.js
 * - Component ini hanya menyediakan method refresh() untuk update table
 * - Event 'province:created' digunakan sebagai trigger untuk refresh
 *
 * Dependencies:
 * - jQuery
 * - DataTables library
 * - ProvinceToast for notifications
 * - CreateProvinceForm for handling create operations
 * - EditProvinceForm for handling edit operations
 *
 * Related Files:
 * - create-province-form.js: Handles create form submission
 * - edit-province-form.js: Handles edit form submission
 */
 /**
  * Province DataTable Handler
  *
  * @package     Wilayah_Indonesia
  * @subpackage  Assets/JS/Components
  * @version     1.1.0
  * @author      arisciwek
  */
 (function($) {
     'use strict';

     const ProvinceDataTable = {
         table: null,
         initialized: false,
         currentHighlight: null,

         init() {
             if (this.initialized) {
                 return;
             }

             // Wait for dependencies
             if (!window.Province || !window.ProvinceToast) {
                 setTimeout(() => this.init(), 100);
                 return;
             }

             this.initialized = true;
             this.initDataTable();
             this.bindEvents();
             this.handleInitialHash();
         },

        initDataTable() {
            if ($.fn.DataTable.isDataTable('#provinces-table')) {
                $('#provinces-table').DataTable().destroy();
            }

            // Initialize clean table structure
            $('#provinces-table').empty().html(`
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Provinsi</th>
                        <th>Jumlah Kab/Kota</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `);

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
                        data: 'code',
                        title: 'Kode',
                        width: '20px'
                    },
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
                    this.updateDashboardStats(settings);

                    // Get current hash if any
                    const hash = window.location.hash;
                    if (hash && hash.startsWith('#')) {
                        const id = hash.substring(1);
                        if (id) {
                            this.highlightRow(id);
                        }
                    }
                },
                createdRow: (row, data) => {
                    $(row).attr('data-id', data.id);
                }
            });
        },

         bindEvents() {
             // Hash change event
             $(window).off('hashchange.provinceTable')
                     .on('hashchange.provinceTable', () => this.handleHashChange());

             // CRUD event listeners
             $(document).off('province:created.datatable province:updated.datatable province:deleted.datatable')
                       .on('province:created.datatable province:updated.datatable province:deleted.datatable',
                           () => this.refresh());
         },

         bindActionButtons() {
             const $table = $('#provinces-table');
             $table.off('click', '.view-province, .edit-province, .delete-province');

             // View action
             $table.on('click', '.view-province', (e) => {
                 const id = $(e.currentTarget).data('id');
                 if (id) window.location.hash = id;

                 // Reset tab ke details
                 $('.tab-content').removeClass('active');
                 $('#province-details').addClass('active');
                 $('.nav-tab').removeClass('nav-tab-active');
                 $('.nav-tab[data-tab="province-details"]').addClass('nav-tab-active');

             });

             // Edit action
             $table.on('click', '.edit-province', (e) => {
                 e.preventDefault();
                 const id = $(e.currentTarget).data('id');
                 this.loadProvinceForEdit(id);
             });

             // Delete action
             $table.on('click', '.delete-province', (e) => {
                 const id = $(e.currentTarget).data('id');
                 this.handleDelete(id);
             });
         },

	async loadProvinceForEdit(id) {
             if (!id) return;

             // Wait for EditProvinceForm to be available
             let attempts = 0;
             const maxAttempts = 10;
             
             while (!window.EditProvinceForm && attempts < maxAttempts) {
                 await new Promise(resolve => setTimeout(resolve, 100));
                 attempts++;
             }

             if (!window.EditProvinceForm) {
                 console.error('EditProvinceForm component not found');
                 ProvinceToast.error('Komponen form edit tidak tersedia');
                 return;
             }

             try {
                 const response = await $.ajax({
                     url: wilayahData.ajaxUrl,
                     type: 'POST',
                     data: {
                         action: 'get_province',
                         id: id,
                         nonce: wilayahData.nonce
                     }
                 });

                 if (response.success) {
                     window.EditProvinceForm.showEditForm(response.data);
                 } else {
                     ProvinceToast.error(response.data?.message || 'Gagal memuat data provinsi');
                 }
             } catch (error) {
                 console.error('Load province error:', error);
                 ProvinceToast.error('Gagal menghubungi server');
             }
         },

         async handleDelete(id) {
             if (!id) return;

             // Tampilkan modal konfirmasi dengan WIModal
             WIModal.show({
                 title: 'Konfirmasi Hapus',
                 message: 'Yakin ingin menghapus provinsi ini? Aksi ini tidak dapat dibatalkan.',
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
                                 action: 'delete_province',
                                 id: id,
                                 nonce: wilayahData.nonce
                             }
                         });

                         if (response.success) {
                             ProvinceToast.success(response.data.message);

                             // Clear hash if deleted province is currently viewed
                             if (window.location.hash === `#${id}`) {
                                 window.location.hash = '';
                             }

                             this.refresh();
                             $(document).trigger('province:deleted');
                         } else {
                             ProvinceToast.error(response.data?.message || 'Gagal menghapus provinsi');
                         }
                     } catch (error) {
                         console.error('Delete province error:', error);
                         ProvinceToast.error('Gagal menghubungi server');
                     }
                 }
             });
         },

         handleHashChange() {
             const hash = window.location.hash;
             if (hash) {
                 const id = hash.substring(1);
                 if (id) {
                     this.highlightRow(id);
                 }
             }
         },

         handleInitialHash() {
             const hash = window.location.hash;
             if (hash && hash.startsWith('#')) {
                 this.handleHashChange();
             }
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
         },

         // Di dalam ProvinceDataTable object
         updateDashboardStats(settings) {
             // Trigger event yang didengarkan oleh Dashboard
             $(document).trigger('datatable:loaded', [settings]);

             // Atau bisa langsung memanggil Dashboard jika tersedia
             if (window.Dashboard) {
                 window.Dashboard.updateStats(settings._iRecordsTotal, null);
             }
         }
     };

     // Initialize when document is ready
     $(document).ready(() => {
         window.ProvinceDataTable = ProvinceDataTable;
         ProvinceDataTable.init();
     });

 })(jQuery);
