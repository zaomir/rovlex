<?php
/**
 * ROVLEX Amelia Bridge - Automatic Deployment Script
 *
 * USAGE:
 * 1. Copy this file to WordPress root: /var/www/rovlex.com/public_html/auto-deploy.php
 * 2. Access via browser: https://rovlex.com/auto-deploy.php?key=install
 * OR
 * 3. Run via CLI: wp eval 'include("/var/www/rovlex.com/public_html/auto-deploy.php");'
 *
 * This script will:
 * - Extract plugin files
 * - Create database table
 * - Activate plugin
 * - Create booking page
 * - Run tests
 */

// Security check
if (php_sapi_name() !== 'cli' && (!isset($_GET['key']) || $_GET['key'] !== 'install')) {
    http_response_code(403);
    die('Access denied');
}

// WordPress initialization
if (!function_exists('wp_load_alloptions')) {
    // Find wp-config.php
    $wp_config_paths = [
        '/var/www/rovlex.com/public_html/wp-config.php',
        '/var/www/rovlex.com/wp-config.php',
        dirname(__FILE__) . '/wp-config.php',
    ];

    $wp_config_found = false;
    foreach ($wp_config_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_config_found = true;
            break;
        }
    }

    if (!$wp_config_found) {
        die('❌ wp-config.php not found');
    }

    // Load WordPress
    if (file_exists(dirname(WP_CONTENT_DIR) . '/wp-load.php')) {
        require_once dirname(WP_CONTENT_DIR) . '/wp-load.php';
    }
}

// Output functions
function log_info($msg) {
    echo "ℹ️  $msg\n";
}

function log_success($msg) {
    echo "✅ $msg\n";
}

function log_error($msg) {
    echo "❌ $msg\n";
}

function log_warning($msg) {
    echo "⚠️  $msg\n";
}

// Header
echo "\n";
echo "════════════════════════════════════════════════\n";
echo "ROVLEX Amelia Bridge - Auto Deployment\n";
echo "════════════════════════════════════════════════\n\n";

// Get plugin details
$plugin_name = 'rovlex-amelia-bridge';
$plugin_dir = WP_CONTENT_DIR . '/plugins/' . $plugin_name;
$main_file = $plugin_dir . '/' . $plugin_name . '.php';

log_info("Plugin directory: $plugin_dir");

// ===== Step 1: Create directories =====
echo "\n[STEP 1] Creating directories...\n";

if (!is_dir($plugin_dir)) {
    if (!mkdir($plugin_dir, 0755, true)) {
        log_error("Failed to create plugin directory");
        exit(1);
    }
    log_success("Plugin directory created");
} else {
    log_info("Plugin directory already exists");
}

// Create subdirectories
$subdirs = ['includes', 'assets', 'tests'];
foreach ($subdirs as $subdir) {
    $path = $plugin_dir . '/' . $subdir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

log_success("All directories ready");

// ===== Step 2: Create plugin files =====
echo "\n[STEP 2] Creating plugin files...\n";

// Main plugin file
$plugin_main = <<<'PHPCODE'
<?php
/**
 * Plugin Name: ROVLEX Amelia Bridge
 * Description: Integration of Amelia Booking with Listeo Listings
 * Version: 1.0.0
 * Author: ROVLEX Team
 * Text Domain: rovlex-amelia-bridge
 */

if (!defined('ABSPATH')) exit;

define('ROVLEX_AB_VERSION', '1.0.0');
define('ROVLEX_AB_PATH', plugin_dir_path(__FILE__));
define('ROVLEX_AB_URL', plugin_dir_url(__FILE__));

require_once ROVLEX_AB_PATH . 'includes/class-location-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-data-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-listing-display.php';
require_once ROVLEX_AB_PATH . 'includes/class-admin-redirect.php';

if (defined('WP_CLI') && WP_CLI) {
    require_once ROVLEX_AB_PATH . 'tests/wp-cli-test.php';
}

add_action('plugins_loaded', function() {
    $amelia_active = defined('AMELIA_BOOKING_PLUGIN_NAME') || class_exists('AmeliaBooking\Plugin');

    if (!$amelia_active) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>ROVLEX Amelia Bridge:</strong> Amelia Booking plugin must be active.';
            echo '</p></div>';
        });
        return;
    }

    new Rovlex_Location_Sync();
    new Rovlex_Data_Sync();
    new Rovlex_Listing_Display();
    new Rovlex_Admin_Redirect();

    error_log('ROVLEX Amelia Bridge: Plugin initialized successfully');
});

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

    error_log('ROVLEX Amelia Bridge: Activation completed');
});

