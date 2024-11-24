<?php
/**
 * File: SettingsModel.php
 * Path: /wilayah-indonesia/src/Models/Settings/SettingsModel.php
 * Description: Model untuk mengelola pengaturan umum plugin
 * Last modified: 2024-11-23
 * Changelog:
 *   - 2024-11-23: Initial creation
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
    ];

    public function getGeneralOptions(): array {
        $options = get_option(self::GENERAL_OPTIONS, []);
        return wp_parse_args($options, $this->default_options);
    }

    public function updateGeneralOptions(array $new_options): bool {
        $options = $this->sanitizeOptions($new_options);
        
        if (empty($options)) {
            return false;
        }

        return update_option(self::GENERAL_OPTIONS, $options);
    }

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

        return $sanitized;
    }

    public function deleteOptions(): bool {
        return delete_option(self::GENERAL_OPTIONS);
    }
}