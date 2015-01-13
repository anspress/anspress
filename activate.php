<?php
/**
 * Installation and activation of anspress
 *
 * @package     AnsPress
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Register hooks that are fired when the plugin is activated.
 */
		

/**
 * Create base pages, add roles, add caps and create tables
 * @param $network_wide
 */
function anspress_activate( $network_wide ) {

	// add roles
	$ap_roles = new AP_Roles;
	$ap_roles->add_roles();
	
	
	global $wpdb;
	
	//TODO: EXTENSION - move category and tags page to extension
	$page_to_create = array(
		'questions' 			=> __('Questions', 'ap'), 
		'user' 					=> __('User', 'ap'),			
		'ask' 					=> __('Ask', 'ap'),			
		'edit_page' 			=> __('Edit', 'ap'),			
	);
	
	foreach($page_to_create as $k => $page_title){
		// create page
		
		// check if page already exists
		$page_id = ap_opt("{$k}_page_id");
		
		$post = get_post($page_id);
		
		if(!$post){
			
			$args['post_type']    		= "page";
			$args['post_content'] 		= "[anspress_{$k}]";
			$args['post_status']  		= "publish";
			$args['post_title']   		= $page_title;
			$args['comment_status']   	= 'closed';
			
			if($k != 'questions')
				$args['post_parent']   = ap_opt('questions_page_id');
			
			// now create post
			$new_page_id = wp_insert_post ($args);
		
			if($new_page_id){
				$page = get_post($new_page_id);
				ap_opt("{$k}_page_slug", $page->post_name);
				ap_opt("{$k}_page_id", $page->ID);
			}
		}
	}
	
	
	
	if( ap_opt ('ap_version') != AP_VERSION ) {
		ap_opt('ap_installed', false);
		ap_opt('ap_version', AP_VERSION);
	}
	
	/**
	 * Run DB quries only if AP_DB_VERSION does not match
	 */
	if( ap_opt ('ap_db_version') != AP_DB_VERSION ) {	
	
		if ( !empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;

		$meta_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."ap_meta` (
				  `apmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `apmeta_userid` bigint(20) DEFAULT NULL,
				  `apmeta_type` varchar(256) DEFAULT NULL,
				  `apmeta_actionid` bigint(20) DEFAULT NULL,
				  `apmeta_value` text,
				  `apmeta_param` LONGTEXT DEFAULT NULL,
				  `apmeta_date` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`apmeta_id`)
				)".$charset_collate.";";

		$message_table = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix ."ap_messages (
				`message_id` bigint(20) NOT NULL auto_increment,
				`message_content` text NOT NULL,
				`message_sender` bigint(20) NOT NULL,
				`message_conversation` bigint(20) NOT NULL,
				`message_date` datetime NOT NULL,
				`message_read` tinyint(1) NOT NULL,
				PRIMARY KEY (`message_id`)
			  )".$charset_collate.";";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta ($meta_table);
		dbDelta ($message_table);
		
		ap_opt ('ap_db_version', AP_DB_VERSION);
	}

	
	if(!get_option('anspress_opt'))
		update_option('anspress_opt', ap_default_options());
	else
		update_option('anspress_opt', get_option('anspress_opt') + ap_default_options());
		
	
	ap_opt('ap_flush', true); 
	flush_rewrite_rules( false );
}