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
                    <div id="job-filter-form__left-header">
                        <div class="form-group">
                            <h6 class="noto-sans-h6">Filter</h6>
                            <button type="button" id="reset-filters">Clear all</button>
                        </div>
                    </div>
                    <div id="job-filter-form__left-body">
                        <!-- Date Post Filter -->
                        <div class="form-group">
                            <h5 class="noto-sans-filter_title">Date Listed</h5>

                            <div class="dropdown">
                                <label class="dropdown__options-filter">
                                    <ul class="dropdown__filter" role="listbox" tabindex="-1">
                                        <li class="dropdown__filter-selected" aria-selected="true">
                                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.98822 12.5C2.98822 8.72876 2.98822 6.84315 4.15979 5.67157C5.33137 4.5 7.21698 4.5 10.9882 4.5H14.9882C18.7595 4.5 20.6451 4.5 21.8166 5.67157C22.9882 6.84315 22.9882 8.72876 22.9882 12.5V14.5C22.9882 18.2712 22.9882 20.1569 21.8166 21.3284C20.6451 22.5 18.7595 22.5 14.9882 22.5H10.9882C7.21698 22.5 5.33137 22.5 4.15979 21.3284C2.98822 20.1569 2.98822 18.2712 2.98822 14.5V12.5Z" stroke="#636363" stroke-width="1.5" />
                                                <path d="M7.98822 4.5V3" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                                <path d="M17.9882 4.5V3" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                                <path d="M3.48822 9.5H22.4882" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
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

                        <hr>

                        <!-- Job Type Filter -->
                        <div class="form-group">
                            <h5 class="noto-sans-filter_title">Job Type</h5>
                            <?php
                            $job_types = get_terms(array(
                                'taxonomy' => 'job_listing_type',
                                'hide_empty' => false,
                            ));
                            if (!is_wp_error($job_types) && !empty($job_types)) {
                                foreach ($job_types as $job_type) {
                                    echo '<div class="form-control"><label class="custom_checkbox"><input type="checkbox" name="job_listing_type[]" value="' . esc_attr($job_type->slug) . '">' . esc_html($job_type->name) . '<span class="checkbox"></span></label></div>';
                                }
                            }
                            ?>
                        </div>

                        <hr>

                        <!-- Salary Range Filter -->
                        <div class="form-group" style="display:none;">
                            <h5 class="noto-sans-filter_title">Salary Range</h5>
                            <div class="form-control">
                                <input type="radio" name="salary_range" value="under_1000" id="under_1000">
                                <label for="under_1000">Under $1000</label>
                            </div>
                            <div class="form-control">
                                <input type="radio" name="salary_range" value="1000_2500" id="1000_2500">
                                <label for="1000_2500">$1000 to $2500</label>
                            </div>
                            <div class="form-control">
                                <input type="radio" name="salary_range" value="2500_5000" id="2500_5000">
                                <label for="2500_5000">$2500 to $5000</label>
                            </div>
                            <div class="form-control">
                                <input type="radio" name="salary_range" value="custom" id="custom">
                                <label for="custom">Custom</label>
                            </div>
                            <div id="custom_price_range" style="display: none;">
                                <div class="price-input">
                                    <div class="field">
                                        <span>Min</span>
                                        <input type="number" class="input-min" name="custom_salary_min" value="2500">
                                    </div>
                                    <div class="separator">-</div>
                                    <div class="field">
                                        <span>Max</span>
                                        <input type="number" class="input-max" name="custom_salary_max" value="7500">
                                    </div>
                                </div>
                                <div class="slider">
                                    <div class="progress"></div>
                                </div>
                                <div class="range-input">
                                    <input type="range" class="range-min" min="0" max="10000" value="2500" step="100">
                                    <input type="range" class="range-max" min="0" max="10000" value="7500" step="100">
                                </div>
                            </div>
                        </div>

                        <hr style="display:none;">



                        <!-- Company Name Filter -->
                        <div class="form-group">
                            <h5 class="noto-sans-filter_title">Organisation</h5>
                            <?php
                            $available_company_names = self::get_company_names();
                            if (!empty($available_company_names)) {
                                foreach ($available_company_names as $company_name) {
                            ?>
                                    <label class="custom_checkbox">
                                        <input type="checkbox" name="company_names[]" value="<?php echo esc_attr($company_name); ?>"
                                            <?php if (in_array($company_name, $company_names)) echo 'checked'; ?>> <!-- Ensure URL values reflect checked state -->
                                        <?php echo esc_html($company_name); ?>
                                        <span class="checkbox"></span>
                                    </label>
                            <?php
                                }
                            } else {
                                echo '<li>No companies found</li>';
                            }
                            ?>
                        </div>

                        <hr>

                        <!-- Job Category Filter -->
                        <div class="form-group">
                            <h5 class="noto-sans-filter_title">Job Category</h5>
                            <?php
                            $job_categories = get_terms(array(
                                'taxonomy' => 'job_listing_category',
                                'hide_empty' => false,
                            ));
                            if (!is_wp_error($job_categories) && !empty($job_categories)) {
                                foreach ($job_categories as $category) {
                                    // Check if the current category is selected
                                    $checked = in_array($category->slug, explode(',', $job_listing_category)) ? 'checked' : '';
                                    echo '<label class="custom_checkbox"><input type="checkbox" name="job_listing_category[]" value="' . esc_attr($category->slug) . '" ' . $checked . '>' . esc_html($category->name) . '<span class="checkbox"></span></label>';

                                    // echo '<label class="custom_checkbox"><input type="checkbox" name="job_listing_category[]" value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '<span class="checkbox"></span></label>';
                                }
                            }
                            ?>
                            <button type="button" id="toggle-more-categories">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.98822 1.5V16.5M16.4882 9H1.48822" stroke="#FF8200" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>More Categories</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="job-filter-form__right">
                    <div class="form-group">
                        <div id="search_filter">
                            <input type="text" name="search_query" placeholder="Type your search" value="<?php echo esc_attr($search_query); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="dropdown">
                            <label class="dropdown__options-filter">
                                <ul class="dropdown__filter" role="listbox" tabindex="-1">
                                    <li class="dropdown__filter-selected" aria-selected="true">
                                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z" stroke="#3D3935" stroke-width="1.5" />
                                            <ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" />
                                        </svg>
                                        <span><?php echo $job_location ? esc_html($job_location) : 'Any Location'; ?></span>
                                    </li>
                                    <li>
                                        <ul class="dropdown__select">
                                            <li class="dropdown__select-option" role="option" data-value="">
                                                Any Location
                                            </li>
                                            <?php foreach ($locations as $location) : ?>
                                                <li class="dropdown__select-option" role="option" data-value="<?php echo esc_attr($location->slug); ?>">
                                                    <?php echo esc_html($location->name); ?>
                                                </li>
                                            <?php endforeach; ?>

                                        </ul>
                                    </li>
                                </ul>
                            </label>
                            <input type="hidden" name="job_location" id="job_location" value="<?php echo esc_attr($job_location); ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" id="apply-filters">Search</button>
                    </div>
                </div>
            </form>

            <div id="job_results_wrapper">
                <div id="total-results"></div>
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
                <div class="home_dropdown">
                    <label class="home_dropdown__options-filter">
                        <ul class="home_dropdown__filter" role="listbox" tabindex="-1">
                            <li class="home_dropdown__filter-selected" aria-selected="true">
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z" stroke="#3D3935" stroke-width="1.5" />
                                    <ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" />
                                </svg>
                                <span>Any Location</span>
                            </li>
                            <li>
                                <ul class="home_dropdown__select">
                                    <li class="home_dropdown__select-option" role="option" data-value="">
                                        Any Location
                                    </li>
                                    <?php foreach ($locations as $location) : ?>
                                        <li class="home_dropdown__select-option" role="option" data-value="<?php echo esc_attr($location->slug); ?>">
                                            <?php echo esc_html($location->name); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        </ul>
                    </label>
                    <input type="hidden" name="job_location" id="job_location" value="" />
                </div>
            </div>

            <div class="form-group">
                <div class="cat_dropdown">
                    <label class="cat_dropdown__options-filter">
                        <ul class="cat_dropdown__filter" role="listbox" tabindex="-1">
                            <li class="cat_dropdown__filter-selected" aria-selected="true">
                                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 14.5C2 10.7288 2 8.84315 3.17157 7.67157C4.34315 6.5 6.22876 6.5 10 6.5H14C17.7712 6.5 19.6569 6.5 20.8284 7.67157C22 8.84315 22 10.7288 22 14.5C22 18.2712 22 20.1569 20.8284 21.3284C19.6569 22.5 17.7712 22.5 14 22.5H10C6.22876 22.5 4.34315 22.5 3.17157 21.3284C2 20.1569 2 18.2712 2 14.5Z" stroke="#636363" stroke-width="1.5" />
                                    <path d="M21.6618 9.21973C18.6519 11.1761 17.147 12.1543 15.5605 12.6472C13.2416 13.3677 10.7586 13.3677 8.43963 12.6472C6.85313 12.1543 5.34822 11.1761 2.33838 9.21973" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M8 11.5V13.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M16 11.5V13.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                    <path d="M9.1709 4.5C9.58273 3.33481 10.694 2.5 12.0002 2.5C13.3064 2.5 14.4177 3.33481 14.8295 4.5" stroke="#636363" stroke-width="1.5" stroke-linecap="round" />
                                </svg>

                                <span>Any Category</span>
                            </li>
                            <li>
                                <ul class="cat_dropdown__select">
                                    <li class="cat_dropdown__select-option" role="option" data-value="">
                                        Any Category
                                    </li>
                                    <?php foreach ($locations as $location) : ?>
                                        <li class="dropdown__select-option" role="option" data-value="<?php echo esc_attr($location->slug); ?>">
                                            <?php echo esc_html($location->name); ?>
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

    // private static function get_job_locations()
    // {
    //     global $wpdb;
    //     // Query to get distinct locations from published jobs only
    //     $locations = $wpdb->get_col("
    //     SELECT DISTINCT meta_value 
    //     FROM {$wpdb->prefix}postmeta 
    //     WHERE meta_key = '_job_location' 
    //     AND post_id IN (
    //         SELECT ID FROM {$wpdb->prefix}posts 
    //         WHERE post_status = 'publish' 
    //         AND post_type = 'job_listing'
    //     )
    // ");
    //     return $locations;
    // }

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
}
add_shortcode('job_filter_form', array('Job_Filtering_Plugin', 'job_filter_form'));
add_shortcode('job_filter_widget', array('Job_Filtering_Plugin', 'job_filter_widget'));
