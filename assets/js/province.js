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
 * Description: Main JavaScript handler untuk UI provinsi.
 *              Fokus ke manajemen panel dan UI interactions.
 *              Tidak menangani operasi data (CRUD).
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
  * @version     1.1.0
  * @author      arisciwek
  */
 (function($) {
     'use strict';

     const Province = {
         currentId: null,
         components: {},

         init() {
             // Store references to required components
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
             this.loadDashboardStats();

         },

         bindEvents() {
             // Panel events
             $('.wi-province-close-panel').on('click', () => this.closePanel());

             // Panel navigation
             $('.nav-tab').on('click', (e) => {
                 e.preventDefault();
                 this.switchTab($(e.currentTarget).data('tab'));
             });

             // Window events
             $(window).on('hashchange', () => this.handleHashChange());

             // Province events
             $(document).on('province:display', (e, data) => this.displayData(data));
             $(document).on('province:loading', () => this.showLoading());
             $(document).on('province:loaded', () => this.hideLoading());

             // CRUD events
             $(document).on('province:created', (e, data) => this.handleCreated(data));
             $(document).on('province:updated', (e, data) => this.handleUpdated(data));
             $(document).on('province:deleted', () => this.handleDeleted());
         },

         handleInitialState() {
             const hash = window.location.hash;

             console.log('handleInitialState with hash:', window.location.hash);

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

                  // Reset ke tab details saat ganti provinsi
                  $('.tab-content').removeClass('active');
                  $('#province-details').addClass('active');
                  $('.nav-tab').removeClass('nav-tab-active');
                  $('.nav-tab[data-tab="province-details"]').addClass('nav-tab-active');

                 this.loadProvinceData(id);
             }
         },

         async loadProvinceData(id) {
             console.log('Loading province data:', id);
             if (!id) return;

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
                 ProvinceToast.error('Gagal menghubungi server');
             } finally {
                 this.hideLoading();
             }
         },
         displayData(data) {
            console.log('Displaying province data:', data);
             if (!data || !data.province) {
                 ProvinceToast.error('Data provinsi tidak valid');
                 return;
             }

             // Reset state saat menampilkan data baru
             $('.tab-content').removeClass('active');
             $('#province-details').addClass('active');
             $('.nav-tab').removeClass('nav-tab-active');
             $('.nav-tab[data-tab="province-details"]').addClass('nav-tab-active');

             this.components.container.addClass('with-right-panel');
             this.components.rightPanel.addClass('visible');

             const createdAt = new Date(data.province.created_at).toLocaleString('id-ID');
             const updatedAt = new Date(data.province.updated_at).toLocaleString('id-ID');

             // Mengisi data ke elemen yang sudah ada
             $('#province-header-name').text(data.province.name);
             $('#province-name').text(data.province.name);
             $('#province-regency-count').text(data.regency_count);
             $('#province-created-at').text(createdAt);
             $('#province-updated-at').text(updatedAt);

             // Highlight in DataTable if available
             if (window.ProvinceDataTable) {
                 window.ProvinceDataTable.highlightRow(data.province.id);
             }
         },

         switchTab(tabId) {
             $('.nav-tab').removeClass('nav-tab-active');
             $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

             $('.tab-content').removeClass('active');
             $(`#${tabId}`).addClass('active');

             // Inisialisasi DataTable regency saat tab aktif
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

         updateStats(totalProvinces, totalRegencies) {
             if (typeof totalProvinces === 'number') {
                 this.components.stats.totalProvinces.text(totalProvinces.toLocaleString('id-ID'));
             }
             if (typeof totalRegencies === 'number') {
                 this.components.stats.totalRegencies.text(totalRegencies.toLocaleString('id-ID'));
             }
         },

         // Event Handlers
         handleCreated(data) {
             if (data && data.id) {
                 window.location.hash = data.id;
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
         },

     };

     // Initialize when document is ready
     $(document).ready(() => {
         window.Province = Province;
         Province.init();
     });

     // Refresh stats after CRUD operations
     $(document).on('province:created province:deleted regency:created regency:deleted',
         () => this.loadDashboardStats()
     );

 })(jQuery);
