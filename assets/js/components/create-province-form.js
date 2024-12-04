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


jQuery(function($) {
    // kode disini
    // CreateProvinceForm.js
    const CreateProvinceForm = {
        init() {
            this.form = $('#create-province-form');
            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            this.form.on('submit', (e) => this.handleCreate(e));
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateField(e.target);
            });
            $('.cancel-create').on('click', () => this.hideCreateForm());
        },

        initializeValidation() {
            this.form.validate({
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
            
            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wilayahData.ajaxUrl,
                    type: 'POST',
                    data: requestData
                });

                if (response.success) {
                    ProvinceToast.success('Provinsi berhasil ditambahkan');
                    this.resetForm();
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
                this.setLoadingState(false);
            }
        },

        // ... other methods
    };

    // Di create-province-form.js
    window.CreateProvinceForm = CreateProvinceForm;
});
