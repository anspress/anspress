<?php
/**
 * The WordPress Question and Answer Plugin.
 *
 * The most advance community question and answer system for WordPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 WP3.in & Rahul Aryan
 * @license   GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link      http://anspress.io
 *
 * @wordpress-plugin
 * Plugin Name:       AnsPress
 * Plugin URI:        http://anspress.io
 * Description:       The most advance community question and answer system for WordPress
 * Donate link: https://www.paypal.com/cgi-bin/webscr?business=support@anspress.io&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
 * Version:           2.1.2
 * Author:            Rahul Aryan
 * Author URI:        http://anspress.io
 * Text Domain:       ap
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

if (!class_exists('AnsPress')) {

    /**
     * Main AnsPress class
     * @package AnsPress
     */
    class AnsPress
    {

        private $_plugin_version = '2.1.2';

        private $_plugin_path;

        private $_plugin_url;

        private $_text_domain = 'ap';

        public static $instance = null;

        public $anspress_actions;
        
        public $anspress_ajax;

        public $pages;
        public $user_pages;
        public $users;
        public $menu;
        public $questions;
        public $answers;
        public $form;

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
        public $anspress_reputation;
        public $anspress_bp;
        public $anspress_users;


        /**
         * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
         * @return instance
         */
        public static function instance()
        {
            if (! isset(self::$instance) && ! (self::$instance instanceof AnsPress)) {
                self::$instance = new AnsPress();
                self::$instance->_setup_constants();
                
                add_action('init', array( self::$instance, 'load_textdomain' ));
                add_action('bp_include', array( self::$instance, 'bp_include' ));

                global $ap_classes;
                $ap_classes = array();

                self::$instance->includes();

                self::$instance->anspress_forms              = new AnsPress_Process_Form();
                self::$instance->anspress_actions            = new AnsPress_Actions();
                self::$instance->anspress_ajax               = new AnsPress_Ajax();
                self::$instance->anspress_query_filter       = new AnsPress_Query_Filter();
                self::$instance->anspress_theme              = new AnsPress_Theme();
                self::$instance->anspress_cpt                = new AnsPress_PostTypes();
                self::$instance->anspress_reputation         = new AnsPress_Reputation();
                self::$instance->anspress_users              = new AnsPress_User();

                /**
                 * ACTION: anspress_loaded
                 * Hooks for extension to load their codes after AnsPress is leaded
                 */
                do_action('anspress_loaded');
            }

            return self::$instance;
        }

         /**
          * Setup plugin constants
          *
          * @access private
          * @since  2.0.1
          * @return void
          */
         private function _setup_constants()
         {
             if (!defined('AP_VERSION')) {
                 define('AP_VERSION', $this->_plugin_version);
             }

             if (!defined('AP_DB_VERSION')) {
                 define('AP_DB_VERSION', '11');
             }

             if (!defined('DS')) {
                 define('DS', DIRECTORY_SEPARATOR);
             }

             if (!defined('ANSPRESS_DIR')) {
                 define('ANSPRESS_DIR', plugin_dir_path(__FILE__));
             }

             if (!defined('ANSPRESS_URL')) {
                 define('ANSPRESS_URL', plugin_dir_url(__FILE__));
             }

             if (!defined('ANSPRESS_WIDGET_DIR')) {
                 define('ANSPRESS_WIDGET_DIR', ANSPRESS_DIR.'widgets'.DS);
             }

             if (!defined('ANSPRESS_ADDON_DIR')) {
                 define('ANSPRESS_ADDON_DIR', ANSPRESS_DIR.'addons'.DS);
             }

             if (!defined('ANSPRESS_ADDON_URL')) {
                 define('ANSPRESS_ADDON_URL', ANSPRESS_URL.'addons/');
             }

             if (!defined('ANSPRESS_THEME_DIR')) {
                 define('ANSPRESS_THEME_DIR', plugin_dir_path(__FILE__).'theme');
             }

             if (!defined('ANSPRESS_THEME_URL')) {
                 define('ANSPRESS_THEME_URL', plugin_dir_url(__FILE__).'theme');
             }

             if (!defined('ANSPRESS_VOTE_META')) {
                 define('ANSPRESS_VOTE_META', '_ap_vote');
             }

             if (!defined('ANSPRESS_SUBSCRIBER_META')) {
                 define('ANSPRESS_SUBSCRIBER_META', '_ap_subscriber');
             }

             if (!defined('ANSPRESS_CLOSE_META')) {
                 define('ANSPRESS_CLOSE_META', '_ap_close');
             }

             if (!defined('ANSPRESS_FLAG_META')) {
                 define('ANSPRESS_FLAG_META', '_ap_flag');
             }

             if (!defined('ANSPRESS_VIEW_META')) {
                 define('ANSPRESS_VIEW_META', '_views');
             }

             if (!defined('ANSPRESS_UPDATED_META')) {
                 define('ANSPRESS_UPDATED_META', '_ap_updated');
             }

             if (!defined('ANSPRESS_ANS_META')) {
                 define('ANSPRESS_ANS_META', '_ap_answers');
             }

             if (!defined('ANSPRESS_SELECTED_META')) {
                 define('ANSPRESS_SELECTED_META', '_ap_selected');
             }

             if (!defined('ANSPRESS_BEST_META')) {
                 define('ANSPRESS_BEST_META', '_ap_best_answer');
             }

             if (!defined('ANSPRESS_PARTI_META')) {
                 define('ANSPRESS_PARTI_META', '_ap_participants');
             }
         }

        /**
         * Include required files
         *
         * @access private
         * @since 2.0.1
         * @return void
         */
        private function includes()
        {
            global $ap_options;

            require_once ANSPRESS_DIR.'includes/options.php';
            require_once ANSPRESS_DIR.'activate.php';
            require_once ANSPRESS_DIR.'includes/functions.php';
            require_once ANSPRESS_DIR.'includes/actions.php';
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
            require_once ANSPRESS_DIR.'includes/history.php';
            require_once ANSPRESS_DIR.'includes/shortcode-basepage.php';
            require_once ANSPRESS_DIR.'includes/common-pages.php';
            require_once ANSPRESS_DIR.'includes/class-form.php';
            require_once ANSPRESS_DIR.'includes/class-validation.php';
            require_once ANSPRESS_DIR.'includes/process-form.php';
            require_once ANSPRESS_DIR.'includes/ask-form.php';
            require_once ANSPRESS_DIR.'includes/answer-form.php';
            require_once ANSPRESS_DIR.'widgets/search.php';
            require_once ANSPRESS_DIR.'widgets/subscribe.php';
            require_once ANSPRESS_DIR.'widgets/participants.php';
            require_once ANSPRESS_DIR.'widgets/question_stats.php';
            require_once ANSPRESS_DIR.'widgets/related_questions.php';
            require_once ANSPRESS_DIR.'widgets/categories.php';
            require_once ANSPRESS_DIR.'widgets/questions.php';
            require_once ANSPRESS_DIR.'includes/rewrite.php';            
            require_once ANSPRESS_DIR.'includes/reputation.php';            
            require_once ANSPRESS_DIR.'vendor/autoload.php';
            require_once ANSPRESS_DIR.'includes/requirements.php';
            require_once ANSPRESS_DIR.'includes/class-user.php';
            require_once ANSPRESS_DIR.'includes/user.php';
            require_once ANSPRESS_DIR.'includes/users-loop.php';
            require_once ANSPRESS_DIR.'includes/deprecated.php';
            require_once ANSPRESS_DIR.'includes/user-fields.php';
             
        }

        /**
         * Load translations
         *
         * @access private
         * @since 2.0.1
         * @return void
         */
        public function load_textdomain()
        {
            load_plugin_textdomain($this->_text_domain, false, dirname(plugin_basename(__FILE__)).'/languages/');
        }

        public function bp_include()
        {
            require_once ANSPRESS_DIR.'includes/bp.php';
            self::$instance->anspress_bp = new AnsPress_BP;
        }

    }
}

function anspress()
{
    return AnsPress::instance();
}

anspress();

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 */

if (is_admin()) {
    require_once plugin_dir_path(__FILE__).'admin/anspress-admin.php';
    add_action('plugins_loaded', array( 'AnsPress_Admin', 'get_instance' ));
}


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */

register_activation_hook(__FILE__, 'anspress_activate');

register_uninstall_hook(__FILE__, 'anspress_uninstall');
function anspress_uninstall()
{

    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    check_admin_referer( 'bulk-plugins' );
    

    if(!ap_opt('db_cleanup'))
        return;

    global $wpdb;

    // remove question and answer cpt
    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'question'");
    $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'answer'");

    // remove meta table
    $meta_table = $wpdb->prefix."ap_meta";
    $wpdb->query("DROP TABLE IF EXISTS $meta_table");

    //remove option
    delete_option( 'anspress_opt' );
    delete_option( 'ap_reputation' );

    //Remove user roles
    AP_Roles::remove_roles();
}

add_action('plugins_loaded', array( 'anspress_vote', 'get_instance' ));
add_action('plugins_loaded', array( 'anspress_view', 'get_instance' ));


