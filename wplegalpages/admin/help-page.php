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

?>
<div id="wplegalpages-help-app" class="wplegalpages-app-container">
<div class="wplegalpages-settings-container">
   <div class="wplegalpages-marketing-banner"></div>
   <form  method="post" id="support_form" name="support_form" spellcheck="false" action="admin.php?page=wplp-dashboard#help-page" class="wplegalpages-settings-form">
   <?php wp_nonce_field('wplegalpages_support_request_nonce', 'wplegalpages_nonce'); ?>
  
   <input type="hidden" name="action" value="wplegalpages_support_request">

      <div class="wplegalpages-settings-content">
         <div class="wplegalpages-settings-nav">
            
            <div class="">
               <div class="tab-content">
                  <main class="wplegalpages-help-container">
                    <section class="wplegalpages-help-header">
                      <h1><?php esc_html_e( 'Help & Resources', 'wplegalpages' ); ?></h1>
                      <p><?php esc_html_e( 'Need help or want to explore more? Find everything you need to get the most out of WPLP Compliance Platform.', 'wplegalpages' ); ?></p>
                    </section>

                    <section class="wplegalpages-help-cards">
                      <div class="wplegalpages-help-card">
                        <div class="wplegalpages-help-icon">
                          <span>
                            <img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/images/File_dock_fill.png' ); ?>" alt="<?php esc_attr_e( 'Help Documentation', 'wplegalpages' ); ?>">
                          </span>
                        </div>
                        <h3><?php esc_html_e( 'Documentation', 'wplegalpages' ); ?></h3>
                        <p><?php esc_html_e( 'Browse our step-by-step guides and articles to help you get started and troubleshoot with ease.', 'wplegalpages' ); ?></p>
                        <a href="<?php echo esc_url( 'https://wplegalpages.com/docs/wp-cookie-consent/', 'wplegalpages' ); ?>" target="_blank"><?php esc_html_e( "Read Documentation." ); ?><img class="gdpr-other-plugin-arrow" src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/blue_right_arrow.svg' ); ?>"></a>
                      </div>

                      <div class="wplegalpages-help-card">
                        <div class="wplegalpages-help-icon">
                          <span>
                            <img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/images/Video_file_fill.png' ); ?>" alt="<?php esc_attr_e( 'Help Video Tutorials', 'wplegalpages' ); ?>">
                          </span>
                        </div>
                        <h3><?php esc_html_e( 'Video Tutorials', 'wplegalpages' ); ?></h3>
                        <p><?php esc_html_e( 'Prefer learning by watching? Explore our tutorials to see the plugin in action and learn how to use it effectively.', 'wplegalpages' ); ?></p>
                        <a href="<?php echo esc_url( 'https://wplegalpages.com/docs/wp-cookie-consent/video-guides/video-resources/','wplegalpages' ); ?>" target="_blank"><?php esc_html_e( "Watch Now." ); ?><img class="gdpr-other-plugin-arrow" src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/blue_right_arrow.svg' ); ?>"></a>
                      </div>

                      <div class="wplegalpages-help-card">
                        <div class="wplegalpages-help-icon">
                          <span>
                            <img src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/images/Subttasks_fill.png' ); ?>" alt="<?php esc_attr_e( 'Help Request Feature', 'wplegalpages' ); ?>">
                          </span>
                        </div>
                        <h3><?php esc_html_e( 'Request a Feature', 'wplegalpages' ); ?></h3>
                        <p><?php esc_html_e( 'Got an idea that could make the plugin better? We’d love to hear from you.', 'wplegalpages' ); ?></p>
                        <a href="<?php echo esc_url( 'https://wplegalpages.com/contact-us/', 'wplegalpages' ); ?>" target="_blank"><?php esc_html_e( "Request Now." ); ?><img class="gdpr-other-plugin-arrow" src="<?php echo esc_url( WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/blue_right_arrow.svg' ); ?>"></a>
                      </div>
                    </section>
                    
                    <section class="wplegalpages-help-footer">
                      <h2><?php esc_html_e( 'Need Further Help?', 'wplegalpages' ); ?></h2>
                      <p><?php esc_html_e( 'Can’t find what you’re looking for? Escalate your issue to our support team and we’ll get back to you shortly.', 'wplegalpages' ); ?></p>
                      <p><?php echo wp_kses_post( __( 'Email us at <a href="mailto:support@wplegalpages.com">support@wplegalpages.com</a>', 'wplegalpages' ) ); ?></p>
                    </section>
                  </main>                  
               </div>
            </div>
         </div>
      </div>
   </form>
</div>
</div>
