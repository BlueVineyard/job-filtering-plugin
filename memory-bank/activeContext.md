# Active Context: Job Filtering Plugin

## Current Focus
The current focus is on reviewing and understanding the radius filter functionality in the Job Filtering Plugin. This involves examining how the radius-based location filtering works from both frontend and backend perspectives.

## Radius Filter Implementation

### Frontend Components
1. **Location Input Field**: 
   - Text input that accepts location names
   - Integrated with Google Places Autocomplete API
   - Present in both main filter form and sidebar widget

2. **Radius Slider**:
   - Range input slider for selecting search radius
   - Default value: 50km
   - Range: 5km to 200km
   - Updates in real-time with visual feedback

3. **Geolocation Button**:
   - "Use My Location" button for browser geolocation
   - Automatically populates coordinates and reverse geocodes to address
   - Shows status messages for location detection

### Backend Processing
1. **Geocoding**:
   - Converts location text to coordinates using Google Maps Geocoding API
   - Supports country restrictions (default: Australia)
   - Caches coordinates with job listings to reduce API calls

2. **Distance Calculation**:
   - Uses Haversine formula to calculate distances between points
   - Accounts for Earth's curvature for accurate distance measurement
   - Calculates distance between search point and each job location

3. **Radius Filtering**:
   - Filters jobs based on calculated distance
   - Only includes jobs within the specified radius
   - Stores distance for display in results

### Data Flow
1. User enters location or uses geolocation
2. Frontend converts location to coordinates via Google API
3. User adjusts radius slider to set search distance
4. AJAX request sends coordinates and radius to server
5. Server retrieves all job listings with coordinates
6. Server calculates distance for each job from search point
7. Server filters jobs to include only those within radius
8. Results returned with distance information
9. Frontend displays filtered jobs with distance indicators

## Recent Changes
- Fixed the radius filter functionality by updating both the PHP and JavaScript code
- Modified the AJAX handler to geocode locations and use coordinates for distance filtering
- Updated the JavaScript to ensure radius sliders and hidden inputs are properly synchronized

## Active Decisions
- Implemented geocoding for location inputs to enable proper radius filtering
- Synchronized multiple radius sliders and inputs for consistent user experience
- Added fallback to text-based location search if geocoding fails

## Next Steps
1. Test the radius filter functionality to ensure it works as expected
2. Consider additional performance optimizations for distance calculations
3. Improve error handling for geocoding failures
4. Add visual feedback for radius changes (e.g., map visualization)
5. Enhance mobile UI for location and radius controls
