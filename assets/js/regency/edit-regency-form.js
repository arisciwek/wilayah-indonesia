/**
 * Edit Regency Form Handler
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Regency
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wilayah-indonesia/assets/js/regency/edit-regency-form.js
 *
 * Description: Handler untuk form edit kabupaten/kota.
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

    const EditRegencyForm = {
        modal: null,
        form: null,

        init() {
            this.modal = $('#edit-regency-modal');
            this.form = $('#edit-regency-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form events
            this.form.on('submit', (e) => this.handleUpdate(e));

            // Edit button handler for DataTable rows
            $(document).on('click', '.edit-regency', (e) => {
                const id = $(e.currentTarget).data('id');
                if (id) {
                    this.loadRegencyData(id);
                }
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

        async loadRegencyData(id) {
            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_regency',
                        id: id,
                        nonce: wilayahData.nonce
                    }
                });

                if (response.success) {
                    this.showEditForm(response.data);
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memuat data kabupaten/kota');
                }
            } catch (error) {
                console.error('Load regency error:', error);
                ProvinceToast.error('Gagal menghubungi server');
            }
        },

        showEditForm(data) {
            if (!data || !data.regency) {
                ProvinceToast.error('Data kabupaten/kota tidak valid');
                return;
            }

            // Reset form first
            this.resetForm();

            // Populate form data
            this.form.find('#regency-id').val(data.regency.id);
            this.form.find('[name="name"]').val(data.regency.name);
            this.form.find('[name="code"]').val(data.regency.code);
            this.form.find('[name="type"]').val(data.regency.type);

            // Update modal title with regency name
            this.modal.find('.modal-header h3').text(`Edit Kabupaten/Kota: ${data.regency.name}`);

            // Show modal with animation
            this.modal.fadeIn(300, () => {
                this.form.find('[name="name"]').focus();
            });
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

        async handleUpdate(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            const id = this.form.find('#regency-id').val();
            const requestData = {
                action: 'update_regency',
                nonce: wilayahData.nonce,
                id: id,
                name: this.form.find('[name="name"]').val().trim(),
                type: this.form.find('[name="type"]').val(),
                code: this.form.find('[name="code"]').val().trim()
            };

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Kabupaten/kota berhasil diperbarui');
                    this.hideModal();

                    // Trigger events untuk refresh DataTable
                    $(document).trigger('regency:updated', [response.data]);

                    // Refresh DataTable jika ada
                    if (window.RegencyDataTable) {
                        window.RegencyDataTable.refresh();
                    }
                } else {
                    ProvinceToast.error(response.data?.message || 'Gagal memperbarui kabupaten/kota');
                }
            } catch (error) {
                console.error('Update regency error:', error);
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
            this.form.validate().resetForm();
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        console.log('Edit modal visibility:', $('#edit-regency-modal').is(':visible'));
        window.EditRegencyForm = EditRegencyForm;
        EditRegencyForm.init();
    });

})(jQuery);
