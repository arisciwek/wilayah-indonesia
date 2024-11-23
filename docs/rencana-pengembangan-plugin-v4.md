# Plugin Wilayah Indonesia untuk WordPress

## Spesifikasi Teknis

### 1. Arsitektur Dasar
- Implementasi MVC dengan strict separation of concerns
- Namespace: `WilayahIndonesia`
- PHP versi minimum: 7.4
- WordPress versi minimum: 5.8
- Autoloading menggunakan file include manual

### 2. Keamanan
- Implementasi nonce untuk setiap form submission
- Sanitasi input menggunakan WordPress sanitization API
- Escape output menggunakan WordPress escaping functions
- Validasi permisi menggunakan `current_user_can()`
- AJAX endpoint menggunakan WordPress admin-ajax.php
- Pencegahan SQL injection menggunakan prepared statements
- XSS protection dengan escape pada semua output
- CSRF protection dengan WordPress nonce pada semua form dan AJAX calls

### 3. Database
```sql
-- Struktur tabel provinsi
CREATE TABLE `{prefix}wi_provinces` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);

-- Struktur tabel kabupaten/kota
CREATE TABLE `{prefix}wi_regencies` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `province_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('kabupaten','kota') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`province_id`) REFERENCES `{prefix}provinces`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `province_name` (`province_id`, `name`)
);
```

### 4. Cache Management
```php
class CacheManager {
    private const CACHE_GROUP = 'wilayah_indonesia';
    private const CACHE_EXPIRY = 12 * HOUR_IN_SECONDS;
    
    // Cache keys
    private const KEY_PROVINCE = 'province_';
    private const KEY_PROVINCE_LIST = 'province_list';
    private const KEY_REGENCY = 'regency_';
    private const KEY_REGENCY_LIST = 'regency_list_';
    
    public function getProvince(int $id): ?array {
        return wp_cache_get(self::KEY_PROVINCE . $id, self::CACHE_GROUP);
    }
    
    public function invalidateProvinceCache(int $id): void {
        wp_cache_delete(self::KEY_PROVINCE . $id, self::CACHE_GROUP);
        wp_cache_delete(self::KEY_PROVINCE_LIST, self::CACHE_GROUP);
    }
}
```

### 5. Asset Management
```php
class AssetLoader {
    public function enqueueAssets(): void {
        // Enqueue sesuai halaman
        if ($this->isProvincePage()) {
            wp_enqueue_style('wilayah-province', 'css/province.css', [], '1.0.0');
            wp_enqueue_script('wilayah-province', 'js/province.js', ['jquery'], '1.0.0', true);
            wp_enqueue_style('bootstrap', 'css/bootstrap.min.css', [], '5.0.0');
            wp_enqueue_script('bootstrap', 'js/bootstrap.min.js', ['jquery'], '5.0.0', true);
        }
        
        // Lokalisasi script
        wp_localize_script('wilayah-province', 'wilayahData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wilayah_nonce')
        ]);
    }
}
```

### 6. UI Components

#### Panel Kiri (45% width)
```php
class LeftPanelManager {
    private function initDataTables(): void {
        // Konfigurasi DataTables
        $config = [
            'pageLength' => 15,
            'columns' => [
                ['data' => 'name', 'title' => 'Nama Provinsi'],
                ['data' => 'regency_count', 'title' => 'Jumlah Kab/Kota'],
                ['data' => 'actions', 'title' => 'Aksi', 'orderable' => false]
            ],
            'order' => [[0, 'asc']],
            'language' => [
                'url' => 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            ]
        ];
        
        wp_localize_script('wilayah-datatables', 'dtConfig', $config);
    }
}
```

#### Panel Kanan (55% width)
```php
class RightPanelManager {
    private function initTabs(): void {
        // Tab structure
        $tabs = [
            'province' => [
                'title' => 'Data Provinsi',
                'template' => 'right-panel/province-detail.php'
            ],
            'regency' => [
                'title' => 'Kabupaten/Kota',
                'template' => 'right-panel/regency-list.php',
                'lazy_load' => true
            ]
        ];
    }
}
```

### 7. Validasi

```php
class ProvinceValidator {
    public function validateCreate(array $data): array {
        $errors = [];
        
        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'Nama provinsi wajib diisi';
        }
        
