<?php
/**
 * The file that defines the core WPLegalPages class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wplegalpages.com/
 * @since      1.5.2
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 */

/**
 * The core WPLegalPages class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this WPLegalPages as well as the current
 * version of the WPLegalPages.
 *
 * @since      1.5.2
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 * @author     WPEka <support@wplegalpages.com>
 */
if ( ! class_exists( 'WP_Legal_Pages' ) ) {
	/**
	 * The core WPLegalPages class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this WPLegalPages as well as the current
	 * version of the WPLegalPages.
	 *
	 * @since      1.5.2
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/includes
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages {
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the WPLegalPages.
		 *
		 * @since    1.5.2
		 * @access   protected
		 * @var      WP_Legal_Pages_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */

		protected $loader;

		/**
		 * API endpoint for wp legalpages
		 *
		 * @since    1.5.2
		 * @access   protected
		 */
		protected $respadons_api;

		/**
		 * 
		 *
		 * @since    1.5.2
		 * @access   protected
		 */
		protected $library_auth;
		/**
		 * The unique identifier of WPLegalPages.
		 *
		 * @since    1.5.2
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		public $plugin_name;

		/**
		 * The root url of the plugin folder.
		 *
		 * @since    1.5.2
		 * @access   protected
		 */
		public $plugin_url;

		/**
		 * popup table
		 *
		 * @since    1.5.2
		 * @access   protected
		 */
		public $popuptable;

		/**
		 * The current version of the WPLegalPages.
		 *
		 * @since    1.5.2
		 * @access   protected
		 * @var      string    $version    The current version of the WPLegalPages.
		 */

		 /**
		 * Table Name
		 *
		 * @since    1.5.2
		 * @access   protected
		 */
		public $tablename;

		public $version;

		/**
		 * Define the core functionality of the WPLegalPages.
		 *
		 * Set the WPLegalPages name and the WPLegalPages version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.5.2
		 */
		public function __construct() {

			global $table_prefix;
			$this->plugin_name = 'wp-legal-pages';
			$this->version     = '3.2.7';
			$this->tablename   = $table_prefix . 'legal_pages';
			$this->popuptable  = $table_prefix . 'lp_popups';
			$this->plugin_url  = plugin_dir_path( __DIR__ );
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
		}

		/**
		 * What type of request is this?
		 *
		 * @since 2.3.9
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
			}
		}

		/**
		 * Load the required dependencies for WPLegalPages.
		 *
		 * Include the following files that make up the WPLegalPages:
		 *
		 * - WP_Legal_Pages_Loader. Orchestrates the hooks of the plugin.
		 * - WP_Legal_Pages_I18n. Defines internationalization functionality.
		 * - WP_Legal_Pages_Admin. Defines all hooks for the admin area.
		 * - WP_Legal_Pages_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.5.2
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core WP_Legal_Pages.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'includes/class-wp-legal-pages-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the WP_Legal_Pages.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'includes/class-wp-legal-pages-i18n.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'admin/class-wp-legal-pages-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'public/class-wp-legal-pages-public.php';

			/**
			 * The class responsible for defining widget specific functionality
			 * of the WP_Legal_Pages.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'widgets/class-wp-widget-legal-pages.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the WP_Legal_Pages.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'includes/class-wp-legal-app-auth.php';
			$this->library_auth = new WP_Legal_Pages_App_Auth();

			require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-api.php';
			$this->respadons_api = new WP_Legal_Pages_Api();

			$this->loader = new WP_Legal_Pages_Loader();
		}

		/**
		 * Define the locale for this WP_Legal_Pages for internationalization.
		 *
		 * Uses the WP_Legal_Pages_I18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.5.2
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new WP_Legal_Pages_I18n();
			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the WP_Legal_Pages.
		 *
		 * @since    1.5.2
		 * @access   private
		 */
		private function define_admin_hooks() {
			$plugin_admin = new WP_Legal_Pages_Admin( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'wplegalpages_hidden_meta_boxes' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'wplegal_admin_init' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			$this->loader->add_action( 'wp_ajax_lp_accept_terms', $plugin_admin, 'wplegal_accept_terms' );
			$this->loader->add_action( 'wp_ajax_nopriv_lp_accept_terms', $plugin_admin, 'wplegal_accept_terms' );
			$this->loader->add_filter( 'plugin_action_links_' . WPL_LITE_PLUGIN_BASENAME, $plugin_admin, 'wplegal_plugin_action_links' );
			$this->loader->add_action( 'wp_ajax_get_accept_terms', $plugin_admin, 'wplegal_get_accept_terms' );
			$this->loader->add_action( 'wp_ajax_save_accept_terms', $plugin_admin, 'wplegal_save_accept_terms' );
			$this->loader->add_filter( 'nav_menu_meta_box_object', $plugin_admin, 'wplegalpages_add_menu_meta_box', 10, 1 );
			$this->loader->add_action( 'wp_ajax_wplegalpages_disable_settings_warning', $plugin_admin, 'wplegalpages_disable_settings_warning', 10, 1 );
			$this->loader->add_action( 'wp_ajax_lp_save_admin_settings', $plugin_admin, 'wplegalpages_ajax_save_settings', 10, 1 );
			$this->loader->add_filter( 'style_loader_src', $plugin_admin, 'wplegalpages_dequeue_styles' );
			$this->loader->add_filter( 'print_styles_array', $plugin_admin, 'wplegalpages_remove_forms_style' );
			$this->loader->add_action( 'wp_ajax_lp_save_footer_form', $plugin_admin, 'wplegalpages_save_footer_form' );
			$this->loader->add_filter( 'wp_ajax_save_banner_form', $plugin_admin, 'wplegalpages_save_banner_form' );
			$this->loader->add_action( 'wp_ajax_save_cookie_bar_form', $plugin_admin, 'wplegalpages_save_cookie_bar_form' );
			$this->loader->add_action( 'post_updated', $plugin_admin, 'wplegalpages_post_updated', 10, 1 );
			$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'wplegalpages_trash_page', 10, 1 );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'setup_legal_wizard' );
			$this->loader->add_action( 'wp_ajax_step_settings', $plugin_admin, 'wplegalpages_step_settings' );
			$this->loader->add_action( 'wp_ajax_page_settings_save', $plugin_admin, 'wplegalpages_page_settings_save' );
			$this->loader->add_action( 'wp_ajax_page_sections_save', $plugin_admin, 'wplegalpages_page_sections_save' );
			$this->loader->add_action( 'wp_ajax_page_preview_save', $plugin_admin, 'wplegalpages_page_preview_save' );
			$this->loader->add_action( 'wp_trash_post', $plugin_admin, 'wplegalpages_pro_trash_post' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'create_popup_delete_process' );
			$this->loader->add_action( 'admin_init', $plugin_admin, 'create_popup_edit_process' );
			// action to add admin notice for api connections
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'wplegal_api_admin_notices' );
			// Hookes of age verification and create popup.
			$this->loader->add_action( 'wplegalpages_save_settings', $plugin_admin, 'wplegalpages_pro_save_settings', 10, 2 );
			$this->loader->add_action( 'wp_ajax_save_age_form', $plugin_admin, 'wplegalpages_pro_save_age_form' );
			$this->loader->add_action( 'wp_ajax_save_popup_form', $plugin_admin, 'wplegalpages_pro_save_popup_form' );
			$this->loader->add_filter( 'wplegalpages_compliances_options', $plugin_admin, 'wplegalpages_pro_compliances_options', 10, 1 );
			$this->loader->add_filter( 'wplegalpages_shortcodes_table', $plugin_admin, 'wplegalpages_pro_shortcodes_table', 10, 1 );
			$this->loader->add_filter( 'wplegalpages_shortcode_content', $plugin_admin, 'wplegalpages_pro_shortcode_content', 10, 1 );
			$this->loader->add_action( 'init', $plugin_admin, 'wplegalpages_pro_register_block_type' );

			//action to add review notice on the admin page
			$this->loader->add_action( 'admin_init', $plugin_admin, 'wplp_review_already_done', 5 );
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'wplp_admin_review_notice' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the WP_Legal_Pages.
		 *
		 * @since    1.5.2
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public     = new WP_Legal_Pages_Public( $this->get_plugin_name(), $this->get_version() );
			$lp_general        = get_option( 'lp_general' );
			$lp_banner_options = get_option( 'lp_banner_options' );
			if ( isset( $lp_general['generate'] ) && '1' === $lp_general['generate'] ) {
				$this->loader->add_filter( 'the_content', $plugin_public, 'wplegal_post_generate' );
			}
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ) );
			$this->loader->add_action( 'wp_footer', $plugin_public, 'wp_legalpages_show_eu_cookie_message' );
			$this->loader->add_action( 'wp_footer', $plugin_public, 'wp_legalpages_show_footer_message' );
			if ( isset( $lp_banner_options['bar_position'] ) && 'bottom' === $lp_banner_options['bar_position'] ) {
				$this->loader->add_action( 'wp_footer', $plugin_public, 'wplegal_announce_bar_content' );
			}
			if ( isset( $lp_banner_options['bar_position'] ) && 'top' === $lp_banner_options['bar_position'] ) {
				$this->loader->add_action( 'wp_head', $plugin_public, 'wplegal_announce_bar_content' );
			}
			// Age verification and create popup.
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			$this->loader->add_action( 'wp_head', $plugin_public, 'wplegalpages_pro_disable_comments' );
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.5.2
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the WP_Legal_Pages used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.5.2
		 * @return    string    The name of the WP_Legal_Pages.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the WP_Legal_Pages.
		 *
		 * @since     1.5.2
		 * @return    WP_Legal_Pages_Loader    Orchestrates the hooks of the WP_Legal_Pages.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the WP_Legal_Pages.
		 *
		 * @since     1.5.2
		 * @return    string    The version number of the WP_Legal_Pages.
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Enqueue jQuery Cookie js library.
		 */
		public function enqueue_frontend_script() {
			wp_register_script( $this->plugin_name . '-jquery-cookie', WPL_LITE_PLUGIN_URL . 'admin/js/jquery.cookie.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( $this->plugin_name . '-jquery-cookie' );
			wp_register_script( $this->plugin_name . 'banner-cookie', WPL_LITE_PLUGIN_URL . 'public/js/wplegalpages-banner-cookie' . WPLPP_SUFFIX . '.js', array(), $this->version, true );
			wp_register_script( $this->plugin_name . 'lp-eu-cookie', WPL_LITE_PLUGIN_URL . 'public/js/wplegalpages-eu-cookie' . WPLPP_SUFFIX . '.js', array(), $this->version, true );
		}
		/**
		 * Enqueues editor specific javascript files.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_enqueue_editor() {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'jquery-affect' );
			wp_admin_css( 'thickbox' );
			wp_print_scripts( 'post' );
			wp_print_scripts( 'media-upload' );
			wp_print_scripts( 'jquery' );
			wp_print_scripts( 'jquery-ui-core' );
			wp_print_scripts( 'jquery-ui-tabs' );
			wp_print_scripts( 'editor' );
			wp_print_scripts( 'wplink' );
			wp_print_styles( 'wplink' );
			add_thickbox();
			wp_admin_css();
			wp_enqueue_script( 'utils' );
			do_action( 'admin_print_styles_post_php' );
		}
	}
}
