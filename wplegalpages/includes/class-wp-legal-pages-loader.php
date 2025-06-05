<?php
/**
 * Register all actions and filters for the WPLegalPages
 *
 * @link       http://wplegalpages.com/
 * @since      1.5.2
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 */

/**
 * Register all actions and filters for the WPLegalPages.
 *
 * Maintain a list of all hooks that are registered throughout
 * the WPLegalPages, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    WP_Legal_Pages
 * @subpackage WP_Legal_Pages/includes
 * @author     WPEka <support@wplegalpages.com>
 */
if ( ! class_exists( 'WP_Legal_Pages_Loader' ) ) {
	/**
	 * Register all actions and filters for the WPLegalPages.
	 *
	 * Maintain a list of all hooks that are registered throughout
	 * the WPLegalPages, and register them with the WordPress API. Call the
	 * run function to execute the list of actions and filters.
	 *
	 * @package    WP_Legal_Pages
	 * @subpackage WP_Legal_Pages/includes
	 * @author     WPEka <support@wplegalpages.com>
	 */
	class WP_Legal_Pages_Loader {

		/**
		 * The array of actions registered with WordPress.
		 *
		 * @since    1.5.2
		 * @access   protected
		 * @var      array    $actions    The actions registered with WordPress to fire when the WPLegalPages loads.
		 */

		protected $actions;

		/**
		 * The array of filters registered with WordPress.
		 *
		 * @since    1.5.2
		 * @access   protected
		 * @var      array    $filters    The filters registered with WordPress to fire when the WPLegalPages loads.
		 */
		protected $filters;

		/**
		 * Initialize the collections used to maintain the actions and filters.
		 *
		 * @since    1.5.2
		 */
		public function __construct() {

			$this->actions = array();
			$this->filters = array();

		}

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @since    1.5.2
		 * @param    string $hook             The name of the WordPress action that is being registered.
		 * @param    object $component        A reference to the instance of the object on which the action is defined.
		 * @param    string $callback         The name of the function definition on the $component.
		 * @param    int    $priority         Optional. he priority at which the function should be fired. Default is 10.
		 * @param    int    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
		 */
		public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @since    1.5.2
		 * @param    string $hook             The name of the WordPress filter that is being registered.
		 * @param    object $component        A reference to the instance of the object on which the filter is defined.
		 * @param    string $callback         The name of the function definition on the $component.
		 * @param    int    $priority         Optional. he priority at which the function should be fired. Default is 10.
		 * @param    int    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
		 */
		public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * A utility function that is used to register the actions and hooks into a single
		 * collection.
		 *
		 * @since    1.5.2
		 * @access   private
		 * @param    array  $hooks            The collection of hooks that is being registered (that is, actions or filters).
		 * @param    string $hook             The name of the WordPress filter that is being registered.
		 * @param    object $component        A reference to the instance of the object on which the filter is defined.
		 * @param    string $callback         The name of the function definition on the $component.
		 * @param    int    $priority         The priority at which the function should be fired.
		 * @param    int    $accepted_args    The number of arguments that should be passed to the $callback.
		 * @return   array                                  The collection of actions and filters registered with WordPress.
		 */
		private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

			$hooks[] = array(
				'hook'          => $hook,
				'component'     => $component,
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args,
			);
			return $hooks;

		}

		/**
		 * Register the filters and actions with WordPress.
		 *
		 * @since    1.5.2
		 */
		public function run() {

			foreach ( $this->filters as $hook ) {
				add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

			foreach ( $this->actions as $hook ) {
				add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

		}
		/* Add defer attribute to scripts */
		public function wplp_register_script_with_defer( $handle, $src, $deps = array(), $ver = false, $in_footer = true ) {
			wp_register_script( $handle, $src, $deps, $ver, $in_footer );

			add_filter( 'script_loader_tag', function ( $tag, $h, $s ) use ( $handle ) {
				if ( $h === $handle ) {
					return str_replace( ' src', ' defer src', $tag );
				}
				return $tag;
			}, 10, 3 );
		}
		public function wplp_register_style_with_defer( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
			wp_register_style( $handle, $src, $deps, $ver, $media );

			add_filter( 'style_loader_tag', function ( $tag, $h ) use ( $handle ) {
				if ( $h === $handle ) {
					return str_replace( "media='all'", "media='print' onload=\"this.onload=null;this.media='all';\"", $tag );
				}
				return $tag;
			}, 10, 2 );
		}

	}
}
