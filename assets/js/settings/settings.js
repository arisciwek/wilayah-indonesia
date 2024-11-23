// File: assets/js/settings/settings.js
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show corresponding content
        $('.tab-content').addClass('hidden');
        $('#' + $(this).data('tab') + '-tab').removeClass('hidden');
    });
    
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
                    // Show success message
                    $('#setting-message').removeClass().addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                } else {
                    // Show error message
                    $('#setting-message').removeClass().addClass('notice notice-error').html('<p>' + response.data.message + '</p>').show();
                }
            }
        });
    });
});