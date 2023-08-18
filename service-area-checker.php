<?php
/**
 * Plugin Name: Service Area Checker
 * Description: Check if an address is inside the service area.
 * Version: 1.0.1
 * Author: Agus Andri Putra
 * Github : 
 */

// Load Leaflet and OpenStreetMap using Leaflet Maps Marker plugin
function load_leaflet_maps_marker() {
    wp_enqueue_script('leaflet-maps-marker', plugins_url('leaflet-maps-marker/leaflet-dist/leaflet.js'), array(), null, true);
}
add_action('wp_enqueue_scripts', 'load_leaflet_maps_marker');

// Shortcode for displaying the address input form
function service_area_checker_shortcode() {
    ob_start();
    ?>
    <form action="" method="post">
        <label for="address">Enter Address:</label>
        <input type="text" name="address" id="address" required>
        <button type="submit">Check</button>
        <div id="map" style="width: 100%; height: 300px;"></div>
        <script>
            var map = L.map('map').setView([0, 0], 2); // Inisialisasi peta Leaflet dengan koordinat awal dan zoom

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        </script>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('service_area_checker', 'service_area_checker_shortcode');

// Function to handle address checking and redirection
function handle_form_submission() {
    if (isset($_POST['address'])) {
        $address = sanitize_text_field($_POST['address']);
        $inside_action_url = get_option('service_area_checker_inside_action_url');
        $outside_action_url = get_option('service_area_checker_outside_action_url');

        if (is_address_inside_service_area($address)) {
            if (!empty($inside_action_url)) {
                wp_redirect($inside_action_url);
                exit;
            }
        } else {
            if (!empty($outside_action_url)) {
                wp_redirect($outside_action_url);
                exit;
            }
        }
    }
}
add_action('init', 'handle_form_submission');

function is_address_inside_service_area($address) {
    // Replace this with your actual logic to determine if the address is inside the service area.
    // You might use geocoding APIs to determine the address's coordinates and compare with your service area boundaries.
    // For demonstration purposes, this example assumes "New York" as the service area.
    return (strpos($address, 'New York') !== false);
}

// Function to display the result and perform actions
function display_result($address) {
    $inside_action_url = get_option('service_area_checker_inside_action_url');
    $outside_action_url = get_option('service_area_checker_outside_action_url');

    if (is_address_inside_service_area($address)) {
        echo "Address is inside the service area.";
        if (!empty($inside_action_url)) {
            echo "<script>window.location.href = '$inside_action_url';</script>";
        }
    } else {
        echo "Address is outside the service area.";
        if (!empty($outside_action_url)) {
            echo "<script>window.location.href = '$outside_action_url';</script>";
        }
    }
}

// Add settings fields in the admin panel
function service_area_checker_settings_init() {
    add_settings_section('service_area_checker_section', 'Service Area Checker Settings', '__return_false', 'service_area_checker');
    
    add_settings_field('service_area_checker_inside_action_url', 'Action URL if Inside Radius', 'render_inside_action_url_field', 'service_area_checker', 'service_area_checker_section');
    add_settings_field('service_area_checker_outside_action_url', 'Action URL if Outside Radius', 'render_outside_action_url_field', 'service_area_checker', 'service_area_checker_section');
    
    register_setting('service_area_checker', 'service_area_checker_inside_action_url');
    register_setting('service_area_checker', 'service_area_checker_outside_action_url');
}
add_action('admin_init', 'service_area_checker_settings_init');

// Render functions for settings fields
function render_inside_action_url_field() {
    $inside_action_url = get_option('service_area_checker_inside_action_url');
    echo "<input type='text' name='service_area_checker_inside_action_url' value='$inside_action_url' />";
}

function render_outside_action_url_field() {
    $outside_action_url = get_option('service_area_checker_outside_action_url');
    echo "<input type='text' name='service_area_checker_outside_action_url' value='$outside_action_url' />";
}

// Shortcode for settings page to display the plugin settings
function service_area_checker_settings_page() {
    ?>
    <div class="wrap">
        <h2>Service Area Checker Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('service_area_checker');
            do_settings_sections('service_area_checker');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
add_action('admin_menu', 'register_service_area_checker_settings_page');

// Register the plugin settings page
function register_service_area_checker_settings_page() {
    add_submenu_page('options-general.php', 'Service Area Checker Settings', 'Service Area Checker', 'manage_options', 'service_area_checker', 'service_area_checker_settings_page');
}
