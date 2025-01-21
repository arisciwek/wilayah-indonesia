# Penggunaan Select List Wilayah Indonesia

## Setup Awal

### 1. Dependensi
Sebelum menggunakan select list, pastikan semua dependensi telah terpenuhi:

- jQuery
- WordPress Core
- RegencyToast untuk notifikasi (opsional)

### 2. Enqueue Scripts dan Styles

Dependencies handler akan secara otomatis menangani enqueue scripts dan styles yang diperlukan. Namun, jika Anda perlu menggunakan select handler secara manual, gunakan method berikut:

```php
$dependencies = new Wilayah_Indonesia_Dependencies('wilayah-indonesia', WILAYAH_INDONESIA_VERSION);
$dependencies->enqueue_select_handler();
```

Script ini akan menambahkan:
- File JavaScript select-handler.js
- Localized script data dengan nonce dan teks terjemahan
- Styling yang diperlukan

### 3. Penggunaan Cache System

Cache diimplementasikan menggunakan `CacheManager` class:

```php
use WilayahIndonesia\Cache\CacheManager;

$cache = new CacheManager();

// Mengambil data dari cache
$data = $cache->get('cache_key');

// Menyimpan data ke cache
$cache->set('cache_key', $data);

// Menghapus data dari cache
$cache->delete('cache_key');
```

## Penggunaan Hook

### 1. Filter untuk Data Options

```php
// Mendapatkan options provinsi
$province_options = apply_filters('wilayah_indonesia_get_province_options', [
    '' => __('Pilih Provinsi', 'wilayah-indonesia')
], true);

// Mendapatkan options kabupaten/kota
$regency_options = apply_filters(
    'wilayah_indonesia_get_regency_options',
    [],
    $province_id,
    true
);
```

### 2. Action untuk Render Select

```php
// Render province select
do_action('wilayah_indonesia_province_select', [
    'name' => 'province_id',
    'id' => 'province_id',
    'class' => 'wilayah-province-select',
    'data-placeholder' => __('Pilih Provinsi', 'wilayah-indonesia'),
    'required' => true
], $selected_province_id);

// Render regency select
do_action('wilayah_indonesia_regency_select', [
    'name' => 'regency_id',
    'id' => 'regency_id',
    'class' => 'wilayah-regency-select',
    'data-loading-text' => __('Memuat...', 'wilayah-indonesia'),
    'required' => true
], $province_id, $selected_regency_id);
```

## Event Handling

Select lists memicu beberapa events yang dapat Anda tangani:

```javascript
// Province change
jQuery('.wilayah-province-select').on('change', function() {
    const provinceId = jQuery(this).val();
    // Handle province change
});

// Before regency load
jQuery('.wilayah-regency-select').on('wilayah:loading', function() {
    // Handle loading state
});

// After regency load
jQuery('.wilayah-regency-select').on('wilayah:loaded', function() {
    // Handle loaded state
});

// On error
jQuery('.wilayah-regency-select').on('wilayah:error', function(e, error) {
    // Handle error
    console.error('Loading error:', error);
});
```

## Error Handling

### 1. Server-side Errors

Errors di server side ditangani dengan try-catch dan logging:

```php
try {
    // Operasi yang mungkin error
} catch (\Exception $e) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Wilayah Indonesia Error: ' . $e->getMessage());
    }
    return $default_value;
}
```

### 2. Client-side Errors

Errors di client side ditampilkan menggunakan RegencyToast:

```javascript
if (typeof RegencyToast !== 'undefined') {
    RegencyToast.error(errorMessage);
}
```

## Cache Management

### 1. Province Cache

```php
// Get province data
$province = $cache->getProvince($id);

// Set province data
$cache->setProvince($id, $province_data);

// Invalidate province cache
$cache->invalidateProvinceCache($id);
```

### 2. Regency Cache

```php
// Get regency data
$regency = $cache->getRegency($id);

// Set regency data
$cache->setRegency($id, $regency_data);

// Get regency list for province
$regencies = $cache->getRegencyList($province_id);

// Invalidate regency cache
$cache->invalidateRegencyCache($id, $province_id);
```

## Debugging

Untuk mengaktifkan mode debug:

```php
// Di wp-config.php atau plugin Anda
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Debug messages akan muncul di error log WordPress dengan prefix "Wilayah Indonesia".

## Data Structure

### Province Data
```php
object(stdClass) {
    id: int
    code: string
    name: string
    regency_count: int
    created_by: int
    updated_by: int
    created_at: string
    updated_at: string
}
```

### Regency Data
```php
object(stdClass) {
    id: int
    province_id: int
    code: string
    name: string
    type: string
    province_name: string
    created_by: int
    updated_by: int
    created_at: string
    updated_at: string
}
```

## Changelog

### Version 1.1.0 (2024-01-21)
- Added improved cache integration
- Added proper error handling
- Fixed missing methods
- Updated documentation

### Version 1.0.0 (2024-01-06)
- Initial release
- Basic select functionality
- Province-regency relation
