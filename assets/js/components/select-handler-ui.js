/**
 * Select List Handler UI
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.1.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/select-handler-ui.js
 * 
 * Description: 
 * - UI components untuk select list wilayah
 * - Menangani tampilan loading states
 * - Error message display
 * - Success indicators
 * - Mobile responsive styling
 * 
 * Dependencies:
 * - jQuery
 * - select-handler-core.js
 * - ProvinceToast (optional)
 * 
 * Usage:
 * Loaded after select-handler-core.js through admin-enqueue-scripts
 * 
 * Changelog:
 * v1.1.0 - 2024-01-07
 * - Added success indicators
 * - Enhanced mobile responsiveness
 * - Improved error display
 * - Added accessibility features
 * 
 * v1.0.0 - 2024-01-06
 * - Initial version
 * - Basic styling
 * - Loading indicators
 */
(function($) {
    'use strict';

    // Extend WilayahSelect with UI specific methods
    const WilayahSelectUI = {
        /**
         * Initialize UI components
         */
        initUI() {
            // Prevent multiple UI initializations
            if (this.uiInitialized) {
                return;
            }
            
            this.uiInitialized = true;
            this.initializeStyles();
            this.setupAccessibility();
            this.debugLog('UI components initialized');
        },

        /**
         * Handle errors
         */
        handleError(e, message) {
            console.error('Wilayah Select Error:', message);
            
            // Show error message
            this.showErrorMessage(message);
            
            // Remove loading states
            $('.wilayah-regency-select').each((i, el) => {
                this.hideLoading($(el));
            });
        },

        /**
         * Show error message
         */
        showErrorMessage(message) {
            // Remove any existing error messages first
            $('.wilayah-error').remove();

            if (typeof ProvinceToast !== 'undefined') {
                ProvinceToast.error(message);
            } else {
                // Create and show error element if toast not available
                const $error = $('<div>', {
                    class: 'wilayah-error',
                    text: message,
                    role: 'alert', // Add ARIA role for accessibility
                    'aria-live': 'polite'
                });

                // Add new error message after the regency select
                $('.wilayah-regency-select').after($error);

                // Auto hide after 5 seconds
                setTimeout(() => {
                    $error.fadeOut(() => $error.remove());
                }, 5000);
            }
        },

        /**
         * Handle successful regency data load
         */
        handleRegencyLoaded(e) {
            const $regency = $(e.target);
            this.debugLog('Regency data loaded');
            
            // Remove any existing error messages
            $('.wilayah-error').remove();
            
            // Add success indicator
            this.showSuccessIndicator($regency);
        },

        /**
         * Show success indicator
         */
        showSuccessIndicator($element) {
            // Remove any existing success indicators first
            $('.wilayah-success').remove();

            const $success = $('<span>', {
                class: 'wilayah-success',
                html: '&#10004;', // Checkmark
                role: 'status',
                'aria-live': 'polite'
            });

            // Add success indicator
            $element.after($success);

            // Auto remove after 2 seconds
            setTimeout(() => {
                $success.fadeOut(() => $success.remove());
            }, 2000);
        },

        /**
         * Initialize and inject required styles
         */
        initializeStyles() {
            // Remove any existing styles first
            $('#wilayah-select-styles').remove();

            const style = `
                .wilayah-loading {
                    margin-left: 8px;
                    color: #666;
                    display: inline-block;
                    vertical-align: middle;
                }
                
                select.wilayah-province-select,
                select.wilayah-regency-select {
                    min-width: 200px;
                    padding: 6px 12px;
                    border: 1px solid #ced4da;
                    border-radius: 4px;
                    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                }
                
                select.wilayah-province-select:focus,
                select.wilayah-regency-select:focus {
                    border-color: #80bdff;
                    outline: 0;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                }
                
                select.loading {
                    background-color: #f8f9fa;
                    cursor: wait;
                    opacity: 0.8;
                }
                
                .wilayah-error {
                    color: #dc3545;
                    margin-top: 4px;
                    font-size: 0.875em;
                    padding: 4px 8px;
                    background-color: #fff;
                    border: 1px solid #dc3545;
                    border-radius: 4px;
                    display: inline-block;
                }

                .wilayah-success {
                    color: #28a745;
                    margin-left: 8px;
                    display: inline-block;
                    vertical-align: middle;
                    animation: fadeInOut 2s ease-in-out;
                }

                @keyframes fadeInOut {
                    0% { opacity: 0; }
                    20% { opacity: 1; }
                    80% { opacity: 1; }
                    100% { opacity: 0; }
                }

                /* Hover effects */
                select.wilayah-province-select:not(:disabled):hover,
                select.wilayah-regency-select:not(:disabled):hover {
                    border-color: #80bdff;
                }

                /* Disabled state */
                select.wilayah-province-select:disabled,
                select.wilayah-regency-select:disabled {
                    background-color: #e9ecef;
                    cursor: not-allowed;
                }

                /* Mobile responsive adjustments */
                @media (max-width: 768px) {
                    select.wilayah-province-select,
                    select.wilayah-regency-select {
                        width: 100%;
                        max-width: none;
                    }
                    
                    .wilayah-error {
                        display: block;
                        margin-top: 8px;
                    }
                }
            `;

            $('<style>', {
                id: 'wilayah-select-styles',
                text: style
            }).appendTo('head');
        },

        /**
         * Setup accessibility attributes
         */
        setupAccessibility() {
            $('.wilayah-province-select, .wilayah-regency-select').each(function() {
                const $select = $(this);
                if (!$select.attr('aria-label')) {
                    $select.attr('aria-label', $select.hasClass('wilayah-province-select') ? 
                        'Pilih Provinsi' : 'Pilih Kabupaten/Kota');
                }
            });
        },

        /**
         * Debug logging
         */
        debugLog(...args) {
            if (this.debug) {
                console.log('Wilayah Select Debug:', ...args);
            }
        }
    };

    // Merge UI methods into main WilayahSelect object
    $.extend(window.WilayahSelect, WilayahSelectUI);

    // Initialize UI components after core initialization
    $(document).on('wilayah:initialized', () => {
        window.WilayahSelect.initUI();
    });

})(jQuery);
