<?php
/**
 * Provide a admin area view for the settings.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package    Wplegalpages
 * @subpackage Wplegalpages/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$baseurl = '';
if ( isset( $_SERVER['PHP_SELF'] ) ) {
	$baseurl = esc_url_raw( wp_unslash( $_SERVER['PHP_SELF'] ) );
}

if ( isset( $_POST['lp_submit'] ) && 'Accept' === $_POST['lp_submit'] ) {
	check_admin_referer( 'lp-submit-accept-terms' );
	update_option( 'lp_accept_terms', isset( $_POST['lp_accept_terms'] ) ? sanitize_text_field( wp_unslash( $_POST['lp_accept_terms'] ) ) : '' );
}

require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-wp-legal-pages-settings.php';

// // Instantiate a new object of the wplegal_Cookie_Consent_Settings class.
$this->settings = new WP_Legal_Pages_Settings();

// // Call the is_connected() method from the instantiated object to check if the user is connected.
$is_user_connected = $this->settings->is_connected();
$api_user_email    = $this->settings->get_email();
$api_user_site_key = $this->settings->get_website_key();
$api_user_plan     = $this->settings->get_plan();


wp_enqueue_script( 'jquery' );
$lp_pro_active           = get_option( '_lp_pro_active' );
$lpterms                 = get_option( 'lp_accept_terms' );
$lp_pro_installed        = get_option( '_lp_pro_installed' );
$lp_pro_key_activated    = get_option( 'wc_am_client_wplegalpages_pro_activated' );
$lp_general              = get_option( 'lp_general' );
$lp_footer_options       = get_option( 'lp_footer_options' );
$lp_banner_options       = get_option( 'lp_banner_options' );

$lp_eu_button_text       = get_option( 'lp_eu_button_text' );
$lp_eu_link_text         = get_option( 'lp_eu_link_text' );
$lp_eu_link_url          = get_option( 'lp_eu_link_url' );
$lp_eu_theme_css         = get_option( 'lp_eu_theme_css' );
$lp_eu_box_color         = get_option( 'lp_eu_box_color' );
$lp_eu_text_color        = get_option( 'lp_eu_text_color' );
$lp_eu_button_color      = get_option( 'lp_eu_button_color' );
$lp_eu_button_text_color = get_option( 'lp_eu_button_text_color' );
$lp_eu_link_color        = get_option( 'lp_eu_link_color' );
$lp_eu_text_size         = get_option( 'lp_eu_text_size' );
$is_blocked_enabled      = get_option( 'wplegalpages_is_block_enabled' );
$lp_show_improved_ui     = true;
if ( $lp_pro_installed && get_option( 'wplegalpages_pro_version' ) && version_compare( get_option( 'wplegalpages_pro_version' ), '8.4.0' ) < 0 ) {
	$lp_show_improved_ui = false;
}

if ( false === $lp_footer_options || empty( $lp_footer_options ) ) {
	$lp_footer_options = array(
		'footer_legal_pages' => '',
		'show_footer'        => '0',
		'footer_bg_color'    => '#ffffff',
		'footer_text_align'  => 'center',
		'footer_separator'   => '',
		'footer_new_tab'     => '0',
		'footer_text_color'  => '#333333',
		'footer_link_color'  => '#333333',
		'footer_font'        => 'Open Sans',
		'footer_font_id'     => 'Open+Sans',
		'footer_font_size'   => '16',
		'footer_custom_css'  => '',
	);
	update_option( 'lp_footer_options', $lp_footer_options );
}
if ( false === $lp_banner_options || empty( $lp_banner_options ) ) {
	$lp_banner_options = array(
		'show_banner'             => '0',
		'bar_position'            => 'top',
		'bar_type'                => 'static',
		'banner_bg_color'         => '#ffffff',
		'banner_font'             => 'Open Sans',
		'banner_font_id'          => 'Open+Sans',
		'banner_text_color'       => '#000000',
		'banner_font_size'        => '20px',
		'banner_link_color'       => '#000000',
		'bar_num_of_days'         => '1',
		'banner_custom_css'       => '',
		'banner_close_message'    => 'Close',
		'banner_message'          => 'Our [wplegalpages_page_link] have been updated on [wplegalpages_last_updated].',
		'banner_multiple_message' => 'Our [wplegalpages_page_link] pages have recently have recently been updated.',
	);
	update_option( 'lp_banner_options', $lp_banner_options );
}
if ( '1' === $lpterms ) {
	if ( '1' !== $lp_pro_active ) :
		?>
		<div style="">
			<div style="line-height: 2.4em;" class='wplegalpages-pro-promotion-settings-page'>
				<a href="https://club.wpeka.com/product/wplegalpages/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=settings-page&utm_content=upgrade-banner" target="_blank">
					<img alt="Upgrade to Pro" src="<?php echo esc_attr( WPL_LITE_PLUGIN_URL ) . 'admin/images/wplegalpages-banner.png'; ?>"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				</a>
			</div>
		</div>
		<div style="clear:both;"></div>
		<?php
	endif;
	?>
<div class="wplegalpages-app-container" id="wplegalpages-settings-app">
	<div class="wplegalpages-settings-container">
	<div class="wplegal-create-legal-settings">
			<div class="wplegal-feature-icon" id="wplegal-settings-create-legal">
				<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/create_legal_blue.svg' ); ?>" alt="create legal" class="wplegal-create-legal-icon"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				<div class="wplegal-create-legal-subtext">
					<p class="wplegal-create-legal-page-subheading"><?php esc_attr_e( 'Create Your Legal Page', 'wplegalpages' ); ?></p>
					<p class="wplegal-create-legal-page-content"><?php esc_attr_e( 'Secure your site in 3 easy steps and generate a personalized legal policy page for enhanced protection.', 'wplegalpages' ); ?></p>
				</div>
			</div>
			<div class="wplegal-create-legal-link">
				<a href=<?php echo esc_url( admin_url( 'index.php?page=wplegal-wizard#/' ) ); ?> class="wplegal-create-legal-page-button">
					<span><?php esc_attr_e( 'Create Page', 'wplegalpages' ); ?></span>
					<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/right_arrow.svg' ); ?>" alt="right arrow"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				</a>
			</div>
		</div>
		<div class="wplegalpages-marketing-banner">
				</div>
		<c-form id="lp-save-settings-form" spellcheck="false" class="wplegalpages-settings-form">
			<input type="hidden" name="settings_form_nonce" value="<?php echo esc_attr( wp_create_nonce( 'settings-form-nonce' ) ); ?>"/>
			<div class="wplegalpages-settings-content">
				<div id="wplegalpages-save-settings-alert"><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/settings_saved.svg' ); ?>" alt="create legal" class="wplegal-save-settings-icon"><?php esc_attr_e( 'Settings saved successfully', 'wplegalpages' ); ?></div> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>

				<c-tabs variant="pills" ref="active_tab" class="wplegalpages-settings-nav">
					<c-tab title="<?php esc_attr_e( 'General', 'wplegalpages' ); ?>" href="#settings#general" id="wplegalpages-settings-general">
					<?php do_action( 'wp_legalpages_notice' ); ?>
						<c-card>
							<c-card-body>

								<?php
								if ( ! $lp_show_improved_ui ) {
									?>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Domain Name', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Enter your website URL. Use [Domain] as shortcode', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-domain-name" value="<?php echo ! empty( $lp_general['domain'] ) ? esc_attr( $lp_general['domain'] ) : esc_url_raw( get_bloginfo( 'url' ) ); ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Business Name', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Enter your Legal business name. Use [Business Name] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-business-name" value="<?php echo ! empty( $lp_general['business'] ) ? esc_attr( $lp_general['business'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'DBA/Trading Name', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Enter your "doing business as"/ trading name. Use [Trading Name] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-trading-name" value="<?php echo ! empty( $lp_general['trading'] ) ? esc_attr( $lp_general['trading'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Phone', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [Phone] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text"  name="lp-phone" value="<?php echo ! empty( $lp_general['phone'] ) ? esc_attr( $lp_general['phone'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Street', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [Street] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-street" value="<?php echo ! empty( $lp_general['street'] ) ? esc_attr( $lp_general['street'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'City, State, Zip code', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [City, State, Zip code] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text"  name="lp-city-state" value="<?php echo ! empty( $lp_general['cityState'] ) ? esc_attr( $lp_general['cityState'] ) : ''; ?>"></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Country', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [Country] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-country" value="<?php echo ! empty( $lp_general['country'] ) ? esc_attr( $lp_general['country'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Email', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [Email] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="email"  name="lp-email" title="Please enter a valid email address" value="<?php echo ! empty( $lp_general['email'] ) ? esc_attr( $lp_general['email'] ) : esc_attr( get_option( 'admin_email' ) ); ?>" ></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Address', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Certain policies like CCPA require your contact details. Use [Address] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-address" value="<?php echo ! empty( $lp_general['address'] ) ? esc_attr( $lp_general['address'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Niche', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( ' Fill the general niche of your business. Use [Niche] as shortcode.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<c-col class="col-sm-8"><c-input type="text" name="lp-niche" value="<?php echo ! empty( $lp_general['niche'] ) ? esc_attr( $lp_general['niche'] ) : ''; ?>"></c-input></c-col>
									</c-row>
									<?php do_action( 'wplegalpages_admin_settings', $lp_general ); ?>
									<c-row>
										<c-col class="col-sm-4">
											<label><?php esc_attr_e( 'Give Credit', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Display credits at bottom of your legal pages.', 'wplegalpages' ); ?>'"></c-icon></label>
										</c-col>
										<c-col class="col-sm-8">
											<input type="hidden" name="lp-generate" v-model="generate">
											<label for="inline-form-credits" class="c-switch form-check-label c-switch-3d-success c-switch-label">
											<span class="screen-reader-text"><?php esc_attr_e( 'Give Credit toggle', 'wplegalpages'); ?></span>

											<input 
												type="checkbox" 
												id="inline-form-credits" 
												ref="generate"
												value="1"
												class="c-switch-input"
												aria-label="Give credits toggle" 
												<?php checked( isset( $lp_general['generate'] ) ? boolval( $lp_general['generate'] ) : false ); ?>
												@change="onChangeCredit(event)"
											>
											<span data-checked="✓" data-unchecked="✕" class="c-switch-slider"></span>

											</label>
										</c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4">
											<label><?php esc_attr_e( 'Allow Usage Tracking', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Allow non-sensitive usage tracking to help us provide improved features and faster fixes. No personal data is tracked or stored.', 'wplegalpages' ); ?>'"></c-icon></label>
										</c-col>
										<c-col class="col-sm-8">
											<c-switch v-bind="labelIcon" v-model="analytics_on" id="lp-analytics-on" variant="3d"  color="success" :checked="analytics_on" v-on:update:checked="onChangeAskForUsage"></c-switch>
											<input type="hidden" name="lp-analytics-on" v-model="analytics_on">
										</c-col>
									</c-row>
									<?php
								} else {
									?>
								<c-row class="wplegalpages-label-row">							
									<c-col class="col-sm-12">
										<label for="lp-domain-name"><?php esc_attr_e( 'Domain Name', 'wplegalpages' ); ?></label>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-12">
										<c-input type="text" name="lp-domain-name" placeholder=" Domain Name" value="<?php echo ! empty( $lp_general['domain'] ) ? esc_attr( $lp_general['domain'] ) : esc_url_raw( get_bloginfo( 'url' ) ); ?>"></c-input>
									</c-col>
								</c-row>

								<c-row class="wplegalpages-label-row">
									<c-col class="col-sm-6">
										<label for="lp-business-name"><?php esc_attr_e( 'Business Name', 'wplegalpages' ); ?></label>
									</c-col>
									<c-col class="col-sm-6">
										<label for="lp-trading-name"><?php esc_attr_e( 'DBA/Trading Name', 'wplegalpages' ); ?></label>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-6">
										<c-input id="lp-business-name" type="text" name="lp-business-name" placeholder=" Business Name" value="<?php echo ! empty( $lp_general['business'] ) ? esc_attr( $lp_general['business'] ) : ''; ?>"></c-input>
									</c-col>
									<c-col class="col-sm-6">
										<c-input id="lp-trading-name" type="text" name="lp-trading-name" placeholder=" DBA/Trading Name" value="<?php echo ! empty( $lp_general['trading'] ) ? esc_attr( $lp_general['trading'] ) : ''; ?>"></c-input>
									</c-col>
								</c-row>

								<c-row class="wplegalpages-label-row">
									<c-col class="col-sm-6">
										<label for="lp-phone"><?php esc_attr_e( 'Phone', 'wplegalpages' ); ?></label>
									</c-col>
									<c-col class="col-sm-6">
										<label for="lp-street"><?php esc_attr_e( 'Street', 'wplegalpages' ); ?></label>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-6">
										<c-input id="lp-phone" type="text"  name="lp-phone" placeholder=" Phone" pattern="^\+?[0-9\s\-]{10,15}$" title="Please enter a valid phone number (10-15 digits, may include + or -)"  value="<?php echo ! empty( $lp_general['phone'] ) ? esc_attr( $lp_general['phone'] ) : ''; ?>"></c-input>
									</c-col>
									<c-col class="col-sm-6">
										<c-input id="lp-street" type="text" name="lp-street" placeholder=" Street" value="<?php echo ! empty( $lp_general['street'] ) ? esc_attr( $lp_general['street'] ) : ''; ?>"></c-input>
									</c-col>
								</c-row>
								<c-row class="wplegalpages-label-row">
									<c-col class="col-sm-8">
										<label for="lp-city-state"><?php esc_attr_e( 'City, State, Zip code', 'wplegalpages' ); ?></label>
									</c-col>
									<c-col class="col-sm-4">
										<label for="lp-country"><?php esc_attr_e( 'Country', 'wplegalpages' ); ?></label>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-8">
										<c-input id="lp-city-state" type="text"  name="lp-city-state" placeholder=" City, State, Zip code" value="<?php echo ! empty( $lp_general['cityState'] ) ? esc_attr( $lp_general['cityState'] ) : ''; ?>">
									</c-col>
									<c-col class="col-sm-4">
										<c-input id="lp-country" type="text" class="wplegalpages-full-width-input" placeholder=" Country" name="lp-country" value="<?php echo ! empty( $lp_general['country'] ) ? esc_attr( $lp_general['country'] ) : ''; ?>"></c-input>
									</c-col>
								</c-row>
								<c-row class="wplegalpages-label-row">
									<c-col class="col-sm-4">
										<label for="lp-email"><?php esc_attr_e( 'Email', 'wplegalpages' ); ?></label>
									</c-col>
									<c-col class="col-sm-4">
										<label for="lp-address"><?php esc_attr_e( 'Address', 'wplegalpages' ); ?></label>
									</c-col>
									<c-col class="col-sm-4">
										<label for="lp-niche"><?php esc_attr_e( 'Niche', 'wplegalpages' ); ?></label>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-4"><c-input id="lp-email" type="email" placeholder=" Email" class="wplegalpages-full-width-input"  name="lp-email" title="Please enter a valid email address" value="<?php echo ! empty( $lp_general['email'] ) ? esc_attr( $lp_general['email'] ) : esc_attr( get_option( 'admin_email' ) ); ?>" ></c-input></c-col>
									<c-col class="col-sm-4"><c-input id="lp-address" type="text" placeholder=" Address" class="wplegalpages-full-width-input" name="lp-address" value="<?php echo ! empty( $lp_general['address'] ) ? esc_attr( $lp_general['address'] ) : ''; ?>"></c-input></c-col>
									<c-col class="col-sm-4"><c-input id="lp-niche" type="text" placeholder=" Niche" class="wplegalpages-full-width-input" name="lp-niche" value="<?php echo ! empty( $lp_general['niche'] ) ? esc_attr( $lp_general['niche'] ) : ''; ?>"></c-input></c-col>
								</c-row>
								<c-row class="wplegalpages-label-row">
								<c-col class="col-sm-6">
									<label for="lp-facebook-url"><?php esc_attr_e( 'Facebook URL', 'wplegalpages' ); ?></label>
								</c-col>
								<c-col class="col-sm-6">
									<label for="lp-google-url"><?php esc_attr_e( 'Google URL', 'wplegalpages' ); ?></label>
								</c-col>
							</c-row>
							<c-row>
								<c-col class="col-sm-6">
									<c-input id="lp-facebook-url" type="text" name="lp-facebook-url" placeholder=" Facebook URL" value="<?php echo ! empty( $lp_general['facebook-url'] ) ? esc_attr( $lp_general['facebook-url'] ) : ''; ?>" ></c-input>
								</c-col>
								<c-col class="col-sm-6">
									<c-input id="lp-google-url" type="text" name="lp-google-url" placeholder=" Google URL" value="<?php echo ! empty( $lp_general['google-url'] ) ? esc_attr( $lp_general['google-url'] ) : ''; ?>" ></c-input>
								</c-col>
							</c-row>
							<c-row class="wplegalpages-label-row">
								<c-col class="col-sm-6">
									<label for="lp-twitter-url"><?php esc_attr_e( 'Twitter URL', 'wplegalpages' ); ?></label>
								</c-col>
								<c-col class="col-sm-6">
									<label for="lp-linkedin-url"><?php esc_attr_e( 'LinkedIn URL', 'wplegalpages' ); ?> </label>
								</c-col>
							</c-row>
							<c-row>
								<c-col class="col-sm-6">
									<c-input id="lp-twitter-url" type="text" name="lp-twitter-url" placeholder=" Twitter URL" value="<?php echo ! empty( $lp_general['twitter-url'] ) ? esc_attr( $lp_general['twitter-url'] ) : ''; ?>" ></c-input>
								</c-col>
								<c-col class="col-sm-6">
									<c-input id="lp-linkedin-url" type="text" name="lp-linkedin-url" placeholder=" LinkedIn URL" value="<?php echo ! empty( $lp_general['linkedin-url'] ) ? esc_attr( $lp_general['linkedin-url'] ) : ''; ?>" ></c-input>
								</c-col>
							</c-row>
									<?php
								}
								?>
								<?php do_action( 'wplegalpages_admin_settings', $lp_general ); ?>
							</c-card-body>
						</c-card>
				<div class="wplegalpages-settings-bottom">
				<div class="wplegalpages-save-button">
					<c-button color="info" @click="saveGeneralSettings"><span><?php echo esc_html( 'Save Changes' ); ?></span></c-button>
				</div>
				</div>
					</c-tab>
					<?php do_action( 'wp_legalpages_after_general_tab' ); ?>
					<?php
					if ( ! $lp_show_improved_ui ) {
						do_action( 'wp_legalpages_after_data_tab' );
					} else {
						?>
					<c-tab title="<?php esc_attr_e( 'Advanced', 'wplegalpages' ); ?>" href="#settings#advanced" id="wplegalpages-advanced-setting">
						<?php do_action( 'wp_legalpages_notice' ); ?>
						<c-card>
							<c-card-body>
							<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-10">
										<label><?php esc_attr_e( 'Show Legal Pages in Search', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Allow search engines to display your legal pages in search results.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2">
										<input type="hidden" name="lp-search" v-model="search">
										<c-switch v-bind="labelIcon" ref="search"  id="inline-form-search" variant="3d" color="success" <?php checked( isset( $lp_general['search'] ) ? boolval( $lp_general['search'] ) : false ); ?> v-on:update:checked="onChangeSearch"></c-col>
									</c-col>
								</c-row>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-10">
										<label><?php esc_attr_e( 'Affiliate Disclosure', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'If you have an affiliate site, having an affiliate disclosure is must', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2">
										<input type="hidden" name="lp-affiliate-disclosure" v-model="affiliate_disclosure">
										<label for="inline-form-affiliate" class="c-switch form-check-label c-switch-3d-success c-switch-label">
											<span class="screen-reader-text"><?php esc_attr_e( 'Affiliate Disclosure toggle', 'wplegalpages'); ?></span>

											<input 
												type="checkbox" 
												id="inline-form-affiliate" 
												ref="affiliate_disclosure"
												value="1"
												class="c-switch-input"
												<?php checked( isset( $lp_general['affiliate-disclosure'] ) ? boolval( $lp_general['affiliate-disclosure'] ) : false ); ?>
												@change="onChangeAffiliate(event)"
											>
											<span data-checked="✓" data-unchecked="✕" class="c-switch-slider"></span>

										</label>
									</c-col>
								</c-row>
								<c-row v-show= "privacy" id="privacy_page_section" class="wplegal-support-text-row">
									<c-col class="col-sm-8 wplegalpages-input-for-toggle-button">
										<?php
										$lp_pages = get_posts(
											array(
												'post_type' => 'page',
												'post_status' => 'publish',
												'numberposts' => -1,
												'orderby' => 'title',
												'order'   => 'ASC',
												'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query	
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
												array_push( $options, $lp_page->post_title );
											}
										}
										$options = wp_json_encode( $options );
										?>
										<input type="hidden" ref="privacy_page" v-model="privacy_page" name="lp-privacy-page">
										<input type="hidden" ref="privacy_page_mount" value="<?php echo esc_html( stripslashes( isset( $lp_general['privacy_page'] ) ? $lp_general['privacy_page'] : '' ) ); ?>">
										<v-select placeholder="Select Privacy Policy Page" id="wplegalpages-select-roles" class="form-group input-with-support-text" :options="page_options" v-model="privacy_page"></v-select>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'The privacy policy disclaimer link in your forms will link to this page', 'wplegalpages' ); ?>
										</span>
									</c-col>
								</c-row>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-10">
										<label><?php esc_attr_e( 'Give Credit', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
										<?php esc_html_e( 'Display credits at bottom of your legal pages.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2">
										<input type="hidden" name="lp-generate" v-model="generate">
										<label for="inline-form-credits" class="c-switch form-check-label c-switch-3d-success c-switch-label">
										<span class="screen-reader-text"><?php esc_attr_e( 'Give Credit section toggle', 'wplegalpages'); ?></span>

										<input 
											type="checkbox" 
											id="inline-form-credits" 
											ref="generate"
											value="1"
											class="c-switch-input"
											<?php checked( isset( $lp_general['generate'] ) ? boolval( $lp_general['generate'] ) : false ); ?>
											@change="onChangeCredit(event)"
										>
										<span data-checked="✓" data-unchecked="✕" class="c-switch-slider"></span>

										</label>
										
									</c-col>
								</c-row>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-10">
										<label><?php esc_attr_e( 'Allow Usage Tracking', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
										<?php esc_html_e( 'Allow non-sensitive usage tracking to help us provide improved features and faster fixes. No personal data is tracked or stored.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2">
										<c-switch v-bind="labelIcon" v-model="analytics_on" id="lp-analytics-on" variant="3d"  color="success" :checked="analytics_on" v-on:update:checked="onChangeAskForUsage"></c-switch>
										<input type="hidden" name="lp-analytics-on" v-model="analytics_on">
									</c-col>
								</c-row>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-10">
										<label><?php esc_attr_e( 'Show WPLP Block in Gutenberg', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Enable this option to add a Gutenberg block for easily inserting any of your generated legal policies directly into the editor.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2">
										<input type="hidden" name="lp-enable-block" v-model="enable_block">
										<label for="lp-enable-block" class="c-switch form-check-label c-switch-3d-success c-switch-label">
											<span class="screen-reader-text"><?php esc_attr_e( 'Show WPLP Block in Gutenberg toggle', 'wplegalpages'); ?></span>

											<input 
												type="checkbox" 
												id="lp-enable-block" 
												ref="enable_block"
												value="1"
												class="c-switch-input"
												<?php checked( isset( $is_blocked_enabled ) ? boolval( $is_blocked_enabled ) : false ); ?>
												@change="onChangeEnableBlock(event)"
											>
											<span data-checked="✓" data-unchecked="✕" class="c-switch-slider"></span>

										</label>
									</c-col>
								</c-row>
							</c-card-body>
						</c-card>
						<div class="wplegalpages-settings-bottom">
				<div class="wplegalpages-save-button">
					<c-button color="info" @click="saveGeneralSettings"><span><?php echo esc_html( 'Save Changes' ); ?></span></c-button>
				</div>
			</div>
					</c-tab>
						<?php
					}
					?>
					<c-tab title="<?php esc_attr_e( 'Compliances', 'wplegalpages' ); ?>" href="#settings#compliances" id="wplegalpages-complianze">
					<input type="hidden" name="lp-is-footer" v-model="is_footer">
					<input type="hidden" v-model="footer_legal_pages" name="footer_legal_pages">
					<input type="hidden" ref="footer_font_family" v-model="footer_font" name="lp-footer-font-family">
					<input type="hidden" ref="footer_font_family_mount" value="<?php echo esc_html( stripslashes( $lp_footer_options['footer_font'] ) ); ?>">
					<input type="hidden" ref="footer_font_size" v-model="footer_font_size" name="lp-footer-font-size">
					<input type="hidden" ref="footer_font_size_mount" value="<?php echo esc_html( stripslashes( $lp_footer_options['footer_font_size'] ) ); ?>">
					<input type="hidden" name="lp-footer-new-tab" v-model="footer_new_tab">
					<input type="hidden" ref="footer_text_align" v-model="footer_text_align" name="lp-footer-text-align">
					<input type="hidden" ref="footer_text_align_mount" value="<?php echo esc_html( stripslashes( $lp_footer_options['footer_text_align'] ) ); ?>">
					<v-modal :append-to="appendField" :based-on="show_footer_form" title="Add Legal Pages Link to the Footer" @close="showFooterForm">
						<c-card id="wplp-conf-wrapper">
							<c-card-body>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Add Privacy Policy links to the footer.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<c-switch v-bind="labelIcon" ref="switch_footer" v-model="is_footer" id="wplegalpages-show-footer" variant="3d" color="success" :checked="is_footer" v-on:update:checked="onSwitchFooter"></c-switch>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Legal Pages', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the Legal Pages you want to add to the footer.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Legal Pages', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<v-select id="wplegalpages-footer-pages" :reduce="label => label.code" class="form-group" :options="page_options" multiple v-model="footer_pages" @input="onFooterPagesSelect">
										</v-select>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Background Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the background color for the footer section', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Background Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
										<c-input class="wplegalpages-color-input" type="text" v-model="link_bg_color"></c-input>
										<c-input class="wplegalpages-color-select" id="wplegalpages-lp-form-bg-color" type="color" name="lp-footer-bg-color" v-model="link_bg_color"></c-input>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the font.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<v-select class="form-group" id="wplegalpages-footer-font" :options="font_options" v-model="footer_font">
										</v-select>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font Size', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the Font size for the footer section.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font Size', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<v-select class="form-group" id="wplegalpages-footer-font-size" :options="font_size_options" v-model="footer_font_size">
										</v-select>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the color for the text.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
										<c-input class="wplegalpages-color-input" type="text" v-model="footer_text_color"></c-input>
										<c-input class="wplegalpages-color-select" id="wplegalpages-lp-form-text-color" type="color" name="lp-footer-text-color" v-model="footer_text_color"></c-input>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Alignment', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the text alignment.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Alignment', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<v-select class="form-group" id="wplegalpages-footer-align" :options="footer_align_options" v-model="footer_text_align">
										</v-select>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Link Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the color for links in the footer.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Link Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
										<c-input class="wplegalpages-color-input" type="text" v-model="footer_link_color"></c-input>
										<c-input class="wplegalpages-color-select" id="wplegalpages-lp-form-link-color" type="color" name="lp-footer-link-color" v-model="footer_link_color"></c-input>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Links Separator', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select link separator element.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Links Separator', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'Enter the element that separates the policy links. Like - . or *.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<c-input id="wplegalpages-lp-form-separator" type="text" name="lp-footer-separator" value="<?php echo esc_html( $lp_footer_options['footer_separator'] ); ?>"></c-input>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Open Link in New Tab', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Enable if you want to open links in the New Tab.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Open Link in New Tab', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<c-switch v-bind="labelIcon" ref="footer_new_tab" v-model="footer_new_tab" id="wplegalpages-footer-new-tab" variant="3d" color="success" :checked="footer_new_tab" v-on:update:checked="onClickNewTab"></c-switch>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Additional CSS', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'You can add CSS to change the style of the footer.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Additional CSS', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<vue-editor id="wplegalpages-lp-footer-custom-css" :editor-toolbar="customToolbarForm" v-model="footer_custom_css"></vue-editor>
									</c-col>
								</c-row>
								<c-row>
									<c-col class="col-sm-4"></c-col>
									<c-col class="col-sm-8">
										<p class="wplegalpages-custom-css-heading">Available CSS Selectors</p>
										<p class="wplegalpages-custom-css-selector">Container ID's: <span class="wplegalpages-custom-css-links" @click="addContainerID">#wplegalpages_footer_links_container</span></p>
										<p class="wplegalpages-custom-css-selector">Links class: <span class="wplegalpages-custom-css-links" @click="addLinksClass">.wplegalpages_footer_link</span></p>
										<p class="wplegalpages-custom-css-selector">Text class: <span class="wplegalpages-custom-css-links" @click="addTextClass">.wplegalpages_footer_separator_text</span></p>
									</c-col>
								</c-row>
								<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Links Order', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Drag to reorder the links.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
									<c-col class="col-sm-4"><label><?php esc_attr_e( 'Links Order', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'Drag to reorder the links.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<draggable id="wplegalpages-footer-order-links" v-model="footer_pages_drag" @change="onDragPages">
											<p class="wplegalpages-draggable-item" v-for="footer_page in footer_pages_drag" :key="footer_page.code">{{footer_page.label}}<img src="<?php echo esc_attr( WPL_LITE_PLUGIN_URL ) . 'admin/images/drag-icon.png'; ?>"/></p> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
										</draggable>
									</c-col>
								</c-row>
								<c-row class="wplegalpages-modal-footer">
									<c-col class="col-sm-4"><input type="hidden" id="wplegalpages-footer-form-nonce" name="lp-footer-form-nonce" value="<?php echo esc_attr( wp_create_nonce( 'settings_footer_form_nonce' ) ); ?>"/></c-col>
									<c-col class="col-sm-8 wplegalpages-modal-buttons">
										<c-button id="wplp-conf-save-btn" class="wplegalpages-modal-button" @click="saveFooterData" color="info"><span>Save</span></c-button>
										<c-button id="wplp-conf-cancel-btn" class="wplegalpages-modal-button" color="secondary" @click="showFooterForm"><span>Cancel</span></c-button>
									</c-col>
								</c-row>
							</c-card-body>
						</c-card>
					</v-modal>
					<input type="hidden" name="lp-is-banner" v-model="is_banner">
					<input type="hidden" ref="bar_position" v-model="bar_position" name="lp-bar-position">
					<input type="hidden" ref="bar_position_mount" value="<?php echo esc_html( stripslashes( isset( $lp_banner_options['bar_position'] ) ? $lp_banner_options['bar_position'] : '' ) ); ?>">
					<input type="hidden" ref="bar_type" v-model="bar_type" name="lp-bar-type">
					<input type="hidden" ref="bar_type_mount" value="<?php echo esc_html( stripslashes( isset( $lp_banner_options['bar_type'] ) ? $lp_banner_options['bar_type'] : '' ) ); ?>">
					<input type="hidden" ref="bar_num_of_days" v-model="bar_num_of_days" name="lp-bar-num-of-days">
					<input type="hidden" ref="bar_num_of_days_mount" value="<?php echo esc_html( stripslashes( isset( $lp_banner_options['bar_num_of_days'] ) ? $lp_banner_options['bar_num_of_days'] : '1' ) ); ?>">
					<input type="hidden" ref="banner_font_family" v-model="banner_font" name="lp-banner-font-family">
					<input type="hidden" ref="banner_font_family_mount" value="<?php echo esc_html( stripslashes( $lp_banner_options['banner_font'] ) ); ?>">
					<input type="hidden" ref="banner_font_size" v-model="banner_font_size" name="lp-banner-font-size">
					<input type="hidden" ref="banner_font_size_mount" value="<?php echo esc_html( stripslashes( isset( $lp_banner_options['banner_font_size'] ) ? $lp_banner_options['banner_font_size'] : '20px' ) ); ?>">
						<v-modal :append-to="appendField" :based-on="show_banner_form" title="Announcement Banner for Legal Pages" @close="showBannerForm">
							<c-card id="wplp-conf-wrapper">
								<c-card-body>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Show Announcement Banner.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<c-switch ref="switch_banner" v-bind="labelIcon" v-model="is_banner" id="wplegalpages-show-footer" variant="3d"  color="success" :checked="is_banner" v-on:update:checked="onSwitchBanner"></c-switch>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Bar Position', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the Announcement Bar Position.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Bar Position', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<v-select class="form-group" id="wplegalpages-bar-position" :options="bar_position_options" v-model="bar_position"></v-select>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Bar type', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'The fixed option ensures the  Announcement bar is visible even after users scroll down.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Bar type', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'The fixed option ensures the  Announcement bar is visible even after users scroll down.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<v-select class="form-group" id="wplegalpages-bar-type" :options="bar_type_options" v-model="bar_type"></v-select>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Duration', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select how long to show the announcement after any privacy policy is changed.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Announcement Duration', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'Enter the duration in days for which the banner should appear.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<v-select class="form-group" id="wplegalpages-bar-expiry" :options="banner_number_of_days" v-model="bar_num_of_days">
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Message', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Message that will be displayed when single privacy policy page is changed.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Message', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'Message that will be displayed when single privacy policy page is changed.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<vue-editor id="wplegalpages-lp-banner-message" :editor-toolbar="customToolbarForm" v-model="banner_message"></vue-editor></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"></c-col>
										<c-col class="col-sm-8">
											<p class="wplegalpages-custom-css-selector">Insert Shortcodes:
											<span class="wplegalpages-custom-css-links" @click="addBannerPageCode">title</span>
											<span class="wplegalpages-custom-css-links" @click="addBannerPageLinkTitle">link</span>
											<span class="wplegalpages-custom-css-links" @click="addBannerPageHref">href</span>
											<span class="wplegalpages-custom-css-links" @click="addBannerPageLed">last effective date</span>
											</p>
											<p class="wplegalpages-custom-css-selector">You can also
											<span class="wplegalpages-custom-css-links" @click="addBannerDefaultMsg">revert message to default.</span>
											</p>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Message for multiple updated pages', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Message that will be displayed when multiple privacy policy pages are changed.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Message for multiple updated pages', 'wplegalpages' ); ?> <tooltip text="<?php esc_html_e( 'Message that will be displayed when multiple privacy policy pages are changed.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
										<vue-editor id="wplegalpages-lp-banner-multiple-message" :editor-toolbar="customToolbarForm" v-model="banner_multiple_message"></vue-editor></c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"></c-col>
										<c-col class="col-sm-8">
											<p class="wplegalpages-custom-css-selector">Insert Shortcodes:
											<span class="wplegalpages-custom-css-links" @click="addBannerMultiplePageCode">titles</span>
											<span class="wplegalpages-custom-css-links" @click="addBannerMultiplePageLinkTitle">link</span>
											<span class="wplegalpages-custom-css-links" @click="addBannerMultiplePageLed">last effective date</span>
											</p>
											<p class="wplegalpages-custom-css-selector">You can also
											<span class="wplegalpages-custom-css-links" @click="addBannerMultipleDefaultMsg">revert message to default.</span>
											</p>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Close Button', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Edit the text for the close button.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Close Button', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<c-input type="text" name="lp-banner-font-size" id="wplegalpages-banner-close-message" v-model="banner_close_message" value="<?php echo ! empty( $lp_banner_options['banner_close_message'] ) ? esc_attr( $lp_banner_options['banner_close_message'] ) : 'Close'; ?>"></c-input>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Background Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the background color for the announcement barf.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Background Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
											<c-input class="wplegalpages-color-input" type="text" v-model="banner_bg_color"></c-input>
											<c-input class="wplegalpages-color-select" id="wplegalpages-lp-banner-bg-color" type="color" name="lp-banner-bg-color" v-model="banner_bg_color"></c-input>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the font.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<v-select class="form-group" id="wplegalpages-banner-font" :options="font_options" v-model="banner_font">
											</v-select>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font Size', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the Font size for the announcement bar.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Font Size', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<v-select class="form-group" id="wplegalpages-banner-font-size" :options="banner_font_size_option" v-model="banner_font_size">
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the color for the text.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Text Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
											<c-input class="wplegalpages-color-input" type="text" v-model="banner_text_color"></c-input>
											<c-input class="wplegalpages-color-select" id="wplegalpages-lp-banner-text-color" type="color" name="lp-banner-text-color" v-model="banner_text_color"></c-input>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Link Color', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Select the color for links in the announcement bar.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Link Color', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8 wplegalpages-color-pick">
											<c-input class="wplegalpages-color-input" type="text" v-model="banner_link_color"></c-input>
											<c-input class="wplegalpages-color-select" id="wplegalpages-lp-banner-link-color" type="color" name="lp-banner-link-color" v-model="banner_link_color"></c-input>
										</c-col>
									</c-row>
									<c-row>
									<?php
									if ( ! $lp_show_improved_ui ) {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Additional CSS', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'You can add CSS to change the style of the announcement bar.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
										<?php
									} else {
										?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Additional CSS', 'wplegalpages' ); ?></label></c-col>
										<?php
									}
									?>
									<c-col class="col-sm-8">
											<vue-editor id="wplegalpages-lp-banner-custom-css" :editor-toolbar="customToolbarForm" v-model="banner_custom_css"></vue-editor>
										</c-col>
									</c-row>
									<c-row>
										<c-col class="col-sm-4"></c-col>
										<c-col class="col-sm-8">
											<p class="wplegalpages-custom-css-heading">Available CSS Selectors</p>
											<p class="wplegalpages-custom-css-selector">Container ID's: <span class="wplegalpages-custom-css-links" @click="addBannerContainerID">.wplegalpages_banner_content</span></p>
											<p class="wplegalpages-custom-css-selector">Links class: <span class="wplegalpages-custom-css-links" @click="addBannerLinksClass">.wplegalpages_banner_link</span></p>
										</c-col>
									</c-row>
									<c-row class="wplegalpages-modal-footer">
										<c-col class="col-sm-4"><input type="hidden" id="wplegalpages-banner-form-nonce" name="lp-banner-form-nonce" value="<?php echo esc_attr( wp_create_nonce( 'settings_banner_form_nonce' ) ); ?>"/></c-col>
										<c-col class="col-sm-8 wplegalpages-modal-buttons">
											<c-button id="wplp-conf-save-btn" class="wplegalpages-modal-button" @click="saveBannerData" color="info"><span>Save</span></c-button>
											<c-button id="wplp-conf-cancel-btn" class="wplegalpages-modal-button" color="secondary" @click="showBannerForm"><span>Cancel</span></c-button>
										</c-col>
									</c-row>
								</c-card-body>
							</c-card>
						</v-modal>
						
						<?php
						$is_age                = get_option( '_lp_require_for' );
						$age_verify_for        = get_option( '_lp_always_verify' );
						$minimum_age           = get_option( '_lp_minimum_age' );
						$age_type_option       = get_option( '_lp_display_option' );
						$yes_button_text       = get_option( 'lp_eu_button_text' );
						$redirect_url_text     = get_option( '_lp_redirect_url' );
						$no_button_text        = get_option( 'lp_eu_button_text_no' );
						$age_verify_for_value  = $age_verify_for ? 'all' === $age_verify_for ? 'All visitors' : 'Guests only' : 'Guests only';
						$age_type_option_value = $age_type_option ? 'date' === $age_type_option ? 'Input Date of Birth' : 'Yes/No Buttons' : 'Yes/No Buttons';
						?>
						<input type="hidden" name="lp-age-verify" v-model="is_age">
						<input type="hidden" ref="age_verify_for" v-model="age_verify_for" name="lp-age-verify-for">
						<input type="hidden" ref="age_verify_for_mount" value="<?php echo esc_html( stripslashes( $age_verify_for_value ) ); ?>">
						<input type="hidden" ref="age_type_option" v-model="age_type_option" name="lp-age-type-option">
						<input type="hidden" ref="age_type_option_mount" value="<?php echo esc_html( stripslashes( $age_type_option_value ) ); ?>">
						<?php
						$lp_obj = new WP_Legal_Pages();
						if ( $lp_obj->wplegalpages_is_plan_pro() ) {
							$markup = $this->wplegalpages_fetch_age_verification_popup_markup();

							if ( ! empty( $markup) ) {

								ob_start();

								echo $markup['popup_markup1']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

								$popup_markup1 = ob_get_clean();

								echo $popup_markup1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								
								?>

								<input type="hidden" id="wplegalpages-age-form-nonce" name="lp-age-form-nonce" value="<?php echo esc_attr( wp_create_nonce( 'settings_age_form_nonce' ) ); ?>"/>

								<?php
								
								ob_start();

								echo $markup['popup_markup2']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

								$popup_markup2 = ob_get_clean();

								echo  $popup_markup2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

							}
						}
						?>

						<input type="hidden" class="wplegalpages-popup-switch" name="lp-popup-enable" v-model="is_popup">
						<v-modal :append-to="appendField" :based-on="show_popup_form" title="Create Popups" @close="showPopupForm">
							<c-card id="wplp-conf-wrapper">
								<c-card-body>
									<c-row>
										<?php
										if ( ! $lp_show_improved_ui ) {
											?>
											<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?> <c-icon name="cib-google-keep" color="primary" v-c-tooltip="'<?php esc_html_e( 'Enabling this setting will activate the Create Popup option in the WPLegalPages plugin menu.', 'wplegalpages' ); ?>'"></c-icon></label></c-col>
											<?php
										} else {
											?>
										<c-col class="col-sm-4"><label><?php esc_attr_e( 'Enabled', 'wplegalpages' ); ?> <tooltip :bottom="true" text="<?php esc_html_e( 'Enabling this setting will activate the Create Popup option in the WPLegalPages plugin menu.', 'wplegalpages' ); ?>"></tooltip></label></c-col>
											<?php
										}
										?>
										<c-col class="col-sm-8">
											<c-switch ref="switch_popup" v-bind="labelIcon" v-model="is_popup" id="wplegalpages-show-popup" variant="3d"  color="success" :checked="is_popup" v-on:update:checked="onSwitchPopup"></c-switch>
										</c-col>
									</c-row>
									<c-row class="wplegalpages-modal-footer">
										<c-col class="col-sm-4"><input type="hidden" id="wplegalpages-popup-form-nonce" name="lp-popup-form-nonce" value="<?php echo esc_attr( wp_create_nonce( 'settings_popup_form_nonce' ) ); ?>"/></c-col>
										<c-col class="col-sm-8 wplegalpages-modal-buttons">
											<c-button id="wplp-conf-save-btn" class="wplegalpages-modal-button" @click="savePopupData" color="info"><span>Save</span></c-button>
											<c-button id="wplp-conf-cancel-btn" class="wplegalpages-modal-button" color="secondary" @click="showPopupForm"><span>Cancel</span></c-button>
										</c-col>
									</c-row>
								</c-card-body>
							</c-card>
						</v-modal>
						<div>
							<c-card-body>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-7 wplegal-compliances-text">
										<label><?php esc_attr_e( 'Add Legal Pages Link to the Footer', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Display links to your legal pages in the footer section of your website.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2 wplegal-compliances-switch">
										<c-switch v-bind="labelIcon" v-model="is_footer" variant="3d"  color="success" :checked="is_footer" v-on:update:checked="onClickFooter"></c-switch>
										<input type="hidden" name="lp-footer" ref="footer" v-model="is_footer">
									</c-col>
									<c-col class="col-sm-3  wplegalpages-configure-section">
										<c-button class="wplegalpages-configure-button" @click="showFooterForm">
											<span>
												<img class="wplegalpages-configure-image" :src="configure_image_url.default" :alt="configure_alt_text"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
												<?php esc_attr_e( 'Configuration', 'wplegalpages' ); ?>
											</span>
										</c-button>
									</c-col>
								</c-row>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-7 wplegal-compliances-text">
										<label><?php esc_attr_e( 'Announcement Banner for Legal Pages', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Display announcement banners on your website whenever any legal pages have been updated.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2 wplegal-compliances-switch">
										<c-switch v-bind="labelIcon" v-model="is_banner" variant="3d"  color="success" :checked="is_banner" v-on:update:checked="onClickBanner"></c-switch>
										<input type="hidden" name="lp-banner" ref="banner" v-model="is_banner">
									</c-col>
									<c-col class="col-sm-3 wplegalpages-configure-section">
										<c-button class="wplegalpages-configure-button" @click="showBannerForm">
											<span>
												<img class="wplegalpages-configure-image" :src="configure_image_url.default" :alt="configure_alt_text"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
												<?php esc_attr_e( 'Configuration', 'wplegalpages' ); ?>
											</span>
										</c-button>
									</c-col>
								</c-row>

								<?php
								if ( ! $lp_obj->wplegalpages_is_plan_pro() ) {
									?>
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-7 wplegal-compliances-text">
										<label><?php esc_attr_e( 'Add Age Verification popup', 'wplegalpages' ); ?> <span class="badge bg-primary"><?php esc_attr_e( 'Pro', 'wplegalpages' ); ?></span></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Display an age verification popup on your website to make sure that your users are old enough to browse your website content.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2 wplegal-compliances-switch">
										<label for="wplp-not-pro-tooltip" class="c-switch form-check-label c-switch-3d-success c-switch-label wplegalpages-not-pro-tooltip">
											<span class="screen-reader-text"><?php esc_attr_e( 'variant toggle', 'wplegalpages'); ?></span>

											<input 
												type="checkbox" 
												id="wplp-not-pro-tooltip"
												class="c-switch-input" 
												disabled
											>
											<span data-checked="✓" data-unchecked="✕" class="c-switch-slider"></span>
										</label>
										<div class="wplegalpages-not-pro-tooltip-text"><?php echo esc_html_e( 'This feature is only available in the Pro version. Kindly', 'wplegalpages' );?> <a href="<?php echo esc_url( 'https://wplegalpages.com/pricing/?utm_source=wplegalpagesplugin&utm_medium=age-verification-tooltip' ); ?>" target="_blank"><?php echo esc_html_e( 'UPGRADE', 'wplegalpages' ); ?></a> <?php esc_html_e( 'to unlock and use it.', 'wplegalpages' ) ?></div>
									</c-col>
									<c-col class="col-sm-3 wplegalpages-configure-section">
										<c-button class="wplegalpages-configure-button wplegalpages-not-pro-tooltip" disabled>
											<span>
												<img class="wplegalpages-configure-image" :src="configure_image_url.default" :alt="configure_alt_text"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
												<?php esc_attr_e( 'Configuration', 'wplegalpages' ); ?>
											</span>
										</c-button>
										<div class="wplegalpages-not-pro-tooltip-text"><?php echo esc_html_e( 'This feature is only available in the Pro version. Kindly', 'wplegalpages' );?> <a href="<?php echo esc_url( 'https://wplegalpages.com/pricing/?utm_source=wplegalpagesplugin&utm_medium=age-verification-tooltip' ); ?>" target="_blank"><?php echo esc_html_e( 'UPGRADE', 'wplegalpages' ); ?></a> <?php esc_html_e( 'to unlock and use it.', 'wplegalpages' ) ?></div>
									</c-col>
								</c-row>
								<?php } else {

									if ( ! empty( $markup) ) {

										ob_start();
										
										echo $markup['setting_markup']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										
										$setting_markup = ob_get_clean();

										echo $setting_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}

								} ?>			
								<c-row class="wplegal-support-text-row">
									<c-col class="col-sm-7 wplegal-compliances-text">
										<label><?php esc_attr_e( 'Create Popups', 'wplegalpages' ); ?></label>
										<span class="wplegalpages-help-text">
											<?php esc_html_e( 'Enabling this setting will activate the Create Popup option in the WPLegalPages plugin menu.', 'wplegalpages' ); ?>
										</span>
									</c-col>
									<c-col class="col-sm-2 wplegal-compliances-switch">
										<c-switch v-bind="labelIcon" v-model="is_popup" variant="3d"  color="success" :checked="is_popup" v-on:update:checked="onClickPopup"></c-switch>
										<input type="hidden" name="lp-popup" ref="popup" v-model="is_popup">
									</c-col>
									<c-col class="col-sm-3 wplegalpages-configure-section">
										<c-button class="wplegalpages-configure-button" @click="showPopupForm">
											<span>
												<img class="wplegalpages-configure-image" :src="configure_image_url.default" :alt="configure_alt_text"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?> 
												<span class="wplegalpages-configure-text"><?php esc_attr_e( 'Configuration', 'wplegalpages' ); ?></span>
											</span>
										</c-button>
									</c-col>
								</c-row>
							</c-card-body>
						</div>
						<div class="wplegalpages-settings-bottom">
				<div class="wplegalpages-save-button">
					<c-button color="info" @click="saveGeneralSettings"><span><?php echo esc_html( 'Save Changes' ); ?></span></c-button>
				</div>
			</div>
					</c-tab>

					<!-- disconnection tab  -->

					<?php if ( $is_user_connected ) : ?>
						<c-tab title="<?php esc_attr_e( 'Connection', 'wplegalpages' ); ?>" href="#settings#connection" id="gdpr-cookie-consent-connection">

						<c-card class="wplegal-connection-tab-card">

									<c-card-body class="wplegal-connection-card-body" >
									<div class="wplegal-connection-success-tick">
									<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ) . 'images/Check_ring.svg'; ?>" alt="API Connection Success Mark"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
									</div>
									<div class="wplegal-connect-information">

										<h3><?php esc_html_e( 'Your website is connected to WP Legal Pages', 'wplegalpages' ); ?></h3>

										<p class="gpdr-email-info"><span class="wplegal-info-title" ><?php esc_html_e( 'Email : ', 'wplegalpages' ); ?></span> <?php echo esc_html( $api_user_email ); ?>  </p>
										<p><span class="wplegal-info-title" ><?php esc_html_e( 'Site Key : ', 'wplegalpages' ); ?></span> <?php echo esc_html( $api_user_site_key ); ?>  </p>
										<p><span class="wplegal-info-title" ><?php esc_html_e( 'Plan : ', 'wplegalpages' ); ?></span> <?php echo esc_html( $api_user_plan ); ?>  </p>
										<!-- API Disconnect Button  -->
										<div class="wplegal-api-connection-disconnect-btn" ><?php esc_attr_e( 'Disconnect', 'wplegalpages' ); ?></div>

									</div>
								</c-card-body>
							</c-card>

						</c-tab>
					<?php endif; ?>


					<?php
					if ( $lp_show_improved_ui ) {
						?>
					<!-- hide shortcode tab in settings since revamp   -->
					<c-tab style="display:none" title="<?php esc_attr_e( 'Shortcodes', 'wplegalpages' ); ?>" href="#settings#shortcodes">
						<c-card>
							<c-card-body>
								<?php
									$shortcodes = array(
										'Domain'        => '[Domain]',
										'Business Name' => '[Business Name]',
										'Trading Name'  => '[Trading Name]',
										'Phone'         => '[Phone]',
										'Street'        => '[Street]',
										'City,State,Zip,Code' => '[City,State,Zip,Code]',
										'Country'       => '[Country]',
										'Email'         => '[Email]',
										'Address'       => '[Address]',
										'Niche'         => '[Niche]',
									);
									?>
									<c-row class="wplegalpages-shortcode-tab-description">
										<span>
											<?php echo esc_html( 'The shortcodes below can be used in place of the mentioned fields in the privacy policy.  For any further information refer the ' ); ?>
											<a href="https://club.wpeka.com/docs/wp-legal-pages/" target="_blank"><?php echo esc_html( 'product documentation' ); ?></a>
										</span>
									</c-row>
								<div class="wplegalpages-shortcode-table">
									<c-row class="wplegalpages-shortcode-table-odd wplegalpages-shortcode-table-header">
										<c-col class="col-sm-6"><?php echo esc_attr( 'Name' ); ?></c-col>
										<c-col class="col-sm-6"><?php echo esc_attr( 'ShortCode' ); ?></c-col>
									</c-row>
									<?php
										$i = 0;
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
									do_action( 'wplegalpages_shortcodes_table' );
									?>
								</div>
							</c-card-body>
						</c-card>
					</c-tab>
						<?php
					}
					?>
				</c-tabs>
			</div>
			
		</c-form>
				</div>
</div>
	<?php
} else {
	?>
	<h2 class="hndle myLabel-head">DISCLAIMER</h2>
	<form action="" method="post">
	<textarea rows="20" cols="130">WPLegalPages.com ("Site") and the documents or pages that it may provide, are provided on the condition that you accept these terms, and any other terms or disclaimers that we may provide.  You may not use or post any of the templates or legal documents until and unless you agreed.  We are not licensed attorneys and do not purport to be.

WPLegalPages.com is not a law firm, is not comprised of a law firm, and its employees are not lawyers.  We do not review your site and we will not review your site. We do not purport to act as your attorney and do not make any claims that would constitute legal advice. We do not practice law in any state, nor are any of the documents provided via our Site intended to be in lieu of receiving legal advice.  The information we may provide is general in nature, and may be different in your jurisdiction.  In other words, do not take these documents to be "bulletproof" or to give you protection from lawsuits.  They are not a substitute for legal advice and you should have an attorney review them.

Accordingly, we disclaim any and all liability and make no warranties, including disclaimer of warranty for implied purpose, merchantability, or fitness for a particular purpose.  We provide these documents on an as is basis, and offer no express or implied warranties.  The use of our plugin and its related documents is not intended to create any representation or approval of the legality of your site and you may not represent it as such.  We will have no responsibility or liability for any claim of loss, injury, or damages related to your use or reliance on these documents, or any third parties use or reliance on these documents.  They are to be used at your own risk.  Your only remedy for any loss or dissatisfaction with WPLegalPages is to discontinue your use of the service and remove any documents you may have downloaded.

To the degree that we have had a licensed attorney review these documents it is for our own internal purposes and you may not rely on this as legal advice.  Since the law is different in every state, you should have these documents reviewed by an attorney in your jurisdiction.  As stated below, we disclaim any and all liability and warranties, including damages or loss that may result from your use or misuse of the documents.  Unless prohibited or limited by law, our damages in any matter are limited to the amount you paid for the WPLegalPages plugin.</textarea><br/><br/>
	Please Tick this checkbox to accept our Terms and Policy <input type="checkbox" name="lp_accept_terms" value="1"
	<?php
	if ( '1' === $lpterms ) {
		echo 'checked';}
	?>
	onclick="jQuery('#lp_submit').toggle();"/>
	<?php
	if ( function_exists( 'wp_nonce_field' ) ) {
		wp_nonce_field( 'lp-submit-accept-terms' );
	}
	?>
	<br/><br/><input type="submit" name="lp_submit" class="btn btn-primary"  id="lp_submit" style="display:none;" value="Accept" />
	</form>
	<?php
}
?>
