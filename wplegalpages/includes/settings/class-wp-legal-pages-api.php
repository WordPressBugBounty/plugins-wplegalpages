<?php
/**
 * Class WP Legal Pages Api file.
 *
 * @package Wplegalpages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WP_Legal_Pages_Api.
 */
class WP_Legal_Pages_Api extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'lp/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'settings';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
	}

	/**
	 * Register the routes for app.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'create_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get a collection of items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
        $token = $request->get_param( 'token' );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();
		$stored_token = $settings->get_token();
		
		// Double-check token validation in callback
		if ( empty( $stored_token ) || ! hash_equals( $stored_token, $token ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid Authorization.', 'wplegalpages' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		
		$data = $settings->get();
		return rest_ensure_response( $data );
    }

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
  public function create_items_permissions_check( $request ) {
		$token            = $request->get_param( 'token' );
		$request_platform = $request->get_param( 'platform' );

		// Validate platform
		if ( 'wordpress' !== $request_platform ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid platform.', 'wplegalpages' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Token must be provided
		if ( empty( $token ) ) {

			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Token missing.', 'wplegalpages' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Get stored token
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();
		$stored_token = $settings->get_token();

		// If no token stored → deny
		if ( empty( $stored_token ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'No token configured.', 'wplegalpages' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Constant-time secure comparison
		if ( ! hash_equals( $stored_token, $token ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'Invalid authorization token.', 'wplegalpages' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
    }
}
