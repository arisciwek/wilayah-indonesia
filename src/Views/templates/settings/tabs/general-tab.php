<?php
/**
 * File: general-tab.php
 * Path: /wilayah-indonesia/src/Views/templates/settings/tabs/general-tab.php 
 * Description: Tab pengaturan umum plugin
 * Version: 3.0.0
 * Last modified: 2024-11-28 11:45:00
 */

defined('ABSPATH') || exit;

$default_options = [
    'records_per_page' => 15,
    'enable_caching' => true,
    'cache_duration' => 43200,
    'datatables_language' => 'id'
];

$options = wp_parse_args($settings['general'] ?? [], $default_options);
?>

<div class="wilayah-settings-tab general-settings">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="general-settings-form">
        <?php wp_nonce_field('wilayah_settings_nonce'); ?>
        <input type="hidden" name="action" value="wilayah_save_settings">
        <input type="hidden" name="tab" value="general">

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="records_per_page"><?php _e('Data Per Halaman', 'wilayah-indonesia'); ?></label>
                </th>
                <td>
                    <input name="general[records_per_page]" 
                           type="number" 
                           id="records_per_page" 
                           value="<?php echo esc_attr($options['records_per_page']); ?>" 
                           min="5" 
                           max="100" 
                           step="5" 
                           class="small-text" 
                           required>
                    <p class="description">
                        <?php _e('Jumlah data yang ditampilkan per halaman di tabel (min: 5, max: 100)', 'wilayah-indonesia'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Caching', 'wilayah-indonesia'); ?></th>
                <td>
                    <fieldset>
                        <label for="enable_caching">
                            <input name="general[enable_caching]" 
                                   type="checkbox" 
                                   id="enable_caching" 
                                   value="1" 
                                   <?php checked(1, $options['enable_caching']); ?>>
                            <?php _e('Aktifkan caching data', 'wilayah-indonesia'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Cache akan mempercepat loading data dengan menyimpan hasil query sementara', 'wilayah-indonesia'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>

            <tr id="cache_duration_row" class="<?php echo $options['enable_caching'] ? '' : 'hidden'; ?>">
                <th scope="row">
                    <label for="cache_duration"><?php _e('Durasi Cache', 'wilayah-indonesia'); ?></label>
                </th>
                <td>
                    <select name="general[cache_duration]" id="cache_duration">
                        <?php
                        $durations = [
                            3600   => __('1 jam', 'wilayah-indonesia'),
                            7200   => __('2 jam', 'wilayah-indonesia'),
                            21600  => __('6 jam', 'wilayah-indonesia'),
                            43200  => __('12 jam', 'wilayah-indonesia'),
                            86400  => __('24 jam', 'wilayah-indonesia')
                        ];
                        foreach ($durations as $value => $label):
                            printf(
                                '<option value="%d" %s>%s</option>',
                                $value,
                                selected($options['cache_duration'], $value, false),
                                esc_html($label)
                            );
                        endforeach;
                        ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="datatables_language"><?php _e('Bahasa DataTables', 'wilayah-indonesia'); ?></label>
                </th>
                <td>
                    <select name="general[datatables_language]" id="datatables_language">
                        <?php
                        $languages = [
                            'id' => 'Indonesia',
                            'en' => 'English'
                        ];
                        foreach ($languages as $code => $name):
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($code),
                                selected($options['datatables_language'], $code, false),
                                esc_html($name)
                            );
                        endforeach;
                        ?>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
