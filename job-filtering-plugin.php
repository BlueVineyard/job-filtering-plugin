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
    
    // Add Google Places API for autocomplete
    wp_enqueue_script('google-places-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBbymmPvtJkHoiX31edT8PeRV7yEDCzDG4&libraries=places', array(), null, true);

    wp_enqueue_script('jfp-job-filtering', plugin_dir_url(__FILE__) . 'assets/js/job-filtering.js', array('jquery', 'google-places-api'), null, true);
    
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