<?php
/**
 * Phase 5: Display staff, services, and booking button on listing pages
 */
class Rovlex_Listing_Display {

    public function __construct() {
        // Add booking button to listing page
        add_action('listeo_single_listing_after_content', [$this, 'render_book_button'], 20);
        add_action('listeo_sidebar_listing_actions', [$this, 'render_sidebar_book_button'], 10);

        // Register booking page
        add_action('init', [$this, 'register_booking_page']);

        // Booking page shortcode
        add_shortcode('rovlex_amelia_booking', [$this, 'booking_page_shortcode']);

        // Enqueue CSS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    /**
     * Render "Book Now" button on listing
     */
    public function render_book_button() {
        global $post;

        $amelia_location_id = Rovlex_Location_Sync::get_amelia_location_id($post->ID);
        if (!$amelia_location_id) return;

        $services_count = get_post_meta($post->ID, '_rovlex_services_count', true);
        if (!$services_count) return;

        $booking_url = $this->get_booking_url($amelia_location_id);

        echo '<div class="rovlex-booking-cta" style="text-align:center; padding: 20px 0;">';
        echo '<a href="' . esc_url($booking_url) . '" class="rovlex-book-btn">';
        echo '📅 Book an Appointment</a>';
        echo '</div>';
    }

    /**
     * Render "Book Now" button in sidebar
     */
    public function render_sidebar_book_button($listing_id = null) {
        if (!$listing_id) {
            global $post;
            $listing_id = $post->ID;
        }

        $amelia_location_id = Rovlex_Location_Sync::get_amelia_location_id($listing_id);
        if (!$amelia_location_id) return;

        $booking_url = $this->get_booking_url($amelia_location_id);

        echo '<a href="' . esc_url($booking_url) . '" class="rovlex-book-btn"';
        echo ' style="display:block; text-align:center; margin: 10px 0;">';
        echo '📅 Book Now</a>';
    }

    /**
     * Get booking URL
     */
    private function get_booking_url($amelia_location_id) {
        $booking_page = get_page_by_path('book');
        if ($booking_page) {
            return add_query_arg('location', $amelia_location_id,
                get_permalink($booking_page->ID));
        }
        return home_url('/book/?location=' . $amelia_location_id);
    }

    /**
     * Booking page shortcode
     */
    public function booking_page_shortcode($atts) {
        $location_id = isset($_GET['location']) ? intval($_GET['location']) : 0;

        if (!$location_id) {
            return '<p>' . esc_html__('Please select a salon first.', 'rovlex-amelia-bridge') . '</p>';
        }

        $output = '<div class="rovlex-booking-page">';
        $output .= do_shortcode('[ameliabooking location="' . $location_id . '"]');
        $output .= '</div>';

        return $output;
    }

    /**
     * Register booking page
     */
    public function register_booking_page() {
        $page = get_page_by_path('book');
        if (!$page && !get_option('rovlex_booking_page_created')) {
            wp_insert_post([
                'post_title'   => 'Book an Appointment',
                'post_name'    => 'book',
                'post_content' => '[rovlex_amelia_booking]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);

            update_option('rovlex_booking_page_created', true);
        }
    }

    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'rovlex-amelia-bridge',
            ROVLEX_AB_URL . 'assets/styles.css',
            [],
            ROVLEX_AB_VERSION
        );
    }
}
