<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Uninstall class for Popup Builder Block
 */
class PopupBuilderBlock_Uninstaller {
    
    /**
     * Run the uninstall process
     */
    public static function uninstall() {
        // Get the uninstall setting value
        $uninstall_data = get_option('pbb-settings-tabs', []);
        $uninstall = $uninstall_data['uninstall-data'] ?? [];

        // Check if 'status' is set to 'active'
        if (isset($uninstall['status']) && $uninstall['status'] === 'active') {
            self::delete_options();
            self::delete_tables();
            self::delete_transients();
            self::delete_usermeta();
            self::delete_postmeta();
        }
    }

    /**
     * Delete plugin options
     */
    private static function delete_options() {
        $options_to_delete = [
            'pbb-settings-tabs',
            'pbb_settings_list',
            'pbb_db_version',
            'pbb_fse_fonts',
            '__pbb_oppai__',
            '__pbb_license_key__',
            'popup_builder_block_pro_installed_time',
            'popup_builder_block_pro_version',
        ];

        foreach ($options_to_delete as $option) {
            delete_option($option);
            delete_site_option($option); // For multisite compatibility
        }
    }

    /**
     * Delete custom database tables
     */
    private static function delete_tables() {
        global $wpdb;

        $tables_to_delete = [
            $wpdb->prefix . 'pbb_log_browsers',
            $wpdb->prefix . 'pbb_log_countries',
            $wpdb->prefix . 'pbb_log_referrers',
            $wpdb->prefix . 'pbb_logs',
            $wpdb->prefix . 'pbb_subscribers',
            $wpdb->prefix . 'pbb_browsers',
            $wpdb->prefix . 'pbb_countries',
            $wpdb->prefix . 'pbb_referrers',
            $wpdb->prefix . 'pbb_ab_test_variants',
            $wpdb->prefix . 'pbb_ab_tests',
        ];

        foreach ($tables_to_delete as $table) {
            $wpdb->query( sprintf( 'DROP TABLE IF EXISTS `%s`', esc_sql( $table ) ) );
        }
    }

    /**
     * Delete transients
     */
    private static function delete_transients() {
        $transients_to_delete = [
            // some transients
        ];

        foreach ($transients_to_delete as $transient) {
            delete_transient($transient);
            delete_site_transient($transient); // For multisite
        }
    }

    /**
     * Delete usermeta
     */
    private static function delete_usermeta() {
        global $wpdb;

        $usermeta_keys_to_delete = [
            // some usermeta keys
        ];

        foreach ($usermeta_keys_to_delete as $meta_key) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s", $meta_key));
        }
    }

    /**
     * Delete postmeta
     */
    private static function delete_postmeta() {
        global $wpdb;

        $postmeta_keys_to_delete = [
            'popup_builder_block_settings',
        ];

        foreach ($postmeta_keys_to_delete as $meta_key) {
            $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s", $meta_key));
        }
    }
}

// Execute uninstall
PopupBuilderBlock_Uninstaller::uninstall();
