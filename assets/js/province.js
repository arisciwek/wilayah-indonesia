/**
 * File: province.js
 * Path: /wilayah-indonesia/assets/js/province.js
 * Description: Custom JavaScript untuk halaman provinsi
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial minimal functionality
 */

jQuery(document).ready(function($) {
    // Toggle right panel
    function toggleRightPanel(show = true) {
        if (show) {
            $('.left-section').addClass('with-right');
            $('.right-section').addClass('visible');
        } else {
            $('.left-section').removeClass('with-right');
            $('.right-section').removeClass('visible');
        }
    }

    // Handle panel toggle button
    $('#toggleRightPanel').on('click', function() {
        const isVisible = $('.right-section').hasClass('visible');
        toggleRightPanel(!isVisible);
    });

    // Handle URL hash changes
    $(window).on('hashchange', function() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#')) {
            const id = hash.substring(1);
            if (id) {
                toggleRightPanel(true);
                // Load province data will be implemented later
            }
        } else {
            toggleRightPanel(false);
        }
    });

    // Check initial URL hash
    if (window.location.hash) {
        $(window).trigger('hashchange');
    }
});