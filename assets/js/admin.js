// Path: assets/js/admin.js
// Last modified: 2024-11-23 20:00:00
// Description: Admin panel JavaScript for Wilayah Indonesia plugin

window.viewDetail = function(id) {
    var detailPanel = jQuery('#detail-panel');
    var listPanel = jQuery('#provinsi-list-panel');
    
    window.history.pushState({}, '', `admin.php?page=wilayah-indonesia#${id}`);
    
    jQuery.ajax({
        url: wilayahIndonesia.ajax_url,
        type: 'POST',
        data: {
            action: 'get_provinsi_detail',
            id: id,
            nonce: wilayahIndonesia.nonce
        },
        success: function(response) {
            if (response.success) {
                jQuery('#detail-content').html(response.data.html);
                detailPanel.removeClass('hidden');
                
                // Ubah proporsi panel
                listPanel.removeClass('md:w-full md:w-2/3').addClass('md:w-[45%]');
                detailPanel.removeClass('md:w-1/3').addClass('md:w-[55%]');
                detailPanel.removeClass('mt-6').addClass('md:mt-0');
                
                adjustDetailPanelPosition();
            }
        }
    });
};

jQuery(document).ready(function($) {
    function checkUrlHash() {
        const hash = window.location.hash;
        if (hash && hash.substring(1)) {
            const id = parseInt(hash.substring(1));
            if (!isNaN(id)) {
                viewDetail(id);
            }
        }
    }

    checkUrlHash();
    $(window).on('hashchange', checkUrlHash);

    // Initialize DataTables v5
    const table = $('#provinsi-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: wilayahIndonesia.ajax_url,
            type: 'POST',
            data: function(d) {
                d.action = 'get_provinsi';
                d.nonce = wilayahIndonesia.nonce;
            }
        },
        columns: [
            { 
                data: 'kode_provinsi',
                width: '15%'
            },
            { 
                data: 'nama_provinsi',
                width: '60%'
            },
            {
                data: 'id',
                width: '25%',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex space-x-2">
                            <button onclick="viewDetail(${data})" 
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Detail
                            </button>
                            <button onclick="editProvinsi(${data}, '${row.kode_provinsi}', '${row.nama_provinsi}')"
                                    class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                Edit
                            </button>
                            <button onclick="deleteProvinsi(${data})"
                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                Hapus
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        },
        drawCallback: function() {
            adjustDetailPanelPosition();
        }
    });

    function adjustDetailPanelPosition() {
        const detailPanel = $('#detail-panel');
        const listPanel = $('#provinsi-list-panel');
        
        if (!detailPanel.hasClass('hidden')) {
            if (window.innerWidth >= 768) {
                detailPanel.css({
                    'position': 'fixed',
                    'right': '0',
                    'top': '32px',
                    'height': 'calc(100vh - 32px)',
                    'overflow-y': 'auto',
                    'background': 'white',
                    'z-index': '100',
                    'padding': '2rem'
                });
            } else {
                detailPanel.css({
                    'position': 'relative',
                    'right': 'auto',
                    'top': 'auto',
                    'height': 'auto',
                    'overflow-y': 'visible',
                    'background': 'white',
                    'z-index': '1',
                    'padding': '1rem'
                });
            }
        }
    }

    $(document).on('click', '#close-detail', function() {
        const detailPanel = $('#detail-panel');
        const listPanel = $('#provinsi-list-panel');
        
        window.history.pushState({}, '', 'admin.php?page=wilayah-indonesia');
        
        detailPanel.addClass('hidden').removeClass('md:block');
        listPanel.removeClass('md:w-[45%]').addClass('md:w-full');
        
        detailPanel.css({
            'position': '',
            'right': '',
            'top': '',
            'height': '',
            'overflow-y': '',
            'background': '',
            'z-index': '',
            'padding': ''
        });
    });

    const provinsiModal = $('#provinsi-modal');
    const provinsiForm = $('#provinsi-form');
    const importModal = $('#import-modal');
    const importForm = $('#import-form');

    window.editProvinsi = function(id, kode, nama) {
        provinsiForm[0].reset();
        $('#provinsi-id').val(id);
        $('#kode_provinsi').val(kode);
        $('#nama_provinsi').val(nama);
        $('#modal-title').text('Edit Provinsi');
        provinsiModal.removeClass('hidden');
    };

    window.deleteProvinsi = function(id) {
        if (confirm(wilayahIndonesia.messages.delete_confirm)) {
            $.ajax({
                url: wilayahIndonesia.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_provinsi',
                    id: id,
                    nonce: wilayahIndonesia.nonce
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        showAlert(response.data, 'success');
                        if ($('#detail-panel').is(':visible')) {
                            $('#close-detail').click();
                        }
                    } else {
                        showAlert(response.data, 'error');
                    }
                },
                error: function() {
                    showAlert(wilayahIndonesia.messages.error, 'error');
                }
            });
        }
    };

    $('#btn-add-provinsi').click(function() {
        provinsiForm[0].reset();
        $('#provinsi-id').val('');
        $('#modal-title').text('Tambah Provinsi');
        provinsiModal.removeClass('hidden');
    });

    $('#btn-import-provinsi').click(function() {
        importForm[0].reset();
        importModal.removeClass('hidden');
    });

    $('.modal-close, #btn-cancel, #btn-cancel-import').click(function() {
        $(this).closest('.fixed').addClass('hidden');
    });

    provinsiForm.submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: wilayahIndonesia.ajax_url,
            type: 'POST',
            data: {
                action: 'save_provinsi',
                id: $('#provinsi-id').val(),
                kode_provinsi: $('#kode_provinsi').val(),
                nama_provinsi: $('#nama_provinsi').val(),
                nonce: wilayahIndonesia.nonce
            },
            beforeSend: function() {
                provinsiForm.addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    provinsiModal.addClass('hidden');
                    table.ajax.reload();
                    viewDetail(response.data.id);
                    showAlert(response.data.message, 'success');
                } else {
                    showAlert(response.data, 'error');
                }
            },
            error: function() {
                showAlert(wilayahIndonesia.messages.error, 'error');
            },
            complete: function() {
                provinsiForm.removeClass('loading');
            }
        });
    });

    importForm.submit(function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('action', 'import_provinsi');
        formData.append('nonce', wilayahIndonesia.nonce);
        
        $.ajax({
            url: wilayahIndonesia.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                importForm.addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    importModal.addClass('hidden');
                    table.ajax.reload();
                    showAlert(response.data.message, 'success');
                    
                    if (response.data.errors && response.data.errors.length > 0) {
                        console.log('Import errors:', response.data.errors);
                    }
                } else {
                    showAlert(response.data, 'error');
                }
            },
            error: function() {
                showAlert(wilayahIndonesia.messages.error, 'error');
            },
            complete: function() {
                importForm.removeClass('loading');
            }
        });
    });

    $('#download-template').click(function(e) {
        e.preventDefault();
        window.location.href = wilayahIndonesia.ajax_url + '?action=download_template&nonce=' + wilayahIndonesia.nonce;
    });

    window.showAlert = function(message, type) {
        const alertDiv = $('<div>')
            .addClass(`alert alert-${type}`)
            .text(message);
        
        $('body').append(alertDiv);
        
        setTimeout(function() {
            alertDiv.remove();
        }, 3000);
    };

    $(window).resize(function() {
        adjustDetailPanelPosition();
    });
});
