<?php
/**
 * Plugin Name: ROVLEX Amelia Bridge
 * Description: Integration of Amelia Booking with Listeo Listings
 * Version: 1.0.0
 * Author: ROVLEX Team
 * Text Domain: rovlex-amelia-bridge
 */

if (!defined('ABSPATH')) exit;

// Constants
define('ROVLEX_AB_VERSION', '1.0.0');
define('ROVLEX_AB_PATH', plugin_dir_path(__FILE__));
define('ROVLEX_AB_URL', plugin_dir_url(__FILE__));

// Load classes
require_once ROVLEX_AB_PATH . 'includes/class-location-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-data-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-listing-display.php';
require_once ROVLEX_AB_PATH . 'includes/class-admin-redirect.php';

// Load WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    require_once ROVLEX_AB_PATH . 'tests/wp-cli-test.php';
}

// Initialize plugin
add_action('plugins_loaded', function() {
    // Check if Amelia is active
    $amelia_active = defined('AMELIA_BOOKING_PLUGIN_NAME') || class_exists('AmeliaBooking\Plugin');

    if (!$amelia_active) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>ROVLEX Amelia Bridge:</strong> Amelia Booking plugin must be active.';
            echo '</p></div>';
        });
        return;
    }

    // Initialize modules
    new Rovlex_Location_Sync();
    new Rovlex_Data_Sync();
    new Rovlex_Listing_Display();
    new Rovlex_Admin_Redirect();

    error_log('ROVLEX Amelia Bridge: Plugin initialized successfully');
});

// Create mapping table on activation
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rovlex_amelia_map (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id bigint(20) UNSIGNED NOT NULL,
        amelia_location_id bigint(20) UNSIGNED NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY listing_id (listing_id),
        UNIQUE KEY amelia_location_id (amelia_location_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    error_log('ROVLEX Amelia Bridge: Activation hook completed');
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('rovlex_amelia_sync');
    error_log('ROVLEX Amelia Bridge: Deactivated');
});
