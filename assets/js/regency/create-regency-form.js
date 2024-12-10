/**
 * Create Regency Form Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Regency
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/regency/create-regency-form.js
 *
 * Description: Handler untuk form tambah kabupaten/kota.
 *              Includes form validation, AJAX submission,
 *              error handling, dan modal management.
 *              Terintegrasi dengan toast notifications.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - ProvinceToast for notifications
 * - WIModal for confirmations
 *
 * Last modified: 2024-12-10
 */
(function($) {
    'use strict';

    const CreateRegencyForm = {
        modal: null,
        form: null,
        provinceId: null,

        init() {
            // Initialize modal and form elements
            this.modal = $('#create-regency-modal');
            this.form = $('#create-regency-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleCreate(e));

            // Input validation events
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
        },

        showModal(provinceId) {
            if (!provinceId) {
                ProvinceToast.error('ID Provinsi tidak valid');
                return;
            }

            this.provinceId = provinceId;
            this.form.find('#province_id').val(provinceId);

            // Reset and show form
            this.resetForm();
            this.modal
                .addClass('regency-modal')
                .fadeIn(300, () => {
                    this.form.find('[name="name"]').focus();
                });
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
                this.provinceId = null;
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
                    type: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: 'Nama kabupaten/kota wajib diisi',
                        minlength: 'Nama kabupaten/kota minimal 3 karakter',
                        maxlength: 'Nama kabupaten/kota maksimal 100 karakter'
                    },
                    type: {
                        required: 'Tipe kabupaten/kota wajib dipilih'
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
                errors.push('Nama kabupaten/kota wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama kabupaten/kota minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama kabupaten/kota maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama kabupaten/kota hanya boleh mengandung huruf dan spasi');
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

            if (!this.form.valid()) {
                return;
            }

            const requestData = {
                action: 'create_regency',
                nonce: wilayahData.nonce,
                province_id: this.provinceId,
                name: this.form.find('[name="name"]').val().trim(),
                type: this.form.find('[name="type"]').val()
            };

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Kabupaten/kota berhasil ditambahkan');
                    this.hideModal();

                    $(document).trigger('regency:created', [response.data]);

                    if (window.RegencyDataTable) {
                        window.RegencyDataTable.refresh();
                    }
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal menambah kabupaten/kota');
                }
            } catch (error) {
                console.error('Create regency error:', error);
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
        window.CreateRegencyForm = CreateRegencyForm;
        CreateRegencyForm.init();
    });

})(jQuery);
