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
         currentId: null,
         isLoading: false,
         components: {
             container: null,
             rightPanel: null,
             detailsPanel: null,
             stats: {
                 totalProvinces: null,
                 totalRegencies: null
             }
         },

         init() {
             this.components = {
                 container: $('.wi-province-container'),
                 rightPanel: $('.wi-province-right-panel'),
                 detailsPanel: $('#province-details'),
                 stats: {
                     totalProvinces: $('#total-provinces'),
                     totalRegencies: $('#total-regencies')
                 }
             };

             this.bindEvents();
             this.handleInitialState();
         },

         bindEvents() {
             // Unbind existing events first to prevent duplicates
             $(document)
                 .off('.Province')
                 .on('province:created.Province', (e, data) => this.handleCreated(data))
                 .on('province:updated.Province', (e, data) => this.handleUpdated(data))
                 .on('province:deleted.Province', () => this.handleDeleted())
                 .on('province:display.Province', (e, data) => this.displayData(data))
                 .on('province:loading.Province', () => this.showLoading())
                 .on('province:loaded.Province', () => this.hideLoading());

             // Panel events
             $('.wi-province-close-panel').off('click').on('click', () => this.closePanel());

             // Panel navigation
             $('.nav-tab').off('click').on('click', (e) => {
                 e.preventDefault();
                 this.switchTab($(e.currentTarget).data('tab'));
             });

             // Window events
             $(window).off('hashchange.Province').on('hashchange.Province', () => this.handleHashChange());
         },

         handleInitialState() {
             const hash = window.location.hash;
             if (hash && hash.startsWith('#')) {
                 const id = hash.substring(1);
                 if (id && id !== this.currentId) {
                     this.loadProvinceData(id);
                 }
             }
         },

         handleHashChange() {
             const hash = window.location.hash;
             if (!hash) {
                 this.closePanel();
                 return;
             }

             const id = hash.substring(1);
             if (id && id !== this.currentId) {
                 $('.tab-content').removeClass('active');
                 $('#province-details').addClass('active');
                 $('.nav-tab').removeClass('nav-tab-active');
                 $('.nav-tab[data-tab="province-details"]').addClass('nav-tab-active');

                 this.loadProvinceData(id);
             }
         },

         async loadProvinceData(id) {
             if (!id || this.isLoading) return;

             this.isLoading = true;
             this.showLoading();

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
                     this.displayData(response.data);
                     this.currentId = id;
                 } else {
                     ProvinceToast.error(response.data?.message || 'Gagal memuat data provinsi');
                 }
             } catch (error) {
                 console.error('Load province error:', error);
                 if (this.isLoading) {
                     ProvinceToast.error('Gagal menghubungi server');
                 }
             } finally {
                 this.isLoading = false;
                 this.hideLoading();
             }
         },

         displayData(data) {
             if (!data || !data.province) {
                 ProvinceToast.error('Data provinsi tidak valid');
                 return;
             }

             $('.tab-content').removeClass('active');
             $('#province-details').addClass('active');
             $('.nav-tab').removeClass('nav-tab-active');
             $('.nav-tab[data-tab="province-details"]').addClass('nav-tab-active');

             this.components.container.addClass('with-right-panel');
             this.components.rightPanel.addClass('visible');

             const createdAt = new Date(data.province.created_at).toLocaleString('id-ID');
             const updatedAt = new Date(data.province.updated_at).toLocaleString('id-ID');

             $('#province-header-name').text(data.province.name);
             $('#province-name').text(data.province.name);
             $('#province-regency-count').text(data.regency_count);
             $('#province-created-at').text(createdAt);
             $('#province-updated-at').text(updatedAt);

             if (window.ProvinceDataTable) {
                 window.ProvinceDataTable.highlightRow(data.province.id);
             }
         },

         switchTab(tabId) {
             $('.nav-tab').removeClass('nav-tab-active');
             $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

             $('.tab-content').removeClass('active');
             $(`#${tabId}`).addClass('active');

             if (tabId === 'regency-list' && this.currentId) {
                 if (window.RegencyDataTable) {
                     window.RegencyDataTable.init(this.currentId);
                 }
             }
         },

         closePanel() {
             this.components.container.removeClass('with-right-panel');
             this.components.rightPanel.removeClass('visible');
             this.currentId = null;
             window.location.hash = '';
             $(document).trigger('panel:closed');
         },

         showLoading() {
             this.components.rightPanel.addClass('loading');
         },

         hideLoading() {
             this.components.rightPanel.removeClass('loading');
         },

         handleCreated(data) {
             if (data && data.id) {
                     window.location.hash = data.id;
             }

             if (window.ProvinceDataTable) {
                 window.ProvinceDataTable.refresh();
             }

             if (window.Dashboard) {
                 window.Dashboard.refreshStats();
             }
         },

         handleUpdated(data) {
             if (data && data.data && data.data.province) {
                 if (this.currentId === data.data.province.id) {
                     this.displayData(data.data);
                 }
             }
         },

         handleDeleted() {
             this.closePanel();
             if (window.ProvinceDataTable) {
                 window.ProvinceDataTable.refresh();
             }
             if (window.Dashboard) {
                window.Dashboard.loadStats(); // Gunakan loadStats() langsung
             }
         },
     };

     // Initialize when document is ready
     $(document).ready(() => {
         window.Province = Province;
         Province.init();
     });

 })(jQuery);

