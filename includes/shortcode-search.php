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

class AnsPress_Search_Shortcode {

	/**
	 * Output for anspress_search shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public static function anspress_search($atts, $content = ''){
		global $questions;
		
		$questions = new Question_Query();
		ob_start();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			if($questions->have_posts())
				include ap_get_theme_location('base.php');
			else
				_e('No questions match your search criteria', 'ap');

			wp_reset_postdata();
		echo '</div>';
		return ob_get_clean();
	}

	
}
