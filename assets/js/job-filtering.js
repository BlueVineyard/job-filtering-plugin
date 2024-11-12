jQuery(document).ready(function ($) {
  if (
    $("#ae_job_filter_wrapper").length > 0 ||
    $("#job-filter-widget-form").length > 0
  ) {
    var categoriesShown = 5;

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
            setMinHeight(); // Call the function to set the min height
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log("Error: " + textStatus + ", " + errorThrown);
        },
      });
    }

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
     * Custom dropdown for Location filter
     */
    const locationDropdown = $(".dropdown").last();
    const locationLabel = locationDropdown.find(
      ".dropdown__filter-selected span"
    );
    const locationOptions = locationDropdown.find(".dropdown__select-option");
    const locationDefaultOption = locationOptions.first().text(); // Store the default option text
    const locationDefaultValue = locationOptions.first().data("value"); // Store the default option value

    locationLabel.on("click", function () {
      locationDropdown.toggleClass("open");
    });

    locationOptions.each(function () {
      $(this).on("click", function () {
        locationLabel.text($(this).text());
        $("#job_location").val($(this).data("value"));
        fetchFilteredJobs(); // Fetch jobs immediately after selecting an option
        locationDropdown.removeClass("open");
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
      console.log("Cat Drop");

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
        locationDropdown.removeClass("open");
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
      dateLabel.text(dateDefaultOption); // Reset the Date Post dropdown text to the default option
      $("#date_post").val(dateDefaultValue); // Reset the hidden input value for Date Post to the default

      locationLabel.text(locationDefaultOption); // Reset the Location dropdown text to the default option
      $("#job_location").val(locationDefaultValue); // Reset the hidden input value for Location to the default

      $('input[name="search_query"]').val(""); // Reset the search input field

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
