<?php
/**
* Select List Hooks Class
*
* @package     Wilayah_Indonesia
* @subpackage  Hooks
* @version     1.0.0
* @author      arisciwek
*
* Path: /wilayah-indonesia/src/Hooks/SelectListHooks.php
*
* Description: Hooks untuk mengelola select list provinsi dan kabupaten.
*              Menyediakan filter dan action untuk render select lists.
*              Includes dynamic loading untuk kabupaten berdasarkan provinsi.
*              Terintegrasi dengan cache system.
*
* Hooks yang tersedia:
* - wilayah_indonesia_get_province_options (filter)
* - wilayah_indonesia_get_regency_options (filter) 
* - wilayah_indonesia_province_select (action)
* - wilayah_indonesia_regency_select (action)
*
* Changelog:
* 1.0.0 - 2024-01-06
* - Initial implementation
* - Added province options filter
* - Added regency options filter
* - Added select rendering actions
* - Added cache integration
*/
namespace WilayahIndonesia\Hooks;

use WilayahIndonesia\Models\ProvinceModel;
use WilayahIndonesia\Models\Regency\RegencyModel;
use WilayahIndonesia\Cache\CacheManager;

class SelectListHooks {
    private $province_model;
    private $regency_model;
    private $cache;
    private $debug_mode;
    private $select_list_hooks; // Tambahkan ini

    public function __construct() {
        $this->province_model = new ProvinceModel();
        $this->regency_model = new RegencyModel();
        $this->cache = new CacheManager();  // Dan ini
        $this->debug_mode = apply_filters('wilayah_indonesia_debug_mode', false);
        
        $this->registerHooks();
    }

    private function registerHooks() {
        // Register filters
        add_filter('wilayah_indonesia_get_province_options', [$this, 'getProvinceOptions'], 10, 2);
        add_filter('wilayah_indonesia_get_regency_options', [$this, 'getRegencyOptions'], 10, 3);
        
        // Register actions
        add_action('wilayah_indonesia_province_select', [$this, 'renderProvinceSelect'], 10, 2);
        add_action('wilayah_indonesia_regency_select', [$this, 'renderRegencySelect'], 10, 3);
        
        // Register AJAX handlers
        add_action('wp_ajax_get_regency_options', [$this, 'handleAjaxRegencyOptions']);
        add_action('wp_ajax_nopriv_get_regency_options', [$this, 'handleAjaxRegencyOptions']);
    }


    private function renderSelect(array $attributes, array $options, ?int $selected_id): void {
        $attributes['class'] = isset($attributes['class']) ? 
            $attributes['class'] . ' regular-text' : 
            'regular-text';
        ?>
        <select <?php echo $this->buildAttributes($attributes); ?>>
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>" 
                    <?php selected($selected_id, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
        // Add loading indicator for regency select
        if (strpos($attributes['class'], 'wilayah-regency-select') !== false): ?>
            <span class="wilayah-loading" style="display: none;">
                <?php echo esc_html($attributes['data-loading-text'] ?? __('Memuat...', 'wilayah-indonesia')); ?>
            </span>
        <?php endif;
    }

    public function handleAjaxRegencyOptions(): void {
        try {
            if (!check_ajax_referer('wilayah_select_nonce', 'nonce', false)) {
                throw new \Exception('Invalid security token');
            }

            $province_id = isset($_POST['province_id']) ? absint($_POST['province_id']) : 0;
            if (!$province_id) {
                throw new \Exception('Invalid province ID');
            }

            $options = $this->getRegencyOptions([], $province_id);
            $html = $this->generateOptionsHtml($options);

            wp_send_json_success([
                'html' => $html,
                'count' => count($options) - 1 // Kurangi 1 untuk pilihan default
            ]);

        } catch (\Exception $e) {
            $this->logError('AJAX Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('Gagal memuat data kabupaten/kota', 'wilayah-indonesia'),
                'details' => WP_DEBUG ? $e->getMessage() : null
            ]);
        }
    }

    private function generateOptionsHtml(array $options): string {
        $html = '';
        foreach ($options as $value => $label) {
            // Skip empty value for regency (already added by select element)
            if ($value === '' && $label === __('Pilih Kabupaten/Kota', 'wilayah-indonesia')) {
                continue;
            }
            $html .= sprintf(
                '<option value="%s">%s</option>',
                esc_attr($value),
                esc_html($label)
            );
        }
        return $html;
    }

