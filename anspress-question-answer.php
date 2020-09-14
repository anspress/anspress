<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * An advanced community question and answer system for WordPress
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   AnsPress
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress Question Answer
 * Plugin URI:        https://anspress.io
 * Description:       The most advance community question and answer system for WordPress
 * Donate link:       https://goo.gl/ffainr
 * Version:           4.2.0
 * Author:            Rahul Aryan
 * Author URI:        https://anspress.io
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       anspress-question-answer
 * Domain Path:       /languages
 * GitHub Plugin URI: qstudio/anspress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define database version.
define( 'AP_DB_VERSION', 35 );

// Check if using required PHP version.
if ( version_compare( PHP_VERSION, '5.5' ) < 0 ) {

	/**
	 * Checks PHP version before initiating AnsPress.
	 */
	function ap_admin_php_version__error() {
		$class    = 'notice notice-error';
		$message  = '<strong>' . __( 'AnsPress is not running!', 'anspress-question-answer' ) . '</strong><br />';
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
		private $_plugin_version = '4.2.00';

		/**
		 * Class instance
		 *
		 * @access public
		 * @static
		 * @var object
		 */
		public static $instance = null;

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
		 * AnsPress reputation events.
		 *
		 * @access public
		 * @var object
		 */
		public $reputation_events;

		/**
		 * AnsPress user pages.
		 *
		 * @access public
		 * @var object
		 */
		public $user_pages;

		/**
		 * AnsPress question rewrite rules.
		 *
		 * @var array
		 * @since 4.1.0
		 */
		public $question_rule = [];

		/**
		 * The forms.
		 *
		 * @var array
		 * @since 4.1.0
		 */
		public $forms = [];

		/**
		 * The activity object.
		 *
		 * @var void|object
		 * @since 4.1.2
		 */
		public $activity;

		/**
		 * The session.
		 *
		 * @var AnsPress\Session
		 * @since 4.1.5
		 */
		public $session;

		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 *
		 * @access public
		 * @static
		 *
		 * @return instance
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
				self::$instance->setup_constants();
				self::$instance->actions = array();
				self::$instance->filters = array();

				self::$instance->includes();
				self::$instance->session = AnsPress\Session::init();

				self::$instance->site_include();
				self::$instance->ajax_hooks();
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

				new AnsPress_Process_Form();

				/*
				 * Hooks for extension to load their codes after AnsPress is loaded.
				 */
				do_action( 'anspress_loaded' );

				if ( class_exists( 'WP_CLI' ) ) {
					WP_CLI::add_command( 'anspress', 'AnsPress_Cli' );
				}
			} // End if().

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since  2.0.1
		 * @access private
		 */
		private function setup_constants() {
			$plugin_dir = wp_normalize_path( plugin_dir_path( __FILE__ ) );

			$constants = array(
				'DS'                  => DIRECTORY_SEPARATOR,
				'AP_VERSION'          => $this->_plugin_version,
				'ANSPRESS_DIR'        => $plugin_dir,
				'ANSPRESS_URL'        => plugin_dir_url( __FILE__ ),
				'ANSPRESS_WIDGET_DIR' => $plugin_dir . 'widgets/',
				'ANSPRESS_THEME_DIR'  => $plugin_dir . 'templates',
				'ANSPRESS_THEME_URL'  => plugin_dir_url( __FILE__ ) . 'templates',
				'ANSPRESS_CACHE_DIR'  => WP_CONTENT_DIR . '/cache/anspress',
				'ANSPRESS_CACHE_TIME' => HOUR_IN_SECONDS,
				'ANSPRESS_ADDONS_DIR' => $plugin_dir . 'addons',
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
			require_once ANSPRESS_DIR . 'loader.php';
			require_once ANSPRESS_DIR . 'includes/activity.php';
			require_once ANSPRESS_DIR . 'includes/common-pages.php';
			require_once ANSPRESS_DIR . 'includes/class-theme.php';
			require_once ANSPRESS_DIR . 'includes/class-form-hooks.php';
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
			require_once ANSPRESS_DIR . 'includes/votes.php';
			require_once ANSPRESS_DIR . 'includes/views.php';
			require_once ANSPRESS_DIR . 'includes/theme.php';
			require_once ANSPRESS_DIR . 'includes/shortcode-basepage.php';
			require_once ANSPRESS_DIR . 'includes/process-form.php';
			require_once ANSPRESS_DIR . 'includes/rewrite.php';
			require_once ANSPRESS_DIR . 'includes/deprecated.php';
			require_once ANSPRESS_DIR . 'includes/flag.php';
			require_once ANSPRESS_DIR . 'includes/shortcode-question.php';
			require_once ANSPRESS_DIR . 'includes/akismet.php';
			require_once ANSPRESS_DIR . 'includes/comments.php';
			require_once ANSPRESS_DIR . 'includes/upload.php';
			require_once ANSPRESS_DIR . 'includes/taxo.php';
			require_once ANSPRESS_DIR . 'includes/reputation.php';
			require_once ANSPRESS_DIR . 'includes/subscribers.php';
			require_once ANSPRESS_DIR . 'includes/class-query.php';
			require_once ANSPRESS_DIR . 'includes/class/class-activity-helper.php';
			require_once ANSPRESS_DIR . 'includes/class/class-activity.php';
			require_once ANSPRESS_DIR . 'includes/class/class-session.php';

			require_once ANSPRESS_DIR . 'widgets/search.php';
			require_once ANSPRESS_DIR . 'widgets/question_stats.php';
			require_once ANSPRESS_DIR . 'widgets/questions.php';
			require_once ANSPRESS_DIR . 'widgets/breadcrumbs.php';
			require_once ANSPRESS_DIR . 'widgets/ask-form.php';

			require_once ANSPRESS_DIR . 'lib/class-anspress-upgrader.php';
			require_once ANSPRESS_DIR . 'lib/class-form.php';
			require_once ANSPRESS_DIR . 'lib/form/class-field.php';
			require_once ANSPRESS_DIR . 'lib/form/class-input.php';
			require_once ANSPRESS_DIR . 'lib/form/class-group.php';
			require_once ANSPRESS_DIR . 'lib/form/class-repeatable.php';
			require_once ANSPRESS_DIR . 'lib/form/class-checkbox.php';
			require_once ANSPRESS_DIR . 'lib/form/class-select.php';
			require_once ANSPRESS_DIR . 'lib/form/class-editor.php';
			require_once ANSPRESS_DIR . 'lib/form/class-upload.php';
			require_once ANSPRESS_DIR . 'lib/form/class-tags.php';
			require_once ANSPRESS_DIR . 'lib/form/class-radio.php';
			require_once ANSPRESS_DIR . 'lib/form/class-textarea.php';
			require_once ANSPRESS_DIR . 'lib/class-validate.php';
			require_once ANSPRESS_DIR . 'lib/class-wp-async-task.php';

			require_once ANSPRESS_DIR . 'includes/class-async-tasks.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once ANSPRESS_DIR . 'lib/class-anspress-cli.php';
			}
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
				AnsPress_Admin_Ajax::init();
			}
		}

		/**
		 * Include all public classes
		 *
		 * @access public
		 * @since 0.0.1
		 * @since 4.1.8 Load all addons if constant `ANSPRESS_ENABLE_ADDONS` is set.
		 */
		public function site_include() {
			$this->theme_compat = new stdClass(); // Base theme compatibility class.

			$this->theme_compat->active = false;

			\AnsPress_Hooks::init();
			$this->activity = AnsPress\Activity_Helper::get_instance();
			\AnsPress_Views::init();

			// Load all addons if constant set.
			if ( defined( 'ANSPRESS_ENABLE_ADDONS' ) && ANSPRESS_ENABLE_ADDONS ) {
				foreach ( ap_get_addons() as $name => $data ) {
					ap_activate_addon( $name );
				}
			}

			foreach ( (array) ap_get_addons() as $data ) {
				if ( $data['active'] && file_exists( $data['path'] ) ) {
					require_once $data['path'];
				}
			}
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
		 * @param integer           $accepted_args Accepted arguments.
		 *
		 * @return type The collection of actions and filters registered with WordPress.
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
		 * @access public
		 */
		public function setup_hooks() {
			foreach ( $this->filters as $hook ) {
				add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

			foreach ( $this->actions as $hook ) {
				add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}
		}

		/**
		 * Get specific AnsPress form.
		 *
		 * @param string $name Name of form.
		 * @return false|object
		 * @since 4.1.0
		 */
		public function &get_form( $name ) {
			$name = preg_replace( '/^form_/i', '', $name );

			if ( $this->form_exists( $name ) ) {
				return $this->forms[ $name ];
			}

			return false;
		}

		/**
		 * Check if a form exists in AnsPress, if not then tries to register.
		 *
		 * @param string $name Name of form.
		 * @return boolean
		 * @since 4.1.0
		 */
		public function form_exists( $name ) {
			$name = preg_replace( '/^form_/i', '', $name );

			if ( isset( $this->forms[ $name ] ) ) {
				return true;
			}

			/**
			 * Register a form in AnsPress.
			 *
			 * @param array $form {
			 *      Form options and fields. Check @see `AnsPress\Form` for more detail.
			 *
			 *      @type string  $submit_label Custom submit button label.
			 *      @type boolean $editing      Pass true if currently in editing mode.
			 *      @type integer $editing_id   If editing then pass editing post or comment id.
			 *      @type array   $fields       Fields. For more detail on field option check documentations.
			 * }
			 * @since 4.1.0
			 * @todo  Add detailed docs for `$fields`.
			 */
			$args = apply_filters( 'ap_form_' . $name, null );

			if ( ! is_null( $args ) && ! empty( $args ) ) {
				$this->forms[ $name ] = new AnsPress\Form( 'form_' . $name, $args );

				return true;
			}

			return false;
		}
	}
} // End if().

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
			 * Action before loading AnsPress.
			 * @since 2.4.7
			 */
			do_action( 'before_loading_anspress' );
			anspress()->setup_hooks();
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
				require_once dirname( __FILE__ ) . '/activate.php';
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

			$tables[] = $wpdb->prefix . 'ap_views';
			$tables[] = $wpdb->prefix . 'ap_qameta';
			$tables[] = $wpdb->prefix . 'ap_activity';
			$tables[] = $wpdb->prefix . 'ap_votes';
			return $tables;
		}
	}
} // End if().

add_action( 'plugins_loaded', [ 'AnsPress_Init', 'load_anspress' ], 1 );
add_action( 'plugins_loaded', [ 'AnsPress_Init', 'load_textdomain' ], 0 );
add_action( 'wpmu_new_blog', [ 'AnsPress_Init', 'create_blog' ], 10, 6 );
add_filter( 'wpmu_drop_tables', [ 'AnsPress_Init', 'drop_blog_tables' ], 10, 2 );

require_once dirname( __FILE__ ) . '/includes/class/roles-cap.php';
require_once dirname( __FILE__ ) . '/includes/class/class-singleton.php';

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
function anspress_activation() {
	require_once dirname( __FILE__ ) . '/activate.php';
	\AP_Activate::get_instance();
}
register_activation_hook( __FILE__, 'anspress_activation' );
