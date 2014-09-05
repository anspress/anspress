<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * A most advance community question and answer system for WordPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://open-wp.com
 * @copyright 2014 Open-WP & Rahul Aryan
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://open-wp.com
 * Description:       A most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           1.0 PR9
 * Author:            Rahul Aryan
 * Author URI:        http://open-wp.com
 * Text Domain:       ap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// define	
define('AP_VERSION', '1.0 Pre Release 9');
define('AP_DB_VERSION', '10');

define('DS', DIRECTORY_SEPARATOR);
define('ANSPRESS_DIR', plugin_dir_path( __FILE__ ));
define('ANSPRESS_URL', plugin_dir_url( __FILE__ ));
define('ANSPRESS_WIDGET_DIR', ANSPRESS_DIR.'widgets'.DS);
define('ANSPRESS_ADDON_DIR', ANSPRESS_DIR.'addons'.DS);
define('ANSPRESS_ADDON_URL', ANSPRESS_URL.'addons/');
define('ANSPRESS_THEME_DIR', plugin_dir_path( __FILE__ ).'theme');
define('ANSPRESS_THEME_URL', plugin_dir_url( __FILE__ ).'theme');
define('ANSPRESS_CAT_TAX', 'question_category');
define('ANSPRESS_TAG_TAX', 'question_tags');


define('ANSPRESS_VOTE_META', '_ap_vote');
define('ANSPRESS_FAV_META', '_ap_favorite');
define('ANSPRESS_CLOSE_META', '_ap_close');
define('ANSPRESS_FLAG_META', '_ap_flag');
define('ANSPRESS_VIEW_META', '_views');
define('ANSPRESS_UPDATED_META', '_ap_updated');
define('ANSPRESS_ANS_META', '_ap_answers');
define('ANSPRESS_SELECTED_META', '_ap_selected');
define('ANSPRESS_BEST_META', '_ap_best_answer');
define('ANSPRESS_PARTI_META', '_ap_participants'); 

define('AP_FOLLOWERS_META', '_ap_followers'); 
define('AP_FOLLOWING_META', '_ap_following'); 

/* Load localization */
function ap_localization_setup() {
 load_plugin_textdomain('ap', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('after_setup_theme', 'ap_localization_setup'); 


/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-roles-cap.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-events.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-addons.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-posts.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-categories.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-tags.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-meta.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-vote.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-view.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-theme.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-main.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-form.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-ajax.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-shortcodes.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-participants.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-labels.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-user.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-ranks.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-badges.php' );
require_once( plugin_dir_path( __FILE__ ) . 'activate.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-points.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-history.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-messages.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-widgets.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, array( 'anspress_activate', 'activate' ) );

register_deactivation_hook( __FILE__, array( 'anspress', 'deactivate' ) );
add_action( 'plugins_loaded', array( 'AP_Addons', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Roles_Permission', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_posts', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Categories', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Tags', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'Ap_Meta', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_vote', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_view', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_form', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_theme', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_ajax', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_shortcodes', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Participents', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_labels', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_User', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Ranks', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Badges', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Points', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_History', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Messages', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'AP_Widgets', 'get_instance' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/anspress-admin.php' );
	add_action( 'plugins_loaded', array( 'anspress_admin', 'get_instance' ) );

}
