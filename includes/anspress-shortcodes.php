<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class anspress_shortcodes {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	public function __construct(){
		add_shortcode( 'anspress', array( $this, 'ap_base_page_sc' ) );
	}

	public function ap_base_page_sc( $atts, $content="" ) {
		if(is_question()){
			$question = get_post( get_question_id());
		}elseif(is_question_tag()){
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			$args = array(
				'post_type'=>'question', 
				'paged' => $paged, 
				'tax_query' => array(		
						array(
							'taxonomy' => 'question_tags',
							'field' => 'id',
							'terms' => array( get_question_tag_id() )
						)
					)
				);
			$question = new WP_Query( $args );
		}elseif(is_question_cat()){
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			$args = array(
				'post_type'=>'question', 
				'paged' => $paged, 
				'tax_query' => array(		
						array(
							'taxonomy' => 'question_category',
							'field' => 'id',
							'terms' => array( get_question_cat_id() )
						)
					)
				);
			$question = new WP_Query( $args );
		}

		include ap_get_theme_location(ap_get_current_page_template());
		
		if(is_question() || is_tag())
			wp_reset_postdata();
	}
	
}
