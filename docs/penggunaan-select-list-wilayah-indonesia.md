# Penggunaan Select List Wilayah Indonesia

## Setup Awal

### 1. Dependensi
Sebelum menggunakan select list, pastikan semua dependensi telah terpenuhi:

- jQuery
- WordPress Core
- ProvinceToast untuk notifikasi (opsional)

### 2. Enqueue Scripts dan Styles

```php
// Di file plugin Anda
add_action('admin_enqueue_scripts', function($hook) {
    // Cek apakah sedang di halaman yang membutuhkan select
    if ($hook === 'your-page.php') {
        // Enqueue script
        wp_enqueue_script(
            'wilayah-select-handler',
            WILAYAH_INDONESIA_URL . 'assets/js/components/select-handler.js',
            ['jquery'],
            WILAYAH_INDONESIA_VERSION,
            true
        );

        // Setup data untuk JavaScript
        wp_localize_script('wilayah-select-handler', 'wilayahData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wilayah_select_nonce'),
            'texts' => [
                'select_regency' => __('Pilih Kabupaten/Kota', 'wilayah-indonesia'),
                'loading' => __('Memuat...', 'wilayah-indonesia'),
                'error' => __('Gagal memuat data', 'wilayah-indonesia')
            ]
        ]);

        // Enqueue ProvinceToast jika digunakan
        wp_enqueue_script('province-toast');
        wp_enqueue_style('province-toast-style');
    }
});
```

### 3. Integrasi Cache System

```php
// Mengaktifkan cache
add_filter('wilayah_indonesia_enable_cache', '__return_true');

// Konfigurasi durasi cache (dalam detik)
add_filter('wilayah_indonesia_cache_duration', function() {
    return 3600; // 1 jam
});
```

## Penggunaan Hook

### 1. Filter untuk Data Options

```php
// Mendapatkan options provinsi dengan cache
$province_options = apply_filters('wilayah_indonesia_get_province_options', [
    '' => __('Pilih Provinsi', 'your-textdomain')
], true); // Parameter kedua untuk include_empty

// Mendapatkan options kabupaten/kota dengan cache
$regency_options = apply_filters(
    'wilayah_indonesia_get_regency_options',
    [],
    $province_id,
    true // Parameter ketiga untuk include_empty
);
```

### 2. Action untuk Render Select

```php
// Render province select dengan atribut lengkap
do_action('wilayah_indonesia_province_select', [
    'name' => 'my_province',
    'id' => 'my_province_field',
    'class' => 'my-select-class wilayah-province-select',
    'data-placeholder' => __('Pilih Provinsi', 'your-textdomain'),
    'required' => 'required',
    'aria-label' => __('Pilih Provinsi', 'your-textdomain')
], $selected_province_id);

// Render regency select dengan loading state
do_action('wilayah_indonesia_regency_select', [
    'name' => 'my_regency',
    'id' => 'my_regency_field',
    'class' => 'my-select-class wilayah-regency-select',
    'data-loading-text' => __('Memuat...', 'your-textdomain'),
    'required' => 'required',
    'aria-label' => __('Pilih Kabupaten/Kota', 'your-textdomain')
], $province_id, $selected_regency_id);
```

## Implementasi JavaScript

### 1. Event Handling

```javascript
(function($) {
    'use strict';

    const WilayahSelect = {
        init() {
            this.bindEvents();
            this.setupLoadingState();
        },

        bindEvents() {
            $(document).on('change', '.wilayah-province-select', this.handleProvinceChange.bind(this));
            $(document).on('wilayah:loaded', '.wilayah-regency-select', this.handleRegencyLoaded.bind(this));
        },

        setupLoadingState() {
            this.$loadingIndicator = $('<span>', {
                class: 'wilayah-loading',
                text: wilayahData.texts.loading
            }).hide();
        },

        handleProvinceChange(e) {
            const $province = $(e.target);
            const $regency = $('.wilayah-regency-select');
            const provinceId = $province.val();

            // Reset dan disable regency select
            this.resetRegencySelect($regency);

            if (!provinceId) return;

            // Show loading state
            this.showLoading($regency);

            // Make AJAX call
            $.ajax({
                url: wilayahData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_regency_options',
                    province_id: provinceId,
                    nonce: wilayahData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $regency.html(response.data.html);
                        $regency.trigger('wilayah:loaded');
                    } else {
                        this.handleError(response.data.message);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    this.handleError(errorThrown);
                },
                complete: () => {
                    this.hideLoading($regency);
                }
            });
        },

        resetRegencySelect($regency) {
            $regency.prop('disabled', true)
                   .html(`<option value="">${wilayahData.texts.select_regency}</option>`);
        },

        showLoading($element) {
            $element.prop('disabled', true);
            this.$loadingIndicator.insertAfter($element).show();
        },

        hideLoading($element) {
            $element.prop('disabled', false);
            this.$loadingIndicator.hide();
        },

        handleError(message) {
            console.error('Wilayah Select Error:', message);
            if (typeof ProvinceToast !== 'undefined') {
                ProvinceToast.error(message || wilayahData.texts.error);
            }
        },

        handleRegencyLoaded(e) {
            const $regency = $(e.target);
            // Custom handling setelah data loaded
        }
    };

    $(document).ready(() => WilayahSelect.init());

})(jQuery);
```

