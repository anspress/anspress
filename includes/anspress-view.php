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


class anspress_view {

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
	private function __construct() {
		add_action( 'the_post', array($this, 'insert_views') );
	}

	public function insert_views($post){
		if(is_question() && $post->post_type == 'question'){
			
			if(!ap_is_already_viewed(get_current_user_id(), $post->ID))
				ap_insert_views($post->ID, $post->post_type);
		}
	}
}

function ap_insert_views($data_id, $type){
	if($type == 'question'){
		global $wpdb;
		$wpdb->insert( 
			$wpdb->base_prefix.'ap_views', 
			array( 
				'uid' 		=> get_current_user_id(), 
				'data_id' 	=> $data_id, 
				'type' 		=> $type, 
				'ip_addres' =>  $_SERVER['REMOTE_ADDR'], 
				'view' 		=> apply_filters('ap_insert_views', 1 )
			), 
			array( 
				'%d', 
				'%d', 
				'%s', 
				'%s', 
				'%d'
			) 
		);	
		$view = ap_get_views_db($data_id);
		update_post_meta( $data_id, ANSPRESS_VIEW_META, apply_filters('ap_insert_views', $view + 1 ));
	}
}

function ap_get_qa_views($id){	
	$views = get_post_meta( $id, ANSPRESS_VIEW_META, true );	
	$views = empty($views) ? 1 : $views;
	
	return apply_filters('ap_get_views', $views);
}

function ap_get_views_db($id){
	global $wpdb;
	return $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->base_prefix."ap_views WHERE data_id = $id ");
}

function ap_is_already_viewed($user_id, $data_id, $type ='question'){
	global $wpdb;
	$ip = $_SERVER['REMOTE_ADDR'];
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$wpdb->base_prefix."ap_views WHERE uid = %d AND type = %s AND data_id = %d", $user_id, $type, $data_id ));
	return apply_filters('ap_is_already_viewed', $count);
}