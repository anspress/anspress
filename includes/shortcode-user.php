<?php
/**
 * AnsPress user shortcode
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

class AnsPress_User_Shortcode {

	/**
	 * Output for anspress_user shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public static function anspress_user($atts, $content = ''){
		global $user_pages, $ap_current_user_meta, $ap_user, $ap_user_data;

		$user_id 		= ap_user_page_user_id();
		$user_page 		= sanitize_text_field(get_query_var('user_page'));
		$user_page 		= $user_page ? $user_page : 'profile';

		$ap_user 		= get_userdata( $user_id );
		$ap_user_data 	= $ap_user->data;
		$ap_current_user_meta = array_map(	'ap_meta_array_map', get_user_meta($user_id));
		
		ob_start();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			if(!empty($user_id))
				include ap_get_theme_location('user.php');
			else
				include ap_get_theme_location('no-user-found.php');

		echo '</div>';
		return ob_get_clean();

	}

	
}