## Integrasi Cache System

Plugin ini menggunakan sistem cache WordPress untuk optimasi performa:

### 1. Cache Implementation

```php
class WilayahCache {
    private $cache_enabled;
    private $cache_duration;
    
    public function __construct() {
        $this->cache_enabled = apply_filters('wilayah_indonesia_enable_cache', true);
        $this->cache_duration = apply_filters('wilayah_indonesia_cache_duration', 3600);
    }
    
    public function get($key) {
        if (!$this->cache_enabled) return false;
        return wp_cache_get($key, 'wilayah_indonesia');
    }
    
    public function set($key, $data) {
        if (!$this->cache_enabled) return false;
        return wp_cache_set($key, $data, 'wilayah_indonesia', $this->cache_duration);
    }
    
    public function delete($key) {
        return wp_cache_delete($key, 'wilayah_indonesia');
    }
}
```

### 2. Penggunaan Cache

```php
// Di SelectListHooks.php
public function getProvinceOptions(array $default_options = [], bool $include_empty = true): array {
    $cache = new WilayahCache();
    $cache_key = 'province_options_' . md5(serialize($default_options) . $include_empty);
    
    $options = $cache->get($cache_key);
    if (false !== $options) {
        return $options;
    }
    
    $options = $this->buildProvinceOptions($default_options, $include_empty);
    $cache->set($cache_key, $options);
    
    return $options;
}
```

## Error Handling & Debugging

### 1. PHP Error Handling

```php
try {
    // Operasi database atau file
} catch (\Exception $e) {
    error_log('Wilayah Indonesia Plugin Error: ' . $e->getMessage());
    wp_send_json_error([
        'message' => __('Terjadi kesalahan saat memproses data', 'wilayah-indonesia')
    ]);
}
```

### 2. JavaScript Debugging

```javascript
// Aktifkan mode debug
add_filter('wilayah_indonesia_debug_mode', '__return_true');

// Di JavaScript
if (wilayahData.debug) {
    console.log('Province changed:', provinceId);
    console.log('AJAX response:', response);
}
```

## Testing & Troubleshooting

### 1. Unit Testing

```php
class WilayahSelectTest extends WP_UnitTestCase {
    public function test_province_options() {
        $hooks = new SelectListHooks();
        $options = $hooks->getProvinceOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('', $options);
    }
}
```

### 2. Common Issues & Solutions

1. **Select Kabupaten Tidak Update**
   - Periksa Console Browser
   - Validasi nonce
   - Pastikan hook AJAX terdaftar

2. **Cache Tidak Bekerja**
   - Periksa Object Cache aktif
   - Validasi cache key
   - Cek durasi cache

3. **Loading State Tidak Muncul**
   - Periksa CSS terload
   - Validasi selector JavaScript
   - Cek konflik jQuery

## Support & Maintenance

### 1. Reporting Issues
- Gunakan GitHub Issues
- Sertakan error log
- Berikan langkah reproduksi

### 2. Development Workflow
1. Fork repository
2. Buat branch fitur
3. Submit pull request
4. Tunggu review

### 3. Kontribusi
- Ikuti coding standards
- Dokumentasikan perubahan
- Sertakan unit test

## Changelog

### Version 1.1.0 (2024-01-07)
- Implementasi loading state
- Perbaikan error handling
- Optimasi cache system
- Update dokumentasi

### Version 1.0.0 (2024-01-06)
- Initial release
- Basic select functionality
- Province-regency relation
