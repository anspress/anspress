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

class anspress_ajax
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
		add_action('wp_ajax_ap_set_status', array($this, 'ap_set_status'));
    }
	
	public function ap_set_status(){
		if(!is_user_logged_in())
			die('not_logged_in');
		
		if(ap_user_can_change_status()){
			$args = explode('-', sanitize_text_field($_REQUEST['args']));
			$action = 'question-'.$args[0];	
			if(wp_verify_nonce( $args[1], $action )){		
				ap_set_question_status($args[0], $args[2]);
			}
		}
		//ap_change_status_btn_html($args[0]);
		die();		
	}
	
}
