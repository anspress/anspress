<?php
/**
 * AnsPress tag sortcode
 *
 * @package   AnsPress
 * @subpackage Tags for anspress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Tag_Shortcode {

	/**
	 * Output for anspress_tag shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public static function anspress_tag($atts, $content = ''){
		$category_id = get_query_var( 'question_tag');
		
		if(empty( $category_id )){
			echo '<div class="anspress-container">';
				/**
				 * ACTION: ap_before
				 * Action is fired before loading AnsPress body.
				 */
				do_action('ap_before');
				
				// include theme file
				include ap_get_theme_location('no-tags-found.php', CATEGORIES_FOR_ANSPRESS_DIR);
			echo '</div>';
			return;
		}

		global $question_tag, $ap_max_num_pages, $ap_per_page, $questions;

		$question_args['tax_query'] = array(		
			array(
				'taxonomy' => 'question_tag',
				'field' => 'id',
				'terms' => array( get_query_var( 'question_tag') )
			)
		);

		/**
		 * FILTER: ap_tag_shortcode_args
		 * Filter applied before getting question of current tag.
		 * @var array
		 * @since 1.0
		 */
		$question_args = apply_filters('ap_tag_shortcode_args', $question_args );

		$questions = new Question_Query( $question_args );
		$question_tag = $questions->get_queried_object();
		echo '<div class="anspress-container">';
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			include ap_get_theme_location('tag.php', TAGS_FOR_ANSPRESS_DIR);
		echo '</div>';
		wp_reset_postdata();
	}

	
}
