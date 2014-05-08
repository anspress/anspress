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
			'add_question'         	=> true,
			'add_answer'         	=> true,
			'edit_question'   		=> true,
			'edit_answer' 			=> true,
			'delete_question' 		=> true,
			'delete_answer' 		=> true,
			'add_comment' 			=> true,
			'edit_comment' 			=> true,
			'delete_comment' 		=> true,
			'cast_vote' 			=> true,
		);
		
		$mod_cap = array(
			'read_question'         => true,
			'add_question'         	=> true,
			'add_answer'         	=> true,
			'edit_question'   		=> true,
			'edit_answer' 			=> true,
			'delete_question' 		=> true,
			'delete_answer' 		=> true,
			'add_comment' 			=> true,
			'edit_comment' 			=> true,
			'delete_comment' 		=> true,
			'cast_vote' 			=> true,
			'mod_question' 			=> true,
			'mod_answer' 			=> true,
			'mod_comment' 			=> true,
		);
		
		add_role('participant',	__( 'Participant', 'ap' ), $parti_cap);
		add_role('moderator', __( 'Moderator', 'ap' ), $mod_cap);
		
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
			
			if($postid)
				update_option('ap_base_page_created', $postid);	
			
			
		}
		
		
		// create table
		if( get_option ('ap_db_version') != AP_DB_VERSION ) {	
		
			if ( !empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET ".$wpdb->charset;
					
			$sql = array();
			
			// table for voting
			$sql[] = "CREATE TABLE ".$wpdb->base_prefix."ap_vote (
						ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						userid bigint(20) NOT NULL,
						type varchar(256) NOT NULL,
						actionid bigint(20) NOT NULL,
						value tinyint(4) DEFAULT NULL,
						note varchar(800) DEFAULT NULL,
						voted_on timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						PRIMARY KEY (ID)
			) ".$charset_collate.";";			
			
			// table for points
			$sql[] = "CREATE TABLE ".$wpdb->base_prefix."ap_points (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				uid bigint(20) NOT NULL,
				type varchar(256) NOT NULL,
				data text NOT NULL,
				points bigint(20) NOT NULL,
				points_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY id (id)
			) ".$charset_collate.";";
			
			// view table
			$sql[] = "CREATE TABLE ".$wpdb->base_prefix."ap_views( 
				id bigint(20) NOT NULL AUTO_INCREMENT,
				uid bigint(20) NOT NULL,
				data_id bigint(20) NOT NULL,
				type varchar(256) NOT NULL,
				ip_addres varchar( 60 ) NOT NULL,
				view int( 10 ) NOT NULL,
				view_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				UNIQUE KEY id (id)
			) ".$charset_collate.";";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta ($sql);
			
			update_option ('ap_db_version', AP_DB_VERSION);
		}

		
		if(!get_option('anspress_opt'))
			update_option('anspress_opt', anspress_admin::default_options());
		else
			update_option('anspress_opt', get_option('anspress_opt') + anspress_admin::default_options());
			
		
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
