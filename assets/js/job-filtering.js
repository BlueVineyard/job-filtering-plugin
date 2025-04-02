

jQuery(document).ready(function ($) {
  if (
    $("#ae_job_filter_wrapper").length > 0 ||
    $("#job-filter-widget-form").length > 0
  ) {
   
    
    var categoriesShown = 5;
    var userLatitude = null;
    var userLongitude = null;
    var searchRadius = 50; // Default radius in kilometers

    // Add geolocation UI to the form
    function addGeolocationUI() {
      // Create geolocation icon to place beside location input
      var geolocationIconHTML = `
        <button type="button" id="get-location" class="location-btn" style="background: none; border: none; cursor: pointer; padding: 0; margin-left: 10px;">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill='#000000'><path d='M440-42v-80q-125-14-214.5-103.5T122-440H42v-80h80q14-125 103.5-214.5T440-838v-80h80v80q125 14 214.5 103.5T838-520h80v80h-80q-14 125-103.5 214.5T520-122v80h-80Zm40-158q116 0 198-82t82-198q0-116-82-198t-198-82q-116 0-198 82t-82 198q0 116 82 198t198 82Zm0-120q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Z'/></svg>
        </button>
      `;

      // Create radius slider container (initially hidden)
      var radiusSliderHTML = `
        <div id="geo-radius-container" style="display: none; margin-top: 10px;">
          <div style="display: flex; align-items: center;">
            <label for="geo-radius-slider" style="margin-right: 10px;"><span id="geo-radius-value">50</span> km</label>
            <input type="range" id="geo-radius-slider" min="5" max="200" value="50" style="flex-grow: 1;">
          </div>
        </div>
        <input type="hidden" id="user_latitude" name="latitude" value="">
        <input type="hidden" id="user_longitude" name="longitude" value="">
        <input type="hidden" id="geo_radius" name="radius" value="50">
      `;

      // Add the icon next to the location input
      $("#job_location")
        .parent()
        .css("display", "flex")
        .append(geolocationIconHTML);

      // Add the radius slider after the location input container
      $("#job_location").parent().after(radiusSliderHTML);

      // Initialize event listeners for geolocation UI
      $("#get-location").on("click", getUserLocation);
      $("#geo-radius-slider").on("input", function () {
        var radius = $(this).val();
        $("#geo-radius-value").text(radius);
        $("#geo_radius").val(radius);
        // Also update the main radius slider and hidden input for consistency
        $("#location-radius-slider").val(radius);
        $("#radius-value").text(radius);
        $("#location_radius").val(radius);
        searchRadius = radius;

        // If we already have location, update the search
        if (userLatitude && userLongitude) {
          fetchFilteredJobs();
        }
      });

      // Show radius slider when location input has value
      $("#job_location").on("input", function () {
        if ($(this).val().trim() !== "") {
          $("#geo-radius-container").show();
        } else if (!userLatitude && !userLongitude) {
          // Hide only if geolocation is not active
          $("#geo-radius-container").hide();
        }
      });
    }

    // Get user's current location
    function getUserLocation() {
      // Change the icon color to indicate processing
      $("#get-location svg path, #get-location svg ellipse").attr(
        "stroke",
        "#808080"
      );

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            userLatitude = position.coords.latitude;
            userLongitude = position.coords.longitude;

            $("#user_latitude").val(userLatitude);
            $("#user_longitude").val(userLongitude);
            $("#geo_radius").val(searchRadius);
            $("#location_radius").val(searchRadius);

            // Try to get the address from coordinates using Google Maps Geocoding API
            reverseGeocode(userLatitude, userLongitude);

            // Change icon color to orange to indicate success
            $("#get-location svg path, #get-location svg").attr(
              "fill",
              "#ff8200"
            );

            // Show the radius slider
            $("#geo-radius-container").show();

            // Fetch jobs with the new location
            fetchFilteredJobs();
          },
          function (error) {
            let errorMessage = "Unable to retrieve your location.";

            switch (error.code) {
              case error.PERMISSION_DENIED:
                errorMessage = "Location access denied.";
                break;
              case error.POSITION_UNAVAILABLE:
                errorMessage = "Location unavailable.";
                break;
              case error.TIMEOUT:
                errorMessage = "Request timed out.";
                break;
            }

            // Reset icon color to black
            $("#get-location svg path, #get-location svg ellipse").attr(
              "stroke",
              "black"
            );
          }
        );
      } else {
        // Reset icon color to black
        $("#get-location svg path, #get-location svg ellipse").attr(
          "stroke",
          "black"
        );
      }
    }

    // Reverse geocode coordinates to address
    function reverseGeocode(lat, lng) {
      // Get API key from the global variable (set in PHP)
      const apiKey =
        typeof jfp_settings !== "undefined" && jfp_settings.api_key
          ? jfp_settings.api_key
          : "";

      // Get country restrictions from the global variable (set in PHP)
      const countryRestrictions =
        typeof jfp_country_restrictions !== "undefined"
          ? jfp_country_restrictions
          : ["au"]; // Default to Australia if not set

      // Use the first country in the list for the region parameter
      const region = countryRestrictions[0] || "au";

      // Restrict results to the selected region
      const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&region=${region}&result_type=street_address|locality|administrative_area_level_1&key=${apiKey}`;

      $.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        success: function (data) {
          if (data.status === "OK" && data.results.length > 0) {
            // Get the formatted address
            const address = data.results[0].formatted_address;

            // Update the location input field with the address
            $("#job_location").val(address);
          }
        },
        error: function (xhr, status, error) {
          console.error("Error reverse geocoding:", error);
        },
      });
    }

    function fetchFilteredJobs(page = 1) {
      var form = $("#job-filter-form");
      $.ajax({
        url: jfp_ajax.ajax_url,
        type: "post",
        data: form.serialize() + "&action=fetch_filtered_jobs&page=" + page,
        success: function (response) {
          if (response.success) {
            $("#job-results").html(response.data.job_results);
            $("#total-results").text(response.data.total_jobs + " Job results");
            // setMinHeight(); // Call the function to set the min height
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log("Error: " + textStatus + ", " + errorThrown);
        },
      });
    }

     addGeolocationUI();

    function setMinHeight() {
      var jobCards = $(".ae_job_card");
      var combinedHeight = 0;
      for (var i = 0; i < 7 && i < jobCards.length; i++) {
        combinedHeight += $(jobCards[i]).outerHeight();
      }
      $("#ae_job_filter_wrapper").css("min-height", combinedHeight + "px");
    }

    /**
     * Custom dropdown for Date Post filter
     */
    const dateDropdown = $(".dropdown").first();
    const dateLabel = dateDropdown.find(".dropdown__filter-selected span");
    const dateOptions = dateDropdown.find(".dropdown__select-option");
    const dateDefaultOption = dateOptions.first().text(); // Store the default option text
    const dateDefaultValue = dateOptions.first().data("value"); // Store the default option value

    dateLabel.on("click", function () {
      dateDropdown.toggleClass("open");
    });

    dateOptions.each(function () {
      $(this).on("click", function () {
        dateLabel.text($(this).text());
        $("#date_post").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        dateDropdown.removeClass("open");
      });
    });

    /**
     * Location input and radius slider
     */
    const locationInput = $("#job_location");
    const radiusSlider = $("#location-radius-slider"); // Match the ID in the HTML
    const radiusValue = $("#radius-value");
    const locationRadius = $("#location_radius"); // Match the ID in the HTML

    // Initialize Google Places Autocomplete
    async function initializeGooglePlaces() {
      try {
        // Dynamically load the places library as recommended in the latest Google Maps API
        const { Place, Autocomplete } = await google.maps.importLibrary('places');
        
        // Get country restrictions from the global variable (set in PHP)
        const countryRestrictions =
          typeof jfp_country_restrictions !== "undefined"
            ? jfp_country_restrictions
            : ["au"]; // Default to Australia if not set

        // Make sure the element exists before trying to initialize autocomplete
        const locationInput = document.getElementById("job_location");
        
        if (locationInput) {
          // Create the autocomplete object with country restrictions
          const autocomplete = new Autocomplete(
            locationInput,
            {
              types: ["geocode"],
              componentRestrictions: { country: countryRestrictions },
              fields: ["geometry", "formatted_address", "name"],
            }
          );

          // When the user selects an address from the dropdown, populate the address field
          autocomplete.addListener("place_changed", function () {
            const place = autocomplete.getPlace();

            if (!place.geometry) {
              console.log(
                "No details available for input: '" + place.name + "'"
              );
              return;
            }

            // Get the location coordinates
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            // Store the coordinates in a global variable for use with the radius slider
            window.selectedPlaceCoordinates = {
              lat: lat,
              lng: lng,
            };

            // Set the coordinates in the form fields
            $("#user_latitude").val(lat);
            $("#user_longitude").val(lng);
            // Show the radius slider since we have a location
            $("#geo-radius-container").show();

            // Fetch jobs with the new location
            fetchFilteredJobs();
          });
        }

        // Apply the same to the widget location input if it exists
        const widgetLocationInput = document.querySelector(
          "#widget_location_filter #job_location"
        );
        
        if (widgetLocationInput) {
          const widgetAutocomplete = new Autocomplete(
            widgetLocationInput,
            {
              types: ["geocode"],
              componentRestrictions: { country: countryRestrictions },
              fields: ["geometry", "formatted_address", "name"],
            }
          );

          widgetAutocomplete.addListener("place_changed", function () {
            const place = widgetAutocomplete.getPlace();

            if (place.geometry) {
              // Get the location coordinates
              const lat = place.geometry.location.lat();
              const lng = place.geometry.location.lng();

              // Store the coordinates in a global variable
              window.selectedPlaceCoordinates = {
                lat: lat,
                lng: lng,
              };

              // Set the coordinates in the form fields
              $("#widget_location_filter #user_latitude").val(lat);
              $("#widget_location_filter #user_longitude").val(lng);
            }

            // Submit the widget form when a place is selected
            $("#job-filter-widget-form").submit();
          });
        }
        
        // Initialize autocomplete for the new location input
        const newLocationInput = document.getElementById("location-autocomplete");
        
        if (newLocationInput) {
          const newAutocomplete = new Autocomplete(
            newLocationInput,
            {
              types: ["geocode"],
              componentRestrictions: { country: countryRestrictions },
              fields: ["geometry", "formatted_address", "name"],
            }
          );
          
          // When the user selects an address from the dropdown
          newAutocomplete.addListener("place_changed", function () {
            const place = newAutocomplete.getPlace();
            
            if (!place.geometry) {
              console.log(
                "No details available for input: '" + place.name + "'"
              );
              return;
            }
            
            // Get the location coordinates
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            
            // Store the coordinates in hidden fields
            $("#location_autocomplete_lat").val(lat);
            $("#location_autocomplete_lng").val(lng);
          });
        }
      } catch (error) {
        console.error(
          "Error initializing Google Places Autocomplete:",
          error
        );
      }
    }

  // Try to initialize Google Places as soon as possible
$(document).ready(function() {
  setTimeout(function() {
    initializeGooglePlaces();
  }, 1000);

});

    // Update radius value when slider changes
    radiusSlider.on("input", function () {
      const radius = $(this).val();
      radiusValue.text(radius);
      locationRadius.val(radius);

      // Also update the geolocation radius slider and hidden input for consistency
      $("#geo-radius-slider").val(radius);
      $("#geo-radius-value").text(radius);
      $("#geo_radius").val(radius);
      searchRadius = radius;

      // Add visual feedback that the slider is working
      radiusValue.css("color", "#ff8200");
      setTimeout(function () {
        radiusValue.css("color", "");
      }, 500);
    });

    // Fetch jobs when slider changes (only when done sliding)
    radiusSlider.on("change", function () {
      // If location is entered, fetch jobs immediately
      if (locationInput.val().trim() !== "") {
        // If we have coordinates from autocomplete, use them
        if (window.selectedPlaceCoordinates) {
          // Use the coordinates from the selected place
          $("#user_latitude").val(window.selectedPlaceCoordinates.lat);
          $("#user_longitude").val(window.selectedPlaceCoordinates.lng);
        }

        // Always update both radius hidden inputs
        $("#location_radius").val($(this).val());
        $("#geo_radius").val($(this).val());

        fetchFilteredJobs();
      }
    });

    // Fetch jobs when location input changes (with debounce)
    let locationTimeout;
    locationInput.on("input", function () {
      clearTimeout(locationTimeout);
      locationTimeout = setTimeout(function () {
        fetchFilteredJobs();
      }, 500); // 500ms debounce
    });

    /**
     * Custom dropdown for Job Type filter
     */
    const jobTypeDropdown = $(".jobTypeDropdown").last();
    const jobTypeLabel = jobTypeDropdown.find(
      ".jobTypeDropdown__filter-selected span"
    );
    const jobTypeOptions = jobTypeDropdown.find(
      ".jobTypeDropdown__select-option"
    );
    const jobTypeDefaultOption = jobTypeOptions.first().text(); // Store the default option text
    const jobTypeDefaultValue = jobTypeOptions.first().data("value"); // Store the default option value

    jobTypeLabel.on("click", function () {
      jobTypeDropdown.toggleClass("open");
    });

    jobTypeOptions.each(function () {
      $(this).on("click", function () {
        jobTypeLabel.text($(this).text());
        $("#job_listing_type").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        jobTypeDropdown.removeClass("open");
      });
    });

    /**
     * Custom dropdown for Job Categories filter
     */
    const jobCatsDropdown = $(".jobCatsDropdown").last();
    const jobCatsLabel = jobCatsDropdown.find(
      ".jobCatsDropdown__filter-selected span"
    );
    const jobCatsOptions = jobCatsDropdown.find(
      ".jobCatsDropdown__select-option"
    );
    const jobCatsDefaultOption = jobCatsOptions.first().text(); // Store the default option text
    const jobCatsDefaultValue = jobCatsOptions.first().data("value"); // Store the default option value

    jobCatsLabel.on("click", function () {
      jobCatsDropdown.toggleClass("open");
    });

    jobCatsOptions.each(function () {
      $(this).on("click", function () {
        jobCatsLabel.text($(this).text());
        $("#job_listing_category").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        jobCatsDropdown.removeClass("open");
      });
    });

    /**
     * Custom dropdown for Organisation filter
     */
    const organisationDropdown = $(".organisationDropdown").last();
    const organisationLabel = organisationDropdown.find(
      ".organisationDropdown__filter-selected span"
    );
    const organisationOptions = organisationDropdown.find(
      ".organisationDropdown__select-option"
    );
    const organisationDefaultOption = organisationOptions.first().text(); // Store the default option text
    const organisationDefaultValue = organisationOptions.first().data("value"); // Store the default option value

    organisationLabel.on("click", function () {
      organisationDropdown.toggleClass("open");
    });

    organisationOptions.each(function () {
      $(this).on("click", function () {
        organisationLabel.text($(this).text());
        $("#company_names").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        organisationDropdown.removeClass("open");
      });
    });

    /**
     * Custom dropdown for Homepage Location filter
     */
    const locationHomeDropdown = $(".home_dropdown").last();
    const locationHomeLabel = locationHomeDropdown.find(
      ".home_dropdown__filter-selected span"
    );
    const locationHomeOptions = locationHomeDropdown.find(
      ".home_dropdown__select-option"
    );
    const locationHomeDefaultOption = locationHomeOptions.first().text(); // Store the default option text
    const locationHomeDefaultValue = locationHomeOptions.first().data("value"); // Store the default option value

    locationHomeLabel.on("click", function () {
      locationHomeDropdown.toggleClass("open");
    });

    locationHomeOptions.each(function () {
      $(this).on("click", function () {
        locationHomeLabel.text($(this).text());
        $("#job_location").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        locationHomeDropdown.removeClass("open");
      });
    });

    /**
     * Custom dropdown for Homepage Category filter
     */
    const $catHomeDropdown = $(".cat_dropdown").last();
    const $catHomeLabel = $catHomeDropdown.find(
      ".cat_dropdown__filter-selected span"
    );
    const $catHomeOptions = $catHomeDropdown.find(
      ".cat_dropdown__select-option"
    );
    const catHomeDefaultOption = $catHomeOptions.first().text(); // Store the default option text
    const catHomeDefaultValue = $catHomeOptions.first().data("value"); // Store the default option value

    $catHomeLabel.on("click", function () {
      $catHomeDropdown.toggleClass("open");
    });

    $catHomeOptions.each(function () {
      $(this).on("click", function () {
        $catHomeLabel.text($(this).text());
        $("#job_listing_category").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        $catHomeDropdown.removeClass("open");
      });
    });

    // Close dropdowns on click outside
    $(document).on("click", function (e) {
      if (!$(e.target).closest(".dropdown").length) {
        dateDropdown.removeClass("open");
        jobTypeDropdown.removeClass("open");
        jobCatsDropdown.removeClass("open");
        organisationDropdown.removeClass("open");
        // catHomeDropdown.removeClass("open");
      }
      if (!$(e.target).closest(".home_dropdown").length) {
        locationHomeDropdown.removeClass("open");
      }
      if (!$(e.target).closest(".cat_dropdown").length) {
        $catHomeDropdown.removeClass("open");
      }
    });

    // Fetch jobs on form input changes
    $("#job-filter-form").on(
      "input change",
      "input, select",
      fetchFilteredJobs
    );

    $("#toggle-more-categories").on("click", function () {
      var categories = $(
        '#job-filter-form input[name="job_listing_category[]"]'
      ).parent();
      categories.slice(categoriesShown, categoriesShown + 5).show();
      categoriesShown += 5;

      if (categoriesShown >= categories.length) {
        $(this).hide();
      }
    });

    // Initially hide categories beyond the first 5
    $('#job-filter-form input[name="job_listing_category[]"]')
      .parent()
      .slice(categoriesShown)
      .hide();

    $('input[name="salary_range"]').on("change", function () {
      if ($(this).val() === "custom") {
        $("#custom_price_range").show();
      } else {
        $("#custom_price_range").hide();
      }
    });

    /**
     * Apply Filters Button
     */
    $("#apply-filters").on("click", function () {
      fetchFilteredJobs();
    });

    /**
     * Reset Filters Button
     */
    $("#reset-filters").on("click", function () {
      $("#job-filter-form")[0].reset();
      $("#custom_price_range").hide();
      dateLabel.text(dateDefaultOption);
      $("#date_post").val(dateDefaultValue);

      // Reset location input field
      $("#job_location").val("");

      // Reset radius sliders
      $("#location-radius-slider").val(50);
      $("#radius-value").text("50");
      $("#location_radius").val("50");

      // Reset geolocation radius slider
      $("#geo-radius-slider").val(50);
      $("#geo-radius-value").text("50");
      $("#geo_radius").val("50");

      // Hide the radius slider container
      $("#geo-radius-container").hide();

      jobTypeLabel.text(jobTypeDefaultOption);
      $("#job_listing_type").val(jobTypeDefaultValue);

      jobCatsLabel.text(jobCatsDefaultOption);
      $("#job_listing_category").val(jobCatsDefaultValue);

      organisationLabel.text(organisationDefaultOption);
      $("#company_names").val(organisationDefaultValue);

      $('input[name="search_query"]').val(""); // Reset the search input field

      // Reset geolocation filter
      userLatitude = null;
      userLongitude = null;
      $("#user_latitude").val("");
      $("#user_longitude").val("");
      $("#geo_radius").val("50");
      $("#geo-radius-value").text("50");
      $("#geo-radius-slider").val(50);

      // Reset the GPS icon color to black
      $("#get-location svg path, #get-location svg ellipse").attr(
        "stroke",
        "black"
      );

      fetchFilteredJobs();
    });

    /**
     * Handle pagination clicks
     */
    $(document).on("click", ".pagination a", function (e) {
      e.preventDefault();
      var page = $(this).attr("href").split("page=")[1];
      fetchFilteredJobs(page);
    });

    /**
     * Initial fetch to load the first set of jobs
     */
    fetchFilteredJobs();

    /**
     * Price Range Slider
     */
    const rangeInput = document.querySelectorAll(".range-input input"),
      priceInput = document.querySelectorAll(".price-input input"),
      range = document.querySelector(".slider .progress");
    let priceGap = 1000;

    priceInput.forEach((input) => {
      input.addEventListener("input", (e) => {
        let minPrice = parseInt(priceInput[0].value),
          maxPrice = parseInt(priceInput[1].value);

        if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
          if (e.target.className === "input-min") {
            rangeInput[0].value = minPrice;
            range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
          } else {
            rangeInput[1].value = maxPrice;
            range.style.right =
              100 - (maxPrice / rangeInput[1].max) * 100 + "%";
          }
        }
      });
    });

    rangeInput.forEach((input) => {
      input.addEventListener("input", (e) => {
        let minVal = parseInt(rangeInput[0].value),
          maxVal = parseInt(rangeInput[1].value);

        if (maxVal - minVal < priceGap) {
          if (e.target.className === "range-min") {
            rangeInput[0].value = maxVal - priceGap;
          } else {
            rangeInput[1].value = minVal + priceGap;
          }
        } else {
          priceInput[0].value = minVal;
          priceInput[1].value = maxVal;
          range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
          range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
        }
      });
    });
  }
});

/**
 * Bookmark Script
 */
jQuery(document).ready(function ($) {
  // Show tooltip on hover
  $(document).on("mouseenter", "[data-tooltip]", function () {
    var $this = $(this);
    var tooltipText = $this.attr("data-tooltip");

    // Create and append the tooltip to the hovered element
    var tooltip = $('<span class="tooltip"></span>').text(tooltipText);
    $this.append(tooltip);

    // Position the tooltip
    tooltip
      .css({
        top: $this.height() + 10 + "px", // 10px below the element
        left: "50%",
        transform: "translateX(-50%)",
        position: "absolute",
      })
      .fadeIn();
  });

  // Hide tooltip on mouse leave
  $(document).on("mouseleave", "[data-tooltip]", function () {
    $(this).find(".tooltip").remove();
  });

  // Using event delegation to bind events to dynamically loaded elements

  // Show the bookmark details when "add-bookmark" is clicked
  $(document).on("click", ".add-bookmark", function (e) {
    e.preventDefault();
    $(this).siblings(".bookmark-details").fadeIn();
  });

  // Close the bookmark details popup on clicking outside of it
  $(document).mouseup(function (e) {
    var container = $(".bookmark-details");

    if (!container.is(e.target) && container.has(e.target).length === 0) {
      container.fadeOut();
    }
  });

  // Reinitialize visibility settings when new content is loaded via AJAX
  $(document).on("ajaxComplete", function () {
    // Initially hide the bookmark details
    $(".bookmark-details").hide();

    // // Check if the form has the 'has-bookmark' class
    // $(".ae_job_card-bookmark_form").each(function () {
    //   if ($(this).hasClass("has-bookmark")) {
    //     $(this).find(".remove-bookmark").show();
    //     $(this).find(".add-bookmark").hide();
    //   } else {
    //     $(this).find(".remove-bookmark").hide();
    //     $(this).find(".add-bookmark").show();
    //   }
    // });
  });
});

/**
 * Advanced Search Btn
 */
jQuery(document).ready(function ($) {
  if ($(".advanced_search").length > 0) {
    $(".advanced_search").on("click", function () {
      $("#ae_job_filter_wrapper #job-filter-form__left").toggleClass("active");
    });
  }
});

/**
 * Widget Location Filter
 */
jQuery(document).ready(function ($) {
  if ($("#widget_location_filter").length > 0) {
    const widgetLocationInput = $("#widget_location_filter #job_location");
    const widgetRadiusSlider = $("#widget-radius-slider");
    const widgetRadiusValue = $("#widget-radius-value");
    const widgetLocationRadius = $("#widget_location_radius");

    // Update radius value when slider changes
    widgetRadiusSlider.on("input", function () {
      const radius = $(this).val();
      widgetRadiusValue.text(radius);
      widgetLocationRadius.val(radius);
    });

    // Submit form when location input changes (with debounce)
    let widgetLocationTimeout;
    widgetLocationInput.on("input", function () {
      clearTimeout(widgetLocationTimeout);
      widgetLocationTimeout = setTimeout(function () {
        $("#job-filter-widget-form").submit();
      }, 1000); // 1 second debounce
    });

    // Submit form when radius changes
    widgetRadiusSlider.on("change", function () {
      if (widgetLocationInput.val().trim() !== "") {
        // If we have coordinates from autocomplete, use them
        if (window.selectedPlaceCoordinates) {
          // Use the coordinates from the selected place
          $("#widget_location_filter #user_latitude").val(
            window.selectedPlaceCoordinates.lat
          );
          $("#widget_location_filter #user_longitude").val(
            window.selectedPlaceCoordinates.lng
          );
          $("#widget_location_filter #search_radius").val($(this).val());
        }

        $("#job-filter-widget-form").submit();
      }
    });
  }
});