        // Format validation
        if (strlen($data['name']) > 100) {
            $errors['name'] = 'Nama provinsi maksimal 100 karakter';
        }
        
        // Unique validation
        if ($this->provinceExists($data['name'])) {
            $errors['name'] = 'Nama provinsi sudah ada';
        }
        
        return $errors;
    }
}
```

### 8. Error Handling & User Feedback

```php
class ResponseHandler {
    public function ajaxResponse($data = null, string $message = '', bool $success = true): void {
        wp_send_json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    public function showNotice(string $message, string $type = 'success'): void {
        add_action('admin_notices', function() use ($message, $type) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($type),
                esc_html($message)
            );
        });
    }
}
```

### 9. WordPress Integration

```php
class WordPressIntegration {
    public function registerHooks(): void {
        // Menu
        add_action('admin_menu', [$this, 'registerMenu']);
        
        // AJAX handlers
        add_action('wp_ajax_get_province', [$this, 'getProvince']);
        add_action('wp_ajax_save_province', [$this, 'saveProvince']);
        
        // Admin hooks
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Plugin lifecycle
        register_activation_hook(PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(PLUGIN_FILE, [$this, 'deactivate']);
    }
}
```

### 10. Performance Optimization

```php
class PerformanceOptimizer {
    public function optimizeQueries(): void {
        // Menggunakan proper indexing
        add_action('init', [$this, 'addDatabaseIndexes']);
        
        // Lazy loading untuk data yang tidak immediately visible
        add_action('wp_ajax_load_regencies', [$this, 'loadRegenciesAjax']);
    }
    
    private function addDatabaseIndexes(): void {
        global $wpdb;
        
        $wpdb->query("
            ALTER TABLE `{$wpdb->prefix}provinces` 
            ADD INDEX `name_index` (`name`)
        ");
    }
}
```

### 11. Dokumentasi
- Setiap file harus memiliki file header dengan format:
```php
/**
 * File: [nama file]
 * Path: [relative path]
 * Description: [deskripsi]
 * Last modified: [tanggal]
 * Changelog:
 *   - [tanggal]: [perubahan]
 */
```

### 12. Hak Akses

#### Capabilities Default
```php
const CAPABILITIES = [
    'view_province_list'    => 'Melihat daftar semua provinsi',
    'view_province'         => 'Melihat detail provinsi apa pun',
    'view_own_province'     => 'Melihat detail provinsi yang dibuat sendiri',
    'create_province'       => 'Membuat provinsi baru',
    'edit_province'         => 'Mengedit provinsi apa pun',
    'edit_own_province'     => 'Mengedit provinsi yang dibuat sendiri',
    'delete_province'       => 'Menghapus provinsi apa pun',
    'delete_own_province'   => 'Menghapus provinsi yang dibuat sendiri',
    
    'view_regency_list'     => 'Melihat daftar semua kabupaten',
    'view_regency'          => 'Melihat detail kabupaten apa pun',
    'view_own_regency'      => 'Melihat detail kabupaten yang dibuat sendiri',
    'create_regency'        => 'Membuat kabupaten baru',
    'edit_regency'          => 'Mengedit kabupaten apa pun',
    'edit_own_regency'      => 'Mengedit kabupaten yang dibuat sendiri',
    'delete_regency'        => 'Menghapus kabupaten apa pun',
    'delete_own_regency'    => 'Menghapus kabupaten yang dibuat sendiri',
];
```

#### Implementasi Permission Manager
```php
class PermissionManager {
    private function checkOwnership(int $provinceId): bool {
        $province = $this->provinceModel->find($provinceId);
        return $province && $province->created_by === get_current_user_id();
    }

