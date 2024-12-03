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
        if (typeof jQuery.validator === 'undefined') {
            console.error('jQuery Validator is required but not loaded');
            return;
        }
        this.bindEvents();
        this.initValidation();
    },

    bindEvents() {
        // Create form submission
        jQuery(this.forms.create).on('submit', (e) => this.handleCreate(e));
        
        // Edit form submission  
        jQuery(this.forms.edit).on('submit', (e) => this.handleUpdate(e));
        
        // Real-time validation
        jQuery(`${this.forms.create}, ${this.forms.edit}`).on('input', 'input[name="name"]', (e) => {
            this.validateField(e.target);
        });
        
        // Cancel buttons
        jQuery('.cancel-edit').on('click', () => this.handleCancel());
    },

    initValidation() {
        // Add custom validation methods
        jQuery.validator.addMethod('validProvinceName', function(value, element) {
            return /^[a-zA-Z\s]+$/.test(value);
        }, 'Nama provinsi hanya boleh mengandung huruf dan spasi');
    },

    setupFormValidation(formId) {
        return jQuery(formId).validate({
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
                jQuery(element).addClass('error');
            },
            unhighlight: (element) => {
                jQuery(element).removeClass('error');
            }
        });
    },

    validateField(field) {
        const $field = jQuery(field);
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
                jQuery('<span class="form-error"></span>')
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
        const $form = jQuery(e.currentTarget);
        
        console.log('Form submission started');
        console.log('Form element:', $form[0]);
        
        if (!this.validateAllFields($form)) {
            console.log('Form validation failed');
            return;
        }

        const requestData = {
            action: 'create_province',
            nonce: wilayahData.nonce,
            name: $form.find('[name="name"]').val()
        };

        console.log('Request data:', requestData);
        console.log('wilayahData:', wilayahData);
        
        this.setLoadingState($form, true);

        try {
            console.log('Sending AJAX request to:', wilayahData.ajaxUrl);
            
            const response = await jQuery.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: requestData,
                beforeSend: function(xhr) {
                    console.log('Request headers:', xhr.getAllResponseHeaders());
                }
            });

            console.log('AJAX response received:', response);
            if (response.success) {
                console.log('Province created successfully');
                ProvinceToast.created();
                this.resetForm($form);
                // Tutup modal
                jQuery('#create-province-modal').hide();
                jQuery(document).trigger('province:created', [response.data]);
                // Update hash dan tampilkan di panel kanan
                window.location.hash = response.data.id;
                jQuery(document).trigger('province:created', [response.data]);
            } else {
                console.error('Server returned error:', response);
                ProvinceToast.error(response.data?.message || 'Gagal menambah provinsi');
            }
        } catch (error) {
            console.error('AJAX request failed:', {
                error: error,
                status: error.status,
                statusText: error.statusText,
                responseText: error.responseText
            });
            ProvinceToast.ajaxError();
        } finally {
            console.log('Request completed');
            this.setLoadingState($form, false);
        }
    },
    
    async handleUpdate(e) {
        e.preventDefault();
        const $form = jQuery(e.currentTarget);
        const id = $form.find('#province-id').val();

        if (!this.validateAllFields($form)) {
            return;
        }

        this.setLoadingState($form, true);

        try {
            const response = await jQuery.ajax({
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
                jQuery(document).trigger('province:updated', [response.data]);
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

// Expose for global use
window.ProvinceForm = ProvinceForm;
