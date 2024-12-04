/**
 * File: permissions-script.js
 * Path: /wilayah-indonesia/assets/js/settings/permissions-script.js
 * Description: Handler untuk matrix permission
 * Version: 1.0.0
 * Last modified: 2024-12-02
 * 
 * Dependencies:
 * - jQuery
 * - wilayahToast
 */

(function($) {
    'use strict';

    const PermissionMatrix = {
        init() {
            this.form = $('#wilayah-permissions-form');
            this.submitBtn = $('#save-permissions');
            this.resetBtn = $('#reset-permissions');
            this.spinner = $('.spinner');
            
            this.bindEvents();
        },

        bindEvents() {
            this.form.on('submit', (e) => this.handleSubmit(e));
            this.resetBtn.on('click', (e) => this.handleReset(e));
        },

        handleSubmit(e) {
            e.preventDefault();
            
            // Collect all checkbox data
            const permissions = {};
            this.form.find('input[type="checkbox"]').each(function() {
                const $checkbox = $(this);
                const name = $checkbox.attr('name');
                if (name && name.startsWith('permissions[')) {
                    const matches = name.match(/permissions\[(.*?)\]\[(.*?)\]/);
                    if (matches) {
                        const role = matches[1];
                        const cap = matches[2];
                        if (!permissions[role]) {
                            permissions[role] = {};
                        }
                        permissions[role][cap] = $checkbox.is(':checked') ? 1 : 0;
                    }
                }
            });

            // Show spinner and disable submit button
            this.spinner.addClass('is-active');
            this.submitBtn.prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_wilayah_permissions',
                    security: this.form.find('[name="security"]').val(),
                    permissions: permissions
                },
                success: (response) => {                    
                    if (response.success) {
                        wilayahToast.success(response.data.message || 'Hak akses berhasil diperbarui');
                        
                        if (response.data.reload) {
                            window.location.reload();
                        }
                    } else {
                        wilayahToast.error(response.data.message || 'Terjadi kesalahan saat memperbarui hak akses');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', {xhr, status, error}); // Debug log
                    wilayahToast.error('Gagal menghubungi server. Silakan coba lagi.');
                },
                complete: () => {
                    this.spinner.removeClass('is-active');
                    this.submitBtn.prop('disabled', false);
                }
            });
        },

        handleReset(e) {
            e.preventDefault();
            
            if (!confirm('Yakin ingin mereset semua hak akses ke default?')) {
                return;
            }

            this.spinner.addClass('is-active');
            this.resetBtn.prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_wilayah_permissions',
                    security: this.form.find('[name="security"]').val(),
                    reset_permissions: 1
                },
                success: (response) => {
                    console.log('Reset response:', response); // Debug log
                    
                    if (response.success) {
                        wilayahToast.success(response.data.message || 'Hak akses berhasil direset');
                        if (response.data.reload) {
                            window.location.reload();
                        }
                    } else {
                        wilayahToast.error(response.data.message || 'Terjadi kesalahan saat mereset hak akses');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Reset Error:', {xhr, status, error}); // Debug log
                    wilayahToast.error('Gagal menghubungi server. Silakan coba lagi.');
                },
                complete: () => {
                    this.spinner.removeClass('is-active');
                    this.resetBtn.prop('disabled', false);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('#wilayah-permissions-form').length) {
            PermissionMatrix.init();
        }
    });

})(jQuery);

