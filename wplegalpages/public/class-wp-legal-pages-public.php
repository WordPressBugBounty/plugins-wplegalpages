<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wplegalpages.com/
 * @since      1.5.2
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/admin
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 * @author     WPEka <support@wplegalpages.com>
 */
if ( ! class_exists( 'WP_Legal_Pages_Public' ) ) {
	/**
	 * The public-facing functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/includes
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */

		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param      string $plugin_name       The name of the plugin.
		 * @param      string $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {
			$this->loader = new WP_Legal_Pages_Loader();
			$this->plugin_name = $plugin_name;
			$this->version     = $version;
			add_shortcode( 'wplegalpage', array( $this, 'wplegalpages_page_shortcode' ) );
			$lp_pro_active    = get_option( '_lp_pro_active' );
			$lp_general = get_option( 'lp_general' );
			if (!is_array($lp_general)) {
				$lp_general = array();
			}
			$lp_general['is_adult'] = '0';
			if( !$lp_pro_active ){
				// age verification feature.
				$age_verify_popup_setting = get_option( '_lp_require_for' );
				if ( 'site' !== $age_verify_popup_setting && isset( $lp_general['is_adult'] ) && '1' === $lp_general['is_adult'] && ! isset( $_COOKIE['is_user_adult'] ) ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'wplegalpages_pro_adult_scripts' ) );
					add_action( 'wp_footer', array( $this, 'wplegalpages_pro_adult_popup' ) );
				}
				if ( 'site' === $age_verify_popup_setting ) {
					add_action( 'wp_footer', array( $this, 'wplegalpages_pro_verify_overlay' ) );
					add_action( 'the_content', array( $this, 'wplegalpages_pro_restrict_content' ) );
					add_action( 'template_redirect', array( $this, 'wplegalpages_pro_verify' ) );
					if ( $this->wplegalpages_pro_confirmation_required() ) {
						add_action( 'register_form', 'wplegalpages_pro_register_form' );
						add_action( 'register_post', 'wplegalpages_pro_register_check', 10, 3 );
					}
				}
				// create popup feature.
				add_shortcode( 'wp-legalpage', array( $this, 'wplegalpages_pro_shortcode' ) );
				$popup_enabled = get_option( 'lp_popup_enabled' );
				if ( ! empty( $popup_enabled ) || '1' === $popup_enabled || true === $popup_enabled || 'true' === $popup_enabled ) {
					add_shortcode( 'wp-legalpopup', array( $this, 'wplegalpages_pro_popup_shortcode' ) );
				}
				$lp_general = get_option( 'lp_general' );
				if ( isset( $lp_general['search'] ) && '1' !== $lp_general['search'] ) {
					add_filter( 'posts_where', array( $this, 'wplegalpages_pro_exclude_search_pages' ) );
				}
			}
		}
		
		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {

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
			$this->loader->wplp_register_style_with_defer( $this->plugin_name . '-public', plugin_dir_url( __FILE__ ) . 'css/wp-legal-pages-public-css' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
			$this->loader->wplp_register_style_with_defer( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wplegalpages-public' . WPLPP_SUFFIX . '.css', array(), $this->version, 'all' );
		}

		/**
		 * Register the JavaScript for the public-facing side of the site.
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
			$this->loader->wplp_register_script_with_defer( $this->plugin_name . 'adult-content', plugin_dir_url( __FILE__ ) . 'js/wplegalpages-public-adult-content' . WPLPP_SUFFIX . '.js', array( 'jquery' ), $this->version, true );
			$this->loader->wplp_register_script_with_defer( $this->plugin_name . 'popups', plugin_dir_url( __FILE__ ) . 'js/wplegalpages-public-popups' . WPLPP_SUFFIX . '.js', array( 'jquery' ), $this->version, true );

		}
		/**
		 * Checks if confirmation required.
		 *
		 * @return bool
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_confirmation_required() {
			if ( 1 === get_option( '_lp_membership', 1 ) ) {
				$return = true;
			} else {
				$return = false;
			}
			return (bool) apply_filters( 'wplegalpages_pro_confirmation_required', $return );
		}

		/**
		 * Enqueues Legal Pages Adult scripts.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_adult_scripts() {
			add_thickbox();
		}

		/**
		 * Renders Legal Pages Adult popup.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_adult_popup() {
			wp_enqueue_script( 'wp-legal-pages-jquery-cookie' );
			wp_enqueue_script( $this->plugin_name . 'adult-content' );
			$lp_general = get_option( 'lp_general' );
			?>
			<a id="inline" style="display:none" href="#data">This shows content of element who has id="data"</a>

			<div id="is_adult_thickbox" style="display:none">
				<div id="data">
					<p>This website contains content suitable for adults only . Please proceed only if you are above your country's legal age limit.</p>
					<a href="#" id="enter_site" style="text-decoration:none;">Yes,I am above my country's legal age limit (Enter)</a>
					<a href="<?php echo esc_url( $lp_general['leave-url'] ); ?>" style="text-decoration:none;" id="leave_site">Leave</a>
				</div>
			</div>
			<?php
		}

		/**
		 * Renders Legal Pages overlay popup.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_verify_overlay() {
			if ( ! $this->wplegalpages_pro_needs_verification() ) {
				return;
			}
			$lp_obj = new WP_Legal_Pages();
			if ( ! $lp_obj->wplegalpages_is_plan_pro() ) {
				return;
			}
			wp_enqueue_style( $this->plugin_name );
			$lp_obj->wplegalpages_pro_enqueue_editor();
			static $get_desc_called = false;
			if ( ! $get_desc_called ) {
				$get_desc_called = true;
				?>
			<div id="lp-overlay-wrap">
				<?php do_action( 'wplegalpages_pro_before_modal' ); ?>
				<div id="lp-overlay">
					<?php
					do_action( 'wplegalpages_pro_before_form' );
					if ( $this->wplegalpages_pro_get_the_desc() ) {
						$this->wplegalpages_pro_get_the_desc();
					}
					do_action( 'wplegalpages_pro_after_form' );
					?>
				</div>
				<?php do_action( 'wplegalpages_pro_after_modal' ); ?>
			</div>
				<?php
			}
		}

		/**
		 * Restrict content.
		 *
		 * @param string $content Content.
		 *
		 * @return string
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_restrict_content( $content ) {
			if ( ! $this->wplegalpages_pro_only_content_restricted() ) {
				return $content;
			}
			if ( is_singular() ) {
				return $content;
			}
			if ( ! $this->wplegalpages_pro_content_is_restricted() ) {
				return $content;
			}
			return sprintf(
				apply_filters( 'wplegalpages_pro_restricted_content_message', __( 'You must be atleast {age} years of age to view this content.<br>{form}', 'wplegalpages' ) . ' <a href="%2s">' . __( 'Please verify your age', 'wplegalpages' ) . '</a>.' ),
				esc_html( $this->wplegalpages_pro_get_minimum_age() ),
				esc_url( get_permalink( get_the_ID() ) )
			);
		}

		/**
		 * Verifies user.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_verify() {
			if ( isset( $_POST['lp-nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lp-nonce'] ) ), 'verify-age' ) ) {
					return;
				}
			} else {
				return;
			}

			$redirect_url = remove_query_arg( array( 'wplegalpages', 'verify-error' ), wp_get_referer() );

			$is_verified = false;

			$error = 1;

			$input_type = $this->wplegalpages_pro_get_input_type();

			switch ( $input_type ) {
				case 'checkbox':
					if ( isset( $_POST['lp_verify_confirm'] ) && 1 === (int) $_POST['lp_verify_confirm'] ) {
						$is_verified = true;
					} else {
						$error = 2;
					}
					break;
				default:
					$lp_verify_m = isset( $_POST['lp_verify_m'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_verify_m'] ) ) : '';
					$lp_verify_d = isset( $_POST['lp_verify_d'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_verify_d'] ) ) : '';
					$lp_verify_y = isset( $_POST['lp_verify_y'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_verify_y'] ) ) : '';
					if ( checkdate( (int) $lp_verify_m, (int) $lp_verify_d, (int) $lp_verify_y ) ) :
						$age = $this->wplegalpages_pro_get_visitor_age( $lp_verify_y, $lp_verify_m, $lp_verify_d );
						if ( $age >= $this->wplegalpages_pro_get_minimum_age() ) {
							$is_verified = true;
						} else {
							$error = 3;
						} else :
							$error = 4;
					endif;
					break;
			}

			$is_verified = apply_filters( 'wplegalpages_pro_passed_verify', $is_verified );
			if ( true === $is_verified ) :
				do_action( 'wplegalpages_pro_was_verified' );
				if ( isset( $_POST['wplegalpages_pro_verify_remember'] ) ) {
					$cookie_duration = time() + ( $this->wplegapages_pro_get_cookie_duration() * 60 );
				} else {
					$cookie_duration = 0;
				}
				setcookie( 'wplegalpages', 1, $cookie_duration, COOKIEPATH, COOKIE_DOMAIN, false );
				wp_safe_redirect( esc_url_raw( $redirect_url ) . '?wplegalpages=' . wp_create_nonce( 'wplegalpages' ) );
				exit;
			else :
				do_action( 'wplegalpages_pro_was_not_verified' );
				wp_safe_redirect(
					esc_url_raw(
						add_query_arg(
							array(
								'verify-error' => $error,
								'nonce'        => wp_create_nonce( 'age_verify_nonce' ),
							),
							$redirect_url
						)
					)
				);
				exit;
			endif;
		}

		/**
		 * Verify user form.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_register_form() {
			$text = '<p class="wplegalpages"><label for="_lp_confirm_age"><input type="checkbox" name="_lp_confirm_age" id="_lp_confirm_age" value="1" /> ';

			$text .= esc_html(
				sprintf(
					apply_filters(
						'wplegalpages_pro_registration_text',
						/* translators: 1: minimum age */
								esc_attr__( 'I am at least %1$s years old', 'wplegalpages' ),
						$this->wplegalpages_pro_get_minimum_age()
					)
				)
			);

			$text .= '</label></p><br />';
			echo esc_attr( $text );
		}
		/**
		 * Gets Legal Pages input types.
		 *
		 * @return mixed|void
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_get_input_type() {
			return apply_filters( 'wplegalpages_pro_get_input_type', get_option( '_lp_input_type', 'dropdowns' ) );
		}
		/**
		 * Checks whether verification is needed or not.
		 *
		 * @return bool
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_needs_verification() {
			$return = true;
			if ( $this->wplegalpages_pro_only_content_restricted() ) :
				$return = false;
				if ( is_singular() && $this->wplegalpages_pro_content_is_restricted() ) {
					$return = true;
				}
			endif;
			if ( isset( $_REQUEST['wplegalpages'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wplegalpages'] ) ), 'wplegalpages' ) ) {
					$return = false;
				}
			}

			if ( 'guests' === get_option( '_lp_always_verify', 'guests' ) && is_user_logged_in() ) {
				$return = false;
			}

			if ( isset( $_COOKIE['wplegalpages'] ) ) {
				$return = false;
			}
			return (bool) apply_filters( 'wplegalpages_pro_needs_verification', $return );
		}
		/**
		 * Returns restricted content.
		 *
		 * @return bool
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_only_content_restricted() {
			$only_content_restricted = 'content' === get_option( '_lp_require_for' ) ? true : false;
			$only_content_restricted = apply_filters( 'wplegalpages_pro_only_content_restricted', $only_content_restricted );
			return (bool) $only_content_restricted;
		}
		/**
		 * Gets the Age verification description.
		 *
		 * @return bool|mixed|void
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_get_the_desc() {
			$desc  = apply_filters( 'wplegalpages_pro_description', get_option( '_lp_description', __( "To proceed, we need to verify that you're {age} or older.<br><span>Please verify your age.</span><br>{form}", 'wplegalpages' ) ) );
			$strre = str_replace( '{age}', $this->wplegalpages_pro_get_minimum_age(), $desc );
			if ( ! empty( $desc ) ) {
				$desc_string   = apply_filters( 'wplegalpages_pro_description', $strre );
				$output_array  = explode( '{form}', $desc_string );
				$i             = 0;
				$output_length = count( $output_array );
				if ( ! empty( $output_array ) ) {
					if ( '' === $output_array[0] ) {
						$i = 1;
						$this->wplegalpages_pro_get_display_option();
					}
					for ( $i; $i < $output_length; $i++ ) {
						echo wp_kses_post( $output_array[ $i ] );
						if ( $i < $output_length - 1 ) {
							$this->wplegalpages_pro_get_display_option();
						}
					}
				}
			} else {
				return false;
			}
		}
		/**
		 * Returns minimum age for user.
		 *
		 * @return int
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_get_minimum_age() {
			$minimum_age = get_option( '_lp_minimum_age', 21 );
			$minimum_age = apply_filters( 'lp_minimum_age', $minimum_age );
			return (int) $minimum_age;
		}
		/**
		 * Returns display options for verify form.
		 *
		 * @return mixed|string|void
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_get_display_option() {
			if ( ( '0' === get_option( '_lp_age_popup_no', '1' ) ) || ( 0 === get_option( '_lp_age_popup_no', 1 ) ) ) {
				include_once plugin_dir_path( __DIR__ ) . 'public/templates/wplegalpages-age-verify-form.php';
			} else if( ( '1' === get_option( '_lp_age_popup_no', '1' ) ) || ( 1 === get_option( '_lp_age_popup_no', 1 ) ) ) {
				include_once plugin_dir_path( __DIR__ ) . 'public/templates/wplegalpages-age-button.php';
			} else 	{
				include_once plugin_dir_path( __DIR__ ) . 'public/templates/wplegalpages-age-leave-form.php';
			}
		}
		/**
		 * Get visitor age.
		 *
		 * @param int $year Year.
		 * @param int $month Month.
		 * @param int $day Day.
		 *
		 * @return int
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_get_visitor_age( $year, $month, $day ) {
			$age        = 0;
			$birthday   = new DateTime( $year . '-' . $month . '-' . $day );
			$phpversion = phpversion();
			if ( $phpversion >= '5.3' ) :
				$current = new DateTime( current_time( 'mysql' ) );
				$age     = $birthday->diff( $current );
				$age     = $age->format( '%y' );
			else :
				list( $year, $month, $day ) = explode( '-', $birthday->format( 'Y-m-d' ) );
				$year_diff                  = date_i18n( 'Y' ) - $year;
				$month_diff                 = date_i18n( 'm' ) - $month;
				$day_diff                   = date_i18n( 'd' ) - $day;
				if ( $month_diff < 0 ) {
					--$year_diff;
				} elseif ( ( 0 === $month_diff ) && ( $day_diff < 0 ) ) {
					--$year_diff;
				}
				$age = $year_diff;
			endif;
			return (int) $age;
		}
		/**
		 * Get cookie duration.
		 *
		 * @return int|mixed|void
		 *
		 * @since 7.0
		 */
		public function wplegapages_pro_get_cookie_duration() {
			$cookie_duration = get_option( '_lp_cookie_duration', 720 );
			$cookie_duration = (int) apply_filters( 'lp_cookie_duration', $cookie_duration );
			return $cookie_duration;
		}
		/**
		 * Process WPLegalPages popup shortcode.
		 *
		 * @param Array $atts Shortcode attributes.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_popup_shortcode( $atts ) {
			global $wpdb;
			wp_enqueue_style( $this->plugin_name );
			$legalpages_pro = new WP_Legal_Pages();
			$atts           = shortcode_atts(
				array(
					'pid' => 1,
				),
				$atts
			);
			$pid            = $atts['pid'];

			$res   = $wpdb->get_row( $wpdb->prepare( 'SELECT * from ' . $legalpages_pro->popuptable . ' where id= %d', $pid ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$terms = '';
			if ( isset( $res->content ) ) {
				$terms = do_shortcode( $res->content );
			}
			$lp_find    = array( '[Domain]', '[Business Name]', '[Phone]', '[Street]', '[City, State, Zip code]', '[Country]', '[Email]', '[Address]', '[Niche]' );
			$lp_general = get_option( 'lp_general' );
			$terms      = str_replace( $lp_find, $lp_general, stripslashes( $terms ) );
			$terms      = apply_filters( 'wplegalpages_shortcode_content', $terms );

			$content = '<div>
							' . $terms . '
							<p><input type="checkbox" name="lp_accept" id="lp_accept" value="1" onclick="jQuery(\'.accept\').toggle();" /> I agree to the terms and conditions.</p>
							<input type="submit" name="lp_submit" id="lp_submit" value="Accept" class="accept" style="display:none;"/>
							<br/>
						</div>';
			if ( is_single() || is_page() ) {
				add_thickbox();
				?>
				<div id="thick-box" style="display:none;" >
					<p>
						<div>
						<?php
						$allowed_html = wp_kses_allowed_html( 'post' );
						echo wp_kses( $terms, $allowed_html );
						?>
						<p><input type="checkbox" name="lp_accept" id="lp_accept" value="1" onclick="jQuery('.accept').toggle();" /> I agree to the terms and conditions.</p>
							<input type="submit" name="lp_submit" id="lp_submit" value="Accept" class="accept" style="display:none;"/>
							<br/>
						</div>
					</p>
				</div>
				<?php
				wp_enqueue_script( 'wp-legal-pages-jquery-cookie' );
				wp_enqueue_script( $this->plugin_name . 'popups' );
			}
		}
		/**
		 * Excludes Legal Pages from search query.
		 *
		 * @param string $where Search query.
		 *
		 * @return string
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_exclude_search_pages( $where = '' ) {
			global $wpdb;
			$exclude = array();

			if ( ! is_admin() && is_search() ) {
				$post_tbl     = $wpdb->prefix . 'posts';
				$postmeta_tbl = $wpdb->prefix . 'postmeta';
				$pagesresult  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $post_tbl . ' as ptbl, ' . $postmeta_tbl . ' as pmtbl WHERE ptbl.ID = pmtbl.post_id and ptbl.post_status=%s AND pmtbl.meta_key=%s', array( 'publish', 'is_legal' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				foreach ( $pagesresult as $pid ) {
					array_push( $exclude, $pid->ID );
				}
				$exclude_count = count( $exclude );
				for ( $x = 0;$x < $exclude_count;$x++ ) {
					$where .= ' AND ID != ' . $exclude[ $x ];
				}
			}
			return $where;
		}
		/**
		 * Process WPLegalPages Shortcodes.
		 *
		 * @param Array $atts Shortcode attributes.
		 *
		 * @return string
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_shortcode( $atts ) {
			global $wpdb;
			$legalpages_pro = new WP_Legal_Pages();
			$atts           = shortcode_atts(
				array(
					'tid' => 1,
				),
				$atts
			);
			$tid            = $atts['tid'];

			$res = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE ID  = %d AND post_type = %s AND post_status != %s", $tid, 'page', 'trash' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( isset( $res->post_content ) ) {
				$content = $res->post_content;
			}
			$lp_find    = array( '[Domain]', '[Business Name]', '[Phone]', '[Street]', '[City, State, Zip code]', '[Country]', '[Email]', '[Address]', '[Niche]' );
			$lp_general = get_option( 'lp_general' );
			$content    = str_replace( $lp_find, $lp_general, stripslashes( $content ) );
			$content    = apply_filters( 'wplegalpages_shortcode_content', $content );

			if ( is_single() || is_page() ) {
				return html_entity_decode( $content );
			}
		}
		/**
		 * Disables comments for Legal Pages.
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_disable_comments() {
			$post_id = get_the_ID();
			$meta    = '';
			if ( ! empty( $post_id ) ) {
				$meta = get_post_meta( $post_id, 'is_legal', true );
			}
			$general = get_option( 'lp_general' );
			if ( isset( $general['disable_comments'] ) && '1' === $general['disable_comments'] && 'yes' === $meta ) {
				add_filter( 'comments_open', array( $this, 'wplegalpages_pro_disable_page_comments' ) );
			}
		}

		/**
		 * Disables comments for Legal Pages.
		 *
		 * @return bool
		 *
		 * @since 7.0
		 */
		public function wplegalpages_pro_disable_page_comments() {
			$post = get_post_meta( get_the_ID(), 'is_legal' );

			if ( 'yes' === $post[0] ) {
				$open = false;
			}

			return $open;
		}
		/**
		 * Show credits.
		 *
		 * @param String $content Content.
		 * @return string
		 */
		public function wplegal_post_generate( $content ) {
			global $post;
			if ( is_page() ) {
				$is_legal = get_post_meta( $post->ID, 'is_legal', true );
				if ( isset( $is_legal ) && 'yes' === $is_legal ) {
					$generate_text = "<div style='font-size: 0.7em;'><i>" . get_the_title( $post ) . " generated by <a href='https://wplegalpages.com/legal-pages/?utm_source=generated-page&utm_medium=credit-link' rel='nofollow' target='_blank'>WPLegalPages</a></i></div>";
					$content       = $content . $generate_text;
				}
			}
			return $content;
		}

		/**
		 * Shortcode callback function for All Legal Pages shortcode.
		 *
		 * @param Array $atts shortcode attributes.
		 */
		public function wplegalpages_page_shortcode( $atts ) {
			global $wpdb;
			$atts         = shortcode_atts(
				array(
					'pid' => 0,
				),
				$atts
			);
			$pid          = $atts['pid'];
			$post_tbl     = $wpdb->prefix . 'posts';
			$postmeta_tbl = $wpdb->prefix . 'postmeta';
			$page         = $wpdb->get_row( $wpdb->prepare( 'SELECT ptbl.* FROM ' . $post_tbl . ' as ptbl , ' . $postmeta_tbl . ' as pmtbl WHERE ptbl.ID = pmtbl.post_id and ptbl.ID = %d and ptbl.post_status = %s AND pmtbl.meta_key = %s', array( $pid, 'publish', 'is_legal' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( isset( $page->post_content ) ) {
				$content = $page->post_content;
			}
		
			if ( is_single() || is_page() ) {
				// Check if the current user has the "unfiltered_html" capability
				if ( author_can($pid, 'unfiltered_html' ) ) {
					// If the user has the capability, decode the content
					$content = html_entity_decode( $content );
				}
				
				return $content;
			}
		}

		/**
		 * Function to display message in footer
		 */
		public function wp_legalpages_show_footer_message() {
			$lp_footer_options = get_option( 'lp_footer_options' );
			if ( false === $lp_footer_options || empty( $lp_footer_options ) ) {
				return;
			}
			if ( '1' !== $lp_footer_options['show_footer'] ) {
				return;
			}
			$footer_bg_color    = $lp_footer_options['footer_bg_color'];
			$footer_text_align  = $lp_footer_options['footer_text_align'];
			$footer_separator   = $lp_footer_options['footer_separator'];
			$footer_text_color  = $lp_footer_options['footer_text_color'];
			$footer_link_color  = $lp_footer_options['footer_link_color'];
			$footer_font_family = $lp_footer_options['footer_font'];
			$footer_font_id     = $lp_footer_options['footer_font_id'];
			$footer_font_size   = $lp_footer_options['footer_font_size'];
			$footer_custom_css  = $lp_footer_options['footer_custom_css'];
			$footer_new_tab     = '1' === $lp_footer_options['footer_new_tab'] ? 'target="_blank"' : '';
			$footer_pages       = $lp_footer_options['footer_legal_pages'];
			$font_family_url    = 'http://fonts.googleapis.com/css?family=' . $footer_font_id;
			if ( empty( $footer_pages ) || empty( $footer_pages[0] ) ) {
				return;
			}

			/* marking all the customized css property added as important */
			function appendImportantToCss($css) {
				// Regular expression to match properties and values
				$css = preg_replace_callback('/(\s*[\w-]+\s*:\s*[^;]+)(;)/i', function($matches) {
					// $matches[1] contains the property and value
					// $matches[2] contains the semicolon

					return $matches[1] . ' !important' . $matches[2];
				}, $css);
				
				return $css;
			}

			$footer_custom_css=appendImportantToCss($footer_custom_css);
			
			wp_enqueue_style( $this->plugin_name . '-public' );
			wp_add_inline_style( $this->plugin_name . '-public', '@import url(' . $font_family_url . ');' );
			$page_count = count( $footer_pages );
			echo '<style>' . esc_html( $footer_custom_css ) . '</style>';
			?>
			<div id="wplegalpages_footer_links_container">
			<?php
			$page_count = count( $footer_pages );
			for ( $i = 0; $i < $page_count; $i++ ) {
				$page_url = get_permalink( $footer_pages[ $i ] );
				?>
				<a class="wplegalpages_footer_link" <?php echo esc_attr( $footer_new_tab ); ?> href="<?php echo esc_attr( $page_url ); ?>" > <?php echo esc_html( get_the_title( $footer_pages[ $i ] ) ); ?></a>
				<?php
				if ( $i !== $page_count - 1 ) {
					?>
					<span class="wplegalpages_footer_separator_text">
						<?php echo esc_html( $footer_separator ); ?>
					</span>
					<?php
				}
			}
			?>
			</div>
			<script type="text/javascript">
					  jQuery(document).ready(function(){
					jQuery('#wplegalpages_footer_links_container').css({
						'width': '100%',
						'background-color': '<?php echo esc_attr( $footer_bg_color ); ?>',
						'text-align': '<?php echo esc_attr( $footer_text_align ); ?>',
						'font-size': '<?php echo esc_attr( $footer_font_size ) . 'px'; ?>',
						'font-family': '<?php echo esc_attr( $footer_font_family ); ?>'
					})
					jQuery('.wplegalpages_footer_link').css({
						'color': '<?php echo esc_attr( $footer_link_color ); ?>'
					})
					jQuery('.wplegalpages_footer_separator_text').css({
						'color': '<?php echo esc_attr( $footer_text_color ); ?>'
					})
				})		
			</script>
			<?php
		}

		/** Show Announcement bar contents
		 */
		public function wplegal_announce_bar_content() {
			$lp_banner_options     = get_option( 'lp_banner_options' );
			$banner_cookie_options = get_option( 'banner_cookie_options' );
			$cookies_array         = array();
			if ( ! $banner_cookie_options || count( $banner_cookie_options ) === 0 ) {
				return;
			}
			foreach ( $banner_cookie_options as $cookie_option ) {
				if ( ! isset( $_COOKIE[ $cookie_option['cookie_name'] ] ) && time() < $cookie_option['cookie_end'] ) {
					$cookie_option['cookie_expire'] = $cookie_option['cookie_end'] - time();
					array_push( $cookies_array, $cookie_option );
				}
			}
			if ( count( $cookies_array ) > 0 ) {
				wp_localize_script( $this->plugin_name . 'banner-cookie', 'cookies', $cookies_array );
				wp_enqueue_script( $this->plugin_name . 'banner-cookie' );
			}
			if ( '1' === $lp_banner_options['show_banner'] || true === $lp_banner_options['show_banner'] || 'true' === $lp_banner_options['show_banner'] ) {
				foreach ( $_COOKIE as $key => $val ) {
					if ( preg_match( '/wplegalpages-update-notice-\d+/', sanitize_key( $key ) ) ) {
						$this->lp_banner_contents_display();
						break;
					}
				}
			}
		}

		/**
		 * Function to display announcement banner content
		 */
		public function lp_banner_contents_display() {
			$lp_banner_options       = get_option( 'lp_banner_options' );
			$banner_position         = $lp_banner_options['bar_position'];
			$banner_type             = $lp_banner_options['bar_type'];
			$banner_bg_color         = $lp_banner_options['banner_bg_color'];
			$banner_font             = $lp_banner_options['banner_font'];
			$banner_font_id          = $lp_banner_options['banner_font_id'];
			$banner_text_color       = $lp_banner_options['banner_text_color'];
			$banner_font_size        = $lp_banner_options['banner_font_size'];
			$banner_link_color       = $lp_banner_options['banner_link_color'];
			$bar_num_of_days         = $lp_banner_options['bar_num_of_days'];
			$banner_custom_css       = $lp_banner_options['banner_custom_css'];
			$banner_close_message    = $lp_banner_options['banner_close_message'];
			$banner_message          = $lp_banner_options['banner_message'];
			$banner_multiple_message = $lp_banner_options['banner_multiple_message'];
			$date_format             = get_option( 'date_format' );
			$updateAt                = get_option('updateAt');
			$font_family_url         = 'http://fonts.googleapis.com/css?family=' . $banner_font_id;
			wp_enqueue_style( $this->plugin_name . '-public' );
			wp_add_inline_style( $this->plugin_name . '-public', '@import url(' . $font_family_url . ');' );
            ?>
         
			<?php
			
			if(!isset($_COOKIE['updateAt'])  || $_COOKIE['updateAt'] !== $updateAt){
			?>
				<div class="wplegalpages_banner_content" 
					style="background-color:red;z-index:1000; 
					<?php if ( 'top' === $banner_position ) { ?>
					top: 0px; 
						<?php
					} else {
						?>
						bottom:0px;
						<?php
					}
					?>
					width:100%;
					display:block;
					position : <?php echo esc_attr( $banner_type ); ?>;
					font-family : <?php echo esc_attr( $banner_font ); ?>;
					background-color: <?php echo esc_attr( $banner_bg_color ); ?>;
					color: <?php echo esc_attr( $banner_text_color ); ?>;
					font-size: <?php echo esc_attr( $banner_font_size ); ?>px;">
					<?php
					$page_ids    = array();
					$page_titles = '';
					$page_links  = array();
					$exp         = '/wplegalpages-update-notice-\d+/';
					foreach ( $_COOKIE as $key => $val ) {
						if ( preg_match( $exp, $key ) ) {
							$p_id = substr( $key, 27 );
							array_push( $page_ids, $p_id );
							$page_titles .= get_the_title( $p_id ) . ', ';
							array_push( $page_links, get_page_link( $p_id ) );
						}
					}
					$num_of_pages = count( $page_ids );
					if ( 1 === $num_of_pages ) {
						$banner_message = str_replace( '[wplegalpages_page_title]', $page_titles, $banner_message );
						$banner_message = str_replace( '[wplegalpages_last_updated]', get_the_modified_date( $date_format, $page_ids[0] ), $banner_message );
						$banner_message = str_replace( '[wplegalpages_page_href]', get_page_link( $page_ids[0] ), $banner_message );
						if ( strpos( $banner_message, '[wplegalpages_page_link]' ) ) {
							echo esc_html( substr( $banner_message, 0, strpos( $banner_message, '[wplegalpages_page_link]' ) ) );
							?>
							<a class="wplegalpages_banner_link" href="<?php echo esc_attr( get_page_link( $page_ids[0] ) ); ?>" > <?php echo esc_html( $page_titles ); ?> </a>
							<?php
							echo esc_html( substr( $banner_message, strpos( $banner_message, '[wplegalpages_page_link]' ) + 24 ) );
						} else {
							echo esc_attr( $banner_message );
						}
					} else {
						$page_latest_update = 0;
						$page_date          = '';
						for ( $i = 0; $i < $num_of_pages; $i++ ) {
							if ( get_post_modified_time( 'U', false, $page_ids[ $i ] ) > $page_latest_update ) {
								$page_date          = get_the_modified_date( $date_format, $page_ids[ $i ] );
								$page_latest_update = get_post_modified_time( 'U', false, $page_ids[ $i ] );
							}
						}
						$banner_multiple_message = str_replace( '[wplegalpages_page_title]', $page_titles, $banner_multiple_message );
						$banner_multiple_message = str_replace( '[wplegalpages_last_updated]', $page_date, $banner_multiple_message );
						if ( strpos( $banner_multiple_message, '[wplegalpages_page_link]' ) ) {
							echo esc_html( substr( $banner_multiple_message, 0, strpos( $banner_multiple_message, '[wplegalpages_page_link]' ) ) );
							for ( $i = 0; $i < $num_of_pages; $i++ ) {
								?>
								<a class="wplegalpages_banner_link" href=" <?php echo esc_attr( $page_links[ $i ] ); ?> "><?php echo esc_html( get_the_title( $page_ids[ $i ] ) ); ?></a>
								<?php
								if ( get_post_modified_time( 'U', false, $page_ids[ $i ] ) > $page_latest_update ) {
									$page_date          = get_the_modified_date( $date_format, $page_ids[ $i ] );
									$page_latest_update = get_post_modified_time( 'U', false, $page_ids[ $i ] );
								}
							}
							echo esc_html( substr( $banner_multiple_message, strpos( $banner_multiple_message, '[wplegalpages_page_link]' ) + 24 ) );
						} else {
							echo esc_attr( $banner_multiple_message );
						}
					}
					?>
					<a style="cursor:pointer;" class="closeButton"> <?php echo esc_attr( $banner_close_message ); ?> </a>
				</div>
				<script type="text/javascript">
					
					jQuery(document).ready(function(){
						jQuery(".wplegalpages_banner_content").find("a").addClass("wplegalpages_banner_link");
						jQuery(".wplegalpages_banner_link").click(
							function() {
								var display_state = jQuery('.wplegalpages_banner_content').css('display');
								if(display_state === 'block'){
									jQuery('.wplegalpages_banner_content').css('display','none');
								}
							    }
						);
						jQuery(".closeButton").click(
							function() {
								function setCookie(cookieName, cookieValue, expirationDays) {
        								var expires = "";
        
        							if (expirationDays) {
           								 var date = new Date();
            							date.setTime(date.getTime() + (expirationDays * 24 * 60 * 60 * 1000));
           								 expires = "; expires=" + date.toUTCString();
       									 }

        							document.cookie = cookieName + "=" + encodeURIComponent(cookieValue) + expires + "; path=/";
    								}
									setCookie("updateAt", <?php echo esc_html( $updateAt);  ?> ,<?php echo esc_html( $bar_num_of_days ) ?>);
							}
							
						);
					});
				</script>
				<?php
			}
				echo '<style>
				.wplegalpages_banner_link{
					color: ' . esc_attr( $banner_link_color ) . ';' .
				'}'
				. esc_html( $banner_custom_css ) .
				'</style>';
		}

		/**
		 * Display EU cookie message on frontend.
		 */
		public function wp_legalpages_show_eu_cookie_message() {

			$lp_eu_get_visibility = get_option( 'lp_eu_cookie_enable' );

			if ( 'ON' === $lp_eu_get_visibility ) {
				$lp_eu_theme_css         = get_option( 'lp_eu_theme_css' );
				$lp_eu_title             = get_option( 'lp_eu_cookie_title' );
				$lp_eu_message           = get_option( 'lp_eu_cookie_message' );
				$lp_eu_box_color         = get_option( 'lp_eu_box_color' );
				$lp_eu_button_color      = get_option( 'lp_eu_button_color' );
				$lp_eu_button_text_color = get_option( 'lp_eu_button_text_color' );
				$lp_eu_text_color        = get_option( 'lp_eu_text_color' );
				$lp_eu_button_text       = get_option( 'lp_eu_button_text' );
				$lp_eu_link_text         = get_option( 'lp_eu_link_text' );
				$lp_eu_link_url          = get_option( 'lp_eu_link_url' );
				$lp_eu_text_size         = get_option( 'lp_eu_text_size' );
				$lp_eu_link_color        = get_option( 'lp_eu_link_color' );
				$lp_eu_head_text_size    = $lp_eu_text_size + 4;

				if ( ! $lp_eu_button_text || '' === $lp_eu_button_text ) {
					$lp_eu_button_text = 'I agree';
					update_option( 'lp_eu_button_text', $lp_eu_button_text );
				}

				$options = array(
					'lp_eu_theme_css'         => $lp_eu_theme_css,
					'lp_eu_title'             => $lp_eu_title,
					'lp_eu_message'           => $lp_eu_message,
					'lp_eu_box_color'         => $lp_eu_box_color,
					'lp_eu_button_color'      => $lp_eu_button_color,
					'lp_eu_button_text_color' => $lp_eu_button_text_color,
					'lp_eu_text_color'        => $lp_eu_text_color,
					'lp_eu_button_text'       => $lp_eu_button_text,
					'lp_eu_link_text'         => $lp_eu_link_text,
					'lp_eu_link_url'          => $lp_eu_link_url,
					'lp_eu_text_size'         => $lp_eu_text_size,
					'lp_eu_link_color'        => $lp_eu_link_color,
					'lp_eu_head_text_size'    => $lp_eu_head_text_size,
				);
				wp_enqueue_style( $this->plugin_name . '-public' );
				wp_localize_script( $this->plugin_name . 'lp-eu-cookie', 'obj', $options );
				wp_enqueue_script( $this->plugin_name . 'lp-eu-cookie' );
				include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/views/lp-eu-cookie.php';
			}
		}
	}
}
