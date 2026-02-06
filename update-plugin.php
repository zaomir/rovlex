<?php
/**
 * ROVLEX Amelia Bridge - Plugin Update Script
 *
 * Updates plugin files from full versions (stub → full)
 *
 * Usage:
 * 1. Upload to: /var/www/rovlex.com/public_html/update-plugin.php
 * 2. Access: https://rovlex.com/update-plugin.php?key=update
 * 3. Wait for completion
 * 4. Delete this file
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'update') {
    http_response_code(403);
    die('Access denied');
}

// Load WordPress
if (!function_exists('wp_load_alloptions')) {
    $wp_config_paths = [
        '/var/www/rovlex_com_usr35/data/www/rovlex.com/wp-config.php',
        '/var/www/rovlex.com/wp-config.php',
        dirname(__FILE__) . '/wp-config.php',
    ];

    foreach ($wp_config_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }

    if (file_exists(dirname(WP_CONTENT_DIR) . '/wp-load.php')) {
        require_once dirname(WP_CONTENT_DIR) . '/wp-load.php';
    }
}

$plugin_dir = WP_CONTENT_DIR . '/plugins/rovlex-amelia-bridge';

echo "════════════════════════════════════════════════\n";
echo "ROVLEX Amelia Bridge - Plugin Update\n";
echo "════════════════════════════════════════════════\n\n";

// Full class files from GitHub raw
$files = [
    'includes/class-location-sync.php' => 'https://raw.githubusercontent.com/zaomir/rovlex/claude-read-integration-plan-t56To/includes/class-location-sync.php',
    'includes/class-admin-redirect.php' => 'https://raw.githubusercontent.com/zaomir/rovlex/claude-read-integration-plan-t56To/includes/class-admin-redirect.php',
    'includes/class-data-sync.php' => 'https://raw.githubusercontent.com/zaomir/rovlex/claude-read-integration-plan-t56To/includes/class-data-sync.php',
    'includes/class-listing-display.php' => 'https://raw.githubusercontent.com/zaomir/rovlex/claude-read-integration-plan-t56To/includes/class-listing-display.php',
];

$updated = 0;
$failed = 0;

foreach ($files as $local_path => $github_url) {
    $full_path = $plugin_dir . '/' . $local_path;

    echo "Updating: $local_path\n";

    // Download from GitHub
    $content = @file_get_contents($github_url);

    if ($content === false) {
        echo "  ❌ Failed to download\n";
        $failed++;
        continue;
    }

    // Save to local file
    if (file_put_contents($full_path, $content) === false) {
        echo "  ❌ Failed to save\n";
        $failed++;
        continue;
    }

    echo "  ✅ Updated (" . strlen($content) . " bytes)\n";
    $updated++;
}

echo "\n════════════════════════════════════════════════\n";
echo "✅ UPDATE COMPLETE!\n";
echo "════════════════════════════════════════════════\n\n";

echo "Updated: $updated files\n";
echo "Failed: $failed files\n\n";

if ($failed === 0) {
    echo "✅ All files updated successfully!\n";
    echo "\nNow:\n";
    echo "1. Reload plugin in wp-admin\n";
    echo "2. Delete this file (update-plugin.php)\n";
    echo "3. Test the plugin\n";
} else {
    echo "⚠️  Some files failed. Try downloading manually from GitHub.\n";
}

echo "\n════════════════════════════════════════════════\n";
?>
