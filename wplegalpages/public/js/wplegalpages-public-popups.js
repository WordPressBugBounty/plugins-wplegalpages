/**
 * Popup JavaScript.
 *
 * @package    Wplegalpages_Pro
 * @subpackage Wplegalpages_Pro/public
 * @author     wpeka <https://club.wpeka.com>
 */

(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).ready(
		function(){

			// set page's last character for cookies name.
			var full_url           = window.location.href;
			if(full_url.indexOf('?') !== -1 ){
				full_url = full_url.substring(0, full_url.indexOf('?'));
			}
			var full_url_last_char = full_url.substring( full_url.length - 1, full_url.length - 4 );

			if ($.cookie( 'lp_eu_terms_condition__popup_' + full_url_last_char ) == null) {
				window.onload = function(){tb_show( "", "#TB_inline?width=600&height=550&inlineId=thick-box&modal=true", false );}
			}

			$( "#lp_submit" ).click(
				function() {
					location.reload();
					$.cookie( 'lp_eu_terms_condition__popup_' + full_url_last_char, 'YES', { expires: 7, path: '/' } );
					self.parent.tb_remove();
				}
			);

			$( window ).load(
				function() {
					if ($.cookie( 'lp_eu_terms_condition__popup_' + full_url_last_char ) == null) {
						$.cookie( 'lp_eu_terms_condition__popup_' + full_url_last_char, 'NO', { expires: 7, path: '/' } );
						if ( jQuery( window ).width() == 640 ) {
							var window_width = 629;
						} else {
							window_width = jQuery( window ).width();
						}
						if ( TB_WIDTH > window_width ) {
							$( "#TB_window" ).css( {marginTop: 0, marginLeft: 0, width: '90%', left: '5%',  top:'10%', height:'auto'} );
							$( "#TB_ajaxContent, #TB_iframeContent" ).css( {width: 'auto', height:'auto'} );
							$( "#TB_closeWindowButton" ).css( {fontSize: '1.2em', marginRight: '5px'} );
						} else {
							$( "#TB_window" ).css( {marginLeft: '-' + parseInt( (TB_WIDTH / 2),10 ) + 'px', width: 'auto'} );
						}
					}
				}
			);
		}
	);
})( jQuery );
