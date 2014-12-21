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

class AnsPress_Questions_Shortcode {

	/**
	 * Output for anspress_questions shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public function anspress_questions($atts, $content = ''){

		$question_args = ap_base_page_main_query();
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		$question = new WP_Query( $question_args );

		echo '<div class="ap-container">';
			
			/**
			 * Action is firered before loading AnsPress body.
			 */
			do_action('ap_before');
			
			include ap_get_theme_location('base.php');

		echo '</div>';

	}

	
}
