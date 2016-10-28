<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * The most advance community question and answer system for WordPress
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 WP3.in & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   AnsPress
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        https://anspress.io
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: 	    https://goo.gl/ffainr
 * Version:           4.0.0-alpha.1
 * Author:            Rahul Aryan
 * Author URI:        https://anspress.io
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       anspress-question-answer
 * Domain Path:       /languages
 * GitHub Plugin URI: anspress/anspress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if using required PHP version.
if ( version_compare( PHP_VERSION, '5.5' ) < 0 ) {

	/**
	 * Checks PHP version before initiating AnsPress.
	 */
	function ap_admin_php_version__error() {
		$class = 'notice notice-error';
		$message = '<strong>' . __( 'AnsPress is not running!', 'anspress-question-answer' ) . '</strong><br />';
		$message .= sprintf( __( 'Irks! At least PHP version 5.5 is required to run AnsPress. Current PHP version is %s. Please ask hosting provider to update your PHP version.', 'anspress-question-answer' ), PHP_VERSION );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	add_action( 'admin_notices', 'ap_admin_php_version__error' );
	return;
}

if ( ! class_exists( 'AnsPress' ) ) {

	/**
	 * Main AnsPress class.
	 */
	class AnsPress {

		/**
		 * AnsPress version
		 *
		 * @access private
		 * @var string
		 */
		private $_plugin_version = '4.0.0-alpha.1';

		/**
		 * Class instance
		 *
		 * @access public
		 * @static
		 * @var object
		 */
		public static $instance = null;

		/**
		 * AnsPress hooks
		 *
		 * @access public
		 * @var object Register all AnsPress hooks
		 */
		public $anspress_hooks;

		/**
		 * AnsPress ajax
		 *
		 * @access public
		 * @var object Register all AnsPress ajax hooks
		 */
		public $anspress_ajax;

		/**
		 * Admin ajax
		 *
		 * @access public
		 * @var object Register all admin ajax hooks
		 */
		public $admin_ajax;

		/**
		 * AnsPress pages
		 *
		 * @access public
		 * @var array All AnsPress pages
		 */
		public $pages;

		/**
		 * AnsPress menu
		 *
		 * @access public
		 * @var array AnsPress menu
		 */
		public $menu;

		/**
		 * AnsPress question loop
		 *
		 * @access public
		 * @var object AnsPress question query loop
		 */
		public $questions;

		/**
		 * Current question.
		 *
		 * @var object
		 */
		public $current_question;

		/**
		 * AnsPress answers loop.
		 *
		 * @var object Answer query loop
		 */
		public $answers;

		/**
		 * Current answer.
		 *
		 * @var object
		 */
		public $current_answer;

		/**
		 * AnsPress form
		 *
		 * @access public
		 * @var object AnsPress form
		 */
		public $form;

		/**
		 * The array of actions registered with WordPress.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @var array The actions registered with WordPress to fire when the plugin loads.
		 */
		protected $actions;

		/**
		 * The array of filters registered with WordPress.
		 *
		 * @since  1.0.0
		 * @access protected
		 * @var array The filters registered with WordPress to fire when the plugin loads.
		 */
		protected $filters;

		/**
		 * AnsPress form object
		 *
		 * @access public
		 * @var object
		 */
		public $anspress_forms;

		/**
		 * AnsPress bad words object
		 *
		 * @access public
		 * @var object
		 */
		public $bad_words_class;

		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 *
		 * @access public
		 * @static
		 *
		 * @return instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! (self::$instance instanceof self) ) {

				self::$instance = new self();
				self::$instance->setup_constants();
				self::$instance->actions = array();
				self::$instance->filters = array();

				global $ap_classes;
				$ap_classes = array();

				self::$instance->includes();
				self::$instance->ajax_hooks();
				self::$instance->site_include();

				AnsPress_PostTypes::init();

				/*
				* Dashboard and Administrative Functionality
				*/
				if ( is_admin() ) {
					require_once ANSPRESS_DIR . 'admin/anspress-admin.php';
					require_once ANSPRESS_DIR . 'admin/class-list-table-hooks.php';

					AnsPress_Admin::init();
					AnsPress_Post_Table_Hooks::init();
				}

				self::$instance->anspress_forms 		       = new AnsPress_Process_Form();

				/*
				 * ACTION: anspress_loaded
				 * Hooks for extension to load their codes after AnsPress is leaded
				 */
				do_action( 'anspress_loaded' );

				self::$instance->setup_hooks();
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since  2.0.1
		 * @access private
		 */
		private function setup_constants() {
				$constants = array(
				'DS' 										=> DIRECTORY_SEPARATOR,
				'AP_VERSION' 						=> $this->_plugin_version,
				'AP_DB_VERSION' 				=> 22,
				'ANSPRESS_DIR' 					=> plugin_dir_path( __FILE__ ),
				'ANSPRESS_URL' 					=> plugin_dir_url( __FILE__ ),
				'ANSPRESS_WIDGET_DIR' 	=> plugin_dir_path( __FILE__ ) . 'widgets' . DIRECTORY_SEPARATOR,
				'ANSPRESS_THEME_DIR' 		=> plugin_dir_path( __FILE__ ) . 'theme',
				'ANSPRESS_THEME_URL' 		=> plugin_dir_url( __FILE__ ) . 'theme',
				'ANSPRESS_CACHE_DIR' 		=> WP_CONTENT_DIR . '/cache/anspress',
				'ANSPRESS_CACHE_TIME' 	=> HOUR_IN_SECONDS,
			);

			foreach ( $constants as $k => $val ) {
				if ( ! defined( $k ) ) {
						define( $k, $val );
				}
			}
		}

		/**
		 * Include required files.
		 *
		 * @since  2.0.1
		 * @access private
		 */
		private function includes() {
			global $ap_options;

			require_once ANSPRESS_DIR . 'includes/class/form.php';
			require_once ANSPRESS_DIR . 'includes/class/validation.php';
			require_once ANSPRESS_DIR . 'includes/class/roles-cap.php';
			require_once ANSPRESS_DIR . 'includes/common-pages.php';
			require_once ANSPRESS_DIR . 'includes/class-theme.php';
			require_once ANSPRESS_DIR . 'includes/options.php';
			require_once ANSPRESS_DIR . 'includes/functions.php';
			require_once ANSPRESS_DIR . 'includes/hooks.php';
			require_once ANSPRESS_DIR . 'includes/question-loop.php';
			require_once ANSPRESS_DIR . 'includes/answer-loop.php';
			require_once ANSPRESS_DIR . 'includes/qameta.php';
			require_once ANSPRESS_DIR . 'includes/qaquery.php';
			require_once ANSPRESS_DIR . 'includes/qaquery-hooks.php';
			require_once ANSPRESS_DIR . 'includes/post-types.php';
			require_once ANSPRESS_DIR . 'includes/post-status.php';
			require_once ANSPRESS_DIR . 'includes/meta.php';
			require_once ANSPRESS_DIR . 'includes/votes.php';
			require_once ANSPRESS_DIR . 'includes/views.php';
			require_once ANSPRESS_DIR . 'includes/theme.php';
			require_once ANSPRESS_DIR . 'includes/form.php';
			require_once ANSPRESS_DIR . 'includes/shortcode-basepage.php';
			require_once ANSPRESS_DIR . 'includes/process-form.php';
			require_once ANSPRESS_DIR . 'includes/ask-form.php';
			require_once ANSPRESS_DIR . 'includes/answer-form.php';
			require_once ANSPRESS_DIR . 'widgets/search.php';
			require_once ANSPRESS_DIR . 'widgets/question_stats.php';
			require_once ANSPRESS_DIR . 'widgets/related_questions.php';
			require_once ANSPRESS_DIR . 'widgets/questions.php';
			require_once ANSPRESS_DIR . 'widgets/breadcrumbs.php';
			require_once ANSPRESS_DIR . 'widgets/ask-form.php';
			require_once ANSPRESS_DIR . 'includes/rewrite.php';
			require_once ANSPRESS_DIR . 'includes/bad-words.php';
			require_once ANSPRESS_DIR . 'includes/deprecated.php';
			require_once ANSPRESS_DIR . 'includes/flag.php';
			require_once ANSPRESS_DIR . 'includes/shortcode-question.php';
			require_once ANSPRESS_DIR . 'includes/akismet.php';
			require_once ANSPRESS_DIR . 'includes/comments.php';
			require_once ANSPRESS_DIR . 'includes/class/avatar.php';
		}

		/**
		 * Register ajax hooks
		 *
		 * @access public
		 */
		public function ajax_hooks() {
			// Load ajax hooks only if DOING_AJAX defined.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				require_once ANSPRESS_DIR . 'admin/ajax.php';
				require_once ANSPRESS_DIR . 'includes/ajax-hooks.php';

				AnsPress_Ajax::init();
				AnsPress_Admin_Ajax::init( );
			}
		}

		/**
		 * Include all public classes
		 *
		 * @access public
		 */
		public function site_include() {
			self::$instance->anspress_hooks 	= AnsPress_Hooks::init();
			AnsPress_Views::init();
			self::$instance->bad_words_class 	= new AP_Bad_words( );
		}

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @since  2.4
		 * @access public
		 *
		 * @param string            $hook          The name of the WordPress action that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the action is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 */
		public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @since  2.4
		 * @access public
		 *
		 * @param string            $hook          The name of the WordPress filter that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the filter is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 */
		public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * A utility function that is used to register the actions and hooks into a single
		 * collection.
		 *
		 * @since  2.4
		 * @access private
		 *
		 * @param array             $hooks         The collection of hooks that is being registered (that is, actions or filters).
		 * @param string            $hook          The name of the WordPress filter that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the filter is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 * @param integer           $priority      Priority.
		 * @param integer           $accepted_args Accepted aruments.
		 *
		 * @return type The collection of actions and filters registered with WordPress.
		 */
		private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
			$hooks[] = array(
				'hook'      => $hook,
				'component' => $component,
				'callback'  => $callback,
				'priority'  => $priority,
				'accepted_args' => $accepted_args,
			);

			return $hooks;
		}

		/**
		 * Register the filters and actions with WordPress.
		 *
		 * @access private
		 */
		private function setup_hooks() {
			foreach ( $this->filters as $hook ) {
				add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

			foreach ( $this->actions as $hook ) {
				add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}
		}
	}
}

