<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://wplegalpages.com/
 * @since 1.0.0
 *
 * @package    Wplegalpages
 */

?>
<div id="wplegal-mascot-app"></div>
<!-- <div style="clear:both;"></div> -->
<?php
				$wplegalpages_pro_version = get_option( 'wplegalpages_pro_version' ) ? get_option( 'wplegalpages_pro_version' ) : '';
				$localized_data           = array(
					'wplegalpages_pro_version' => $wplegalpages_pro_version,
				);
				wp_localize_script( $this->plugin_name . '-main', 'localized_data', $localized_data );
				wp_enqueue_script( $this->plugin_name . '-main' );
				require_once plugin_dir_path( __FILE__ ) . '/admin-settings.php';
				?>
