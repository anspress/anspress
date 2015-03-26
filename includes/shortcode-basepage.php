<?php
/**
 * Class for AnsPress base page shortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_BasePage_Shortcode {

	protected static $instance = NULL;

    public static function get_instance()
    {
        // create an object
        NULL === self::$instance && self::$instance = new self;

        return self::$instance; // return the object
    }

	/**
	 * Control the output of [anspress] shortcode
	 * @param  array $atts  {
	 *    Attributes of the shortcode.
	 *    
	 * 	  $categories slug of question_category
	 * 	  $tags slug of question_tag
	 * 	  $tax_relation taxonomy relation, see here http://codex.wordpress.org/Taxonomies
	 * 	  $tags_operator operator for question_tag taxnomomy
	 * 	  $categories_operator operator for question_category taxnomomy
	 * }
	 * @param  string $content
	 * @return string
	 * @since 2.0.0-beta
	 */
	public function anspress_sc( $atts, $content="" ) {
		
		global $questions, $wp;

		if(isset($atts['categories'])){
			$categories = explode (',', str_replace(', ', ',', $atts['categories']));
			// append $atts in global $wp so that we can use it later
			$wp->set_query_var('ap_sc_atts_categories', $categories);
		}
		
		if(isset($atts['tags'])){
			$tags = explode (',', str_replace(', ', ',', $atts['tags']));
			$wp->set_query_var('ap_sc_atts_tags', $tags);
		}

		if(isset($atts['tax_relation'])){
			$tax_relation = $atts['tax_relation'];
			$wp->set_query_var('ap_sc_atts_tax_relation', $tax_relation);
		}

		if(isset($atts['tags_operator'])){
			$tags_operator = $atts['tags_operator'];
			$wp->set_query_var('ap_sc_atts_tags_operator', $tags_operator);
		}

		if(isset($atts['categories_operator'])){
			$categories_operator = $atts['categories_operator'];
			$wp->set_query_var('ap_sc_atts_categories_operator', $categories_operator);
		}

		ob_start();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			ap_page();

			if(!ap_opt('author_credits'))
				echo '<div class="ap-cradit">Question and answer is powered by <a href="http://wp3.in" traget="_blank">AnsPress</a></div>';

		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();				
	}
	
}

