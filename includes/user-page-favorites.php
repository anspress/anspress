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

class AnsPress_User_Page_Favorites {

	/**
	 * Output for profile page
	 * @since 2.0.1
	 */
	public static function output(){
		global $questions;
		
		$current_user = get_query_var('user');


		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			if(!empty($current_user))
				include ap_get_theme_location('user-profile.php');
			else
				include ap_get_theme_location('not-found.php');
		echo '</div>';

	}

	
}
