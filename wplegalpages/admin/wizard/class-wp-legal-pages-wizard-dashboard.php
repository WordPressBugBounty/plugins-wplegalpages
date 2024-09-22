<?php
/**
 * WPLegalPages Dashboard.
 *
 * This class defines all the pages on wizard dashboard.
 *
 * @since      1.5.2
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/admin/wizard
 * @author     WPEka <support@wplegalpages.com>
 */

if ( ! class_exists( 'WP_Legal_Pages_Wizard_Dashboard' ) ) {

	/**
	 * WPLegalPages Dashboard.
	 *
	 * This class defines all the pages on wizard dashboard.
	 *
	 * @since      1.5.2
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/admin/wizard
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages_Wizard_Dashboard {

		/**
		 *
		 * Legal Pages Array.
		 *
		 * @var array $legal_pages legalpages
		 */
		public $legal_pages = array();

		/**
		 *
		 * Pro Legal Pages Array.
		 *
		 * @var array $pro_legalpages legalpages
		 */
		public $pro_legalpages = array();

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			// Array to store retrieved content.
			$content         = array();
			// Fetch data from the specified API URL.
			$response        = wp_remote_get( WPLEGAL_API_URL . 'get_list_of_tempaltes' );
			// Retrieve HTTP response status code.
			$response_status = wp_remote_retrieve_response_code( $response );

			// If the response status is successful (HTTP 200),
   			// decode the JSON response body and assign it to $content.
			if ( 200 === $response_status ) {
				$content = json_decode( wp_remote_retrieve_body( $response ),true );
			}
			// Assign the retrieved content to the 'legal_pages' property of the class instance.
			$this->legal_pages = $content;

		}

		/**
		 *
		 * Get Legal Pages.
		 *
		 * @return array $legal_pages legal pages.
		 */
		public function get_legal_pages() {
			return $this->legal_pages;
		}

		/**
		 *
		 * Get Pro Legal Pages
		 *
		 * @return array $pro_legalpages legal pages.
		 */
		public function get_pro_legal_pages() {
			return $this->pro_legalpages;
		}

	}
}
