<?php
/**
 * AnsPress shortcodes
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Questions_Shortcode {

	/**
	 * Output for anspress_questions shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public static function anspress_questions($atts, $content = ''){
		global $questions;
		
		$questions = new Question_Query();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			include ap_get_theme_location('base.php');

			wp_reset_postdata();
		echo '</div>';

	}

	
}
