<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * The most advance community question and answer system for WordPress
 *
 * @author    Rahul Aryan <rah12@live.com>
 * @copyright Copyright (c) 2014-2020, Rahul Aryan. 2020, LattePress
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.net
 * @package   AnsPress
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress Question Answer
 * Plugin URI:        https://anspress.net
 * Description:       The most advance community question and answer system for WordPress
 * Donate link:       https://paypal.me/anspress
 * Version:           5.0.0
 * Author:            Rahul Aryan
 * Author URI:        https://anspress.net
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       anspress-question-answer
 * Domain Path:       /languages
 * GitHub Plugin URI: anspress/anspress
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\Router;
use AnsPress\Modules\Answer\AnswerPolicy;
use AnsPress\Modules\Comment\CommentPolicy;
use AnsPress\Modules\Config\ConfigService;
use AnsPress\Modules\Question\QuestionPolicy;
use AnsPress\Modules\Subscriber\SubscriberPolicy;
use AnsPress\Modules\Vote\VotePolicy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// Define database version.
define( 'AP_DB_VERSION', 38 ); // @todo remove this in version 5.0.0

// New constants for version 5.0.0.
define( 'ANSPRESS_DB_VERSION', 38 );
define( 'ANSPRESS_PLUGIN_VERSION', '5.0.0' );
define( 'ANSPRESS_PLUGIN_FILE', __FILE__ );

