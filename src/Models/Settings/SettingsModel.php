<?php
/**
 * File: SettingsModel.php
 * Path: /wilayah-indonesia/src/Models/Settings/SettingsModel.php
 * Description: Model untuk mengelola pengaturan umum plugin
 * Version: 1.1.0
 * Last modified: 2024-11-25 06:15:00
 * 
 * Changelog:
 * v1.1.0 - 2024-11-25
 * - Added getSettings method untuk mendapatkan semua pengaturan
 * - Added registerSettings method untuk mendaftarkan pengaturan ke WordPress
 * - Added saveGeneralSettings method untuk menyimpan pengaturan umum
 * - Restructured settings organization
 * - Added proper settings registration
 * 
 * v1.0.0 - 2024-11-23
 * - Initial creation
 */

namespace WilayahIndonesia\Models\Settings;

class SettingsModel {
    private const OPTION_GROUP = 'wilayah_indonesia_settings';
    private const GENERAL_OPTIONS = 'wilayah_indonesia_general_options';
    
    private $default_options = [
        'records_per_page' => 15,
        'enable_caching' => true,
        'cache_duration' => 43200, // 12 hours in seconds
        'datatables_language' => 'id',
        'display_format' => 'hierarchical',
        'enable_api' => false,
        'api_key' => '',
        'log_enabled' => false
    ];

    /**
     * Get all settings termasuk default values
     */
    public function getSettings(): array {
        return [
            'general' => $this->getGeneralOptions(),
            'api' => $this->getApiSettings(),
            'display' => $this->getDisplaySettings(),
            'system' => $this->getSystemSettings()
        ];
    }

    /**
     * Register semua settings ke WordPress
     */
    public function registerSettings() {
        register_setting(
            self::OPTION_GROUP,
            self::GENERAL_OPTIONS,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitizeOptions']
            ]
        );

        // General Settings Section
        add_settings_section(
            'wilayah_general_section',
            __('Pengaturan Umum', 'wilayah-indonesia'),
            [$this, 'renderGeneralSection'],
            self::OPTION_GROUP
        );

        // Add settings fields
        add_settings_field(
            'records_per_page',
            __('Data Per Halaman', 'wilayah-indonesia'),
            [$this, 'renderNumberField'],
            self::OPTION_GROUP,
            'wilayah_general_section',
            [
                'id' => 'records_per_page',
                'desc' => __('Jumlah data yang ditampilkan per halaman', 'wilayah-indonesia')
            ]
        );

        add_settings_field(
            'enable_caching',
            __('Aktifkan Cache', 'wilayah-indonesia'),
            [$this, 'renderCheckboxField'],
            self::OPTION_GROUP,
            'wilayah_general_section',
            [
                'id' => 'enable_caching',
                'desc' => __('Aktifkan caching untuk performa lebih baik', 'wilayah-indonesia')
            ]
        );
    }

    /**
     * Get general options dengan default values
     */
    public function getGeneralOptions(): array {
        $options = get_option(self::GENERAL_OPTIONS, []);
        return wp_parse_args($options, $this->default_options);
    }

    /**
     * Get API related settings
     */
    private function getApiSettings(): array {
        $options = $this->getGeneralOptions();
        return [
            'enable_api' => $options['enable_api'],
            'api_key' => $options['api_key']
        ];
    }

    /**
     * Get display related settings
     */
    private function getDisplaySettings(): array {
        $options = $this->getGeneralOptions();
        return [
            'display_format' => $options['display_format'],
            'datatables_language' => $options['datatables_language']
        ];
    }

    /**
     * Get system related settings
     */
    private function getSystemSettings(): array {
        $options = $this->getGeneralOptions();
        return [
            'enable_caching' => $options['enable_caching'],
            'cache_duration' => $options['cache_duration'],
            'log_enabled' => $options['log_enabled']
        ];
    }

    /**
     * Save general settings dengan validasi
     */
    public function saveGeneralSettings(array $input): bool {
        if (!is_array($input)) {
            return false;
        }

        $sanitized = $this->sanitizeOptions($input);
        return $this->updateGeneralOptions($sanitized);
    }

    /**
     * Update general options
     */
    public function updateGeneralOptions(array $new_options): bool {
        $options = $this->sanitizeOptions($new_options);
        
        if (empty($options)) {
            return false;
        }

        return update_option(self::GENERAL_OPTIONS, $options);
    }

    /**
     * Sanitize all option values
     */
    private function sanitizeOptions(array $options): array {
        $sanitized = [];
        
        // Sanitize records per page
        if (isset($options['records_per_page'])) {
            $sanitized['records_per_page'] = absint($options['records_per_page']);
            if ($sanitized['records_per_page'] < 5) {
                $sanitized['records_per_page'] = 5;
            }
        }

        // Sanitize enable caching
        if (isset($options['enable_caching'])) {
            $sanitized['enable_caching'] = (bool) $options['enable_caching'];
        }

        // Sanitize cache duration
        if (isset($options['cache_duration'])) {
            $sanitized['cache_duration'] = absint($options['cache_duration']);
            if ($sanitized['cache_duration'] < 3600) { // Minimum 1 hour
                $sanitized['cache_duration'] = 3600;
            }
        }

        // Sanitize datatables language
        if (isset($options['datatables_language'])) {
            $sanitized['datatables_language'] = sanitize_key($options['datatables_language']);
        }

        // Sanitize display format
        if (isset($options['display_format'])) {
            $sanitized['display_format'] = in_array($options['display_format'], ['hierarchical', 'flat']) 
                ? $options['display_format'] 
                : 'hierarchical';
        }

        // Sanitize API settings
        if (isset($options['enable_api'])) {
            $sanitized['enable_api'] = (bool) $options['enable_api'];
        }

        if (isset($options['api_key'])) {
            $sanitized['api_key'] = sanitize_key($options['api_key']);
        }

        // Sanitize logging
        if (isset($options['log_enabled'])) {
            $sanitized['log_enabled'] = (bool) $options['log_enabled'];
        }

        return $sanitized;
    }

    /**
     * Delete all plugin options
     */
    public function deleteOptions(): bool {
        return delete_option(self::GENERAL_OPTIONS);
    }

    /**
     * Render methods for settings fields
     */
    public function renderGeneralSection() {
        echo '<p>' . __('Pengaturan umum untuk plugin Wilayah Indonesia.', 'wilayah-indonesia') . '</p>';
    }

    public function renderNumberField($args) {
        $options = $this->getGeneralOptions();
        $value = $options[$args['id']] ?? '';
        
        printf(
            '<input type="number" id="%1$s" name="wilayah_indonesia_general_options[%1$s]" value="%2$s" class="regular-text">',
            esc_attr($args['id']),
            esc_attr($value)
        );
        
        if (isset($args['desc'])) {
            printf('<p class="description">%s</p>', esc_html($args['desc']));
        }
    }

    public function renderCheckboxField($args) {
        $options = $this->getGeneralOptions();
        $checked = isset($options[$args['id']]) ? checked($options[$args['id']], true, false) : '';
        
        printf(
            '<label><input type="checkbox" id="%1$s" name="wilayah_indonesia_general_options[%1$s]" value="1" %2$s></label>',
            esc_attr($args['id']),
            $checked
        );
        
        if (isset($args['desc'])) {
            printf('<p class="description">%s</p>', esc_html($args['desc']));
        }
    }
}
