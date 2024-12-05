/**
 * Province Form Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/province-form.js
 * 
 * Description: Handler untuk form provinsi.
 *              Menangani create dan update provinsi.
 *              Includes validasi form, error handling,
 *              dan integrasi dengan komponen lain.
 * 
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - ProvinceToast for notifications
 * - Province main component
 * - WordPress AJAX API
 * 
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper form validation
 * - Added AJAX integration
 * - Added modal management
 * - Added loading states
 * - Added error handling
 * - Added toast notifications
 * - Added panel integration
 * 
 * Last modified: 2024-12-03 16:30:00
 */

(function($) {
    'use strict';

    const ProvinceForm = {
        forms: {
            create: '#create-province-form',
            edit: '#edit-province-form'
        },

        init() {
            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Create form submission
            $(this.forms.create).on('submit', (e) => this.handleCreate(e));
            
            // Edit form submission  
            $(this.forms.edit).on('submit', (e) => this.handleUpdate(e));
            
            // Real-time validation
            $(`${this.forms.create}, ${this.forms.edit}`).on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });
            
            // Cancel buttons
            $('.cancel-edit').on('click', () => this.handleCancel());
            $('.cancel-create').on('click', () => this.hideCreateForm());
        },

        showEditForm(data) {
            const $form = $(this.forms.edit);
            $form.find('#province-id').val(data.id);
            $form.find('[name="name"]').val(data.name);
            $('#edit-mode').show();
            $('#view-mode').hide();
        },

        hideCreateForm() {
            $('#create-province-modal').fadeOut(300);
            this.resetForm($(this.forms.create));
        },

        initializeValidation() {
            // Validate create form
            $(this.forms.create).validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    }
                },
                messages: {
                    name: {
                        required: 'Nama provinsi wajib diisi',
                        minlength: 'Nama provinsi minimal 3 karakter',
                        maxlength: 'Nama provinsi maksimal 100 karakter'
                    }
                },
                errorElement: 'span',
                errorClass: 'form-error',
                errorPlacement: (error, element) => {
                    error.insertAfter(element);
                },
                highlight: (element) => {
                    $(element).addClass('error');
                },
                unhighlight: (element) => {
                    $(element).removeClass('error');
                }
            });

            // Validate edit form
            $(this.forms.edit).validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100,
                    }
                },
                messages: {
                    name: {
                        required: 'Nama provinsi wajib diisi',
                        minlength: 'Nama provinsi minimal 3 karakter',
                        maxlength: 'Nama provinsi maksimal 100 karakter'
                    }
                },
                errorElement: 'span',
                errorClass: 'form-error',
                errorPlacement: (error, element) => {
                    error.insertAfter(element);
                },
                highlight: (element) => {
                    $(element).addClass('error');
                },
                unhighlight: (element) => {
                    $(element).removeClass('error');
                }
            });
        },

        validateField(field) {
            const $field = $(field);
            const value = $field.val().trim();
            const errors = [];

            if (!value) {
                errors.push('Nama provinsi wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama provinsi minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama provinsi maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama provinsi hanya boleh mengandung huruf dan spasi');
                }
            }

            const $error = $field.next('.form-error');
            if (errors.length > 0) {
                $field.addClass('error');
                if ($error.length) {
                    $error.text(errors[0]);
                } else {
                    $('<span class="form-error"></span>')
                        .text(errors[0])
                        .insertAfter($field);
                }
                return false;
            } else {
                $field.removeClass('error');
                $error.remove();
                return true;
            }
        },

        async handleCreate(e) {
            e.preventDefault();
            const $form = $(e.currentTarget);
            
            if (!$form.valid()) {
                return;
            }

            const requestData = {
                action: 'create_province',
                nonce: wilayahData.nonce,
                name: $form.find('[name="name"]').val()
            };
            
            this.setLoadingState($form, true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Provinsi berhasil ditambahkan');
                    this.resetForm($form);
                    this.hideCreateForm();
                    window.location.hash = response.data.id;
                    $(document).trigger('province:created', [response.data]);
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal menambah provinsi');
                }
            } catch (error) {
                console.error('AJAX request failed:', error);
                ProvinceToast.error('Gagal menghubungi server');
            } finally {
                this.setLoadingState($form, false);
            }
        },

        async handleUpdate(e) {
            e.preventDefault();
            const $form = $(e.currentTarget);
            
            if (!$form.valid()) {
                return;
            }

            const id = $form.find('#province-id').val();
            const requestData = {
                action: 'update_province',
                nonce: wilayahData.nonce,
                id: id,
                name: $form.find('[name="name"]').val()
            };
            
            this.setLoadingState($form, true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Provinsi berhasil diperbarui');
                    $(document).trigger('province:updated', [response.data]);
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memperbarui provinsi');
                }
            } catch (error) {
                console.error('Update province error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            } finally {
                this.setLoadingState($form, false);
            }
        },

        setLoadingState($form, loading) {
            const $submitBtn = $form.find('[type="submit"]');
            const $spinner = $form.find('.spinner');
            
            if (loading) {
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                $form.addClass('loading');
            } else {
                $submitBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
                $form.removeClass('loading');
            }
        },

        resetForm($form) {
            $form[0].reset();
            $form.find('.form-error').remove();
            $form.find('.error').removeClass('error');
        },

        handleCancel() {
            if (window.Province) {
                Province.switchToViewMode();
            }
        },
        switchToViewMode() {
            $('#edit-mode').hide();
            $('#view-mode').show();
        },
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.ProvinceForm = ProvinceForm;
        ProvinceForm.init();
    });

})(jQuery);

