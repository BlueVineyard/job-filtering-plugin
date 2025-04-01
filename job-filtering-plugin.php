<?php

/**
 * Plugin Name: Job Filtering Plugin
 * Description: A plugin to filter job listings by various criteria.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('JFP_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once JFP_PLUGIN_DIR . 'includes/class-job-filtering-plugin.php';
require_once JFP_PLUGIN_DIR . 'includes/class-job-filtering-ajax.php';
require_once JFP_PLUGIN_DIR . 'includes/class-job-filtering-settings.php';


function jfp_enqueue_scripts()
{
    wp_enqueue_style('jfp-jQueryUI', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('jfp-job-filtering', plugin_dir_url(__FILE__) . 'assets/css/job-filtering.css');
    wp_enqueue_script('jquery-ui-slider');

    // Get API key from settings
    $api_key = get_option('jfp_google_maps_api_key', ''); // Get API key from settings

    // Check if another plugin has already enqueued Google Maps API
    global $wp_scripts;
    $maps_api_enqueued = false;

    if (isset($wp_scripts->registered)) {
        foreach ($wp_scripts->registered as $script) {
            // Check if any script URL contains maps.googleapis.com
            if (isset($script->src) && strpos($script->src, 'maps.googleapis.com') !== false) {
                $maps_api_enqueued = true;
                break;
            }
        }
    }

    wp_enqueue_script('jfp-job-filtering', plugin_dir_url(__FILE__) . 'assets/js/job-filtering.js', array('jquery'), null, true);
    // Only enqueue Google Maps API if it's not already enqueued
    if (!$maps_api_enqueued && !empty($api_key)) {
        wp_enqueue_script('jfp-google-places-api', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places', array(), null, true);
    }


    // Get country restrictions from settings
    $country_restrictions = get_option('jfp_country_restrictions', array('au')); // Default to Australia

    // Ensure it's an array
    if (!is_array($country_restrictions)) {
        $country_restrictions = array($country_restrictions);
    }

    // Pass data to JavaScript
    wp_localize_script('jfp-job-filtering', 'jfp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    // Pass API key and country restrictions to JavaScript
    wp_localize_script('jfp-job-filtering', 'jfp_settings', array(
        'api_key' => $api_key
    ));

    // Pass country restrictions to JavaScript
    wp_localize_script('jfp-job-filtering', 'jfp_country_restrictions', $country_restrictions);
}
add_action('wp_enqueue_scripts', 'jfp_enqueue_scripts');

add_action('wp_ajax_fetch_filtered_jobs', array('Job_Filtering_Ajax', 'fetch_filtered_jobs'));
add_action('wp_ajax_nopriv_fetch_filtered_jobs', array('Job_Filtering_Ajax', 'fetch_filtered_jobs'));

function override_bookmark_form_template($template, $template_name, $template_path)
{
    // Define the path to your custom template
    $plugin_template_path = plugin_dir_path(__FILE__) . 'templates/' . $template_name;

    // Check if the template exists in your plugin
    if (file_exists($plugin_template_path)) {
        return $plugin_template_path;
    }

    return $template;
}
add_filter('job_manager_locate_template', 'override_bookmark_form_template', 10, 3);