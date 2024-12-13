/**
* Dashboard Statistics Handler
*
* @package     Wilayah_Indonesia
* @subpackage  Assets/JS
* @version     1.0.0
* @author      arisciwek
*
* Path: /wilayah-indonesia/assets/js/dashboard.js
*
* Description: Handler untuk statistik dashboard.
*              Menangani pembaruan statistik total provinsi dan kabupaten.
*              Includes AJAX loading, event handling, dan formatting angka.
*              Terintegrasi dengan ProvinceDataTable untuk data provinsi.
*
* Dependencies:
* - jQuery
* - ProvinceDataTable (for province stats)
* - WordPress AJAX API
*
* Changelog:
* 1.0.0 - 2024-12-13
* - Initial implementation
* - Added stats loading via AJAX
* - Added event handlers for CRUD operations
* - Added number formatting
* - Added error handling
*
* Last modified: 2024-12-13 14:30:00
*/

// assets/js/dashboard.js
(function($) {
    'use strict';

    const Dashboard = {
        components: {
            stats: {
                totalProvinces: $('#total-provinces'),
                totalRegencies: $('#total-regencies')
            }
        },

        init() {
            this.loadStats();
            this.bindEvents();
        },

        bindEvents() {
            // Refresh stats saat ada perubahan data
            $(document).on(
                'province:created province:deleted regency:created regency:deleted',
                () => this.loadStats()
            );
        },

        loadStats() {
            $.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_dashboard_stats',
                    nonce: wilayahData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(null, response.data.total_regencies);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Failed to load dashboard stats:', error);
                }
            });
        },

        updateStats(totalProvinces, totalRegencies) {
            if (typeof totalProvinces === 'number') {
                this.components.stats.totalProvinces
                    .text(totalProvinces.toLocaleString('id-ID'));
            }
            if (typeof totalRegencies === 'number') {
                this.components.stats.totalRegencies
                    .text(totalRegencies.toLocaleString('id-ID'));
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.Dashboard = Dashboard;
        Dashboard.init();
    });

})(jQuery);
