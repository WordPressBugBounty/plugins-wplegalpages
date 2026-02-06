<?php
/**
 * Fired during WPLegalPages activation
 *
 * @link       http://wplegalpages.com/
 * @since      1.5.2
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 */

/**
 * Fired during WPLegalPages activation.
 *
 * This class defines all code necessary to run during the WPLegalPages's activation.
 *
 * @since      1.5.2
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 * @author     WPEka <support@wplegalpages.com>
 */
if ( ! class_exists( 'WP_Legal_Pages_Activator' ) ) {
	/**
	 * Fired during WPLegalPages activation.
	 *
	 * This class defines all code necessary to run during the WPLegalPages's activation.
	 *
	 * @since      1.5.2
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/includes
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages_Activator {
		/**
		 * Short Description. (use period)
		 *
		 * Long Description.
		 *
		 * @since    1.5.2
		 */
		public static function activate() {

			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			if ( is_multisite() ) {
				// Get all blogs in the network and activate plugin on each one.
				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs ); // db call ok; no-cache ok.
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::install_db();
					restore_current_blog();
				}
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			} else {
				self::install_db();
			}

	    //Option for the legal pages review 
		$wplp_review_option_exists = get_option( 'wplp_review_pending');
		if ( ! $wplp_review_option_exists ) {
			add_option( 'wplp_review_pending', '0', '', true );
		}

		
		}

		/**
		 * Install required tables.
		 */
		public static function install_db() {
			global $wpdb;

			$legal_pages = new WP_Legal_Pages();
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$search_query = "SHOW TABLES LIKE '%" . $legal_pages->tablename . "%'";
			if ( ! $wpdb->get_results( $search_query, ARRAY_N ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$sql = 'CREATE TABLE IF NOT EXISTS ' . $legal_pages->tablename . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
							' (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `title` text NOT NULL,
                              `content` longtext NOT NULL,
                              `notes` text NOT NULL,
                              `contentfor` varchar(200) NOT NULL,
                              PRIMARY KEY (`id`)
                            );';
				dbDelta( $sql );
			}
			$like         = 'is_active';
			$column_count = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $legal_pages->tablename . ' LIKE %s', array( $like ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( empty( $column_count ) ) {
				$alter_sql = 'ALTER TABLE ' . $legal_pages->tablename . ' ADD `is_active` BOOLEAN NULL DEFAULT NULL AFTER `notes`;';
				$wpdb->query( $alter_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}
			$search_query = "SHOW TABLES LIKE '%" . $legal_pages->popuptable . "%'";
			if ( ! $wpdb->get_results( $search_query, ARRAY_N ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$popup_sql = 'CREATE TABLE IF NOT EXISTS ' . $legal_pages->popuptable . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
							' (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `popup_name` text NOT NULL,
                              `content` longtext NOT NULL,
                              PRIMARY KEY (`id`)
                            );';
				dbDelta( $popup_sql );
			}
			$like         = 'popupName';
			$column_count = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $legal_pages->popuptable . ' LIKE %s', array( $like ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( ! empty( $column_count ) ) {
				$alter_popup_sql = 'ALTER TABLE ' . $legal_pages->popuptable . ' CHANGE `popupName` `popup_name` TEXT;';
				$wpdb->query( $alter_popup_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}
			
			delete_option( '_lp_db_updated' );
			delete_option( '_lp_terms_updated' );
			delete_option( '_lp_terms_fr_de_updated' );
			add_option( '_lp_templates_updated', true );
			add_option( '_lp_effective_date_templates_updated', true );
			add_option( 'lp_excludePage', 'true' );
			add_option( 'lp_general', '' );
			add_option( 'lp_accept_terms', '0' );

		}
	}


}
