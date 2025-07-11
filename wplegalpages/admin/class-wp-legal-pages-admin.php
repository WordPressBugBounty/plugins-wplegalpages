<?php
/**
 * The Admin-specific functionality of the WPLegalPages.
 *
 * @link       http://wplegalpages.com/
 * @since      1.5.2
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/admin
 */

/**
 * The admin-specific functionality of the WPLegalPages.
 *
 * Defines the WPLegalPages name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 * @author     WPEka <support@wplegalpages.com>
 */
if ( ! class_exists( 'WP_Legal_Pages_Admin' ) ) {
	/**
	 * The admin-specific functionality of the WPLegalPages.
	 *
	 * Defines the WPLegalPages name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/includes
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages_Admin {
		/**
		 * The ID of this WPLegalPages.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $WP Legal Pages_name    The ID of this WPLegalPages.
		 */
		private $plugin_name;

		/**
		 * The version of this WPLegalPages.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this WPLegalPages.
		 */
		private $version;


		protected $settings;
		/**
		 * The name of the plugin.
		 *
		 * @since    2.8.0
		 * @access   private
		 * @var      string    $WP Legal Pages_name    The name of this Plugin.
		 */
		private static $wplp_plugin_name;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $plugin_name The name of this WPLegalPages.
		 * @param      string $version    The version of this WPLegalPages.
		 */
		public function __construct( $plugin_name, $version ) {
			$this->plugin_name      = $plugin_name;
			$this->version          = $version;
			self::$wplp_plugin_name = $this->plugin_name;
			// Affiliate disclosure.
			$lp_general = get_option( 'lp_general' );
			if ( isset( $lp_general['affiliate-disclosure'] ) && '1' === $lp_general['affiliate-disclosure'] ) {
				add_action( 'add_meta_boxes', array( $this, 'wplegalpages_pro_register_meta_boxes' ) );
				
				add_action( 'save_post', array( $this, 'wplegalpages_pro_save_meta_box' ) );
				add_filter( 'the_content', array( $this, 'wplegalpages_pro_post_content' ) );
			}
			add_action('wp_ajax_gdpr_install_plugin', array($this, 'wplp_gdpr_install_plugin_ajax_handler'));
			add_action('rest_api_init', array($this, 'register_wpl_dashboard_route'));
			
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {
			wp_register_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/wp-legal-pages-admin' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-bootstrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-vue-style', plugin_dir_url( __FILE__ ) . 'css/vue/vue-getting-started' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-wizard', plugin_dir_url( __FILE__ ) . 'css/wp-legal-wizard-admin' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-select2', plugin_dir_url( __FILE__ ) . 'wizard/libraries/select2/select2.css', array(), $this->version, 'all' );
			wp_register_style( $this->plugin_name . '-review-notice', plugin_dir_url( __FILE__ ) . 'css/wp-legal-pages-review-notice' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
		    wp_enqueue_style( $this->plugin_name );
		    wp_enqueue_style( $this->plugin_name . '-review-notice' );
		}


		/**
		 * Register REST Route to send data to saas server
		 *
		 * @since    1.0.0
		 */
	public function register_wpl_dashboard_route() {
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		global $is_user_connected, $api_user_plan; // Make global variables accessible
		$this->settings = new WP_Legal_Pages_Settings();
		
		$is_user_connected = $this->settings->is_connected();
		
		
		register_rest_route(
			'wpl/v2', // Namespace
			'/get_user_dashboard_data', 
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'wplp_send_data_to_dashboard_appwplp_server'), // Function to handle the request
				'permission_callback' => function() use ($is_user_connected) {
					// Check if user is connected and the API plan is valid
					if ($is_user_connected) {
						return true; // Allow access
					}
					return new WP_Error('rest_forbidden', 'Unauthorized access', array('status' => 401));
				},
			)
		);
		register_rest_route(
			'wpl/v2', // Namespace
			'/delete_activation', 
			array(
				'methods'  => 'POST',
				'callback' => array($this, 'disconnect_account_request'), // Function to handle the request
				'permission_callback' => function() use ($is_user_connected) {
					// Check if user is connected and the API plan is valid
					if ($is_user_connected) {
						return true; // Allow access
					}
					return new WP_Error('rest_forbidden', 'Unauthorized access', array('status' => 401));
				},
			)
		);

		$appwplp_namespace  = 'appwplp/v1';

		$appwplp_payment_status_route      = 'wplp_get_payment_status';
		$appwplp_payment_status_full_route = '/' . trim( $appwplp_namespace, '/' ) . '/' . trim( $appwplp_payment_status_route, '/' );

		$appwplp_subscription_status_pending_cancel_route = 'wplp_subscription_status_pending_cancel';
		$appwplp_subscription_status_full_route           = '/' . trim( $appwplp_namespace, '/' ) . '/' . trim( $appwplp_subscription_status_pending_cancel_route, '/' );

		$rest_server = rest_get_server();
		$routes      = $rest_server->get_routes();

		if ( ! array_key_exists( $appwplp_payment_status_full_route, $routes ) ) {
			register_rest_route(
				$appwplp_namespace,
				'/' . $appwplp_payment_status_route,
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'wplegalpages_get_wplp_payment_status' ),
					'permission_callback' => function() use ( $is_user_connected ) {
						// Check if user is connected and the API plan is valid
						if ( $is_user_connected ) {
							return true; // Allow access
						}
						return new WP_Error( 'rest_forbidden', 'Unauthorized access', array( 'status' => 401 ) );
					},
				)
			);
		}

		if ( ! array_key_exists( $appwplp_subscription_status_full_route, $routes ) ) {
			register_rest_route(
				$appwplp_namespace,
				'/' . $appwplp_subscription_status_pending_cancel_route,
				array(
					'methods'  => 'POST',
					'callback' => array( $this, 'wplegalpages_set_subscription_payment_pending_cancel' ),
					'permission_callback' => function() use ( $is_user_connected ) {
						// Check if user is connected and the API plan is valid
						if ( $is_user_connected ) {
							return true; // Allow access
						}
						return new WP_Error('rest_forbidden', 'Unauthorized access', array( 'status' => 401 ) );
					},
				)
			);
		}
	}

	/**
	 * REST API callback to update and store the subscription payment status.
	 *
	 * This endpoint is hit by the main site to inform the client site about the subscription payment status.
	 * It either sets or deletes a transient based on whether the payment is 'completed' or not.
	 *
	 * @param WP_REST_Request $request The REST request object containing the payment status.
	 *
	 * @return WP_REST_Response The response confirming the updated status.
	 */
	public function wplegalpages_get_wplp_payment_status( WP_REST_Request $request ) {

		$payment_status = $request->get_param( 'payment_status' );

		if ( 'completed' === $payment_status ) {
			delete_transient( 'app_wplp_subscription_payment_status_failed' );
			$message = 'Completed';
		} else {
			set_transient( 'app_wplp_subscription_payment_status_failed', true, 7 * DAY_IN_SECONDS );
			$message = 'Failed';
		}

		return rest_ensure_response(
			array(
				'message' => 'Status Changed to ' . $message,
			)
		);
	}

	/**
	 * REST API callback to update the local subscription status to either 'active' or 'pending-cancel'.
	 *
	 * This endpoint is called by the main site to notify the client site about the subscription status change.
	 * It updates or deletes an option based on the received status.
	 *
	 * @param WP_REST_Request $request The REST request object containing the subscription status.
	 *
	 * @return WP_REST_Response The response confirming the updated status.
	 */
	public function wplegalpages_set_subscription_payment_pending_cancel( WP_REST_Request $request ) {

		$subscription_status = $request->get_param( 'subscription_status' );

		if ( 'active' === $subscription_status ) {
			delete_option( 'app_wplp_subscription_status_pending_cancel' );
			$message = 'Active';
		} else {
			update_option( 'app_wplp_subscription_status_pending_cancel', 1 );
			$message = 'Pending Cancel';
		}

		return rest_ensure_response(
			array(
				'message' => 'Subscription Status Changed to ' . $message,
			)
		);
	}

	/**
	 * Fucntion to disconnect account when site deleted from saas dashboard
	 */
	public function disconnect_account_request(){

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

		if ( false !== get_option( 'wplegal_api_framework_app_settings' ) ) {
			update_option( 'wplegal_api_framework_app_settings', $options );
		}

		update_option( $wcam_lib_legalpages->wc_am_activated_key, 'Deactivated' );

		if ( isset( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ] ) ) {
			update_option( $wcam_lib_legalpages->data[ $wcam_lib_legalpages->wc_am_activated_key ], 'Deactivated' );
		}
	}

	/* Added endpoint to send dashboard data from plugin to the saas appwplp server */
	public function wplp_send_data_to_dashboard_appwplp_server(WP_REST_Request $request  ){		
		$current_user = wp_get_current_user();
		$client_site_name = get_bloginfo('name');
		
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';
		$this->settings = new WP_Legal_Pages_Settings();
		$user_email_id         = $this->settings->get_email();


		$is_user_connected = $this->settings->is_connected();

		global $wpdb;
		$post_tbl     = $wpdb->prefix . 'posts';
		$postmeta_tbl = $wpdb->prefix . 'postmeta';
		$post_tbl     = esc_sql( $post_tbl );
		$postmeta_tbl = esc_sql( $postmeta_tbl );
		
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching	
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(*) 
				FROM {$post_tbl} AS ptbl, {$postmeta_tbl} AS pmtbl 
				WHERE ptbl.ID = pmtbl.post_id 
				AND ptbl.post_status = %s 
				AND pmtbl.meta_key = %s
				",
				'publish',
				'is_legal'
			)
		);
				
		$pagesresult = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT ptbl.post_title 
				FROM {$post_tbl} AS ptbl, {$postmeta_tbl} AS pmtbl 
				WHERE ptbl.ID = pmtbl.post_id 
				AND ptbl.post_status = %s 
				AND pmtbl.meta_key = %s
				",
				'publish',
				'is_legal'
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching	

	$titles = array_column($pagesresult, 'post_title');

		return rest_ensure_response(
			array(
				'success' => true,
				'is_user_connected'                => $is_user_connected,
				'client_site_url'                  => get_site_url(),
				'user_email_id'					   => $user_email_id,
				'legal_pages_published'			   => $count,
				'page_results'					   => $titles,
				'client_site_name'				   => $client_site_name,
			)
		);
	}
		
	/**
	 * Function to display gdpr review notice on admin page.
	 *
	 * @return void
	 */
	public function wplp_admin_review_notice() {
		$wplp_review_option_exists = get_option( 'wplp_review_pending' );
		switch ( $wplp_review_option_exists ) {
			case '0':
				$check_for_review_transient = get_transient( 'wplp_review_transient' );
				if ( false === $check_for_review_transient ) {
					set_transient( 'wplp_review_transient', 'Review Pending',2592000);
					update_option( 'wplp_review_pending', '1', true );
				}
				break;
			case '1':
				$check_for_review_transient = get_transient( 'wplp_review_transient' );
				if ( false === $check_for_review_transient ) {
					wp_enqueue_style( $this->plugin_name . 'review-notice' );
					echo sprintf(
						'
						<div class="wplp-review-notice updated">

						<form method="post" action="%2$s" id="review_form">
							<div class="wplp-review-notice-text-container">
								<p><span>%3$s<strong>WP Legal Pages?</strong>%4$s</span></p>
								<button class="wplp-review-dismiss-btn" style="border: none;padding:0;background: none;color: #2271b1;"href="%2$s"><i class="dashicons dashicons-dismiss"></i>%5$s</button>
							</div>
							<div class="wplp-review-btns-container">
								<button class="wplp-review-btns wplp-review-rate-us-btn"><a href="%1$s" target="_blank">%6$s<i class="dashicons dashicons-thumbs-up"></i></a></button>
								<button class="wplp-review-btns wplp-review-already-done-btn" href="%2$s" >%7$s<i class="dashicons dashicons-smiley"></i></button>
							</div>
							<input type="hidden" id="wplp_review_nonce" name="wplp_review_nonce" value="' . esc_attr( wp_create_nonce( 'wplp_review' ) ) . '" />
							</form>
						</div>
						',
						esc_url( 'https://wordpress.org/support/plugin/wplegalpages/reviews/' ),
						esc_url( get_admin_url() . '?already_done=1' ),
						esc_html__( 'Love ', 'wplegalpages' ),
						esc_html__( ' Share your experience! Leave a review on WordPress.org and let others know how WP Legal Pages helps you in generating legal pages like privacy policy and other 25+ legal policies.', 'wplegalpages' ),
						esc_html__( 'Dismiss', 'wplegalpages' ),
						esc_html__( 'Rate Us', 'wplegalpages' ),
						esc_html__( 'I already did', 'wplegalpages' )
					);
				}
				break;
			case '2':
				break;
			default:
				break;
		}
	}
	/**
	 * Function to check the user's input on gdpr review notice.
	 *
	 * @return void
	 */

	public function wplp_review_already_done() {
		$dnd = '';
		if ( isset( $_POST['wplp_review_nonce'] ) && check_admin_referer( 'wplp_review', 'wplp_review_nonce' ) ) {
			if ( isset( $_GET['already_done'] ) && ! empty( $_GET['already_done'] ) ) {
				$dnd = sanitize_text_field( wp_unslash( $_GET['already_done'] ) );
			}
			if ( '1' === $dnd ) {
				update_option( 'wplp_review_pending', '2', true );
			}
		}
	}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */
			wp_register_script( $this->plugin_name . '-tooltip', plugin_dir_url( __FILE__ ) . 'js/tooltip' . WPLPP_SUFFIX . '.js', array(), $this->version, true );
			wp_register_script( $this->plugin_name . '-vue-js', plugin_dir_url( __FILE__ ) . 'js/vue/vue-getting-started.js', array( 'jquery' ), $this->version, true );
			wp_register_script( $this->plugin_name . '-vue', plugin_dir_url( __FILE__ ) . 'js/vue/vue.min.js', array(), $this->version, true );
			wp_register_script( $this->plugin_name . '-main', plugin_dir_url( __FILE__ ) . 'js/vue/wplegalpages-admin-main.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-vue-mascot', plugin_dir_url( __FILE__ ) . 'js/vue/vue-mascot.js', array( 'jquery' ), $this->version, true );
			wp_register_script( $this->plugin_name . '-wizard-js', plugin_dir_url( __FILE__ ) . 'js/wplegalpages-admin-wizard' . WPLPP_SUFFIX . '.js', array( 'jquery' ), $this->version, true );
			wp_register_script( $this->plugin_name . '-select2', plugin_dir_url( __FILE__ ) . 'wizard/libraries/select2/select2.js', array( 'jquery' ), $this->version, false );
		   
		}
		public function wplp_remove_dashboard_submenu() {
			// Define the current version constant
			$current_version = $this->version;
		
			// Target version to hide the submenu
			$target_version = '3.4.1';
		
			// Check if the current version is below the target version
			if (version_compare($current_version, $target_version, '<')) {
				// Remove the 'Dashboard' submenu
				remove_submenu_page('wp-legal-pages', 'wplp-dashboard');
				remove_submenu_page('wp-legal-pages', 'wplp-dashboard#help-page');
			}
		}
		/**
		 * This function is provided for WordPress dashbord menus.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Legal_Pages_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Legal_Pages_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		public function admin_menu() {
			$terms = get_option('lp_accept_terms');

			$installed_plugins = get_plugins();

			$legal_pages_installed     = isset( $installed_plugins['wplegalpages/wplegalpages.php'] ) ? true : false;
			$gdpr_installed     = isset( $installed_plugins['gdpr-cookie-consent/gdpr-cookie-consent.php'] ) ? true : false;
			$plugin_name                   = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
			$is_gdpr_active = is_plugin_active( $plugin_name );
			$plugin_name_lp                   = 'wplegalpages/wplegalpages.php';
			$is_legalpages_active = is_plugin_active( $plugin_name_lp );
			$callback_function = $is_gdpr_active  ? array( $this, 'gdpr_cookie_consent_new_admin_screen' ) : array( $this, 'gdpr_cookie_consent_install_activate_screen' );
			if (empty($GLOBALS['admin_page_hooks']['wp-legal-pages'])) {
				add_menu_page(
				__( 'WP Legal Pages', 'wplegalpages' ), // Page title
				__( 'WP Legal Pages', 'wplegalpages' ), // Menu title
				'manage_options', // Capability
				'wp-legal-pages', // Menu slug
				$callback_function , // Callback function
				'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTIuODMzMTcgOS4wMjYyOUwwLjM1NzQyMiAwLjM2MTgxNkgyLjM1NTc2TDMuNzg3OTggNi4zODIwOUgzLjg1OThMNS40Mzk4OSAwLjM2MTgxNkg3LjE1MDk1TDguNzI2ODEgNi4zOTQ3OUg4LjgwMjg2TDEwLjIzNTEgMC4zNjE4MTZIMTIuMjMzNEw5Ljc1NzY3IDkuMDI2MjlINy45NzQ3OUw2LjMyNzEgMy4zNjEzOEg2LjI1OTUxTDQuNjE2MDUgOS4wMjYyOUgyLjgzMzE3WiIgZmlsbD0iIzlDQTJBNyIvPgo8cGF0aCBkPSJNMTMuMTE1MiA5LjAwOTY4VjAuMzQ1MjE1SDE2LjUyODlDMTcuMTg1MiAwLjM0NTIxNSAxNy43NDQzIDAuNDcwNzI5IDE4LjIwNjIgMC43MjE3NDNDMTguNjY4MSAwLjk2OTk1MSAxOS4wMjAxIDEuMzE1NDUgMTkuMjYyNCAxLjc1ODI3QzE5LjUwNzQgMi4xOTgyNiAxOS42MyAyLjcwNTk1IDE5LjYzIDMuMjgxMzJDMTkuNjMgMy44NTY2OSAxOS41MDYxIDQuMzY0MzggMTkuMjU4MSA0LjgwNDM3QzE5LjAxMDMgNS4yNDQzNiAxOC42NTEyIDUuNTg3MDUgMTguMTgwOCA1LjgzMjQzQzE3LjcxMzIgNi4wNzc4MSAxNy4xNDcxIDYuMjAwNSAxNi40ODI0IDYuMjAwNUgxNC4zMDY3VjQuNzMyNDVIMTYuMTg2N0MxNi41Mzg3IDQuNzMyNDUgMTYuODI4OSA0LjY3MTggMTcuMDU3IDQuNTUwNTJDMTcuMjg4IDQuNDI2NDMgMTcuNDU5OCA0LjI1NTc5IDE3LjU3MjUgNC4wMzg2MUMxNy42ODggMy44MTg2MiAxNy43NDU2IDMuNTY2MTkgMTcuNzQ1NiAzLjI4MTMyQzE3Ljc0NTYgMi45OTM2MyAxNy42ODggMi43NDI2MSAxNy41NzI1IDIuNTI4MjVDMTcuNDU5OCAyLjMxMTA4IDE3LjI4OCAyLjE0MzI2IDE3LjA1NyAyLjAyNDhDMTYuODI2MSAxLjkwMzUzIDE2LjUzMzIgMS44NDI4OCAxNi4xNzgzIDEuODQyODhIMTQuOTQ0NlY5LjAwOTY4SDEzLjExNTJaIiBmaWxsPSIjOUNBMkE3Ii8+CjxwYXRoIGQ9Ik00LjY4MzQ0IDEzLjk2OTRDNS43NTc3MiAxMy45Njk0IDYuNjI4NiAxMy4wOTg1IDYuNjI4NiAxMi4wMjQzQzYuNjI4NiAxMC45NSA1Ljc1NzcyIDEwLjA3OTEgNC42ODM0NCAxMC4wNzkxQzMuNjA5MTYgMTAuMDc5MSAyLjczODI4IDEwLjk1IDIuNzM4MjggMTIuMDI0M0MyLjczODI4IDEzLjA5ODUgMy42MDkxNiAxMy45Njk0IDQuNjgzNDQgMTMuOTY5NFoiIGZpbGw9IiM5Q0EyQTciLz4KPHBhdGggZD0iTTQuNjgzNDQgMTguOTk5N0M1Ljc1NzcyIDE4Ljk5OTcgNi42Mjg2IDE4LjEyODggNi42Mjg2IDE3LjA1NDVDNi42Mjg2IDE1Ljk4MDMgNS43NTc3MiAxNS4xMDk0IDQuNjgzNDQgMTUuMTA5NEMzLjYwOTE2IDE1LjEwOTQgMi43MzgyOCAxNS45ODAzIDIuNzM4MjggMTcuMDU0NUMyLjczODI4IDE4LjEyODggMy42MDkxNiAxOC45OTk3IDQuNjgzNDQgMTguOTk5N1oiIGZpbGw9IiM5Q0EyQTciLz4KPHBhdGggZD0iTTEzLjEyODkgMTguOTU0VjEwLjI4OTZIMTYuNTQyNkMxNy4xOTg5IDEwLjI4OTYgMTcuNzU4IDEwLjQxNSAxOC4yMTk5IDEwLjY2NkMxOC42ODE4IDEwLjkxNDIgMTkuMDMzOCAxMS4yNTk3IDE5LjI3NiAxMS43MDI1QzE5LjUyMTEgMTIuMTQyNSAxOS42NDM2IDEyLjY1MDIgMTkuNjQzNiAxMy4yMjU2QzE5LjY0MzYgMTMuODAxIDE5LjUxOTcgMTQuMzA4NyAxOS4yNzE5IDE0Ljc0ODdDMTkuMDI0IDE1LjE4ODYgMTguNjY0OSAxNS41MzEzIDE4LjE5NDUgMTUuNzc2N0MxNy43MjcgMTYuMDIyMSAxNy4xNjA5IDE2LjE0NDggMTYuNDk2MSAxNi4xNDQ4SDE0LjMyMDRWMTQuNjc2OEgxNi4yMDA0QzE2LjU1MjUgMTQuNjc2OCAxNi44NDI2IDE0LjYxNjEgMTcuMDcwNyAxNC40OTQ4QzE3LjMwMTcgMTQuMzcwNyAxNy40NzM1IDE0LjIwMDEgMTcuNTg2MSAxMy45ODI5QzE3LjcwMTcgMTMuNzYyOSAxNy43NTkzIDEzLjUxMDUgMTcuNzU5MyAxMy4yMjU2QzE3Ljc1OTMgMTIuOTM4IDE3LjcwMTcgMTIuNjg2OSAxNy41ODYxIDEyLjQ3MjVDMTcuNDczNSAxMi4yNTUzIDE3LjMwMTcgMTIuMDg3NiAxNy4wNzA3IDExLjk2OTFDMTYuODM5OCAxMS44NDc4IDE2LjU0NjggMTEuNzg3MiAxNi4xOTIgMTEuNzg3MkgxNC45NTgzVjE4Ljk1NEgxMy4xMjg5WiIgZmlsbD0iIzlDQTJBNyIvPgo8cGF0aCBkPSJNOC4wMjM1NyAxOC45NTJMOC4wMjM0NCAxMC4wNzkxSDkuODUyOEw5Ljg1MjkyIDE3LjIzNDRIMTIuMjA1NVYxOC45NTJIOC4wMjM1N1oiIGZpbGw9IiM5Q0EyQTciLz4KPC9zdmc+Cg==', // Icon URL (choose an icon from the WordPress Dashicons library)
				67 // Position
				);
			}
			
			if($is_legalpages_active && $is_gdpr_active){
				add_submenu_page(
					'wp-legal-pages', // Parent slug (same as main menu slug)
					__( 'Dashboard', 'wplegalpages' ),  // Page title
					__( 'Dashboard', 'wplegalpages' ),     // Dashboard page title
					'manage_options',   // Capability
					'wplp-dashboard', // Menu slug
					array( $this, 'conditional_dashboard_callback'),
					90
				);
			}
			if(!$gdpr_installed || ($gdpr_installed && !$is_gdpr_active)){
				add_submenu_page(
					'wp-legal-pages', // Parent slug (same as main menu slug)
					__( 'Dashboard', 'wplegalpages' ),  // Page title
					__( 'Dashboard', 'wplegalpages' ),     // Dashboard page title
					'manage_options',   // Capability
					'wplp-dashboard', // Menu slug
					array( $this, 'wp_legalpages_new_admin_screen_dashboard' ), // Callback function
					90
				);
			}
			// Add the "WPLegalPages" sub-menu under "WP Legal Pages"
			add_submenu_page(
				'wp-legal-pages', // Parent slug (same as main menu slug)
				__( 'WPLegalPages', 'wplegalpages' ), // Page title
				__( 'Legal Pages', 'wplegalpages' ), // Menu title
				'manage_options', // Capability
				'legal-pages', // Menu slug
				array( $this, 'wp_legalpages_new_admin_screen' ), // Callback function
				85
			);
			if($is_legalpages_active && $is_gdpr_active){
				add_submenu_page(
					'wp-legal-pages', // Parent slug
					__('WP Cookie Consent', 'wplegalpages'),  // Page title
					__('Cookie Consent', 'wplegalpages'),    // Menu title
					'manage_options',   // Capability
					'gdpr-cookie-consent', // Menu slug
					function() {
						// Call the function via the hook
						do_action('gdpr_cookie_consent_admin_screen');
					}, // Custom function to call the callback
					90
				);
			}
			if(!$gdpr_installed || ($gdpr_installed && !$is_gdpr_active)){
				add_submenu_page(
					'wp-legal-pages', // Parent slug (same as main menu slug)
					__( 'WP Cookie Consent', 'wplegalpages' ),  // Page title
					__( 'Cookie Consent', 'wplegalpages' ),     // Menu title
					'manage_options',   // Capability
					'gdpr-cookie-consent', // Menu slug
					array( $this, 'gdpr_cookie_consent_install_activate_screen' ), // Callback function
				);
			}
			if($is_legalpages_active && $is_gdpr_active){
				add_submenu_page(
					'wp-legal-pages', // Parent slug (same as main menu slug)
					__( 'Help', 'wplegalpages' ),  // Page title
					__( 'Help', 'wplegalpages' ),     // Dashboard page title
					'manage_options',   // Capability
					'wplp-dashboard#help-page', // Menu slug
					array( $this, 'conditional_help_callback' ), // Callback function
					91
				);
			}
			if(!$gdpr_installed || ($gdpr_installed && !$is_gdpr_active)){
				add_submenu_page(
					'wp-legal-pages', // Parent slug (same as main menu slug)
					__( 'Help', 'wplegalpages' ),  // Page title
					__( 'Help', 'wplegalpages' ),     // Dashboard page title
					'manage_options',   // Capability
					'wplp-dashboard#help-page', // Menu slug
					array( $this, 'help_page_content' ), // Callback function
					91
				);
			}
			if($legal_pages_installed || $gdpr_installed){
				remove_submenu_page('wp-legal-pages', 'wp-legal-pages');
			}
			if ( '1' === $terms ) {
				add_dashboard_page( '', '', 'manage_options', 'wplegal-wizard', '' );
				if ( version_compare( $this->version, '2.7.0', '<' ) ) {
					add_submenu_page( 'legal-pages', __( 'Cookie Bar', 'wplegalpages' ), __( 'Cookie Bar', 'wplegalpages' ), 'manage_options', 'lp-eu-cookies', array( $this, 'update_eu_cookies' ) );
				}
			}
		}
		function conditional_dashboard_callback() {
			// Check if the action hook exists
			if ( has_action( 'gdpr_cookie_consent_new_admin_dashboard_screen' ) ) {
				// Trigger the action
				do_action( 'gdpr_cookie_consent_new_admin_dashboard_screen' );
			} else {
				// Default callback content
				$this->wp_legalpages_new_admin_screen_dashboard();
			}
		}
		function conditional_help_callback() {
			// Check if the action hook exists
			if ( has_action( 'gdpr_help_page_content' ) ) {
				// Trigger the action
				do_action( 'gdpr_help_page_content' );
			} else {
				// Default callback content
				$this->help_page_content();
			}
		}
		
		/**
		 * Admin init for database update.
		 *
		 * @since 2.3.5
		 */
		public function wplegal_admin_init() {

			if ( ! get_option( 'wplegalpages_free_version' ) || version_compare( get_option( 'wplegalpages_free_version' ), $this->version ) !== 0 ) {
				update_option( 'wplegalpages_free_version', $this->version );
			}

			$lp_templates_updated               = get_option( '_lp_templates_updated' );
			$lp_effective_date_template_updated = get_option( '_lp_effective_date_templates_updated' );
			if ( '1' !== $lp_templates_updated || '1' !== $lp_effective_date_template_updated ) {
				global $wpdb;
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				if ( is_multisite() ) {
					// Get all blogs in the network and activate plugin on each one.
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching	
					$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs ); // db call ok; no-cache ok.
					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						$this->wplegal_admin_init_install_db();
						restore_current_blog();
					}
				} else {
					$this->wplegal_admin_init_install_db();
				}
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching	
			}
		}

		/**
		 * Returns plugin action links.
		 *
		 * @param array $links Plugin action links.
		 * @return array
		 */
		public function wplegal_plugin_action_links( $links ) {
			$current_url = get_site_url();
			$current_url = $current_url . '/wp-admin/index.php?page=wplegal-wizard#';

			$lp_pro_installed = get_option( '_lp_pro_installed' );
			if ( '1' !== $lp_pro_installed ) {
				$links = array_merge(
					array(
						'<a href="' . esc_url( 'https://app.wplegalpages.com/pricing/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=upgrade' ) . '" target="_blank" rel="noopener noreferrer"><strong style="color: #11967A; display: inline;">' . __( 'Upgrade to Pro', 'wplegalpages' ) . '</strong></a>',
					),
					$links
				);
			}
			$links = array_merge(
				array(
					'<a href="' . esc_url( $current_url ) . '" target="_self" rel="noopener noreferrer"><strong style="color: #11967A; display: inline;">' . __( 'Create Legal Page', 'gdpr-cookie-consent' ) . '</strong></a>',
				),
				$links
			);
			return $links;
		}

		/**
		 * Update templates on admin init.
		 */
		public function wplegal_admin_init_install_db() {
			delete_option( '_lp_db_updated' );
			delete_option( '_lp_terms_updated' );
			delete_option( '_lp_terms_fr_de_updated' );
			global $wpdb;
			$legal_pages = new WP_Legal_Pages();
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			update_option( '_lp_templates_updated', true );
			update_option( '_lp_effective_date_templates_updated', true );
		}
		/**
		 * Registeres Affiliate disclosure metabox for posts.
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_register_meta_boxes() {
			add_meta_box( 'meta-box-id', esc_attr__( 'Add Affiliate Disclosure', 'wplegalpages' ), array( $this, 'wplegalpages_pro_display_callback' ), 'post' );
		}
		/**
		 * Save post meta data.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_save_meta_box( $post_id ) {
			if ( isset( $_POST['wplegal_custom_metadata'] ) ) {
				check_admin_referer( 'wplegal_save_custom_metabox', 'wplegal_custom_metadata' );
			}
			$legal_content = isset( $_POST['legal_content'] ) ? wp_kses_post( wp_unslash( $_POST['legal_content'] ) ) : '';
			$legal_check   = isset( $_POST['legal_check'] ) ? sanitize_text_field( wp_unslash( $_POST['legal_check'] ) ) : '';
			update_post_meta( $post_id, '_legal_content', $legal_content );
			update_post_meta( $post_id, '_legal_check', $legal_check );
		}
		/**
		 * Affiliate disclosure content for post.
		 *
		 * @param WP_Post $post Post.
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_display_callback( $post ) {
			$legal_check_status = '';
			$legal_content      = '';
			if ( ! empty( $post->ID ) ) {
				$legal_check_status = get_post_meta( $post->ID, '_legal_check', true );
				$legal_content      = get_post_meta( $post->ID, '_legal_content', true );
			}
			wp_nonce_field( 'wplegal_save_custom_metabox', 'wplegal_custom_metadata' );
			if ( '' === $legal_content ) {
				$legal_content = "Disclosure: Some of the links in this post are 'affiliate links.' This means if you click on the link and purchase the item, I will receive an affiliate commission.";
			}

			?>

			<script>
				jQuery(document).ready(function(){

					jQuery("#legal_check").click(function(){

						if(jQuery("#legal_check").is(':checked'))
						{
							jQuery("#content_area_read").hide();
							jQuery("#content_area_edit").show();
						}
						else
						{
							jQuery("#content_area_read").show();
							jQuery("#content_area_edit").hide();
						}

					});
				});
			</script>

			<table>
				<tr>
					<?php if ( 'on' !== $legal_check_status ) { ?>
						<td valign="middle" width="10%" align="center"> <input type="checkbox" name="legal_check" id="legal_check" > </td>
						<td width="90%">
							<div id="content_area_edit" style="display:none;">
								<textarea name="legal_content" cols="80" rows="2" ><?php echo esc_attr( $legal_content ); ?></textarea>
							</div>
							<div id="content_area_read">
								<p><?php echo esc_attr( $legal_content ); ?></p>
							</div>
						</td>
					<?php } else { ?>
						<td valign="middle" width="10%" align="center"> <input type="checkbox" checked name="legal_check" id="legal_check" > </td>
						<td width="90%">
							<div id="content_area_edit">
								<textarea name="legal_content" cols="80" rows="2" ><?php echo esc_attr( $legal_content ); ?></textarea>
							</div>
							<div id="content_area_read" style="display:none;">
								<p><?php echo esc_attr( $legal_content ); ?></p>
							</div>
						</td>
					<?php } ?>

				</tr>
			</table>

			<?php
		}
		/**
		 * Modify post content to include affiliate disclosure.
		 *
		 * @param string $content Post content.
		 *
		 * @return string
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_post_content( $content ) {
			global $post;
			if ( ! empty( $post->ID ) ) {
				$legal_check_status = get_post_meta( $post->ID, '_legal_check', true );
				$meta               = get_post_meta( $post->ID, '_legal_content', true );
				$meta               = "<div id='_affiliate_disclosure'><i>" . $meta . '</i></div>';
				if ( 'on' === $legal_check_status ) {
					$content = $content . $meta;
				}
			}
			return $content;
		}
		/**
		 * Register affiliate disclosure block.
		 */
		public function wplegalpages_pro_register_block_type() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}
			wp_register_script(
				$this->plugin_name . '-block',
				plugin_dir_url( __FILE__ ) . 'js/blocks/wplegalpages-pro-admin-block.js',
				array(
					'jquery',
					'wp-blocks',
					'wp-i18n',
					'wp-editor',
					'wp-element',
					'wp-components',
				),
				$this->version,
				true
			);
			register_block_type(
				'wplegal/affiliate-disclosure-block',
				array(
					'editor_script' => $this->plugin_name . '-block',
				)
			);
		}
		/**
		 * Registers the WP Legal Pages Gutenberg block.
		 *
		 * @return void
		 */
		public function wplegalpages_register_legal_template_block() {
			if ( ! function_exists( 'register_block_type' ) ) {
				return;
			}
			$is_block_enabled = get_option( 'wplegalpages_is_block_enabled', 0 );
			if ( ! $is_block_enabled ) {
				return;
			}
			wp_register_script(
				$this->plugin_name . '-legal-template-block',
				plugin_dir_url( __FILE__ ) . 'js/blocks/wplegalpages-legal-template-block.js',
				array(
					'jquery',
					'wp-blocks',
					'wp-i18n',
					'wp-editor',
					'wp-element',
					'wp-components',
				),
				$this->version,
				true
			);

			wp_localize_script(
				$this->plugin_name . '-legal-template-block',
				'wplp_localize_data',
				array(
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'wplpurl'           => WPL_LITE_PLUGIN_URL,
					'siteurl'           => site_url(),
					'admin_url'         => admin_url(),
					'wplegal_app_url'   => WPLEGAL_APP_URL,
					'_ajax_nonce'       => wp_create_nonce( 'wp-legal-pages' ),
					'all_legal_pages'   => $this->wplegalpages_get_generated_legal_pages(),
					'is_block_enabled'  => $is_block_enabled,
				)
			);

			register_block_type(
				'wplegal/legal-template',
				array(
					'editor_script' => $this->plugin_name . '-legal-template-block',
				)
			);
		}

		/**
		 * Retrieves all published legal pages marked with the 'is_legal' post meta.
		 *
		 * This function queries the database directly to fetch all posts that are published
		 * and have the meta key 'is_legal'. It then strips shortcodes and Gutenberg block 
		 * comments from the post content for clean output.
		 *
		 * @global wpdb $wpdb WordPress database access object.
		 *
		 * @return array List of objects containing the ID, title, and cleaned post content of each legal page.
		 */
		public function wplegalpages_get_generated_legal_pages() {
			global $wpdb;
			$post_tbl     = $wpdb->prefix . 'posts';
			$postmeta_tbl = $wpdb->prefix . 'postmeta';
			$pagesresult  = $wpdb->get_results( $wpdb->prepare( 'SELECT ptbl.ID, ptbl.post_content, ptbl.post_title FROM ' . $post_tbl . ' as ptbl , ' . $postmeta_tbl . ' as pmtbl WHERE ptbl.ID = pmtbl.post_id and ptbl.post_status = %s AND pmtbl.meta_key = %s', array( 'publish', 'is_legal' ) ) );

			foreach ( $pagesresult as &$page ) {
				// Remove shortcodes.
				$page->post_content = strip_shortcodes( $page->post_content );

				// Remove Gutenberg block tags like <!-- wp:paragraph -->
				$page->post_content = preg_replace( '/<!--\s?\/?wp:[^>]+-->/', '', $page->post_content );

				$page->post_content = trim( $page->post_content );
			}
			unset($page);

			return $pagesresult;
		}

		/**
		 * Enqueue admin common style and scripts.
		 */
		public function enqueue_common_style_scripts() {
			wp_enqueue_style( $this->plugin_name . '-admin' );
			wp_enqueue_style( $this->plugin_name . '-bootstrap' );
			wp_enqueue_script( $this->plugin_name . '-tooltip' );
		}

		/**
		 * Callback function for Dashboard page.
		 *
		 * @since 
		 */
		public function gdpr_cookie_consent_install_activate_screen() { 

		$gdpr_install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=gdpr-cookie-consent' ), 'install-plugin_gdpr-cookie-consent' );
		$plugin_name                   = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
		$gdpr_activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_name . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin_name );
		$installed_plugins = get_plugins();
		$is_gdpr_installed     = isset( $installed_plugins['gdpr-cookie-consent/gdpr-cookie-consent.php'] ) ? true : false;
		?>
		<div class="gdpr-install-activate-screen">
			<img id="gdpr-install-activate-img"src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/cookie-consent-install-banner.jpg'; ?>" alt="WP Cookie Consent Logo"><?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
			<div class="lp-popup-container">

				<p class="lp-plugin-install-activation-text"><?php esc_html_e( 'WP Cookie Consent is currently inactive. Please install and activate the plugin to display the cookie banner and collect user consent.', 'wplegalpages' ); ?></p>
				<?php 
				if(!$is_gdpr_installed) { ?>
				<a style="width:27%;" href="<?php echo esc_url($gdpr_install_url); ?>">
					<button id="lp-install-activate-btn"><?php esc_html_e('Install Now','wplegalpages') ?></button>
				</a> 
				<?php }
				else { ?>
					<a style="width:27%;" href="<?php echo esc_url($gdpr_activation_url); ?>">
					<button id="lp-install-activate-btn"><?php esc_html_e('Activate Now','wplegalpages') ?></button>
					</a> 
				<?php } ?>
        </div>
		</div>
		
		<?php   wp_enqueue_style( $this->plugin_name . '-admin' );} 

		/**
		 * Callback function for Dashboard page.
		 *
		 * @since 2.10.0
		 */
		public function wp_legalpages_new_admin_screen() {

			require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';

			// Instantiate a new object of the wplegal_Cookie_Consent_Settings class.
			$this->settings = new WP_Legal_Pages_Settings();

			// Call the is_connected() method from the instantiated object to check if the user is connected.
			$is_user_connected = $this->settings->is_connected();
			$plan_name         = $this->settings->get_plan();

			$pro_is_activated = get_option( '_lp_pro_active' );

			$if_terms_are_accepted = get_option( 'lp_accept_terms' );

			$this->enqueue_common_style_scripts();
			$this->wplegalpages_mascot_enqueue();

			wp_enqueue_script( //phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				'wp-legalpages-admin-revamp',
				WPL_LITE_PLUGIN_URL . 'admin/js/wp-legalpages-admin-revamp.js',
				array( 'jquery' ),
				true
			);

			wp_localize_script(
				'wp-legalpages-admin-revamp',
				'wplp_localize_data',
				array(
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'wplpurl'           => WPL_LITE_PLUGIN_URL,
					'siteurl'           => site_url(),
					'admin_url'         => admin_url(),
					'is_pro_activated'  => $pro_is_activated,
					'lp_terms'          => $if_terms_are_accepted,
					'wplegal_app_url'   => WPLEGAL_APP_URL,
					'is_user_connected' => $is_user_connected,
					'plan_name'         => $plan_name,
					'_ajax_nonce'       => wp_create_nonce( 'wp-legal-pages' ),

				)
			);
			include_once plugin_dir_path( __DIR__ ) . 'admin/partials/wp-legal-pages-main-screen.php';
		}
		public function wp_legalpages_new_admin_screen_dashboard() {

			require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';

			// Instantiate a new object of the wplegal_Cookie_Consent_Settings class.
			$this->settings = new WP_Legal_Pages_Settings();

			// Call the is_connected() method from the instantiated object to check if the user is connected.
			$is_user_connected = $this->settings->is_connected();
			$plan_name         = $this->settings->get_plan();

			$pro_is_activated = get_option( '_lp_pro_active' );

			$if_terms_are_accepted = get_option( 'lp_accept_terms' );

			$this->enqueue_common_style_scripts();
			$this->wplegalpages_mascot_enqueue();

			wp_enqueue_script( //phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				'wp-legalpages-admin-revamp',
				WPL_LITE_PLUGIN_URL . 'admin/js/wp-legalpages-admin-revamp.js',
				array( 'jquery' ),
				true
			);

			wp_localize_script(
				'wp-legalpages-admin-revamp',
				'wplp_localize_data',
				array(
					'ajaxurl'           => admin_url( 'admin-ajax.php' ),
					'wplpurl'           => WPL_LITE_PLUGIN_URL,
					'siteurl'           => site_url(),
					'admin_url'         => admin_url(),
					'is_pro_activated'  => $pro_is_activated,
					'lp_terms'          => $if_terms_are_accepted,
					'wplegal_app_url'   => WPLEGAL_APP_URL,
					'is_user_connected' => $is_user_connected,
					'plan_name'         => $plan_name,
					'_ajax_nonce'       => wp_create_nonce( 'wp-legal-pages' ),

				)
			);

			include_once plugin_dir_path( __DIR__ ) . 'admin/partials/wp-legal-pages-main-screen-dashboard.php';
		}
		/**
		 * This Callback function for Admin Setting menu for WPLegalpages.
		 */
		public function admin_setting() {
			$this->enqueue_common_style_scripts();
			if ( get_option( 'wplegalpages_pro_version' ) && version_compare( get_option( 'wplegalpages_pro_version' ), '8.2.0' ) < 0 ) {
				include_once plugin_dir_path( __DIR__ ) . 'admin/admin-settings-ver818.php';
			} else {
				$lp_pages = get_posts(
					array(
						'post_type'   => 'page',
						'post_status' => 'publish',
						'numberposts' => -1,
						'orderby'     => 'title',
						'order'       => 'ASC',
						'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query	
							array(
								'key'     => 'is_legal',
								'value'   => 'yes',
								'compare' => '=',
							),
						),
					)
				);
				$options  = array();
				if ( $lp_pages ) {
					foreach ( $lp_pages as $lp_page ) {

						if ( html_entity_decode( $lp_page->post_title ) !== $lp_page->post_title ) {
							$lp_page->post_title = html_entity_decode( $lp_page->post_title );
							wp_update_post( $lp_page );
						}
						$page_array = array(
							'label' => $lp_page->post_title,
							'code'  => $lp_page->ID,
						);
						array_push( $options, $page_array );
					}
				}
				$lp_options               = get_option( 'lp_general' );
				$lp_banner_options        = get_option( 'lp_banner_options' );
				$lp_footer_options        = get_option( 'lp_footer_options' );
				$lp_eu_theme_css          = get_option( 'lp_eu_theme_css' );
				$lp_eu_get_visibility     = get_option( 'lp_eu_cookie_enable' );
				$lp_eu_title              = get_option( 'lp_eu_cookie_title' );
				$lp_eu_message            = get_option( 'lp_eu_cookie_message' );
				$lp_eu_box_color          = get_option( 'lp_eu_box_color' );
				$lp_eu_button_color       = get_option( 'lp_eu_button_color' );
				$lp_eu_button_text_color  = get_option( 'lp_eu_button_text_color' );
				$lp_eu_text_color         = get_option( 'lp_eu_text_color' );
				$lp_eu_button_text        = get_option( 'lp_eu_button_text' );
				$lp_eu_link_text          = get_option( 'lp_eu_link_text' );
				$lp_eu_link_url           = get_option( 'lp_eu_link_url' );
				$lp_eu_text_size          = get_option( 'lp_eu_text_size' );
				$lp_eu_link_color         = get_option( 'lp_eu_link_color' );
				$ask_for_usage_optin      = get_option( 'wplegalpages-ask-for-usage-optin' );
				$cookie_text_size_options = array();
				for ( $i = 10; $i < 32; $i += 2 ) {
					array_push( $cookie_text_size_options, $i );
				}
				if ( ! $lp_eu_get_visibility ) {
					$lp_eu_get_visibility = 'off';
				}
				$options_object = array(
					'lp_options'               => $lp_options,
					'page_options'             => $options,
					'lp_footer_options'        => $lp_footer_options,
					'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
					'number_of_days'           => range( 1, 30 ),
					'lp_banner_options'        => $lp_banner_options,
					'font_size_options'        => range( 8, 72 ),
					'lp_eu_theme_css'          => $lp_eu_theme_css,
					'lp_eu_cookie_enable'      => $lp_eu_get_visibility,
					'lp_eu_cookie_title'       => $lp_eu_title,
					'lp_eu_cookie_message'     => $lp_eu_message,
					'lp_eu_box_color'          => $lp_eu_box_color,
					'lp_eu_button_color'       => $lp_eu_box_color,
					'lp_eu_button_text_color'  => $lp_eu_button_text_color,
					'lp_eu_text_color'         => $lp_eu_text_color,
					'lp_eu_button_text'        => $lp_eu_button_text,
					'lp_eu_link_text'          => $lp_eu_link_text,
					'lp_eu_link_url'           => $lp_eu_link_url,
					'lp_eu_text_size'          => $lp_eu_text_size,
					'lp_eu_link_color'         => $lp_eu_link_color,
					'cookie_text_size_options' => $cookie_text_size_options,
					'ask_for_usage_optin'      => $ask_for_usage_optin,
				);

				$options_object = apply_filters( 'wplegalpages_compliances_options', $options_object );
				wp_localize_script( $this->plugin_name . '-main', 'obj', $options_object );
				wp_enqueue_script( $this->plugin_name . '-main' );
				include_once plugin_dir_path( __DIR__ ) . 'admin/wplegalpages-admin-settings.php';
				$this->wplegalpages_mascot_enqueue();
			}
		}


		/**
		 * This Callback function for Show Page menu for WPLegalpages.
		 */
		public function show_pages() {
			$activated = apply_filters( 'wplegal_check_license_status', true );
			if ( $activated ) {
				$this->enqueue_common_style_scripts();
				include_once plugin_dir_path( __DIR__ ) . 'admin/show-pages.php';
				$this->wplegalpages_mascot_enqueue();
			}
		}

		/**
		 * This Callback function for Getting Started menu for WPLegalpages.
		 */
		public function getting_started() {
			wp_enqueue_style( $this->plugin_name . '-admin' );
			wp_enqueue_script( $this->plugin_name . '-tooltip' );
			include_once plugin_dir_path( __DIR__ ) . 'admin/getting-started.php';
			$this->wplegalpages_mascot_enqueue();
		}

		/**
		 * This Callback function for Help Page menu for WPLegalpages.
		 */
		public function help_page_content() {
			wp_enqueue_style( $this->plugin_name . '-admin' );
			wp_enqueue_script( $this->plugin_name . '-tooltip' );
			include_once plugin_dir_path( __DIR__ ) . 'admin/help-page.php';
			$this->wplegalpages_mascot_enqueue();
		}

		/**
		 * This Callback function for Getting Started menu for WPLegalpages.
		 */
		public function vue_getting_started() {
			$is_pro = get_option( '_lp_pro_active' );
			$installed_plugins = get_plugins();

			$legal_pages_installed     = isset( $installed_plugins['wplegalpages/wplegalpages.php'] ) ? '1' : '0';
			$gdpr_installed     = isset( $installed_plugins['gdpr-cookie-consent/gdpr-cookie-consent.php'] ) ? '1' : '0';

			$plugin_name                   = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
			$is_gdpr_active = is_plugin_active( $plugin_name );
			$plugin_name_lp                   = 'wplegalpages/wplegalpages.php';
			$is_legalpages_active = is_plugin_active( $plugin_name_lp );
			if ( $is_pro ) {
				$support_url = 'https://club.wpeka.com/my-account/orders/?utm_source=wplegalpages&utm_medium=help-mascot&utm_campaign=link&utm_content=support';
			} else {
				$support_url = 'https://wordpress.org/support/plugin/wplegalpages/?utm_source=wplegalpages&utm_medium=help-mascot&utm_campaign=link&utm_content=forums';
			}
			wp_enqueue_style( $this->plugin_name . '-vue-style' );
			wp_enqueue_script( $this->plugin_name . '-vue' );
			wp_enqueue_script( $this->plugin_name . '-vue-js' );
			wp_localize_script(
				$this->plugin_name . '-vue-js',
				'obj',
				array(
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'           => wp_create_nonce( 'admin-ajax-nonce' ),
					'is_pro'               => $is_pro,
					'video_url'            => 'https://www.youtube.com/embed/WkTqA60bGRg?si=aty3JoOdjsyWsetx',
					'image_url'            => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/',
					'welcome_text'         => __( 'Welcome to WPLP Compliance Platform!', 'wplegalpages' ),
					'welcome_subtext'      => __( 'Privacy Policy Generator For WordPress', 'wplegalpages' ),
					'welcome_description'  => __( 'Thank you for choosing WP Legal Pages plugin - A robust plugin for hassle-free legal compliance. ', 'wplegalpages' ),
					'legal_pages_installed' => $legal_pages_installed,
					'gdpr_installed'		=> $gdpr_installed,
					'is_gdpr_active'		=> $is_gdpr_active,
					'install_gdpr_text'		   => __('Install WP Cookie Consent!', 'wplegalpages'),
					'install_gdpr_subtext' => __('Seamlessly add a cookie consent banner to your WordPress website.', 'wplegalpages'),
					'install_gdpr_btn'	   => __('Install Now', 'wplegalpages'),
					'create_gdpr'         => __( 'Your Site\'s Cookie Banner', 'wplegalpages' ),
					'create_gdpr_subtext' => __( 'The banner currently displayed on your website.', 'wplegalpages' ),
					'create_legal'         => __( 'Create Your Legal Page', 'wplegalpages' ),
					'create_legal_subtext' => __( 'Generate your personalized legal policy page for enhanced protection.', 'wplegalpages' ),
					'quick_links_text'     => __( 'See Quick Links', 'wplegalpages' ),
					'link_title'           => __( 'Create Page', 'wplegalpages' ),
					'gdpr_link_title' 	   => __( 'Configure Banner', 'wplegalpages'),
					'create_legal_url'     => admin_url( 'index.php?page=wplegal-wizard#/' ),
					'create_gdpr_url' 	   => admin_url('admin.php?page=gdpr-cookie-consent#cookie_settings'),
					'feature_heading'      => __( 'WP Legal Pages Features', 'wplegalpages' ),
					'feature_description'  => __( 'Choose WP Legal Pages for seamless legal compliance.', 'wplegalpages' ),
					'feature_button'       => __( 'Upgrade Now', 'wplegalpages' ),
					'overlay'              => __( 'true', 'wplegalpages' ),
					'terms'                => array(
						'text'        => sprintf(
							/* translators: %s: Terms of use link */
							esc_html__( 'WPLegalPages is a privacy policy and terms & conditions generator for WordPress. With just a few clicks you can generate %s for your WordPress website.', 'wplegalpages' ),
							sprintf(
								/* translators: %s: Terms of use link, %s Text */
								'<a href="%s" target="_blank" style="color:#0A6CD0;">%s</a>',
								esc_url( 'https://club.wpeka.com/product/wplegalpages/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=getting-started&utm_content=25-policy-pages#wplegalpages-policy-templates' ),
								__( '25+ policy pages', 'wplegalpages' )
							)
						),
						'heading'     => __( 'Terms and Conditions', 'wplegalpages' ),
						'image'       => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/terms.svg',
						'subtext'     => __( 'These policy pages are vetted by experts and are constantly updated to keep up with the latest regulations such as GDPR, CCPA, CalOPPA and many others.', 'wplegalpages' ),
						'button_text' => __( 'Accept', 'wplegalpages' ),
						'input_text'  => sprintf(
							/* translators: %s: Terms of use link, %s Text */
							esc_html__( 'By using WPLegalPages, you accept the %s.', 'wplegalpages' ),
							sprintf(
								/* translators: %s: Terms of use link */
								'<a href="%s" target="_blank" style="color:#0A6CD0;">%s</a>',
								esc_url( 'https://wplegalpages.com/product-terms-of-use/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=getting-started&utm_content=terms-of-use' ),
								__( 'terms of use', 'wplegalpages' )
							)
						),
					),
					'new_feature'          => array(
						'powerful_yet_simple'  => array(
							'title'       => __( 'Powerful yet simple', 'wplegalpages' ),
							'description' => __( 'Add 25+ legal policy pages to your WordPress website in less than 5 minutes.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/powerful_yet_simple.svg',
							'alt_text'	  => '25+ legal policy pages icon',
						),
						'pre_built_template'   => array(
							'title'       => __( 'Pre-built templates', 'wplegalpages' ),
							'description' => __( 'Choose from 25+ lawyer approved, legal policy pages from GDPR policies to affiliate disclosures.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/pre_built_template.svg',
							'alt_text'	  => 'Pre-built templates icon',
						),
						'editable_templates'   => array(
							'title'       => __( 'Editable templates', 'wplegalpages' ),
							'description' => __( 'Edit or create your own legal policy templates using the WYSIWYG WordPress editor.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/editable_templates.svg',
							'alt_text'	  => 'Editable templates icon',
						),
						'gdpr_compliance'      => array(
							'title'       => __( 'GDPR compliance', 'wplegalpages' ),
							'description' => __( 'Easy to use shortcodes to display business information in legal policy pages.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/gdpr_compliance.svg',
							'alt_text'	  => 'GDPR compliance icon',
						),
						'forced_consent'       => array(
							'title'       => __( 'Forced consent', 'wplegalpages' ),
							'description' => __( 'Force website visitors to agree to your Terms, Privacy Policy, etc using post / page lock down features.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/forced_consent.svg',
							'alt_text'	  => 'Forced consent icon',
						),
						'easy_shortcodes'      => array(
							'title'       => __( 'Easy shortcodes', 'wplegalpages' ),
							'description' => __( 'Easy to use shortcodes to display business information in legal policy pages.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/easy_shortcodes.svg',
							'alt_text'	  => 'Easy shortcodes icon',
						),
						'easy_install'         => array(
							'title'       => __( 'Easy to install', 'wplegalpages' ),
							'description' => __( 'WP Legal Pages is super-easy to install. Download & install takes less than 2 minutes.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/easy_to_install.svg',
							'alt_text'	  => 'Easy to install icon',
						),
						'helpful_docs'         => array(
							'title'       => __( 'Helpful docs & guides', 'wplegalpages' ),
							'description' => __( 'Even if you get stuck using WP Legal Pages, you can use our easy to follow docs & guides.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/helpful_docs.svg',
							'alt_text'	  => 'Helpful docs & guides icon',
						),
						'multilingual_support' => array(
							'title'       => __( 'Multilingual support', 'wplegalpages' ),
							'description' => __( 'Supports multi-language translations for English, French, Spanish, German, Italian, Portuguese.', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/multilingual_support.svg',
							'alt_text'	  => 'Multilingual support icon',
						),

					),
					'quick_link'           => array(
						'help_center'  => array(
							'title'       => __( 'Help Center', 'wplegalpages' ),
							'description' => __( 'Read the documentation to find answers to your questions.', 'wplegalpages' ),
							'link'        => 'https://club.wpeka.com/docs/wp-legal-pages',
							'link_name'   => __( 'Learn More', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/help_center.svg',
							'alt_text'	  => "Help Center Icon",
						),
						'video_guides' => array(
							'title'       => __( 'Video Guides', 'wplegalpages' ),
							'description' => __( 'Explore video tutorials for insights on WP Legal Pages functionality.', 'wplegalpages' ),
							'link'        => 'https://club.wpeka.com/docs/wp-legal-pages/video-guides/video-guides',
							'link_name'   => __( 'Watch Now', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/video_guides.svg',
							'alt_text'	  => 'Video Guides Icon',
						),
						'faq_answers'  => array(
							'title'       => __( 'FAQ with Answers', 'wplegalpages' ),
							'description' => __( 'Find answers to some of the most commonly asked questions.', 'wplegalpages' ),
							'link'        => 'https://club.wpeka.com/docs/wp-legal-pages/faqs',
							'link_name'   => __( 'Find Out', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/faq_answers.svg',
							'alt_text'	  => 'FAQ with Answers Icon',
						),
						'settings'     => array(
							'title'       => __( 'Settings', 'wplegalpages' ),
							'description' => __( 'Take a couple of minutes to set up your business info before generating pages.', 'wplegalpages' ),
							'link'        => admin_url( 'admin.php?page=legal-pages#settings' ),
							'link_name'   => __( 'Go To Settings', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/settings.svg',
							'alt_text'	  => 'Settings Icon',
						),
						'feedback'     => array(
							'title'       => __( 'Feedback', 'wplegalpages' ),
							'description' => __( 'Enjoy our WordPress plugin? Share your feedback!', 'wplegalpages' ),
							'link'        => 'https://club.wpeka.com/contact',
							'link_name'   => __( 'Find Out', 'wplegalpages' ),
							'image_src'   => WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/feedback.svg',
							'alt_text'	  => 'Feedback Icon',
						),
					),

				)
			);

			?>
			<div id="gettingstartedapp"></div>
			<div id="wplegal-mascot-app"></div>
			<?php
			$this->wplegalpages_mascot_enqueue();
		}

		/**
		 * Function to enqueue mascot files
		 */
		public static function wplegalpages_mascot_enqueue() {
			$settings = new WP_Legal_Pages_Settings();

			$is_user_connected = $settings->is_connected();
			$api_user_plan = $settings->get_plan();

			$is_pro = get_option( '_lp_pro_active' );
			if ( $api_user_plan !== "free" ) {
				$support_url = 'https://club.wpeka.com/my-account/orders/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=help-mascot&utm_content=support';
			} else {
				$support_url = 'https://wordpress.org/support/plugin/wplegalpages/?utm_source=wplegalpages&utm_medium=help-mascot&utm_campaign=link&utm_content=forums';
			}
			wp_localize_script(
				self::$wplp_plugin_name . '-vue-mascot',
				'data',
				array(
					'menu_items'       => array(
						'support_text'       => __( 'Support', 'wplegalpages' ),
						'support_url'        => $support_url,
						'documentation_text' => __( 'Documentation', 'wplegalpages' ),
						'documentation_url'  => 'https://wplegalpages.com/docs/wp-legal-pages/',
						'faq_text'           => __( 'FAQ', 'wplegalpages' ),
						'faq_url'            => 'https://wplegalpages.com/docs/wp-legal-pages/faqs/',
						'upgrade_text'       => __( 'Upgrade to Pro &raquo;', 'wplegalpages' ),
						'upgrade_url'        => 'https://club.wpeka.com/product/wplegalpages/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=help-mascot&utm_content=upgrade-to-pro',
					),
					'is_user_connected' => $is_user_connected,
					'api_user_plan' => $api_user_plan,
					'quick_links_text' => __( 'See Quick Links', 'wplegalpages' ),
				)
			);
			wp_enqueue_style( self::$wplp_plugin_name . '-vue-style' );
			wp_enqueue_script( self::$wplp_plugin_name . '-vue' );
			wp_enqueue_script( self::$wplp_plugin_name . '-vue-mascot' );
		}

		/**
		 * This Callback function for EU_Cookies Page menu for WPLegalpages.
		 */
		public function update_eu_cookies() {
			$activated = apply_filters( 'wplegal_check_license_status', true );
			if ( $activated ) {
				$this->enqueue_common_style_scripts();
				include_once 'update-eu-cookies.php';
			}
		}

		/**
		 * Accpet terms.
		 */
		public function wplegal_accept_terms() {
			// Check nonce.
			check_admin_referer( 'lp-accept-terms' );
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			update_option( 'lp_accept_terms', '1' );
			add_submenu_page( 'legal-pages', __( 'Getting Started', 'wplegalpages' ), __( 'Getting Started', 'wplegalpages' ), 'manage_options', 'getting-started', array( $this, 'vue_getting_started' ) );
			wp_send_json_success( array( 'terms_accepted' => true ) );
		}

		/**
		 * Returns whether terms accepts or not.
		 */
		public function wplegal_get_accept_terms() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$result = array(
				'success' => false,
				'data'    => false,
			);
			if ( isset( $_GET['action'] ) ) {
				check_ajax_referer( 'admin-ajax-nonce', 'nonce' );
				$result['success'] = true;
				$result['data']    = get_option( 'lp_accept_terms' );
			}
			return wp_send_json( $result );
		}

		/**
		 * Accept terms.
		 */
		public function wplegal_save_accept_terms() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$result = array(
				'success' => false,
				'data'    => false,
			);
			if ( isset( $_POST['action'] ) ) {
				check_ajax_referer( 'admin-ajax-nonce', 'nonce' );
				if ( isset( $_POST['data']['lp_accept_terms'] ) ) {
					$lp_accept_terms = sanitize_text_field( wp_unslash( $_POST['data']['lp_accept_terms'] ) );
					update_option( 'lp_accept_terms', $lp_accept_terms );
					add_submenu_page( 'legal-pages', __( 'Getting Started', 'wplegalpages' ), __( 'Getting Started', 'wplegalpages' ), 'manage_options', 'getting-started', array( $this, 'vue_getting_started' ) );
					$result['success'] = true;
					$result['data']    = get_option( 'lp_accept_terms' );
				}
			}
			return wp_send_json( $result );
		}

		/**
		 * Add menu object to the theme menu screen.
		 *
		 * @param Object $object Menu object.
		 * @return mixed
		 */
		public function wplegalpages_add_menu_meta_box( $object ) {
			add_meta_box( 'wplegalpages-menu-metabox', __( 'WPLegalPages', 'wplegalpages' ), array( $this, 'wplegalpages_menu_meta_box' ), 'nav-menus', 'side', 'low' );
			return $object;
		}

		/**
		 * WPLegalPages hidden metaboxes
		 */
		public function wplegalpages_hidden_meta_boxes() {
			$hidden_meta_boxes = get_user_option( 'metaboxhidden_nav-menus' );
			if ( is_array( $hidden_meta_boxes ) ) {
				$key = array_search( 'wplegalpages-menu-metabox', $hidden_meta_boxes, true );
				if ( false !== $key ) {
					unset( $hidden_meta_boxes[ $key ] );
				}
			}
			$user = wp_get_current_user();
			update_user_option( $user->ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes, true );
		}

		/**
		 * WPLegalPages Menu items on theme menu screen.
		 */
		public function wplegalpages_menu_meta_box() {

			global $_nav_menu_placeholder, $nav_menu_selected_id;

			$post_type_name = 'wplegalpages';
			$post_type      = 'page';
			$tab_name       = $post_type_name . '-tab';

			// Paginate browsing for large numbers of post objects.
			$per_page = 50;
			// the phpcs ignore comment is added after referring WordPress core code.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended	
			$pagenum = isset( $_REQUEST[ $tab_name ] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1; // phpcs:ignore input var ok, CSRF ok, sanitization ok.
			// phpcs:enable WordPress.Security.NonceVerification.Recommended	
			$offset  = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;

			$args = array(
				'offset'                 => $offset,
				'order'                  => 'ASC',
				'orderby'                => 'title',
				'posts_per_page'         => $per_page,
				'post_type'              => $post_type,
				'suppress_filters'       => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'post_status'            => 'publish',
				'meta_key'               => 'is_legal', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => 'yes', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value	
			);

			/*
			 * If we're dealing with pages, let's prioritize the Front Page,
			 * Posts Page and Privacy Policy Page at the top of the list.
			 */
			$important_pages = array();
			if ( 'page' === $post_type_name ) {
				$suppress_page_ids = array();

				// Insert Front Page or custom Home link.
				$front_page = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;

				$front_page_obj = null;
				if ( ! empty( $front_page ) ) {
					$front_page_obj                = get_post( $front_page );
					$front_page_obj->front_or_home = true;

					$important_pages[]   = $front_page_obj;
					$suppress_page_ids[] = $front_page_obj->ID;
				} else {
					// phpcs comment is added after referring WordPress core code.
					$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? (int) $_nav_menu_placeholder - 1 : -1; // phpcs:ignore
					$front_page_obj        = (object) array(
						'front_or_home' => true,
						'ID'            => 0,
						'object_id'     => $_nav_menu_placeholder,
						'post_content'  => '',
						'post_excerpt'  => '',
						'post_parent'   => '',
						'post_title'    => _x( 'Home', 'nav menu home label', 'wplegalpages' ),
						'post_type'     => 'nav_menu_item',
						'type'          => 'custom',
						'url'           => home_url( '/' ),
					);

					$important_pages[] = $front_page_obj;
				}

				// Insert Posts Page.
				$posts_page = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_for_posts' ) : 0;

				if ( ! empty( $posts_page ) ) {
					$posts_page_obj             = get_post( $posts_page );
					$posts_page_obj->posts_page = true;

					$important_pages[]   = $posts_page_obj;
					$suppress_page_ids[] = $posts_page_obj->ID;
				}

				// Insert Privacy Policy Page.
				$privacy_policy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

				if ( ! empty( $privacy_policy_page_id ) ) {
					$privacy_policy_page = get_post( $privacy_policy_page_id );
					if ( $privacy_policy_page instanceof WP_Post && 'publish' === $privacy_policy_page->post_status ) {
						$privacy_policy_page->privacy_policy_page = true;

						$important_pages[]   = $privacy_policy_page;
						$suppress_page_ids[] = $privacy_policy_page->ID;
					}
				}

				// Add suppression array to arguments for WP_Query.
				if ( ! empty( $suppress_page_ids ) ) {
					$args['post__not_in'] = $suppress_page_ids; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				}
			}

			// @todo Transient caching of these results with proper invalidation on updating of a post of this type.
			$get_posts = new WP_Query();
			$posts     = $get_posts->query( $args );

			// Only suppress and insert when more than just suppression pages available.
			if ( ! $get_posts->post_count ) {
				if ( ! empty( $suppress_page_ids ) ) {
					unset( $args['post__not_in'] );
					$get_posts = new WP_Query();
					$posts     = $get_posts->query( $args );
				} else {
					echo '<p>' . esc_attr_e( 'No items.', 'wplegalpages' ) . '</p>';
					return;
				}
			} elseif ( ! empty( $important_pages ) ) {
				$posts = array_merge( $important_pages, $posts );
			}

			$num_pages = $get_posts->max_num_pages;

			$page_links = paginate_links(
				array(
					'base'               => add_query_arg(
						array(
							$tab_name     => 'all',
							'paged'       => '%#%',
							'item-type'   => 'post_type',
							'item-object' => $post_type_name,
						)
					),
					'format'             => '',
					'prev_text'          => '<span aria-label="' . esc_attr__( 'Previous page', 'wplegalpages' ) . '">' . __( '&laquo;', 'wplegalpages' ) . '</span>',
					'next_text'          => '<span aria-label="' . esc_attr__( 'Next page', 'wplegalpages' ) . '">' . __( '&raquo;', 'wplegalpages' ) . '</span>',
					'before_page_number' => '<span class="screen-reader-text">' . __( 'Page', 'wplegalpages' ) . '</span> ',
					'total'              => $num_pages,
					'current'            => $pagenum,
				)
			);

			$db_fields = false;
			if ( is_post_type_hierarchical( $post_type_name ) ) {
				$db_fields = array(
					'parent' => 'post_parent',
					'id'     => 'ID',
				);
			}

			$walker = new Walker_Nav_Menu_Checklist( $db_fields );

			$current_tab = 'most-recent';
			// Added below phpcs disable after referring core code.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_REQUEST[ $tab_name ] ) ) {
				$request_tab_name = sanitize_title( wp_unslash( $_REQUEST[ $tab_name ] ) );
				if ( in_array( $request_tab_name, array( 'all', 'search' ), true ) ) {
					$current_tab = $request_tab_name;
				}
			}
			if ( ! empty( $_REQUEST[ 'quick-search-posttype-' . $post_type_name ] ) ) {
				$current_tab = 'search';
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			$removed_args = array(
				'action',
				'customlink-tab',
				'edit-menu-item',
				'menu-item',
				'page-tab',
				'_wpnonce',
			);

			$most_recent_url = '';
			$view_all_url    = '';
			$search_url      = '';
			if ( $nav_menu_selected_id ) {
				$most_recent_url = esc_url( add_query_arg( $tab_name, 'most-recent', remove_query_arg( $removed_args ) ) );
				$view_all_url    = esc_url( add_query_arg( $tab_name, 'all', remove_query_arg( $removed_args ) ) );
				$search_url      = esc_url( add_query_arg( $tab_name, 'search', remove_query_arg( $removed_args ) ) );
			}
			?>
			<div id="posttype-<?php echo esc_attr( $post_type_name ); ?>" class="posttypediv">
				<ul id="posttype-<?php echo esc_attr( $post_type_name ); ?>-tabs" class="posttype-tabs add-menu-item-tabs">
					<li <?php echo ( 'most-recent' === $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link" data-type="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-most-recent" href="<?php echo esc_url( $most_recent_url ); ?>#tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-most-recent">
							<?php esc_attr_e( 'Most Recent', 'wplegalpages' ); ?>
						</a>
					</li>
					<li <?php echo ( 'all' === $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link" data-type="<?php echo esc_attr( $post_type_name ); ?>-all" href="<?php echo esc_url( $view_all_url ); ?>#<?php echo esc_attr( $post_type_name ); ?>-all">
							<?php esc_attr_e( 'View All', 'wplegalpages' ); ?>
						</a>
					</li>
					<li <?php echo ( 'search' === $current_tab ? ' class="tabs"' : '' ); ?>>
						<a class="nav-tab-link" data-type="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-search" href="<?php echo esc_url( $search_url ); ?>#tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-search">
							<?php esc_attr_e( 'Search', 'wplegalpages' ); ?>
						</a>
					</li>
				</ul><!-- .posttype-tabs -->

				<div id="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-most-recent" class="tabs-panel <?php echo ( 'most-recent' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>" role="region" aria-label="<?php esc_attr_e( 'Most Recent', 'wplegalpages' ); ?>" tabindex="0">
					<ul id="<?php echo esc_attr( $post_type_name ); ?>checklist-most-recent" class="categorychecklist form-no-clear">
						<?php
						$recent_args    = array_merge(
							$args,
							array(
								'orderby'        => 'post_date',
								'order'          => 'DESC',
								'posts_per_page' => 15,
							)
						);
						$most_recent    = $get_posts->query( $recent_args );
						$args['walker'] = $walker;

						/**
						 * Filters the posts displayed in the 'Most Recent' tab of the current
						 * post type's menu items meta box.
						 *
						 * The dynamic portion of the hook name, `$post_type_name`, refers to the post type name.
						 *
						 * @since 4.3.0
						 * @since 4.9.0 Added the `$recent_args` parameter.
						 *
						 * @param WP_Post[] $most_recent An array of post objects being listed.
						 * @param array     $args        An array of `WP_Query` arguments for the meta box.
						 * @param array     $box         Arguments passed to `wp_nav_menu_item_post_type_meta_box()`.
						 * @param array     $recent_args An array of `WP_Query` arguments for 'Most Recent' tab.
						 */
						$most_recent = apply_filters( "nav_menu_items_{$post_type_name}_recent", $most_recent, $args, array(), $recent_args );

						echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $most_recent ), 0, (object) $args );
						?>
					</ul>
				</div><!-- /.tabs-panel -->

				<div class="tabs-panel <?php echo ( 'search' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>" id="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-search" role="region" aria-label="<?php echo esc_attr( $post_type_name ); ?>" tabindex="0">
					<?php
					// phpcs:disable WordPress.Security.NonceVerification.Recommended	
					if ( isset( $_REQUEST[ 'quick-search-posttype-' . $post_type_name ] ) ) { // phpcs:ignore CSRF ok
						$searched       = sanitize_title( wp_unslash( $_REQUEST[ 'quick-search-posttype-' . $post_type_name ] ) ); // phpcs:ignore CSRF ok
						$search_results = get_posts(
							array(
								's'         => $searched,
								'post_type' => $post_type,
								'fields'    => 'all',
								'order'     => 'DESC',
							)
						);
					} else {
						$searched       = '';
						$search_results = array();
					}
					?>
					<p class="quick-search-wrap">
						<label for="quick-search-posttype-<?php echo esc_attr( $post_type_name ); ?>" class="screen-reader-text"><?php esc_attr_e( 'Search', 'wplegalpages' ); ?></label>
						<input type="search"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="quick-search" value="<?php echo esc_attr( $searched ); ?>" name="quick-search-posttype-<?php echo esc_attr( $post_type ); ?>" id="quick-search-posttype-<?php echo esc_attr( $post_type_name ); ?>" />
						<span class="spinner"></span>
						<?php submit_button( __( 'Search', 'wplegalpages' ), 'small quick-search-submit hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-posttype-' . $post_type_name ) ); ?>
					</p>

					<ul id="<?php echo esc_attr( $post_type_name ); ?>-search-checklist" data-wp-lists="list:<?php echo esc_attr( $post_type_name ); ?>" class="categorychecklist form-no-clear">
						<?php if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) ) : ?>
							<?php
							$args['walker'] = $walker;
							echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $search_results ), 0, (object) $args );
							?>
						<?php elseif ( is_wp_error( $search_results ) ) : ?>
							<li><?php echo esc_html( $search_results->get_error_message() ); ?></li>
						<?php elseif ( ! empty( $searched ) ) : ?>
							<li><?php esc_attr_e( 'No results found.', 'wplegalpages' ); ?></li>
						<?php endif; ?>
					</ul>
				</div><!-- /.tabs-panel -->

				<div id="<?php echo esc_attr( $post_type_name ); ?>-all" class="tabs-panel tabs-panel-view-all <?php echo ( 'all' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>" role="region" aria-label="<?php echo esc_attr( $post_type_name ); ?>" tabindex="0">
					<?php if ( ! empty( $page_links ) ) : ?>
						<div class="add-menu-item-pagelinks">
							<?php echo esc_html( $page_links ); ?>
						</div>
					<?php endif; ?>
					<ul id="<?php echo esc_attr( $post_type_name ); ?>checklist" data-wp-lists="list:<?php echo esc_attr( $post_type_name ); ?>" class="categorychecklist form-no-clear">
						<?php
						$args['walker'] = $walker;

						/**
						 * Filters the posts displayed in the 'View All' tab of the current
						 * post type's menu items meta box.
						 *
						 * The dynamic portion of the hook name, `$post_type_name`, refers
						 * to the slug of the current post type.
						 *
						 * @since 3.2.0
						 * @since 4.6.0 Converted the `$post_type` parameter to accept a WP_Post_Type object.
						 *
						 * @see WP_Query::query()
						 *
						 * @param object[]     $posts     The posts for the current post type. Mostly `WP_Post` objects, but
						 *                                can also contain "fake" post objects to represent other menu items.
						 * @param array        $args      An array of `WP_Query` arguments.
						 * @param WP_Post_Type $post_type The current post type object for this menu item meta box.
						 */
						$posts = apply_filters( "nav_menu_items_{$post_type_name}", $posts, $args, $post_type );

						$checkbox_items = walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $posts ), 0, (object) $args );

						echo esc_html( $checkbox_items );
						?>
					</ul>
					<?php if ( ! empty( $page_links ) ) : ?>
						<div class="add-menu-item-pagelinks">
							<?php echo esc_html( $page_links ); ?>
						</div>
					<?php endif; ?>
				</div><!-- /.tabs-panel -->

				<p class="button-controls wp-clearfix" data-items-type="posttype-<?php echo esc_attr( $post_type_name ); ?>">
			<span class="list-controls hide-if-no-js">
				<input type="checkbox"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> id="<?php echo esc_attr( $tab_name ); ?>" class="select-all" />
				<label for="<?php echo esc_attr( $tab_name ); ?>"><?php esc_attr_e( 'Select All', 'wplegalpages' ); ?></label>
			</span>

					<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'wplegalpages' ); ?>" name="add-post-type-menu-item" id="<?php echo esc_attr( 'submit-posttype-' . $post_type_name ); ?>" />
				<span class="spinner"></span>
			</span>
				</p>

			</div><!-- /.posttypediv -->
			<?php
		}

		/**
		 * Set option for do not show again settings warning on create legal page submenu.
		 */
		public function wplegalpages_disable_settings_warning() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			update_option( 'wplegalpages_disable_settings_warning', '1' );
			return true;
		}

		/**
		 * Ajax callback for setting page
		 */
		public function wplegalpages_ajax_save_settings() {
			if ( isset( $_POST['settings_form_nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['settings_form_nonce'] ) ), 'settings-form-nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			
			if ( isset( $_POST['lp-analytics-on'] ) ) {
				$ask_for_usage_analytics = true === sanitize_text_field( wp_unslash( $_POST['lp-analytics-on'] ) ) || 'true' === sanitize_text_field( wp_unslash( $_POST['lp-analytics-on'] ) ) ? '1' : '0';
				update_option( 'wplegalpages-ask-for-usage-optin', $ask_for_usage_analytics );
				update_option( 'wplegalpages-ask-for-usage-dismissed', '1' );
			}
			if ( isset( $_POST['lp-generate'] ) && ( 'true' === $_POST['lp-generate'] || true === $_POST['lp-generate'] ) ) {
				$_POST['lp-generate'] = '1';
			} else {
				$_POST['lp-generate'] = '0';
			}
			if ( isset( $_POST['lp-search'] ) && ( 'true' === $_POST['lp-search'] || true === $_POST['lp-search'] ) ) {
				$_POST['lp-search'] = '1';
			} else {
				$_POST['lp-search'] = '0';
			}
			if ( isset( $_POST['lp-affiliate-disclosure'] ) && ( 'true' === $_POST['lp-affiliate-disclosure'] || true === $_POST['lp-affiliate-disclosure'] ) ) {
				$_POST['lp-affiliate-disclosure'] = '1';
			} else {
				$_POST['lp-affiliate-disclosure'] = '0';
			}
			$is_block_enabled = '0';
			if ( isset( $_POST['lp-enable-block'] ) && ( 'true' === $_POST['lp-enable-block'] ) ) {
				$is_block_enabled = '1';
			}
			update_option( 'wplegalpages_is_block_enabled', $is_block_enabled );
			if ( isset( $_POST['lp-is_adult'] ) && ( 'true' === $_POST['lp-is_adult'] || true === $_POST['lp-is_adult'] ) ) {
				$_POST['lp-is_adult'] = '1';
			} else {
				$_POST['lp-is_adult'] = '0';
			}
			if ( isset( $_POST['lp-privacy'] ) && ( 'true' === $_POST['lp-privacy'] || true === $_POST['lp-privacy'] ) ) {
				$_POST['lp-privacy'] = '1';
			} else {
				$_POST['lp-privacy'] = '0';
			}
			if ( isset( $_POST['lp-footer'] ) && ( 'true' === $_POST['lp-footer'] || true === $_POST['lp-footer'] ) ) {
				$_POST['lp-footer'] = '1';
			} else {
				$_POST['lp-footer'] = '0';
			}
			if ( isset( $_POST['lp-banner'] ) && ( 'true' === $_POST['lp-banner'] || true === $_POST['lp-banner'] ) ) {
				$_POST['lp-banner'] = '1';
			} else {
				$_POST['lp-banner'] = '0';
			}
			$lp_general = array(
				'domain'    => isset( $_POST['lp-domain-name'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-domain-name'] ) ) : '',
				'business'  => isset( $_POST['lp-business-name'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-business-name'] ) ) : '',
				'phone'     => isset( $_POST['lp-phone'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-phone'] ) ) : '',
				'street'    => isset( $_POST['lp-street'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-street'] ) ) : '',
				'cityState' => isset( $_POST['lp-city-state'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-city-state'] ) ) : '',
				'country'   => isset( $_POST['lp-country'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-country'] ) ) : '',
				'email'     => isset( $_POST['lp-email'] ) ? sanitize_email( wp_unslash( $_POST['lp-email'] ) ) : '',
				'address'   => isset( $_POST['lp-address'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-address'] ) ) : '',
				'niche'     => isset( $_POST['lp-niche'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-niche'] ) ) : '',
				'generate'  => isset( $_POST['lp-generate'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-generate'] ) ) : '',
				'is_footer' => isset( $_POST['lp-footer'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer'] ) ) : '0',
				'is_banner' => isset( $_POST['lp-banner'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner'] ) ) : '0',
				'trading'   => isset( $_POST['lp-trading-name'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-trading-name'] ) ) : '',
			);
			$lp_general = apply_filters( 'wplegalpages_save_settings', $lp_general, $_POST );
			update_option( 'lp_general', $lp_general );
			$lp_footer_options                = get_option( 'lp_footer_options' );
			$lp_footer_options['show_footer'] = isset( $_POST['lp-footer'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer'] ) ) : '0';
			update_option( 'lp_footer_options', $lp_footer_options );
			$lp_banner_options                = get_option( 'lp_banner_options' );
			$lp_banner_options['show_banner'] = isset( $_POST['lp-banner'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner'] ) ) : '0';
			update_option( 'lp_banner_options', $lp_banner_options );

			if ( isset( $_POST['lp-cookie-bar'] ) ) {
				update_option( 'lp_eu_cookie_enable', sanitize_text_field( wp_unslash( $_POST['lp-cookie-bar'] ) ) );
			}

			wp_send_json_success( array( 'form_options_saved' => true ) );
		}

		/**
		 * Save WPLegalPages Pro Admin settings.
		 *
		 * @param Array $lp_general General settings.
		 * @param Array $data Data.
		 *
		 * @return mixed
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_save_settings( $lp_general, $data ) {
			$lp_general['privacy']              = isset( $data['lp-privacy'] ) ? sanitize_text_field( wp_unslash( $data['lp-privacy'] ) ) : '';
			$lp_general['privacy_page']         = isset( $data['lp-privacy-page'] ) ? sanitize_text_field( wp_unslash( $data['lp-privacy-page'] ) ) : '';
			$lp_general['facebook-url']         = isset( $data['lp-facebook-url'] ) ? sanitize_text_field( esc_attr( $data['lp-facebook-url'] ) ) : '';
			$lp_general['google-url']           = isset( $data['lp-google-url'] ) ? sanitize_text_field( esc_attr( $data['lp-google-url'] ) ) : '';
			$lp_general['twitter-url']          = isset( $data['lp-twitter-url'] ) ? sanitize_text_field( esc_attr( $data['lp-twitter-url'] ) ) : '';
			$lp_general['linkedin-url']         = isset( $data['lp-linkedin-url'] ) ? sanitize_text_field( esc_attr( $data['lp-linkedin-url'] ) ) : '';
			$lp_general['leave-url']            = isset( $data['lp-leave-url'] ) ? sanitize_text_field( esc_attr( $data['lp-leave-url'] ) ) : '';
			$lp_general['date']                 = isset( $data['lp-date'] ) ? sanitize_text_field( esc_attr( $data['lp-date'] ) ) : '';
			$lp_general['recipient-party']      = isset( $data['lp-recipient-party'] ) ? sanitize_text_field( esc_attr( $data['lp-recipient-party'] ) ) : '';
			$lp_general['disclosing-party']     = isset( $data['lp-disclosing-party'] ) ? sanitize_text_field( esc_attr( $data['lp-disclosing-party'] ) ) : '';
			$lp_general['affiliate-disclosure'] = isset( $data['lp-affiliate-disclosure'] ) ? sanitize_text_field( esc_attr( $data['lp-affiliate-disclosure'] ) ) : 0;
			$lp_general['days']                 = isset( $data['lp-days'] ) ? sanitize_text_field( esc_attr( $data['lp-days'] ) ) : '';
			$lp_general['refund_period']        = isset( $data['lp-refund-period'] ) ? sanitize_text_field( esc_attr( $data['lp-refund-period'] ) ) : '';
			$lp_general['return_period']        = isset( $data['lp-return-period'] ) ? sanitize_text_field( esc_attr( $data['lp-return-period'] ) ) : '';
			$lp_general['duration']             = isset( $data['lp-duration'] ) ? sanitize_text_field( esc_attr( $data['lp-duration'] ) ) : '';
			$lp_general['search']               = isset( $data['lp-search'] ) ? sanitize_text_field( esc_attr( $data['lp-search'] ) ) : 0;
			$lp_general['generate']             = isset( $data['lp-generate'] ) ? sanitize_text_field( esc_attr( $data['lp-generate'] ) ) : 0;
			$lp_general['is_adult']             = isset( $data['lp-is_adult'] ) ? sanitize_text_field( esc_attr( $data['lp-is_adult'] ) ) : 0;
			$lp_general['is_popup']             = isset( $data['lp-popup'] ) && 'true' === sanitize_text_field( esc_attr( $data['lp-popup'] ) ) ? '1' : '0';
			$lp_general['disable_comments']     = 1;
			$popup_option                       = isset( $data['lp-popup'] ) && 'true' === sanitize_text_field( esc_attr( $data['lp-popup'] ) ) ? '1' : '0';
			update_option( 'lp_popup_enabled', $popup_option );
			if ( isset( $data['lp-age'] ) ) {
				update_option( '_lp_require_for', sanitize_text_field( wp_unslash( $data['lp-age'] ) ) );
			}
			return $lp_general;
		}
		/**
		 * Ajax callback for saving age form
		 */
		public function wplegalpages_pro_save_age_form() {
			if ( isset( $_POST['lp_age_nonce_data'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp_age_nonce_data'] ) ), 'settings_age_form_nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			if ( isset( $_POST['lp-is-age'] ) ) {
				update_option( '_lp_require_for', sanitize_text_field( wp_unslash( $_POST['lp-is-age'] ) ) );
			}
			if ( isset( $_POST['lp-verify-for'] ) ) {
				'All visitors' === $_POST['lp-verify-for'] ? update_option( '_lp_always_verify', 'all' ) : update_option( '_lp_always_verify', 'guests' );
			}
			if ( isset( $_POST['lp-minimum-age'] ) ) {
				update_option( '_lp_minimum_age', sanitize_text_field( wp_unslash( $_POST['lp-minimum-age'] ) ) );
			}
			if ( isset( $_POST['lp-display-option'] ) ) {
				'Input Date of Birth' === $_POST['lp-display-option'] ? update_option( '_lp_display_option', 'date' ) : update_option( '_lp_display_option', 'button' );
			}
			if ( isset( $_POST['lp-redirect-url'] ) ) {
				update_option( '_lp_redirect_url', sanitize_text_field( wp_unslash( $_POST['lp-redirect-url'] ) ) );
			}
			if ( isset( $_POST['lp-age-popup-no'] ) ) {
				update_option( '_lp_age_popup_no', sanitize_text_field( wp_unslash( $_POST['lp-age-popup-no'] ) ) );
			}
			if ( isset( $_POST['lp-yes-button-text'] ) ) {
				update_option( 'lp_eu_button_text', sanitize_text_field( wp_unslash( $_POST['lp-yes-button-text'] ) ) );
			}
			if ( isset( $_POST['lp-no-button-text'] ) ) {
				update_option( 'lp_eu_button_text_no', sanitize_text_field( wp_unslash( $_POST['lp-no-button-text'] ) ) );
			}
			if ( isset( $_POST['lp-verification-description'] ) ) {
				update_option( '_lp_description', sanitize_text_field( wp_unslash( $_POST['lp-verification-description'] ) ) );
			}
			if ( isset( $_POST['lp-verification-description-invalid'] ) ) {
				update_option( '_lp_invalid_description', sanitize_text_field( wp_unslash( $_POST['lp-verification-description-invalid'] ) ) );
			}
			wp_send_json_success( array( 'form_options_saved' => true ) );
		}

		/**
		 * Ajax callback for saving popup form
		 */
		public function wplegalpages_pro_save_popup_form() {
			if ( isset( $_POST['lp_popup_nonce_data'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp_popup_nonce_data'] ) ), 'settings_popup_form_nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$popup = isset( $_POST['lp-is-popup'] ) && ( 'true' === $_POST['lp-is-popup'] || true === $_POST['lp-is-popup'] ) ? '1' : '0';
			update_option( 'lp_popup_enabled', sanitize_text_field( wp_unslash( $popup ) ) );
			wp_send_json_success( array( 'form_options_saved' => true ) );
		}
		/**
		 * Function to return pro options required for compliances tab
		 *
		 * @param  object $options_object data.
		 */
		public function wplegalpages_pro_compliances_options( $options_object ) {
			$age_verify              = get_option( '_lp_require_for' );
			$minimum_age             = get_option( '_lp_minimum_age' );
			$yes_button              = get_option( 'lp_eu_button_text' );
			$no_button               = get_option( 'lp_eu_button_text_no' );
			$age_description         = get_option( '_lp_description' );
			$invalid_age_description = get_option( '_lp_invalid_description' );
			$popup_enabled           = get_option( 'lp_popup_enabled' );
			if ( ! $age_verify ) {
				$age_verify = 'content';
			}
			if ( ! $minimum_age ) {
				$minimum_age = 18;
			}
			if ( ! $yes_button ) {
				$yes_button = 'Yes, I am';
			}
			if ( ! $no_button ) {
				$no_button = 'No, I am not';
			}
			if ( ! $age_description ) {
				$age_description = "To proceed, we need to verify that you're {age} or older.\n<br><span>Please verify your age.</span>\n{form}";
			}
			if ( ! $invalid_age_description ) {
				$invalid_age_description = 'We are sorry. You are not of valid age.';
			}
			if ( ! $popup_enabled ) {
				$popup_enabled = '0';
			}
			$options_object['age_verify_enable']       = $age_verify;
			$options_object['minimum_age']             = $minimum_age;
			$options_object['age_yes_button']          = $yes_button;
			$options_object['age_no_button']           = $no_button;
			$options_object['age_description']         = $age_description;
			$options_object['invalid_age_description'] = $invalid_age_description;
			$options_object['popup_enabled']           = $popup_enabled;
			return $options_object;
		}
		/**
		 * Function for Shortcodes tab
		 */
		public function wplegalpages_pro_shortcodes_table() {
			$shortcodes = array(
				'Facebook URL'             => '[facebook]',
				'Google URL'               => '[twitter]',
				'Twitter URL'              => '[google]',
				'LinkedIn URL'             => '[linkedin]',
				'Date'                     => '[date]',
				'Duration'                 => '[duration]',
				'Days'                     => '[days]',
				'Return Period'            => '[Return Period]',
				'Refund Processing Period' => '[Refund Processing Period]',
				'Disclosing Party Name'    => '[disclosing-party]',
				'Recipient Party Name'     => '[recipient-party]',
			);
			$i          = 1;
			foreach ( $shortcodes as $key => $val ) {
				$classname = 0 === $i % 2 ? 'wplegalpages-shortcode-table-even' : 'wplegalpages-shortcode-table-odd';
				?>
					<c-row class="<?php echo esc_attr( $classname ); ?>">
						<c-col class="col-sm-6"><?php echo esc_attr( $key ); ?></c-col>
						<c-col class="col-sm-6"><?php echo esc_attr( $val ); ?></c-col>
					</c-row>
					<?php
					++$i;
			}
		}
		/**
		 * Process WPLegalPages shortcodes.
		 *
		 * @param string $content Content.
		 *
		 * @return mixed
		 *
		 * @since 3.0.0
		 */
		public function wplegalpages_pro_shortcode_content( $content ) {
			$lp_general = get_option( 'lp_general' );

			$content = str_replace( '[date]', isset( $lp_general['date'] ) ? $lp_general['date'] : '', stripslashes( $content ) );
			$content = str_replace( '[recipient-party]', isset( $lp_general['recipient-party'] ) ? $lp_general['recipient-party'] : '', stripslashes( $content ) );
			$content = str_replace( '[disclosing-party]', isset( $lp_general['disclosing-party'] ) ? $lp_general['disclosing-party'] : '', stripslashes( $content ) );
			$content = str_replace( '[days]', isset( $lp_general['days'] ) ? $lp_general['days'] : '', stripslashes( $content ) );
			$content = str_replace( '[Return Period]', isset( $lp_general['return_period'] ) ? $lp_general['return_period'] : '', stripslashes( $content ) );
			$content = str_replace( '[Refund Processing Period]', isset( $lp_general['refund_period'] ) ? $lp_general['refund_period'] : '', stripslashes( $content ) );
			$content = str_replace( '[duration]', isset( $lp_general['duration'] ) ? $lp_general['duration'] : '', stripslashes( $content ) );
			if ( ! shortcode_exists( 'wpl_cookie_details' ) ) {
				$content = str_replace( '[wpl_cookie_details]', '', stripslashes( $content ) );
			}
			$latitude  = '';
			$longitude = '';
			$facebook  = '';
			$google    = '';
			$twitter   = '';
			$linkedin  = '';

			$pos = strpos( $content, '[latitude]' );
			if ( false !== $pos ) {
				$output = $this->add_map();

				if ( ! empty( $output->results ) ) {
					$latitude  = $output->results[0]->geometry->location->lat;
					$longitude = $output->results[0]->geometry->location->lng;
				}
				$lp_map_find   = array( '[latitude]', '[longitude]' );
				$map_dimension = array(
					'latitude'  => $latitude,
					'longitude' => $longitude,
				);
				$content       = str_replace( $lp_map_find, $map_dimension, stripslashes( $content ) );
			}

			$facebook_url = isset( $lp_general['facebook-url'] ) ? $lp_general['facebook-url'] : '';

			if ( $facebook_url ) {
				$facebook = array( 'facebook' => '<a style="width: 30px; height: 30px; display:inline-block" href="' . $facebook_url . '"><img src="' . WPL_LITE_PLUGIN_URL . 'admin/images/facebook.png"/></a>' ); //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			}
			$lp_find_facebook = array( '[facebook]' );
			$content          = str_replace( $lp_find_facebook, $facebook, stripslashes( $content ) );

			$google_url = isset( $lp_general['google-url'] ) ? $lp_general['google-url'] : '';

			if ( $google_url ) {

				$google = array( 'google' => '<a style="width: 30px; height: 30px; display:inline-block" href="' . $google_url . '"><img src="' . WPL_LITE_PLUGIN_URL . 'admin/images/google.png"/></a>' ); //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			}
			$lp_find_google = array( '[google]' );
			$content        = str_replace( $lp_find_google, $google, stripslashes( $content ) );

			$twitter_url = isset( $lp_general['twitter-url'] ) ? $lp_general['twitter-url'] : '';
			if ( $twitter_url ) {

				$twitter = array( 'twitter' => '<a style="width: 30px; height: 30px; display:inline-block" href="' . $twitter_url . '"><img src="' . WPL_LITE_PLUGIN_URL . 'admin/images/twitter.png"/></a>' ); //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			}
			$lp_find_twitter = array( '[twitter]' );
			$content         = str_replace( $lp_find_twitter, $twitter, stripslashes( $content ) );

			$linkedin_url = isset( $lp_general['linkedin-url'] ) ? $lp_general['linkedin-url'] : '';
			if ( $linkedin_url ) {
				$linkedin = array( 'linkedin' => '<a style="width: 30px; height: 30px; display:inline-block" href="' . $linkedin_url . '"><img src="' . WPL_LITE_PLUGIN_URL . 'admin/images/linkedin.png"/></a>' ); //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
			}
			$lp_find_linkedin = array( '[linkedin]' );
			$content          = str_replace( $lp_find_linkedin, $linkedin, stripslashes( $content ) );
			return $content;
		}
		/**
		 * Ajax callback for legalpages add link to footer form
		 */
		public function wplegalpages_save_footer_form() {
			if ( isset( $_POST['lp_footer_nonce_data'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp_footer_nonce_data'] ) ), 'settings_footer_form_nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$footer_legal_pages = array();
			if ( isset( $_POST['lp-footer-pages'] ) ) {
				$footer_legal_pages = explode( ',', sanitize_text_field( wp_unslash( $_POST['lp-footer-pages'] ) ) );
			}
			$lp_footer_options = array(
				'footer_legal_pages' => $footer_legal_pages,
				'show_footer'        => isset( $_POST['lp-is-footer'] ) && ( true === $_POST['lp-is-footer'] || 'true' === $_POST['lp-is-footer'] ) ? '1' : '0',
				'footer_bg_color'    => isset( $_POST['lp-footer-link-bg-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-link-bg-color'] ) ) : '#ffffff',
				'footer_text_align'  => isset( $_POST['lp-footer-align'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-align'] ) ) : 'center',
				'footer_separator'   => isset( $_POST['lp-footer-separator'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-separator'] ) ) : '',
				'footer_new_tab'     => isset( $_POST['lp-footer-new-tab'] ) && ( true === $_POST['lp-footer-new-tab'] || 'true' === $_POST['lp-footer-new-tab'] ) ? '1' : '0',
				'footer_text_color'  => isset( $_POST['lp-footer-text-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-text-color'] ) ) : '#333333',
				'footer_link_color'  => isset( $_POST['lp-footer-link-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-link-color'] ) ) : '#333333',
				'footer_font'        => isset( $_POST['lp-footer-font'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-font'] ) ) : 'Open Sans',
				'footer_font_id'     => isset( $_POST['lp-footer-font-family-id'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-font-family-id'] ) ) : 'Open+Sans',
				'footer_font_size'   => isset( $_POST['lp-footer-font-size'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-footer-font-size'] ) ) : '16',
				'footer_custom_css'  => isset( $_POST['lp-footer-css'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lp-footer-css'] ) ) : '',
			);
			update_option( 'lp_footer_options', $lp_footer_options );
			$lp_general              = get_option( 'lp_general' );
			$lp_general['is_footer'] = isset( $_POST['lp-is-footer'] ) && ( true === $_POST['lp-is-footer'] || 'true' === $_POST['lp-is-footer'] ) ? '1' : '0';
			update_option( 'lp_general', $lp_general );
			wp_send_json_success( array( 'form_options_saved' => true ) );
		}

		/**
		 * Function to set cookie when a post is updated
		 *
		 * @param int $post_id id of post updated.
		 */
		public function wplegalpages_post_updated( $post_id ) {
			$currentTimestamp = time();
			update_option( 'updateAt', $currentTimestamp );
			if ( 'yes' === get_post_meta( $post_id, 'is_legal', true ) && 'publish' === get_post_status( $post_id ) ) {
				$lp_banner_options = get_option( 'lp_banner_options' );
				if ( $lp_banner_options ) {
					$cookie_days = $lp_banner_options['bar_num_of_days'];
					if ( false === get_option( 'banner_cookie_options' ) || ! get_option( 'banner_cookie_options' ) ) {
						update_option( 'banner_cookie_options', array() );
					}
					$banner_cookie_options             = get_option( 'banner_cookie_options' );
					$banner_cookie_options[ $post_id ] = array(
						'cookie_start' => time(),
						'cookie_end'   => time() + ( 86400 * $cookie_days ),
						'cookie_name'  => 'wplegalpages-update-notice-' . $post_id,
					);
					update_option( 'banner_cookie_options', $banner_cookie_options );
				}
			}
		}

		/**
		 * Ajax callback for banner form
		 */
		public function wplegalpages_save_banner_form() {
			if ( isset( $_POST['lp_banner_nonce_data'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp_banner_nonce_data'] ) ), 'settings_banner_form_nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$lp_banner_options = array(
				'show_banner'             => isset( $_POST['lp-is-banner'] ) && ( true === $_POST['lp-is-banner'] || 'true' === $_POST['lp-is-banner'] ) ? '1' : '0',
				'bar_position'            => isset( $_POST['lp-bar-position'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-bar-position'] ) ) : 'top',
				'bar_type'                => isset( $_POST['lp-bar-type'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-bar-type'] ) ) : 'static',
				'banner_bg_color'         => isset( $_POST['lp-banner-bg-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-bg-color'] ) ) : '#ffffff',
				'banner_font'             => isset( $_POST['lp-banner-font'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-font'] ) ) : 'Open Sans',
				'banner_font_id'          => isset( $_POST['lp-banner-font-id'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-font-id'] ) ) : 'Open+Sans',
				'banner_text_color'       => isset( $_POST['lp-banner-text-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-text-color'] ) ) : '#000000',
				'banner_font_size'        => isset( $_POST['lp-banner-font-size'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-font-size'] ) ) : '20px',
				'banner_link_color'       => isset( $_POST['lp-banner-link-color'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-link-color'] ) ) : '#000000',
				'bar_num_of_days'         => isset( $_POST['lp-bar-num-of-days'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-bar-num-of-days'] ) ) : '1',
				'banner_custom_css'       => isset( $_POST['lp-banner-css'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lp-banner-css'] ) ) : '',
				'banner_close_message'    => isset( $_POST['lp-banner-close-message'] ) ? sanitize_text_field( wp_unslash( $_POST['lp-banner-close-message'] ) ) : 'Close',
				'banner_message'          => isset( $_POST['lp-banner-message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lp-banner-message'] ) ) : 'Our [wplegalpages_page_link] have been updated on [wplegalpages_last_updated].',
				'banner_multiple_message' => isset( $_POST['lp-banner-multiple-msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lp-banner-multiple-msg'] ) ) : 'Our [wplegalpages_page_link] pages have recently have recently been updated.',
			);
			update_option( 'lp_banner_options', $lp_banner_options );
			$lp_general              = get_option( 'lp_general' );
			$lp_general['is_banner'] = isset( $_POST['lp-is-banner'] ) && ( true === $_POST['lp-is-banner'] || 'true' === $_POST['lp-is-banner'] ) ? '1' : '0';
			update_option( 'lp_general', $lp_general );
			wp_send_json_success( array( 'form_options_saved' => true ) );
		}

		/**
		 * Ajax callback for Cookie Bar form
		 */
		public function wplegalpages_save_cookie_bar_form() {
			if ( isset( $_POST['lp-cookie-bar-nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp-cookie-bar-nonce'] ) ), 'settings_cookie_bar_form_nonce' ) ) {
					return;
				}
			} else {
				return;
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			if ( isset( $_POST['lp-cookie-bar-enable'] ) ) {
				update_option( 'lp_eu_cookie_enable', sanitize_text_field( wp_unslash( $_POST['lp-cookie-bar-enable'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-bar-title'] ) ) {
				update_option( 'lp_eu_cookie_title', sanitize_text_field( wp_unslash( $_POST['lp-cookie-bar-title'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-message-body'] ) ) {
				update_option( 'lp_eu_cookie_message', sanitize_text_field( wp_unslash( $_POST['lp-cookie-message-body'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-button-text'] ) ) {
				update_option( 'lp_eu_button_text', sanitize_text_field( wp_unslash( $_POST['lp-cookie-button-text'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-link-text'] ) ) {
				update_option( 'lp_eu_link_text', sanitize_text_field( wp_unslash( $_POST['lp-cookie-link-text'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-link-url'] ) ) {
				update_option( 'lp_eu_link_url', sanitize_text_field( wp_unslash( $_POST['lp-cookie-link-url'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-use-theme-css'] ) ) {
				update_option( 'lp_eu_theme_css', sanitize_text_field( wp_unslash( $_POST['lp-cookie-use-theme-css'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-box-background-color'] ) ) {
				update_option( 'lp_eu_box_color', sanitize_text_field( wp_unslash( $_POST['lp-cookie-box-background-color'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-box-text-color'] ) ) {
				update_option( 'lp_eu_text_color', sanitize_text_field( wp_unslash( $_POST['lp-cookie-box-text-color'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-button-background-color'] ) ) {
				update_option( 'lp_eu_button_color', sanitize_text_field( wp_unslash( $_POST['lp-cookie-button-background-color'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-button-text-color'] ) ) {
				update_option( 'lp_eu_button_text_color', sanitize_text_field( wp_unslash( $_POST['lp-cookie-button-text-color'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-link-color'] ) ) {
				update_option( 'lp_eu_link_color', sanitize_text_field( wp_unslash( $_POST['lp-cookie-link-color'] ) ) );
			}
			if ( isset( $_POST['lp-cookie-text-size'] ) ) {
				update_option( 'lp_eu_text_size', sanitize_text_field( wp_unslash( $_POST['lp-cookie-text-size'] ) ) );
			}
			wp_send_json_success( array( 'form_options_saved' => true ) );
		}

		/**
		 * Dequeue forms.css
		 *
		 * @param string $href .
		 */
		public function wplegalpages_dequeue_styles( $href ) {
			if ( is_admin() ) {
				$my_current_screen = get_current_screen();
				if ( isset( $my_current_screen->post_type ) && ( 'toplevel_page_legal-pages' === $my_current_screen->base ) ) {
					if ( strpos( $href, 'forms.css' ) !== false || strpos( $href, 'revisions' ) ) {
						return false;
					}
					if ( version_compare( $this->version, '2.6.0', '>=' ) ) {
						if ( strpos( $href, 'bootstrap.min.css' ) !== false || strpos( $href, 'revisions' ) ) {
							return false;
						}
					}
				}
			}
			return $href;
		}

		/**
		 * Dequeue forms.css && revisions.css for newer version of WordPress.
		 *
		 * @param array $to_dos .
		 */
		public function wplegalpages_remove_forms_style( $to_dos ) {
			if ( is_admin() ) {
				$my_current_screen = get_current_screen();

				if ( isset( $my_current_screen->post_type ) && ( 'toplevel_page_legal-pages' === $my_current_screen->base ) && ( in_array( 'forms', $to_dos, true ) || in_array( 'revisions', $to_dos, true ) ) ) {
					$key = array_search( 'forms', $to_dos, true );
					unset( $to_dos[ $key ] );
					$key = array_search( 'revisions', $to_dos, true );
					unset( $to_dos[ $key ] );
				}
			}
			return $to_dos;
		}


		/**
		 * Callback for wp_trash_post hook
		 *
		 * @since 2.8.1
		 *
		 * @param int $post_id ID of the post trashed.
		 */
		public function wplegalpages_trash_page( $post_id ) {
			if ( 'yes' === get_post_meta( $post_id, 'is_legal', true ) ) {
				$footer_options = get_option( 'lp_footer_options' );
				if ( empty( $footer_options ) ) {
					return;
				}
				$footer_pages = $footer_options['footer_legal_pages'];
				if ( ! empty( $footer_pages ) && in_array( $post_id, $footer_pages, true ) ) {
					$length               = count( $footer_pages );
					$updated_footer_pages = array();
					$j                    = 0;
					for ( $i = 0; $i < $length; $i++ ) {
						if ( $footer_pages[ $i ] !== $post_id ) {
							$updated_footer_pages[ $j ] = $footer_pages[ $i ];
							++$j;
						}
					}
					$footer_options['footer_legal_pages'] = $updated_footer_pages;
					update_option( 'lp_footer_options', $footer_options );
				}
			}
		}

		/**
		 * Setup wizard.
		 */
		public function setup_legal_wizard() {
			// The below phpcs ignore comment is added after referring woocommerce plugin.]
			if ( empty( $_GET['page'] ) || 'wplegal-wizard' !== $_GET['page'] ) { // phpcs:ignore CSRF ok, input var ok.\
				return;
			}

			// Don't load the interface if doing an ajax call.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			set_current_screen();

			// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
			remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

			$this->load_wplegal_wizard();
		}

		/**
		 * Load wizard.
		 */
		public function load_wplegal_wizard() {
			$this->wplegal_wizard_enqueue_scripts();
			$this->wplegal_wizard_header();
			$this->wplegal_wizard_content();
			$this->wplegal_wizard_footer();

			exit;
		}

		/**
		 * Enqueue wizard scripts.
		 */
		public function wplegal_wizard_enqueue_scripts() {
			wp_enqueue_style( $this->plugin_name . '-select2', plugin_dir_url( __FILE__ ) . 'wizard/libraries/select2/select2.css', array(), $this->version, '' );
			wp_enqueue_style( $this->plugin_name . '-vue-style', plugin_dir_url( __FILE__ ) . 'css/vue/vue-wizard.css', array(), $this->version, '' );
			wp_enqueue_style( $this->plugin_name . '-vue-toast-style', plugin_dir_url( __FILE__ ) . 'css/vue/vue-toast.css', array(), $this->version, '' );
			wp_enqueue_style( $this->plugin_name . '-vue-nav-tabs-style', plugin_dir_url( __FILE__ ) . 'css/vue/vue-nav-tabs.css', array(), $this->version, '' );
			wp_register_script( $this->plugin_name . '-vue', plugin_dir_url( __FILE__ ) . 'js/vue/vue.min.js', array(), $this->version, false );
			wp_register_script( $this->plugin_name . '-vue-router', plugin_dir_url( __FILE__ ) . 'js/vue/vue-router.js', array(), $this->version, false );
			wp_register_script( $this->plugin_name . '-vue-toast', plugin_dir_url( __FILE__ ) . 'js/vue/vue-toast.js', array(), $this->version, false );
			wp_register_script( $this->plugin_name . '-select2', plugin_dir_url( __FILE__ ) . 'wizard/libraries/select2/select2.js', array( 'jquery' ), $this->version, true );
			wp_register_script( $this->plugin_name . '-vue-script', WPLEGAL_API_ADMIN_URL . 'vue-wizard/vue-wizard.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-tinymce-vue', plugin_dir_url( __FILE__ ) . 'js/vue/vue-tinymce.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-tinymce', plugin_dir_url( __FILE__ ) . 'js/vue/tinymce/tinymce.min.js', array( 'jquery' ), $this->version, false );
			wp_register_script( $this->plugin_name . '-vue-nav-tabs', plugin_dir_url( __FILE__ ) . 'js/vue/vue-nav-tabs.js', array( 'jquery' ), $this->version, false );

			require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';

			// Instantiate a new object of the WP_Legal_Pages_Settings class.
			$this->settings = new WP_Legal_Pages_Settings();

			// Call the is_connected() method from the instantiated object to check if the user is connected.
			$is_user_connected = $this->settings->is_connected();
			$plan_name         = $this->settings->get_plan();
			$installed_plugins = get_plugins();
			$pro_installed     = isset( $installed_plugins['wplegalpages-pro/wplegalpages-pro.php'] ) ? "Activated" : "Not Activated";
			wp_localize_script(
				$this->plugin_name . '-vue-script',
				'wizard_obj',
				array(
					'return_button_text' => __( 'Exit Wizard', 'wplegalpages' ),
					'return_url'         => admin_url( 'admin.php?page=legal-pages' ),
					'image_url'          => WPL_LITE_PLUGIN_URL . 'admin/js/vue/wizard_images/',
					'ajax_url'           => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'         => wp_create_nonce( 'admin-ajax-nonce' ),
					'pro_active'         => $pro_installed,
					'available_tab'      => __( 'Available Templates', 'wplegalpages' ),
					'pro_tab'            => __( 'Templates', 'wplegalpages' ),
					'promotion_text'     => __( 'Can\'t find what you are looking for?', 'wplegalpages' ),
					'promotion_button'   => __( 'Go Pro', 'wplegalpages' ),
					'pro_text'           => __( "Can't find what you are looking for?", 'wplegalpages' ),
					'pro_button'         => __( 'Go Pro', 'wplegalpages' ),
					'wplegal_app_url'    => WPLEGAL_APP_URL,
					'is_user_connected'  => $is_user_connected,
					'plan_name'          => $plan_name,
					'_ajax_nonce'        => wp_create_nonce( 'wp-legal-pages' ),
					'promotion_link'     => 'https://app.wplegalpages.com/pricing/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=upgrade',
					'welcome'            => array(
						'create'     => __( 'Create', 'wplegalpages' ),
						'edit'       => __( 'Edit', 'wplegalpages' ),
						'next'       => __( 'Next', 'wplegalpages' ),
						'prev'       => __( 'Go Back', 'wplegalpages' ),
						'title'      => __( 'Welcome to WPLegalPages Wizard!', 'wplegalpages' ),
						'subtitle'   => __( 'Follow the guided wizard to get started', 'wplegalpages' ),
						'inputtitle' => __( 'Select the policy template to get started.', 'wplegalpages' ),
					),
					'settings'           => array(
						'next'     => __( 'Next', 'wplegalpages' ),
						'prev'     => __( 'Go Back', 'wplegalpages' ),
						'title'    => __( 'Recommended Settings', 'wplegalpages' ),
						'subtitle' => __( 'WPLegalPages recommends the following settings based on your policy template', 'wplegalpages' ),
					),
					'sections'           => array(
						'next'     => __( 'Next', 'wplegalpages' ),
						'prev'     => __( 'Go Back', 'wplegalpages' ),
						'title'    => __( 'Data Sections', 'wplegalpages' ),
						'subtitle' => __( 'Choose the appropriate sections for your policy template', 'wplegalpages' ),
					),
					'preview'            => array(
						'next'     => __( 'Create and Edit', 'wplegalpages' ),
						'prev'     => __( 'Go Back', 'wplegalpages' ),
						'title'    => __( 'Policy Template Preview', 'wplegalpages' ),
						'subtitle' => __( 'Review your policy template and publish', 'wplegalpages' ),
					),
				)
			);
			wp_print_styles( $this->plugin_name . '-select2' );
		}

		/**
		 * Wizard header.
		 */
		public function wplegal_wizard_header() {
			?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php esc_html_e( 'WPLegalPages &rsaquo; Wizard', 'wplegalpages' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wplegal-wizard">
			<?php
		}

		/**
		 * Wizard footer.
		 */
		public function wplegal_wizard_footer() {
			?>
			<?php wp_print_scripts( $this->plugin_name . '-select2' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-tinymce' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-tinymce-vue' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-vue' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-vue-nav-tabs' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-vue-router' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-vue-toast' ); ?>
			<?php wp_print_scripts( $this->plugin_name . '-vue-script' ); ?>
		</body>
		</html>
			<?php
		}
		/**
		 * Wizard content.
		 */
		public function wplegal_wizard_content() {
			?>
		<div id="wplegalwizardapp"></div>
			<?php
		}

		/**
		 * Save page section clauses.
		 *
		 * @param array $field Field.
		 * @param array $data Data options.
		 * @return array
		 */
		public function wplegalpages_page_sections_clauses_save( $field, $data = array() ) {
			switch ( $field->type ) {
				case 'section':
					if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
						foreach ( $field->sub_fields as $sub_key => $sub_field ) {
							$data = $this->wplegalpages_page_sections_clauses_save( $sub_field, $data );
						}
					}
					break;
				case 'select2':
					if ( isset( $field->value ) && ! empty( $field->value ) ) {
						$data[ $field->id ] = $field->value;
					}
					break;
				case 'input':
					if ( isset( $field->value ) && ! empty( $field->value ) ) {
						$data[ $field->id ] = $field->value;
					}
					break;
				case 'textarea':
					if ( isset( $field->value ) && ! empty( $field->value ) ) {
						$data[ $field->id ] = $field->value;
					}
					break;
				case 'checkbox':
					if ( $field->checked ) {
						$data[ $field->id ] = true;
						if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
							foreach ( $field->sub_fields as $sub_key => $sub_field ) {
								$data = $this->wplegalpages_page_sections_clauses_save( $sub_field, $data );
							}
						}
					}
					break;
				case 'radio':
					if ( $field->checked ) {
						$data[ $field->id ] = true;
						if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
							foreach ( $field->sub_fields as $sub_key => $sub_field ) {
								$data = $this->wplegalpages_page_sections_clauses_save( $sub_field, $data );
							}
						}
					}
					break;
				case 'wpeditor':
					if ( isset( $field->value ) && ! empty( $field->value ) ) {
						$data[ $field->id ] = $field->value;
					}
					break;
			}
			return $data;
		}

		/**
		 * Save page section settings.
		 *
		 * @param array $field Field.
		 * @param array $post_data Post Data.
		 * @return mixed
		 */
		public function wplegalpages_page_sections_settings_save( $field, $post_data = '' ) {
			switch ( $field->type ) {
				case 'section':
					if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
						foreach ( $field->sub_fields as $sub_field ) {
							$this->wplegalpages_page_sections_settings_save( $sub_field, $post_data );
						}
					}
					return $field;
				case 'select2':
					if ( isset( $post_data[ $field->name ] ) ) {
						$field->value = $post_data[ $field->name ];
						$sub_fields   = $field->sub_fields;
						foreach ( $post_data[ $field->name ] as $value ) {
							$sub_fields->$value->checked = true;
						}
						$field->sub_fields = $sub_fields;
					}

					break;
				case 'input':
					if ( isset( $post_data[ $field->name ] ) ) {
						$field->value = $post_data[ $field->name ];
					}
					return $field;
				case 'textarea':
					if ( isset( $post_data[ $field->name ] ) ) {
						$field->value = $post_data[ $field->name ];
					}
					return $field;
				case 'checkbox':
					if ( isset( $post_data[ $field->name ] ) ) {
						$field->checked = true;
						if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
							foreach ( $field->sub_fields as $sub_field ) {
								$this->wplegalpages_page_sections_settings_save( $sub_field, $post_data );
							}
						}
					} else {
						$field->checked = false;
					}
					return $field;
				case 'radio':
					if ( isset( $post_data[ $field->name ] ) && $post_data[ $field->name ] === $field->value ) {
						$field->checked = true;
						if ( isset( $field->sub_fields ) && ! empty( $field->sub_fields ) ) {
							foreach ( $field->sub_fields as $sub_field ) {
								$this->wplegalpages_page_sections_settings_save( $sub_field, $post_data );
							}
						}
					} else {
						$field->checked = false;
					}
					return $field;
				case 'wpeditor':
					if ( isset( $post_data[ $field->name ] ) ) {
						$field->value = $post_data[ $field->name ];
					}
					return $field;
			}
		}

		/**
		 * Create policy page and return post id.
		 *
		 * @param string $title Page title.
		 * @param string $content Page content.
		 * @return int|void|WP_Error
		 */
		public function wplegalpages_get_pid_by_insert_page( $title = '', $content = '' ) {
			$post_args = array(
				'post_title'   => apply_filters( 'the_title', $title ),
				'post_content' => $content,
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_author'  => get_current_user_id(),
			);
			$pid       = wp_insert_post( $post_args );
			if ( ! is_wp_error( $pid ) ) {
				return $pid;
			} else {
				return;
			}
		}

		/**
		 * Save page sections.
		 */
		public function wplegalpages_page_sections_save() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			require_once plugin_dir_path( __DIR__ ) . 'admin/wizard/class-wp-legal-pages-wizard-page.php';
			$lp     = new WP_Legal_Pages_Wizard_Page();
			$result = array(
				'success' => false,
			);
			if ( isset( $_POST['action'] ) ) {
				$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
				if ( wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
					$page      = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 'privacy_policy';
					$pid       = $this->wplegalpages_get_pid_by_page( $page );
					$post_data = isset( $_POST['data'] ) ? map_deep( wp_unslash( $_POST['data'] ), 'sanitize_text_field' ) : '';
					switch ( $page ) {
						case 'terms_of_use':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages__get_pid_by_insert_page( 'Terms and Conditions' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$clauses       = $this->wplegalpages_get_remote_data( 'get_clauses' );
								$terms_options = array();
								foreach ( $clauses as $key => $clause ) {
									if ( empty( $clause->fields ) ) {
										$clause->fields        = $this->wplegalpages_get_remote_data( 'get_clause_settings?clause=' . $key );
										$terms_options[ $key ] = $clause;
									}
								}
								update_post_meta( $pid, 'legal_page_clauses', $terms_options );
								update_option( 'wplegal_terms_of_use_page', $pid );
							} else {
								$terms_clauses = get_post_meta( $pid, 'legal_page_clauses', true );
								$terms_options = $terms_clauses;
							}
							$data = array();
							foreach ( $terms_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_clauses', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_clauses_options', $data );
							break;
						case 'terms_of_use_free':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Terms of Use' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$terms_of_use_free_options = $this->wplegalpages_get_remote_data( 'get_terms_of_use' );
								update_post_meta( $pid, 'legal_page_terms_of_use_settings', $terms_of_use_free_options );
								update_option( 'wplegal_terms_of_use_free_page', $pid );
							} else {
								$terms_of_use_free_settings = get_post_meta( $pid, 'legal_page_terms_of_use_settings', true );
								$terms_of_use_free_options  = $terms_of_use_free_settings;
							}
							$data = array();
							foreach ( $terms_of_use_free_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_terms_of_use_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_terms_of_use_options', $data );
							break;
						case 'fb_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Facebook Policy' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$fb_policy_options = $this->wplegalpages_get_remote_data( 'get_fb_policy' );
								update_post_meta( $pid, 'legal_page_fb_policy_settings', $fb_policy_options );
								update_option( 'wplegal_fb_policy_page', $pid );
							} else {
								$fb_policy_settings = get_post_meta( $pid, 'legal_page_fb_policy_settings', true );
								$fb_policy_options  = $fb_policy_settings;
							}
							$data = array();
							foreach ( $fb_policy_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_fb_policy_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_fb_policy_options', $data );
							break;
						case 'affiliate_agreement':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Affiliate Agreement' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$affiliate_agreement_options = $this->wplegalpages_get_remote_data( 'get_affiliate_agreement' );
								update_post_meta( $pid, 'legal_page_affiliate_agreement_settings', $affiliate_agreement_options );
								update_option( 'wplegal_affiliate_agreement_page', $pid );
							} else {
								$affiliate_agreement_settings = get_post_meta( $pid, 'legal_page_affiliate_agreement_settings', true );
								$affiliate_agreement_options  = $affiliate_agreement_settings;
							}
							$data = array();
							foreach ( $affiliate_agreement_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_affiliate_agreement_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_affiliate_agreement_options', $data );
							break;
						case 'standard_privacy_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Standard Privacy Policy' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$standard_privacy_policy_options = $this->wplegalpages_get_remote_data( 'get_standard_privacy_policy' );
								update_post_meta( $pid, 'legal_page_standard_privacy_policy_settings', $standard_privacy_policy_options );
								update_option( 'wplegal_standard_privacy_policy_page', $pid );
							} else {
								$standard_privacy_policy_settings = get_post_meta( $pid, 'legal_page_standard_privacy_policy_settings', true );
								$standard_privacy_policy_options  = $standard_privacy_policy_settings;
							}
							$data = array();
							foreach ( $standard_privacy_policy_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_standard_privacy_policy_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_standard_privacy_policy_options', $data );
							break;
						case 'california_privacy_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Privacy Notice For California Residents' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$ccpa_options = $this->wplegalpages_get_remote_data( 'get_ccpa_settings' );
								update_post_meta( $pid, 'legal_page_ccpa_settings', $ccpa_options );
								update_option( 'wplegal_california_privacy_policy_page', $pid );
							} else {
								$ccpa_settings = get_post_meta( $pid, 'legal_page_ccpa_settings', true );
								$ccpa_options  = $ccpa_settings;
							}
							$data = array();
							foreach ( $ccpa_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										if ( isset( $_POST['data'][ $field_key ] ) ) {
											$field->checked = true;
										} else {
											$field->checked = false;
										}
										$settings_data[ $field_key ] = $field;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							$options = array();
							foreach ( $data as $key => $option ) {
								if ( isset( $option->checked ) && true === $option->checked ) {
									$options[ $key ] = true;
									$fields          = $option->fields;
									foreach ( $fields as $field_key => $field ) {
										if ( isset( $field->checked ) && true === $field->checked ) {
											$options[ $field_key ] = true;
										} else {
											$options[ $field_key ] = false;
										}
									}
								} else {
									$options[ $key ] = false;
								}
							}
							update_post_meta( $pid, 'legal_page_ccpa_settings', $data );
							update_post_meta( $pid, 'legal_page_ccpa_options', $options );
							break;
						case 'returns_refunds_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Returns and Refunds Policy' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$returns_refunds_options = $this->wplegalpages_get_remote_data( 'get_returns_refunds_settings' );
								update_post_meta( $pid, 'legal_page_returns_refunds_settings', $returns_refunds_options );
								update_option( 'wplegal_returns_refunds_policy_page', $pid );
							} else {
								$returns_refunds_settings = get_post_meta( $pid, 'legal_page_returns_refunds_settings', true );
								$returns_refunds_options  = $returns_refunds_settings;
							}
							$data = array();
							foreach ( $returns_refunds_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_returns_refunds_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_returns_refunds_options', $data );
							break;
						case 'impressum':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Impressum' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$impressum_options = $this->wplegalpages_get_remote_data( 'get_impressum_settings' );
								update_post_meta( $pid, 'legal_page_impressum_settings', $impressum_options );
								update_option( 'wplegal_impressum_page', $pid );
							} else {
								$impressum_settings = get_post_meta( $pid, 'legal_page_impressum_settings', true );
								$impressum_options  = $impressum_settings;
							}
							$data = array();
							foreach ( $impressum_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_impressum_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_impressum_options', $data );
							break;

						case 'end_user_license':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'End User License Agreement' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$end_user_license_options = $this->wplegalpages_get_remote_data( 'get_end_user_license_settings' );
								update_post_meta( $pid, 'legal_page_end_user_license_settings', $end_user_license_options );
								update_option( 'wplegal_end_user_license_page', $pid );
							} else {
								$end_user_license_settings = get_post_meta( $pid, 'legal_page_end_user_license_settings', true );
								$end_user_license_options  = $end_user_license_settings;
							}

							$data = array();
							foreach ( $end_user_license_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_end_user_license_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}

							update_post_meta( $pid, 'legal_page_end_user_license_options', $data );
							break;

						case 'digital_goods_refund_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Digital Goods Refund Policy' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$digital_goods_refund_policy_options = $this->wplegalpages_get_remote_data( 'get_digital_goods_refund_policy_settings' );
								update_post_meta( $pid, 'legal_page_digital_goods_refund_policy_settings', $digital_goods_refund_policy_options );
								update_option( 'wplegal_digital_goods_refund_policy_page', $pid );
							} else {
								$digital_goods_refund_policy_settings = get_post_meta( $pid, 'legal_page_digital_goods_refund_policy_settings', true );
								$digital_goods_refund_policy_options  = $digital_goods_refund_policy_settings;
							}

							$data = array();
							foreach ( $digital_goods_refund_policy_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_digital_goods_refund_policy_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}

							update_post_meta( $pid, 'legal_page_digital_goods_refund_policy_options', $data );
							break;
						case 'privacy_policy':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Privacy Policy' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$privacy_options = $this->wplegalpages_get_remote_data( 'get_privacy_settings' );
								$privacy_options = self::wplegalpages_add_gdpr_options_to_remote_data( $privacy_options );
								update_post_meta( $pid, 'legal_page_privacy_settings', $privacy_options );
								update_option( 'wplegal_privacy_policy_page', $pid );
							} else {
								$privacy_settings = get_post_meta( $pid, 'legal_page_privacy_settings', true );
								$privacy_options  = $privacy_settings;
								$privacy_options  = self::wplegalpages_add_gdpr_options_to_remote_data( $privacy_options );
							}
							$data = array();
							foreach ( $privacy_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_privacy_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_privacy_options', $data );
							break;
							case 'dmca':
								if ( empty( $pid ) ) {
									$pid = $this->wplegalpages_get_pid_by_insert_page( 'DMCA' );
									update_post_meta( $pid, 'is_legal', 'yes' );
									update_post_meta( $pid, 'legal_page_type', $page );
									$dmca_options = $this->wplegalpages_get_remote_data( 'get_dmca_policy_settings' );
									update_post_meta( $pid, 'legal_page_dmca_policy_settings', $dmca_options );
									update_option( 'wplegal_dmca_page', $pid );
								} else {
									$dmca_settings = get_post_meta( $pid, 'legal_page_dmca_policy_settings', true );
									$dmca_options  = $dmca_settings;
								}
								$data = array();
								foreach ( $dmca_options as $key => $option ) {
									if ( isset( $_POST['data'][ $key ] ) ) {
										$option->checked = true;
										$fields          = $option->fields;
										$settings_data   = array();
										foreach ( $fields as $field_key => $field ) {
											$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
											$settings_data[ $field_key ] = $field_data;
										}
										$option->fields = $settings_data;
									} else {
										$option->checked = false;
									}
									$data[ $key ] = $option;
								}
								update_post_meta( $pid, 'legal_page_dmca_policy_settings', $data );
								$options = array();
								foreach ( $data as $key => $value ) {
									if ( $value->checked ) {
										if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
											$subfields = $value->fields;
											foreach ( $subfields as $sub_key => $sub_fields ) {
												$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
												$options[ $sub_key ][ $key ] = true;
											}
										}
									}
								}
								$data = array();
								foreach ( $options as $option ) {
									$data = array_merge( $data, $option );
								}
								update_post_meta( $pid, 'legal_page_dmca_policy_options', $data );
								break;
								case 'cookies_policy':
									if ( empty( $pid ) ) {
										$pid = $this->wplegalpages_get_pid_by_insert_page( 'Cookies Policy' );
										update_post_meta( $pid, 'is_legal', 'yes' );
										update_post_meta( $pid, 'legal_page_type', $page );
										$cookies_policy_options = $this->wplegalpages_get_remote_data( 'get_cookies_policy_settings' );
										update_post_meta( $pid, 'legal_page_cookies_policy_settings', $cookies_policy_options );
										update_option( 'wplegal_cookies_policy_page', $pid );
									} else {
										$cookies_policy_settings = get_post_meta( $pid, 'legal_page_cookies_policy_settings', true );
										$cookies_policy_options  = $cookies_policy_settings;
									}
									$data = array();
									foreach ( $cookies_policy_options as $key => $option ) {
										if ( isset( $_POST['data'][ $key ] ) ) {
											$option->checked = true;
											$fields          = $option->fields;
											$settings_data   = array();
											foreach ( $fields as $field_key => $field ) {
												$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
												$settings_data[ $field_key ] = $field_data;
											}
											$option->fields = $settings_data;
										} else {
											$option->checked = false;
										}
										$data[ $key ] = $option;
									}
									update_post_meta( $pid, 'legal_page_cookies_policy_settings', $data );
									$options = array();
									foreach ( $data as $key => $value ) {
										if ( $value->checked ) {
											if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
												$subfields = $value->fields;
												foreach ( $subfields as $sub_key => $sub_fields ) {
													$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
													$options[ $sub_key ][ $key ] = true;
												}
											}
										}
									}
									$data = array();
									foreach ( $options as $option ) {
										$data = array_merge( $data, $option );
									}
									update_post_meta( $pid, 'legal_page_cookies_policy_options', $data );
									break;
								case 'general_disclaimer':
										if ( empty( $pid ) ) {
											$pid = $this->wplegalpages_get_pid_by_insert_page( 'General Disclaimer' );
											update_post_meta( $pid, 'is_legal', 'yes' );
											update_post_meta( $pid, 'legal_page_type', $page );
											$general_disclaimer_options = $this->wplegalpages_get_remote_data( 'get_general_disclaimer_settings' );
											update_post_meta( $pid, 'legal_page_general_disclaimer_settings', $general_disclaimer_options );
											update_option( 'wplegal_general_disclaimer_page', $pid );
										} else {
											$general_disclaimer_settings = get_post_meta( $pid, 'legal_page_general_disclaimer_settings', true );
											$general_disclaimer_options  = $general_disclaimer_settings;
										}
										$data = array();
										foreach ( $general_disclaimer_options as $key => $option ) {
											if ( isset( $_POST['data'][ $key ] ) ) {
												$option->checked = true;
												$fields          = $option->fields;
												$settings_data   = array();
												foreach ( $fields as $field_key => $field ) {
													$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
													$settings_data[ $field_key ] = $field_data;
												}
												$option->fields = $settings_data;
											} else {
												$option->checked = false;
											}
											$data[ $key ] = $option;
										}
										update_post_meta( $pid, 'legal_page_general_disclaimer_settings', $data );
										$options = array();
										foreach ( $data as $key => $value ) {
											if ( $value->checked ) {
												if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
													$subfields = $value->fields;
													foreach ( $subfields as $sub_key => $sub_fields ) {
														$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
														$options[ $sub_key ][ $key ] = true;
													}
												}
											}
										}
										$data = array();
										foreach ( $options as $option ) {
											$data = array_merge( $data, $option );
										}
										update_post_meta( $pid, 'legal_page_general_disclaimer_options', $data );
										break;
								case 'earnings_disclaimer':
											if ( empty( $pid ) ) {
												$pid = $this->wplegalpages_get_pid_by_insert_page( 'Earnings Disclaimer' );
												update_post_meta( $pid, 'is_legal', 'yes' );
												update_post_meta( $pid, 'legal_page_type', $page );
												$earnings_disclaimer_options = $this->wplegalpages_get_remote_data( 'get_earnings_disclaimer_settings' );
												update_post_meta( $pid, 'legal_page_earnings_disclaimer_settings', $earnings_disclaimer_options );
												update_option( 'wplegal_earnings_disclaimer_page', $pid );
											} else {
												$earnings_disclaimer_settings = get_post_meta( $pid, 'legal_page_earnings_disclaimer_settings', true );
												$earnings_disclaimer_options  = $earnings_disclaimer_settings;
											}
											$data = array();
											foreach ( $earnings_disclaimer_options as $key => $option ) {
												if ( isset( $_POST['data'][ $key ] ) ) {
													$option->checked = true;
													$fields          = $option->fields;
													$settings_data   = array();
													foreach ( $fields as $field_key => $field ) {
														$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
														$settings_data[ $field_key ] = $field_data;
													}
													$option->fields = $settings_data;
												} else {
													$option->checked = false;
												}
												$data[ $key ] = $option;
											}
											update_post_meta( $pid, 'legal_page_earnings_disclaimer_settings', $data );
											$options = array();
											foreach ( $data as $key => $value ) {
												if ( $value->checked ) {
													if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
														$subfields = $value->fields;
														foreach ( $subfields as $sub_key => $sub_fields ) {
															$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
															$options[ $sub_key ][ $key ] = true;
														}
													}
												}
											}
											$data = array();
											foreach ( $options as $option ) {
												$data = array_merge( $data, $option );
											}
											update_post_meta( $pid, 'legal_page_earnings_disclaimer_options', $data );
											break;
											case 'coppa':
												if ( empty( $pid ) ) {
													$pid = $this->wplegalpages_get_pid_by_insert_page( 'COPPA - Children’s Online Privacy Policy' );
													update_post_meta( $pid, 'is_legal', 'yes' );
													update_post_meta( $pid, 'legal_page_type', $page );
													$coppa_options = $this->wplegalpages_get_remote_data( 'get_coppa_settings' );
													update_post_meta( $pid, 'legal_page_coppa_settings', $coppa_options );
													update_option( 'wplegal_coppa_policy_page', $pid );
												} else {
													$earnings_disclaimer_settings = get_post_meta( $pid, 'legal_page_coppa_settings', true );
													$earnings_disclaimer_options  = $earnings_disclaimer_settings;
												}
												$data = array();
												foreach ( $earnings_disclaimer_options as $key => $option ) {
													if ( isset( $_POST['data'][ $key ] ) ) {
														$option->checked = true;
														$fields          = $option->fields;
														$settings_data   = array();
														foreach ( $fields as $field_key => $field ) {
															$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
															$settings_data[ $field_key ] = $field_data;
														}
														$option->fields = $settings_data;
													} else {
														$option->checked = false;
													}
													$data[ $key ] = $option;
												}
												update_post_meta( $pid, 'legal_page_coppa_settings', $data );
												$options = array();
												foreach ( $data as $key => $value ) {
													if ( $value->checked ) {
														if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
															$subfields = $value->fields;
															foreach ( $subfields as $sub_key => $sub_fields ) {
																$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
																$options[ $sub_key ][ $key ] = true;
															}
														}
													}
												}
												$data = array();
												foreach ( $options as $option ) {
													$data = array_merge( $data, $option );
												}
												update_post_meta( $pid, 'legal_page_coppa_options', $data );
												break;
						case 'custom_legal':
							if ( empty( $pid ) ) {
								$pid = $this->wplegalpages_get_pid_by_insert_page( 'Custom Legal Page' );
								update_post_meta( $pid, 'is_legal', 'yes' );
								update_post_meta( $pid, 'legal_page_type', $page );
								$custom_legal_options = $lp->get_custom_legal_page_fields();
								update_post_meta( $pid, 'legal_page_custom_legal_settings', $custom_legal_options );
								update_option( 'wplegal_custom_legal_page', $pid );
							} else {
								$custom_legal_settings = get_post_meta( $pid, 'legal_page_custom_legal_settings', true );
								$custom_legal_options  = $custom_legal_settings;
							}
							$data = array();
							foreach ( $custom_legal_options as $key => $option ) {
								if ( isset( $_POST['data'][ $key ] ) ) {
									$option->checked = true;
									$fields          = $option->fields;
									$settings_data   = array();
									foreach ( $fields as $field_key => $field ) {
										$field_data                  = $this->wplegalpages_page_sections_settings_save( $field, $post_data );
										$settings_data[ $field_key ] = $field_data;
									}
									$option->fields = $settings_data;
								} else {
									$option->checked = false;
								}
								$data[ $key ] = $option;
							}
							update_post_meta( $pid, 'legal_page_custom_legal_settings', $data );
							$options = array();
							foreach ( $data as $key => $value ) {
								if ( $value->checked ) {
									if ( isset( $value->fields ) && ! empty( $value->fields ) ) {
										$subfields = $value->fields;
										foreach ( $subfields as $sub_key => $sub_fields ) {
											$options[ $sub_key ]         = $this->wplegalpages_page_sections_clauses_save( $sub_fields );
											$options[ $sub_key ][ $key ] = true;
										}
									}
								}
							}
							$data = array();
							foreach ( $options as $option ) {
								$data = array_merge( $data, $option );
							}
							update_post_meta( $pid, 'legal_page_custom_legal_options', $data );
							break;
					}
					$result['success'] = true;

				}
			}
			return wp_send_json( $result );
		}

		/**
		 * Save page settings.
		 */
		public function wplegalpages_page_settings_save() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$result = array(
				'success' => false,
			);
			$is_pro = get_option( '_lp_pro_active' );
			if ( isset( $_POST['action'] ) ) {
				$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
				if ( wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
					$lp_general       = get_option( 'lp_general', array() );
					$domain_name      = isset( $lp_general['domain'] ) ? $lp_general['domain'] : '';
					$business         = isset( $lp_general['business'] ) ? $lp_general['business'] : '';
					$trading          = isset( $lp_general['trading'] ) ? $lp_general['trading'] : '';
					$phone            = isset( $lp_general['phone'] ) ? $lp_general['phone'] : '';
					$street           = isset( $lp_general['street'] ) ? $lp_general['street'] : '';
					$city_state       = isset( $lp_general['cityState'] ) ? $lp_general['cityState'] : '';
					$country          = isset( $lp_general['country'] ) ? $lp_general['country'] : '';
					$email            = isset( $lp_general['email'] ) ? $lp_general['email'] : '';
					$date             = isset( $lp_general['date'] ) ? $lp_general['date'] : '';
					$days             = isset( $lp_general['days'] ) ? $lp_general['days'] : '';
					$duration         = isset( $lp_general['duration'] ) ? $lp_general['duration'] : '';
					$disclosing_party = isset( $lp_general['disclosing-party'] ) ? $lp_general['disclosing-party'] : '';
					$recipient_party  = isset( $lp_general['recipient-party'] ) ? $lp_general['recipient-party'] : '';
					$facebook_url     = isset( $lp_general['facebook-url'] ) ? $lp_general['facebook-url'] : '';
					$google_url       = isset( $lp_general['google-url'] ) ? $lp_general['google-url'] : '';
					$twitter_url      = isset( $lp_general['twitter-url'] ) ? $lp_general['twitter-url'] : '';
					$linkedin_url     = isset( $lp_general['linkedin-url'] ) ? $lp_general['linkedin-url'] : '';
					$address          = isset( $lp_general['address'] ) ? $lp_general['address'] : '';
					if ( isset( $_POST['data'] ) && ! empty( $_POST['data'] ) ) {
						$general                         = array();
						$general['date']                 = isset( $_POST['data']['lp-date'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-date'] ) ) : $date;
						$general['days']                 = isset( $_POST['data']['lp-days'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-days'] ) ) : $days;
						$general['duration']             = isset( $_POST['data']['lp-duration'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-duration'] ) ) : $duration;
						$general['disclosing-party']     = isset( $_POST['data']['lp-disclosing-party'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-disclosing-party'] ) ) : $disclosing_party;
						$general['recipient-party']      = isset( $_POST['data']['lp-recipient-party'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-recipient-party'] ) ) : $recipient_party;
						$general['facebook-url']         = isset( $_POST['data']['lp-facebook-url'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-facebook-url'] ) ) : $facebook_url;
						$general['google-url']           = isset( $_POST['data']['lp-google-url'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-google-url'] ) ) : $google_url;
						$general['twitter-url']          = isset( $_POST['data']['lp-twitter-url'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-twitter-url'] ) ) : $twitter_url;
						$general['linkedin-url']         = isset( $_POST['data']['lp-linkedin-url'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-linkedin-url'] ) ) : $linkedin_url;
						$general['domain']               = isset( $_POST['data']['lp-domain-name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-domain-name'] ) ) : $domain_name;
						$general['business']             = isset( $_POST['data']['lp-business-name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-business-name'] ) ) : $business;
						$general['trading']				 = isset( $_POST['data']['lp-trading-name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-trading-name'] ) ) : $trading;
						$general['phone']                = isset( $_POST['data']['lp-phone'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-phone'] ) ) : $phone;
						$general['email']                = isset( $_POST['data']['lp-email'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-email'] ) ) : $email;
						$general['street']               = isset( $_POST['data']['lp-street'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-street'] ) ) : $street;
						$general['cityState']            = isset( $_POST['data']['lp-city-state'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-city-state'] ) ) : $city_state;
						$general['country']              = isset( $_POST['data']['lp-country'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-country'] ) ) : $country;
						$general['language']             = isset( $_POST['data']['lp-lang'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-lang'] ) ) : determine_locale();
						$general['address']              = isset( $_POST['data']['lp-address'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['lp-address'] ) ) : $address;
						$general['niche']                = isset( $lp_general['niche'] ) ? $lp_general['niche'] : '';
						$general['generate']             = isset( $lp_general['generate'] ) ? $lp_general['generate'] : '0';
						$general['privacy']              = isset( $lp_general['privacy'] ) ? $lp_general['privacy'] : '0';
						$general['privacy_page']         = isset( $lp_general['privacy_page'] ) ? $lp_general['privacy_page'] : '';
						$general['leave-url']            = isset( $lp_general['leave-url'] ) ? $lp_general['leave-url'] : '';
						$general['affiliate-disclosure'] = isset( $lp_general['affiliate-disclosure'] ) ? $lp_general['affiliate-disclosure'] : '0';
						$general['refund_period']        = isset( $lp_general['refund_period'] ) ? $lp_general['refund_period'] : '';
						$general['return_period']        = isset( $lp_general['return_period'] ) ? $lp_general['return_period'] : '';
						$general['search']               = isset( $lp_general['search'] ) ? $lp_general['search'] : '0';
						$general['is_adult']             = isset( $lp_general['is_adult'] ) ? $lp_general['is_adult'] : '0';
						$general['is_footer']            = isset( $lp_general['is_footer'] ) ? $lp_general['is_footer'] : '0';
						$general['is_banner']            = isset( $lp_general['is_banner'] ) ? $lp_general['is_banner'] : '0';
						update_option( 'lp_general', $general );
						$result['success'] = true;
					}
				}
			}
			return wp_send_json( $result );
		}

		/**
		 * Get wizard steps.
		 */
		public function wplegalpages_step_settings() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$result = array(
				'success' => false,
				'data'    => array(),
			);
			if ( isset( $_GET['action'] ) ) {
				$step  = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : '';
				$page  = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'privacy_policy';
				$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
				if ( wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
					$data = array();
					switch ( $step ) {
						case 'getting_started':
							$data = $this->wplegalpages_get_page_templates();
							break;
						case 'page_settings':
							$data = $this->wplegalpages_get_page_settings( $page );
							break;
						case 'page_sections':
							$data = $this->wplegalpages_get_page_sections( $page );
							break;
						case 'page_preview':
							$data = $this->wplegalpages_get_page_preview( $page );
							break;
					}
					if ( isset( $data ) && ! empty( $data ) ) {
						$result['success'] = true;
						$result['data']    = $data;
						$result['page']    = $page;
					}
				}
			}
			return wp_send_json( $result );
		}

		/**
		 * Returns wizard getting started page templates.
		 *
		 * @return array
		 */
		public function wplegalpages_get_page_templates() {
			$data = array();
			require_once plugin_dir_path( __DIR__ ) . 'admin/wizard/class-wp-legal-pages-wizard-dashboard.php';
			$lp       = new WP_Legal_Pages_Wizard_Dashboard();
			$lp_pages = $lp->get_legal_pages();
			foreach ( $lp_pages as $lp_type_key => $lp_type ) {
				$data = array();
				foreach ( $lp_type as $key => $lpage ) {
					$pid = '';
					switch ( $key ) {
						case 'privacy_policy':
							$pid = get_option( 'wplegal_privacy_policy_page' );
							break;
						case 'terms_of_use_free':
							$pid = get_option( 'wplegal_terms_of_use_free_page' );
							break;
						case 'fb_policy':
							$pid = get_option( 'wplegal_fb_policy_page' );
							break;
						case 'dmca':
							$pid = get_option( 'wplegal_dmca_page' );
							break;
						case 'affiliate_agreement':
							$pid = get_option( 'wplegal_affiliate_agreement_page' );
							break;
						case 'affiliate_disclosure':
							$pid = get_option( 'wplegal_affiliate_disclosure_page' );
							break;
						case 'amazon_affiliate_disclosure':
							$pid = get_option( 'wplegal_amazon_affiliate_disclosure_page' );
							break;
						case 'testimonials_disclosure':
							$pid = get_option( 'wplegal_testimonials_disclosure_page' );
							break;
						case 'advertising_disclosure':
							$pid = get_option( 'wplegal_advertising_disclosure_page' );
							break;
						case 'antispam':
							$pid = get_option( 'wplegal_antispam_page' );
							break;
						case 'ftc_statement':
							$pid = get_option( 'wplegal_ftc_statement_page' );
							break;
						case 'double_dart':
							$pid = get_option( 'wplegal_double_dart_page' );
							break;
						case 'about_us':
							$pid = get_option( 'wplegal_about_us_page' );
							break;
						case 'cpra':
							$pid = get_option( 'wplegal_cpra_page' );
							break;
						case 'end_user_license':
							$pid = get_option( 'wplegal_end_user_license_page' );
							break;
						case 'digital_goods_refund_policy':
							$pid = get_option( 'wplegal_digital_goods_refund_policy_page' );
							break;
						case 'confidentiality_disclosure':
							$pid = get_option( 'wplegal_confidentiality_disclosure_page' );
							break;
						case 'general_disclaimer':
							$pid = get_option( 'wplegal_general_disclaimer_page' );
							break;
						case 'earnings_disclaimer':
							$pid = get_option( 'wplegal_earnings_disclaimer_page' );
							break;
						case 'medical_disclaimer':
							$pid = get_option( 'wplegal_medical_disclaimer_page' );
							break;
						case 'newsletters':
							$pid = get_option( 'wplegal_newsletters_page' );
							break;
						case 'standard_privacy_policy':
							$pid = get_option( 'wplegal_standard_privacy_policy_page' );
							break;
						case 'ccpa_free':
							$pid = get_option( 'wplegal_ccpa_free_page' );
							break;
						case 'terms_of_use':
							$pid = get_option( 'wplegal_terms_of_use_page' );
							break;
						case 'california_privacy_policy':
							$pid = get_option( 'wplegal_california_privacy_policy_page' );
							break;
						case 'coppa':
							$pid = get_option( 'wplegal_coppa_policy_page' );
							break;
						case 'terms_forced':
							$pid = get_option( 'wplegal_terms_forced_policy_page' );
							break;
						case 'gdpr_cookie_policy':
							$pid = get_option( 'wplegal_gdpr_cookie_policy_page' );
							break;
						case 'gdpr_privacy_policy':
							$pid = get_option( 'wplegal_gdpr_privacy_policy_page' );
							break;
						case 'cookies_policy':
							$pid = get_option( 'wplegal_cookies_policy_page' );
							break;
						case 'blog_comments_policy':
							$pid = get_option( 'wplegal_blog_comments_policy_page' );
							break;
						case 'linking_policy':
							$pid = get_option( 'wplegal_linking_policy_page' );
							break;
						case 'external_link_policy':
							$pid = get_option( 'wplegal_external_link_policy_page' );
							break;
						case 'returns_refunds_policy':
							$pid = get_option( 'wplegal_returns_refunds_policy_page' );
							break;
						case 'impressum':
							$pid = get_option( 'wplegal_impressum_page' );
							break;
						case 'custom_legal':
							$pid = get_option( 'wplegal_custom_legal_page' );
							break;
					}

					$field  = array(
						'name'        => 'policy_template',
						'label'       => $lpage['title'],
						'value'       => $key,
						'type'        => 'radio',
						'description' => $lpage['desc'],
						'pid'         => $pid,
						'pro'         => isset( $lpage['is_pro'] ) ? $lpage['is_pro'] : '',
					);
					$data[] = $field;
				}
				$return_data[ $lp_type_key ] = $data;
			}
			if ( 'Activated' !== get_option( 'wc_am_client_wplegalpages_pro_activated' ) ) {
				$lp_pro_pages = $lp->get_pro_legal_pages();
				foreach ( $lp_pro_pages as $lp_type_key => $lp_type ) {
					$data = array();
					foreach ( $lp_type as $key => $lpage ) {
						$field  = array(
							'name'        => 'policy_template',
							'label'       => $lpage['title'],
							'value'       => $key,
							'type'        => 'radio',
							'description' => $lpage['desc'],
						);
						$data[] = $field;
					}
					$pro_return_data[ $lp_type_key ] = $data;
				}
			}
			if ( empty( $pro_return_data ) ) {
				$pro_return_data = array();
			}
			return array( $return_data, $pro_return_data );
		}

		/**
		 * Returns wizard page settings.
		 *
		 * @param string $page Page.
		 * @return array
		 */
		public function wplegalpages_get_page_settings( $page ) {
			$data = array();
			require_once plugin_dir_path( __DIR__ ) . 'admin/wizard/class-wp-legal-pages-wizard-page.php';
			$lp            = new WP_Legal_Pages_Wizard_Page();
			$lp_general    = get_option( 'lp_general' );
			$selected_lang = 'en_US';
			if ( $lp_general && isset( $lp_general['language'] ) ) {
				$selected_lang = $lp_general['language'];
			}
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';

			$languages    = $lp->get_available_languages();
			$translations = wp_get_available_translations();

			$options   = array();
			$options[] = array(
				'value'    => 'en_US',
				'label'    => 'English (United States)',
				'selected' => 'en_US' === $selected_lang ? true : false,
			);
			foreach ( $languages as $locale ) {

				if ( isset( $translations[ $locale ] ) ) {
					$translation = $translations[ $locale ];
					$options[]   = array(
						'value'    => $translation['language'],
						'label'    => $translation['native_name'],
						'selected' => $translation['language'] === $selected_lang ? true : false,
					);

				}
			}

			if ( 'custom_legal' !== $page ) {
				$data[] = array(
					'name'    => 'lp-lang',
					'label'   => __( 'Select Language', 'wplegalpages' ),
					'type'    => 'select',
					'options' => $options,
				);
			}

			$lp_settings = $lp->get_setting_fields_by_page( $page );
			if ( 'custom_legal' === $page ) {
				foreach ( $lp_settings as $key => $setting ) {
					$field  = array(
						'name'      => $key,
						'label'     => $setting['title'],
						'value'     => $setting['value'],
						'type'      => 'text',
						'required'  => $setting['required'],
						'shortcode' => $setting['shortcode'],
					);
					$data[] = $field;

				}
			} else {
				foreach ( $lp_settings as $key => $setting ) {
					$field  = array(
						'name'     => $key,
						'label'    => $setting['title'],
						'value'    => $setting['value'],
						'type'     => $setting['type'] ? $setting['type'] : 'text',
						'pattern'  => $setting['pattern'],
						'error_msg' => $setting['error_msg'],
						'required' => $setting['required'],
					);
					$data[] = $field;
				}
			}

			return $data;
		}

		/**
		 * Returns wizard page sections.
		 *
		 * @param string $page Page.
		 * @return array
		 */
		public function wplegalpages_get_page_sections( $page ) {
			require_once plugin_dir_path( __DIR__ ) . 'admin/wizard/class-wp-legal-pages-wizard-page.php';
			$lp          = new WP_Legal_Pages_Wizard_Page();
			$lp_sections = (array) $lp->get_section_fields_by_page( $page );

			if ( 'privacy_policy' === $page ) {
				$lp_sections = self::wplegalpages_add_gdpr_options_to_remote_data( $lp_sections );
			}
			foreach ( $lp_sections as $key => $lp_section ) {
				if ( 'terms_of_use' === $page ) {
					if ( empty( $lp_section->fields ) ) {
						$lp_section->fields = $this->wplegalpages_get_remote_data( 'get_clause_settings?clause=' . $key );
					}
				}
				$lp_section->type    = 'heading';
				$lp_sections[ $key ] = $lp_section;
			}
			if ( 'terms_of_use' === $page ) {
				$pid = get_option( 'wplegal_terms_of_use_page' );
				update_post_meta( $pid, 'legal_page_clauses', $lp_sections );
			}

			$lp_section_fields = array();
			foreach ( $lp_sections as $key => $section ) {
				if ( ! empty( $section->fields ) ) {
					$fields         = $section->fields;
					$section_fields = array();
					foreach ( $fields as $section_key => $field ) {
						$section_fields[ $section_key ] = $field;
					}
					$section->fields = $fields;
				}
				$lp_section_fields[ $key ] = $section;
			}
			return $lp_section_fields;
		}

		/**
		 * Returns wizard page preview.
		 *
		 * @param string $page Page.
		 * @return string
		 */
		public function wplegalpages_get_page_preview( $page ) {
			require_once plugin_dir_path( __DIR__ ) . 'admin/wizard/class-wp-legal-pages-wizard-page.php';
			$lp            = new WP_Legal_Pages_Wizard_Page();
			$lp_general    = get_option( 'lp_general' );
			$selected_lang = 'en_US';
			if ( $lp_general && isset( $lp_general['language'] ) ) {
				$selected_lang = $lp_general['language'];
			}
			$preview_text = $lp->get_preview_by_page( $page, $selected_lang );
			return $preview_text;
		}

		/**
		 * Save page preview.
		 */
		public function wplegalpages_page_preview_save() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( -1 );
			}
			$result = array(
				'success' => false,
				'url'     => '',
			);
			if ( isset( $_POST['action'] ) ) {
				$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
				if ( wp_verify_nonce( $nonce, 'admin-ajax-nonce' ) ) {
					$page = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 'privacy_policy';
					switch ( $page ) {
						case 'privacy_policy':
							$pid = get_option( 'wplegal_privacy_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'terms_of_use':
							$pid = get_option( 'wplegal_terms_of_use_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'impressum':
							$pid = get_option( 'wplegal_impressum_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'terms_of_use_free':
							$pid = get_option( 'wplegal_terms_of_use_free_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'fb_policy':
							$pid = get_option( 'wplegal_fb_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'affiliate_agreement':
							$pid = get_option( 'wplegal_affiliate_agreement_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'affiliate_disclosure':
							$pid = get_option( 'wplegal_affiliate_disclosure_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'amazon_affiliate_disclosure':
							$pid = get_option( 'wplegal_amazon_affiliate_disclosure_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'testimonials_disclosure':
							$pid = get_option( 'wplegal_testimonials_disclosure_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'advertising_disclosure':
							$pid = get_option( 'wplegal_advertising_disclosure_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'confidentiality_disclosure':
							$pid = get_option( 'wplegal_confidentiality_disclosure_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'general_disclaimer':
							$pid = get_option( 'wplegal_general_disclaimer_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'medical_disclaimer':
							$pid = get_option( 'wplegal_medical_disclaimer_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'antispam':
							$pid = get_option( 'wplegal_antispam_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'ftc_statement':
							$pid = get_option( 'wplegal_ftc_statement_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'double_dart':
							$pid = get_option( 'wplegal_double_dart_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'about_us':
							$pid = get_option( 'wplegal_about_us_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'cpra':
							$pid = get_option( 'wplegal_cpra_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'end_user_license':
							$pid = get_option( 'wplegal_end_user_license_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'digital_goods_refund_policy':
							$pid = get_option( 'wplegal_digital_goods_refund_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'earnings_disclaimer':
							$pid = get_option( 'wplegal_earnings_disclaimer_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'newsletters':
							$pid = get_option( 'wplegal_newsletters_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'standard_privacy_policy':
							$pid = get_option( 'wplegal_standard_privacy_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'ccpa_free':
							$pid = get_option( 'wplegal_ccpa_free_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'dmca':
							$pid = get_option( 'wplegal_dmca_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'california_privacy_policy':
							$pid = get_option( 'wplegal_california_privacy_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'coppa':
							$pid = get_option( 'wplegal_coppa_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'terms_forced':
							$pid = get_option( 'wplegal_terms_forced_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'gdpr_cookie_policy':
							$pid = get_option( 'wplegal_gdpr_cookie_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'gdpr_privacy_policy':
							$pid = get_option( 'wplegal_gdpr_privacy_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'cookies_policy':
							$pid = get_option( 'wplegal_cookies_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'blog_comments_policy':
							$pid = get_option( 'wplegal_blog_comments_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'external_link_policy':
							$pid = get_option( 'wplegal_external_link_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'linking_policy':
							$pid = get_option( 'wplegal_linking_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'returns_refunds_policy':
							$pid = get_option( 'wplegal_returns_refunds_policy_page' );
							$url = get_edit_post_link( $pid );
							break;
						case 'custom_legal':
							$pid = get_option( 'wplegal_custom_legal_page' );
							$url = get_edit_post_link( $pid );
							delete_option( 'wplegal_custom_legal_page' );
							break;
					}
					$args    = array(
						'template' => $page,
					);
					$this->wplegalpages_send_shared_usage_data( 'LP Template Downloaded', $args );
					$url               = str_replace( '&amp;', '&', $url );
					$result['success'] = true;
					$result['url']     = $url;
				}
			}
			return wp_send_json( $result );
		}

		/**
		 * Fetch data from remote.
		 *
		 * @param string $data Data to be fetched.
		 * @return array|mixed
		 */
		public function wplegalpages_get_remote_data( $data = '' ) {
			$content         = array();
			$response        = wp_remote_get( WPLEGAL_API_URL . $data );
			$response_status = wp_remote_retrieve_response_code( $response );
			if ( 200 === $response_status ) {
				$content = json_decode( wp_remote_retrieve_body( $response ) );
			}
			return $content;
		}

		/**
		 * Return post id for the policy page.
		 *
		 * @param string $page Page.
		 * @return mixed|void
		 */
		public function wplegalpages_get_pid_by_page( $page ) {
			switch ( $page ) {
				case 'terms_of_use':
					$pid = get_option( 'wplegal_terms_of_use_page' );
					break;
				case 'california_privacy_policy':
					$pid = get_option( 'wplegal_california_privacy_policy_page' );
					break;
				case 'privacy_policy':
					$pid = get_option( 'wplegal_privacy_policy_page' );
					break;
				case 'returns_refunds_policy':
					$pid = get_option( 'wplegal_returns_refunds_policy_page' );
					break;
				case 'impressum':
					$pid = get_option( 'wplegal_impressum_page' );
					break;
				case 'custom_legal':
					$pid = get_option( 'wplegal_custom_legal_page' );
					break;
				case 'terms_of_use_free':
					$pid = get_option( 'wplegal_terms_of_use_free_page' );
					break;
				case 'fb_policy':
					$pid = get_option( 'wplegal_fb_policy_page' );
					break;
				case 'affiliate_agreement':
					$pid = get_option( 'wplegal_affiliate_agreement_page' );
					break;
				case 'affiliate_disclosure':
					$pid = get_option( 'wplegal_affiliate_disclosure_page' );
					break;
				case 'amazon_affiliate_disclosure':
					$pid = get_option( 'wplegal_amazon_affiliate_disclosure_page' );
					break;
				case 'testimonials_disclosure':
					$pid = get_option( 'wplegal_testimonials_disclosure_page' );
					break;
				case 'advertising_disclosure':
					$pid = get_option( 'wplegal_advertising_disclosure_page' );
					break;
				case 'antispam':
					$pid = get_option( 'wplegal_antispam_page' );
					break;
				case 'ftc_statement':
					$pid = get_option( 'wplegal_ftc_statement_page' );
					break;
				case 'double_dart':
					$pid = get_option( 'wplegal_double_dart_page' );
					break;
				case 'about_us':
					$pid = get_option( 'wplegal_about_us_page' );
					break;
				case 'cpra':
					$pid = get_option( 'wplegal_cpra_page' );
					break;
				case 'end_user_license':
					$pid = get_option( 'wplegal_end_user_license_page' );
					break;
				case 'digital_goods_refund_policy':
					$pid = get_option( 'wplegal_digital_goods_refund_policy_page' );
					break;
				case 'confidentiality_disclosure':
					$pid = get_option( 'wplegal_confidentiality_disclosure_page' );
					break;
				case 'general_disclaimer':
					$pid = get_option( 'wplegal_general_disclaimer_page' );
					break;
				case 'earnings_disclaimer':
					$pid = get_option( 'wplegal_earnings_disclaimer_page' );
					break;
				case 'medical_disclaimer':
					$pid = get_option( 'wplegal_medical_disclaimer_page' );
					break;
				case 'newsletters':
					$pid = get_option( 'wplegal_newsletters_page' );
					break;
				case 'standard_privacy_policy':
					$pid = get_option( 'wplegal_standard_privacy_policy_page' );
					break;
				case 'ccpa_free':
					$pid = get_option( 'wplegal_ccpa_free_page' );
					break;
				case 'coppa':
					$pid = get_option( 'wplegal_coppa_policy_page' );
					break;
				case 'terms_forced':
					$pid = get_option( 'wplegal_terms_forced_policy_page' );
					break;
				case 'gdpr_cookie_policy':
					$pid = get_option( 'wplegal_gdpr_cookie_policy_page' );
					break;
				case 'gdpr_privacy_policy':
					$pid = get_option( 'wplegal_gdpr_privacy_policy_page' );
					break;
				case 'cookies_policy':
					$pid = get_option( 'wplegal_cookies_policy_page' );
					break;
				case 'blog_comments_policy':
					$pid = get_option( 'wplegal_blog_comments_policy_page' );
					break;
				case 'linking_policy':
					$pid = get_option( 'wplegal_linking_policy_page' );
					break;
				case 'external_link_policy':
					$pid = get_option( 'wplegal_external_link_policy_page' );
					break;
				case 'dmca':
					$pid = get_option( 'wplegal_dmca_page' );
					break;
			}
			return $pid;
		}

		/**
		 * Adds gdpr scanned services to remote data from api.
		 *
		 * @param  object $lp_sections data.
		 * @return object
		 */
		public static function wplegalpages_add_gdpr_options_to_remote_data( $lp_sections ) {

			if ( ! file_exists( WP_PLUGIN_DIR . '/wpl-cookie-consent' ) ) {

				$gdpr_services = array(
					'id'          => 'gdpr_third_party_services',
					'title'       => '<strong>To scan website for third-party services, install and activate plugin <a href="https://club.wpeka.com/product/wp-gdpr-cookie-consent/" target="_blank">GDPR Cookie Consent Pro</a>.</strong>',
					'description' => '',
					'type'        => 'section',
					'position'    => 1,
					'parent'      => 'allow_third_party_yes',
					'collapsible' => '',
					'sub_fields'  => '',

				);

				$lp_sections = self::wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services );
				return $lp_sections;

			}

			if ( ! is_plugin_active( 'wpl-cookie-consent/wpl-cookie-consent.php' ) ) {

				$gdpr_services = array(
					'id'          => 'gdpr_third_party_services',
					'title'       => '<strong><a href="' . admin_url() . 'plugins.php" target="_blank">Activate</a> plugin GDPR Cookie Consent Pro, to scan for third-party services on your website.</strong>',
					'description' => '',
					'type'        => 'section',
					'position'    => 1,
					'parent'      => 'allow_third_party_yes',
					'collapsible' => '',
					'sub_fields'  => '',

				);

				$lp_sections = self::wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services );
				return $lp_sections;
			}

			global $wpdb;
			$scan_table = $wpdb->prefix . 'wpl_cookie_scan';
			$sql        = "SELECT * FROM `$scan_table` ORDER BY id_wpl_cookie_scan DESC LIMIT 1";
			$last_scan  = $wpdb->get_row( $sql, ARRAY_A ); //phpcs:ignore

			if ( $last_scan ) {
				$last_scan = gmdate( 'F j, Y g:i a T', $last_scan['created_at'] );

				$gdpr_services = array();
				$cookies_table = $wpdb->prefix . 'wpl_cookie_scan_cookies';

				$args = array(
					'post_type'   => 'gdprpolicies',
					'post_status' => 'publish',
				);

				$gdpr_services = new WP_Query( $args );
				if ( $gdpr_services->have_posts() ) {
						$gdpr_services_fields  = array();
						$gdpr_service_position = 1;
					while ( $gdpr_services->have_posts() ) {
						$gdpr_services->the_post();

						$service_name          = str_replace( ' ', '_', get_the_title() );
						$service_name_prefixed = get_the_ID() . 'gdpr_scanned_' . $service_name;
						$gdpr_sub_fields       = self::wplegalpages_get_to_path( $lp_sections, array( 'general_information', 'fields', 'allow_third_party', 'sub_fields', 'allow_third_party_yes', 'sub_fields', 'gdpr_third_party_services', 'sub_fields' ) );

						if ( ! isset( $gdpr_sub_fields->$service_name_prefixed ) ) {

								$gdpr_services_fields[ $service_name_prefixed ] = (object) array(
									'id'          => $service_name_prefixed,
									'title'       => get_the_title(),
									'description' => '',
									'type'        => 'checkbox',
									'position'    => $gdpr_service_position,
									'name'        => $service_name_prefixed,
									'value'       => 1,
									'checked'     => true,
									'sub_fields'  => '',
								);
						} else {
							$gdpr_services_fields[ $service_name_prefixed ] = (object) array(
								'id'          => $service_name_prefixed,
								'title'       => get_the_title(),
								'description' => '',
								'type'        => 'checkbox',
								'position'    => $gdpr_service_position,
								'name'        => $service_name_prefixed,
								'value'       => 1,
								'checked'     => $gdpr_sub_fields->$service_name_prefixed->checked,
								'sub_fields'  => '',
							);
						}

								++$gdpr_service_position;

					}
					wp_reset_postdata();

					$gdpr_services = array(
						'id'          => 'gdpr_third_party_services',
						'title'       => '<strong>Last scan:</strong> ' . $last_scan . '<br>Third-party services detected:',
						'description' => '',
						'type'        => 'section',
						'position'    => 1,
						'parent'      => 'allow_third_party_yes',
						'collapsible' => '',
						'sub_fields'  => (object) $gdpr_services_fields,

					);

							$lp_sections = self::wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services );
							return $lp_sections;

				} else {
					$gdpr_services = array(
						'id'          => 'gdpr_third_party_services',
						'title'       => '<strong>Last scan:</strong> ' . $last_scan . '<br>No third-party services detected.',
						'description' => '',
						'type'        => 'section',
						'position'    => 1,
						'parent'      => 'allow_third_party_yes',
						'collapsible' => '',
						'sub_fields'  => '',

					);
					$lp_sections = self::wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services );
					return $lp_sections;

				}
			} else {

				$gdpr_services = array(
					'id'          => 'gdpr_third_party_services',
					'title'       => '<strong><a href="' . admin_url() . 'admin.php?page=gdpr-cookie-consent#cookie_settings#cookie_list" target="_blank">Scan now</a> to automatically detect third-party services on your website.</strong>',
					'description' => '',
					'type'        => 'section',
					'position'    => 1,
					'parent'      => 'allow_third_party_yes',
					'collapsible' => '',
					'sub_fields'  => '',

				);
				$lp_sections = self::wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services );
				return $lp_sections;

			}
		}

		/**
		 * Set order of allowed third party services.
		 *
		 * @param  object $lp_sections data.
		 * @param  object $gdpr_services data.
		 * @return object
		 */
		public static function wplegalpages_set_gdpr_options_order( $lp_sections, $gdpr_services ) {
			$lp_sections                                = (array) $lp_sections;
			$lp_sections['general_information']->fields = (object) $lp_sections['general_information']->fields;

			$stored_third_party_services                                        = self::wplegalpages_get_to_path( $lp_sections, array( 'general_information', 'fields', 'allow_third_party', 'sub_fields', 'allow_third_party_yes', 'sub_fields', 'third_party_services' ) );
			$allowed_third_party_yes_sub_fields                                 = (object) array();
			$allowed_third_party_yes_sub_fields->gdpr_third_party_services      = (object) $gdpr_services;
			$allowed_third_party_yes_sub_fields->third_party_services           = $stored_third_party_services;
			$allowed_third_party_yes_sub_fields->third_party_services->position = 2;

			$lp_sections['general_information']->fields->allow_third_party->sub_fields->allow_third_party_yes->sub_fields = $allowed_third_party_yes_sub_fields;
			return $lp_sections;
		}

		/**
		 * Adds description to selected gdpr scanned policies and send as options to api.
		 *
		 * @param  object $options options to be sent to api.
		 * @return object
		 */
		public static function wplegalpages_add_gdpr_policy_description( $options ) {
			foreach ( $options as $key => $value ) {

				if ( strpos( $key, 'gdpr_scanned_' ) !== false ) {

					$service_id   = substr( $key, 0, strpos( $key, 'gdpr_scanned_' ) );
					$purpose      = get_post_field( 'post_content', $service_id );
					$links        = get_post_meta( $service_id, '_gdpr_policies_links_editor', true );
					$links        = explode( "\n", $links );
					$privacy_link = '';
					$opt_out_link = '';
					foreach ( $links as $link ) {

						if ( $link ) {
							$dom = new DomDocument();
							$dom->loadHTML( $link );
							$output = array();
							foreach ( $dom->getElementsByTagName( 'a' ) as $item ) {

								if ( strpos( $link, 'privacy' ) !== false ) {
									if ( ! $privacy_link ) {
										$privacy_link = $item->getAttribute( 'href' );
									}
								}
								if ( strpos( $link, 'Opt Out' ) !== false ) {
									if ( ! $opt_out_link ) {
										$opt_out_link = $item->getAttribute( 'href' );
									}
								}
							}
						}
					}

					$service_description  = $purpose . '<br>';
					$service_description .= $privacy_link ? '<a href=' . $privacy_link . ' target="_blank">Privacy Policy</a>' : '';
					$service_description .= $privacy_link && $opt_out_link ? ' - ' : '';
					$service_description .= $opt_out_link ? '<a href=' . $opt_out_link . ' target="_blank">Opt Out</a>' : '';
					$options[ 'gs_' . str_replace( $service_id . 'gdpr_scanned_', '', $key ) . '_description' ] = $service_description;

				}
			}
			return $options;
		}

		/**
		 * Delete entry from options when policy page is moved to trash
		 *
		 * @param int $post_id Post ID.
		 */
		public function wplegalpages_pro_trash_post( $post_id ) {
			$legal_page_type = get_post_meta( $post_id, 'legal_page_type', true );
			if ( $legal_page_type && ! empty( $legal_page_type ) ) {
				switch ( $legal_page_type ) {
					case 'terms_of_use':
						delete_option( 'wplegal_terms_of_use_page' );
						break;
					case 'california_privacy_policy':
						delete_option( 'wplegal_california_privacy_policy_page' );
						break;
					case 'privacy_policy':
						delete_option( 'wplegal_privacy_policy_page' );
						break;
					case 'returns_refunds_policy':
						delete_option( 'wplegal_returns_refunds_policy_page' );
						break;
					case 'impressum':
						delete_option( 'wplegal_impressum_page' );
						break;
					case 'standard_privacy_policy':
						delete_option( 'wplegal_standard_privacy_policy_page' );
						break;
					case 'terms_of_use_free':
						delete_option( 'wplegal_terms_of_use_free_page' );
						break;
					case 'dmca':
						delete_option( 'wplegal_dmca_page' );
						break;
					case 'ccpa_free':
						delete_option( 'wplegal_ccpa_free_page' );
						break;
					case 'coppa':
						delete_option( 'wplegal_coppa_policy_page' );
						break;
					case 'terms_forced':
						delete_option( 'wplegal_terms_forced_policy_page' );
						break;
					case 'gdpr_cookie_policy':
						delete_option( 'wplegal_gdpr_cookie_policy_page' );
						break;
					case 'gdpr_privacy_policy':
						delete_option( 'wplegal_gdpr_privacy_policy_page' );
						break;
					case 'cookies_policy':
						delete_option( 'wplegal_cookies_policy_page' );
						break;
					case 'blog_comments_policy':
						delete_option( 'wplegal_blog_comments_policy_page' );
						break;
					case 'linking_policy':
						delete_option( 'wplegal_linking_policy_page' );
						break;
					case 'external_link_policy':
						delete_option( 'wplegal_external_link_policy_page' );
						break;
					case 'fb_policy':
						delete_option( 'wplegal_fb_policy_page' );
						break;
					case 'affiliate_disclosure':
						delete_option( 'wplegal_affiliate_disclosure_page' );
						break;
					case 'amazon_affiliate_disclosure':
						delete_option( 'wplegal_amazon_affiliate_disclosure_page' );
						break;
					case 'testimonials_disclosure':
						delete_option( 'wplegal_testimonials_disclosure_page' );
						break;
					case 'advertising_disclosure':
						delete_option( 'wplegal_advertising_disclosure_page' );
						break;
					case 'confidentiality_disclosure':
						delete_option( 'wplegal_confidentiality_disclosure_page' );
						break;
					case 'general_disclaimer':
						delete_option( 'wplegal_general_disclaimer_page' );
						break;
					case 'earnings_disclaimer':
						delete_option( 'wplegal_earnings_disclaimer_page' );
						break;
					case 'medical_disclaimer':
						delete_option( 'wplegal_medical_disclaimer_page' );
						break;
					case 'newsletters':
						delete_option( 'wplegal_newsletters_page' );
						break;
					case 'affiliate_agreement':
						delete_option( 'wplegal_affiliate_agreement_page' );
						break;
					case 'antispam':
						delete_option( 'wplegal_antispam_page' );
						break;
					case 'ftc_statement':
						delete_option( 'wplegal_ftc_statement_page' );
						break;
					case 'double_dart':
						delete_option( 'wplegal_double_dart_page' );
						break;
					case 'about_us':
						delete_option( 'wplegal_about_us_page' );
						break;
					case 'cpra':
						delete_option( 'wplegal_cpra_page' );
						break;
					case 'end_user_license':
						delete_option( 'wplegal_end_user_license_page' );
						break;
					case 'digital_goods_refund_policy':
						delete_option( 'wplegal_digital_goods_refund_policy_page' );
						break;
					case 'custom_legal':
						if ( intval( get_option( 'wplegal_custom_legal_page' ) ) === intval( $post_id ) ) {
							delete_option( 'wplegal_custom_legal_page' );
						}
						break;
				}
				echo '<script>window.location.replace("' . esc_js( esc_url( admin_url( 'admin.php?page=legal-pages#all_legal_pages' ) ) ) . '");</script>';
			}
		}

		/**
		 * Get to the path of required directory from the stored or remote data.
		 *
		 * @param  object $directory data.
		 * @param  array  $path values of sub directories.
		 * @return object
		 */
		public static function wplegalpages_get_to_path( $directory, $path ) {
			$current_dir = $directory;
			foreach ( $path as $path ) {

				if ( is_array( $current_dir ) ) {
					if ( isset( $current_dir[ $path ] ) ) {
						$current_dir = $current_dir[ $path ];
					} else {
						return false;
					}
				} elseif ( is_object( $current_dir ) ) {
					if ( isset( $current_dir->$path ) ) {
						$current_dir = $current_dir->$path;

					} else {
						return false;
					}
				}
			}

			return $current_dir;
		}

		/**
		 * Create Popup Delete Process.
		 *
		 * @since 2.3.5
		 */
		public function create_popup_delete_process() {
			global $wpdb;

			if ( class_exists( 'WP_Legal_Pages' ) ) {
				$lp_obj = new WP_Legal_Pages();
			}

			if ( isset( $_REQUEST['mode'] ) && 'deletepopup' === $_REQUEST['mode'] && current_user_can( 'manage_options' ) ) {
				if ( isset( $_REQUEST['nonce'] ) ) {
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) );
					
					// Verify the nonce and stop execution if it is invalid.
					if ( ! wp_verify_nonce( $nonce, 'lp-submit-create-popups' ) ) {
						wp_die( esc_html__( 'Security check failed. Please try again.', 'wplegalpages' ) );
					}
				} else {
					wp_die( esc_html__( 'Missing nonce. Please try again.', 'wplegalpages' ) );
				}

				$lpid = isset( $_REQUEST['lpid'] ) ? intval( sanitize_text_field( wp_unslash( $_REQUEST['lpid'] ) ) ) : 0;

				// Proceed only if a valid ID is provided.
				if ( $lpid > 0 ) {
					$wpdb->delete( $lp_obj->popuptable, array( 'id' => $lpid ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				}

				wp_redirect( admin_url( 'admin.php?page=legal-pages#create_popup' ) );
				exit; // Always exit after a redirect.
			}
		}


		/**
		 * Create Popup Edit Process.
		 *
		 * @since 2.3.5
		 */
		public function create_popup_edit_process() {

				global $wpdb;

			if ( class_exists( 'WP_Legal_Pages' ) ) {

				$lp_obj = new WP_Legal_Pages();
			}

			if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'legal-pages' ) ) {
				$flag = false;

				if ( isset( $_REQUEST['mode'] ) && 'edit' === $_REQUEST['mode'] && isset( $_REQUEST['lpid'] ) ) {

					$lpid = isset( $_REQUEST['lpid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['lpid'] ) ) : 0;
					$row  = $wpdb->get_row( $wpdb->prepare( 'SELECT * from ' . $lp_obj->popuptable . ' where id=%d', $lpid ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

					if ( $row ) {

						// The option key
						$option_key = 'wplegalpalges_create_popup';

						$serialized_object = serialize( $row );
						// The new option value
						$option_value = $serialized_object;

						// Check if the option key already exists
						$existing_value = get_option( $option_key );

						if ( false === $existing_value ) {
							// Option key doesn't exist, so add it
							add_option( $option_key, $option_value );
						} else {
							// Option key exists, so update its value
							update_option( $option_key, $option_value );
						}

						// flag key

						$option_key_flag = 'wplegalpalges_flag_key';
						$flag            = true;
						// Check if the option key already exists
						$existing_value_flag = get_option( $option_key_flag );

						if ( false === $existing_value_flag ) {
							// Option key doesn't exist, so add it
							add_option( $option_key_flag, $flag );
						} else {

							// Option key exists, so update its value
							update_option( $option_key_flag, $flag );
						}
					}

					wp_redirect( admin_url( 'admin.php?page=legal-pages#create_popup' ) );

				}
			}
		}

		/**
		 * Displays admin notices related to WPLegalPages plugin.
		 *
		 * This function is responsible for displaying admin notices based on the
		 * connection status of the user to the WPLegalPages plugin.
		 *
		 * @since 3.0.0
		 */
		public function wplegal_api_admin_notices() {

			require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';

			// Instantiate a new object of the wplegal_Cookie_Consent_Settings class.
			$this->settings = new WP_Legal_Pages_Settings();

			// Call the is_connected() method from the instantiated object to check if the user is connected.
			$is_user_connected = $this->settings->is_connected();
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		
		// GDPR Plugin Installation code
		public function wplp_gdpr_install_plugin_ajax_handler() {
			// Check nonce for security
			check_ajax_referer( 'wp-legal-pages', '_ajax_nonce' );
		
		
			// Load necessary WordPress plugin installer classes
			if ( ! class_exists( 'Plugin_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}
		
			$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';
		
			// Get plugin information
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin_slug,
					'fields' => array(
						'sections' => false,
					),
				)
			);
		
			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}
		
			// Install the plugin
			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
		
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}
		
			// Activate the plugin
			$activate = activate_plugin( $plugin_slug . '/' . $plugin_slug . '.php' );
			if ( is_wp_error( $activate ) ) {
				wp_send_json_error( array( 'message' => $activate->get_error_message() ) );
			}
		
			// Success response
			wp_send_json_success( array( 'message' => __( 'Plugin installed and activated successfully.', 'wplegalpages' ) ) );
		}



	public function wplegalpages_support_request_handler() {
		// Verify nonce for security
		if (! isset( $_POST['wplegalpages_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wplegalpages_nonce'] ) ), 'wplegalpages_support_request_nonce' )) {
			wp_send_json_error(['message' => 'Security check failed.']);
		}

		// Sanitize and validate input
		$name = isset( $_POST['name'] ) ? sanitize_text_field(wp_unslash( $_POST['name'] )) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email(wp_unslash( $_POST['email'] )) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field(wp_unslash( $_POST['message'] )) : '';

		// Support email details
		$to = "hello@wpeka.com"; // Replace with your support email
		$subject = "Support Request from $name";
		$body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
		$headers = ['Reply-To: ' . $email];

		// Send the email and respond with JSON
		if (wp_mail($to, $subject, $body, $headers)) {
			wp_send_json_success(['message' => 'Your message has been sent successfully.']);
		} else {
			wp_send_json_error(['message' => 'There was an error sending your message. Please try again later.']);
		}
	}
	public function wplp_admin_new_clause_addition_notice(){
		$screen = get_current_screen();
		// Show notice only on Plugins page
		if ($screen->id !== 'plugins') {
			return;
		}
	
		// Check if dismissed
		if (get_option('wplp_template_notice_dismissed')) {
			return;
		}
		// Dismiss Notice via URL
		if (isset($_GET['wplp_template_dismiss_notice'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			update_option('wplp_template_notice_dismissed', true);
			return;
		}
		?>
	
		<div class="notice notice-success">
			<div style="display: flex; justify-content: space-between; align-items: center;padding: 5px;">
			<div style="max-width: 80%;">
				<p style="margin: 0;">
					<strong><?php esc_html_e('WP Legal Pages: ','wplegalpages'); ?></strong>
					<?php esc_html_e('We\'ve recently updated our DMCA, Professional Privacy Policy, COPPA, General Disclaimer, Earnings Disclaimer, Terms and Conditions Pro, and Cookies Policy templates. These updates include additional clauses related to AI-generated media usage to help you stay compliant with evolving standards. Kindly update your policies to stay compliant.', 'wplegalpages'); ?>
				</p>
			</div>
				<a href="<?php echo esc_url(add_query_arg('wplp_template_dismiss_notice', '1')); ?>" class="button"><?php esc_html_e('Dismiss','wplegalpages'); ?></a>
			</div>
		</div>

		<?php
	}

		/**
		 * Fetches the Age Verification popup markup from the WP Legal Pages API.
		 *
		 * Constructs the API request URL by switching to v1 of the endpoint and appending 
		 * necessary query arguments based on site options such as minimum age, button texts, 
		 * and redirect URL. Sends a GET request to retrieve the popup markup.
		 *
		 * @return array|null The decoded JSON response containing the markup, or null on failure.
		 */
		public function wplegalpages_fetch_age_verification_popup_markup() {
			
			$api_url = WPLEGAL_API_URL;

			$updated_api_url = str_replace('/v2/', '/v1/', $api_url);

			$url = $updated_api_url . 'get_age_verification_popup_markup';

			$lp_show_improved_ui = true;
			$lp_pro_installed    = get_option( '_lp_pro_installed' );
			if ( $lp_pro_installed && get_option( 'wplegalpages_pro_version' ) && version_compare( get_option( 'wplegalpages_pro_version' ), '8.4.0' ) < 0 ) {
				$lp_show_improved_ui = false;
			}
			$minimum_age       = get_option( '_lp_minimum_age' );
			$yes_button_text   = get_option( 'lp_eu_button_text' );
			$no_button_text    = get_option( 'lp_eu_button_text_no' );
			$redirect_url_text = get_option( '_lp_redirect_url' );

			$args = array(
				'source'              => 'wplegalpages',
				'lp_show_improved_ui' => $lp_show_improved_ui,
				'minimum_age'         => $minimum_age,
				'yes_button_text'     => $yes_button_text,
				'no_button_text'      => $no_button_text,
				'redirect_url_text'   => $redirect_url_text,
			);

			$request_url = add_query_arg( $args, $url );

			$response = wp_remote_get( $request_url, array( 'timeout' => 10 ) );

			$status_code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== (int) $status_code ) {
				return;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			return $body;
		}

		/**
		 * Handles AJAX request when the upgrade to Pro popup is clicked.
		 *
		 * Sends event data to server and returns a JSON success response.
		 */
		public function wplegalpages_upgrade_to_pro_popup_clicked() {

			$response = $this->wplegalpages_send_shared_usage_data( 'LP Upgrade Popup' );
			if ( $response ) {
				wp_send_json_success(
					array(
						'message' => 'Data Sent Successfully',
					)
				);
			} else {
				wp_send_json_success(
					array(
						'message' => 'Failed to Send Data',
					)
				);
			}
		}

		/**
		 * Determines the operating system of the user based on the HTTP User-Agent string.
		 *
		 * @return string The detected operating system name or 'Unknown OS' if not identified.
		 */
		public function wplegalpages_get_user_os() {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
			if ( stripos( $user_agent, 'Windows' ) !== false ) return 'Windows';
			if ( stripos( $user_agent, 'Mac' ) !== false ) return 'Mac OS';
			if ( stripos( $user_agent, 'Linux' ) !== false ) return 'Linux';
			if ( stripos( $user_agent, 'Android' ) !== false ) return 'Android';
			if ( stripos( $user_agent, 'iPhone' ) !== false || stripos( $user_agent, 'iPad' ) !== false ) return 'iOS';
		
			return 'Unknown OS';
		}

		/**
		 * Determines the type of device based on the user agent.
		 *
		 * This function inspects the `HTTP_USER_AGENT` server variable to identify whether 
		 * the device is a mobile, tablet, or desktop.
		 *
		 * @return string Returns 'Mobile' if a mobile device is detected, 'Tablet' if a tablet is detected, 
		 *               and 'Desktop' otherwise.
		 */
		public function wplegalpages_get_device_type() {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];

			if ( preg_match( '/mobile|android|iphone|ipod|blackberry|opera mini|windows phone|webos/i', $user_agent ) ) {
				return 'Mobile';
			}

			if ( preg_match( '/tablet|ipad/i', $user_agent ) ) {
				return 'Tablet';
			}

			return 'Desktop';
		}

		/**
		 * Retrieves the user's IP address.
		 *
		 * This function checks various server variables to determine the user's IP address,
		 * including `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, and `REMOTE_ADDR`.
		 *
		 * @return string The detected IP address of the user.
		 */
		public function wplegalpages_get_user_ip() {
			if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				return $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				return $_SERVER['REMOTE_ADDR'];
			}
		}

		/**
		 * Retrieves the user's country code based on their IP address using the country.is API.
		 *
		 * @return string|null Two-letter country code (e.g., 'US') or null on failure.
		 */
		public function wplegalpages_get_user_country() {
			$geoData = wp_remote_get( "https://api.country.is/" );
		
			if ( is_wp_error( $geoData ) ) {
				return null;
			}
		
			$body = wp_remote_retrieve_body( $geoData );
			$data = json_decode( $body, true );
			
			return $data['country'];
		}

		/**
		 * Sends shared usage data if opt-in is allowed.
		 *
		 * @param string $event Event name to be tracked.
		 * @param array  $args  Optional. Additional event-specific data to send.
		 *
		 * @return bool True on successful request, false otherwise or if opt-in is not enabled.
		 */
		public function wplegalpages_send_shared_usage_data( $event, $args = array() ) {

			if ( ! get_option( 'wplegalpages-ask-for-usage-optin' ) ) {
				return false;
			}
	
			$url     = WPLEGAL_APP_URL . '/wp-json/api/v1/plugin/app_wplp_collect_shared_usage_data';
			$user_id = get_current_user_id();
			
			if ( $user_id ) { 
				$user       = get_userdata( $user_id );
				$user_email = $user ? $user->user_email : null;
			} else {
				$user_email = null;
			}
		
			$data = array(
				'event'       => $event,
				'src'         => 'wplegalpages',
				'site_url'    => site_url(),
				'email'       => $user_email,
				'os_name'     => $this->wplegalpages_get_user_os(),
				'device_type' => $this->wplegalpages_get_device_type(),
				'ip'          => $this->wplegalpages_get_user_ip(),
				'country'     => $this->wplegalpages_get_user_country(),
				'time'        => time() * 1000,
				'args'        => $args,
			);
	
			$response = wp_safe_remote_post(
				$url,
				array(
					'body'    => json_encode($data),
					'headers' => array( 'Content-Type' => 'application/json' ),
					'method'  => 'POST',
					'timeout' => 20,
				)
			);

			if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
				return true;
			}
			return false;
		}
	}
	
	
}
