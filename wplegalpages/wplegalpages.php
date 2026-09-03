<?php
/**
 * Plugin Name: WPLP Legal Pages
 * Plugin URI: https://club.wpeka.com/
 * Description: WPLegalPages is a simple 1 click legal page management plugin. You can quickly add in legal pages to your WordPress sites.
 * Author: WPLP Legal Pages
 * Version: 3.7.3
 * Author URI: https://wplegalpages.com
 * License: GPL2
 * Text Domain: wplegalpages
 * Domain Path: /languages
 *
 * @package           Wplegalpages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'WPL_LITE_PLUGIN_URL' ) ) {
	define( 'WPL_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WPL_LITE_PLUGIN_BASENAME' ) ) {
	define( 'WPL_LITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WPLEGAL_API_URL' ) ) {
	define( 'WPLEGAL_API_URL', 'https://app.wplegalpages.com/wp-json/wplegal/v2/' );
}
if ( ! defined( 'WPLEGAL_API_ADMIN_URL' ) ) {
	define( 'WPLEGAL_API_ADMIN_URL', 'https://app.wplegalpages.com/wp-content/plugins/wplegal-api/admin/' );
}
/**
 * Check if the constant GDPR_APP_URL is not already defined.
*/
if ( ! defined( 'WPLEGAL_APP_URL' ) ) {
	define( 'WPLEGAL_APP_URL', 'https://app.wplegalpages.com' );
}
 
if ( ! defined( 'APPWPLP_WPLP_SECRET_KEY_FEATURE_VERSION' ) ) {
	define( 'APPWPLP_WPLP_SECRET_KEY_FEATURE_VERSION', '3.7.3' );
}

/**
 * How long a successful bearer-token verdict stays cached, in seconds.
 *
 * Bounds how long a token revoked at the app can still be honoured here, so
 * keep it short. A cached entry is additionally capped by the token's own
 * `exp` claim, whichever comes first.
 */
if ( ! defined( 'APPWPLP_JWT_CACHE_TTL' ) ) {
	define( 'APPWPLP_JWT_CACHE_TTL', 15 * MINUTE_IN_SECONDS );
}

if ( ! defined( 'APPWPLP_SECRET_KEY_OPTION' ) ) {
	define( 'APPWPLP_SECRET_KEY_OPTION', 'appwplp_shared_secret_key' );
}
 
if ( ! defined( 'APPWPLP_SECRET_KEY_STATUS_OPTION' ) ) {
	define( 'APPWPLP_SECRET_KEY_STATUS_OPTION', 'appwplp_shared_secret_key_status' ); // 'pending' | 'confirmed'
}
if ( ! defined( 'APPWPLP_WPLP_SECRET_KEY_VERSION_OPTION' ) ) {
	define( 'APPWPLP_WPLP_SECRET_KEY_VERSION_OPTION', 'APPWPLP_WPLP_SECRET_KEY_FEATURE_VERSION' );
}

if ( ! defined( 'APPWPLP_SECRET_KEY_ATTEMPTS_OPTION' ) ) {
	define( 'APPWPLP_SECRET_KEY_ATTEMPTS_OPTION', 'appwplp_secret_key_retry_attempts' );
}

/**
 * Total number of registration posts a site will make before giving up.
 *
 * Counted as posts, not as retries on top of a first try, so eight means eight
 * requests and then silence. On a 15 minute loop that is a two hour window.
 *
 * Verification runs inside the registration request on the server, so a post
 * from a site the server cannot reach holds a php-fpm worker there for the full
 * timeout. Without a cap an unreachable site pays that cost every 15 minutes
 * forever; with one, each site's total cost is bounded and the traffic stops on
 * its own.
 */
if ( ! defined( 'APPWPLP_SECRET_KEY_MAX_ATTEMPTS' ) ) {
	define( 'APPWPLP_SECRET_KEY_MAX_ATTEMPTS', 8 );
}



/**
 * Load WC_AM_Client class if it exists.
 */
