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
    
    // Import required components
    const { table, bindEvents, refresh, destroy } = window.ProvinceDataTable;
    
    const Province = {
        table: null,
        rightPanel: null,
        currentMode: 'view',
        form: null,

        init() {
            this.dataTable = window.ProvinceDataTable;
            this.dataTable.init();
            this.initRightPanel();
            this.bindEvents();
            this.checkUrlHash();
        
            this.form = window.ProvinceForm;
            this.form.init();
            window.ProvinceToast.init();
        },

        initRightPanel() {
            this.rightPanel = $('#wi-province-right-panel');
        },

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
            
            // Modal events
            $('#create-province-modal .wi-modal-close, #create-province-modal .cancel-create').on('click', () => {
                $('#create-province-modal').hide();
            });

            // Delete confirmation modal
            $('#delete-province-modal').on('click', '.confirm-delete', () => {
                const id = $('#delete-province-modal .confirm-delete').data('id');
                this.executeDelete(id);
            });

            $('#delete-province-modal').on('click', '.cancel-delete, .wi-modal-close', () => {
                $('#delete-province-modal').hide();
            });

            // Form validation events
            $('#province-name').on('input', (e) => this.validateField(e.target));
            $('#edit-province-name').on('input', (e) => this.validateField(e.target));
        },

        handleView(e) {
            const id = $(e.currentTarget).data('id');
            window.location.hash = id;
        },

        handleEdit(e) {
            const id = $(e.currentTarget).data('id');
            window.location.hash = id;
            this.switchToEditMode();
        },

        handleDelete(e) {
            const id = $(e.currentTarget).data('id');
            this.showDeleteConfirmation(id);
        },

        handleUpdate(e) {
            e.preventDefault();
            this.form.handleUpdate(e).then(() => {
                if (this.dataTable) {
                    this.dataTable.refresh();
                }
            });
        },

        showAddForm() {
            $('#create-province-modal').show();
        },

        showDeleteConfirmation(id) {
            const modal = $('#delete-province-modal');
            modal.find('.confirm-delete').data('id', id);
            modal.show();
        },

        executeDelete(id) {
            const modal = $('#delete-province-modal');
            const buttons = modal.find('button');
            const spinner = modal.find('.spinner');

            $.ajax({
                url: wilayahData.ajaxUrl,
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
                        window.ProvinceToast.success('Provinsi berhasil dihapus');
                        if (this.dataTable) {
                            this.dataTable.refresh();
                        }
                        this.closePanel();
                        modal.hide();
                    } else {
                        window.ProvinceToast.error(response.data.message || 'Gagal menghapus provinsi');
                    }
                },
                error: () => {
                    window.ProvinceToast.error('Terjadi kesalahan saat menghapus data');
                },
                complete: () => {
                    buttons.prop('disabled', false);
                    spinner.removeClass('is-active');
                }
            });
        },

        validateField(field) {
            const $field = $(field);
            const value = $field.val().trim();
            const errors = [];

            if (!value) {
                errors.push('Nama provinsi wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama provinsi minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama provinsi maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama provinsi hanya boleh mengandung huruf dan spasi');
                }
            }

            const $error = $field.next('.form-error');
            if (errors.length > 0) {
                $field.addClass('error');
                if ($error.length) {
                    $error.text(errors[0]);
                } else {
                    $('<span class="form-error"></span>')
                        .text(errors[0])
                        .insertAfter($field);
                }
                return false;
            } else {
                $field.removeClass('error');
                $error.remove();
                return true;
            }
        },

        // Handle direct URL access
        checkUrlHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const id = parseInt(hash.substring(1));
                if (id) {
                    this.loadProvinceData(id);
                }
            }
        },

        loadProvinceData(id) {
            $.ajax({
                url: wilayahData.ajaxUrl,
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
                        window.ProvinceToast.error(response.data.message || 'Gagal memuat data provinsi');
                    }
                },
                error: () => {
                    window.ProvinceToast.error('Terjadi kesalahan saat memuat data');
                },
                complete: () => {
                    this.rightPanel.removeClass('loading');
                }
            });
        },

        populateData(data) {
            // Data provinsi dari controller
            $('#province-name').text(data.province.name);
            $('#regency-count').text(data.province.regency_count);
            $('#created-by').text(data.province.created_by);
            $('#created-at').text(data.province.created_at);
            $('#updated-at').text(data.province.updated_at);

            // Juga update form edit
            $('#edit-province-name').val(data.province.name);
            $('#province-id').val(data.province.id);

            this.openPanel();
        },

        openPanel() {
            $('.wi-province-container').addClass('with-right-panel');
            $('#wi-province-right-panel').addClass('visible');
        },

        closePanel() {
            $('.wi-province-container').removeClass('with-right-panel');
            $('#wi-province-right-panel').removeClass('visible');
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

    $(document).ready(() => {
        Province.init();
    });

})(jQuery);