/*
---

(function($) {
    'use strict';

    // Initialize wilayah handlers
    function initWilayahHandlers() {
        $(document).on('change', '.wilayah-province-select', handleProvinceChange);
        
        // If there's an initial province value, load its regencies
        $('.wilayah-province-select').each(function() {
            const $select = $(this);
            const initialValue = $select.val();
            
            if (initialValue) {
                const $regencySelect = $(`[data-dependent="${$select.attr('id')}"]`);
                if ($regencySelect.length) {
                    loadRegencyOptions($regencySelect, initialValue, $regencySelect.val());
                }
            }
        });
    }

    // Load regency options via AJAX
    async function loadRegencyOptions($regencySelect, provinceId, selectedId = '') {
        if (!provinceId) {
            resetRegencySelect($regencySelect);
            return;
        }

        try {
            // Show loading state
            $regencySelect.prop('disabled', true);
            $regencySelect.next('.wilayah-loading').show();

            const response = await $.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_regency_options',
                    province_id: provinceId,
                    nonce: $regencySelect.data('nonce')
                }
            });

            if (response.success && response.data) {
                updateRegencyOptions($regencySelect, response.data, selectedId);
            } else {
                throw new Error(response.data?.message || 'Failed to load regency options');
            }
        } catch (error) {
            console.error('Error loading regency options:', error);
            resetRegencySelect($regencySelect, true);
        } finally {
            $regencySelect.next('.wilayah-loading').hide();
        }
    }

    // Update regency select with new options
    function updateRegencyOptions($select, options, selectedId = '') {
        let optionsHtml = '<option value="">Pilih Kabupaten/Kota</option>';
        
        Object.entries(options).forEach(([value, label]) => {
            const selected = selectedId && selectedId == value ? 'selected' : '';
            optionsHtml += `<option value="${value}" ${selected}>${label}</option>`;
        });

        $select
            .html(optionsHtml)
            .prop('disabled', false)
            .trigger('change');
    }

    // Reset regency select to initial state
    function resetRegencySelect($select, isError = false) {
        const message = isError ? 'Error memuat data' : 'Pilih Kabupaten/Kota';
        $select
            .html(`<option value="">${message}</option>`)
            .prop('disabled', true)
            .trigger('change');
    }

    // Initialize when document is ready
    $(document).ready(initWilayahHandlers);

})(jQuery);
*/