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
 * @copyright 2014 Open-WP & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://wp3.in
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           1.4.3
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
		
		private $plugin_version = '1.4.3';
		
		private static $instance = null;
		
		private $plugin_path;
		
		private $plugin_url;
		
		private $text_domain = 'ap';


		/**
		 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AnsPress ) ) {
				self::$instance = new AnsPress;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
				/* self::$instance->roles      = new EDD_Roles();
				self::$instance->fees       = new EDD_Fees();
				self::$instance->api        = new EDD_API();
				self::$instance->session    = new EDD_Session();
				self::$instance->html       = new EDD_HTML_Elements();
				self::$instance->emails     = new EDD_Emails();
				self::$instance->email_tags = new EDD_Email_Template_Tags();
				self::$instance->customers  = new EDD_DB_Customers(); */
			}
			return self::$instance;
		}
		
		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 2.0
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
			
			if (!defined('ANSPRESS_CAT_TAX'))		
				define('ANSPRESS_CAT_TAX', 'question_category');
			
			if (!defined('ANSPRESS_TAG_TAX'))	
				define('ANSPRESS_TAG_TAX', 'question_tags');

			if (!defined('ANSPRESS_VOTE_META'))
				define('ANSPRESS_VOTE_META', '_ap_vote');
			
			if (!defined('ANSPRESS_FAV_META'))	
				define('ANSPRESS_FAV_META', '_ap_favorite');
			
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
		 * @since 2.0
		 * @return void
		 */
		private function includes() {
			global $ap_options;
			
			require_once( ANSPRESS_DIR . 'activate.php' );
			
			require_once( ANSPRESS_DIR . 'includes/functions.php' );
			require_once( ANSPRESS_DIR . 'includes/class-roles-cap.php' );
			require_once( ANSPRESS_DIR . 'includes/events.php' );
			require_once( ANSPRESS_DIR . 'includes/addons.php' );
			require_once( ANSPRESS_DIR . 'includes/posts.php' );
			require_once( ANSPRESS_DIR . 'includes/categories.php' );
			require_once( ANSPRESS_DIR . 'includes/tags.php' );
			require_once( ANSPRESS_DIR . 'includes/meta.php' );
			require_once( ANSPRESS_DIR . 'includes/vote.php' );
			require_once( ANSPRESS_DIR . 'includes/view.php' );
			require_once( ANSPRESS_DIR . 'includes/theme.php' );
			require_once( ANSPRESS_DIR . 'includes/main.php' );
			require_once( ANSPRESS_DIR . 'includes/form.php' );
			require_once( ANSPRESS_DIR . 'includes/ajax.php' );
			require_once( ANSPRESS_DIR . 'includes/basepage.php' );
			require_once( ANSPRESS_DIR . 'includes/participants.php' );
			require_once( ANSPRESS_DIR . 'includes/labels.php' );
			require_once( ANSPRESS_DIR . 'includes/user.php' );
			require_once( ANSPRESS_DIR . 'includes/ranks.php' );
			require_once( ANSPRESS_DIR . 'includes/badges.php' );
			
			require_once( ANSPRESS_DIR . 'includes/points.php' );
			require_once( ANSPRESS_DIR . 'includes/history.php' );
			require_once( ANSPRESS_DIR . 'includes/shortcodes.php' );
			require_once( ANSPRESS_DIR . 'includes/widgets.php' );
			require_once( ANSPRESS_DIR . 'includes/image_resize.php' );
		}
		
		/**
		 * Load translations
		 *
		 * @access private
		 * @since 2.0
		 * @return void
		 */
		public function load_textdomain(){
			load_plugin_textdomain( $this->text_domain, false, 'languages' );
		}
	
	}
	
endif;

function anspress(){
	AnsPress::instance();
}

anspress();


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, 'anspress_activate'  );

register_deactivation_hook( __FILE__, array( 'anspress', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'AP_Addons', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_main', 'get_instance' ) );

add_action( 'plugins_loaded', array( 'anspress_posts', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Categories', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Tags', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'Ap_Meta', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_vote', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_view', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_form', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_theme', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_ajax', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_BasePage', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Participents', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_labels', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_User', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Ranks', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Badges', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Points', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_History', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_ShortCodes', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Widgets', 'get_instance' ) );

$roless = new AP_Roles;
$roless->remove_roles();
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


