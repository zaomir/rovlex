<?php
/**
 * ROVLEX Instant Fix - Complete Plugin Update
 * One-file solution to update all plugin classes
 *
 * Usage:
 * 1. Upload to: /var/www/rovlex.com/public_html/instant-fix.php
 * 2. Open: https://rovlex.com/instant-fix.php
 * 3. Done!
 */

$d = dirname(__FILE__) . '/wp-content/plugins/rovlex-amelia-bridge';

echo "<!DOCTYPE html><html><head><title>ROVLEX Fix</title><style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}pre{background:#252526;padding:15px;border-radius:5px}.ok{color:#4ec9b0}.err{color:#f48771}</style></head><body>";
echo "<h1>🚀 ROVLEX Amelia Bridge - Instant Fix</h1>";
echo "<pre>";

$files = [
    'includes/class-location-sync.php' => '<?php
class Rovlex_Location_Sync {
    public function __construct() {
        add_action("save_post_listing", [$this, "on_listing_saved"], 20, 3);
        add_action("listeo_after_submit_listing", [$this, "on_listing_created"], 10, 1);
    }
    public function on_listing_saved($id, $p, $u) {
        if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($id)) return;
        if (!in_array($p->post_status, ["publish", "pending"])) return;
        $this->sync_location($id);
    }
    public function on_listing_created($id) { $this->sync_location($id); }
    private function sync_location($id) {
        global $wpdb;
        $post = get_post($id);
        if (!$post) return;
        $name = $post->post_title;
        $desc = wp_trim_words($post->post_content, 50);
        $addr = get_post_meta($id, "_address", true) ?: "";
        $phone = get_post_meta($id, "_phone", true) ?: "";
        $lat = get_post_meta($id, "_geolocation_lat", true);
        $lng = get_post_meta($id, "_geolocation_long", true);
        $existing = $wpdb->get_var($wpdb->prepare("SELECT amelia_location_id FROM {$wpdb->prefix}rovlex_amelia_map WHERE listing_id = %d", $id));
        if ($existing) {
            $wpdb->update("{$wpdb->prefix}amelia_locations", ["name"=>$name, "address"=>$addr, "phone"=>$phone, "latitude"=>$lat?:0, "longitude"=>$lng?:0, "description"=>$desc], ["id"=>$existing], ["%s","%s","%s","%f","%f","%s"], ["%d"]);
        } else {
            $r = $wpdb->insert("{$wpdb->prefix}amelia_locations", ["status"=>"visible", "name"=>$name, "description"=>$desc, "address"=>$addr, "phone"=>$phone, "latitude"=>$lat?:0, "longitude"=>$lng?:0], ["%s","%s","%s","%s","%s","%f","%f"]);
            if ($r) {
                $aid = $wpdb->insert_id;
                $wpdb->insert("{$wpdb->prefix}rovlex_amelia_map", ["listing_id"=>$id, "amelia_location_id"=>$aid], ["%d","%d"]);
                update_post_meta($id, "_amelia_location_id", $aid);
                error_log("ROVLEX: Created Location ID=$aid for listing $id");
            }
        }
    }
    public static function get_amelia_location_id($id) { return get_post_meta($id, "_amelia_location_id", true); }
}',

    'includes/class-admin-redirect.php' => '<?php
class Rovlex_Admin_Redirect {
    public function __construct() {
        add_filter("listeo_submit_redirect", [$this, "redirect_to_amelia"], 10, 2);
        add_action("admin_notices", [$this, "show_amelia_notice"]);
    }
    public function redirect_to_amelia($url, $id) {
        $aid = Rovlex_Location_Sync::get_amelia_location_id($id);
        if ($aid) { return admin_url("admin.php?page=wpamelia-employees&location=$aid&rovlex_listing=$id"); }
        return $url;
    }
    public function show_amelia_notice() {
        if (!isset($_GET["rovlex_listing"])) return;
        $id = intval($_GET["rovlex_listing"]);
        $title = get_the_title($id);
        echo "<div class=\"notice notice-info is-dismissible\"><p><strong>✅ Listing \"$title\" created!</strong></p><p>Add staff & services in Amelia.</p></div>";
    }
}',

    'includes/class-data-sync.php' => '<?php
class Rovlex_Data_Sync {
    public function __construct() {
        add_action("init", [$this, "schedule_sync"]);
        add_action("rovlex_amelia_sync", [$this, "run_sync"]);
    }
    public function schedule_sync() {
        if (!wp_next_scheduled("rovlex_amelia_sync")) {
            wp_schedule_event(time(), "fifteen_minutes", "rovlex_amelia_sync");
        }
    }
    public function run_sync() {
        error_log("ROVLEX: Sync running");
    }
}
add_filter("cron_schedules", function($s) { $s["fifteen_minutes"]=["interval"=>900,"display"=>"Every 15 minutes"]; return $s; });',

    'includes/class-listing-display.php' => '<?php
class Rovlex_Listing_Display {
    public function __construct() {
        add_action("wp_enqueue_scripts", [$this, "enqueue_styles"]);
        add_action("init", [$this, "register_booking_page"]);
        add_shortcode("rovlex_amelia_booking", [$this, "booking_page_shortcode"]);
    }
    public function enqueue_styles() {
        wp_enqueue_style("rovlex-amelia", plugins_url("assets/styles.css", dirname(__FILE__)), [], "1.0.0");
    }
    public function register_booking_page() {
        if (!get_page_by_path("book") && !get_option("rovlex_booking_page_created")) {
            wp_insert_post(["post_title"=>"Book an Appointment", "post_name"=>"book", "post_content"=>"[rovlex_amelia_booking]", "post_status"=>"publish", "post_type"=>"page"]);
            update_option("rovlex_booking_page_created", true);
        }
    }
    public function booking_page_shortcode() {
        $l = isset($_GET["location"]) ? intval($_GET["location"]) : 0;
        return $l ? do_shortcode("[ameliabooking location=\"$l\"]") : "<p>Select a salon</p>";
    }
}',
];

$ok = 0;
$bad = 0;

foreach ($files as $path => $code) {
    $full = $d . '/' . $path;
    if (file_put_contents($full, $code) > 0) {
        echo "<span class=\"ok\">✅</span> $path\n";
        $ok++;
    } else {
        echo "<span class=\"err\">❌</span> $path\n";
        $bad++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "<span class=\"ok\">✅ COMPLETE: $ok files updated</span>\n";
if ($bad) { echo "<span class=\"err\">⚠️ Failed: $bad files</span>\n"; }
echo "\nNext steps:\n";
echo "1. Delete this file (instant-fix.php)\n";
echo "2. Reload plugin in wp-admin\n";
echo "3. Test the plugin\n";
echo "</pre></body></html>";
?>
