<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * A most advance community question and answer system for WordPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 rahularyan
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://rahularyan.com
 * Description:       A most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           0.1.7
 * Author:            Rahul Aryan
 * Author URI:        http://rahularyan.com
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
define('AP_VERSION', '0.1.7');
define('AP_DB_VERSION', '3');

define('ANSPRESS_DIR', plugin_dir_path( __FILE__ ));
define('ANSPRESS_URL', plugin_dir_url( __FILE__ ));
define('ANSPRESS_THEME_DIR', plugin_dir_path( __FILE__ ).'theme');
define('ANSPRESS_THEME_URL', plugin_dir_url( __FILE__ ).'theme');
define('ANSPRESS_CAT_TAX', 'question_category');
define('ANSPRESS_TAG_TAX', 'question_tags');


define('ANSPRESS_VOTE_META', '_ap_vote');
define('ANSPRESS_FAV_META', '_ap_favourite');
define('ANSPRESS_CLOSE_META', '_ap_close');
define('ANSPRESS_FLAG_META', '_ap_flag');



/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'activate.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-posts.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-vote.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-view.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-theme.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-main.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-form.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-ajax.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-points.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/anspress-shortcodes.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook( __FILE__, array( 'anspress_activate', 'activate' ) );

register_deactivation_hook( __FILE__, array( 'anspress', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'anspress', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_posts', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_vote', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_view', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_form', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_theme', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_ajax', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_points', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'anspress_shortcodes', 'get_instance' ) );


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
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/anspress-admin.php' );
	add_action( 'plugins_loaded', array( 'anspress_admin', 'get_instance' ) );

}
