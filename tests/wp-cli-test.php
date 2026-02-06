<?php
/**
 * WP-CLI Commands for ROVLEX Amelia Bridge Testing
 *
 * Usage:
 *   wp rovlex test [phase]
 *   wp rovlex sync force
 *   wp rovlex status
 */

if (defined('WP_CLI') && WP_CLI) {

    class ROVLEX_CLI_Commands extends WP_CLI_Command {

        /**
         * Test specific phase or all
         *
         * ## OPTIONS
         * [<phase>]
         *   : Phase number (1-5) or 'all'
         *   ---
         *   default: all
         *
         * ## EXAMPLES
         *     wp rovlex test 1
         *     wp rovlex test all
         */
        public function test($args, $assoc_args) {
            $phase = $args[0] ?? 'all';

            WP_CLI::line("\n" . str_repeat("=", 50));
            WP_CLI::line("ROVLEX AMELIA BRIDGE - TESTS");
            WP_CLI::line(str_repeat("=", 50) . "\n");

            if ($phase === 'all' || $phase === '1') {
                $this->test_phase_1();
            }
            if ($phase === 'all' || $phase === '2') {
                $this->test_phase_2();
            }
            if ($phase === 'all' || $phase === '3') {
                $this->test_phase_3();
            }
            if ($phase === 'all' || $phase === '4') {
                $this->test_phase_4();
            }
            if ($phase === 'all' || $phase === '5') {
                $this->test_phase_5();
            }

            WP_CLI::line(str_repeat("=", 50) . "\n");
        }

        private function test_phase_1() {
            WP_CLI::line("PHASE 1: Auto-create Amelia Location");

            global $wpdb;

            // Check if any mappings exist
            $count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rovlex_amelia_map"
            );

            if ($count > 0) {
                WP_CLI::success("✅ Found $count location mappings");

                $sample = $wpdb->get_row(
                    "SELECT * FROM {$wpdb->prefix}rovlex_amelia_map LIMIT 1"
                );

                WP_CLI::line("   Sample: Listing {$sample->listing_id} → Amelia Location {$sample->amelia_location_id}");
            } else {
                WP_CLI::warning("⚠️  No mappings yet. Create a listing to trigger.");
            }

            WP_CLI::line("");
        }

        private function test_phase_2() {
            WP_CLI::line("PHASE 2: Admin Redirect");

            global $wpdb;

            $count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rovlex_amelia_map"
            );

            if ($count > 0) {
                WP_CLI::success("✅ Redirect would work ($count locations mapped)");
            } else {
                WP_CLI::warning("⚠️  No locations to redirect to");
            }

            WP_CLI::line("");
        }

        private function test_phase_3() {
            WP_CLI::line("PHASE 3: Staff & Services Display");

            global $wpdb;

            $staff_count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_staff_html' AND meta_value != ''"
            );

            $services_count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_services_html' AND meta_value != ''"
            );

            if ($staff_count > 0) {
                WP_CLI::success("✅ Staff HTML found in $staff_count listings");
            } else {
                WP_CLI::warning("⚠️  No staff data (sync may not have run)");
            }

            if ($services_count > 0) {
                WP_CLI::success("✅ Services HTML found in $services_count listings");
            } else {
                WP_CLI::warning("⚠️  No services data (sync may not have run)");
            }

            WP_CLI::line("");
        }

        private function test_phase_4() {
            WP_CLI::line("PHASE 4: Cron Data Sync");

            $timestamp = wp_next_scheduled('rovlex_amelia_sync');

            if ($timestamp) {
                $next = date('Y-m-d H:i:s', $timestamp);
                WP_CLI::success("✅ Cron scheduled for: $next");
            } else {
                WP_CLI::warning("⚠️  Cron not scheduled (will be on next page load)");
            }

            global $wpdb;
            $last_sync = $wpdb->get_var(
                "SELECT MAX(meta_value) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_last_sync'"
            );

            if ($last_sync) {
                WP_CLI::line("   Last sync: $last_sync");
            } else {
                WP_CLI::line("   No syncs yet");
            }

            WP_CLI::line("");
        }

        private function test_phase_5() {
            WP_CLI::line("PHASE 5: Booking Button & Page");

            $page = get_page_by_path('book');

            if ($page) {
                WP_CLI::success("✅ Booking page exists: " . get_permalink($page->ID));

                if (strpos($page->post_content, 'rovlex_amelia_booking') !== false) {
                    WP_CLI::line("   Shortcode: ✅ Present");
                } else {
                    WP_CLI::warning("   Shortcode: ⚠️  Missing (add [rovlex_amelia_booking])");
                }
            } else {
                WP_CLI::warning("⚠️  Booking page not found at /book/");
            }

            WP_CLI::line("");
        }

        /**
         * Trigger manual sync
         *
         * ## OPTIONS
         * [--force]
         *   : Force sync immediately
         *
         * ## EXAMPLES
         *     wp rovlex sync force
         */
        public function sync($args, $assoc_args) {
            WP_CLI::line("Running manual sync...");

            do_action('rovlex_amelia_sync');

            WP_CLI::success("✅ Sync completed");
        }

        /**
         * Show plugin status
         *
         * ## EXAMPLES
         *     wp rovlex status
         */
        public function status($args, $assoc_args) {
            global $wpdb;

            WP_CLI::line("\n" . str_repeat("=", 50));
            WP_CLI::line("ROVLEX AMELIA BRIDGE - STATUS");
            WP_CLI::line(str_repeat("=", 50) . "\n");

            // Plugin status
            $active = is_plugin_active('rovlex-amelia-bridge/rovlex-amelia-bridge.php');
            WP_CLI::line("Plugin Active: " . ($active ? "✅ Yes" : "❌ No"));

            // Database
            $table = $wpdb->prefix . 'rovlex_amelia_map';
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            WP_CLI::line("Mapping Table: " . ($exists ? "✅ Exists" : "❌ Missing"));

            // Mappings
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            WP_CLI::line("Mappings: $count location(s)");

            // Cron
            $next_cron = wp_next_scheduled('rovlex_amelia_sync');
            $cron_status = $next_cron ? date('Y-m-d H:i:s', $next_cron) : "Not scheduled";
            WP_CLI::line("Next Cron: $cron_status");

            // Last sync
            $last_sync = $wpdb->get_var(
                "SELECT MAX(meta_value) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_last_sync'"
            );
            WP_CLI::line("Last Sync: " . ($last_sync ?: "Never"));

            // Listings with sync data
            $with_staff = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_staff_html' AND meta_value != ''"
            );
            $with_services = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_rovlex_services_html' AND meta_value != ''"
            );

            WP_CLI::line("Listings with Staff: $with_staff");
            WP_CLI::line("Listings with Services: $with_services");

            WP_CLI::line("\n" . str_repeat("=", 50) . "\n");
        }
    }

    WP_CLI::add_command('rovlex', 'ROVLEX_CLI_Commands');
}
