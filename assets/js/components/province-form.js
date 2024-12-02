/**
 * Province Form Handler
 * 
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: assets/js/components/province-form.js
 * 
 * Description: Menangani form handling untuk CRUD provinsi.
 *              Includes real-time validation dan feedback.
 *              Menggunakan ProvinceToast untuk notifikasi.
 *              Support error handling dan loading states.
 */

const ProvinceForm = {
    forms: {
        create: '#create-province-form',
        edit: '#edit-province-form'
    },

    init() {
        this.bindEvents();
        this.initValidation();
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
    },

    initValidation() {
        // Add custom validation methods
        $.validator.addMethod('validProvinceName', function(value, element) {
            return /^[a-zA-Z\s]+$/.test(value);
        }, 'Nama provinsi hanya boleh mengandung huruf dan spasi');
    },

    setupFormValidation(formId) {
        return $(formId).validate({
            rules: {
                name: {
                    required: true,
                    minlength: 3,
                    maxlength: 100,
                    validProvinceName: true
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

        // Basic validation
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

        // Update UI
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
        const $submitBtn = $form.find('[type="submit"]');
        const $spinner = $form.find('.spinner');

        // Validate all fields
        if (!this.validateAllFields($form)) {
            return;
        }

        // Show loading state
        this.setLoadingState($form, true);

        try {
            const response = await $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'create_province',
                    nonce: wilayahData.nonce,
                    name: $form.find('[name="name"]').val()
                }
            });

            if (response.success) {
                ProvinceToast.created();
                this.resetForm($form);
                $(document).trigger('province:created', [response.data]);
            } else {
                ProvinceToast.error(response.data.message || 'Gagal menambah provinsi');
            }
        } catch (error) {
            console.error('Create province error:', error);
            ProvinceToast.ajaxError();
        } finally {
            this.setLoadingState($form, false);
        }
    },

    async handleUpdate(e) {
        e.preventDefault();
        const $form = $(e.currentTarget);
        const id = $form.find('#province-id').val();

        // Validate all fields
        if (!this.validateAllFields($form)) {
            return;
        }

        // Show loading state
        this.setLoadingState($form, true);

        try {
            const response = await $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_province',
                    nonce: wilayahData.nonce,
                    id: id,
                    name: $form.find('[name="name"]').val()
                }
            });

            if (response.success) {
                ProvinceToast.updated();
                $(document).trigger('province:updated', [response.data]);
            } else {
                ProvinceToast.error(response.data.message || 'Gagal memperbarui provinsi');
            }
        } catch (error) {
            console.error('Update province error:', error);
            ProvinceToast.ajaxError();
        } finally {
            this.setLoadingState($form, false);
        }
    },

    validateAllFields($form) {
        let isValid = true;
        $form.find('input[required]').each((i, field) => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
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
        Province.switchToViewMode();
    }
};

export default ProvinceForm;