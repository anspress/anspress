<?php
/**
 * Installation and activation of anspress
 *
 * @package     AnsPress
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Register hooks that are fired when the plugin is activated.
 */
		
/**
 * Create base pages, add roles, add caps and create tables
 *
 * @since 2.0
 */
function anspress_activate( $network_wide ) {

	// add roles
	$ap_roles = new AP_Roles;
	$ap_roles->add_roles();
	
	
	global $wpdb;

	// create base page
	if(!get_option('ap_base_page_created') || !get_post(get_option('ap_base_page_created'))){
		global $user_ID;
		$post = array();
		$post['post_type']    = 'page';
		$post['post_content'] = '[anspress]';
		$post['post_author']  = null;
		$post['post_status']  = 'publish';
		$post['post_title']   = '[anspress]';
		$postid = wp_insert_post ($post);
		
		if($postid){
			update_option('ap_base_page_created', $postid);	
			$post = get_post($postid);
			ap_opt('base_page_slug', $post->post_name);
			ap_opt('base_page', $postid);
		}
		
		
	}
	
	if( get_option ('ap_version') != AP_VERSION ) {
		update_option('ap_installed', false);
		update_option('ap_version', AP_VERSION);
	}
	
	// create table
	if( get_option ('ap_db_version') != AP_DB_VERSION ) {	
	
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
		
		update_option ('ap_db_version', AP_DB_VERSION);
	}

	
	if(!get_option('anspress_opt'))
		update_option('anspress_opt', ap_default_options());
	else
		update_option('anspress_opt', get_option('anspress_opt') + ap_default_options());
		
	
	add_option('ap_flush', true); 
	flush_rewrite_rules( false );
}