<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Job_Filtering_Ajax
{

    public static function fetch_filtered_jobs()
    {
        try {
            $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

            $args = array(
                'post_type' => 'job_listing',
                'posts_per_page' => 30,
                'paged' => $paged,
                'orderby' => 'modified', // Order by modified date
                'order' => 'DESC', // Latest modified first
                'post_status' => 'publish', // Only show published jobs
                'meta_query' => array(
                    'relation' => 'AND'
                ),
                'tax_query' => array(
                    'relation' => 'AND'
                )
            );

            // Search query filter
            if (!empty($_POST['search_query'])) {
                $args['s'] = sanitize_text_field($_POST['search_query']);
            }

            // Company Name filter
            if (!empty($_POST['company_names'])) {
                $company_names = array_map('sanitize_text_field', (array)$_POST['company_names']);
                $args['meta_query'][] = array(
                    'key' => '_company_name',
                    'value' => $company_names,
                    'compare' => 'IN',
                );
            }

            // Date Post filter
            if (!empty($_POST['date_post']) && $_POST['date_post'] !== 'anytime') {
                switch ($_POST['date_post']) {
                    case '24_hours':
                        $args['date_query'] = array(
                            array(
                                'column' => 'post_modified_gmt',
                                'after' => '24 hours ago'
                            )
                        );
                        break;
                    case '1_week':
                        $args['date_query'] = array(
                            array(
                                'column' => 'post_modified_gmt',
                                'after' => '1 week ago'
                            )
                        );
                        break;
                    case '1_month':
                        $args['date_query'] = array(
                            array(
                                'column' => 'post_modified_gmt',
                                'after' => '1 month ago'
                            )
                        );
                        break;
                }
            }

            // Location filter
            if (!empty($_POST['job_location'])) {
                $args['meta_query'][] = array(
                    'key' => '_job_location',
                    'value' => sanitize_text_field($_POST['job_location']),
                    'compare' => 'LIKE'
                );
            }

            // Salary Range filter
            if (!empty($_POST['salary_range'])) {
                switch ($_POST['salary_range']) {
                    case 'under_1000':
                        $args['meta_query'][] = array(
                            'key' => '_job_salary',
                            'value' => 1000,
                            'compare' => '<',
                            'type' => 'NUMERIC'
                        );
                        break;
                    case '1000_2500':
                        $args['meta_query'][] = array(
                            'key' => '_job_salary',
                            'value' => array(1000, 2500),
                            'compare' => 'BETWEEN',
                            'type' => 'NUMERIC'
                        );
                        break;
                    case '2500_5000':
                        $args['meta_query'][] = array(
                            'key' => '_job_salary',
                            'value' => array(2500, 5000),
                            'compare' => 'BETWEEN',
                            'type' => 'NUMERIC'
                        );
                        break;
                    case 'custom':
                        if (!empty($_POST['custom_salary_min']) && !empty($_POST['custom_salary_max'])) {
                            $args['meta_query'][] = array(
                                'key' => '_job_salary',
                                'value' => array((int)$_POST['custom_salary_min'], (int)$_POST['custom_salary_max']),
                                'compare' => 'BETWEEN',
                                'type' => 'NUMERIC'
                            );
                        }
                        break;
                }
            }

            // Job Type filter
            if (!empty($_POST['job_listing_type'])) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_type',
                    'field' => 'slug',
                    'terms' => $_POST['job_listing_type'],
                );
            }


            // Job Category filter
            if (!empty($_POST['job_listing_category'])) {
                $args['tax_query'][] = array(
                    'taxonomy' => 'job_listing_category',
                    'field' => 'slug',
                    'terms' => $_POST['job_listing_category'],
                );
            }

            $query = new WP_Query($args);
            $total_jobs = $query->found_posts;

            ob_start();
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                    $title = get_the_title();
                    $company_name = get_post_meta(get_the_ID(), '_company_name', true);
                    $job_types = wp_get_post_terms(get_the_ID(), 'job_listing_type', array('fields' => 'names'));
                    $location = get_post_meta(get_the_ID(), '_job_location', true);
                    $salary = get_post_meta(get_the_ID(), '_job_salary', true);
                    $salaryUnit = get_post_meta(get_the_ID(), '_job_salary_unit', true);
                    $salaryCurrency = get_post_meta(get_the_ID(), '_job_salary_currency', true);
                    $jobDuration = get_post_meta(get_the_ID(), '_job_expires', true);
                    $publish_date = get_the_date();
                    $last_updated = human_time_diff(get_the_modified_time('U'), current_time('timestamp')) . ' ago';

                    $map_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z" stroke="#3D3935" stroke-width="1.5" /><ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" /></svg>';
                    $salary_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10.4998" r="8.33333" stroke="#3D3935" stroke-width="1.5"/><path d="M10 5.5V15.5" stroke="#3D3935" stroke-width="1.5" stroke-linecap="round"/><path d="M12.5 8.41683C12.5 7.26624 11.3807 6.3335 10 6.3335C8.61929 6.3335 7.5 7.26624 7.5 8.41683C7.5 9.56742 8.61929 10.5002 10 10.5002C11.3807 10.5002 12.5 11.4329 12.5 12.5835C12.5 13.7341 11.3807 14.6668 10 14.6668C8.61929 14.6668 7.5 13.7341 7.5 12.5835" stroke="#3D3935" stroke-width="1.5" stroke-linecap="round"/></svg>';
                    $time_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.66659 10.5003C1.66659 15.1027 5.39755 18.8337 9.99992 18.8337C14.6023 18.8337 18.3333 15.1027 18.3333 10.5003C18.3333 5.89795 14.6023 2.16699 9.99992 2.16699" stroke="#D83636" stroke-width="1.5" stroke-linecap="round"/><path d="M10 8V11.3333H13.3333" stroke="#D83636" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="10.5003" r="8.33333" stroke="#D83636" stroke-width="1.5" stroke-linecap="round" stroke-dasharray="0.5 3.5"/></svg>';

                    echo '<div class="ae_job_card">';
                    do_action('single_job_listing_meta_after');
                    echo '<div class="ae_job_card-top">';
                    echo '<img class="ae_job_card__img" src="' . esc_url($featured_image) . '" alt="' . esc_attr($title) . '">';
                    echo '<div>';
                    echo '<h4 class="ae_job_card__title"><a href="' . get_the_permalink() . '">' . esc_html($title) . '</a></h4>';
                    echo '<span class="ae_job_card__company">' . esc_html($company_name) . '</span>';
                    echo '<span style="color: #CACACA; font-size: 14px;"> | </span>';

                    if (!empty($job_types)) {
                        foreach ($job_types as $job_type) {
                            $job_type_color = '';
                            switch ($job_type) {
                                case 'Full Time':
                                    $job_type_color = '17B86A';
                                    break;
                                case 'Part Time':
                                    $job_type_color = 'FF8200';
                                    break;
                                case 'Contract':
                                    $job_type_color = '0275F4';
                                    break;
                                case 'Casual':
                                    $job_type_color = '101010';
                                    break;
                            }
                            echo '<span class="ae_job_card__type" style="color: #' . $job_type_color . ';">' . esc_html($job_type) . '</span>';
                        }
                    }

                    echo '</div>';
                    echo '</div>';
                    if ($location) {
                        echo '<div class="ae_job_card__location">' . $map_svg . ' <span>' . esc_html($location) . '</span></div>';
                    }
                    // if ($salary) {
                    //     echo '<div class="ae_job_card__salary">' . $salary_svg . ' <span>' . esc_html($salary) . ' ' . $salaryCurrency . '</span></div>';
                    // }
                    echo '<hr/>';
                    echo '<div class="ae_job_card-bottom">';
                    // Format the date
                    echo '<div class="ae_job_card__published">';
                    if ($jobDuration) {
                        $formattedJobDuration = date_i18n('M jS, Y', strtotime($jobDuration));
                        echo '' . $time_svg . ' <span>' . esc_html($formattedJobDuration) . '</span>';
                    }
                    echo '</div>';
                    echo '<span class="ae_job_card__modified">Updated ' . esc_html($last_updated) . '</span>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo 'No jobs found.';
            }

            $pagination = paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?page=%#%',
                'type' => 'array',
                'prev_text' => '<svg width="10" height="18" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.75 16.5L1.25 9L8.75 1.5" stroke="#FF8200" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
                'next_text' => '<svg width="10" height="18" viewBox="0 0 10 18" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M1.25 16.5L8.75 9L1.25 1.5" stroke="#FF8200" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
            ));

            if ($pagination) {
                echo '<div class="pagination">';
                foreach ($pagination as $page_link) {
                    echo $page_link;
                }
                echo '</div>';
            }

            $job_results = ob_get_clean();

            wp_send_json_success(array(
                'total_jobs' => $total_jobs,
                'job_results' => $job_results
            ));
        } catch (Exception $e) {
            error_log('Error in Job Filtering AJAX: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'An error occurred while processing the request.'));
        }
    }
}