// Check if using required PHP version.
if ( version_compare( PHP_VERSION, '8.0' ) < 0 ) {

	/**
	 * Checks PHP version before initiating AnsPress.
	 */
	function ap_admin_php_version__error() {
		$class    = 'notice notice-error';
		$message  = '<strong>' . __( 'AnsPress is not running!', 'anspress-question-answer' ) . '</strong><br />';
		$message .= sprintf(
			// translators: %s contain server PHP version.
			__( 'Irks! At least PHP version 7.2 is required to run AnsPress. Current PHP version is %s. Please ask hosting provider to update your PHP version.', 'anspress-question-answer' ),
			PHP_VERSION
		);
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
		private $_plugin_version = '5.5.0'; // phpcs:ignore

		/**
		 * Class instance
		 *
		 * @access public
		 * @static
		 * @var AnsPress
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
		 * @var null|WP_Query AnsPress question query loop
		 */
		public $questions;

		/**
		 * Current question.
		 *
		 * @var WP_Post|null
		 */
		public $current_question;

		/**
		 * AnsPress answers loop.
		 *
		 * @var WP_Query|null Answer query loop
		 */
		public $answers;

		/**
		 * Current answer.
		 *
		 * @var WP_Post|null
		 */
		public $current_answer;

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
		public $question_rule = array();

		/**
		 * The forms.
		 *
		 * @var array
		 * @since 4.1.0
		 */
		public $forms = array();

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
		 * Used for property assignment.
		 *
		 * @var object
		 */
		public $theme_compat;

		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 *
		 * @access public
		 * @static
		 *
		 * @return AnsPress
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
				self::$instance->setup_constants();

				self::$instance->includes();
				self::$instance->session = AnsPress\Session::init();

				self::$instance->site_include();
				self::$instance->ajax_hooks();
				AnsPress_PostTypes::init();

				// Add roles.
				$ap_roles = new AP_Roles();
				$ap_roles->add_roles();
				$ap_roles->add_capabilities();

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
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since  2.0.1
		 * @access private
		 * @since 4.2.0 Made constants compatible for code editors.
		 * @codeCoverageIgnore
		 */
		private function setup_constants() {
			$plugin_dir = wp_normalize_path( plugin_dir_path( __FILE__ ) );

			define( 'DS', DIRECTORY_SEPARATOR );
			define( 'AP_VERSION', $this->_plugin_version );
			define( 'ANSPRESS_DIR', $plugin_dir );
			define( 'ANSPRESS_URL', plugin_dir_url( __FILE__ ) );
			define( 'ANSPRESS_WIDGET_DIR', $plugin_dir . 'widgets/' );
			define( 'ANSPRESS_THEME_DIR', $plugin_dir . 'templates' );
			define( 'ANSPRESS_THEME_URL', ANSPRESS_URL . 'templates' );
			define( 'ANSPRESS_CACHE_DIR', WP_CONTENT_DIR . '/cache/anspress' );
			define( 'ANSPRESS_CACHE_TIME', HOUR_IN_SECONDS );
			define( 'ANSPRESS_ADDONS_DIR', $plugin_dir . 'addons' );
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since  2.0.1
		 * @since  4.2.0 Added categories/categories.php
		 * @since  5.0.0 Removed flag.php and added deprecated-classes.php
		 * @codeCoverageIgnore
		 */
		private function includes() {
			require_once ANSPRESS_DIR . 'loader.php';
			require_once ANSPRESS_DIR . 'includes/activity.php';
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
			require_once ANSPRESS_DIR . 'includes/views.php';
			require_once ANSPRESS_DIR . 'includes/theme.php';
			require_once ANSPRESS_DIR . 'includes/process-form.php';
			require_once ANSPRESS_DIR . 'includes/rewrite.php';
			require_once ANSPRESS_DIR . 'includes/deprecated.php';
			require_once ANSPRESS_DIR . 'includes/deprecated-classes.php';
			require_once ANSPRESS_DIR . 'includes/shortcode-question.php';
			require_once ANSPRESS_DIR . 'includes/akismet.php';
			require_once ANSPRESS_DIR . 'includes/comments.php';
			require_once ANSPRESS_DIR . 'includes/upload.php';
			require_once ANSPRESS_DIR . 'includes/taxo.php';
			require_once ANSPRESS_DIR . 'includes/reputation.php';
			require_once ANSPRESS_DIR . 'includes/class-query.php';
			require_once ANSPRESS_DIR . 'includes/class/class-activity-helper.php';
			require_once ANSPRESS_DIR . 'includes/class/class-activity.php';
			require_once ANSPRESS_DIR . 'includes/class/class-session.php';
			require_once ANSPRESS_DIR . 'includes/class/class-abstract-addon.php';

			require_once ANSPRESS_DIR . 'widgets/search.php';
			require_once ANSPRESS_DIR . 'widgets/question_stats.php';
			require_once ANSPRESS_DIR . 'widgets/questions.php';
			require_once ANSPRESS_DIR . 'widgets/breadcrumbs.php';
			require_once ANSPRESS_DIR . 'widgets/leaderboard.php';

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
		}

		/**
		 * Get specific AnsPress form.
		 *
		 * @param string $name Name of form.
		 * @return false|object
		 * @throws \Exception Throws when requested from does not exits.
		 * @since 4.1.0
		 * @since 4.2.0 Fixed: Only variable references should be returned by reference.
		 * @deprecated 5.0.0
		 */
		public function &get_form( $name ) {
			_deprecated_function( __METHOD__, '5.0.0' );

			$name = preg_replace( '/^form_/i', '', $name );

			if ( $this->form_exists( $name ) ) {
				return $this->forms[ $name ];
			}

			throw new \Exception(
				sprintf(
					// translators: %s contains name of the form requested.
					esc_html__( 'Requested form: %s is not registered .', 'anspress-question-answer' ),
					esc_html( $name )
				)
			);
		}

		/**
		 * Check if a form exists in AnsPress, if not then tries to register.
		 *
		 * @param string $name Name of form.
		 * @return boolean
		 * @since 4.1.0
		 * @deprecated 5.0.0
		 */
		public function form_exists( $name ) {
			_deprecated_function( __METHOD__, '5.0.0' );

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
			 * @deprecated 5.0.0
			 */
			$args = apply_filters( 'ap_form_' . $name, null );

			if ( ! is_null( $args ) && ! empty( $args ) ) {
				$this->forms[ $name ] = new AnsPress\Form( 'form_' . $name, $args );

				return true;
			}

			return false;
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

require_once __DIR__ . '/includes/class/roles-cap.php';
require_once __DIR__ . '/includes/class/class-singleton.php';

/**
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
function anspress_activation() {
	require_once __DIR__ . '/activate.php';
	\AP_Activate::get_instance();
}
register_activation_hook( __FILE__, 'anspress_activation' );

// Version 5.0.0 of AnsPress introduced a new way of handling services.

require_once __DIR__ . '/src/backend/autoloader.php';

/**
 * Hook before AnsPress is loaded.
 */
do_action( 'anspress/pre_load' );

/**
 * Load AnsPress.
 */
add_action(
	'plugins_loaded',
	function () {
		$container = new AnsPress\Classes\Container();

		$instnace = Plugin::make(
			ANSPRESS_PLUGIN_FILE,
			ANSPRESS_PLUGIN_VERSION,
			ANSPRESS_DB_VERSION,
			'8.1',
			'6.5',
			$container
		);

		// Register auth policies.
		$instnace->getContainer()->set(
			Auth::class,
			fn() => new Auth(
				array(
					VotePolicy::class,
					SubscriberPolicy::class,
					CommentPolicy::class,
					AnswerPolicy::class,
					QuestionPolicy::class,
				)
			)
		);

		$instnace->registerModules();

		( new Router( $container ) )->register();
	}
);

anspress();
