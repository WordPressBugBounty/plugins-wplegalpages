<?php

/**
 * Provide a admin area view for the WP Legal Pages plugin
 *
 * This file is used to markup the admin-facing aspects of the WP Legal Pages plugin.
 *
 * @link       https://wplegalpages.com/
 * @since      3.0.0
 *
 * @package Wplegalpages
 */

// // Instantiate a new object of the wplegal_Cookie_Consent_Settings class.
$this->settings = new WP_Legal_Pages_Settings();

$is_user_connected     = $this->settings->is_connected();
$api_user_plan     = $this->settings->get_plan();
$lp_pro_active         = get_option( '_lp_pro_active' );
$popup                 = get_option( 'lp_popup_enabled' );
$lp_pro_installed      = get_option( '_lp_pro_installed' );
$lp_pro_key_activated  = get_option( 'wc_am_client_wplegalpages_pro_activated' );
$if_terms_are_accepted = get_option( 'lp_accept_terms' );

$installed_plugins = get_plugins();
$plugin_name                   = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
$is_gdpr_active = is_plugin_active( $plugin_name );
$plugin_name_lp                   = 'wplegalpages/wplegalpages.php';
$is_legalpages_active = is_plugin_active( $plugin_name_lp );

?>

<div id="wp-legalpages-main-admin-structure" class="wp-legalpages-main-admin-structure">
	<div id="wp-legalpages-main-admin-header" class="wp-legalpages-main-admin-header">
		<!-- Main top banner  -->
		<div class="wp-legalpages-admin-fixed-banner">
				<div class="wp-legalpages-admin-logo-and-label">
					<div class="wp-legalpages-admin-logo">
						<!-- //image  -->
						<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/WPLPCompliancePlatformWhite.png'; ?>" alt="WP Cookie Consent Logo">
					</div>
					
				</div>
				<!-- <div id="wplegalpages-save-settings-alert"><img src="<?php echo WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/settings_saved.svg'; ?>" alt="create legal" class="wplegal-save-settings-icon"><?php esc_attr_e( 'Settings saved successfully', 'wplegalpages' ); ?></div> -->
				<div class="wp-legalpages-admin-help-and-support">
				<div class="wp-legalpages-admin-help">
						<div class="wp-legalpages-admin-help-icon">
							<!-- //image  -->
							<a href="https://club.wpeka.com/docs/wp-legal-pages/" target="_blank">
								<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/wp_cookie_help.png'; ?>" alt="WP Cookie Consent Help">
							</a>
						</div>
						<div class="wp-legalpages-admin-help-text"><a href="https://club.wpeka.com/docs/wp-legal-pages/" target="_blank">
							Help Guide</a>
						</div>
					</div>
					<div class="wp-legalpages-admin-support">
						<!-- //support  -->
						<div class="wp-legalpages-admin-support-icon">
							<!-- //image  -->
							<a href="https://club.wpeka.com/contact/" target="_blank">
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/wp_cookie_support.png'; ?>" alt="WP Cookie Consent Support">
							</a>
						</div>
						<div class="wp-legalpages-admin-support-text"><a href="https://club.wpeka.com/contact/" target="_blank">
							Support</a>
						</div>
					</div>
				</div>
		</div>
		<!-- Connect your banner to WP Legal Pages  -->
		<?php
		if ( $if_terms_are_accepted ) { 
			if ( $is_user_connected != true ) {
				?>
			<div class="wplegalpages-connect-api-container">
				<div class="gdpr-api-info-content">
				<div class="gdpr-api-detailed-info">
				<h2>
					<?php echo esc_html( 'Connect your website to WP Legal Pages Compliance Platform', 'wplegalpages' ); ?>
				</h2>
					<p><?php echo esc_html( 'Sign up for a free account to integrate seamlessly with the WP Legal Pages Compliance Platform server. Once connected, gain full control over your settings and unlock advanced features:', 'wplegalpages' ); ?></p>
				<p>
					<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.png'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( '25+ Legal Templates:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Choose from a variety of pre-written templates for essential legal documents like Privacy Policies, Terms & Conditions, DMCA Notices, etc.
					', 'wplegalpages' ); ?>
				</p>
				<p>
					<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.png'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Cookie Scanner:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Identify cookies on your website and automatically block them before user consent (essential for legal compliance).
					', 'wplegalpages' ); ?>
				</p>
				<p>
					<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.png'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Advanced Dashboard:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Unlock useful insights on user\'s consent data, cookie summary, and consent logs.', 'wplegalpages' ); ?>
				</p>
				<p>
					<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.png'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Geo-targeting:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Display or hide the GDPR cookie consent notice depending on the visitor’s location.', 'wplegalpages' ); ?>
				</p>
				<p>
					<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.png'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Age Verification Popup:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Add an age gate to restrict users below a particular age from entering your website.
					', 'wplegalpages' ); ?>
				</p>
			</div>
			<div class="gdpr-api-connection-btns">
				<button class="gdpr-start-auth"><?php echo esc_html( 'New? Create a free account', 'wplegalpages' ); ?></button>
				<button class="api-connect-to-account-btn"><?php echo esc_html( 'Connect your existing account', 'wplegalpages' ); ?></button>
			</div>
				</div>
				<div id="popup-site-excausted" class="popup-overlay">
					<div class="popup-content">
						<div class="popup-header">
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/cancel.svg'; ?>" alt="Right Corner Image" class="popup-image">
						</div>
						<div class="excausted-popup-body">
							<h2><?php esc_html('Attention! Usage Limit Reached', 'wplegalpages'); ?></h2>
							<p><?php esc_html('You\'ve reached your license limit. Please upgrade to continue using the plugin on this site.', 'wplegalpages'); ?></p>
							<button class="wplegalpages-admin-upgrade-button upgrade-button"><?php esc_html('Upgrade Plan', 'wplegalpages'); ?></button>
							<p>
								<?php 
								esc_html('Need to activate on a new site? Manage your licenses in ', 'wplegalpages'); 
								?>
								<a href="<?php echo esc_url('https://app.wplegalpages.com/signup/api-keys/'); ?>" target="_blank">
									<?php esc_html('My Account.', 'wplegalpages'); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>

				<?php

			}
		}
		?>
		<!-- WP Legal Pages Connection Status -->
		<?php
		if ( $if_terms_are_accepted ) { 
				// if user is connected to the app.wplegalpages then show remaining scans
				if ( $is_user_connected == true) {
					?>
					<div class="gdpr-remaining-scans-content" >
						<div class="gdpr-current-plan-container">
							<p><span>Current Plan: </span><?php echo $api_user_plan; ?></p>
							<?php
							if ( $api_user_plan == 'free' ) {
								?>
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/gdpr_upgrade_btn.png'; ?>" class="wplegalpages-admin-upgrade-button wplegalpages-connection-popup" alt="<?php echo esc_attr( 'Upgrade Button', 'wplegalpages' ); ?>">
								<?php
							}
							?>
						</div>
						</div>
					<?php

				}
			}
				?>
		<?php if ( $if_terms_are_accepted ) { ?>
			<div class="legalpages-banner-div">
			<?php
			if ( $is_user_connected == true && $api_user_plan == 'free' ) {
				?>
			<!-- Legal pages banner for upgrade to pro -->
			<a href="https://app.wplegalpages.com/checkout/?add-to-cart=143&utm_source=wplegalpagesplugin&utm_medium=banner" target="_blank">
				<img class="legal-pages-upgrade-to-pro-banner" src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/legal-pages-banner-upgrade-to-pro.png'; ?>" alt="Banner legal pages">
			</a> 
				<?php
			}
		}
		?>
		</div>
		<!-- tabs -->
		<div class="wp-legalpages-admin-tabs-section">
			<div class="wp-legalpages-admin-tabs">
				<!-- Dashboard tab  -->
				<?php 
					
					if ($is_gdpr_active) {
						$plugin_slug = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
						
						// Fetch the plugin data
						$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_slug);
			
						// Get the version
						$gdpr_version = $plugin_data['Version'];
						if($gdpr_version >= '3.7.0') { ?>
							<a href="?page=wplp-dashboard" class="wp-legalpages-admin-tab-link dashboard-tab">
								<div class="wp-legalpages-admin-wplp-tab" >
										<?php echo esc_html('Dashboard','wplegalpages'); ?>
								</div>
							</a>
				<?php } }else{
					?>
					<a href="?page=wplp-dashboard" class="wp-legalpages-admin-tab-link dashboard-tab">
								<div class="wp-legalpages-admin-wplp-tab" >
										<?php echo esc_html('Dashboard','wplegalpages'); ?>
								</div>
							</a>
				<?php
					} ?>

				<?php
				// if terms are accepted only then show rest of the tabs
				if ( $if_terms_are_accepted ) {

					?>

					<!-- Legal Pages tab  -->
					<a href="?page=legal-pages" class="wp-legalpages-admin-tab-link legalpages-tab">
					<div class="wp-legalpages-admin-wplp-tab">
						<?php echo esc_html('Legal Pages','wplegalpages'); ?>
					</div>
					</a>
					<!-- Cookie Consent tab  -->
					
					<a href="?page=gdpr-cookie-consent" class="wp-legalpages-admin-tab-link gdpr-cookie-consent-tab">

					<div class="wp-legalpages-admin-wplp-tab">
					<?php echo esc_html('Cookie Consent','wplegalpages'); ?>
					</div>
					</a>
					<!-- Help tab  -->
					<?php if ($is_gdpr_active) {
						$plugin_slug = 'gdpr-cookie-consent/gdpr-cookie-consent.php';
						
						// Fetch the plugin data
						$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_slug);
			
						// Get the version
						$gdpr_version = $plugin_data['Version'];
						if($gdpr_version >= '3.7.0') {  ?>
					<a href="?page=wplp-dashboard#help-page" class="wp-legalpages-admin-tab-link">
					<div class="wp-legalpages-admin-wplp-tab">
						<?php echo esc_html('Help','wplegalpages'); ?>
					</div>
					</a>
					<?php }} else{
						?>
					<a href="?page=wplp-dashboard#help-page" class="wp-legalpages-admin-tab-link">
					<div class="wp-legalpages-admin-wplp-tab">
						<?php echo esc_html('Help','wplegalpages'); ?>
					</div>
					</a>
						<?php
					} ?>
					<?php

				}

				?>
				

			</div>

			<div class="wp-legalpages-admin-tabs wplp-sub-tabs">
				<!-- Gettins Started tab  -->
				<div class="wp-legalpages-admin-tab wp-legalpages-admin-getting-started-tab" data-tab="getting_started">
					Getting&nbsp;Started
				</div>

				<?php
				// if terms are accepted only then show rest of the tabs
				if ( $if_terms_are_accepted ) {

					?>

					<!-- Create Legal Pages tab  -->
					<div class="wp-legalpages-admin-tab wp-legalpages-admin-create_legalpages-tab" data-tab="create_legal_page">
						Create&nbsp;Legal&nbsp;Pages
					</div>
					<!-- Settings tab  -->
					<div class="wp-legalpages-admin-tab wp-legalpages-admin-settings-tab" data-tab="settings">
						Settings
					</div>
					<!-- All Legal Pages data tab  -->
					<div class="wp-legalpages-admin-tab wp-legalpages-admin-all_legalpages-tab" data-tab="all_legal_pages">
					All Legal Pages
					</div>
					<?php

				}

				?>
				<!-- tab for create popup  -->
				<?php
				// first check if popup is activated
				if ( $popup ) {
					?>
						<div class="wp-legalpages-admin-tab wp-legalpages-admin-create-popups-tab" data-tab="create_popup">
						<p class="wp-legalpages-admin-tab-name">Create&nbsp;Popups</p>
						</div>

					<?php
				}

				?>

			</div>
		</div>

		<!-- tab content  -->

		<div class="wp-legalpages-admin-tabs-content">
			<div class="wp-legalpages-admin-tabs-inner-content">
				<!-- Getting Started content  -->
				<div class="wp-legalpages-admin-getting-started-content wp-legalpages-admin-tab-content" id="getting_started">

				<?php require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-getting-started-template.php'; ?>

				</div>
				<!-- create cookie content  -->
				<div class="wp-legalpages-admin-create-cookie-content wp-legalpages-admin-tab-content" id="create_legal_page">

				</div>
				<!-- settings content -->
				<div class="wp-legalpages-admin-cookie-settings-content wp-legalpages-admin-tab-content" id="settings">

				<?php require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-settings-template.php'; ?>

				</div>
				<!-- all legalpages data content  -->
				<div class="wp-legalpages-admin-all-legalpages-data-content wp-legalpages-admin-tab-content" id="all_legal_pages">

				<?php require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-all-legalpages-template.php'; ?>

				</div>
				<!-- create popup  -->
				<div class="wp-legalpages-admin-legal-pages-content wp-legalpages-admin-tab-content" id="create_popup">

				<?php
					require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-create-popups-template.php';
				?>


				</div>
				<!-- help content  -->
				<div class="wp-legalpages-admin-help-page-content wp-legalpages-admin-tab-content" id="help-page">

				<?php
					require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-help-page-template.php';
				?>

				</div>
			</div>
		</div>

	</div>
</div>
