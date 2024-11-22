<?php
namespace WilayahIndonesia;

class Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('wp_ajax_get_provinsi', array($this, 'get_provinsi'));
        add_action('wp_ajax_get_provinsi_detail', array($this, 'get_provinsi_detail'));
        add_action('wp_ajax_save_provinsi', array($this, 'save_provinsi'));
        add_action('wp_ajax_delete_provinsi', array($this, 'delete_provinsi'));
        // Add to existing constructor
        add_action('wp_ajax_import_provinsi', array($this, 'import_provinsi'));
        add_action('wp_ajax_download_template', array($this, 'download_template'));
    }

    public function add_menu_pages() {
        add_menu_page(
            __('Wilayah Indonesia', 'wilayah-indonesia'),
            __('Wilayah Indonesia', 'wilayah-indonesia'),
            'manage_options',
            'wilayah-indonesia',
            array($this, 'render_main_page'),
            'dashicons-location',
            30
        );
    }

    public function render_main_page() {
        include WILAYAH_PATH . 'templates/admin/main.php';
    }

    public function get_provinsi() {
        check_ajax_referer('wilayah_indonesia_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        // Handle DataTables parameters
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = isset($_POST['search']['value']) ? sanitize_text_field($_POST['search']['value']) : '';
        
        // Base query
        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                " WHERE nama_provinsi LIKE %s OR kode_provinsi LIKE %s",
                '%' . $search . '%',
                '%' . $search . '%'
            );
        }

        // Get total records
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        // Get filtered records
        $total_filtered = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

        // Get data
        $query = $wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY nama_provinsi ASC LIMIT %d OFFSET %d",
            $length,
            $start
        );
        $data = $wpdb->get_results($query, ARRAY_A);

        wp_send_json(array(
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data
        ));
    }

    public function get_provinsi_detail($prov_id = null) {
        check_ajax_referer('wilayah_indonesia_nonce', 'nonce');
        
        // Jika tidak ada parameter, ambil dari POST
        if ($prov_id === null) {
            $prov_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        }
        
        if (!$prov_id) {
            wp_send_json_error('ID Provinsi tidak valid');
        }
        
        $provinsi = $this->helper->get_provinsi($prov_id);
        
        if (!$provinsi) {
            wp_send_json_error('Provinsi tidak ditemukan');
        }
        
        wp_send_json_success([
            'html' => $this->render_detail_panel($provinsi),
            'data' => [
                'id' => $provinsi->id,
                'kode_provinsi' => $provinsi->kode_provinsi,
                'nama_provinsi' => $provinsi->nama_provinsi
            ]
        ]);
    }
    
    private function render_detail_panel($provinsi) {
        ob_start();
        include WILAYAH_PATH . 'templates/admin/detail-panel.php';
        return ob_get_clean();
    }

    public function save_provinsi() {
        check_ajax_referer('wilayah_indonesia_nonce', 'nonce');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $kode_provinsi = sanitize_text_field($_POST['kode_provinsi']);
        $nama_provinsi = sanitize_text_field($_POST['nama_provinsi']);

        if (empty($kode_provinsi) || empty($nama_provinsi)) {
            wp_send_json_error(__('Kode dan nama provinsi harus diisi', 'wilayah-indonesia'));
        }

        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        $data = array(
            'kode_provinsi' => $kode_provinsi,
            'nama_provinsi' => $nama_provinsi
        );

        if ($id) {
            // Update
            $result = $wpdb->update(
                $table,
                $data,
                array('id' => $id),
                array('%s', '%s'),
                array('%d')
            );
        } else {
            // Insert
            $result = $wpdb->insert(
                $table,
                $data,
                array('%s', '%s')
            );
            $id = $wpdb->insert_id;
        }

        if ($result === false) {
            wp_send_json_error(__('Gagal menyimpan data', 'wilayah-indonesia'));
        }

        wp_send_json_success(array(
            'message' => __('Data berhasil disimpan', 'wilayah-indonesia'),
            'id' => $id
        ));
    }

    public function delete_provinsi() {
        check_ajax_referer('wilayah_indonesia_nonce', 'nonce');

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        global $wpdb;
        $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;

        $result = $wpdb->delete(
            $table,
            array('id' => $id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(__('Gagal menghapus data', 'wilayah-indonesia'));
        }

        wp_send_json_success(__('Data berhasil dihapus', 'wilayah-indonesia'));
    }


    public function import_provinsi() {
        try {
            check_ajax_referer('wilayah_indonesia_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                throw new \Exception(__('Anda tidak memiliki izin untuk melakukan operasi ini', 'wilayah-indonesia'));
            }

            if (!isset($_FILES['import_file'])) {
                throw new \Exception(__('File tidak ditemukan', 'wilayah-indonesia'));
            }

            $file = $_FILES['import_file'];
            $allowed_types = array('application/vnd.ms-excel', 'text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new \Exception(__('Format file tidak valid. Gunakan file Excel atau CSV', 'wilayah-indonesia'));
            }

            require_once WILAYAH_PATH . 'vendor/autoload.php';

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            array_shift($rows);

            global $wpdb;
            $table = $wpdb->prefix . WILAYAH_PROVINSI_TABLE;
            $imported = 0;
            $errors = array();

            foreach ($rows as $index => $row) {
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $kode_provinsi = sanitize_text_field($row[0]);
                $nama_provinsi = sanitize_text_field($row[1]);

                // Check if kode_provinsi already exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table WHERE kode_provinsi = %s",
                    $kode_provinsi
                ));

                if ($exists) {
                    $errors[] = sprintf(
                        __('Baris %d: Kode provinsi %s sudah ada', 'wilayah-indonesia'),
                        $index + 2,
                        $kode_provinsi
                    );
                    continue;
                }

                $result = $wpdb->insert(
                    $table,
                    array(
                        'kode_provinsi' => $kode_provinsi,
                        'nama_provinsi' => $nama_provinsi
                    ),
                    array('%s', '%s')
                );

                if ($result) {
                    $imported++;
                } else {
                    $errors[] = sprintf(
                        __('Baris %d: Gagal import data', 'wilayah-indonesia'),
                        $index + 2
                    );
                }
            }

            wp_send_json_success(array(
                'message' => sprintf(
                    __('Berhasil import %d data provinsi', 'wilayah-indonesia'),
                    $imported
                ),
                'imported' => $imported,
                'errors' => $errors
            ));

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }


public function download_template() {
    check_ajax_referer('wilayah_indonesia_nonce', 'nonce');

    require_once WILAYAH_PATH . 'vendor/autoload.php';

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $sheet->setCellValue('A1', 'Kode Provinsi');
    $sheet->setCellValue('B1', 'Nama Provinsi');

    // Example data
    $sheet->setCellValue('A2', '11');
    $sheet->setCellValue('B2', 'ACEH');

    // Set column width
    $sheet->getColumnDimension('A')->setWidth(15);
    $sheet->getColumnDimension('B')->setWidth(30);

    // Style header
    $sheet->getStyle('A1:B1')->applyFromArray([
        'font' => [
            'bold' => true
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2E8F0'
            ]
        ]
    ]);

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="template-provinsi.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}
    
}
