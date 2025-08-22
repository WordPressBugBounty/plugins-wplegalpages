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
						<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/WPLPCompliancePlatformWhite.png'; ?>" alt="WP Cookie Consent Logo"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
					</div>
					
				</div>
				<!-- <div id="wplegalpages-save-settings-alert"><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/settings_saved.svg' ); ?>" alt="create legal" class="wplegal-save-settings-icon"><?php esc_attr_e( 'Settings saved successfully', 'wplegalpages' ); ?></div> --> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				<div class="wp-legalpages-admin-help-and-support">
				<div class="wp-legalpages-admin-help">
						<div class="wp-legalpages-admin-help-icon">
							<!-- //image  -->
							<a href="https://club.wpeka.com/docs/wp-legal-pages/" target="_blank">
								<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/wp_cookie_help.png'; ?>" alt="WP Cookie Consent Help"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
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
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/wp_cookie_support.png'; ?>" alt="WP Cookie Consent Support"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
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
				<div class="gdpr-api-detailed-info-wrapper">
					<div class="gdpr-api-detailed-info">
						<h2>
							<?php echo esc_html( 'Sign Up for Free to Access Core Features', 'wplegalpages' ); ?>
						</h2>
						<p><?php echo esc_html( 'Get started with essential tools to manage cookies and legal policies:', 'wplegalpages' ); ?></p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Cookie Insights:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Detailed reports on cookies detected on your site.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Cookie Scanner:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Automatically scan your website for cookies.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'A/B Testing:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Compare two cookie banners to find the best performer.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Consent Log:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Track and store user consent records.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Data Subject Access Request:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Simplify user data requests.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Essential Legal Policies:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Generate key policies like Privacy Policy, Terms of Use, and more.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
					</div>
				<div class="gdpr-api-connection-btns">
					<button class="gdpr-start-auth gdpr-signup"><?php echo esc_html( 'Sign Up for Free', 'wplegalpages' ); ?></button>
					<p><?php echo esc_html( 'Already have an account?', 'wplegalpages' ); ?><a class="gdpr-start-auth gdpr-login" href=""><?php esc_html_e( 'Connect your existing account', 'wplegalpages' ); ?></a></p>
				</div>
			</div>

			<div class="gdpr-api-detailed-info-wrapper">
					<div class="gdpr-api-detailed-info">
						<h2>
							<?php echo esc_html( 'Upgrade to Pro for Advanced Features', 'gdpr-cookie-consent' ); ?>
						</h2>
						<p><?php echo esc_html( 'Take your website compliance to the next level with Pro:', 'gdpr-cookie-consent' ); ?></p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Advanced Dashboard:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Gain detailed insights into cookie consent performance.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Geo-targeting:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Show banners tailored to visitor locations.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'IAB TCF 2.2 Support:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Comply with the latest transparency framework.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( 'Google Consent Mode:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Manage Google tags based on user consent.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( '25+ Legal Templates:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Access a library of customizable templates.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
						<p>
							<span><img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/bullet_point.svg'; ?>" alt="<?php echo esc_attr( 'API Connection Success Mark', 'wplegalpages' ); ?>"></span> <strong><?php echo esc_html( '20,000 Pages per Scan:', 'wplegalpages' ); ?></strong> <?php echo esc_html( 'Ensure comprehensive website cookie scanning.', 'wplegalpages' ); ?> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</p>
					</div>
				<div class="gdpr-api-connection-btns">
					<button class="gdpr-cookie-consent-admin-upgrade-button upgrade-button">Upgrade to Pro</button>
				</div>
			</div>
				

				</div>
				<div id="popup-site-excausted" class="popup-overlay">
					<div class="popup-content">
						<div class="popup-header">
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/cancel.svg'; ?>" alt="Right Corner Image" class="popup-image"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
						</div>
						<div class="excausted-popup-body">
							<h2><?php esc_html('Attention! Usage Limit Reached', 'wplegalpages'); ?></h2>
							<p><?php esc_html('You\'ve reached your license limit. Please upgrade to continue using the plugin on this site.', 'wplegalpages'); ?></p>
							<button class="wplegalpages-admin-upgrade-button upgrade-button"><?php echo esc_html('Upgrade Plan', 'wplegalpages'); ?></button>
							<p>
								<?php 
								esc_html('Need to activate on a new site? Manage your licenses in ', 'wplegalpages'); 
								?>
								<a href="<?php echo esc_url('https://app.wplegalpages.com/signup/api-keys/'); ?>" target="_blank">
									<?php echo esc_html('My Account.', 'wplegalpages'); ?>
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
							<p><span>Current Plan: </span><?php echo esc_html( $api_user_plan ); ?></p>
							<?php
							if ( $api_user_plan == 'free' ) {
								?>
							<img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/gdpr_upgrade_btn.png'; ?>" class="wplegalpages-admin-upgrade-button wplegalpages-connection-popup" alt="<?php echo esc_attr( 'Upgrade Button', 'wplegalpages' ); ?>"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
								<?php
							}
							?>
						</div>
						</div>
					<?php
					if ( get_transient( 'app_wplp_subscription_payment_status_failed' ) ) {
						?>
						<div class="wp-legalpages-subsription-payment-failed-notice">
							<p><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Your last payment attempt failed. Please update your payment details within 7 days to avoid service disruption.', 'wplegalpages' ); ?></p>
						</div>
						<?php
					}
					if ( get_option( 'app_wplp_subscription_status_pending_cancel' ) ) {
						?>
						<div class="wp-legalpages-subsription-payment-failed-notice">
							<p><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Your plan has been canceled to the Free Plan due to a failed payment or manual cancellation. Upgrade now to restore premium features.', 'wplegalpages' ); ?></p>
						</div>
						<?php
					}
				}
			}
				?>
		<?php if ( $if_terms_are_accepted ) { ?>
			<div class="legalpages-banner-div">
			<?php
			if ( $is_user_connected == true && $api_user_plan == 'free' ) {
				?>
			<!-- Legal pages banner for upgrade to pro -->
			<div>
				<img class="legal-pages-upgrade-to-pro-banner" src="<?php echo esc_url( WPL_LITE_PLUGIN_URL ) . 'admin/images/legal-pages-banner-upgrade-to-pro.png'; ?>" alt="Banner legal pages"> <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
			</div> 
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
						Create&nbsp;Popups

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
				if( $is_user_connected ){
					require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-create-popups-template.php';
				} else {
					wp_enqueue_style('wp-legal-pages-vue-wizard', plugin_dir_url(__FILE__) . '../css/vue/vue-wizard.css', array(), '1.0.0', 'all');
					?>
					<div class="wplegal-api-connection-popup">
							<h3>
								<?php echo esc_html('Connect Your Website','wplegalpages'); ?>
							</h3>
							<p class="wplegal-api-upgrade-text">
								<?php echo esc_html('Sign up for an account to use this feature.','wplegalpages'); ?>
							</p>
							<button class="gdpr-start-auth gdpr-signup">
								<?php echo esc_html('New? Create an account','wplegalpages'); ?>
							</button>
							<p class="wplegal-api-connect-text">
								<?php echo esc_html('Already have an account?','wplegalpages'); ?> <span class="wplegal-api-connect-existing"><a href="#"><?php echo esc_html('Connect your existing account','wplegalpages'); ?></a></span>
							</p>
							<span class="wplegal-api-close-icon">&times;</span>
					</div>
					<div class="wplegal-api-overlay"></div>	
					<script>
						document.addEventListener("DOMContentLoaded", function () {
							const createPopupTab = document.querySelector(".wp-legalpages-admin-create-popups-tab");
						    const popup = document.querySelector(".wplegal-api-connection-popup");
						    const overlay = document.querySelector(".wplegal-api-overlay");
						    const closeButton = document.querySelector(".wplegal-api-close-icon");
						    const restrictedContent = document.querySelector(".postbox"); 
							restrictedContent.style.cursor = 'not-allowed';

						    function showPopup() {
						        popup.style.display = "block";
						        overlay.style.display = "block";
						    }
						
						    function closePopup() {
						        popup.style.display = "none";
						        overlay.style.display = "none";
						    }
						
						    closeButton.addEventListener("click", closePopup);
						    overlay.addEventListener("click", closePopup);

							document.getElementById('wplp').addEventListener('mousedown', function (event) {
							    event.preventDefault(); // Prevents the dropdown from opening														
							    showPopup(); 
							});

							createPopupTab.addEventListener("click", function () {
							    showPopup();
							});
						
							setTimeout(() => {
    						    if (createPopupTab.classList.contains("active-tab")) {
    						        showPopup();
    						    }
    						}, 500);

						    // Show popup when user interacts with restricted content
						    if (restrictedContent) {
						        restrictedContent.addEventListener("click", showPopup);
						        restrictedContent.addEventListener("focusin", showPopup, true); // Handles input fields
								restrictedContent.addEventListener('click', function (event) {
								    event.stopImmediatePropagation(); 
    								event.preventDefault(); 
    								return false; 
								});
						    }
						});

					</script>
				<?php require_once plugin_dir_path( __FILE__ ) . 'wp-legal-pages-create-popups-template.php';
			} ?>


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
