/**
 * Adult content JavaScript.
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
		function() {

			window.onload = function(){tb_show( "", "#TB_inline?width=570&height=250&inlineId=is_adult_thickbox&modal=true", false );}

			$( window ).load(
				function() {
					if ( $( window ).width() == 640 ) {
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
			);

			// set design to box.
			$( '#data' ).css(
				{ 'font-size'   : '0.9em',
					'margin'     : '5% 1% 10%',
					'font-weight':  'normal',
					'line-height':  '2em'
				}
			);

			$( '#leave_site' ).css(
				{ 'background-color' : '#e84c3d',
					'color'            : '#fff',
					'font-size'		: '1em',
					'margin'			: '10px 6px 0 0',
					'padding'			: '6px 10px',
					'border-radius'		: '4px',
					'float'				: 'left'
				}
			);

			$( '#enter_site' ).css(
				{ 'background-color' : '#000',
					'color'            : '#fff',
					'font-size'		: '1em',
					'margin'			: '10px 6px 0 0',
					'padding'			: '6px 10px',
					'border-radius'	: '4px',
					'float'			: 'left'
				}
			);

			$( '#enter_site' ).click(
				function(){
					$.cookie( "is_user_adult", 1 );
					self.parent.tb_remove();
				}
			);

		}
	);
})( jQuery );
