/*
 * File: assets/js/settings/settings-script.js
 */

jQuery(document).ready(function($) {
    // Tab switching
    function switchTab(tabId) {
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(`[data-tab="${tabId}"]`).addClass('nav-tab-active');
        
        // Show corresponding content
        $('.tab-content').addClass('hidden');
        $(`#${tabId}-tab`).removeClass('hidden');
    }

    // Handle tab clicks
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');
        switchTab(tabId);

        // Update URL hash
        window.location.hash = tabId;
    });

    // Handle initial tab based on URL hash
    const hash = window.location.hash.substring(1);
    if (hash && $('#' + hash + '-tab').length) {
        switchTab(hash);
    }
    
    // Save settings via AJAX
    $('#wilayah-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_wilayah_settings',
                nonce: wilayahSettings.nonce,
                formData: formData
            },
            success: function(response) {
                if (response.success) {
                    $('#setting-message')
                        .removeClass()
                        .addClass('notice notice-success')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                } else {
                    $('#setting-message')
                        .removeClass()
                        .addClass('notice notice-error')
                        .html('<p>' + response.data.message + '</p>')
                        .show();
                }
            }
        });
    });
});
