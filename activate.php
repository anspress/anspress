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


class anspress_activate {
	
	public static function add_roles(){
		// moderator and admin role
		
		$parti_cap = array(
			'read_question'         => true,
			'read_answer'			=> true,
			
			'new_question'			=> true,
			'new_answer'			=> true,
			'new_comment'			=> true,
			
			'edit_question'			=> true,
			'edit_answer'			=> true,
			'edit_comment'			=> true,
			
			'hide_question'			=> true,
			'hide_answer'			=> true,
			'delete_comment'		=> true,
			
			'vote_up'				=> true,
			'vote_down'				=> true,
			'vote_flag'				=> true,
			'vote_close'			=> true,
			
			'upload_cover'			=> true,
		);
		
		$editor_cap = array(
			'read_question'         => true,
			'read_answer'			=> true,
			
			'new_question'			=> true,
			'new_answer'			=> true,
			'new_comment'			=> true,
			
			'edit_question'			=> true,
			'edit_answer'			=> true,
			'edit_comment'			=> true,
			
			'delete_question'		=> true,
			'delete_answer'			=> true,
			'delete_comment'		=> true,
			
			'vote_up'				=> true,
			'vote_down'				=> true,
			'vote_flag'				=> true,
			'vote_close'			=> true,
			
			'edit_others_question'	=> true,
			'edit_others_answer'	=> true,
			'edit_others_comment'	=> true,
			
			'upload_cover'			=> true,
		);
		
		$mod_cap = array(
			'read_question'         => true,
			'read_answer'			=> true,
			
			'new_question'			=> true,
			'new_answer'			=> true,
			'new_comment'			=> true,
			
			'edit_question'			=> true,
			'edit_answer'			=> true,
			'edit_comment'			=> true,
			
			'delete_question'		=> true,
			'delete_answer'			=> true,
			'delete_comment'		=> true,
			
			'vote_up'				=> true,
			'vote_down'				=> true,
			'vote_flag'				=> true,
			'vote_close'			=> true,
			
			'edit_others_question'	=> true,
			'edit_others_answer'	=> true,
			'edit_others_comment'	=> true,
			
			'hide_others_question'	=> true,
			'hide_others_answer'	=> true,
			'hide_others_comment'	=> true,
			
			'delete_others_question'	=> true,
			'delete_others_answer'		=> true,
			'delete_others_comment'		=> true,
			
			'change_label'			=> true,
			
			'upload_cover'			=> true,
		);
		
		add_role('ap_participant',	__( 'Participant', 'ap' ), $parti_cap);
		add_role('ap_editor', __( 'Editor', 'ap' ), $editor_cap);
		add_role('ap_moderator', __( 'Moderator', 'ap' ), $mod_cap);
		
		// add capability to existing roles
		$roles = array('administrator', 'subscriber');
		$roles_obj = new WP_Roles();
		
		foreach ($roles as $role_name) {
			if($role_name == 'administrator')
				foreach ($mod_cap as $k => $grant){
					$roles_obj->add_cap($role_name, $k ); 				
				}
			else
				foreach ($parti_cap as $k => $grant){
					$roles_obj->add_cap($role_name, $k ); 				
				}
		}
	}
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		global $wpdb;
		
		anspress_activate::add_roles();
		// create base page
		if(!get_option('ap_base_page_created') || !get_post(get_option('ap_base_page_created'))){
			global $user_ID;
			$post = array();
			$post['post_type']    = 'page';
			$post['post_content'] = '[anspress]';
			$post['post_author']  = null;
			$post['post_status']  = 'publish';
			$post['post_title']   = 'AnsPress';
			$postid = wp_insert_post ($post);
			
			if($postid){
				update_option('ap_base_page_created', $postid);	
				$post = get_post($postid);
				ap_opt('base_page_slug', $post->post_name);
				ap_opt('base_page', $postid);
			}
			
			
		}
		
		
		// create table
		if( get_option ('ap_db_version') != AP_DB_VERSION ) {	
		
			if ( !empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;
					
			$sql = array();
			
			$sql[] = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."ap_meta` (
					  `apmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
					  `apmeta_userid` bigint(20) DEFAULT NULL,
					  `apmeta_type` varchar(256) DEFAULT NULL,
					  `apmeta_actionid` bigint(20) DEFAULT NULL,
					  `apmeta_value` text,
					  `apmeta_param` LONGTEXT DEFAULT NULL,
					  `apmeta_date` timestamp NULL DEFAULT NULL,
					  PRIMARY KEY (`apmeta_id`)
					)".$charset_collate.";";
			
			$sql[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix ."ap_messages (
					`message_id` bigint(20) NOT NULL auto_increment,
					`message_conversation` bigint(20) DEFAULT NULL,
					`message_content` text NOT NULL,
					`message_sender` bigint(20) NOT NULL,
					`message_date` datetime NOT NULL,
					`message_read` tinyint(1) NOT NULL,
					PRIMARY KEY (`message_id`)
				  )".$charset_collate.";";
				  
			$sql[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix ."ap_relations (
					`r_id` bigint(20) NOT NULL auto_increment,
					`r_type` varchar(256) DEFAULT NULL,
					`r_relation` bigint(20) NOT NULL,
					PRIMARY KEY (`r_id`)
				  )".$charset_collate.";";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta ($sql);
			
			update_option ('ap_db_version', AP_DB_VERSION);
		}

		
		if(!get_option('anspress_opt'))
			update_option('anspress_opt', ap_default_options());
		else
			update_option('anspress_opt', get_option('anspress_opt') + ap_default_options());
			
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}
		
		add_option('ap_flush', true); 
		flush_rewrite_rules( false );
	}
	
	/**
	 * Fired for each blog when the plugin is activated.
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}
	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}
	
	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

}
