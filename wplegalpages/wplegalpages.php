<?php
/**
 * Plugin Name: WP Legal Pages
 * Plugin URI: https://club.wpeka.com/
 * Description: WPLegalPages is a simple 1 click legal page management plugin. You can quickly add in legal pages to your WordPress sites.
 * Author: WP Legal Pages
 * Version: 3.3.8
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
	define( 'WPLEGAL_API_URL', 'https://api.wpeka.com/wp-json/wplegal/v2/' );
}
if ( ! defined( 'WPLEGAL_API_ADMIN_URL' ) ) {
	define( 'WPLEGAL_API_ADMIN_URL', 'https://api.wpeka.com/wp-content/plugins/wplegal-api/admin/' );
}
/**
 * Check if the constant GDPR_APP_URL is not already defined.
*/
if ( ! defined( 'WPLEGAL_APP_URL' ) ) {
	define( 'WPLEGAL_APP_URL', 'https://app.wplegalpages.com' );
}

if ( ! function_exists( 'wplp_fs' ) ) {
	/**
	 * Helper function to access SDK.
	 *
	 * @return Analytics
	 */
	function wplp_fs() {
		global $wplp_fs;

		if ( ! isset( $wplp_fs ) ) {
			// Include Analytics SDK.
			require_once __DIR__ . '/analytics/start.php';

			$wplp_fs = ras_dynamic_init(
				array(
					'id'              => '4',
					'slug'            => 'wplegalpages',
					'product_name'    => 'WPLegalPages',
					'module_type'     => 'plugin',
					'version'         => '3.1.0',
					'plugin_basename' => 'wplegalpages/wplegalpages.php',
					'plugin_url'      => WPL_LITE_PLUGIN_URL,
				)
			);
		}

		return $wplp_fs;
	}

	// Init Analytics.
	// wplp_fs();
	// SDK initiated.
	// do_action( 'wplp_fs_loaded' );
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