    /**
     * Get province options with caching
     */
        public function getProvinceOptions(array $default_options = [], bool $include_empty = true): array {
            try {
                // Pastikan default options selalu array
                $options = $default_options;
                
                // Tambahkan opsi default jika diperlukan
                if ($include_empty) {
                    $options[''] = __('Pilih Provinsi', 'wilayah-indonesia');
                }

                // Generate cache key
                $cache_key = 'province_options_' . md5(serialize($default_options) . $include_empty);
                if (empty($cache_key)) {
                    return $options; // Return default jika cache key invalid
                }

                // Get from cache
                $cached = $this->cache->get($cache_key);
                if ($cached !== null && is_array($cached)) {
                    return $cached;
                }

                // Get from database
                $provinces = $this->province_model->getAllProvinces();
                if (!empty($provinces)) {
                    foreach ($provinces as $province) {
                        $options[$province->id] = esc_html($province->name);
                    }
                    // Set cache
                    $this->cache->set($cache_key, $options);
                }

                return $options; // Selalu return array

            } catch (\Exception $e) {
                $this->logError('Error getting province options: ' . $e->getMessage());
                return $options; // Return default options pada error
            }
        }

    /**
     * Get regency options with caching
     */
    public function getRegencyOptions(array $default_options = [], ?int $province_id = null, bool $include_empty = true): array {
        try {
            // Pastikan default options selalu array
            $options = $default_options;
            
            // Tambahkan opsi default
            if ($include_empty) {
                $options[''] = __('Pilih Kabupaten/Kota', 'wilayah-indonesia');
            }

            // Jika tidak ada province_id, return default options
            if (empty($province_id)) {
                return $options;
            }

            // Generate cache key
            $cache_key = sprintf(
                'regency_options_p%d_%s', 
                $province_id,
                md5(serialize($default_options) . $include_empty)
            );

            // Get from cache
            $cached = $this->cache->get($cache_key);
            if ($cached !== null && is_array($cached)) {
                return $cached;
            }

            // Get from database
            $result = $this->regency_model->getDataTableData(
                $province_id,
                0,
                1000,
                '',
                'name',
                'asc'
            );

            if (!empty($result['data'])) {
                foreach ($result['data'] as $regency) {
                    if (!empty($regency->id)) {
                        $options[$regency->id] = esc_html($regency->name);
                    }
                }
                // Cache hanya jika ada data valid
                if (count($options) > 1) {
                    $this->cache->set($cache_key, $options);
                }
            }

            return $options; // Selalu return array

        } catch (\Exception $e) {
            $this->logError('Error getting regency options: ' . $e->getMessage());
            return $options; // Return default options pada error
        }
}

    /**
     * Render province select element
     */
    public function renderProvinceSelect(array $attributes = [], ?int $selected_id = null): void {
        try {
            $default_attributes = [
                'name' => 'province_id',
                'id' => 'province_id',
                'class' => 'wilayah-province-select'
            ];

            $attributes = wp_parse_args($attributes, $default_attributes);
            $options = $this->getProvinceOptions();

            $this->renderSelect($attributes, $options, $selected_id);

        } catch (\Exception $e) {
            $this->logError('Error rendering province select: ' . $e->getMessage());
            echo '<p class="error">' . esc_html__('Error loading province selection', 'wilayah-indonesia') . '</p>';
        }
    }

    /**
     * Render regency select element
     */
    public function renderRegencySelect(array $attributes = [], ?int $province_id = null, ?int $selected_id = null): void {
        try {
            $default_attributes = [
                'name' => 'regency_id',
                'id' => 'regency_id',
                'class' => 'wilayah-regency-select'
            ];

            $attributes = wp_parse_args($attributes, $default_attributes);
            $options = $this->getRegencyOptions([], $province_id);

            $this->renderSelect($attributes, $options, $selected_id);

        } catch (\Exception $e) {
            $this->logError('Error rendering regency select: ' . $e->getMessage());
            echo '<p class="error">' . esc_html__('Error loading regency selection', 'wilayah-indonesia') . '</p>';
        }
    }

    /**
     * Build HTML attributes string
     */
    private function buildAttributes(array $attributes): string {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= sprintf(' %s', esc_attr($key));
                }
            } else {
                $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
            }
        }
        return $html;
    }

    /**
     * Debug logging
     */
    private function debugLog(string $message): void {
        if ($this->debug_mode) {
            error_log('Wilayah Select Debug: ' . $message);
        }
    }

    /**
     * Error logging
     */
    private function logError(string $message): void {
        error_log('Wilayah Select Error: ' . $message);
    }
}
