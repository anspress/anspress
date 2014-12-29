<?php
/**
 * AnsPress tags sortcode
 *
 * @package   AnsPress
 * @subpackage Categories for anspress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Tags_Shortcode {

	/**
	 * Output for anspress_tags shortcode
	 * @param  $atts
	 * @param  string $content
	 */
	public static function anspress_tags($atts, $content = ''){
		global $question_tags, $ap_max_num_pages, $ap_per_page;

		$paged = get_query_var('paged') ? get_query_var('paged') : 1;
		$per_page    		= ap_opt('tag_per_page');
		$total_terms 		= wp_count_terms('question_tag'); 	
		$offset      		= $per_page * ( $paged - 1) ;
		$ap_max_num_pages 	= $total_terms / $per_page ;

		$tags_args = array(
			'parent' 		=> 0,
			'number'		=> $per_page,
			'offset'       	=> $offset,
			'hide_empty'    => false,
			'orderby'       => 'count',
			'order'         => 'DESC',
		);

		/**
		 * FILTER: ap_tags_shortcode_args
		 * Filter applied before getting categories.
		 * @var array
		 * @since 1.0
		 */
		$tags_args = apply_filters('ap_tags_shortcode_args', $tags_args );

		$question_tags = get_terms( 'question_tag' , $tags_args); 
		echo '<div class="anspress-container">';
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			include ap_get_theme_location('tags.php', TAGS_FOR_ANSPRESS_DIR);
		echo '</div>';

	}

	
}
