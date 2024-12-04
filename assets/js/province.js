/**
 * Province Management Interface
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/province.js
 * 
 * Description: Main JavaScript handler untuk halaman provinsi.
 *              Mengatur interaksi antar komponen seperti DataTable,
 *              form, panel kanan, dan notifikasi.
 *              Includes state management dan event handling.
 *              Terintegrasi dengan WordPress AJAX API.
 * 
 * Dependencies:
 * - jQuery
 * - ProvinceDataTable
 * - ProvinceForm 
 * - ProvinceToast
 * - WordPress AJAX
 * 
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper jQuery no-conflict handling
 * - Added panel kanan integration
 * - Added CRUD event handlers
 * - Added toast notifications
 * - Improved error handling
 * - Added loading states
 * 
 * Last modified: 2024-12-03 16:45:00
 */
(function($) {
    'use strict';

    const Province = {
        init() {
            // Initialize components
            ProvinceDataTable.init();
            ProvinceForm.init();
            ProvinceToast.init();
            
            this.bindEvents();
            this.checkInitialHash();
        },

        bindEvents() {
            // Panel toggle event
            $('.wi-province-close-panel').on('click', () => this.closePanel());
            
            // Tab navigation
            $('.nav-tab').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });
            
            // Add province button
            $('#add-province-btn').on('click', () => this.showCreateForm());
        },

        checkInitialHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                ProvinceDataTable.handleHashChange();
            }
        },

        switchTab(tabId) {
            $('.nav-tab').removeClass('nav-tab-active');
            $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');
            
            $('.tab-content').removeClass('active');
            $(`#${tabId}`).addClass('active');
        },

        showCreateForm() {
            // Reset any existing form
            ProvinceForm.resetForm($('#create-province-form'));
            
            // Show modal with animation
            $('#create-province-modal').fadeIn(300);
        },

        displayData(data, editMode = false) {
            // Update container classes
            $('.wi-province-container').addClass('with-right-panel');
            $('.wi-province-right-panel').addClass('visible');

            // Populate data
            $('#province-details').html(`
                <h3>${data.province.name}</h3>
                <div class="meta-info">
                    <p>Jumlah Kabupaten/Kota: ${data.regency_count}</p>
                    <p>Dibuat: ${data.province.created_at}</p>
                    <p>Terakhir diupdate: ${data.province.updated_at}</p>
                </div>
            `);

            if (editMode) {
                this.switchToEditMode(data);
            }
        },

        switchToEditMode(data = null) {
            const $form = $('#edit-province-form');
            if (data) {
                $form.find('#province-id').val(data.province.id);
                $form.find('[name="name"]').val(data.province.name);
            }
            
            $('#view-mode').hide();
            $('#edit-mode').show();
        },

        switchToViewMode() {
            $('#edit-mode').hide();
            $('#view-mode').show();
        },

        closePanel() {
            $('.wi-province-container').removeClass('with-right-panel');
            $('.wi-province-right-panel').removeClass('visible');
            window.location.hash = '';
            $(document).trigger('panel:closed');
        },

        showDeleteConfirmation(id) {
            if (confirm('Yakin ingin menghapus provinsi ini?')) {
                this.handleDelete(id);
            }
        },

        async handleDelete(id) {
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
                    ProvinceToast.deleted();
                    this.closePanel();
                    $(document).trigger('province:deleted');
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal menghapus provinsi');
                }
            } catch (error) {
                console.error('Delete province error:', error);
                ProvinceToast.ajaxError();
            }
        },

        showLoading() {
            $('.wi-province-panel-content').addClass('loading');
        },

        hideLoading() {
            $('.wi-province-panel-content').removeClass('loading');
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.Province = Province;
        Province.init();
    });
    /*
    $(document).ready(() => {
        window.Province = Province;
        Province.init();
        // Inisialisasi ProvinceDataTable setelah Province
        if (window.ProvinceDataTable) {
            ProvinceDataTable.init(); 
        }
    });

    */

})(jQuery);