<?php
/**
 * Phase 2: Redirect owner to Amelia after listing creation
 */
class Rovlex_Admin_Redirect {

    public function __construct() {
        // Intercept submit redirect
        add_filter('listeo_submit_redirect', [$this, 'redirect_to_amelia'], 10, 2);

        // Show notice in admin
        add_action('admin_notices', [$this, 'show_amelia_notice']);

        // Add manage button on listing dashboard - DISABLED
        // add_action('listeo_dashboard_listing_actions', [$this, 'add_manage_button'], 10, 1);
    }

    /**
     * Redirect to Amelia after listing creation
     */
    public function redirect_to_amelia($redirect_url, $listing_id) {
        $amelia_location_id = Rovlex_Location_Sync::get_amelia_location_id($listing_id);

        if ($amelia_location_id) {
            return admin_url(
                'admin.php?page=wpamelia-employees&location=' . $amelia_location_id
                . '&rovlex_listing=' . $listing_id
            );
        }

        return $redirect_url;
    }

    /**
     * Show notice in admin
     */
    public function show_amelia_notice() {
        if (!isset($_GET['rovlex_listing'])) return;

        $listing_id = intval($_GET['rovlex_listing']);
        $listing_title = get_the_title($listing_id);

        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>🎉 ' . sprintf(
            esc_html__('Listing "%s" created successfully!', 'rovlex-amelia-bridge'),
            esc_html($listing_title)
        ) . '</strong></p>';
        echo '<p>' . esc_html__('Now add staff members and services for this salon.', 'rovlex-amelia-bridge') . '</p>';
        echo '<p>';
        echo '<a href="' . admin_url('admin.php?page=wpamelia-employees') . '" class="button button-primary">';
        echo '→ ' . esc_html__('Add Staff', 'rovlex-amelia-bridge') . '</a> ';
        echo '<a href="' . admin_url('admin.php?page=wpamelia-services') . '" class="button">';
        echo '→ ' . esc_html__('Add Services', 'rovlex-amelia-bridge') . '</a>';
        echo '</p>';
        echo '</div>';
    }

    /**
     * Add manage button on listing dashboard
     */
    public function add_manage_button($listing_id) {
        $amelia_location_id = Rovlex_Location_Sync::get_amelia_location_id($listing_id);

        if ($amelia_location_id) {
            echo '<a href="' . admin_url('admin.php?page=wpamelia-employees') . '"';
            echo ' class="button" target="_blank" title="' . esc_attr__('Manage Staff & Services in Amelia', 'rovlex-amelia-bridge') . '">';
            echo '👥 ' . esc_html__('Staff & Services', 'rovlex-amelia-bridge') . '</a> ';
        }
    }
}
