<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Job_Filtering_Plugin
{

    /**
     * Job Filter Form
     */

    public static function job_filter_form()
    {
        $locations = self::get_job_locations();
        $jobTypes = self::get_job_listing_types();
        $jobCats = self::get_job_listing_categories();
        $available_company_names = self::get_company_names();

        // Get query parameters if they exist
        $search_query = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
        $job_location = isset($_GET['job_location']) ? sanitize_text_field($_GET['job_location']) : '';
        $job_listing_category = isset($_GET['job_listing_category']) ? sanitize_text_field($_GET['job_listing_category']) : '';
        $company_names = isset($_GET['company_names']) ? array_map('sanitize_text_field', (array)$_GET['company_names']) : [];

        ob_start();
?>

<div id="ae_job_filter_wrapper">
    <form id="job-filter-form" action="<?php echo esc_url(home_url('/job-filter')); ?>" method="GET">
        <div id="job-filter-form__left">
            <div id="job-filter-form__left-body">
                <!-- Date Post Filter -->
                <div class="form-group">
                    <div class="dropdown">
                        <label class="dropdown__options-filter">
                            <ul class="dropdown__filter" role="listbox" tabindex="-1">
                                <li class="dropdown__filter-selected" aria-selected="true">
                                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M2.98822 12.5C2.98822 8.72876 2.98822 6.84315 4.15979 5.67157C5.33137 4.5 7.21698 4.5 10.9882 4.5H14.9882C18.7595 4.5 20.6451 4.5 21.8166 5.67157C22.9882 6.84315 22.9882 8.72876 22.9882 12.5V14.5C22.9882 18.2712 22.9882 20.1569 21.8166 21.3284C20.6451 22.5 18.7595 22.5 14.9882 22.5H10.9882C7.21698 22.5 5.33137 22.5 4.15979 21.3284C2.98822 20.1569 2.98822 18.2712 2.98822 14.5V12.5Z"
                                            stroke="#636363" stroke-width="1.5" />
                                        <path d="M7.98822 4.5V3" stroke="#636363" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <path d="M17.9882 4.5V3" stroke="#636363" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <path d="M3.48822 9.5H22.4882" stroke="#636363" stroke-width="1.5"
                                            stroke-linecap="round" />
                                    </svg>
                                    <span>Anytime</span>
                                </li>
                                <li>
                                    <ul class="dropdown__select">
                                        <li class="dropdown__select-option" role="option" data-value="anytime">
                                            Anytime
                                        </li>
                                        <li class="dropdown__select-option" role="option" data-value="24_hours">
                                            24 hours ago
                                        </li>
                                        <li class="dropdown__select-option" role="option" data-value="1_week">
                                            1 week ago
                                        </li>
                                        <li class="dropdown__select-option" role="option" data-value="1_month">
                                            1 month ago
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </label>
                        <input type="hidden" name="date_post" id="date_post" value="anytime" />
                    </div>
                </div>

                <!-- Organisation Dropdown Filter -->
                <div class="form-group">
                    <div class="dropdown organisationDropdown">
                        <label class="dropdown__options-filter organisationDropdown__options-filter">
                            <ul class="dropdown__filter organisationDropdown__filter" role="listbox" tabindex="-1">
                                <li class="dropdown__filter-selected organisationDropdown__filter-selected"
                                    aria-selected="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960"
                                        width="25px" fill="#000">
                                        <path
                                            d="M89.33-101.85v-601.38h183.13v-182.62h416.92v367.08H872v416.92H552.05v-183.12h-144.1v183.12H89.33Zm51.18-51.18h131.95v-131.94H140.51v131.94Zm0-183.12h131.95v-131.44H140.51v131.44Zm0-183.95h131.95v-131.95H140.51v131.95Zm183.13 183.95h131.44v-131.44H323.64v131.44Zm0-183.95h131.44v-131.95H323.64v131.95Zm0-183.13h131.44v-131.44H323.64v131.44Zm182.62 367.08h131.95v-131.44H506.26v131.44Zm0-183.95h131.95v-131.95H506.26v131.95Zm0-183.13h131.95v-131.44H506.26v131.44Zm183.12 550.2h131.44v-131.94H689.38v131.94Zm0-183.12h131.44v-131.44H689.38v131.44Z" />
                                    </svg>

                                    <span>Any Organistaion</span>
                                </li>
                                <li>
                                    <ul class="dropdown__select organisationDropdown__select">
                                        <li class="dropdown__select-option organisationDropdown__select-option"
                                            role="option" data-value="">
                                            Any Organistaion
                                        </li>
                                        <?php foreach ($available_company_names as $organisation) : ?>
                                        <li class="dropdown__select-option organisationDropdown__select-option"
                                            role="option" data-value="<?php echo esc_attr($organisation); ?>">
                                            <?php echo esc_html($organisation); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            </ul>
                        </label>
                        <input type="hidden" name="company_names" id="company_names" value="" />
                    </div>
                </div>

                <!-- Job Categories Dropdown Filter -->
                <div class="form-group">
                    <div class="dropdown jobCatsDropdown">
                        <label class="dropdown__options-filter jobCatsDropdown__options-filter">
                            <ul class="dropdown__filter jobCatsDropdown__filter" role="listbox" tabindex="-1">
                                <li class="dropdown__filter-selected jobCatsDropdown__filter-selected"
                                    aria-selected="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960"
                                        width="24px" fill="#000">
                                        <path
                                            d="M88-152v-302.46h302.46V-152H88Zm66-66h170.46v-170.46H154V-218Zm86.61-362 202.62-332.77L645.85-580H240.61Zm117.7-66h171.84l-86.92-138.15L358.31-646ZM870.54-55.62 766.08-160.08q-20.7 14.77-45.59 23.43-24.9 8.65-53.26 8.65-72.69 0-121.96-50.85Q496-229.71 496-303.23q0-72.69 49.27-121.96 49.27-49.27 121.96-49.27 73.53 0 124.38 49.27 50.85 49.27 50.85 121.96 0 28-8.15 51.08-8.16 23.07-21.93 43.77l105.47 105.46-47.31 47.3ZM667.21-194q44.87 0 77.06-31.06 32.19-31.06 32.19-76.04 0-44.98-32.17-76.17-32.17-31.19-77.04-31.19-44.87 0-75.06 31.06Q562-346.35 562-301.37q0 44.99 30.17 76.18Q622.34-194 667.21-194ZM324.46-388.46ZM444.23-646Z" />
                                    </svg>

                                    <span>Any Category</span>
                                </li>
                                <li>
                                    <ul class="dropdown__select jobCatsDropdown__select">
                                        <li class="dropdown__select-option jobCatsDropdown__select-option" role="option"
                                            data-value="">
                                            Any Category
                                        </li>
                                        <?php foreach ($jobCats as $jobCat) : ?>
                                        <li class="dropdown__select-option jobCatsDropdown__select-option" role="option"
                                            data-value="<?php echo esc_attr($jobCat->slug); ?>">
                                            <?php echo esc_html($jobCat->name); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            </ul>
                        </label>
                        <input type="hidden" name="job_listing_category" id="job_listing_category" value="" />
                    </div>
                </div>
            </div>
        </div>
        <div id="job-filter-form__right">
            <div class="form-group">
                <div id="search_filter">
                    <input type="text" name="search_query" placeholder="Type your search"
                        value="<?php echo esc_attr($search_query); ?>">
                </div>
            </div>

            <!-- Location Input Filter -->
            <div class="form-group">
                <div id="location_filter">
                    <div class="location-input-wrapper">
                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="location-icon">
                            <path
                                d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z"
                                stroke="#3D3935" stroke-width="1.5" />
                            <ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" />
                        </svg>
                        <input type="text" name="job_location" id="job_location" placeholder="Enter location"
                            value="<?php echo esc_attr($job_location); ?>" class="location-input">
                    </div>
                </div>
            </div>

            <!-- Job Type Dropdown Filter -->
            <div class="form-group">
                <div class="dropdown jobTypeDropdown">
                    <label class="dropdown__options-filter jobTypeDropdown__options-filter">
                        <ul class="dropdown__filter jobTypeDropdown__filter" role="listbox" tabindex="-1">
                            <li class="dropdown__filter-selected jobTypeDropdown__filter-selected" aria-selected="true">
                                <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2 14.5C2 10.7288 2 8.84315 3.17157 7.67157C4.34315 6.5 6.22876 6.5 10 6.5H14C17.7712 6.5 19.6569 6.5 20.8284 7.67157C22 8.84315 22 10.7288 22 14.5C22 18.2712 22 20.1569 20.8284 21.3284C19.6569 22.5 17.7712 22.5 14 22.5H10C6.22876 22.5 4.34315 22.5 3.17157 21.3284C2 20.1569 2 18.2712 2 14.5Z"
                                        stroke="#636363" stroke-width="1.5" />
                                    <path
                                        d="M21.6618 9.21973C18.6519 11.1761 17.147 12.1543 15.5605 12.6472C13.2416 13.3677 10.7586 13.3677 8.43963 12.6472C6.85313 12.1543 5.34822 11.1761 2.33838 9.21973"
                                        stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M8 11.5V13.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M16 11.5V13.5" stroke="#636363" stroke-width="1.5"
                                        stroke-linecap="round" />
                                    <path
                                        d="M9.1709 4.5C9.58273 3.33481 10.694 2.5 12.0002 2.5C13.3064 2.5 14.4177 3.33481 14.8295 4.5"
                                        stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                </svg>

                                <span>Any Job Type</span>
                            </li>
                            <li>
                                <ul class="dropdown__select jobTypeDropdown__select">
                                    <li class="dropdown__select-option jobTypeDropdown__select-option" role="option"
                                        data-value="">
                                        Any Job Type
                                    </li>
                                    <?php foreach ($jobTypes as $jobType) : ?>
                                    <li class="dropdown__select-option jobTypeDropdown__select-option" role="option"
                                        data-value="<?php echo esc_attr($jobType->slug); ?>">
                                        <?php echo esc_html($jobType->name); ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </label>
                    <input type="hidden" name="job_listing_type" id="job_listing_type" value="" />
                </div>
            </div>


            <div class="form-group" style="display: flex; column-gap: 20px;">
                <div class="advanced_search">
                    <svg xmlns="http://www.w3.org/2000/svg" height="25px" viewBox="0 -960 960 960" width="25px"
                        fill="#000">
                        <path
                            d="M687.64-171.95q-52.93 0-89.48-36.67-36.54-36.68-36.54-89.8 0-53.11 36.53-89.68 36.54-36.57 89.46-36.57 52.92 0 89.82 36.57 36.9 36.57 36.9 89.68 0 53.12-36.74 89.8-36.74 36.67-89.95 36.67Zm-.2-51.18q31.11 0 53.41-22.14 22.3-22.14 22.3-53.22 0-30.81-22.15-52.9-22.15-22.1-53.26-22.1-31.1 0-53.02 22.02-21.93 22.01-21.93 52.92t21.78 53.17q21.77 22.25 52.87 22.25Zm-519.75-49.59v-51.18h298.87v51.18H167.69Zm105.44-262.61q-53.11 0-89.79-36.68-36.67-36.67-36.67-89.79 0-53.12 36.67-89.68 36.68-36.57 89.79-36.57 53.12 0 89.69 36.57 36.56 36.56 36.56 89.68 0 53.12-36.56 89.79-36.57 36.68-89.69 36.68Zm.08-51.18q30.81 0 52.9-22.14 22.1-22.14 22.1-53.23 0-30.8-22.02-52.9-22.02-22.09-52.92-22.09-30.91 0-53.17 22.01-22.25 22.02-22.25 52.93 0 30.9 22.14 53.16 22.13 22.26 53.22 22.26Zm221.56-49.59v-51.18h298.54v51.18H494.77Zm192.87 337.46ZM273.36-662.03Z" />
                    </svg>
                </div>

                <button type="button" id="apply-filters">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                        fill="#fff">
                        <path
                            d="M788.38-127.85 535.92-380.31q-30 24.54-73.5 38.04t-83.88 13.5q-106.1 0-179.67-73.53-73.56-73.53-73.56-179.57 0-106.05 73.53-179.71 73.53-73.65 179.57-73.65 106.05 0 179.71 73.56Q631.77-688.1 631.77-582q0 42.69-13.27 83.69t-37.27 70.69l253.46 253.47-46.31 46.3ZM378.54-394.77q79.61 0 133.42-53.81 53.81-53.8 53.81-133.42 0-79.62-53.81-133.42-53.81-53.81-133.42-53.81-79.62 0-133.42 53.81-53.81 53.8-53.81 133.42 0 79.62 53.81 133.42 53.8 53.81 133.42 53.81Z" />
                    </svg>
                </button>
            </div>
        </div>
    </form>

    <div id="job_results_wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div id="total-results"></div>
            <button type="button" id="reset-filters">Clear all</button>
        </div>
        <div id="job-results"></div>
    </div>
</div>
<?php
        return ob_get_clean();
    }



    /**
     * Job Filter Widget
     */
    public static function job_filter_widget()
    {
        $locations = self::get_job_locations();
        // Get job categories
        $categories = self::get_job_listing_categories();


        ob_start();
    ?>
<form id="job-filter-widget-form" action="<?php echo esc_url(home_url('/job-filter')); ?>" method="GET">
    <div class="form-group">
        <div id="search_filter">
            <input type="text" name="search_query" placeholder="Search jobs">
        </div>
    </div>

    <div class="form-group">
        <div id="widget_location_filter">
            <div class="location-input-wrapper">
                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="location-icon">
                    <path
                        d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z"
                        stroke="#3D3935" stroke-width="1.5" />
                    <ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" />
                </svg>
                <input type="text" name="job_location" id="job_location" placeholder="Enter location"
                    class="location-input">
            </div>
            <div class="location-slider-container">
                <label for="widget-radius-slider">Distance: <span id="widget-radius-value">50</span> km</label>
                <input type="range" id="widget-radius-slider" min="5" max="200" value="50"
                    class="location-radius-slider">
                <input type="hidden" name="radius" id="widget_location_radius" value="50">
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="cat_dropdown">
            <label class="cat_dropdown__options-filter">
                <ul class="cat_dropdown__filter" role="listbox" tabindex="-1">
                    <li class="cat_dropdown__filter-selected" aria-selected="true">
                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M2 14.5C2 10.7288 2 8.84315 3.17157 7.67157C4.34315 6.5 6.22876 6.5 10 6.5H14C17.7712 6.5 19.6569 6.5 20.8284 7.67157C22 8.84315 22 10.7288 22 14.5C22 18.2712 22 20.1569 20.8284 21.3284C19.6569 22.5 17.7712 22.5 14 22.5H10C6.22876 22.5 4.34315 22.5 3.17157 21.3284C2 20.1569 2 18.2712 2 14.5Z"
                                stroke="#636363" stroke-width="1.5" />
                            <path
                                d="M21.6618 9.21973C18.6519 11.1761 17.147 12.1543 15.5605 12.6472C13.2416 13.3677 10.7586 13.3677 8.43963 12.6472C6.85313 12.1543 5.34822 11.1761 2.33838 9.21973"
                                stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M8 11.5V13.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M16 11.5V13.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                            <path
                                d="M9.1709 4.5C9.58273 3.33481 10.694 2.5 12.0002 2.5C13.3064 2.5 14.4177 3.33481 14.8295 4.5"
                                stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                        </svg>

                        <span>Any Category</span>
                    </li>
                    <li>
                        <ul class="cat_dropdown__select">
                            <li class="cat_dropdown__select-option" role="option" data-value="">
                                Any Category
                            </li>
                            <?php foreach ($categories as $category) : ?>
                            <li class="cat_dropdown__select-option" role="option"
                                data-value="<?php echo esc_attr($category->slug); ?>">
                                <?php echo esc_html($category->name); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
            </label>
            <input type="hidden" name="job_listing_category" id="job_listing_category" value="" />
        </div>
    </div>

    <div class="form-group">
        <button type="submit" id="apply-filters">Search</button>
    </div>
</form>
<?php
        return ob_get_clean();
    }

    private static function get_job_locations()
    {
        $terms = get_terms(array(
            'taxonomy' => 'location',
            'hide_empty' => false,
        ));
        return $terms;
    }


    private static function get_company_names()
    {
        global $wpdb;
        // Query to get distinct company names from published jobs only
        $company_names = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->prefix}postmeta 
            WHERE meta_key = '_company_name'
            AND post_id IN (
                SELECT ID FROM {$wpdb->prefix}posts 
                WHERE post_status = 'publish'
                AND post_type = 'job_listing'
            )
        ");
        return $company_names;
    }

    private static function get_job_listing_categories()
    {
        $terms = get_terms(array(
            'taxonomy' => 'job_listing_category',
            'hide_empty' => false,
        ));
        return $terms;
    }

    private static function get_job_listing_types()
    {
        $terms = get_terms(array(
            'taxonomy' => 'job_listing_type',
            'hide_empty' => false,
        ));
        return $terms;
    }
}
add_shortcode('job_filter_form', array('Job_Filtering_Plugin', 'job_filter_form'));
add_shortcode('job_filter_widget', array('Job_Filtering_Plugin', 'job_filter_widget'));