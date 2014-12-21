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

class AP_Addons
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
		$addons = ap_read_addons();
		if(!empty($addons))
			foreach($addons as $addon){
				$include = ANSPRESS_ADDON_DIR.$addon['folder']. DS .$addon['file'];
				
				if(ap_is_addon_active($addon['name']) && file_exists($include))
					require_once( $include );
			}
    }

}

function ap_read_addons(){
	return ap_read_features('addon');
}

function ap_addon_counts(){
	return count(ap_read_addons());
}

function ap_is_addon_active($name){
	$option = get_option('ap_addons');
	if(isset($option[$name]) && $option[$name])
		return true;
	
	return false;
}