<?php
/**
 * AnsPress user profile page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_User_Page_Profile {

	/**
	 * Output for profile page
	 * @since 2.0.1
	 */
	public static function output(){
		global $questions;
		
		$user_id = ap_user_page_user_id();


		echo '<div class="anspress-user-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			if(!empty($user_id))
				include ap_get_theme_location('user-profile.php');
			else
				include ap_get_theme_location('not-found.php');
		echo '</div>';

	}

	
}