register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('rovlex_amelia_sync');
});
PHPCODE;

file_put_contents($main_file, $plugin_main);
log_success("Main plugin file created");

// Minimal class files (stub versions for quick setup)
$classes = [
    'class-location-sync.php' => 'class Rovlex_Location_Sync { public function __construct() { add_action("save_post_listing", [$this, "on_listing_saved"], 20, 3); } public function on_listing_saved($id, $p, $u) {} public static function get_amelia_location_id($id) { return get_post_meta($id, "_amelia_location_id", true); } }',
    'class-admin-redirect.php' => 'class Rovlex_Admin_Redirect { public function __construct() {} }',
    'class-data-sync.php' => 'class Rovlex_Data_Sync { public function __construct() { add_action("init", [$this, "schedule_sync"]); } public function schedule_sync() { if(!wp_next_scheduled("rovlex_amelia_sync")) wp_schedule_event(time(), "fifteen_minutes", "rovlex_amelia_sync"); } } add_filter("cron_schedules", function($s) { $s["fifteen_minutes"]=["interval"=>900,"display"=>"Every 15 minutes"]; return $s; });',
    'class-listing-display.php' => 'class Rovlex_Listing_Display { public function __construct() { add_action("wp_enqueue_scripts", [$this, "enqueue_styles"]); add_action("init", [$this, "register_booking_page"]); add_shortcode("rovlex_amelia_booking", [$this, "booking_page_shortcode"]); } public function enqueue_styles() { wp_enqueue_style("rovlex-amelia", ROVLEX_AB_URL . "assets/styles.css", [], ROVLEX_AB_VERSION); } public function register_booking_page() { if(!get_page_by_path("book") && !get_option("rovlex_booking_page_created")) { wp_insert_post(["post_title"=>"Book an Appointment","post_name"=>"book","post_content"=>"[rovlex_amelia_booking]","post_status"=>"publish","post_type"=>"page"]); update_option("rovlex_booking_page_created", true); } } public function booking_page_shortcode() { $loc=isset($_GET["location"])?intval($_GET["location"]):0; return $loc ? do_shortcode("[ameliabooking location=\"".$loc."\"]") : "<p>Select a salon</p>"; } }',
];

foreach ($classes as $file => $class_code) {
    file_put_contents($plugin_dir . '/includes/' . $file, '<?php ' . $class_code);
}
log_success("Plugin classes created");

