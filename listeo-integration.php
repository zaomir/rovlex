<?php
/**
 * ROVLEX Amelia Bridge - Listeo Integration
 *
 * Add this code to wp-content/themes/listeo-child/functions.php
 */

// ===== Phase 3: Display Staff & Services on Listing Pages =====

/**
 * Add "Staff & Services" tab to listing page
 */
add_filter('listeo_listing_data_tabs', function($tabs) {
    $tabs['staff-services'] = [
        'label'    => __('Staff & Services', 'rovlex-amelia-bridge'),
        'target'   => 'staff-services-tab',
        'priority' => 25,
        'icon'     => 'sl sl-icon-people',
    ];
    return $tabs;
});

/**
 * Display Staff & Services content
 */
add_action('listeo_listing_data_panels', function() {
    global $post;

    if (!$post || get_post_type($post->ID) !== 'listing') return;

    $staff_html = get_post_meta($post->ID, '_rovlex_staff_html', true);
    $services_html = get_post_meta($post->ID, '_rovlex_services_html', true);
    $last_sync = get_post_meta($post->ID, '_rovlex_last_sync', true);

    echo '<div id="staff-services-tab" class="listing-section">';

    if ($staff_html || $services_html) {
        if ($staff_html) {
            echo '<div class="staff-section">';
            echo '<h3 class="listing-desc-headline">' . esc_html__('Our Team', 'rovlex-amelia-bridge') . '</h3>';
            echo '<div class="rovlex-staff-grid">' . wp_kses_post($staff_html) . '</div>';
            echo '</div>';
        }

        if ($services_html) {
            echo '<div class="services-section">';
            echo '<h3 class="listing-desc-headline">' . esc_html__('Services & Prices', 'rovlex-amelia-bridge') . '</h3>';
            echo '<div class="rovlex-services-list">' . wp_kses_post($services_html) . '</div>';
            echo '</div>';
        }

        if ($last_sync) {
            echo '<p class="rovlex-sync-info" style="font-size:12px; color:#999; margin-top:20px;">';
            echo sprintf(
                esc_html__('Last updated: %s', 'rovlex-amelia-bridge'),
                esc_html($last_sync)
            );
            echo '</p>';
        }
    } else {
        echo '<p>' . esc_html__('Information about staff and services will be available soon.', 'rovlex-amelia-bridge') . '</p>';
    }

    echo '</div>';
});

/**
 * Alternative: Display staff & services in single listing template
 * If the above hooks don't work, add this directly to single-listing.php template
 */
function rovlex_display_staff_services() {
    global $post;

    if (!$post || get_post_type($post->ID) !== 'listing') return;

    $staff_html = get_post_meta($post->ID, '_rovlex_staff_html', true);
    $services_html = get_post_meta($post->ID, '_rovlex_services_html', true);

    if (!$staff_html && !$services_html) return;

    echo '<section class="rovlex-staff-services-section">';

    if ($staff_html) {
        echo '<div class="staff-section">';
        echo '<h2>' . esc_html__('Our Team', 'rovlex-amelia-bridge') . '</h2>';
        echo '<div class="rovlex-staff-grid">' . wp_kses_post($staff_html) . '</div>';
        echo '</div>';
    }

    if ($services_html) {
        echo '<div class="services-section">';
        echo '<h2>' . esc_html__('Services & Prices', 'rovlex-amelia-bridge') . '</h2>';
        echo '<div class="rovlex-services-list">' . wp_kses_post($services_html) . '</div>';
        echo '</div>';
    }

    echo '</section>';
}

// Load text domain for translations
add_action('plugins_loaded', function() {
    load_plugin_textdomain('rovlex-amelia-bridge', false,
        dirname(plugin_basename(__FILE__)) . '/languages/');
});
