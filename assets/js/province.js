/**
 * Province Management Scripts
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/province.js
 * 
 * Description: Mengelola interaksi UI untuk halaman manajemen provinsi.
 *              Mengimplementasikan DataTables untuk daftar provinsi.
 *              Menangani operasi CRUD dengan AJAX.
 *              Mengelola panel kanan untuk detail provinsi.
 *              Includes validasi form dan notifikasi.
 * 
 * 
 * Changelog:
 * 1.0.0 - 2024-12-02 16:11:00
 * - Initial release
 * - Added DataTables integration
 * - Added CRUD operations via AJAX
 * - Added right panel management
 * - Added toast notifications
 * - Added permission-based UI
 * 
 * 
 * Dependencies:
 * - jQuery 3.6+
 * - DataTables 1.13+
 * - wilayahToast.js for notifications
 * - WordPress admin-ajax.php
 * - WordPress capability system
 */

(function($) {
    'use strict';
    
    import ProvinceDataTable from './components/province-datatable';
    import ProvinceToast from './components/province-toast';

    const Province = {
        table: null,
        rightPanel: null,
        currentMode: 'view',
        // Tambahkan properti form
        form: null,

        init() {
            this.dataTable = ProvinceDataTable;
            this.dataTable.init();
            this.initRightPanel();
            this.bindEvents();
            this.checkUrlHash();
        
            // Initialize form handler
            this.form = ProvinceForm;
            this.form.init();
            
            // Initialize toast
            ProvinceToast.init();
        },

        // Initialize DataTables
        initDatatable() {
            this.table = $('#provinces-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_provinces',
                        nonce: wilayahData.nonce
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
                        className: 'text-center'
                    },
                    {
                        data: null,
                        title: 'Aksi',
                        orderable: false,
                        className: 'text-center',
                        render: function(data) {
                            let buttons = [];
                            
                            // View button
                            if (wilayahData.caps.view) {
                                buttons.push(`<button type="button" class="button view-province" data-id="${data.id}" title="Lihat Detail">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>`);
                            }
                            
                            // Edit button
                            if (wilayahData.caps.edit) {
                                buttons.push(`<button type="button" class="button edit-province" data-id="${data.id}" title="Edit">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>`);
                            }
                            
                            // Delete button
                            if (wilayahData.caps.delete) {
                                buttons.push(`<button type="button" class="button delete-province" data-id="${data.id}" title="Hapus">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>`);
                            }
                            
                            return buttons.join(' ');
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                order: [[0, 'asc']],
                responsive: true
            });
        },

        // Initialize right panel
        initRightPanel() {
            this.rightPanel = $('#wi-province-right-panel');
        },

        // Bind all event handlers
        bindEvents() {
            // DataTable action buttons
            $('#provinces-table').on('click', '.view-province', (e) => this.handleView(e));
            $('#provinces-table').on('click', '.edit-province', (e) => this.handleEdit(e));
            $('#provinces-table').on('click', '.delete-province', (e) => this.handleDelete(e));

            // Right panel events
            $('.wi-province-close-panel').on('click', () => this.closePanel());
            $('.nav-tab').on('click', (e) => this.handleTabClick(e));
            
            // Form submissions
            $('#edit-province-form').on('submit', (e) => this.handleUpdate(e));
            $('#add-province-btn').on('click', () => this.showAddForm());
            
            // URL hash change
            $(window).on('hashchange', () => this.checkUrlHash());
            
            // Cancel buttons
            $('.cancel-edit').on('click', () => this.switchToViewMode());
        },

        // Load province data
        loadProvinceData(id) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_province',
                    id: id,
                    nonce: wilayahData.nonce
                },
                beforeSend: () => {
                    this.rightPanel.addClass('loading');
                },
                success: (response) => {
                    if (response.success) {
                        this.populateData(response.data);
                        this.openPanel();
                    } else {
                        wilayahToast.error(response.data.message || 'Gagal memuat data provinsi');
                    }
                },
                error: () => {
                    wilayahToast.error('Terjadi kesalahan saat memuat data');
                },
                complete: () => {
                    this.rightPanel.removeClass('loading');
                }
            });
        },

        // Populate data to view mode
        populateData(data) {
            $('#province-name').text(data.province.name);
            $('#regency-count').text(data.regency_count);
            $('#created-by').text(data.created_by_name);
            $('#created-at').text(data.created_at);
            $('#updated-at').text(data.updated_at);
            $('#province-id').val(data.province.id);
            
            // Update edit form if in edit mode
            $('#edit-province-name').val(data.province.name);
        },

        // Handle form submission for update
        handleUpdate(e) {
            e.preventDefault();
            
            const form = $(e.currentTarget);
            const submitBtn = form.find('[type="submit"]');
            const spinner = form.find('.spinner');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_province',
                    nonce: wilayahData.nonce,
                    id: $('#province-id').val(),
                    name: $('#edit-province-name').val()
                },
                beforeSend: () => {
                    submitBtn.prop('disabled', true);
                    spinner.addClass('is-active');
                },
                success: (response) => {
                    if (response.success) {
                        wilayahToast.success('Provinsi berhasil diperbarui');
                        this.table.ajax.reload(null, false);
                        this.switchToViewMode();
                        this.loadProvinceData(response.data.id);
                    } else {
                        wilayahToast.error(response.data.message || 'Gagal memperbarui provinsi');
                    }
                },
                error: () => {
                    wilayahToast.error('Terjadi kesalahan saat memperbarui data');
                },
                complete: () => {
                    submitBtn.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
            $(document).trigger('province:updated');
            
            // Delegate ke ProvinceForm
            this.form.handleUpdate(e).then(() => {
                if (this.table) {
                    this.table.ajax.reload(null, false);
                }
            });
        },

        // Update setLoadingState method
        setLoadingState($form, loading) {
            // Delegate ke ProvinceForm
            this.form.setLoadingState($form, loading);
        },

        // Update showError method
        showError(message) {
            ProvinceToast.error(message);
        },

        // Update showSuccess method
        showSuccess(message) {
            ProvinceToast.success(message);
        }

        // Handle delete action
        handleDelete(e) {
            const id = $(e.currentTarget).data('id');
            const modal = $('#delete-province-modal');
            
            modal.find('.confirm-delete').data('id', id);
            modal.show();
            
            // Confirm delete
            modal.find('.confirm-delete').one('click', () => {
                const spinner = modal.find('.spinner');
                const buttons = modal.find('button');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'delete_province',
                        id: id,
                        nonce: wilayahData.nonce
                    },
                    beforeSend: () => {
                        buttons.prop('disabled', true);
                        spinner.addClass('is-active');
                    },
                    success: (response) => {
                        if (response.success) {
                            wilayahToast.success('Provinsi berhasil dihapus');
                            this.table.ajax.reload(null, false);
                            this.closePanel();
                            modal.hide();
                        } else {
                            wilayahToast.error(response.data.message || 'Gagal menghapus provinsi');
                        }
                    },
                    error: () => {
                        wilayahToast.error('Terjadi kesalahan saat menghapus data');
                    },
                    complete: () => {
                        buttons.prop('disabled', false);
                        spinner.removeClass('is-active');
                    }
                });
            });

            $(document).trigger('province:deleted');
           
            // Cancel delete
            modal.find('.cancel-delete, .wi-modal-close').one('click', () => {
                modal.hide();
            });
        },

        // Panel and mode management
        openPanel() {
            this.rightPanel.addClass('visible');
            $('.wi-province-container').addClass('with-right-panel');
        },

        closePanel() {
            this.rightPanel.removeClass('visible');
            $('.wi-province-container').removeClass('with-right-panel');
            window.location.hash = '';
        },

        switchToViewMode() {
            $('#view-mode').show();
            $('#edit-mode').hide();
            this.currentMode = 'view';
        },

        switchToEditMode() {
            $('#view-mode').hide();
            $('#edit-mode').show();
            this.currentMode = 'edit';
        },

        // URL hash management
        checkUrlHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const id = parseInt(hash.substring(1));
                if (id) {
                    this.loadProvinceData(id);
                }
            }
        },

        // Tab management
        handleTabClick(e) {
            e.preventDefault();
            const $tab = $(e.currentTarget);
            const tabId = $tab.data('tab');
            
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            $('.tab-content').removeClass('active');
            $(`#${tabId}`).addClass('active');
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        Province.init();
    });

})(jQuery);
