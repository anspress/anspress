<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class AP_Widgets
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		require_once( ANSPRESS_WIDGET_DIR. 'search.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'quick-ask.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'categories.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'questions.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'users.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'related_questions.php' );
		require_once( ANSPRESS_WIDGET_DIR. 'post_discussion.php' );
		
    }
}

