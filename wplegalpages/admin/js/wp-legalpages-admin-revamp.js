var jQuery = jQuery.noConflict();
var jQuery = jQuery.noConflict();

jQuery(document).ready(function () {
    // localised variable
    const isProActivated = wplp_localize_data.is_pro_activated;
    const adminUrl = wplp_localize_data.admin_url;
	const lpTerms  = wplp_localize_data.lp_terms;
	const is_user_connected = wplp_localize_data.is_user_connected;

    if (isProActivated) {
        jQuery('.wp-legalpages-admin-tabs-section').addClass('pro-is-activated');
        jQuery('.wp-legalpages-admin-tab').addClass('pro-is-activated');
    }

	if ( lpTerms != '1') {
		jQuery('.wp-legalpages-admin-tab .wp-legalpages-admin-tab-name').addClass('lp-terms-not-acpt');
		jQuery('.wplegal-help-card').addClass('lp-terms-not-acpt');
		jQuery('.wplegal-container .wplegal-container-features .wplegal-features-section .wplegal-section-content .wplegal-feature').addClass('lp-terms-not-acpt');
		jQuery('.wp-legalpages-admin-help-and-support .wp-legalpages-admin-help-text').addClass('lp-terms-not-acpt');
		jQuery('.wp-legalpages-admin-help-and-support .wp-legalpages-admin-support-text').addClass('lp-terms-not-acpt');
	}


    // Hide all tab contents initially except the first one
    jQuery('.wp-legalpages-admin-tab-content').not(':first').hide();
    jQuery('.wp-legalpages-admin-getting-started-tab').addClass('active-tab');
	jQuery('.wp-legalpages-admin-wplp-dashboard-tab').addClass('active-tab');
    jQuery('#getting_started').show();

	// Check if the "wp-legalpages-admin-getting-started-tab" is active
    if (jQuery('.wp-legalpages-admin-getting-started-tab').hasClass('active-tab')) {
        // Add 'active' class to the other tab
        jQuery('.legalpages-tab').addClass('active-tab');
    } else {
        // Remove 'active' class from the other tab if not needed
        jQuery('.legalpages-tab').removeClass('active-tab');
    }

    // On tab click, redirect to the specified URL for create_legalpages tab
    jQuery('.wp-legalpages-admin-create_legalpages-tab').on('click', function () {
        // Redirect to the specified URL when the tab is clicked
        window.location.href =  adminUrl + 'index.php?page=wplegal-wizard';
    });

    // On tab click, show the corresponding content and update URL hash for other tabs
    jQuery('.wp-legalpages-admin-tabs').on('click', '.wp-legalpages-admin-tab:not(.wp-legalpages-admin-create_legalpages-tab)', function (event) {
        event.preventDefault();
        var tabId = jQuery(this).data('tab');

        // Remove active class from all tabs
        jQuery('.wp-legalpages-admin-tab').removeClass('active-tab');

        // Hide all tab contents
        jQuery('.wp-legalpages-admin-tab-content').hide();

        // Show the selected tab content
        jQuery('#' + tabId).show();
        jQuery(this).addClass('active-tab');

        // Update URL hash with the tab ID
        history.pushState({}, '', '#' + tabId);
    });

    // Retrieve the active tab from URL hash on page load
    var hash = window.location.hash;

    if (hash) {
        var tabId = hash.substring(1); // Remove '#' from the hash

        const substr = 'settings#';

        if (tabId.includes(substr)) {
            tabId = 'settings'
        }
        // Remove active class from all tabs
        jQuery('.wp-legalpages-admin-tab').removeClass('active-tab');

        // Hide all tab contents
        jQuery('.wp-legalpages-admin-tab-content').hide();

        // Show the stored active tab content
        jQuery('#' + tabId).show();
        jQuery('[data-tab="' + tabId + '"]').addClass('active-tab');
    }

    // load the clicked link
    if ('scrollRestoration' in window.history) {
        window.history.scrollRestoration = 'manual'
    }

	/**
	 * Javascript functionality for SaaS API Framework.
	*/

	/**
	 * Add an event listener to listen for messages sent from the server.
	*/
	 window.addEventListener("message", function(event) {
		// Check if the event is originated on server and not successful
		if ( event.isTrusted && event.origin === wplp_localize_data.wplegal_app_url ) {
			if (!event.data.success) {
				const popup = jQuery("#popup-site-excausted");
				const cancelButton = jQuery(".popup-image");
		
				popup.fadeIn();
		
				cancelButton.off("click").on("click", function (e) {
				  popup.fadeOut();
				});
			  } else{
				storeAuth(event.data)
			  }
	 	}
	 });
	/**
	 * Store the Authentication Data
	 * @param {*} data
	*/
	function storeAuth(data) {
		
		// Create spinner element
		var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');
   		jQuery('#wpbody-content').append(spinner);
 
		//Make Ajax Call
		jQuery.ajax({
			type: 'POST',
			url: wplp_localize_data.ajaxurl,
			data: {
				action: 'wp_legal_pages_app_store_auth',
				_ajax_nonce : wplp_localize_data._ajax_nonce,
				response: data.response,
				origin: data.origin,

			},
			success: function(response) {
               // Hide the spinner after the success HTML is loaded
				spinner.hide();

				// remove hidden instance from the local storage
				localStorage.removeItem('wplegalConnectPopupHide');
				//remove disconnect from local storage when user connects to the api
				localStorage.removeItem('wplegalDisconnect');

				// redirection for the user if user connection through connection popup.
				var baseUrl = window.location.origin;
				var relativePath = "/wp-admin/admin.php?page=legal-pages";
				var tabHash = "#settings#general";

				 // Construct the full URL
				 var fullUrl = baseUrl + relativePath + tabHash;

				 //reload the window after settimeout.
				 setTimeout(function () {
				   window.location.href = fullUrl;
				   location.reload();
				 }, 100);
				// //reload the window after settimeout.
				// setTimeout(function() {
				// 	location.reload();
				// }, 100);

			},
			error: function(error) {
				// Handle error response
				console.error('Error sending data to PHP:', error);
			}
		});

	}

	/**
	 * Clicked on connect to exiting account.
	*/
	jQuery(".gdpr-start-auth").on("click", startAuth);
	jQuery('.api-connect-to-account-btn').on('click', startAuth );
	jQuery('.wplegal-api-connect-existing').on('click', startAuth );
	jQuery(".gdpr-cookie-consent-admin-upgrade-button").on("click", paidAuth);

	/**
	 * Function to Start the Authentication Process.
	 *
	 * @param {*} event
	 */
	function startAuth(event) {

		// Prevent the default action of the event.
		event.preventDefault();

		var is_new_user = this.classList.contains("gdpr-signup");

		// Create spinner element
		var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');

		// Append spinner to #wpbody-content div.

		var container = jQuery('#wpbody-content');
		container.css('position', 'relative'); // Ensure container has relative positioning.
		container.append(spinner);

		// Make an AJAX request.
		jQuery.ajax(
			{
				url  : wplp_localize_data.ajaxurl,
				type : 'POST',
				data : {
					action      : 'wp_legal_pages_app_start_auth',
					_ajax_nonce : wplp_localize_data._ajax_nonce,
					is_new_user : is_new_user,
				},
				beforeSend: function() {
					// Show spinner before AJAX call starts
					spinner.show();
				},
				complete: function() {
					// Hide spinner after AJAX call completes
					spinner.hide();
				}
			}
		)
		.done(
			function ( response ) {

				// Get the width and height of the viewport.
				var viewportWidth = window.innerWidth;
				var viewportHeight = window.innerHeight;

				// Set the dimensions of the popup.
				var popupWidth = 1360;
				var popupHeight = 740;

				// Calculate the position to center the popup.
				var leftPosition = (viewportWidth - popupWidth) / 2;
				var topPosition = (viewportHeight - popupHeight) / 2;

				// Open the popup window at the calculated position.
				var e = window.open(
				response.data.url,
				"_blank",
				"location=no,width=" + popupWidth + ",height=" + popupHeight + ",left=" + leftPosition + ",top=" + topPosition + ",scrollbars=0"
				);

				if (null === e) {
					console.log('Failed to open the authentication window');
				} else {
					e.focus();// Focus on the popup window.
				}

			}
		);


	}

	/**
   * Store the Redirect to the Buying Page.
   * @param {*} data
   */

	function paidAuth(event) {
		// Prevent the default action of the event.
		event.preventDefault();
	
		var is_new_user = this.classList.contains('wplegal-api-connect-new');
	
		// Create spinner element
		var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');
	
		// Append spinner to #wpbody-content div.

		var container = jQuery('#wpbody-content');
		container.css('position', 'relative'); // Ensure container has relative positioning.
		container.append(spinner);
	
		// Make an AJAX request.
		jQuery
		  .ajax({
			url: wplp_localize_data.ajaxurl,
			type: "POST",
			data: {
				action      : 'wp_legal_pages_app_paid_start_auth',
				_ajax_nonce : wplp_localize_data._ajax_nonce,
				is_new_user : is_new_user,
			},
			beforeSend: function () {
			  // Show spinner before AJAX call starts
			  spinner.show();
			},
			complete: function () {
			  // Hide spinner after AJAX call completes
			  spinner.hide();
			},
		  })
		  .done(function (response) {
			// Get the width and height of the viewport
			var viewportWidth = window.innerWidth;
			var viewportHeight = window.innerHeight;
	
			// Set the dimensions of the popup
			var popupWidth = 1260;
			var popupHeight = 740;
	
			// Calculate the position to center the popup
			var leftPosition = (viewportWidth - popupWidth) / 2;
			var topPosition = (viewportHeight - popupHeight) / 2;
			// Open the popup window at the calculated position
			var e = window.open(
			  response.data.url,
			  "_blank",
			  "location=no,width=" +
				popupWidth +
				",height=" +
				popupHeight +
				",left=" +
				leftPosition +
				",top=" +
				topPosition +
				",scrollbars=0"
			);
			if (null == e) {
			  console.log("error while opening the popup window");
			}
		  });
	  }


	/**
   * Clicked on connect to exiting account.
   */
	jQuery(document).on("click", ".wplegal-mascot-quick-links-item-upgrade", wplegalPaidAuth);
	jQuery(document).on("click", ".wplegalpages-admin-upgrade-button", wplegalPaidAuth);


	/**
   * Store the Authentication Data
   * @param {*} data
   */

	function wplegalPaidAuth(event) {
		// Prevent the default action of the event.
		event.preventDefault();
		var is_new_user = this.classList.contains('wplegal-api-connect-new');
		var is_user_from_connection_popup = this.classList.contains('wplegalpages-connection-popup');

		// Create spinner element
		var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');

		// Append spinner to #wpbody-content div.

		var container = jQuery('#wpbody-content');
		container.css('position', 'relative'); // Ensure container has relative positioning.
		container.append(spinner);


		// Make an AJAX request.
		jQuery
		.ajax({
		  url: wplp_localize_data.ajaxurl,
		  type: "POST",
		  data: {
			action: "wp_legal_pages_app_paid_start_auth",
			_ajax_nonce: wplp_localize_data._ajax_nonce,
			is_user_from_connection_popup: is_user_from_connection_popup ? true :false,
		  },
		  beforeSend: function () {
			// Show spinner before AJAX call starts
			spinner.show();
		  },
		  complete: function () {
			// Hide spinner after AJAX call completes
			spinner.hide();
		  },
		})
		.done(function (response) {
		  // Get the width and height of the viewport
		  var viewportWidth = window.innerWidth;
		  var viewportHeight = window.innerHeight;
  
		  // Set the dimensions of the popup
		  var popupWidth = 1260;
		  var popupHeight = 740;
  
		  // Calculate the position to center the popup
		  var leftPosition = (viewportWidth - popupWidth) / 2;
		  var topPosition = (viewportHeight - popupHeight) / 2;
		  // Open the popup window at the calculated position
		  var e = window.open(
			response.data.url,
			"_blank",
			"location=no,width=" +
			  popupWidth +
			  ",height=" +
			  popupHeight +
			  ",left=" +
			  leftPosition +
			  ",top=" +
			  topPosition +
			  ",scrollbars=0"
		  );
		  if (null == e) {
			console.log("error while opening the popup window");
		  }
		});
	}



	/**
	 * modal pop after successfull connection or disconnection
	*/

	var fixedBanner = jQuery('.wp-legalpages-admin-fixed-banner');

	jQuery('#wplegal-wpcc-notice').insertAfter(fixedBanner);
	jQuery('#wplegal-disconnect-wpcc-notice').insertAfter(fixedBanner);


	// check if user is connected, show connection popup
	if ( is_user_connected ) {
		jQuery('#wplegal-wpcc-notice').removeClass('wplegal-hidden');
		jQuery('#wplegal-wpcc-notice').show();

	}else if (localStorage.getItem('wplegalDisconnect') === 'true'){
		jQuery('#wplegal-disconnect-wpcc-notice').removeClass('wplegal-hidden');
		jQuery('#wplegal-disconnect-wpcc-notice').show();
	}

	// Check if the 'wplegalConnectPopupHide' item in localStorage is set to 'true'.
	if (localStorage.getItem('wplegalConnectPopupHide') === 'true') {
		jQuery('#wplegal-wpcc-notice').hide();
		jQuery('#wplegal-disconnect-wpcc-notice').hide();
	}

	// Add a click event listener to the element with class 'notice-dismiss'.
	jQuery('#wplegal-wpcc-notice .notice-dismiss').on('click', closeDiv );

	/**
	 * Method to close the div.
	*/
	function closeDiv (){
		jQuery('#wplegal-wpcc-notice').hide();
		localStorage.setItem('wplegalConnectPopupHide', 'true');
	}

	// Add a click event listener to the element with class 'notice-dismiss'.
	jQuery('#wplegal-disconnect-wpcc-notice .notice-dismiss').on('click', closeDivDisconnect );

	/**
	 * Method to close the div.
	*/
	function closeDivDisconnect (){
		jQuery('#wplegal-disconnect-wpcc-notice').hide();
		localStorage.setItem('wplegalConnectPopupHide', 'true');
	}

	/**
	 * click on disconnect button to disconnect api connection.
	*/
	jQuery('.wplegal-api-connection-disconnect-btn').on('click', disconnectAppAuth );

	/**
	 * Function to Disconnect the API Connection
	*/
	function disconnectAppAuth () {

		// Create spinner element
		var spinner = jQuery('<div class="wplegal-ajax-spinner"></div>');

		// Append spinner to .wplegal-connection-tab-card div
		var container = jQuery('.wplegal-connection-tab-card');
		container.css('position', 'relative'); // Ensure container has relative positioning
		container.append(spinner);

		//Make Ajax Requests.
		jQuery.ajax(
			{
				url  : wplp_localize_data.ajaxurl,
				type : 'POST',
				data : {
					action      : 'wp_legal_pages_app_delete_auth',
					_ajax_nonce : wplp_localize_data._ajax_nonce,
				},
				beforeSend: function() {
					// Show spinner before AJAX call starts
					spinner.show();
				},
				complete: function() {
					// Hide spinner after AJAX call completes
					spinner.hide();
				}
			}
		).done(
			function ( response ) {

					location.reload();
				
			}
		);
	}

	   //For Installing GDPR plugin - Unified Dashboard 
	   jQuery(document).ready(function ($) {
		// Handle wp help menu click- start
		jQuery('#toplevel_page_wp-legal-pages a[href="admin.php?page=wplp-dashboard#help-page"]').on('click', function (e) {
			e.preventDefault(); // Prevent default anchor behavior
			// Remove 'current' class from all <li> elements
			jQuery('li').removeClass('current');
	  
			// Add 'current' class to the immediate <li> parent of the clicked <a> tag
			jQuery(this).closest('li').addClass('current');
	  
			// Show the #help-page div and hide all other sibling divs
			if (jQuery('.wplegal-container').length > 0) {
			  jQuery('.wplegal-container').hide();
			  jQuery('.wp-legalpages-admin-wplp-dashboard-tab').removeClass('active-tab');

        		jQuery('.wp-legalpages-admin-help-tab').addClass('active-tab');
			}
			jQuery('#help-page').show();
		 });
		if (window.location.href.includes('#help-page')) {
			// Select the "Help Page" link and its immediate parent <li>
			var $helpPageLink = jQuery('#toplevel_page_wp-legal-pages a[href="admin.php?page=wplp-dashboard#help-page"]');
			var $dashboardLink = jQuery('#toplevel_page_wp-legal-pages a[href="admin.php?page=wplp-dashboard"]');
			
			// Add the 'current' class to the parent <li> of the "Help Page" link
			$helpPageLink.closest('li').addClass('current');
	  
			// Remove the 'current' class from the parent <li> of the "Dashboard" link
			$dashboardLink.closest('li').removeClass('current');
		  }
		// Handle wp help menu click- end

		$('.install-gdpr-button').on('click', function (e) {
			e.preventDefault();
	
			var pluginSlug = 'gdpr-cookie-consent'; //$(this).data('plugin-slug'); // Get the plugin slug from the anchor tag
			var baseURL = window.location.origin;
		// Construct the URL for plugins.php
		var dashboardpageurl =
		  baseURL + "/wp-admin/admin.php?page=legal-pages";
		
			 var $clickedButton = $(this); // Reference to the clicked button
	
			$.ajax({
				url: wplp_localize_data.ajaxurl,
				method: 'POST',
				data: {
					action: 'gdpr_install_plugin',
					plugin_slug: pluginSlug,
					_ajax_nonce: wplp_localize_data._ajax_nonce,
				},
				beforeSend: function () {
					$clickedButton.text('Installing...');
				},
				success: function (response) {
					if (response.success) {
					  window.location.href = dashboardpageurl;
  
					} else {
					   // $('.install-plugin-status').text('Error: ' + response.data.message);
					}
				},
				error: function () {
					//$('.install-plugin-status').text('An unexpected error occurred.');
				},
			});
		});

		$('#support_form').on('submit', function (e) {
			e.preventDefault();
	
			// Collect form data
			var formData = {
				action: 'wplegalpages_support_request',
				name: $('input[name="sup-name"]').val(),
				email: $('input[name="sup-email"]').val(),
				message: $('textarea[name="sup-message"]').val(),
				wplegalpages_nonce: $('input[name="wplegalpages_nonce"]').val(),
			};
	
			// Clear previous messages
			$('.notice').remove();
	
			// Send AJAX request
			$.ajax({
				url: ajaxurl, // Provided by WordPress
				type: 'POST',
				data: formData,
				success: function (response) {
					if (response.success) {
						$('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>').insertBefore('#support_form');
					} else {
						$('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>').insertBefore('#support_form');
					}
				},
				error: function () {
					$('<div class="notice notice-error is-dismissible"><p>An unexpected error occurred. Please try again.</p></div>').insertBefore('#support_form');
				},
			});
		});

		// Tooltip for Pro in Compliances Section.
		$('.wplegalpages-not-pro-tooltip').on('mouseenter', function () {
			$(this).siblings('.wplegalpages-not-pro-tooltip-text').stop(true, true).fadeIn(200);
		});
		
		$('.wplegalpages-not-pro-tooltip, .wplegalpages-not-pro-tooltip-text').on('mouseleave', function () {
			const $tooltip = $(this).siblings('.wplegalpages-not-pro-tooltip-text').length
				? $(this).siblings('.wplegalpages-not-pro-tooltip-text')
				: $(this); // If the mouse leaves the tooltip itself
		
			setTimeout(function() {
				if (
					!$tooltip.is(':hover') &&
					!$tooltip.siblings('.wplegalpages-not-pro-tooltip').is(':hover')
				) {
					$tooltip.stop(true, true).fadeOut(200);
				}
			}, 100);
		});
	});

});
document.addEventListener("DOMContentLoaded", alignSideBar);
function alignSideBar(){
  var side_bar = document.querySelector(".wplp-sub-tabs");

  function updateTopBasedOnTab(tabList) {
        if (tabList.includes("settings") || tabList.includes("all_legal_pages")) {
            side_bar.style.top = "185px";
        } 
		else {
            side_bar.style.top = "65px"; // Default value
        }
    }
    // Get the hash (part after #)
    var urlParts = window.location.href.split("#");
    updateTopBasedOnTab(urlParts);

    document.querySelectorAll(".wplp-sub-tabs .wp-legalpages-admin-tab").forEach(function(tab){
        tab.addEventListener("click", function () {
            var tabValue = this.getAttribute("data-tab");
            updateTopBasedOnTab([tabValue]);
        });
    });
}