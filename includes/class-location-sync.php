<?php
/**
 * Phase 1: Auto-create Amelia Location when listing is created/updated
 */
class Rovlex_Location_Sync {

    public function __construct() {
        // Hook after listing is published/updated
        add_action('save_post_listing', [$this, 'on_listing_saved'], 20, 3);

        // Hook for frontend submission (Listeo)
        add_action('listeo_after_submit_listing', [$this, 'on_listing_created'], 10, 1);
    }

    /**
     * Handle listing saved event
     */
    public function on_listing_saved($post_id, $post, $update) {
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Skip revisions
        if (wp_is_post_revision($post_id)) return;

        // Only for published or pending listings
        if (!in_array($post->post_status, ['publish', 'pending'])) return;

        $this->sync_location($post_id);
    }

    /**
     * Handle Listeo frontend submission
     */
    public function on_listing_created($listing_id) {
        $this->sync_location($listing_id);
    }

    /**
     * Create or update Amelia Location from listing
     */
    private function sync_location($listing_id) {
        global $wpdb;

        $post = get_post($listing_id);
        if (!$post) return;

        // Get listing data - try multiple possible meta keys
        $name = $post->post_title;
        $description = wp_trim_words($post->post_content, 50);

        // Try to get coordinates and address from various meta keys
        $address = $this->get_meta_value($listing_id, ['_address', 'location_address', 'address']);
        $phone = $this->get_meta_value($listing_id, ['_phone', 'phone_number', 'contact_phone']);
        $email = $this->get_meta_value($listing_id, ['_email', 'contact_email', 'email']);
        $lat = $this->get_meta_value($listing_id, ['_geolocation_lat', 'latitude', 'lat']);
        $lng = $this->get_meta_value($listing_id, ['_geolocation_long', 'longitude', 'lng']);

        // Check if location already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT amelia_location_id FROM {$wpdb->prefix}rovlex_amelia_map
             WHERE listing_id = %d",
            $listing_id
        ));

        // Prepare location data
        $location_data = [
            'name'        => $name,
            'address'     => $address ?: '',
            'phone'       => $phone ?: '',
            'latitude'    => $lat ? floatval($lat) : 0,
            'longitude'   => $lng ? floatval($lng) : 0,
            'description' => $description ?: '',
            'status'      => 'visible'
        ];

        if ($existing) {
            // Update existing location
            $this->update_amelia_location($existing, $location_data);
            error_log("ROVLEX: Updated Amelia Location ID={$existing} for listing {$listing_id}");
        } else {
            // Create new location
            $amelia_id = $this->create_amelia_location($location_data);

            if ($amelia_id) {
                // Save mapping
                $wpdb->insert(
                    $wpdb->prefix . 'rovlex_amelia_map',
                    [
                        'listing_id'        => $listing_id,
                        'amelia_location_id' => $amelia_id
                    ],
                    ['%d', '%d']
                );

                // Also save in post meta for quick access
                update_post_meta($listing_id, '_amelia_location_id', $amelia_id);

                error_log("ROVLEX: Created Amelia Location ID={$amelia_id} for listing {$listing_id}");
            }
        }
    }

    /**
     * Try multiple meta keys to get value
     */
    private function get_meta_value($post_id, $keys) {
        foreach ($keys as $key) {
            $value = get_post_meta($post_id, $key, true);
            if ($value) return $value;
        }
        return null;
    }

    /**
     * Create Location in Amelia via direct DB insert
     */
    private function create_amelia_location($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'amelia_locations',
            [
                'status'      => $data['status'],
                'name'        => $data['name'],
                'description' => $data['description'],
                'address'     => $data['address'],
                'phone'       => $data['phone'],
                'latitude'    => $data['latitude'],
                'longitude'   => $data['longitude'],
            ],
            ['%s', '%s', '%s', '%s', '%s', '%f', '%f']
        );

        if ($result === false) {
            error_log('ROVLEX: Failed to create Amelia Location. Error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update existing Amelia Location
     */
    private function update_amelia_location($amelia_id, $data) {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'amelia_locations',
            [
                'name'        => $data['name'],
                'address'     => $data['address'],
                'phone'       => $data['phone'],
                'latitude'    => $data['latitude'],
                'longitude'   => $data['longitude'],
                'description' => $data['description'],
            ],
            ['id' => $amelia_id],
            ['%s', '%s', '%s', '%f', '%f', '%s'],
            ['%d']
        );
    }

    /**
     * Public method to get Amelia Location ID for a listing
     */
    public static function get_amelia_location_id($listing_id) {
        return get_post_meta($listing_id, '_amelia_location_id', true);
    }
}
