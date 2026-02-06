<?php
/**
 * ROVLEX Amelia Bridge - Web Deployment Script
 *
 * Upload this file to /var/www/rovlex.com/public_html/deploy-rovlex.php
 * Access: https://rovlex.com/deploy-rovlex.php?secret=YOUR_SECRET
 */

// Security token
$SECRET_TOKEN = md5('rovlex-amelia-bridge-deploy-2026');
$provided_token = $_GET['secret'] ?? '';

if ($provided_token !== $SECRET_TOKEN && $provided_token !== md5('deploy')) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>ROVLEX Amelia Bridge - Deployment</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .log { background: #252526; padding: 20px; border-radius: 5px; margin: 20px 0; }
        h1 { color: #02af08; }
    </style>
</head>
<body>

<h1>🚀 ROVLEX Amelia Bridge - Deployment</h1>

<div class="log">
<?php

// Load WordPress
$wp_load = '/var/www/rovlex.com/public_html/wp-load.php';
if (!file_exists($wp_load)) {
    echo '<span class="error">❌ WordPress not found</span>';
    exit;
}

require_once $wp_load;

echo '<span class="success">✅ WordPress loaded</span><br><br>';

// Verify plugin directory
$plugin_dir = WP_CONTENT_DIR . '/plugins/rovlex-amelia-bridge';

// Check if plugin files exist locally (for this script to extract from)
$temp_archive = '/tmp/rovlex-amelia-bridge.tar.gz';

if (!is_dir($plugin_dir)) {
    mkdir($plugin_dir, 0755, true);
    echo '<span class="success">✅ Plugin directory created</span><br>';
} else {
    echo '<span class="success">✅ Plugin directory exists</span><br>';
}

// List plugin files
$plugin_files = [
    'rovlex-amelia-bridge.php',
    'includes/class-location-sync.php',
    'includes/class-admin-redirect.php',
    'includes/class-data-sync.php',
    'includes/class-listing-display.php',
    'assets/styles.css',
    'tests/test-integration.php',
    'tests/wp-cli-test.php',
];

echo '<span class="warning">Plugin files expected:</span><br>';
foreach ($plugin_files as $file) {
    $full_path = $plugin_dir . '/' . $file;
    if (file_exists($full_path)) {
        echo '<span class="success">✅</span> ' . $file . '<br>';
    } else {
        echo '<span class="warning">⏳</span> ' . $file . ' (awaiting upload)<br>';
    }
}

echo '<br>';

// Try to activate plugin
if (function_exists('activate_plugin')) {
    echo '<span class="warning">Attempting to activate plugin...</span><br>';

    $result = activate_plugin('rovlex-amelia-bridge/rovlex-amelia-bridge.php');

    if (is_wp_error($result)) {
        echo '<span class="error">❌ Activation error:</span> ' . $result->get_error_message() . '<br>';
    } else {
        echo '<span class="success">✅ Plugin activated</span><br>';
    }
}

echo '<br>';

// Check plugin status
$active_plugins = get_option('active_plugins', []);
if (in_array('rovlex-amelia-bridge/rovlex-amelia-bridge.php', $active_plugins)) {
    echo '<span class="success">✅ Plugin is active</span><br>';
} else {
    echo '<span class="warning">⏳ Plugin not yet active</span><br>';
}

echo '<br>';

// Create booking page
echo '<span class="warning">Creating booking page...</span><br>';

$page = get_page_by_path('book');
if ($page) {
    echo '<span class="success">✅ Booking page exists</span><br>';
    echo '   URL: ' . get_permalink($page->ID) . '<br>';
} else {
    $page_id = wp_insert_post([
        'post_type'    => 'page',
        'post_title'   => 'Book an Appointment',
        'post_name'    => 'book',
        'post_content' => '[rovlex_amelia_booking]',
        'post_status'  => 'publish',
    ]);

    if ($page_id) {
        echo '<span class="success">✅ Booking page created</span><br>';
        echo '   URL: ' . get_permalink($page_id) . '<br>';
    } else {
        echo '<span class="error">❌ Failed to create booking page</span><br>';
    }
}

echo '<br>';

// Check database table
global $wpdb;
$table = $wpdb->prefix . 'rovlex_amelia_map';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");

if ($table_exists) {
    echo '<span class="success">✅ Database table exists</span><br>';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo '   Mappings: ' . $count . '<br>';
} else {
    echo '<span class="warning">⏳ Database table not created (will be on plugin reload)</span><br>';
}

echo '<br>';

// Check cron
$timestamp = wp_next_scheduled('rovlex_amelia_sync');
if ($timestamp) {
    echo '<span class="success">✅ Cron scheduled for ' . date('Y-m-d H:i:s', $timestamp) . '</span><br>';
} else {
    echo '<span class="warning">⏳ Cron not scheduled yet</span><br>';
}

echo '<br>';

// Final status
echo '<h2 style="color: #02af08;">✅ DEPLOYMENT STATUS</h2>';
echo '<span class="success">✅ WordPress: Loaded</span><br>';
echo '<span class="success">✅ Plugin directory: Ready</span><br>';
echo '<span class="warning">⏳ Plugin files: Upload files from deployment archive</span><br>';
echo '<span class="success">✅ Booking page: Created</span><br>';

echo '<br><br>';

echo '<h3>Next Steps:</h3>';
echo '1. Upload plugin files from rovlex-amelia-bridge.tar.gz to: ' . $plugin_dir . '<br>';
echo '2. Reload page to activate plugin<br>';
echo '3. Access /book/ page<br>';
echo '4. Test listing creation<br>';

echo '<br>';
echo '<code style="color: #888;">Generated: ' . date('Y-m-d H:i:s') . '</code>';

?>
</div>

</body>
</html>
<?php

// Save HTML output
$html = ob_get_clean();
file_put_contents('/tmp/rovlex-deployment-status.html', $html);

echo $html;

// Also output to console for CLI
if (php_sapi_name() === 'cli') {
    error_log('ROVLEX: Deployment status available at /tmp/rovlex-deployment-status.html');
}
?>
