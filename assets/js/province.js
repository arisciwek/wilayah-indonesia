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
            // First check if all required components exist
            if (!window.ProvinceDataTable || !window.CreateProvinceForm || !window.EditProvinceForm || !window.ProvinceToast) {
                // If any component is missing, retry after a short delay
                setTimeout(() => this.init(), 100);
                return;
            }

            // Initialize components in correct order
            try {
                ProvinceToast.init();
                CreateProvinceForm.init();
                EditProvinceForm.init();
                ProvinceDataTable.init();

                this.bindEvents();
                this.checkInitialHash();
            } catch (error) {
                console.error('Error initializing Province:', error);
            }
        },

        bindEvents() {
            // Panel toggle event
            $('.wi-province-close-panel').off('click').on('click', () => this.closePanel());

            // Tab navigation
            $('.nav-tab').off('click').on('click', (e) => {
                e.preventDefault();
                this.switchTab($(e.currentTarget).data('tab'));
            });

            // Add province button
            $('#add-province-btn').off('click').on('click', () => CreateProvinceForm.showForm());
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
            if (!window.EditProvinceForm) {
                console.error('EditProvinceForm not found');
                return;
            }

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

        async handleDelete(id) {
            // Konfirmasi hanya sekali di sini
            if (!confirm('Yakin ingin menghapus provinsi ini?')) {
                return;
            }

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
                    // Clear hash dan tutup panel sebelum refresh
                    window.location.hash = '';
                    this.closePanel();

                    // Tampilkan pesan sukses
                    ProvinceToast.success('Provinsi berhasil dihapus');

                    // Trigger refresh data
                    $(document).trigger('province:deleted');
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal menghapus provinsi');
                }
            } catch (error) {
                console.error('Delete province error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            }
        },

        showLoading() {
            $('.wi-province-panel-content').addClass('loading');
        },

        hideLoading() {
            $('.wi-province-panel-content').removeClass('loading');
        }
    };

    // Initialize when document is ready and ensure jQuery is available
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeProvince);
    } else {
        initializeProvince();
    }

    function initializeProvince() {
        if (window.jQuery) {
            window.Province = Province;
            Province.init();
        } else {
            setTimeout(initializeProvince, 50);
        }
    }

})(jQuery);
