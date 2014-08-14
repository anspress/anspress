<?php
/*
	Name:Basic Email
	Description: Basic email notification
	Version:1.0
	Author: Rahul Aryan
	Author URI: http://open-wp.com
	Addon URI: http://open-wp.com/anspress
*/


class AP_Basic_Email_Addon
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
	
    }

}


AP_Basic_Email_Addon::get_instance();