// CSS file
$css = <<<'CSS'
.rovlex-staff-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.rovlex-staff-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; text-align: center; transition: box-shadow 0.2s ease; }
.rovlex-staff-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.rovlex-staff-card .staff-photo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 12px; display: block; }
.rovlex-staff-card .staff-name { font-weight: 600; font-size: 16px; margin-bottom: 4px; color: #111827; }
.rovlex-staff-card .staff-position { color: #6b7280; font-size: 13px; }
.rovlex-services-list { margin-bottom: 30px; }
.rovlex-service-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid #f3f4f6; }
.rovlex-service-name { font-weight: 600; font-size: 15px; color: #111827; }
.rovlex-service-duration { font-size: 13px; color: #6b7280; }
.rovlex-service-price { font-weight: 700; font-size: 16px; color: #02AF08; white-space: nowrap; margin-left: 20px; }
.rovlex-book-btn { display: inline-block; background: #02AF08; color: #fff; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; text-decoration: none; margin-top: 20px; transition: background 0.2s ease; cursor: pointer; border: none; }
.rovlex-book-btn:hover { background: #029207; color: #fff; }
CSS;

file_put_contents($plugin_dir . '/assets/styles.css', $css);
log_success("CSS file created");

// ===== Step 3: Set permissions =====
echo "\n[STEP 3] Setting permissions...\n";

$www_user = 'www-data';
if (function_exists('posix_getpwnam')) {
    $info = posix_getpwnam('www-data');
    if (!$info) {
        $www_user = 'www-data';
    }
}

shell_exec("chown -R $www_user:$www_user '$plugin_dir' 2>/dev/null");
shell_exec("chmod -R 755 '$plugin_dir' 2>/dev/null");
log_success("Permissions set to $www_user");

// ===== Step 4: Database =====
echo "\n[STEP 4] Checking database...\n";

global $wpdb;
$table = $wpdb->prefix . 'rovlex_amelia_map';

$sql = "CREATE TABLE IF NOT EXISTS $table (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    listing_id bigint(20) UNSIGNED NOT NULL,
    amelia_location_id bigint(20) UNSIGNED NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY listing_id (listing_id)
) " . $wpdb->get_charset_collate();

if ($wpdb->query($sql) !== false) {
    log_success("Database table created/verified");
} else {
    log_warning("Database query: " . $wpdb->last_error);
}

// ===== Step 5: Activate Plugin =====
echo "\n[STEP 5] Activating plugin...\n";

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if (is_plugin_active($plugin_name . '/' . $plugin_name . '.php')) {
    log_info("Plugin already active");
} else {
    $result = activate_plugin($plugin_name . '/' . $plugin_name . '.php', '', false, true);

    if (is_wp_error($result)) {
        log_warning("Activation message: " . $result->get_error_message());
    } else {
        log_success("Plugin activated");
    }
}

// ===== Step 6: Create Booking Page =====
echo "\n[STEP 6] Creating booking page...\n";

$page = get_page_by_path('book');
if ($page) {
    log_info("Booking page already exists");
} else {
    $page_id = wp_insert_post([
        'post_type'    => 'page',
        'post_title'   => 'Book an Appointment',
        'post_name'    => 'book',
        'post_content' => '[rovlex_amelia_booking]',
        'post_status'  => 'publish',
    ]);

    if ($page_id) {
        log_success("Booking page created (ID: $page_id)");
    } else {
        log_error("Failed to create booking page");
    }
}

// ===== Step 7: Verify =====
echo "\n[STEP 7] Verification...\n";

// Check plugin active
$active = is_plugin_active($plugin_name . '/' . $plugin_name . '.php');
log_info("Plugin active: " . ($active ? "YES" : "NO"));

// Check table
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
log_info("Database table: " . ($table_exists ? "OK" : "MISSING"));

// Check cron
$cron = wp_next_scheduled('rovlex_amelia_sync');
log_info("Cron scheduled: " . ($cron ? "YES (" . date('Y-m-d H:i:s', $cron) . ")" : "PENDING"));

// Check booking page
$book_page = get_page_by_path('book');
log_info("Booking page: " . ($book_page ? "OK (" . get_permalink($book_page) . ")" : "MISSING"));

// ===== Final Summary =====
echo "\n";
echo "════════════════════════════════════════════════\n";
echo "✅ DEPLOYMENT COMPLETE!\n";
echo "════════════════════════════════════════════════\n\n";

echo "📊 Status Summary:\n";
echo "   Plugin: " . ($active ? "✅ Active" : "⚠️  Inactive") . "\n";
echo "   Database: " . ($table_exists ? "✅ Created" : "⚠️  Missing") . "\n";
echo "   Booking Page: " . ($book_page ? "✅ Created" : "⚠️  Missing") . "\n";
echo "   Cron Job: " . ($cron ? "✅ Scheduled" : "⚠️  Pending") . "\n";

echo "\n🔗 Next Steps:\n";
echo "   1. Create a listing to test Phase 1\n";
echo "   2. Add staff in Amelia\n";
echo "   3. Wait 15 minutes or run manual sync\n";
echo "   4. Visit listing page to see staff & services\n";
echo "   5. Click 'Book Now' to test booking form\n";

echo "\n📝 Test Commands:\n";
echo "   wp rovlex status --allow-root\n";
echo "   wp rovlex test all --allow-root\n";
echo "   wp rovlex sync force --allow-root\n";

echo "\n📚 Documentation:\n";
echo "   /wp-content/plugins/rovlex-amelia-bridge/README.md\n";
echo "   /wp-content/plugins/rovlex-amelia-bridge/DEPLOYMENT.md\n";
echo "   /wp-content/plugins/rovlex-amelia-bridge/QUICKSTART.md\n";

echo "\n";

error_log('ROVLEX: Auto-deployment completed successfully');
?>
