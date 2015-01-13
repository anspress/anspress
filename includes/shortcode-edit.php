<?php
/**
 * Edit form sortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Edit_Shortcode {

	/**
	 * Output for anspress_edit shortcode
	 * @param  $atts
	 * @param  string $content
	 * @since 2.0.1
	 */
	public static function anspress_edit($atts, $content = ''){
		$post_id = (int) sanitize_text_field( get_query_var( 'edit_post_id' ));
		
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');

			if( !ap_user_can_edit_question($post_id)){
				echo '<p>'.__('You don\'t have permission to access this page.', 'ap').'</p>';
				return;
			}else{
				global $editing_post;
				$editing_post = get_post($post_id);
				
				// include theme file
				include ap_get_theme_location('edit.php');
			}			

		echo '</div>';

	}

	
}
