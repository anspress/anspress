<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * The most advance community question and answer system for WordPress
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 WP3.in & Rahul Aryan
 * @license   GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt *
 * @link      http://anspress.io
 * @package   AnsPress
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://anspress.io
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: 	  https://goo.gl/ffainr
 * Version:           2.4
 * Author:            Rahul Aryan
 * Author URI:        http://anspress.io
 * Text Domain:       ap
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: anspress/anspress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'AnsPress' ) ) {

	/**
	 * Main AnsPress class.
	 */
	class AnsPress
	{
		/**
		 * AnsPress version
		 * @var string
		 */
	    private $_plugin_version = '2.4';

	    /**
	     * Class instance
	     * @var object
	     */
	    public static $instance = null;

	    /**
	     * AnsPress hooks
	     * @var object Register all AnsPress hooks
	     */
	    public $anspress_hooks;

	    /**
	     * AnsPress ajax
	     * @var object Register all AnsPress ajax hooks
	     */
	    public $anspress_ajax;

	    /**
	     * AnsPress pages
	     * @var array All AnsPress pages
	     */
	    public $pages;

	    /**
	     * AnsPress users pages
	     * @var array AnsPress user pages
	     */
	    public $user_pages;

	    /**
	     * AnsPress user
	     * @var object AnsPress users loop
	     */
	    public $users;

	    /**
	     * AnsPress menu
	     * @var array AnsPress menu
	     */
	    public $menu;

	    /**
	     * AnsPress question loop
	     * @var object AnsPress question query loop
	     */
	    public $questions;

	    /**
	     * AnsPress answers loop
	     * @var object Answer query loop
	     */
	    public $answers;

	    /**
	     * AnsPress form
	     * @var object AnsPress form
	     */
	    public $form;

	    /**
	     * AnsPress reputation
	     * @var object
	     */
	    public $reputations;

		/**
		 * The array of actions registered with WordPress.
		 * @since    1.0.0
		 * @var array The actions registered with WordPress to fire when the plugin loads.
		 */
		protected $actions;

		/**
		 * The array of filters registered with WordPress.
		 * @since    1.0.0
		 * @var array The filters registered with WordPress to fire when the plugin loads.
		 */
		protected $filters;

		/**
		 * Filter object.
		 * @var object
		 */
		public $anspress_query_filter;

		/**
		 * Theme object.
		 * @var object
		 * @since 2.0.1
		 */
		public $anspress_theme;

		/**
		 * Post type object.
		 * @var object
		 * @since 2.0.1
		 */
		public $anspress_cpt;

		/**
		 * AnsPress form object
		 * @var object
		 */
	    public $anspress_forms;

	    public $anspress_reputation;
	    public $anspress_bp;
	    public $third_party;
	    public $common_pages;
	    public $post_status;
	    public $users_class;
	    public $rewrite_class;
	    public $history_class;
	    public $subscriber_hooks;
	    public $mention_hooks;

		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 *
		 * @return instance
		 */
		public static function instance() {

		    if ( ! isset( self::$instance ) && ! (self::$instance instanceof self) ) {
		        self::$instance = new self();
		        self::$instance->setup_constants();
		        self::$instance->actions = array();
		        self::$instance->filters = array();

		        add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

		        add_action( 'bp_loaded', array( self::$instance, 'bp_include' ) );

		        global $ap_classes;
		        $ap_classes = array();

		        self::$instance->includes();

		        self::$instance->ajax_hooks();
		        self::$instance->site_include();

		        self::$instance->anspress_forms 		= new AnsPress_Process_Form();
		        self::$instance->anspress_query_filter 	= new AnsPress_Query_Filter();
		        self::$instance->anspress_cpt 			= new AnsPress_PostTypes();
		        self::$instance->anspress_reputation 	= new AP_Reputation();

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
		 * @since  2.0.1
		 */
		private function setup_constants() {

		    $constants = array(
				'DS' 						=> DIRECTORY_SEPARATOR,
				'AP_VERSION' 				=> $this->_plugin_version,
				'AP_DB_VERSION' 			=> 18,
				'ANSPRESS_DIR' 				=> plugin_dir_path( __FILE__ ),
				'ANSPRESS_URL' 				=> plugin_dir_url( __FILE__ ),
				'ANSPRESS_WIDGET_DIR' 		=> plugin_dir_path( __FILE__ ).'widgets'.DIRECTORY_SEPARATOR,
				'ANSPRESS_THEME_DIR' 		=> plugin_dir_path( __FILE__ ).'theme',
				'ANSPRESS_THEME_URL' 		=> plugin_dir_url( __FILE__ ).'theme',
				'ANSPRESS_VOTE_META' 		=> '_ap_vote',
				'ANSPRESS_SUBSCRIBER_META' 	=> '_ap_subscriber',
				'ANSPRESS_CLOSE_META' 		=> '_ap_close',
				'ANSPRESS_FLAG_META' 		=> '_ap_flag',
				'ANSPRESS_VIEW_META' 		=> '_views',
				'ANSPRESS_UPDATED_META' 	=> '_ap_updated',
				'ANSPRESS_ANS_META' 		=> '_ap_answers',
				'ANSPRESS_SELECTED_META' 	=> '_ap_selected',
				'ANSPRESS_BEST_META' 		=> '_ap_best_answer',
				'ANSPRESS_PARTI_META' 		=> '_ap_participants',
			);

		    foreach ( $constants as $k => $val ) {
		        if ( ! defined( $k ) ) {
		            define( $k, $val );
		        }
		    }
		}

		/**
		 * Include required files.
		 * @since 2.0.1
		 */
		private function includes() {

		    global $ap_options;

		    require_once ANSPRESS_DIR.'includes/options.php';
		    require_once ANSPRESS_DIR.'activate.php';
		    require_once ANSPRESS_DIR.'includes/functions.php';
		    require_once ANSPRESS_DIR.'includes/hooks.php';
		    require_once ANSPRESS_DIR.'includes/ajax.php';
		    require_once ANSPRESS_DIR.'includes/class-roles-cap.php';
		    require_once ANSPRESS_DIR.'includes/question-loop.php';
		    require_once ANSPRESS_DIR.'includes/answer-loop.php';
		    require_once ANSPRESS_DIR.'includes/class-theme.php';
		    require_once ANSPRESS_DIR.'includes/post_types.php';
		    require_once ANSPRESS_DIR.'includes/query_filter.php';
		    require_once ANSPRESS_DIR.'includes/post_status.php';
		    require_once ANSPRESS_DIR.'includes/meta.php';
		    require_once ANSPRESS_DIR.'includes/vote.php';
		    require_once ANSPRESS_DIR.'includes/view.php';
		    require_once ANSPRESS_DIR.'includes/theme.php';
		    require_once ANSPRESS_DIR.'includes/form.php';
		    require_once ANSPRESS_DIR.'includes/participants.php';
		    require_once ANSPRESS_DIR.'includes/activity-hooks.php';
		    require_once ANSPRESS_DIR.'includes/shortcode-basepage.php';
		    require_once ANSPRESS_DIR.'includes/common-pages.php';
		    require_once ANSPRESS_DIR.'includes/class-form.php';
		    require_once ANSPRESS_DIR.'includes/class-validation.php';
		    require_once ANSPRESS_DIR.'includes/process-form.php';
		    require_once ANSPRESS_DIR.'includes/ask-form.php';
		    require_once ANSPRESS_DIR.'includes/answer-form.php';
		    require_once ANSPRESS_DIR.'widgets/search.php';
		    require_once ANSPRESS_DIR.'widgets/subscribe.php';
		    require_once ANSPRESS_DIR.'widgets/question_stats.php';
		    require_once ANSPRESS_DIR.'widgets/related_questions.php';
		    require_once ANSPRESS_DIR.'widgets/questions.php';
		    require_once ANSPRESS_DIR.'widgets/breadcrumbs.php';
		    require_once ANSPRESS_DIR.'widgets/followers.php';
		    require_once ANSPRESS_DIR.'widgets/user_notification.php';
		    require_once ANSPRESS_DIR.'widgets/users.php';
		    require_once ANSPRESS_DIR.'includes/rewrite.php';
		    require_once ANSPRESS_DIR.'includes/reputation.php';
		    require_once ANSPRESS_DIR.'vendor/autoload.php';
		    require_once ANSPRESS_DIR.'includes/class-user.php';
		    require_once ANSPRESS_DIR.'includes/user.php';
		    require_once ANSPRESS_DIR.'includes/users-loop.php';
		    require_once ANSPRESS_DIR.'includes/deprecated.php';
		    require_once ANSPRESS_DIR.'includes/user-fields.php';
		    require_once ANSPRESS_DIR.'includes/subscriber.php';
		    require_once ANSPRESS_DIR.'includes/follow.php';
		    require_once ANSPRESS_DIR.'includes/notification.php';
		    require_once ANSPRESS_DIR.'widgets/user.php';
		    require_once ANSPRESS_DIR.'widgets/ask-form.php';
		    require_once ANSPRESS_DIR.'includes/3rd-party.php';
		    require_once ANSPRESS_DIR.'includes/flag.php';
		    require_once ANSPRESS_DIR.'includes/activity.php';
		    require_once ANSPRESS_DIR.'includes/subscriber-hooks.php';
		    require_once ANSPRESS_DIR.'includes/shortcode-question.php';
		    require_once ANSPRESS_DIR.'includes/mention.php';
		}

		/**
		 * Load translations.
		 * @since 2.0.1
		 */
		public function load_textdomain() {
		    $locale = apply_filters( 'plugin_locale', get_locale(), 'ap' );
		    $loaded = load_textdomain( 'ap', trailingslashit( WP_LANG_DIR ).'ap'.'/'.'ap'.'-'.$locale.'.mo' );

		    if ( $loaded ) {
		        return $loaded;
		    } else {
		        load_plugin_textdomain( 'ap', false, basename( dirname( __FILE__ ) ).'/languages/' );
		    }
		}

		/**
		 * Register ajax hooks
		 */
		public function ajax_hooks() {
			// Load ajax hooks only if DOING_AJAX defined.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		    	$this->anspress_ajax = new AnsPress_Ajax( $this );
			}
		}

		/**
		 * Include all public classes
		 */
		public function site_include() {
		    self::$instance->anspress_hooks 	= new AnsPress_Hooks( $this );
		    self::$instance->anspress_theme 	= new AnsPress_Theme( $this );
		    self::$instance->common_pages 		= new AnsPress_Common_Pages( $this );
		    self::$instance->post_status 		= new AnsPress_Post_Status( $this );
		    self::$instance->users_class 		= new AnsPress_User( $this );
	    	self::$instance->rewrite_class 		= new AnsPress_Rewrite( $this );
	    	self::$instance->history_class 		= new AnsPress_Activity_Hook( $this );
	    	self::$instance->subscriber_hooks 	= new AnsPress_Subscriber_Hooks( $this );
	    	self::$instance->mention_hooks 		= new AP_Mentions_Hooks( $this );
		}

		/**
		 * Include BuddyPress hooks and files
		 */
	    public function bp_include() {
	        if ( ! class_exists( 'BuddyPress' ) ) {
	            return;
	        }

	        require_once ANSPRESS_DIR.'includes/bp.php';
	        self::$instance->anspress_bp = new AnsPress_BP();
	    }

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @since    2.4
		 *
		 * @param string            $hook          The name of the WordPress action that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the action is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 */
		public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
		    $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @since    2.4
		 *
		 * @param string            $hook          The name of the WordPress filter that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the filter is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 */
		public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
		    $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * A utility function that is used to register the actions and hooks into a single
		 * collection.
		 *
		 * @since    2.4
		 *
		 * @param array             $hooks         The collection of hooks that is being registered (that is, actions or filters).
		 * @param string            $hook          The name of the WordPress filter that is being registered.
		 * @param object            $component     A reference to the instance of the object on which the filter is defined.
		 * @param string            $callback      The name of the function definition on the $component.
		 * @param int      Optional $priority      The priority at which the function should be fired.
		 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
		 * @param integer $priority
		 * @param integer $accepted_args
		 *
		 * @return type The collection of actions and filters registered with WordPress.
		 */
		private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
		    $hooks[] = array(
				'hook' => $hook,
				'component' => $component,
				'callback' => $callback,
				'priority' => $priority,
				'accepted_args' => $accepted_args,
			);

		    return $hooks;
		}

		/**
		 * Register the filters and actions with WordPress.
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
 * @return object
 */
function anspress() {
	return AnsPress::instance();
}

anspress();

/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 */

if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ).'admin/anspress-admin.php';
	add_action( 'plugins_loaded', array( 'AnsPress_Admin', 'get_instance' ) );
}

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, 'anspress_activate' );

register_uninstall_hook( __FILE__, 'anspress_uninstall' );

/**
 * Plugin un-installation hook, called by WP while removing AnsPress
 */
function anspress_uninstall() {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	check_admin_referer( 'bulk-plugins' );

	if ( ! ap_opt( 'db_cleanup' ) ) {
		return;
	}

	global $wpdb;

	// remove question and answer cpt
	$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'question'" );
	$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'answer'" );

	// remove meta table
	$meta_table = $wpdb->prefix.'ap_meta';
	$wpdb->query( "DROP TABLE IF EXISTS $meta_table" );

	// remove option
	delete_option( 'anspress_opt' );
	delete_option( 'ap_reputation' );

	// Remove user roles
	AP_Roles::remove_roles();
}

add_action( 'plugins_loaded', array( 'anspress_view', 'get_instance' ) );

function ap_activation_redirect($plugin) {

	if ( $plugin == plugin_basename( __FILE__ ) ) {
		exit( wp_redirect( admin_url( 'admin.php?page=anspress_about' ) ) );
	}
}
add_action( 'activated_plugin', 'ap_activation_redirect' );
