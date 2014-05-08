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
		add_action( 'single_template', array($this, 'insert_views') );
	}

	public function insert_views($single_template){
		
		global $post;

		if($post->post_type == 'question' || $post->post_type == 'answer'){
			if(!ap_is_already_viewed(get_current_user_id(), $post->post_type, $post->ID))
				ap_insert_views($post->ID, $post->post_type);
		}
		
		return $single_template;
	}
}

function ap_insert_views($data_id, $type){
	global $wpdb;
	$wpdb->insert( 
		$wpdb->base_prefix.'ap_views', 
		array( 
			'uid' 		=> get_current_user_id(), 
			'data_id' 	=> $data_id, 
			'type' 		=> $type, 
			'ip_addres' => $_SERVER['REMOTE_ADDR'], 
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
	
	if($type == 'question' || $type == 'answer'){
		$view = ap_get_qa_views($data_id);
		update_post_meta( $data_id, '_views', apply_filters('ap_insert_views', $view + 1 ), true );
	}
}

function ap_get_qa_views($id){
	$views = get_post_meta( $id, '_views', true );
	$views = empty($views) ? 1 : $views;
	
	return apply_filters('ap_get_views', $views);
}

function ap_is_already_viewed($user_id, $type, $data_id){
	global $wpdb;
	$ip = $_SERVER['REMOTE_ADDR'];
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->base_prefix."ap_views WHERE uid = $user_id AND type = $type AND  data_id = $data_id ");
	return apply_filters('ap_is_already_viewed', $count);
}