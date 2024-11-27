/**
 * File: permissions-script.js
 * Path: assets/js/settings/permissions-script.js
 * Purpose: Manages province listing page functionality
 * Dependencies: jQuery, DataTables
 * Features:
 * - Toggle right panel display
 * - Handle URL hash changes for province navigation
 * - Initialize DataTables for province list
 * Last modified: 2024-11-23
 */

jQuery(document).ready(function($) {
    const $form = $('#wilayah-permissions-form');
    const $submitButton = $('#save-permissions');
    const $spinner = $form.find('.spinner');

    $form.on('submit', function(e) {
        e.preventDefault();
        
        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');

        // Get form data
        const formData = {};
        $(this).find('input[type="checkbox"]').each(function() {
            const $checkbox = $(this);
            const name = $checkbox.attr('name');
            if (name) {
                const matches = name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                if (matches) {
                    const role = matches[1];
                    const cap = matches[2];
                    if (!formData[role]) {
                        formData[role] = {};
                    }
                    formData[role][cap] = $checkbox.is(':checked');
                }
            }
        });
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_permissions',
                _wpnonce: wilayahSettings.nonce,
                _wp_http_referer: $('input[name="_wp_http_referer"]').val(),
                permissions: formData
            },
            success: function(response) {
                if (response.success) {
                    irToast.success(wilayahSettings.strings.permissionSaved);
                } else {
                    irToast.error(response.data || wilayahSettings.strings.permissionError);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', error);
                console.log('Status:', status);
                console.log('Response:', xhr.responseText);
                irToast.error(wilayahSettings.strings.permissionError);
            }
        });
    });
});