if ( ! class_exists( 'WC_AM_Client_2_7_WPLegalPages' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'wc-am-client-legalpages.php';
}

/*
 * Instantiate WC_AM_Client class object if the WC_AM_Client class is loaded.
 */
if ( class_exists( 'WC_AM_Client_2_7_WPLegalPages' ) ) {

	$wcam_lib_legalpages = new WC_AM_Client_2_7_WPLegalPages( __FILE__, '', '3.1.0', 'plugin', WPLEGAL_APP_URL, 'WPLegalPages', 'wplegalpages' );
}

if ( ! defined( 'WPLPP_SUFFIX' ) ) {
	define( 'WPLPP_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );
}

if ( ! function_exists( 'activate_wp_legal_pages' ) ) {
	/**
	 * The code that runs during WPLegalPages activation.
	 * This action is documented in includes/class-wp-legal-pages-activator.php
	 */
	function activate_wp_legal_pages() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-legal-pages-activator.php';
		WP_Legal_Pages_Activator::activate();
		add_option( 'analytics_activation_redirect_wplegalpages', true );
		// Get redirect URL.
		add_option( 'redirect_after_activation_option_lp', true );
	}
}

add_action( 'admin_init', 'activation_redirect_wplegalpages' );

/**
 * Generates a cryptographically strong 32-character secret key.
 *
 * @return string
 */
if ( ! function_exists( 'appwplp_generate_secret_key' ) ) {
	function appwplp_generate_secret_key() {
		// random_bytes(16) -> 32 hex characters. Cryptographically secure.
		return bin2hex( random_bytes( 16 ) );
	}
}
/**
 * Single-flight guard shared by WP Cookie Consent and WPLegalPages.
 *
 * Both plugins listen on `appwplp_secret_key_generated` and post the very same
 * key to the very same endpoint, and each one triggers the routine on its own
 * activation and version upgrade. Without a shared claim a single site ends up
 * registering the key several times over.
 *
 * Only the first caller wins - inside one request through the static flag, and
 * across requests through a lock that expires shortly before the 15 minute
 * retry cron is due, so a genuine retry is never blocked.
 *
 * The function is defined once, by whichever of the two plugins loads first, so
 * both share the same static flag and the same lock.
 *
 * @return bool True when the caller owns this registration attempt.
 */
if ( ! function_exists( 'appwplp_claim_secret_key_registration' ) ) {
	function appwplp_claim_secret_key_registration() {
		static $claimed = false;

		// A second listener in the same request - the key is already going out.
		if ( $claimed ) {
			return false;
		}

		// An attempt from another request is still inside the current retry window.
		if ( get_transient( 'appwplp_secret_key_registration_lock' ) ) {
			return false;
		}

		$claimed = true;

		// One minute short of the retry interval so the cron tick always gets through.
		set_transient( 'appwplp_secret_key_registration_lock', time(), 14 * MINUTE_IN_SECONDS );

		return true;
	}
}

/**
 * Generates and stores a local secret key for this site, if one doesn't
 * already exist. Does NOT register it with the server - that happens in
 * step 3, triggered separately after this runs.
 */
if ( ! function_exists( 'appwplp_maybe_generate_secret_key' ) ) {
	function appwplp_maybe_generate_secret_key() {
		$existing_key    = get_option( APPWPLP_SECRET_KEY_OPTION );
		$existing_status = get_option( APPWPLP_SECRET_KEY_STATUS_OPTION );

		if ( ! empty( $existing_key ) && 'confirmed' === $existing_status ) {
			$timestamp = wp_next_scheduled( 'appwplp_secret_key_retry_event' );
			if ( $timestamp ) {
				wp_clear_scheduled_hook( 'appwplp_secret_key_retry_event' );
			}
			delete_option( APPWPLP_SECRET_KEY_ATTEMPTS_OPTION );
			return;
		}

		/*
		 * Out of attempts - stop the loop for good.
		 *
		 * The counter is raised by the listener that actually posts, so an
		 * attempt is never spent by the second plugin standing down on the
		 * shared claim. Reactivating either plugin clears it and allows a fresh
		 * set, which is the only way back for a site that was unreachable while
		 * these ran out.
		 */
		if ( (int) get_option( APPWPLP_SECRET_KEY_ATTEMPTS_OPTION, 0 ) >= APPWPLP_SECRET_KEY_MAX_ATTEMPTS ) {
			$timestamp = wp_next_scheduled( 'appwplp_secret_key_retry_event' );
			if ( $timestamp ) {
				wp_clear_scheduled_hook( 'appwplp_secret_key_retry_event' );
			}
			return;
		}

		if ( ! empty( $existing_key ) ) {
			update_option( APPWPLP_SECRET_KEY_STATUS_OPTION, 'pending', false );
			do_action( 'appwplp_secret_key_generated', $existing_key );
		} else {
			/*
			* First installation - generate the key.
			*/
			$new_key = appwplp_generate_secret_key();
			update_option( APPWPLP_SECRET_KEY_OPTION, $new_key, false );
			update_option( APPWPLP_SECRET_KEY_STATUS_OPTION, 'pending', false );

			do_action( 'appwplp_secret_key_generated', $new_key );
			
		}
		if ( ! wp_next_scheduled( 'appwplp_secret_key_retry_event' ) ) {
			wp_schedule_event( time() + ( 15 * MINUTE_IN_SECONDS ), 'appwplp_fifteen_minutes', 'appwplp_secret_key_retry_event' );
		}
	}
}

/**
 * Custom 15-minute cron schedule, used by the retry mechanism below.
 */
add_filter( 'cron_schedules', function ( $schedules ) {
	$schedules['appwplp_fifteen_minutes'] = array(
		'interval' => 15 * MINUTE_IN_SECONDS,
		'display'  => 'Every 15 Minutes',
	);
	return $schedules;
} );

add_action( 'appwplp_secret_key_retry_event', 'appwplp_maybe_generate_secret_key' );
/**
 * Runs the secret key routine once on existing installs.
 *
 * register_activation_hook() does not fire when WordPress updates a plugin
 * in place, so sites upgrading from a version without this feature would
 * never get a key. A stored feature version is compared against the current
 * one so this runs exactly once per site after the update.
 *
 * @return void
 */
if ( ! function_exists( 'wplp_appwplp_secret_key_version_check' ) ) {
	function wplp_appwplp_secret_key_version_check() {
		if ( APPWPLP_WPLP_SECRET_KEY_FEATURE_VERSION === get_option( APPWPLP_WPLP_SECRET_KEY_VERSION_OPTION ) ) {
			return;
		}
		delete_option( APPWPLP_SECRET_KEY_ATTEMPTS_OPTION );
		appwplp_maybe_generate_secret_key();
		update_option( APPWPLP_WPLP_SECRET_KEY_VERSION_OPTION, APPWPLP_WPLP_SECRET_KEY_FEATURE_VERSION, false );
	}
}
add_action( 'admin_init', 'wplp_appwplp_secret_key_version_check' );


/**
 * It will redirect to the wizard page after plugin activation.
 *
 * @return void
 */
function activation_redirect_wplegalpages() {
	if ( get_option( 'redirect_after_activation_option_lp', false ) ) {
		delete_option( 'redirect_after_activation_option_lp' );
		exit( esc_html( wp_redirect( admin_url( 'admin.php?page=legal-pages' ) ) ) );
	}
}
/**
 * The code that runs during WPLegalPages deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
if ( ! function_exists( 'deactivate_wp_legal_pages' ) ) {
	/**
	 * The code that runs during WPLegalPages deactivation.
	 * This action is documented in includes/class-plugin-name-deactivator.php
	 */
	function deactivate_wp_legal_pages() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-legal-pages-deactivator.php';
		WP_Legal_Pages_Deactivator::deactivate();
	}
}
if ( ! function_exists( 'delete_wp_legal_pages' ) ) {
	/**
	 * The code that runs during WPLegalPages delete.
	 * This action is documented in includes/class-plugin-name-delete.php
	 */
	function delete_wp_legal_pages() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-legal-pages-delete.php';
		WP_Legal_Pages_Delete::delete();
	}
}
register_activation_hook( __FILE__, 'activate_wp_legal_pages' );
register_deactivation_hook( __FILE__, 'deactivate_wp_legal_pages' );
register_uninstall_hook( __FILE__, 'delete_wp_legal_pages' );



/**
 * The core WPLegalPages class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-legal-pages.php';

/**
 * Begins execution of the WPLegalPages.
 *
 * Since everything within the WPLegalPages is registered via hooks,
 * then kicking off the WPLegalPages from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_legal_pages() {
	$legal_pages = new WP_Legal_Pages();
	$legal_pages->run();
}
run_wp_legal_pages();
