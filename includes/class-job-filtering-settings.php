<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Job_Filtering_Settings
{
    /**
     * Initialize the settings page
     */
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    /**
     * Add settings page to admin menu
     */
    public static function add_settings_page()
    {
        add_submenu_page(
            'edit.php?post_type=job_listing',
            'Job Filtering Settings',
            'Job Filtering Settings',
            'manage_options',
            'job-filtering-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public static function register_settings()
    {
        register_setting('job_filtering_settings', 'jfp_google_maps_api_key');
        register_setting('job_filtering_settings', 'jfp_country_restrictions');

        add_settings_section(
            'jfp_api_settings',
            'API Settings',
            array(__CLASS__, 'api_settings_section_callback'),
            'job-filtering-settings'
        );

        add_settings_field(
            'jfp_google_maps_api_key',
            'Google Maps API Key',
            array(__CLASS__, 'google_maps_api_key_callback'),
            'job-filtering-settings',
            'jfp_api_settings'
        );
        
        add_settings_section(
            'jfp_location_settings',
            'Location Settings',
            array(__CLASS__, 'location_settings_section_callback'),
            'job-filtering-settings'
        );
        
        add_settings_field(
            'jfp_country_restrictions',
            'Country Restrictions',
            array(__CLASS__, 'country_restrictions_callback'),
            'job-filtering-settings',
            'jfp_location_settings'
        );
    }

    /**
     * API Settings section callback
     */
    public static function api_settings_section_callback()
    {
        echo '<p>Configure API keys for job filtering functionality.</p>';
    }

    /**
     * Google Maps API Key field callback
     */
    public static function google_maps_api_key_callback()
    {
        $api_key = get_option('jfp_google_maps_api_key');
        ?>
<input type="text" name="jfp_google_maps_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
<p class="description">Enter your Google Maps API key. This is required for geocoding job locations.</p>
<p class="description">You can get a key from the <a
        href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Google Maps
        Platform</a>.</p>
<?php
    }

    /**
     * Location Settings section callback
     */
    public static function location_settings_section_callback()
    {
        echo '<p>Configure location settings for job filtering functionality.</p>';
    }
    
    /**
     * Country Restrictions field callback
     */
    public static function country_restrictions_callback()
    {
        $countries = array(
            'au' => 'Australia',
            'nz' => 'New Zealand',
            'us' => 'United States',
            'ca' => 'Canada',
            'gb' => 'United Kingdom',
            'sg' => 'Singapore',
            'jp' => 'Japan',
            'in' => 'India',
            'de' => 'Germany',
            'fr' => 'France',
        );
        
        $selected_countries = get_option('jfp_country_restrictions', array('au')); // Default to Australia
        
        if (!is_array($selected_countries)) {
            $selected_countries = array($selected_countries);
        }
        
        echo '<select name="jfp_country_restrictions[]" multiple="multiple" style="min-width: 300px; height: 150px;">';
        
        foreach ($countries as $code => $name) {
            $selected = in_array($code, $selected_countries) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }
        
        echo '</select>';
        echo '<p class="description">Select countries to restrict location search to. Hold Ctrl/Cmd to select multiple countries.</p>';
        echo '<p class="description">These restrictions will apply to both the location input autocomplete and the "Use My Location" feature.</p>';
    }

    /**
     * Render settings page
     */
    public static function render_settings_page()
    {
        ?>
<div class="wrap">
    <h1>Job Filtering Settings</h1>
    <form method="post" action="options.php">
        <?php
                settings_fields('job_filtering_settings');
                do_settings_sections('job-filtering-settings');
                submit_button();
                ?>
    </form>
</div>
<?php
    }
}

// Initialize settings
Job_Filtering_Settings::init();