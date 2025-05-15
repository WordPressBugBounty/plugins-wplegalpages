<?php
/**
 * Class for handling the WP Cookie Consent App authentication.
 *
 * @package Wplegalpages
 */

/**
 * Class WP_Legal_Pages_App_Auth.
 */
class WP_Legal_Pages_App_Auth {

	/**
	 * Base URL of WP Legal Pages App API
	 */
	const API_BASE_PATH = WPLEGAL_APP_URL . '/wp-json/api/v1/';

	/**
	 * Is the current plugin authenticated with the WPLegalPages App
	 *
	 * @var bool
	 */
	private $has_auth;

	/**
	 * The api key used for authenticated requests to the WPLegalPages App.
	 *
	 * @var string
	 */
	private $auth_key;

	/**
	 * The auth data from the db.
	 *
	 * @var array
	 */
	private $auth_data;

	/**
	 * Header arguments
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * Request max timeout
	 *
	 * @var int
	 */
	private $timeout = 180;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		// Add AJAX actions for authentication.
		if ( is_admin() ) {
			add_action( 'wp_ajax_wp_legal_pages_app_start_auth', array( $this, 'ajax_auth_url' ) );
			add_action( 'wp_ajax_legalpages_template_view_capabilities', array( $this, 'legalpages_template_view_capabilities' ) );
			add_action( 'wp_ajax_wp_legal_pages_app_paid_start_auth', array( $this, 'legal_page_upgrade_user_plan' ) );
			add_action( 'wp_ajax_wp_legal_pages_app_store_auth', array( $this, 'store_auth_key' ) );
			add_action( 'wp_ajax_wp_legal_pages_app_delete_auth', array( $this, 'delete_app_auth' ) );
		}
	}

	/**
	 * Ajax handler that returns the auth url used to start the Connect process.
	 *
	 * @return void
	 */
	public function ajax_auth_url() {

		// Verify AJAX nonce.
		check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permissions to connect WP Cookie Consent.', 'wplegalpages' ) );
		}
		$is_new_user  = filter_input( INPUT_POST, 'is_new_user', FILTER_VALIDATE_BOOLEAN );
		$site_address = rawurlencode( get_site_url() );
		$api_auth_url = $is_new_user ? $this->get_api_url( 'signup' ) : $this->get_api_url( 'login' );
		// Require necessary file and get settings.
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();
		global $wcam_lib_legalpages;

		$instance_id      = $wcam_lib_legalpages->wc_am_instance_id;
		$object           = $wcam_lib_legalpages->wc_am_domain;
		$software_version = $wcam_lib_legalpages->wc_am_software_version;

		// Build auth URL with site name.
		$auth_url = add_query_arg(
			array(
				'platform'         => 'wordpress',
				'site'             => $site_address,
				'rest_url'         => rawurlencode( get_rest_url() ),
				'src_plugin'       => 'wplegalpages',
				'instance_id'      => rawurldecode( $instance_id ),
				'object'           => rawurldecode( $object ),
				'software_version' => rawurldecode( $software_version ),
			),
			$api_auth_url
		);

		// Send JSON response with auth URL.
		wp_send_json_success(
			array(
				'url' => $auth_url,
			)
		);
	}

	/**
	 * Get current template view capabilities
	 *
	 * @since 3.0.3
	 */
	public function legalpages_template_view_capabilities() {

		check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to perform this action', 'wplegalpages' ) );
		}

		// Require necessary file and get settings.
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();

		global $wcam_lib_legalpages;
		$api_key    = $settings->get( 'api', 'token' );
		$product_id = $settings->get( 'account', 'product_id' );

		if ( empty( $api_key ) || '' === $api_key || empty( $product_id ) || '' === $product_id ) {
			wp_send_json_error(
				array(
					'message' => 'Please check your connection with Wplegalpages  Domain',
					'error'   => true,
				),
			);
		}

		$args = array(
			'api_key' => $api_key,
		);

		update_option( $wcam_lib_legalpages->wc_am_product_id, $product_id );
		update_option(
			$wcam_lib_legalpages->data_key,
			array(
				$wcam_lib_legalpages->data_key . '_api_key' => $api_key,
			),
		);
		$activate_args = $wcam_lib_legalpages->activate( $args, $product_id );
		$status_args   = $wcam_lib_legalpages->status( $args, $product_id );

		$demo_type = isset( $_POST['demo_type'] ) ? sanitize_text_field( wp_unslash( $_POST['demo_type'] )) : '';

		$response = $this->post(
			'plugin/importcaps',
			wp_json_encode(
				array(
					'id'                  => $settings->get_user_id(),
					'platform'            => 'wordpress',
					'demo_type'           => $demo_type,
					'status_args'         => $status_args,
					'activate_args'       => $activate_args,
					'wc_am_activated_key' => $wcam_lib_legalpages->data,
				)
			)
		);

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			wp_send_json_error(
				array(
					'message' => 'Cannot made request with Wplegalpages Domain. Some data is missing.',
					'error'   => true,
				),
			);
		}
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! $response_body->allow_import ) {
			wp_send_json_error(
				array(
					'message' => 'Please check your connection with Wplegalpages Domain',
					'error'   => true,
				),
			);
		}
		if ( isset( $response_body->update_options ) ) {
			if ( 'success' === $response_body->update_options ) {
				update_option( $wcam_lib_legalpages->wc_am_activated_key, $response_body->activated_key );
				update_option( $wcam_lib_legalpages->wc_am_deactivate_checkbox_key, $response_body->deactivate_checkbox_key );
			} elseif ( 'fail_1' === $response_body->update_options ) {
				if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], $response_body->activated_key );
				}
			} elseif ( 'fail_2' === $response_body->update_options ) {
				if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], $response_body->activated_key );
				}
			}
		}

		if ( isset( $response_body->current_instance ) && $response_body->current_instance == 'active' ) {
			$settings->set_plan( $response_body->plan );
			wp_send_json_success(
				array(
					'connection_status' => $response_body->current_instance,
					'error'             => false,
				),
			);
		} else {
			wp_send_json_error(
				array(
					'connection_status' => $response_body->current_instance,
					'error'             => true,
				)
			);
		}
	}
	/**
	 * Ajax handler that returns the auth url used to start the Connect process.
	 *
	 * @return void
	 */
	public function legal_page_upgrade_user_plan() {
		check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission.', 'wplegalpages' ) );
		}

		$site_address = rawurlencode( get_site_url() );
		$rest_url     = rawurlencode( get_rest_url() );
		// adding the condition for check whether user is connecting from popup.
		$is_user_from_connection_popup = isset( $_POST['is_user_from_connection_popup'] ) ? sanitize_text_field( wp_unslash( $_POST['is_user_from_connection_popup'] ) ) : '' ;
		// Require necessary file and get settings.
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();
		global $wcam_lib_legalpages;

		$instance_id      = $wcam_lib_legalpages->wc_am_instance_id;
		$object           = $wcam_lib_legalpages->wc_am_domain;
		$software_version = $wcam_lib_legalpages->wc_am_software_version;
		$api_auth_url     = $this->get_api_url( 'pricing' );

		if($is_user_from_connection_popup){
			$auth_url = add_query_arg(
				array(
					'platform'         => 'wordpress',
					'site'             => $site_address,
					'rest_url'         => $rest_url,
					'is_user_from_connection_popup'       => $is_user_from_connection_popup,
					'instance_id'      => rawurldecode( $instance_id ),
					'object'           => rawurldecode( $object ),
					'software_version' => rawurldecode( $software_version ),
				),
				$api_auth_url
			);
		}else{
			$auth_url = add_query_arg(
				array(
					'platform'         => 'wordpress',
					'site'             => $site_address,
					'rest_url'         => $rest_url,
					'src_plugin'       => 'wplegalpages',
					'instance_id'      => rawurldecode( $instance_id ),
					'object'           => rawurldecode( $object ),
					'software_version' => rawurldecode( $software_version ),
				),
				$api_auth_url
			);
		}
		wp_send_json_success(
			array(
				'url' => $auth_url,
			)
		);
	}

	/**
	 * Get the full URL to an API endpoint by passing the path.
	 *
	 * @param string $path The path for the API endpoint.
	 *
	 * @return string
	 */
	public function get_api_url( $path ) {

		return trailingslashit( WPLEGAL_APP_URL ) . $path;
	}

	/**
	 * Get the full path to an API endpoint by passing the path.
	 *
	 * @param string $path The path for the API endpoint.
	 *
	 * @return string
	 */
	public function get_api_path( $path ) {
		return trailingslashit( self::API_BASE_PATH ) . $path;
	}

	/**
	 * Ajax handler to save the auth API key.
	 *
	 * @return void
	 */
	public function store_auth_key() {
		// Ensure no output before this point
		ob_start();

		// Verify AJAX nonce
		check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );
		global $wcam_lib_legalpages;

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			ob_end_clean();
			wp_send_json_error( esc_html__( 'You do not have permissions to connect WP Cookie Consent.', 'wplegalpages' ) );
		}

		// Get data from POST request
		$data   = isset( $_POST['response'] ) ? $_POST['response'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash	
		$origin = ! empty( $_POST['origin'] ) ? esc_url_raw( wp_unslash( $_POST['origin'] ) ) : false;

		// Verify data and origin
		if ( empty( $data ) || WPLEGAL_APP_URL !== $origin ) {
			ob_end_clean();
			wp_send_json_error();
		}

		// Update option with auth data
		update_option( 'wpeka_api_framework_app_settings', $data );
		$wcam_lib_legalpages->product_id = isset( $_POST['response']['account']['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['response']['account']['product_id'] )) : '';

		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$settings = new WP_Legal_Pages_Settings();

		if ( isset( $_POST['update_options'] ) ) {
			if ( 'success' === $_POST['update_options'] ) {
				update_option( $wcam_lib_legalpages->wc_am_activated_key, sanitize_text_field( wp_unslash( $_POST['activated_key'] ) )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				update_option( $wcam_lib_legalpages->wc_am_deactivate_checkbox_key, sanitize_text_field( wp_unslash( $_POST['deactivate_checkbox_key'] ) )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			} elseif ( 'fail_1' === $_POST['update_options'] ) {
				if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], sanitize_text_field( wp_unslash( $_POST['activated_key'] ) )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}
			} elseif ( 'fail_2' === $_POST['update_options'] ) {
				if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], sanitize_text_field( wp_unslash( $_POST['activated_key'] ) )); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				}
			}
		}
		else{
			update_option($wcam_lib_legalpages->wc_am_activated_key, 'Activated');
		}

		$this->auth_data = $data;

		// Clear any buffered output
		ob_end_clean();

		// Send success response
		wp_send_json_success(
			array(
				'title' => __( 'Authentication successfully completed', 'wplegalpages' ),
				'text'  => __( 'Reloading page, please wait.', 'wplegalpages' ),
			)
		);
	}


	/**
	 * Ajax handler to delete the auth data and disconnect the site from the WPCode Library.
	 *
	 * @return void
	 */
	public function delete_app_auth() {
		check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permissions to disconnect  to App Wpeka Responsive Domain.', 'wplegalpages' ) );
		}

		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$settings   = new WP_Legal_Pages_Settings();
		$options    = $settings->get_defaults();
		$product_id = $settings->get( 'account', 'product_id' );

		global $wcam_lib_legalpages;
		$activation_status = get_option( $wcam_lib_legalpages->wc_am_activated_key );

		$args = array(
			'api_key' => $settings->get( 'api', 'token' ),
		);
		update_option( 'wpeka_api_framework_app_settings', $options );

		if ( false !== get_option( 'gdpr_api_framework_app_settings' ) ) {
			update_option( 'gdpr_api_framework_app_settings', $options );
		}

			$deactivate_results = json_decode( $wcam_lib_legalpages->deactivate( $args, $product_id ), true );

		if ( true === $deactivate_results['success'] && true === $deactivate_results['deactivated'] ) {
			if ( ! empty( $wcam_lib_legalpages->wc_am_activated_key ) ) {
					update_option( $wcam_lib_legalpages->wc_am_activated_key, 'Deactivated' );
			}

				wp_send_json_success(
					array(
						'deactivate_results' => $deactivate_results,
						'error'              => false,
						'message'            => $deactivate_results['activations_remaining'],
					)
				);
		}

		if ( isset( $deactivate_results['data']['error_code'] ) && ! empty( $wcam_lib_legalpages->data ) && ! empty( $wcam_lib_legalpages->wc_am_activated_key ) ) {
			if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
					update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], 'Deactivated' );
			}
				wp_send_json_error(
					array(
						'deactivate_results' => $deactivate_results,
						'error'              => true,
						'message'            => $deactivate_results['data']['error'],
					)
				);
		}
			wp_send_json_error(
				array(
					'deactivate_results' => false,
					'error'              => true,
					'message'            => 'Connection Already Deactivated',
				)
			);
	}


	/**
	 * Check if the site is authenticated.
	 *
	 * @return bool Whether the site is authenticated.
	 */
	public function has_auth() {
		if ( ! isset( $this->has_auth ) ) {
			$auth_key = $this->get_auth_key();

			$this->has_auth = ! empty( $auth_key );
		}
		return $this->has_auth;
	}

	/**
	 * Get the auth key.
	 *
	 * @return bool|string he auth key if available, otherwise false.
	 */
	public function get_auth_key() {
		if ( ! isset( $this->auth_key ) ) {
			$data           = $this->get_auth_data();
			$this->auth_key = isset( $data['api']['token'] ) ? $data['api']['token'] : false;
		}
		return $this->auth_key;
	}

	/**
	 * Get the auth data from the db.
	 *
	 * @return array|bool The auth data if available, otherwise false.
	 */
	public function get_auth_data() {
		if ( ! isset( $this->auth_data ) ) {
			$this->auth_data = get_option( 'wpeka_api_framework_app_settings', false );
		}
		return $this->auth_data;
	}

	/**
	 * Make a POST API Call
	 *
	 * @param string $path  Endpoint route.
	 * @param array  $data  Data.
	 *
	 * @return mixed
	 */
	public function post( $path, $data = array() ) {
		try {
			return $this->request( $path, $data );
		} catch ( Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Add a new request argument for GET requests
	 *
	 * @param string $name   Argument name.
	 * @param string $value  Argument value.
	 */
	public function add_header_argument( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	/**
	 * Make a authenticated request by adding
	 *
	 * @return void
	 */
	protected function make_auth_request() {

		$api_key = $this->get_auth_key();
		if ( ! empty( $api_key ) ) {
			$this->add_header_argument( 'Authorization', 'Bearer ' . $api_key );
			$this->add_header_argument( 'Content-Type', 'application/json' );
		}
	}

	/**
	 * Make an API Request
	 *
	 * @param string $path    Path.
	 * @param array  $data    Arguments array.
	 * @param string $method  Method.
	 *
	 * @return array|mixed|object
	 */
	public function request( $path, $data = array(), $method = 'post' ) {
		$url = $this->get_api_path( $path );

		$this->make_auth_request();

		$args = array(
			'headers' => $this->headers,
			'method'  => strtoupper( $method ),
			'timeout' => $this->timeout,
			'body'    => $data,
		);

		$response = wp_remote_post( $url, $args );

		return $response;
	}
}
