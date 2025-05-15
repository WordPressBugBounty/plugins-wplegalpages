<?php
/**
 * Provide a admin area view for age verify.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package    Wplegalpages_Pro
 * @subpackage Wplegalpages_Pro/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$lp_eu_button_text    = get_option( 'lp_eu_button_text' );
$lp_eu_button_text_no = get_option( 'lp_eu_button_text_no' , 'No, I am not');
$data                 = apply_filters( 'wplegalpages_pro_invalid_description', get_option( '_lp_invalid_description', __( 'We are Sorry.', 'wplegalpages' ) ) );
?>

				<div class="verify">
						<div class="buttons-set">
							<span class="agebutton">
								<input type="submit" name="lp_verify" id="lp_verify_no" value=" <?php echo esc_attr( $lp_eu_button_text_no ); ?> " href="#" />
							</span>
							<span class="agebutton">
								<input type="submit" name="lp_verify" id="lp_verify_yes" value="<?php echo esc_attr( $lp_eu_button_text ); ?>" />
							</span>
						<br>
						</div>
				</div>
				<a id="inline" style="display:none" href="#data">This shows content of element who has id="data"</a>
				<div id="is_adult_thickbox" style="display:none">
				<div id="data">
					<?php echo esc_html( $data ); ?>
					</div>
					</div>
				<script>
					jQuery(document).ready(function($) {
							$('#lp_verify_yes').click(function(){
								$.cookie("wplegalpages", 1, { expires : 1 });
							location.reload();
							});

							$('#lp_verify_no').click(function(){
								tb_show("", "#TB_inline?inlineId=is_adult_thickbox&modal=true", false);

					if( $(window).width() == 640 ){
						window_width = 570;
					}else
						window_width = $(window).width();

					if( TB_WIDTH > window_width ){
						$("#TB_window").css({marginTop: 0, marginLeft: 0, width: '90%', left: '5%',  top:'10%', height:'auto'});
						$("#TB_ajaxContent, #TB_iframeContent").css({width: 'auto', height:'auto'});
						$("#TB_closeWindowButton").css({fontSize: '1.2em', marginRight: '5px'});
					}
					else{
						$("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px',width: + parseInt(TB_WIDTH) + 'px',height: + parseInt(TB_HEIGHT) + 'px'});
						$("#TB_ajaxContent, #TB_iframeContent").css({width: + parseInt(TB_WIDTH) + 'px',height: + parseInt(TB_HEIGHT) + 'px'});
					}

					$('#data').css( {'margin'     : '5% 1%',
											'font-weight':  'normal',
											'line-height':  '5em',
											'text-align': 'center'
											} );

					$('#TB_ajaxContent.TB_modal').css({ 'display' : 'flex',
						'position' : 'fixed',
						'align-items' : 'center',
						'justify-content' : 'center'

					})
						});
					});
					</script>
<?php
