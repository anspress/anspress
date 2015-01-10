<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * The most advance community question and answer system for WordPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 WP3.in & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://wp3.in
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           2.0.0-alpha
 * Author:            Rahul Aryan
 * Author URI:        http://wp3.in
 * Text Domain:       ap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if(!class_exists('AnsPress')):
	
	class AnsPress{
		
		private $plugin_version = '2.0.0-alpha';
		
		private $plugin_path;
		
		private $plugin_url;
		
		private $text_domain = 'ap';

		static $instance = null;

		public $anspress_actions;
		public $anspress_ajax;

		/**
		 * Filter object
		 * @var object
		 */
		public $anspress_query_filter;

		/**
		 * Theme object
		 * @var object
		 * @since 2.0.1
		 */
		public $anspress_theme;

		/**
		 * Post type object
		 * @var object
		 * @since 2.0.1
		 */
		public $anspress_cpt;
		

		public $anspress_forms;

		
		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AnsPress ) ) {
				self::$instance = new AnsPress;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				global $ap_classes;
				$ap_classes = array();

				self::$instance->includes();

				self::$instance->anspress_forms      		= new AnsPress_Process_Form();
				self::$instance->anspress_actions      		= new AnsPress_Actions();
				self::$instance->anspress_ajax      		= new AnsPress_Ajax();
				self::$instance->anspress_query_filter      = new AnsPress_Query_Filter();
				self::$instance->anspress_theme      		= new AnsPress_Theme();
				self::$instance->anspress_cpt      			= new AnsPress_PostTypes();

				

			}
			return self::$instance;
		}
		
		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 2.0.1
		 * @return void
		 */		 
		 private function setup_constants(){
			if (!defined('AP_VERSION'))
				define('AP_VERSION', '1.4.3');
			
			if (!defined('AP_DB_VERSION'))
				define('AP_DB_VERSION', '10');
			
			if (!defined('DS'))
				define('DS', DIRECTORY_SEPARATOR);
			
			if (!defined('ANSPRESS_DIR'))	
				define('ANSPRESS_DIR', plugin_dir_path( __FILE__ ));
			
			if (!defined('ANSPRESS_URL'))	
				define('ANSPRESS_URL', plugin_dir_url( __FILE__ ));
			
			if (!defined('ANSPRESS_WIDGET_DIR'))	
				define('ANSPRESS_WIDGET_DIR', ANSPRESS_DIR.'widgets'.DS);
			
			if (!defined('ANSPRESS_ADDON_DIR'))	
				define('ANSPRESS_ADDON_DIR', ANSPRESS_DIR.'addons'.DS);
			
			if (!defined('ANSPRESS_ADDON_URL'))	
				define('ANSPRESS_ADDON_URL', ANSPRESS_URL.'addons/');
			
			if (!defined('ANSPRESS_THEME_DIR'))
				define('ANSPRESS_THEME_DIR', plugin_dir_path( __FILE__ ).'theme');
			
			if (!defined('ANSPRESS_THEME_URL'))	
				define('ANSPRESS_THEME_URL', plugin_dir_url( __FILE__ ).'theme');
			
				if (!defined('ANSPRESS_VOTE_META'))
				define('ANSPRESS_VOTE_META', '_ap_vote');
			
			if (!defined('ANSPRESS_SUBSCRIBER_META'))	
				define('ANSPRESS_SUBSCRIBER_META', '_ap_subscriber');
			
			if (!defined('ANSPRESS_CLOSE_META'))	
				define('ANSPRESS_CLOSE_META', '_ap_close');
			
			if (!defined('ANSPRESS_FLAG_META'))	
				define('ANSPRESS_FLAG_META', '_ap_flag');
			
			if (!defined('ANSPRESS_VIEW_META'))		
				define('ANSPRESS_VIEW_META', '_views');
			
			if (!defined('ANSPRESS_UPDATED_META'))	
				define('ANSPRESS_UPDATED_META', '_ap_updated');
			
			if (!defined('ANSPRESS_ANS_META'))	
				define('ANSPRESS_ANS_META', '_ap_answers');
			
			if (!defined('ANSPRESS_SELECTED_META'))			
				define('ANSPRESS_SELECTED_META', '_ap_selected');
			
			if (!defined('ANSPRESS_BEST_META'))		
				define('ANSPRESS_BEST_META', '_ap_best_answer');
			
			if (!defined('ANSPRESS_PARTI_META'))		
				define('ANSPRESS_PARTI_META', '_ap_participants'); 
			
			if (!defined('AP_FOLLOWERS_META'))
				define('AP_FOLLOWERS_META', '_ap_followers');
			
			if (!defined('AP_FOLLOWING_META'))	
				define('AP_FOLLOWING_META', '_ap_following'); 
		 }
		 
		 /**
		 * Include required files
		 *
		 * @access private
		 * @since 2.0.1
		 * @return void
		 */
		private function includes() {
			global $ap_options;
			
			require_once( ANSPRESS_DIR . 'includes/options.php' );
			require_once( ANSPRESS_DIR . 'activate.php' );
			
			require_once( ANSPRESS_DIR . 'includes/functions.php' );
			require_once( ANSPRESS_DIR . 'includes/actions.php' );
			require_once( ANSPRESS_DIR . 'includes/ajax.php' );

			require_once( ANSPRESS_DIR . 'includes/class-roles-cap.php' );
			require_once( ANSPRESS_DIR . 'includes/class-question_query.php' );
			require_once( ANSPRESS_DIR . 'includes/class-answer_query.php' );
			require_once( ANSPRESS_DIR . 'includes/post_types.php' );

			require_once( ANSPRESS_DIR . 'includes/events.php' );
			require_once( ANSPRESS_DIR . 'includes/query_filter.php' );
			require_once( ANSPRESS_DIR . 'includes/post_status.php' );
			
			require_once( ANSPRESS_DIR . 'includes/meta.php' );
			require_once( ANSPRESS_DIR . 'includes/vote.php' );
			require_once( ANSPRESS_DIR . 'includes/view.php' );
			require_once( ANSPRESS_DIR . 'includes/theme.php' );
			require_once( ANSPRESS_DIR . 'includes/main.php' );
			require_once( ANSPRESS_DIR . 'includes/form.php' );
			
			require_once( ANSPRESS_DIR . 'includes/basepage.php' );
			require_once( ANSPRESS_DIR . 'includes/participants.php' );
			require_once( ANSPRESS_DIR . 'includes/labels.php' );
			require_once( ANSPRESS_DIR . 'includes/user.php' );
			require_once( ANSPRESS_DIR . 'includes/ranks.php' );
			require_once( ANSPRESS_DIR . 'includes/badges.php' );			
			require_once( ANSPRESS_DIR . 'includes/points.php' );
			require_once( ANSPRESS_DIR . 'includes/history.php' );
			
			require_once( ANSPRESS_DIR . 'includes/widgets.php' );
			require_once( ANSPRESS_DIR . 'includes/image_resize.php' );

			require_once( ANSPRESS_DIR . 'includes/shortcode-questions.php' );
			require_once( ANSPRESS_DIR . 'includes/shortcode-user.php' );
			require_once( ANSPRESS_DIR . 'includes/shortcode-ask.php' );
			require_once( ANSPRESS_DIR . 'includes/shortcode-edit.php' );

			require_once( ANSPRESS_DIR . 'includes/user-page-profile.php' );
			require_once( ANSPRESS_DIR . 'includes/user-page-questions.php' );
			require_once( ANSPRESS_DIR . 'includes/user-page-answers.php' );
			require_once( ANSPRESS_DIR . 'includes/user-page-favorites.php' );
			require_once( ANSPRESS_DIR . 'includes/class-form.php' );
			require_once( ANSPRESS_DIR . 'includes/class-validation.php' );
			require_once( ANSPRESS_DIR . 'includes/process-form.php' );
			require_once( ANSPRESS_DIR . 'includes/ask-form.php' );
			require_once( ANSPRESS_DIR . 'includes/answer-form.php' );
		}
		
		/**
		 * Load translations
		 *
		 * @access private
		 * @since 2.0.1
		 * @return void
		 */
		public function load_textdomain(){
			load_plugin_textdomain( $this->text_domain, false, 'languages' );
		}
	
	}
	
endif;

function anspress(){
	/**
	 * ACTION: anspress_loaded
	 * Hooks for extension to load their codes after AnsPress is leaded
	 */
	do_action( 'anspress_loaded');
	AnsPress::instance();	
}

anspress();


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, 'anspress_activate'  );

register_deactivation_hook( __FILE__, array( 'anspress', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'anspress_main', 'get_instance' ) );

add_action( 'plugins_loaded', array( 'Ap_Meta', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_vote', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_view', 'get_instance' ) );
//add_action( 'plugins_loaded', array( 'anspress_form', 'get_instance' ) );

add_action( 'plugins_loaded', array( 'AP_Participents', 'get_instance' ) );
//add_action( 'plugins_loaded', array( 'AP_labels', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AnsPress_User', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Ranks', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Badges', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Points', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_History', 'get_instance' ) );

add_action( 'plugins_loaded', array( 'AP_Widgets', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 */

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/anspress-admin.php' );
	add_action( 'plugins_loaded', array( 'anspress_admin', 'get_instance' ) );

}
