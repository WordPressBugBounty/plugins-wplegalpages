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
				$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs ); // db call ok; no-cache ok.
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::install_db();
					restore_current_blog();
				}
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
			$privacy      = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/privacy.html' );
			$dmca         = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/dmca.html' );
			$terms_latest = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/Terms-of-use.html' );
			$ccpa         = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/CCPA.html' );
			$terms_fr     = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/Terms-of-use-fr.html' );
			$terms_de     = file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/Terms-of-use-de.html' );
			$terms                      = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/Terms.html' );
			$privacy_california         = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/privacyCalifornia.html' );
			$earnings                   = '<p>Effective date - [Last Updated]</p><p>EVERY EFFORT HAS BEEN MADE TO ACCURATELY REPRESENT THIS PRODUCT AND IT\'S POTENTIAL. EVEN THOUGH THIS INDUSTRY IS ONE OF THE FEW WHERE ONE CAN WRITE THEIR OWN CHECK IN TERMS OF EARNINGS, THERE IS NO GUARANTEE THAT YOU WILL EARN ANY MONEY USING THE TECHNIQUES AND IDEAS IN THESE MATERIALS. EXAMPLES IN THESE MATERIALS ARE NOT TO BE INTERPRETED AS A PROMISE OR GUARANTEE OF EARNINGS. EARNING POTENTIAL IS ENTIRELY DEPENDENT ON THE PERSON USING OUR PRODUCT, IDEAS AND TECHNIQUES. WE DO NOT PURPORT THIS AS A "GET RICH SCHEME."</p><p>ANY CLAIMS MADE OF ACTUAL EARNINGS OR EXAMPLES OF ACTUAL RESULTS CAN BE VERIFIED UPON REQUEST. YOUR LEVEL OF SUCCESS IN ATTAINING THE RESULTS CLAIMED IN OUR MATERIALS DEPENDS ON THE TIME YOU DEVOTE TO THE PROGRAM, IDEAS AND TECHNIQUES MENTIONED, YOUR FINANCES, KNOWLEDGE AND VARIOUS SKILLS. SINCE THESE FACTORS DIFFER ACCORDING TO INDIVIDUALS, WE CANNOT GUARANTEE YOUR SUCCESS OR INCOME LEVEL. NOR ARE WE RESPONSIBLE FOR ANY OF YOUR ACTIONS.</p><p>MATERIALS IN OUR PRODUCT AND OUR WEBSITE MAY CONTAIN INFORMATION THAT INCLUDES OR IS BASED UPON FORWARD-LOOKING STATEMENTS WITHIN THE MEANING OF THE SECURITIES LITIGATION REFORM ACT OF 1995. FORWARD-LOOKING STATEMENTS GIVE OUR EXPECTATIONS OR FORECASTS OF FUTURE EVENTS. YOU CAN IDENTIFY THESE STATEMENTS BY THE FACT THAT THEY DO NOT RELATE STRICTLY TO HISTORICAL OR CURRENT FACTS. THEY USE WORDS SUCH AS "ANTICIPATE," "ESTIMATE," "EXPECT," "PROJECT," "INTEND," "PLAN," "BELIEVE," AND OTHER WORDS AND TERMS OF SIMILAR MEANING IN CONNECTION WITH A DESCRIPTION OF POTENTIAL EARNINGS OR FINANCIAL PERFORMANCE.</p><p>ANY AND ALL FORWARD LOOKING STATEMENTS HERE OR ON ANY OF OUR SALES MATERIAL ARE INTENDED TO EXPRESS OUR OPINION OF EARNINGS POTENTIAL. MANY FACTORS WILL BE IMPORTANT IN DETERMINING YOUR ACTUAL RESULTS AND NO GUARANTEES ARE MADE THAT YOU WILL ACHIEVE RESULTS SIMILAR TO OURS OR ANYBODY ELSES, IN FACT NO GUARANTEES ARE MADE THAT YOU WILL ACHIEVE ANY RESULTS FROM OUR IDEAS AND TECHNIQUES IN OUR MATERIAL.</p><p>The author and publisher disclaim any warranties (express or implied), merchantability, or fitness for any particular purpose. The author and publisher shall in no event be held liable to any party for any direct, indirect, punitive, special, incidental or other consequential damages arising directly or indirectly from any use of this material, which is provided "as is", and without warranties.</p><p>As always, the advice of a competent legal, tax, accounting or other  professional should be sought.</p><p>[Domain] does not warrant the performance, effectiveness or applicability of any sites listed or linked to on [Domain]</p><p>All links are for information purposes only and are not warranted for content, accuracy or any other implied or explicit purpose.</p>';
			$disclaimer                 = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/disclaimer.html' );
			$disclaimer_fr              = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/disclaimer-fr.html' );
			$disclaimer_de              = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/disclaimer-de.html' );
			$testimonials               = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/testimonial-disclosure.html' );
			$linking                    = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/linking-policy.html' );
			$refund                     = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/refund-policy.html' );
			$return_refund              = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/return-refund-policy.html' );
			$affiliate                  = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/affiliate-agreement.html' );
			$disclosure                 = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/affiliate-disclosure.html' );
			$antispam                   = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/antispam.html' );
			$ftc                        = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/ftcstatement.html' );
			$medical                    = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/medical-disclaimer.html' );
			$dart                       = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/double-dart-cookie.html' );
			$external                   = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/external-links.html' );
			$fbpolicy                   = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/fbpolicy.html' );
			$about_us                   = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/about-us.html' );
			$digital_goods              = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/digital-goods-refund-policy.html' );
			$coppa                      = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/COPPA.html' );
			$blog_policy                = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/blog-comments-policy.html' );
			$newsletter                 = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/Newsletter-Subscription-and-Disclaimer.html' );
			$cookies_policy             = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/cookies-policy.html' );
			$gdpr_cookie_policy         = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/gdpr-cookie-policy.html' );
			$gdpr_privacy_policy        = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/gdpr-privacy-policy.html' );
			$gdpr_privacy_policy_fr     = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/gdpr-privacy-policy-fr.html' );
			$gdpr_privacy_policy_de     = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/gdpr-privacy-policy-de.html' );
			$confidentiality_disclosure = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/confidentiality-disclosure.html' );
			$privacy_policy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'kCjTeYOZxB' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $privacy_policy_count ) {
				$wpdb->insert(
					$legal_pages->tablename,
					array(
						'title'      => 'Privacy Policy',
						'content'    => $privacy,
						'contentfor' => 'kCjTeYOZxB',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				); // db call ok; no-cache ok.
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $privacy,
						'contentfor' => 'kCjTeYOZxB',
					),
					array( 'title' => 'Privacy Policy' ),
					array( '%d', '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$dmca_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '1r4X6y8tssz0j' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $dmca_count ) {
				$wpdb->insert(
					$legal_pages->tablename,
					array(
						'title'      => 'DMCA',
						'content'    => $dmca,
						'contentfor' => '1r4X6y8tssz0j',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				); // db call ok; no-cache ok.
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $dmca,
						'contentfor' => 'r4X6y8tssz',
					),
					array( 'title' => 'DMCA' ),
					array( '%d', '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$terms_of_use_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'n1bmPjZ6Xj' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $terms_of_use_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Terms of Use',
						'content'    => $terms_latest,
						'contentfor' => 'n1bmPjZ6Xj',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $terms_latest,
						'contentfor' => 'n1bmPjZ6Xj',
					),
					array( 'title' => 'Terms of Use' ),
					array( '%d', '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$terms_of_use_fr_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'MMFqUJfC3m' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $terms_of_use_fr_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Terms of Use - FR',
						'content'    => $terms_fr,
						'contentfor' => 'MMFqUJfC3m',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $terms_fr,
						'contentfor' => 'MMFqUJfC3m',
					),
					array( 'title' => 'Terms of Use - FR' ),
					array( '%d', '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$terms_of_use_de_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'fbBlC5Y4yZ' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $terms_of_use_de_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Terms of Use - DE',
						'content'    => $terms_de,
						'contentfor' => 'fbBlC5Y4yZ',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $terms_de,
						'contentfor' => 'fbBlC5Y4yZ',
					),
					array( 'title' => 'Terms of Use - DE' ),
					array( '%d', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$ccpa_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'JRevVk8nkP' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $ccpa_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'CCPA - California Consumer Privacy Act',
						'content'    => $ccpa,
						'contentfor' => 'JRevVk8nkP',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'is_active'  => '1',
						'content'    => $ccpa,
						'contentfor' => 'JRevVk8nkP',
					),
					array( 'title' => 'CCPA - California Consumer Privacy Act' ),
					array( '%d', '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$terms_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'B8wltvJ4cB' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $terms_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Terms(forced agreement)',
						'content'    => $terms,
						'contentfor' => 'B8wltvJ4cB',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $terms,
						'contentfor' => 'B8wltvJ4cB',
					),
					array( 'title' => 'Terms(forced agreement)' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$california_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'wOHnKlLcmo' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $california_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'California Privacy Rights',
						'content'    => $privacy_california,
						'contentfor' => 'wOHnKlLcmo',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $privacy_california,
						'contentfor' => 'wOHnKlLcmo',
					),
					array( 'title' => 'California Privacy Rights' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$earnings_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'J5GdjXkOYs' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $earnings_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Earnings Disclaimer',
						'content'    => $earnings,
						'contentfor' => 'J5GdjXkOYs',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $earnings,
						'contentfor' => 'J5GdjXkOYs',
					),
					array( 'title' => 'Earnings Disclaimer' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$disclaimer_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'Xq8I33kdBD' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $disclaimer_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Disclaimer',
						'content'    => $disclaimer,
						'contentfor' => 'Xq8I33kdBD',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $disclaimer,
						'contentfor' => 'Xq8I33kdBD',
					),
					array( 'title' => 'Disclaimer' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$disclaimer_fr_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'ywMXk14kX5' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $disclaimer_fr_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Disclaimer - FR',
						'content'    => $disclaimer_fr,
						'contentfor' => 'ywMXk14kX5',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $disclaimer_fr,
						'contentfor' => 'ywMXk14kX5',
					),
					array( 'title' => 'Disclaimer - FR' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$disclaimer_de_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'sOGbuLkgDX' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $disclaimer_de_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Disclaimer - DE',
						'content'    => $disclaimer_de,
						'contentfor' => 'sOGbuLkgDX',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $disclaimer_de,
						'contentfor' => 'sOGbuLkgDX',
					),
					array( 'title' => 'Disclaimer - DE' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$testimonials_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'ICdlpogo8O' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $testimonials_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Testimonials Disclosure',
						'content'    => $testimonials,
						'contentfor' => 'ICdlpogo8O',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $testimonials,
						'contentfor' => 'ICdlpogo8O',
					),
					array( 'title' => 'Testimonials Disclosure' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$linking_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'HCdw9KSLn8' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $linking_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Linking Policy',
						'content'    => $linking,
						'contentfor' => 'HCdw9KSLn8',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $linking,
						'contentfor' => 'HCdw9KSLn8',
					),
					array( 'title' => 'Linking Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$refund_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'Xg2AWjKu7e' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' !== $refund_count ) {
				if ( ! get_option( '_lp_pro_refund_db_updated' ) ) {
					$wpdb->delete(
						$legal_pages->tablename,
						array(
							'contentfor' => 'Xg2AWjKu7e',
						),
						array(
							'%s',
						)
					); // db call ok; no-cache ok.
					add_option( '_lp_pro_refund_db_updated', true );
				}
			}
			$return_refund_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'R4CiGI3sJ4' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $return_refund_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: General',
						'content'    => $return_refund,
						'contentfor' => 'R4CiGI3sJ4',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: General',
						'content'    => $return_refund,
						'contentfor' => 'R4CiGI3sJ4',
					),
					array( 'contentfor' => 'R4CiGI3sJ4' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$affiliate_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'uxygs19AsJ' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $affiliate_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Affiliate Agreement',
						'content'    => $affiliate,
						'contentfor' => 'uxygs19AsJ',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $affiliate,
						'contentfor' => 'uxygs19AsJ',
					),
					array( 'title' => 'Affiliate Agreement' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$antispam_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '9nEm1Jy29P' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $antispam_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Antispam',
						'content'    => $antispam,
						'contentfor' => '9nEm1Jy29P',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $antispam,
						'contentfor' => '9nEm1Jy29P',
					),
					array( 'title' => 'Antispam' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$ftc_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'dbLy6a8FAx' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $ftc_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'FTC Statement',
						'content'    => $ftc,
						'contentfor' => 'dbLy6a8FAx',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $ftc,
						'contentfor' => 'dbLy6a8FAx',
					),
					array( 'title' => 'FTC Statement' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$medical_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'RLlofiRSgd' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $medical_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Medical Disclaimer',
						'content'    => $medical,
						'contentfor' => 'RLlofiRSgd',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $medical,
						'contentfor' => 'RLlofiRSgd',
					),
					array( 'title' => 'Medical Disclaimer' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$dart_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'EdNSxwT2eB' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $dart_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Double Dart Cookie',
						'content'    => $dart,
						'contentfor' => 'EdNSxwT2eB',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $dart,
						'contentfor' => 'EdNSxwT2eB',
					),
					array( 'title' => 'Double Dart Cookie' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$external_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'GsnkrA9R91' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $external_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'External Links Policy',
						'content'    => $external,
						'contentfor' => 'GsnkrA9R91',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $external,
						'contentfor' => 'GsnkrA9R91',
					),
					array( 'title' => 'External Links Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$disclosure_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'TwiV64Z4y1' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $disclosure_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Affiliate Disclosure',
						'content'    => $disclosure,
						'contentfor' => 'TwiV64Z4y1',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $disclosure,
						'contentfor' => 'TwiV64Z4y1',
					),
					array( 'title' => 'Affiliate Disclosure' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$fbpolicy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'Q9ytZuRIgJ' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $fbpolicy_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'FB Policy',
						'content'    => $fbpolicy,
						'contentfor' => 'Q9ytZuRIgJ',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $fbpolicy,
						'contentfor' => 'Q9ytZuRIgJ',
					),
					array( 'title' => 'FB Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$about_us_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'J2tfsnhta5' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $about_us_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'About Us',
						'content'    => $about_us,
						'contentfor' => 'J2tfsnhta5',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $about_us,
						'contentfor' => 'J2tfsnhta5',
					),
					array( 'title' => 'About Us' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$digital_goods_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'DDj1NshuFZ' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' !== $digital_goods_count ) {
				if ( ! get_option( '_lp_pro_digital_db_updated' ) ) {
					$wpdb->delete(
						$legal_pages->tablename,
						array(
							'contentfor' => 'DDj1NshuFZ',
						),
						array(
							'%s',
						)
					); // db call ok; no-cache ok.
					add_option( '_lp_pro_digital_db_updated', true );
				}
			}
			$coppa_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '5o3hglUfDr' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $coppa_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'COPPA - Children’s Online Privacy Policy',
						'content'    => $coppa,
						'contentfor' => '5o3hglUfDr',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $coppa,
						'contentfor' => '5o3hglUfDr',
					),
					array( 'title' => 'COPPA - Children’s Online Privacy Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$blog_policy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '3PjAe6pJUc' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $blog_policy_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Blog Comments Policy',
						'content'    => $blog_policy,
						'contentfor' => '3PjAe6pJUc',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $blog_policy,
						'contentfor' => '3PjAe6pJUc',
					),
					array( 'title' => 'Blog Comments Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$newsletter_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '52ahHjKsVH' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $newsletter_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Newsletter : Subscription and Disclaimer',
						'content'    => $newsletter,
						'contentfor' => '52ahHjKsVH',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $newsletter,
						'contentfor' => '52ahHjKsVH',
					),
					array( 'title' => 'Newsletter : Subscription and Disclaimer' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$cookies_policy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'Kp726GRpYC' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $cookies_policy_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Cookies Policy',
						'content'    => $cookies_policy,
						'contentfor' => 'Kp726GRpYC',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $cookies_policy,
						'contentfor' => 'Kp726GRpYC',
					),
					array( 'title' => 'Cookies Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$gdpr_cookie_policy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'EfjpLEnTzv' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $gdpr_cookie_policy_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'GDPR Cookie Policy',
						'content'    => $gdpr_cookie_policy,
						'contentfor' => 'EfjpLEnTzv',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $gdpr_cookie_policy,
						'contentfor' => 'EfjpLEnTzv',
					),
					array( 'title' => 'GDPR Cookie Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$gdpr_privacy_policy_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '6x5434Xdu7' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $gdpr_privacy_policy_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'GDPR Privacy Policy',
						'content'    => $gdpr_privacy_policy,
						'contentfor' => '6x5434Xdu7',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $gdpr_privacy_policy,
						'contentfor' => '6x5434Xdu7',
					),
					array( 'title' => 'GDPR Privacy Policy' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$gdpr_privacy_policy_fr_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'IUZtVbDV68' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $gdpr_privacy_policy_fr_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'GDPR Privacy Policy - FR',
						'content'    => $gdpr_privacy_policy_fr,
						'contentfor' => 'IUZtVbDV68',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $gdpr_privacy_policy_fr,
						'contentfor' => 'IUZtVbDV68',
					),
					array( 'title' => 'GDPR Privacy Policy - FR' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$gdpr_privacy_policy_de_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'EsbCPJ5XCB' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $gdpr_privacy_policy_de_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'GDPR Privacy Policy - DE',
						'content'    => $gdpr_privacy_policy_de,
						'contentfor' => 'EsbCPJ5XCB',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $gdpr_privacy_policy_de,
						'contentfor' => 'EsbCPJ5XCB',
					),
					array( 'title' => 'GDPR Privacy Policy - DE' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$confidentiality_disclosure_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( 'LuXcsW5oIn' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $confidentiality_disclosure_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Confidentiality Disclosure',
						'content'    => $confidentiality_disclosure,
						'contentfor' => 'LuXcsW5oIn',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $confidentiality_disclosure,
						'contentfor' => 'LuXcsW5oIn',
					),
					array( 'title' => 'Confidentiality Disclosure' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$returns_refunds_norefunds        = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/returns-refunds-policy-norefunds.html' );
			$returns_refunds_digital_goods    = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/returns-refunds-policy-digital-goods.html' );
			$returns_refunds_physical_goods   = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/returns-refunds-policy-physical-goods.html' );
			$returns_refunds_perishable_goods = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/returns-refunds-policy-perishable-goods.html' );
			$amazon                           = file_get_contents( plugin_dir_path( __DIR__ ) . 'templates/amazon-affiliate.html' );
			$returns_refunds_norefunds_count  = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE title=%s', array( 'Returns and Refunds Policy: No Refunds' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $returns_refunds_norefunds_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: No Refunds',
						'content'    => $returns_refunds_norefunds,
						'contentfor' => 'NCknfH8jrd',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $returns_refunds_norefunds,
						'contentfor' => 'NCknfH8jrd',
					),
					array( 'title' => 'Returns and Refunds Policy: No Refunds' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$returns_refunds_digital_goods_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE title=%s', array( 'Returns and Refunds Policy: Digital Goods' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $returns_refunds_digital_goods_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: Digital Goods',
						'content'    => $returns_refunds_digital_goods,
						'contentfor' => 'SVwyhB4wbf',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $returns_refunds_digital_goods,
						'contentfor' => 'SVwyhB4wbf',
					),
					array( 'title' => 'Returns and Refunds Policy: Digital Goods' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$returns_refunds_physical_goods_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE title=%s', array( 'Returns and Refunds Policy: Physical Goods' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $returns_refunds_physical_goods_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: Physical Goods',
						'content'    => $returns_refunds_physical_goods,
						'contentfor' => 'sfjX0CxRCV',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $returns_refunds_physical_goods,
						'contentfor' => 'sfjX0CxRCV',
					),
					array( 'title' => 'Returns and Refunds Policy: Physical Goods' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$returns_refunds_perishable_goods_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE title=%s', array( 'Returns and Refunds Policy: Perishable Goods' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $returns_refunds_perishable_goods_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Returns and Refunds Policy: Perishable Goods',
						'content'    => $returns_refunds_perishable_goods,
						'contentfor' => 'hFrxQomrZM',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'content'    => $returns_refunds_perishable_goods,
						'contentfor' => 'hFrxQomrZM',
					),
					array( 'title' => 'Returns and Refunds Policy: Perishable Goods' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			$amazon_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $legal_pages->tablename . ' WHERE contentfor=%s', array( '3ILrb9ARfX' ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( '0' === $amazon_count ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$legal_pages->tablename,
					array(
						'title'      => 'Amazon Affiliate Disclosure',
						'content'    => $amazon,
						'contentfor' => '3ILrb9ARfX',
						'is_active'  => '1',
					),
					array( '%s', '%s', '%s', '%d' )
				);
			} else {
				$wpdb->update(
					$legal_pages->tablename,
					array(
						'title'   => 'Amazon Affiliate Disclosure',
						'content' => $amazon,
					),
					array( 'contentfor' => '3ILrb9ARfX' ),
					array( '%s', '%s' ),
					array( '%s' )
				); // db call ok; no-cache ok.
			}
			delete_option( '_lp_db_updated' );
			delete_option( '_lp_terms_updated' );
			delete_option( '_lp_terms_fr_de_updated' );
			add_option( '_lp_templates_updated', true );
			add_option( '_lp_effective_date_templates_updated', true );
			add_option( 'lp_excludePage', 'true' );
			add_option( 'lp_general', '' );
			add_option( 'lp_accept_terms', '0' );
			add_option( 'lp_eu_cookie_title', 'A note to our visitors' );
			$message_body = 'This website has updated its privacy policy in compliance with changes to European Union data protection law, for all members globally. We’ve also updated our Privacy Policy to give you more information about your rights and responsibilities with respect to your privacy and personal information. Please read this to review the updates about which cookies we use and what information we collect on our site. By continuing to use this site, you are agreeing to our updated privacy policy.';
			add_option( 'lp_eu_cookie_message', htmlentities( $message_body ) );
			add_option( 'lp_eu_cookie_enable', 'OFF' );
			add_option( 'lp_eu_box_color', '#000000' );
			add_option( 'lp_eu_button_text', 'I agree' );
			add_option( 'lp_eu_theme_css', '1' );
			add_option( 'lp_eu_cookie_message', htmlentities( $message_body ) );
			add_option( 'lp_eu_cookie_enable', 'OFF' );
			add_option( 'lp_eu_box_color', '#000000' );
			add_option( 'lp_eu_button_color', '#e3e3e3' );
			add_option( 'lp_eu_button_text_color', '#333333' );
			add_option( 'lp_eu_text_color', '#FFFFFF' );
			add_option( 'lp_eu_link_color', '#8f0410' );
			add_option( 'lp_eu_text_size', '12' );

		}
	}


}
