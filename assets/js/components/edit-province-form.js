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
/**
 * Edit Province Form Handler
 */
/**
 * Edit Province Form Handler
 */
(function($) {
    'use strict';

    const EditProvinceForm = {
        modal: null,
        form: null,

        init() {
            // Initialize modal and form elements
            this.modal = $('#edit-province-modal');
            this.form = $('#edit-province-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleUpdate(e));
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });

            // Modal events
            $('.modal-close', this.modal).on('click', () => this.hideModal());
            $('.cancel-edit', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });
        },

        showEditForm(data) {
            // Reset form first
            this.resetForm();

            // Populate form data
            this.form.find('#province-id').val(data.province.id);
            this.form.find('[name="name"]').val(data.province.name);

            // Update modal title
            this.modal.find('.modal-header h3').text('Edit Provinsi');

            // Show modal with animation
            this.modal.fadeIn(300);
            $('#edit-mode').show();
        },

        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
                $('#edit-mode').hide();
            });
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
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

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Provinsi berhasil diperbarui');
                    this.hideModal();
                    // Update URL hash to edited province's ID
                    window.location.hash = id;
                    
                    $(document).trigger('province:updated', [response.data]);
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memperbarui provinsi');
                }
            } catch (error) {
                console.error('Update province error:', error);
                ProvinceToast.error('Gagal menghubungi server');
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
        }
    };

    // Expose to global scope
    window.EditProvinceForm = EditProvinceForm;

    // Initialize when document is ready
    $(document).ready(() => {
        EditProvinceForm.init();
    });

})(jQuery);