/**
 * Run AnsPress thingy
 *
 * @return object
 */
if ( ! function_exists( 'anspress' ) ) {
	/**
	 * Initialize AnsPress.
	 */
	function anspress() {
		return AnsPress::instance();
	}
}

if ( ! class_exists( 'AnsPress_Init' ) ) {

	/**
	 * AnsPress initialization class.
	 */
	class AnsPress_Init {

		/**
		 * Load anspress.
		 *
		 * @access public
		 * @static
		 */
		public static function load_anspress() {
			/*
			 * ACTION: before_loading_anspress
			 * Action before loading AnsPress.
			 * @since 2.4.7
			 */
			do_action( 'before_loading_anspress' );
			anspress();
		}

		/**
		 * Load translations.
		 *
		 * @since  2.0.1
		 * @access public
		 * @static
		 */
		public static function load_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'anspress-question-answer' );
			$loaded = load_textdomain( 'anspress-question-answer', trailingslashit( WP_LANG_DIR ) . "anspress-question-answer/anspress-question-answer-{$locale}.mo" );

			if ( $loaded ) {
				return $loaded;
			} else {
				load_plugin_textdomain( 'anspress-question-answer', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Delete a cpt posts. Used by AnsPress uninstaller.
		 *
		 * @since  3.0.0
		 * @access public
		 * @static
		 * @param  string $type Accepted args question or answer.
		 */
		public static function delete_cpt( $type = 'question' ) {
			global $wpdb;
			$count = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->posts WHERE post_type = '%s'", $type ) ); // db call ok, cache ok.

			$deleted = 0;

			if ( $count > 0 ) {
				while ( $deleted <= $count ) {
					$question_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = '%s' LIMIT 50", $type ) ); // db call okay, cache okay.

					foreach ( (array) $question_ids as $id ) {
						wp_delete_post( $id, true );
						$deleted++;
					}
				}
			}
		}

		/**
		 * Plugin un-installation hook, called by WP while removing AnsPress
		 *
		 * @access public
		 * @static
		 */
		public static function anspress_uninstall() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			check_admin_referer( 'bulk-plugins' );
			$option = get_option( 'anspress_opt' );
			if ( ! isset( $option['db_cleanup'] ) || (isset( $option['db_cleanup'] ) && $option['db_cleanup']) ) {
				return;
			}

			global $wpdb;

			// Remove question CPT.
			SELF::delete_cpt();

			// Removes answer CPT.
			SELF::delete_cpt( 'answer' );

			// Remove tables
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ap_meta" ); // @codingStandardsIgnoreLine

			// Remove options.
			delete_option( 'anspress_opt' );

			require_once ANSPRESS_DIR . 'includes/class/roles-cap.php';

			// Remove user roles.
			AP_Roles::remove_roles();
		}

		/**
		 * Before activation redirect
		 *
		 * @access public
		 * @static
		 *
		 * @param  string $plugin Plugin base name.
		 */
		public static function activation_redirect( $plugin ) {
			if ( plugin_basename( __FILE__ ) === $plugin ) {
				add_option( 'anspress_do_installation_redirect', true );
			}
		}

		/**
		 * Creating table whenever a new blog is created
		 *
		 * @access public
		 * @static
		 *
		 * @param  integer $blog_id Blog id.
		 * @param  integer $user_id User id.
		 * @param  string  $domain  Domain.
		 * @param  string  $path    Path.
		 * @param  integer $site_id Site id.
		 * @param  array   $meta    Site meta.
		 */
		public static function create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				switch_to_blog( $blog_id ); // @codingStandardsIgnoreLine
				AP_Activate::get_instance( true );
				restore_current_blog();
			}
		}

		/**
		 * Deleting the table whenever a blog is deleted
		 *
		 * @access public
		 * @static
		 *
		 * @param  array $tables  Table names.
		 * @param  int   $blog_id Blog ID.
		 *
		 * @return array
		 */
		public static function drop_blog_tables( $tables, $blog_id ) {
			if ( empty( $blog_id ) || 1 === (int) $blog_id || $blog_id !== $GLOBALS['blog_id'] ) {
				return $tables;
			}

			global $wpdb;

			$tables[] 	= $wpdb->prefix . 'ap_meta';
			$tables[] 	= $wpdb->prefix . 'ap_qameta';
			return $tables;
		}

		/**
		 * Redirect to about AnsPress page after activating AnsPress.
		 *
		 * @since  3.0.0
		 * @access public
		 * @static
		 */
		public static function redirect_to_about_page() {
			if ( get_option( 'anspress_do_installation_redirect' ) ) {
				delete_option( 'anspress_do_installation_redirect' );
				wp_safe_redirect( admin_url( 'admin.php?page=anspress_about' ) );
				wp_die();
			}
		}

	}
}

add_action( 'plugins_loaded', [ 'AnsPress_Init', 'load_anspress' ] );
add_action( 'plugins_loaded', [ 'AnsPress_Init', 'load_textdomain' ] );
add_action( 'activated_plugin', [ 'AnsPress_Init', 'activation_redirect' ] );
add_action( 'wpmu_new_blog', [ 'AnsPress_Init', 'create_blog' ], 10, 6 );
add_filter( 'wpmu_drop_tables', [ 'AnsPress_Init', 'drop_blog_tables' ], 10, 2 );
add_filter( 'admin_init', [ 'AnsPress_Init', 'redirect_to_about_page' ] );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
require_once dirname( __FILE__ ) . '/activate.php';

register_activation_hook( __FILE__, [ 'AP_Activate', 'get_instance' ] );
register_uninstall_hook( __FILE__, [ 'AnsPress_Init', 'anspress_uninstall' ] );
