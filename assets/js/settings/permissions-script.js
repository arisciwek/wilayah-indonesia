// File: assets/js/settings/permissions-script.js

jQuery(document).ready(function($) {
    // Handle role capability changes
    $('.role-capability-checkbox').on('change', function() {
        var $checkbox = $(this);
        var $row = $checkbox.closest('tr');
        
        // If this is administrator and required capability
        if ($checkbox.hasClass('required-capability') && !$checkbox.prop('checked')) {
            $checkbox.prop('checked', true);
            alert('This capability cannot be removed from Administrator role');
            return false;
        }
        
        // Update visual feedback
        if ($checkbox.prop('checked')) {
            $row.addClass('has-capability');
        } else {
            $row.removeClass('has-capability');
        }
    });

    // Handle bulk actions
    $('.select-all-capabilities').on('click', function(e) {
        e.preventDefault();
        var roleId = $(this).data('role');
        $('.capability-' + roleId + ':not(.required-capability)').prop('checked', true).trigger('change');
    });
    
    $('.deselect-all-capabilities').on('click', function(e) {
        e.preventDefault();
        var roleId = $(this).data('role');
        $('.capability-' + roleId + ':not(.required-capability)').prop('checked', false).trigger('change');
    });
});