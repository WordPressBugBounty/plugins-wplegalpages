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
$input_type          = $this->wplegalpages_pro_get_input_type();
$submit_button_label = apply_filters( 'wplegalpages_pro_form_submit_label', __( 'VERIFY AGE', 'wplegalpages' ) );
$err                 = false;
if ( isset( $_GET['verify-error'] ) ) {
	if ( isset( $_GET['nonce'] ) ) {
		if ( wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), 'age_verify_nonce' ) ) {
			$err = sanitize_text_field( wp_unslash( $_GET['verify-error'] ) );
		}
	}
}
?>
<form id="lp_verify_form" action=" <?php esc_url( home_url( '/' ) ); ?>" method="post">
<?php
if ( $err ) :
			$homeurl = get_home_url();
			$data    = apply_filters( 'wplegalpages_pro_invalid_description', get_option( '_lp_invalid_description', __( 'We are Sorry.', 'wplegalpages' ) ) );
	?>
					<a id="inline" style="display:none" href="#data">This shows content of element who has id="data"</a>

					<div id="is_adult_thickbox" style="display:none">
					<div id="data">
							<?php echo esc_html( $data ); ?>
						<br>
						<a href="<?php echo esc_url( $homeurl ); ?>">Go Back to HomePage</a>
					</div>
						</div>
					<script>
						jQuery(document).ready(function($) {

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

					$('#data').css( {'margin'     : '0 auto',
								'font-weight':  'normal',
								'line-height':  '5em'} );

					$('#TB_ajaxContent.TB_modal').css({ 'display' : 'flex',
					'position' : 'fixed',
					'text-align' : 'center',
					'align-items' : 'center'

					})
				});
				</script>
	<?php
		endif;
		do_action( 'wplegalpages_pro_form_before_inputs' );
		wp_nonce_field( 'verify-age', 'lp-nonce' );
switch ( $input_type ) {
	case 'dropdowns':
		?>
		<p><select name="lp_verify_m" id="lp_verify_m">
		<?php
		foreach ( range( 1, 12 ) as $month ) :
			$month_name = gmdate( 'F', mktime( 0, 0, 0, $month, 1 ) );
			?>
			<option value="<?php echo esc_attr( $month ); ?>" > <?php echo esc_html( $month_name ); ?> </option>
			<?php
		endforeach;
		?>
		</select>  <select name="lp_verify_d" id="lp_verify_d">
		<?php
		foreach ( range( 1, 31 ) as $day ) :
			?>
			<option value="<?php echo esc_attr( $day ); ?>"><?php echo esc_html( zeroise( $day, 2 ) ); ?></option>
			<?php
		endforeach;
		?>
		</select>  <select name="lp_verify_y" id="lp_verify_y">
		<?php
		foreach ( range( 1910, gmdate( 'Y' ) ) as $year_number ) :
			$selected = gmdate( 'Y' ) === $year_number ? 'selected="selected"' : '';
			?>
			<option value="<?php echo esc_attr( $year_number ); ?>"<?php echo esc_html( $selected ); ?>><?php echo esc_html( $year_number ); ?></option>
			<?php
		endforeach;
		?>
		</select></p>
		<?php
		break;
	case 'inputs':
		?>
		<p><input type="text" name="lp_verify_m" id="lp_verify_m" maxlength="2" value="" placeholder="MM" /> - <input type="text" name="lp_verify_d" id="lp_verify_d" maxlength="2" value="" placeholder="DD" /> - <input type="text" name="lp_verify_y" id="lp_verify_y" maxlength="4" value="" placeholder="YYYY" /></p>
		<?php
		break;
	case 'checkbox':
		?>
		<p><label for="lp_verify_confirm"><input type="checkbox" name="lp_verify_confirm" id="lp_verify_confirm" value="1" />
		<?php
		echo esc_html(
			sprintf(
				apply_filters(
					'lp_confirm_text',
					/* translators: 1: minimum age */
					esc_attr__( 'I am at least %1$s years old', 'wplegalpages' )
				),
				$this->wplegalpages_pro_get_minimum_age()
			)
		);
		?>
		</label></p>
		<?php
		break;
};
		do_action( 'wplegalpages_pro_form_after_inputs' );
?>
		<div class="buttons-set">
				<label for="lp_verify_remember" id="lp_verify_remember_wrap">
					<input type="checkbox" name="lp_verify_remember" id="lp_verify_remember" value="1" /><?php echo esc_html__( 'Remember me', 'wplegalpages' ); ?></label>
		<input type="submit" name="lp_verify" id="lp_verify" value="<?php echo esc_attr( $submit_button_label ); ?>" /></div>
		</form>
<?php
