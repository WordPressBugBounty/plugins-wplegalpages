<?php
/**
 * Provide a admin area view for the show legal pages.
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
<div class="wrap">
<div id="wplegal-mascot-app"></div>
<?php
$lp_pro_active = get_option( '_lp_pro_active' );
if ( '1' !== $lp_pro_active ) :
	?>
<div style="">
	<div style="line-height: 2.4em;" class='wplegalpages-pro-promotion'>
		<a href="https://club.wpeka.com/product/wplegalpages/?utm_source=plugin&utm_medium=wplegalpages&utm_campaign=all-legal-pages&utm_content=upgrade-banner" target="_blank">
			<img alt="Upgrade to Pro" src="<?php echo esc_attr( WPL_LITE_PLUGIN_URL ) . 'admin/images/wplegalpages-banner.png'; ?>">
		</a>
	</div>
</div>
<div style="clear:both;"></div>
	<?php
endif;

if ( isset( $_REQUEST['mode'] ) ) {
	check_ajax_referer( 'my-nonce', 'my-_wpnonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_attr__( 'Security Check.', 'wplegalpages' ) );
	}
	if ( 'delete' === $_REQUEST['mode'] ) {
		if ( isset( $_REQUEST['pid'] ) ) {
			if ( ! wp_trash_post( sanitize_text_field( wp_unslash( $_REQUEST['pid'] ) ) ) ) {
				wp_die( esc_attr__( 'Error in moving to Trash.', 'wplegalpages' ) );
			}
		}
		?>
		<div id="message" >
			<p><span class="label label-success myAlert">Legal page moved to trash.</span></p>
		</div>

		<?php
	}
}
$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
?>
<div class="wplegal-create-legal-all-pages">
			<div class="wplegal-feature-icon" id="wplegal-settings-create-legal">
				<img src="<?php echo WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/create_legal_blue.svg'; ?>" alt="create legal" class="wplegal-create-legal-icon">
				<div class="wplegal-create-legal-subtext">
					<p class="wplegal-create-legal-page-subheading"><?php esc_attr_e( 'Create Your Legal Page', 'wplegalpages' ); ?></p>
					<p class="wplegal-create-legal-page-content"><?php esc_attr_e( 'Secure your site in 3 easy steps and generate a personalized legal policy page for enhanced protection.', 'wplegalpages' ); ?></p>
				</div>
			</div>
			<div class="wplegal-create-legal-link">
				<a href=<?php echo admin_url( 'index.php?page=wplegal-wizard#/' ); ?> class="wplegal-create-legal-page-button">
					<span><?php esc_attr_e( 'Create Page', 'wplegalpages' ); ?></span>
					<img src="<?php echo WPL_LITE_PLUGIN_URL . 'admin/js/vue/images/right_arrow.svg'; ?>" alt="right arrow">
				</a>
			</div>
		</div>
<h2 class="hndle myLabel-head"> <?php esc_attr_e( 'Available Pages', 'wplegalpages' ); ?> </h2>
<table class="widefat fixed comments table table-striped-all-pages">
	<thead>
		<tr class="wplegalpages-all-pages-heading">
			<th width="6%" ><?php esc_attr_e( 'S.No.', 'wplegalpages' ); ?></th>
			<th width="20%"><?php esc_attr_e( 'Page Title', 'wplegalpages' ); ?></th>
			<th width="10%"><?php esc_attr_e( 'Page ID', 'wplegalpages' ); ?></th>
			<th width="20%"><?php esc_attr_e( 'Shortcode', 'wplegalpages' ); ?></th>
			<th width="10%"><?php esc_attr_e( 'Author', 'wplegalpages' ); ?></th>
			<th width="10%"><?php esc_attr_e( 'Date', 'wplegalpages' ); ?></th>
			<th width="15%" class="wplegalpages-all-pages-heading-last" ><?php esc_attr_e( 'Action', 'wplegalpages' ); ?></th>
		</tr>
	</thead>
	<tbody>

	<?php
		global $wpdb;
		$post_tbl     = $wpdb->prefix . 'posts';
		$postmeta_tbl = $wpdb->prefix . 'postmeta';
		$pagesresult  = $wpdb->get_results( $wpdb->prepare( 'SELECT ptbl.* FROM ' . $post_tbl . ' as ptbl , ' . $postmeta_tbl . ' as pmtbl WHERE ptbl.ID = pmtbl.post_id and ptbl.post_status = %s AND pmtbl.meta_key = %s', array( 'publish', 'is_legal' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

	if ( $pagesresult ) {
		$nonce    = wp_create_nonce( 'my-nonce' );
		$count    = 1;
		$user_tbl = $wpdb->prefix . 'users';
		foreach ( $pagesresult as $res ) {
				$url     = get_permalink( $res->ID );
				$author  = $wpdb->get_results( $wpdb->prepare( 'SELECT utbl.user_login FROM ' . $post_tbl . ' as ptbl, ' . $user_tbl . ' as utbl WHERE ptbl.post_author = utbl.ID and ptbl.ID = %d', array( $res->ID ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$delurl  = isset( $_SERVER['PHP_SELF'] ) ? esc_url_raw( wp_unslash( $_SERVER['PHP_SELF'] ) ) : '';
				$delurl .= "?pid=$res->ID&page=$current_page&mode=delete&_wpnonce=$nonce";
			?>
			<tr>
				<td><?php echo esc_attr( $count ); ?></td>
				<td><?php echo esc_attr( $res->post_title ); ?></td>
				<td><?php echo esc_attr( $res->ID ); ?></td>
				<td><?php echo '[wplegalpage pid=' . esc_attr( $res->ID ) . ']'; ?></td>
				<td><?php echo esc_attr( ucfirst( $author[0]->user_login ) ); ?></td>
				<td><?php echo esc_attr( gmdate( 'Y/m/d', strtotime( $res->post_date ) ) ); ?></td>
				<td class="wplegal-table-link">
					<a href="<?php echo esc_attr( get_admin_url() ); ?>/post.php?post=<?php echo esc_attr( $res->ID ); ?>&action=edit" class="table-link"><?php esc_attr_e( 'Edit ', 'wplegalpages' ); ?></a> | <a href="<?php echo esc_url_raw( $url ); ?>" class="table-link"><?php esc_attr_e( ' View ', 'wplegalpages' ); ?></a>| <a href="<?php echo esc_url_raw( $delurl ); ?>" class="table-link table-link-alert"><?php esc_attr_e( ' Trash', 'wplegalpages' ); ?></a>
				</td>
			</tr>
				<?php
				$count++;
		}
		?>

		<?php } else { ?>
		<tr>
			<td colspan="7" class="wplegalpages-no-pages"><?php esc_attr_e( 'No page Available', 'wplegalpages' ); ?></td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
</div>
