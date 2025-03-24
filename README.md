# Job Filtering Plugin

A WordPress plugin for advanced job filtering functionality, designed to work with job listing sites. This plugin provides a comprehensive set of filters to help users find relevant job listings quickly and efficiently.

## Features

- **Advanced Search Filtering**: Filter jobs by multiple criteria simultaneously
- **Location-Based Filtering**: Search for jobs by location with adjustable radius
- **Category Filtering**: Filter jobs by job categories
- **Organization Filtering**: Filter jobs by company/organization
- **Job Type Filtering**: Filter jobs by employment type (full-time, part-time, etc.)
- **Date-Based Filtering**: Filter jobs by posting date
- **Responsive Design**: Works on all device sizes
- **AJAX-Powered**: Real-time filtering without page reloads
- **Shortcode Support**: Easy integration into any WordPress page
- **Widget Form**: Compact form for sidebars and widget areas

## Installation

1. Upload the `job-filtering-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the shortcodes to display the filtering forms on your pages

## Usage

### Main Filter Form

Add the main job filter form to any page using the shortcode:

```
[job_filter_form]
```

This will display the comprehensive job filter form with all available filtering options.

### Widget Filter Form

Add the compact widget filter form using the shortcode:

```
[job_filter_widget]
```

This is ideal for sidebars or areas with limited space.

## Filter Types

### Location Filter

The location filter allows users to search for jobs by location with an adjustable radius:

- **Text Input**: Users can enter any location name
- **Distance Slider**: Adjustable radius from 5km to 200km
- **Geolocation**: "Use My Location" button to use the user's current location

### Date Filter

Filter jobs based on when they were posted:

- Anytime
- 24 hours ago
- 1 week ago
- 1 month ago

### Organization Filter

Filter jobs by the company or organization posting the job. The dropdown is automatically populated with all organizations that have posted jobs.

### Job Category Filter

Filter jobs by their category (e.g., IT, Marketing, Finance). Categories are pulled from the job_listing_category taxonomy.

### Job Type Filter

Filter jobs by employment type (e.g., Full-time, Part-time, Contract). Types are pulled from the job_listing_type taxonomy.

## Technical Details

### File Structure

```
job-filtering-plugin/
├── job-filtering-plugin.php          # Main plugin file
├── assets/                           # Frontend assets
│   ├── css/                          # CSS files
│   │   ├── job-filtering.css         # Main stylesheet
│   │   └── index.scss                # SCSS source file
│   └── js/                           # JavaScript files
│       └── job-filtering.js          # Main JavaScript file
├── includes/                         # PHP classes
│   ├── class-job-filtering-plugin.php # Main plugin class
│   ├── class-job-filtering-ajax.php   # AJAX handling
│   └── class-job-filtering-settings.php # Settings page
└── templates/                        # Template files
    ├── bookmark-form.php             # Bookmark form template
    └── logged-out-bookmark-form.php  # Logged out bookmark form
```

### JavaScript Functionality

The plugin uses jQuery for DOM manipulation and AJAX requests. Key JavaScript features include:

1. **AJAX Filtering**: Real-time job filtering without page reloads
2. **Debounced Input**: Prevents excessive AJAX requests during typing
3. **Custom Dropdowns**: Enhanced dropdown functionality for better UX
4. **Geolocation**: Browser geolocation API integration
5. **Distance Slider**: Interactive radius adjustment
6. **Form Reset**: One-click reset of all filters

### PHP Classes

1. **Job_Filtering_Plugin**: Main class that handles shortcodes and form rendering
2. **Job_Filtering_AJAX**: Handles AJAX requests and job filtering logic
3. **Job_Filtering_Settings**: Manages plugin settings and admin interface

### Filter Logic

The filtering logic combines multiple criteria using WordPress's WP_Query with meta_query and tax_query parameters:

- **Location**: Uses coordinates and the Haversine formula to calculate distances
- **Date**: Uses post_date parameter with calculated date ranges
- **Categories/Types**: Uses tax_query with the respective taxonomies
- **Organization**: Uses meta_query with the _company_name meta field
- **Search**: Uses s parameter for keyword searching

## Customization

### CSS Customization

The plugin's appearance can be customized by adding CSS rules to your theme's stylesheet. Key CSS classes include:

- `#ae_job_filter_wrapper`: Main container for the filter form
- `#job-filter-widget-form`: Container for the widget form
- `.form-group`: Individual filter groups
- `.location-input`: Location input field
- `.location-radius-slider`: Distance slider
- `.dropdown`: Custom dropdown elements

### Filter Customization

Additional filters can be added by extending the `Job_Filtering_Plugin` class and adding new form elements to the form templates.

### Template Customization

The plugin's templates can be overridden by copying the template files to your theme directory in a folder named `job-filtering-plugin/templates/`.

## AJAX Endpoints

The plugin registers the following AJAX endpoints:

- `fetch_filtered_jobs`: Retrieves filtered job listings
- `save_job_bookmark`: Saves a job bookmark for logged-in users
- `remove_job_bookmark`: Removes a job bookmark

## Hooks and Filters

The plugin provides several hooks and filters for developers to extend its functionality:

- `jfp_before_job_filter_form`: Action before the main filter form
- `jfp_after_job_filter_form`: Action after the main filter form
- `jfp_job_query_args`: Filter for modifying the job query arguments
- `jfp_job_card_html`: Filter for modifying the job card HTML

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- jQuery (included with WordPress)

## Compatibility

The plugin is designed to work with:

- Standard WordPress themes
- Job listing plugins that use the post type 'job_listing'
- Responsive themes and mobile devices

## Support

For support or feature requests, please contact the plugin author.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
