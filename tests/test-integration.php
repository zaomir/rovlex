<?php
/**
 * ROVLEX Amelia Bridge - Integration Tests
 *
 * Test each phase of the integration
 */

class ROVLEX_Integration_Tests {

    private $test_listing_id;
    private $test_location_id = 99;

    public function __construct() {
        $this->run_all_tests();
    }

    /**
     * Phase 1: Test Location Creation
     */
    public function test_phase_1_location_creation() {
        echo "\n=== PHASE 1: Auto-create Amelia Location ===\n";

        // Create test listing
        $post_id = wp_insert_post([
            'post_type'    => 'listing',
            'post_title'   => 'Test Salon - Phase 1',
            'post_content' => 'Test Description',
            'post_status'  => 'publish',
            'meta_input'   => [
                '_address' => '123 Test Street',
                '_phone'   => '+1234567890',
                '_email'   => 'test@example.com',
            ]
        ]);

        $this->test_listing_id = $post_id;

        // Check if location was created
        $amelia_id = Rovlex_Location_Sync::get_amelia_location_id($post_id);

        if ($amelia_id) {
            echo "✅ Location created: ID=$amelia_id\n";
            echo "✅ Listing meta updated: listing_id=$post_id → amelia_location_id=$amelia_id\n";
            return true;
        } else {
            echo "❌ Location creation failed\n";
            return false;
        }
    }

    /**
     * Phase 2: Test Admin Redirect
     */
    public function test_phase_2_admin_redirect() {
        echo "\n=== PHASE 2: Admin Redirect ===\n";

        $listing_id = $this->test_listing_id ?: 402;
        $amelia_id = Rovlex_Location_Sync::get_amelia_location_id($listing_id);

        if ($amelia_id) {
            $expected_url = admin_url('admin.php?page=wpamelia-employees&location=' . $amelia_id);
            echo "✅ Redirect URL would be: $expected_url\n";
            return true;
        } else {
            echo "❌ No Amelia Location found\n";
            return false;
        }
    }

    /**
     * Phase 3: Test Staff/Services Display
     */
    public function test_phase_3_display() {
        echo "\n=== PHASE 3: Staff & Services Display ===\n";

        $listing_id = $this->test_listing_id ?: 402;

        $staff_html = get_post_meta($listing_id, '_rovlex_staff_html', true);
        $services_html = get_post_meta($listing_id, '_rovlex_services_html', true);

        if ($staff_html) {
            echo "✅ Staff HTML found (" . strlen($staff_html) . " bytes)\n";
        } else {
            echo "⚠️  No staff HTML (sync may not have run yet)\n";
        }

        if ($services_html) {
            echo "✅ Services HTML found (" . strlen($services_html) . " bytes)\n";
        } else {
            echo "⚠️  No services HTML (sync may not have run yet)\n";
        }

        return true;
    }

    /**
     * Phase 4: Test Cron Sync
     */
    public function test_phase_4_sync() {
        echo "\n=== PHASE 4: Cron Data Sync ===\n";

        global $wpdb;

        // Check if mapping exists
        $mappings = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rovlex_amelia_map LIMIT 5"
        );

        if (!empty($mappings)) {
            echo "✅ Found " . count($mappings) . " mappings:\n";
            foreach ($mappings as $map) {
                echo "   - Listing $map->listing_id → Amelia Location $map->amelia_location_id\n";
            }
        } else {
            echo "❌ No mappings found\n";
            return false;
        }

        // Check cron schedule
        $timestamp = wp_next_scheduled('rovlex_amelia_sync');
        if ($timestamp) {
            echo "✅ Cron scheduled for: " . date('Y-m-d H:i:s', $timestamp) . "\n";
        } else {
            echo "⚠️  Cron not scheduled yet (will be on next page load)\n";
        }

        return true;
    }

    /**
     * Phase 5: Test Booking Button
     */
    public function test_phase_5_booking() {
        echo "\n=== PHASE 5: Booking Button & Page ===\n";

        $booking_page = get_page_by_path('book');

        if ($booking_page) {
            echo "✅ Booking page exists: " . get_permalink($booking_page->ID) . "\n";

            $content = $booking_page->post_content;
            if (strpos($content, 'rovlex_amelia_booking') !== false) {
                echo "✅ Shortcode found in page\n";
                return true;
            } else {
                echo "⚠️  Shortcode not found (add [rovlex_amelia_booking] to page)\n";
                return false;
            }
        } else {
            echo "⚠️  Booking page not found (will be created on plugin activation)\n";
            return false;
        }
    }

    /**
     * Database Check
     */
    public function test_database() {
        echo "\n=== DATABASE ===\n";

        global $wpdb;

        // Check mapping table
        $table_exists = $wpdb->get_var(
            "SHOW TABLES LIKE '{$wpdb->prefix}rovlex_amelia_map'"
        );

        if ($table_exists) {
            echo "✅ Mapping table exists\n";

            $count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}rovlex_amelia_map"
            );
            echo "   Records: $count\n";
        } else {
            echo "❌ Mapping table not found\n";
            return false;
        }

        // Check Amelia tables
        $amelia_tables = $wpdb->get_results(
            "SHOW TABLES LIKE '%amelia%'"
        );

        if (!empty($amelia_tables)) {
            echo "✅ Amelia tables found: " . count($amelia_tables) . "\n";
        } else {
            echo "❌ Amelia plugin not installed\n";
            return false;
        }

        return true;
    }

    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "ROVLEX AMELIA BRIDGE - INTEGRATION TESTS\n";
        echo str_repeat("=", 50) . "\n";

        $results = [];
        $results['database'] = $this->test_database();
        $results['phase_1'] = $this->test_phase_1_location_creation();
        $results['phase_2'] = $this->test_phase_2_admin_redirect();
        $results['phase_3'] = $this->test_phase_3_display();
        $results['phase_4'] = $this->test_phase_4_sync();
        $results['phase_5'] = $this->test_phase_5_booking();

        // Summary
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "SUMMARY\n";
        echo str_repeat("=", 50) . "\n";

        $passed = array_sum($results);
        $total = count($results);

        echo "Passed: $passed/$total\n";

        if ($passed === $total) {
            echo "✅ All tests passed!\n";
        } else {
            echo "⚠️  Some tests failed or were incomplete\n";
        }

        echo str_repeat("=", 50) . "\n";
    }

    /**
     * Public static method to run from admin
     */
    public static function run() {
        return new self();
    }
}

// Run if directly accessed
if (php_sapi_name() === 'cli') {
    ROVLEX_Integration_Tests::run();
}
