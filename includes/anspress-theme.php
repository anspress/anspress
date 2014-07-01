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

class anspress_theme {

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
		//add_filter( 'template_include', array($this, 'template_files'), 1 );	
		add_filter( 'comments_template', array($this, 'comment_template') );
		add_action( 'after_setup_theme', array($this, 'includes') );
		//add_filter( 'comments_open', array($this, 'disable_comment_form'), 10 , 2 );

		add_filter('wp_title', array($this, 'ap_title'), 10, 2);
	}
	
	// include required theme files
	public function includes(){
		require_once ap_get_theme_location('functions.php');	
	}
	
	/* Template for single question */		
	public function template_files( $template_path ) {
		global $post;
		
		if($post){
			if ( 
			'question' == get_post_type() 			|| 
			is_tax( 'question_category' ) 			|| 
			ap_opt('base_page') == $post->ID 		|| 
			ap_opt('ask_page') == $post->ID 		|| 
			ap_opt('edit_page') == $post->ID 		|| 
			ap_opt('a_edit_page') == $post->ID 		|| 
			ap_opt('categories_page') == $post->ID 	|| 
			ap_opt('tags_page') == $post->ID 		|| 
			ap_opt('users_page') == $post->ID) {
				$template_path = ap_get_theme_location('index.php');				
			}
		}
		
		if ( is_tax( 'question_category' ) || is_tax( 'question_tags' ))
			$template_path = ap_get_theme_location('index.php');
		
		if ( 'answer' == get_post_type() ) {
			if ( is_single() ) {
				global $post; 				
				wp_redirect( get_permalink($post->post_parent) ); exit;							
			}
		}		

		if( $post && ap_opt('ask_page') == $post->ID ){
			if(!is_user_logged_in()) auth_redirect();
		}


		return $template_path;
	}
	

	// register comment template	
	public function comment_template( $comment_template ) {
		 global $post;
		 if($post->post_type == 'question' || $post->post_type == 'answer' ){ 
			return ap_get_theme_location('comments.php');
		 }
	}
	
	public function disable_comment_form( $open, $post_id ) {
		if( ap_opt('base_page') == $post_id || ap_opt('ask_page') == $post_id || ap_opt('edit_page') == $post_id || ap_opt('a_edit_page') == $post_id || ap_opt('categories_page') == $post_id ) {
			return false;
		}
		return $open;
	}
	
	public function ap_title( $title, $sep ) {
		if ( is_question() ) {
			return get_the_title(get_question_id()).' '.$sep;
		}
		return $title;
	}

}
