<?php
/**
 * Phase 4: Data sync from Amelia → Listeo via cron
 */
class Rovlex_Data_Sync {

    public function __construct() {
        add_action('init', [$this, 'schedule_sync']);
        add_action('rovlex_amelia_sync', [$this, 'run_sync']);
        add_action('wp_ajax_rovlex_force_sync', [$this, 'ajax_force_sync']);
    }

    /**
     * Schedule cron job
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('rovlex_amelia_sync')) {
            wp_schedule_event(time(), 'fifteen_minutes', 'rovlex_amelia_sync');
        }
    }

    /**
     * Unschedule cron
     */
    public function unschedule_sync() {
        wp_clear_scheduled_hook('rovlex_amelia_sync');
    }

    /**
     * Main sync function
     */
    public function run_sync() {
        global $wpdb;

        // Get all mappings
        $mappings = $wpdb->get_results(
            "SELECT listing_id, amelia_location_id
             FROM {$wpdb->prefix}rovlex_amelia_map"
        );

        if (empty($mappings)) return;

        foreach ($mappings as $map) {
            $this->sync_listing($map->listing_id, $map->amelia_location_id);
        }

        error_log("ROVLEX: Sync completed. Processed " . count($mappings) . " listings.");
    }

    /**
     * Sync single listing
     */
    private function sync_listing($listing_id, $amelia_location_id) {
        global $wpdb;

        $currency = $this->get_currency();

        // Get staff (employees) for this location
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT u.id, u.firstName, u.lastName, u.email, u.phone,
                    u.pictureFullPath, u.pictureThumbPath, u.description
             FROM {$wpdb->prefix}amelia_users u
             WHERE u.locationId = %d
               AND u.type = 'provider'
               AND u.status = 'visible'
             ORDER BY u.firstName ASC",
            $amelia_location_id
        ));

        // Try alternative query if no results (different Amelia schema)
        if (empty($staff)) {
            $staff = $wpdb->get_results($wpdb->prepare(
                "SELECT u.id, u.firstName, u.lastName, u.email, u.phone,
                        u.pictureFullPath, u.pictureThumbPath, u.description
                 FROM {$wpdb->prefix}amelia_users u
                 INNER JOIN {$wpdb->prefix}amelia_providers_to_locations pl
                    ON u.id = pl.userId
                 WHERE pl.locationId = %d
                   AND u.type = 'provider'
                   AND u.status = 'visible'
                 ORDER BY u.firstName ASC",
                $amelia_location_id
            ));
        }

        // Get services for this location
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT s.id, s.name, s.price, s.duration, s.description,
                    s.pictureFullPath, s.pictureThumbPath
             FROM {$wpdb->prefix}amelia_services s
             INNER JOIN {$wpdb->prefix}amelia_providers_to_services ps
                ON s.id = ps.serviceId
             INNER JOIN {$wpdb->prefix}amelia_providers_to_locations pl
                ON ps.userId = pl.userId
             WHERE pl.locationId = %d
               AND s.status = 'visible'
             GROUP BY s.id
             ORDER BY s.price ASC",
            $amelia_location_id
        ));

        // Try alternative if no results
        if (empty($services)) {
            $services = $wpdb->get_results($wpdb->prepare(
                "SELECT s.id, s.name, s.price, s.duration, s.description,
                        s.pictureFullPath, s.pictureThumbPath
                 FROM {$wpdb->prefix}amelia_services s
                 WHERE s.status = 'visible'
                 LIMIT 0, 100",
                []
            ));
        }

        // Generate HTML for staff
        $staff_html = '';
        if (!empty($staff)) {
            foreach ($staff as $member) {
                $name = esc_html($member->firstName . ' ' . $member->lastName);
                $photo = $member->pictureThumbPath ? esc_url($member->pictureThumbPath) : '';
                $about = esc_html(wp_trim_words($member->description ?? '', 15));

                $staff_html .= '<div class="rovlex-staff-card">';
                if ($photo) {
                    $staff_html .= '<img src="' . $photo . '" alt="' . $name . '" class="staff-photo">';
                } else {
                    $staff_html .= '<div class="staff-photo" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:24px;color:#9ca3af;">👤</div>';
                }
                $staff_html .= '<div class="staff-name">' . $name . '</div>';
                if ($about) {
                    $staff_html .= '<div class="staff-position">' . $about . '</div>';
                }
                $staff_html .= '</div>';
            }
        }

        // Generate HTML for services
        $services_html = '';
        if (!empty($services)) {
            foreach ($services as $service) {
                $sname = esc_html($service->name);
                $price = $this->format_price($service->price, $currency);
                $duration = $this->format_duration($service->duration);

                $services_html .= '<div class="rovlex-service-item">';
                $services_html .= '<div class="rovlex-service-info">';
                $services_html .= '<div class="rovlex-service-name">' . $sname . '</div>';
                $services_html .= '<div class="rovlex-service-duration">' . $duration . '</div>';
                $services_html .= '</div>';
                $services_html .= '<div class="rovlex-service-price">' . $price . '</div>';
                $services_html .= '</div>';
            }
        }

        // Save to post meta
        update_post_meta($listing_id, '_rovlex_staff_html', $staff_html);
        update_post_meta($listing_id, '_rovlex_services_html', $services_html);
        update_post_meta($listing_id, '_rovlex_staff_count', count($staff));
        update_post_meta($listing_id, '_rovlex_services_count', count($services));
        update_post_meta($listing_id, '_rovlex_last_sync', current_time('mysql'));
    }

    /**
     * Format price
     */
    private function format_price($price, $currency) {
        $symbols = [
            'GBP' => '£', 'EUR' => '€', 'USD' => '$',
            'PLN' => 'zł', 'CZK' => 'Kč'
        ];
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format(floatval($price), 2);
    }

    /**
     * Format duration
     */
    private function format_duration($minutes) {
        $minutes = intval($minutes);
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
        }
        return "{$minutes}m";
    }

    /**
     * Get currency
     */
    private function get_currency() {
        $amelia_settings = get_option('amelia_settings');
        if ($amelia_settings) {
            $settings = json_decode($amelia_settings, true);
            if (!empty($settings['payments']['currency'])) {
                return $settings['payments']['currency'];
            }
        }
        return get_option('listeo_currency', 'GBP');
    }

    /**
     * AJAX handler for manual sync
     */
    public function ajax_force_sync() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Forbidden');
        }

        $this->run_sync();
        wp_send_json_success(['message' => 'Sync completed']);
    }
}

// Register custom cron interval
add_filter('cron_schedules', function($schedules) {
    $schedules['fifteen_minutes'] = [
        'interval' => 900,
        'display'  => 'Every 15 minutes'
    ];
    return $schedules;
});
