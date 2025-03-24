# Technical Context: Job Filtering Plugin

## Technologies Used

### Backend
- **PHP**: Core programming language for WordPress plugin development
- **WordPress**: Plugin framework and content management system
- **MySQL**: Database for storing job listings and metadata
- **WordPress Plugin API**: Hooks, filters, and actions for integration
- **WordPress Custom Post Types**: For job listings
- **WordPress Custom Taxonomies**: For job categories and types

### Frontend
- **HTML/CSS**: Structure and styling for the filter interface
- **JavaScript/jQuery**: Client-side interactivity and AJAX requests
- **SCSS**: CSS preprocessor for more maintainable styles
- **Google Maps API**: Geocoding, autocomplete, and location services
- **jQuery UI**: For slider components

### APIs and Services
- **Google Maps Geocoding API**: Converting addresses to coordinates
- **Google Places API**: Location autocomplete functionality
- **Browser Geolocation API**: "Use My Location" feature

## Development Setup
- WordPress installation with WP Job Manager plugin
- Google Maps API key with Geocoding, Places, and Maps JavaScript API enabled
- Country restrictions configured for location searches (default: Australia)
- SCSS compilation setup for styling

## Technical Constraints
- **API Usage Limits**: Google Maps API has usage limits and requires billing setup
- **Browser Compatibility**: Geolocation API requires HTTPS in modern browsers
- **WordPress Version Compatibility**: Plugin should work with WordPress 5.0+
- **Mobile Responsiveness**: UI must adapt to various screen sizes
- **Performance Considerations**: Geocoding and distance calculations can be resource-intensive

## Dependencies
- **WordPress Core**: 5.0+
- **WP Job Manager**: For job listing functionality
- **jQuery**: 1.12.4+ (included with WordPress)
- **Google Maps API**: External dependency for location services
- **ACF Plugin**: For storing location data (optional but recommended)

## Database Structure

### Custom Post Type
- `job_listing`: Standard WP Job Manager post type

### Custom Fields
- `_company_name`: Company/organization name
- `_job_location`: Text representation of job location
- `_job_latitude`: Cached latitude for location
- `_job_longitude`: Cached longitude for location
- `_job_distance`: Calculated distance from search point (temporary)
- `_application_deadline`: Job expiration date
- `address`: ACF field for storing location data (serialized)

### Custom Taxonomies
- `job_listing_category`: Job categories
- `job_listing_type`: Job types (Full-time, Part-time, etc.)
- `location`: Location taxonomy (optional)

## Configuration Options
- Google Maps API key
- Country restrictions for location searches
- Default search radius
- Results per page
- Enabled filter types

## Performance Considerations
1. **Geocoding Caching**: Coordinates are stored with job listings to reduce API calls
2. **Debounced Inputs**: Prevent excessive AJAX requests during typing
3. **Optimized Queries**: SQL queries are optimized for performance
4. **Pagination**: Results are paginated to limit data transfer
5. **Selective Loading**: Only necessary scripts and styles are loaded
