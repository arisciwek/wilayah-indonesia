/**
 * Province Form Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/components/create-province-form.js
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

    const CreateProvinceForm = {
        modal: null,
        form: null,

        init() {
            // Initialize modal and form elements
            this.modal = $('#create-province-modal');
            this.form = $('#create-province-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleCreate(e));
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });

            // Modal events
            $('.modal-close', this.modal).on('click', () => this.hideModal());
            $('.cancel-create', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });

            // Add button handler
            $('#add-province-btn').on('click', () => this.showModal());
        },

        showModal() {
            // Reset form first
            this.resetForm();

            // Show modal with animation
            this.modal.fadeIn(300);
            this.form.find('[name="code"]').focus();
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
            });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    code: {
                        required: true,
                        minlength: 2,
                        maxlength: 2,
                        digits: true
                    }
                },
                messages: {
                    name: {
                        required: 'Nama provinsi wajib diisi',
                        minlength: 'Nama provinsi minimal 3 karakter',
                        maxlength: 'Nama provinsi maksimal 100 karakter'
                    },
                    code: {
                        required: 'Kode provinsi wajib diisi',
                        minlength: 'Kode provinsi harus 2 digit',
                        maxlength: 'Kode provinsi harus 2 digit',
                        digits: 'Kode provinsi harus berupa angka'
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
            console.log('Starting form submission...');

            if (!this.form.valid()) {
                return;
            }

            const requestData = {
                action: 'create_province',
                nonce: wilayahData.nonce,  // Gunakan nonce dari wilayahData
                name: this.form.find('[name="name"]').val().trim(),
                code: this.form.find('[name="code"]').val().trim()
            };

            console.log('Request data:', requestData);  // Debug
            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Provinsi berhasil ditambahkan');
                    this.hideModal();

                    if (response.data.id) {
                        window.location.hash = response.data.id;
                    }

                    $(document).trigger('province:created', [response.data]);

                    if (window.ProvinceDataTable) {
                        window.ProvinceDataTable.refresh();
                    }
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal menambah provinsi');
                }
            } catch (error) {
                console.error('Create province error:', error);
                ProvinceToast.error('Gagal menghubungi server. Silakan coba lagi.');
            } finally {
                this.setLoadingState(false);
            }
        },

        setLoadingState(loading) {
            const $submitBtn = this.form.find('[type="submit"]');
            const $spinner = this.form.find('.spinner');

            if (loading) {
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                this.form.addClass('loading');
            } else {
                $submitBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
                this.form.removeClass('loading');
            }
        },

        resetForm() {
            this.form[0].reset();
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.form.validate().resetForm();
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CreateProvinceForm = CreateProvinceForm;
        CreateProvinceForm.init();
    });

})(jQuery);
