/**
 * Select List Handler Core
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.1.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/select-handler-core.js
 * 
 * Description: 
 * - Core functionality untuk select list wilayah
 * - Menangani AJAX loading untuk data kabupaten
 * - Includes error handling dan loading states
 * - Terintegrasi dengan cache system
 * 
 * 
 * Dependencies:
 * - jQuery
 * - WordPress AJAX API
 * - ProvinceToast for notifications
 * 
 * Usage:
 * Loaded through admin-enqueue-scripts hook
 * 
 * Changelog:
 * v1.1.0 - 2024-01-07
 * - Added loading state management
 * - Enhanced error handling
 * - Added debug mode
 * - Improved AJAX reliability
 * 
 * v1.0.0 - 2024-01-06
 * - Initial version
 * - Basic AJAX functionality
 * - Province-regency relation
 */

(function($) {
    'use strict';

    const WilayahSelect = {
        /**
         * Initialize the handler
         */
        init() {
            // Prevent multiple initializations
            if (this.initialized) {
                return;
            }
            
            this.initialized = true;
            this.debug = typeof wilayahData !== 'undefined' && wilayahData.debug;
            this.bindEvents();
            this.setupLoadingState();

            // Initialize toast if available
            if (typeof ProvinceToast !== 'undefined') {
                this.debugLog('ProvinceToast initialized');
            }

            // Trigger initialization complete event
            $(document).trigger('wilayah:initialized');
        },

        /**
         * Bind event handlers with namespacing
         */
        bindEvents() {
            // Remove any existing bindings first
            $(document).off('.wilayahSelect');
            
            // Add new bindings with namespace
            $(document)
                .on('change.wilayahSelect', '.wilayah-province-select', this.handleProvinceChange.bind(this))
                .on('wilayah:loaded.wilayahSelect', '.wilayah-regency-select', this.handleRegencyLoaded.bind(this))
                .on('wilayah:error.wilayahSelect', this.handleError.bind(this))
                .on('wilayah:beforeLoad.wilayahSelect', this.handleBeforeLoad.bind(this))
                .on('wilayah:afterLoad.wilayahSelect', this.handleAfterLoad.bind(this));
        },

        /**
         * Setup loading indicator
         */
        setupLoadingState() {
            this.$loadingIndicator = $('<span>', {
                class: 'wilayah-loading',
                text: wilayahData.texts.loading || 'Loading...'
            }).hide();

            // Add loading indicator after each regency select
            $('.wilayah-regency-select').each((i, el) => {
                const $regency = $(el);
                // Remove any existing loading indicators first
                $regency.next('.wilayah-loading').remove();
                // Add new loading indicator
                $regency.after(this.$loadingIndicator.clone());
            });
        },

        /**
         * Handle province selection change
         */
        handleProvinceChange(e) {
            const $province = $(e.target);
            const provinceId = $province.val();
            // Find the associated regency select using data-dependent attribute
            const provinceElementId = $province.attr('id');
            const $regency = $('[data-dependent="' + provinceElementId + '"]');

            this.debugLog('Province changed:', provinceId);

            if (!$regency.length) {
                this.debugLog('No dependent regency select found for province:', provinceElementId);
                return;
            }

            // Reset and disable regency select
            this.resetRegencySelect($regency);

            if (!provinceId) {
                return;
            }

            // Trigger before load event
            $(document).trigger('wilayah:beforeLoad', [$province, $regency]);

            // Show loading state
            this.showLoading($regency);

            // Make AJAX call
            $.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_regency_options',
                    province_id: provinceId,
                    nonce: wilayahData.nonce
                },
                success: (response) => {
                    this.debugLog('AJAX response:', response);

                    if (response.success) {
                        $regency.html(response.data.html);
                        $regency.trigger('wilayah:loaded', [response.data]);
                    } else {
                        $(document).trigger('wilayah:error', [
                            response.data.message || wilayahData.texts.error
                        ]);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    this.debugLog('AJAX error:', textStatus, errorThrown);
                    $(document).trigger('wilayah:error', [
                        wilayahData.texts.error || 'Failed to load data'
                    ]);
                },
                complete: () => {
                    this.hideLoading($regency);
                    // Trigger after load event
                    $(document).trigger('wilayah:afterLoad', [$province, $regency]);
                }
            });
        },

        /**
         * Reset regency select to initial state
         */
        resetRegencySelect($regency) {
            $regency
                .prop('disabled', true)
                .html(`<option value="">${wilayahData.texts.select_regency}</option>`);
        },

        /**
         * Show loading state
         */
        showLoading($element) {
            $element.prop('disabled', true);
            $element.next('.wilayah-loading').show();
            $element.addClass('loading');
            this.debugLog('Loading state shown');
        },

        /**
         * Hide loading state
         */
        hideLoading($element) {
            $element.prop('disabled', false);
            $element.next('.wilayah-loading').hide();
            $element.removeClass('loading');
            this.debugLog('Loading state hidden');
        },

        /**
         * Handle before load event
         */
        handleBeforeLoad(e, $province, $regency) {
            this.debugLog('Before load event triggered');
            // Add any custom pre-load handling here
        },

        /**
         * Handle after load event
         */
        handleAfterLoad(e, $province, $regency) {
            this.debugLog('After load event triggered');
            // Add any custom post-load handling here
        }
    };

    // Export to window for extensibility
    window.WilayahSelect = WilayahSelect;

    // Initialize on document ready
    $(document).ready(() => WilayahSelect.init());

})(jQuery);
