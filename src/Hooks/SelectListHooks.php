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
use WilayahIndonesia\Cache\WilayahCache;

class SelectListHooks {
    private $province_model;
    private $regency_model;
    private $cache;
    private $debug_mode;

    public function __construct() {
        $this->province_model = new ProvinceModel();
        $this->regency_model = new RegencyModel();
        $this->cache = new WilayahCache();
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

    /**
     * Get province options with caching
     */
    public function getProvinceOptions(array $default_options = [], bool $include_empty = true): array {
        try {
            $cache_key = 'province_options_' . md5(serialize($default_options) . $include_empty);
            
            // Try to get from cache first
            $options = $this->cache->get($cache_key);
            if (false !== $options) {
                $this->debugLog('Retrieved province options from cache');
                return $options;
            }

            $options = $default_options;
            
            if ($include_empty) {
                $options[''] = __('Pilih Provinsi', 'wilayah-indonesia');
            }

            $provinces = $this->province_model->getAllProvinces();
            foreach ($provinces as $province) {
                $options[$province->id] = esc_html($province->name);
            }

            // Cache the results
            $this->cache->set($cache_key, $options);
            $this->debugLog('Cached new province options');

            return $options;

        } catch (\Exception $e) {
            $this->logError('Error getting province options: ' . $e->getMessage());
            return $default_options;
        }
    }

    /**
     * Get regency options with caching
     */
    public function getRegencyOptions(array $default_options = [], ?int $province_id = null, bool $include_empty = true): array {
        try {
            if ($province_id) {
                $cache_key = "regency_options_{$province_id}_" . md5(serialize($default_options) . $include_empty);
                
                // Try cache first
                $options = $this->cache->get($cache_key);
                if (false !== $options) {
                    $this->debugLog("Retrieved regency options for province {$province_id} from cache");
                    return $options;
                }
            }

            $options = $default_options;
            
            if ($include_empty) {
                $options[''] = __('Pilih Kabupaten/Kota', 'wilayah-indonesia');
            }

            if ($province_id) {
                $regencies = $this->regency_model->getByProvince($province_id);
                foreach ($regencies as $regency) {
                    $options[$regency->id] = esc_html($regency->name);
                }

                // Cache the results
                $this->cache->set($cache_key, $options);
                $this->debugLog("Cached new regency options for province {$province_id}");
            }

            return $options;

        } catch (\Exception $e) {
            $this->logError('Error getting regency options: ' . $e->getMessage());
            return $default_options;
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
     * Handle AJAX request for regency options
     */
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

            wp_send_json_success(['html' => $html]);

        } catch (\Exception $e) {
            $this->logError('AJAX Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('Gagal memuat data kabupaten/kota', 'wilayah-indonesia')
            ]);
        }
    }

    /**
     * Helper method to render select element
     */
    private function renderSelect(array $attributes, array $options, ?int $selected_id): void {
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
    }

    /**
     * Generate HTML for select options
     */
    private function generateOptionsHtml(array $options): string {
        $html = '';
        foreach ($options as $value => $label) {
            $html .= sprintf(
                '<option value="%s">%s</option>',
                esc_attr($value),
                esc_html($label)
            );
        }
        return $html;
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