    public function canViewProvince(int $provinceId): bool {
        // User bisa melihat jika:
        // 1. Punya capability view_province, atau
        // 2. Punya capability view_own_province dan adalah pembuat provinsi tersebut
        return current_user_can('view_province') || 
               (current_user_can('view_own_province') && $this->checkOwnership($provinceId));
    }

    public function canEditProvince(int $provinceId): bool {
        return current_user_can('edit_province') || 
               (current_user_can('edit_own_province') && $this->checkOwnership($provinceId));
    }

    public function filterProvinceList(array $provinces): array {
        // Jika user punya view_province, tampilkan semua
        if (current_user_can('view_province')) {
            return $provinces;
        }

        // Jika hanya punya view_own_province, filter yang dibuat sendiri
        if (current_user_can('view_own_province')) {
            return array_filter($provinces, function($province) {
                return $province->created_by === get_current_user_id();
            });
        }

        return [];
    }
}
```

#### Perubahan Database
```sql
-- Menambah kolom created_by pada tabel provinces
ALTER TABLE `{prefix}wi_provinces` 
ADD COLUMN `created_by` bigint(20) NOT NULL AFTER `name`,
ADD INDEX `created_by_index` (`created_by`);

-- Menambah kolom created_by pada tabel regencies
ALTER TABLE `{prefix}wi_regencies` 
ADD COLUMN `created_by` bigint(20) NOT NULL AFTER `type`,
ADD INDEX `created_by_index` (`created_by`);
```

#### Role Assignment Default
```php
class RoleManager {
    private const ROLE_CAPABILITIES = [
        'administrator' => [
            'view_province_list', 'view_province', 'create_province', 
            'edit_province', 'delete_province',
            'view_regency_list', 'view_regency', 'create_regency', 
            'edit_regency', 'delete_regency'
        ],
        'editor' => [
            'view_province_list', 'view_province', 'create_province',
            'edit_own_province', 'delete_own_province',
            'view_regency_list', 'view_regency', 'create_regency',
            'edit_own_regency', 'delete_own_regency'
        ],
        'author' => [
            'view_province_list', 'view_province', 'view_own_province',
            'create_province', 'edit_own_province',
            'view_regency_list', 'view_own_regency', 'create_regency'
        ],
        'contributor' => [
            'view_province_list', 'view_own_province',
            'view_regency_list', 'view_own_regency'
        ]
    ];
}
```

Manfaat dari adanya "View Own":
1. **Kontrol Akses Granular**: User bisa melihat konten yang mereka buat tanpa perlu akses ke semua konten
2. **Data Privacy**: Membatasi akses data hanya kepada pembuat atau admin
3. **Content Management**: Memudahkan user fokus pada konten mereka sendiri
4. **Audit Trail**: Mempermudah tracking siapa yang membuat/mengubah data
5. **Workflow Management**: Mendukung alur kerja di mana kontributor hanya bisa melihat dan mengelola konten mereka sendiri

### 13. Fase Implementasi
1. Setup Dasar (1-2 hari)
   - Struktur folder
   - File utama plugin
   - Aktivasi/deaktivasi hooks
   - Database setup

2. Implementasi Sistem Permission (2-3 hari)
   - Setup capability system
   - Role assignment
   - Permission checks
   - Ownership tracking

3. Implementasi Province (3-4 hari)
   - CRUD operations dengan permission
   - UI dasar dengan DataTables
   - Form handling
   - Validation

4. Cache Implementation (1-2 hari)
   - Setup cache group
   - Cache invalidation
   - Cache retrieval

5. UI Development (3-4 hari)
   - Layout responsif
   - Panel management
   - AJAX operations
   - Error handling

6. Regency Management (3-4 hari)
   - CRUD dengan permission
   - UI integration
   - Relasi dengan Province

7. Testing & Bug Fixing (2-3 hari)
   - Unit testing
   - Integration testing
   - Permission testing
   - UI testing

8. Dokumentasi & Deployment (1-2 hari)
   - User documentation
   - Code documentation
   - Deployment checklist

Total estimasi waktu: 16-24 hari kerja