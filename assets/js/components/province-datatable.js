/**
 * Province DataTables Manager
 *
 * @package     Wilayah_Indonesia
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: /wilayah-indonesia/assets/js/components/province-datatable.js
 * 
 * Description: Komponen untuk mengelola DataTables provinsi.
 *              Handle server-side processing, formatting,
 *              dan integrasi dengan panel UI.
 * 
 * Dependencies:
 * - jQuery 3.6+
 * - DataTables 1.13+
 * - wilayahToast.js
 */

const ProvinceDataTable = {
    table: null,
    
    init() {
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#provinces-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                data: (d) => {
                    return {
                        ...d,
                        action: 'handle_province_datatable',
                        nonce: wilayahData.nonce
                    };
                },
                error: (xhr, error, thrown) => {
                    wilayahToast.error('Gagal memuat data provinsi');
                    console.error('DataTables Error:', error);
                }
            },
            columns: [
                { 
                    data: 'name',
                    title: 'Nama Provinsi'
                },
                { 
                    data: 'regency_count',
                    title: 'Jumlah Kab/Kota',
                    className: 'text-center',
                    searchable: false
                },
                {
                    data: null,
                    title: 'Aksi',
                    orderable: false,
                    searchable: false,
                    className: 'text-center nowrap',
                    render: (data) => this.renderActionButtons(data)
                }
            ],
            order: [[0, 'asc']],
            pageLength: wilayahData.perPage || 10,
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            responsive: true
        });
    },

    renderActionButtons(data) {
        const buttons = [];
        
        if (wilayahData.caps.view) {
            buttons.push(`
                <button type="button" 
                        class="button view-province" 
                        data-id="${data.id}" 
                        title="Lihat Detail">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
            `);
        }
        
        if (wilayahData.caps.edit) {
            buttons.push(`
                <button type="button" 
                        class="button edit-province" 
                        data-id="${data.id}" 
                        title="Edit">
                    <span class="dashicons dashicons-edit"></span>
                </button>
            `);
        }
        
        if (wilayahData.caps.delete) {
            buttons.push(`
                <button type="button" 
                        class="button delete-province" 
                        data-id="${data.id}" 
                        title="Hapus">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            `);
        }
        
        return buttons.join(' ');
    },

    bindEvents() {
        const $table = $('#provinces-table');
        
        // View button
        $table.on('click', '.view-province', (e) => {
            const id = $(e.currentTarget).data('id');
            // Update URL hash which will trigger panel load
            window.location.hash = id;
        });
        
        // Edit button
        $table.on('click', '.edit-province', (e) => {
            const id = $(e.currentTarget).data('id');
            window.location.hash = id;
            Province.switchToEditMode();
        });
        
        // Delete button
        $table.on('click', '.delete-province', (e) => {
            const id = $(e.currentTarget).data('id');
            Province.showDeleteConfirmation(id);
        });

        // Refresh on panel close
        $(document).on('panel:closed', () => {
            this.refresh();
        });

        // Refresh on CRUD operations
        $(document).on('province:created province:updated province:deleted', () => {
            this.refresh();
        });
    },

    refresh() {
        this.table.ajax.reload(null, false);
    },

    getSelectedIds() {
        return this.table.rows({ selected: true }).data()
            .map(item => item.id)
            .toArray();
    },

    destroy() {
        if (this.table) {
            this.table.destroy();
            this.table = null;
        }
    }
};

// Export for modular use
export default ProvinceDataTable;
