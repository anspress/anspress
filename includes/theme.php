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

		add_filter('wp_title', array($this, 'ap_title'), 100, 2);
		add_filter( 'the_title', array($this, 'the_title'), 100, 2 );
		//add_filter( 'nav_menu_link_attributes', array($this, 'menu'), 10, 3 );
		add_filter( 'wp_head', array($this, 'feed_link'), 9);

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
		 else {
			return $comment_template;
		 }
	}
	
	public function disable_comment_form( $open, $post_id ) {
		if( ap_opt('base_page') == $post_id || ap_opt('ask_page') == $post_id || ap_opt('edit_page') == $post_id || ap_opt('a_edit_page') == $post_id || ap_opt('categories_page') == $post_id ) {
			return false;
		}
		return $open;
	}
	
	public function ap_title( $title) {
		if(is_anspress()){
			$new_title = ap_page_title();
		
			$new_title = str_replace('[anspress]', $new_title, $title);
			$new_title = apply_filters('ap_title', $new_title);
			
			return $new_title;
		}
		
		return $title;
	}
	
	public function the_title( $title, $id ) {		
			
		if ( $id == ap_opt('base_page') ) {
			return ap_page_title();
		}
		return $title;
	}
	
	public function menu( $atts, $item, $args ) {
		return $atts;
	}
	
	public function feed_link( ) {
		if(is_anspress()){
			echo '<link href="'. esc_url( home_url( '/feed/question-feed' ) ) .'" title="'.__('Question >> Feed', 'ap').'" type="application/rss+xml" rel="alternate">';
		}
	}
	

}

function ap_page_title() {
	if(is_question())
		$new_title = get_the_title(get_question_id());
	elseif(is_ask()){
		if(get_query_var('parent') != '')
			$new_title = sprintf('%s about "%s"', ap_opt('ask_page_title'), get_the_title(get_query_var('parent')));
		else
			$new_title = ap_opt('ask_page_title');
	}elseif(is_question_categories())
		$new_title = ap_opt('categories_page_title');
	elseif(is_question_tags())
		$new_title = ap_opt('tags_page_title');
	elseif(is_question_tag()){
		$tag = get_term_by('slug', get_query_var('question_tags'), 'question_tags');
		$new_title = sprintf(__('Question tag: %s', 'ap'), $tag->name);
	}elseif(is_question_cat()){
		$category = get_term_by('slug', get_query_var('question_category'), 'question_category');
		$new_title = sprintf(__('Question category: %s', 'ap'), $category->name);
	}elseif(is_question_edit())
		$new_title = __('Edit question ', 'ap'). get_the_title(get_question_id());
	elseif(is_answer_edit())
		$new_title = __('Edit answer', 'ap');
	elseif(is_ap_users())
		$new_title = ap_opt('users_page_title');
	elseif(is_ap_user())
		$new_title = ap_user_page_title();
	elseif(is_ap_search())
		$new_title = sprintf(ap_opt('search_page_title'), sanitize_text_field(get_query_var('ap_s')));
	else{
		if(get_query_var('parent') != '')
			$new_title = sprintf( __( 'Discussion on "%s"', 'ap'), get_the_title(get_query_var('parent') ));
		else
			$new_title = ap_opt('base_page_title');
			
	}
	$new_title = apply_filters('ap_page_title', $new_title);
	
	return $new_title;
}

function ap_user_page_title(){
	if(is_ap_user()){
		$userid = ap_get_user_page_user();
		$user = get_userdata($userid);
		$user_page = get_query_var('user_page');
		$user_page = $user_page ? $user_page : 'profile';
		
		$name = $user->data->display_name;
		
		if(get_current_user_id() == $userid)
			$name = __('You', 'ap');
		
		if( 'profile' == $user_page){
			if(get_current_user_id() == $userid)
				$title = __('Your profile', 'ap');
			else
				$title = sprintf(__('%s\'s profile', 'ap'), $name);
		}elseif( 'questions' == $user_page ){
			$title = sprintf(__('Questions asked by %s', 'ap'), $name);
		}elseif( 'answers' == $user_page ){
			$title = sprintf(__('Answers posted by %s', 'ap'), $name);
		}elseif( 'activity' == $user_page ){
			if(get_current_user_id() == $userid)
				$title = __('Your activity', 'ap');
			else
				$title = sprintf(__('%s\'s activity', 'ap'), $name);
		}elseif( 'favorites' == $user_page ){
			$title = sprintf(__('Favorites questions of %s', 'ap'), $name);
		}elseif( 'followers' == $user_page ){
			$title = sprintf(__('Users following %s', 'ap'), $name);
		}elseif( 'following' == $user_page ){
			$title = sprintf(__('Users being followed by %s', 'ap'), $name);
		}elseif( 'edit_profile' == $user_page ){
			$title = __('Edit your profile', 'ap');
		}elseif( 'settings' == $user_page ){
			$title = __('Your settings', 'ap');
		}elseif( 'messages' == $user_page ){
			$title = __('Your messages', 'ap');
		}elseif( 'badges' == $user_page ){
			if(get_current_user_id() == $userid)
				$title = __('Your badges', 'ap');
			else
				$title = sprintf(__('%s\'s activity', 'ap'), $name);
		}elseif( 'message' == $user_page ){
			$title = sprintf(__('Message', 'ap'), $name);
		}
		$title = apply_filters('ap_user_page_title', $title);
		
		return $title;
	}
	
	return __('Page not found', 'ap');
}

function is_anspress(){
	$queried_object = get_queried_object();
	
	if(!isset($queried_object->ID)) 
		return false;

	if( $queried_object->ID ==  ap_opt('base_page'))
		return true;
		
	return false;
}

function is_question(){
	if(is_anspress() && (get_query_var('question_id') || get_query_var('question') || get_query_var('question_name')))
		return true;
		
	return false;
}

function is_ask(){
	if(is_anspress() && get_query_var('ap_page')=='ask')
		return true;
		
	return false;
}
function is_question_categories(){
	if(is_anspress() && get_query_var('ap_page')=='categories')
		return true;
		
	return false;
}
function is_question_tags(){
	if(is_anspress() && get_query_var('ap_page')=='tags')
		return true;
		
	return false;
}
function is_ap_users(){
	if(is_anspress() && get_query_var('ap_page')=='users')
		return true;
		
	return false;
}
function is_question_tag(){
	if(is_anspress() && (get_query_var('qtag_id') || get_query_var('question_tags')))
		return true;
		
	return false;
}

function is_question_cat(){
	if(is_anspress() && (get_query_var('qcat_id') || get_query_var('question_category')))
		return true;
		
	return false;
}

function is_my_profile(){
	if(ap_get_user_page_user() == get_current_user_id())
		return true;
	
	return false;
}


function get_question_id(){
	if(is_question() && get_query_var('question_id')){
		return get_query_var('question_id');
	}elseif(is_question() && get_query_var('question')){
		return get_query_var('question');
	}elseif(is_question() && get_query_var('question_name')){
		$post = get_page_by_path(get_query_var('question_name'), OBJECT, 'question');
		return $post->ID;
	}elseif(get_query_var('edit_q')){
		return get_query_var('edit_q');
	}
	
	return false;
}

function get_question_tag_id(){
	
	if(is_question_tag() && get_option('permalink_structure')){
		$term = get_term_by('slug', get_query_var('question_tags'), 'question_tags');
		return $term->term_id;
	}else
		return get_query_var('qtag_id');
		
	return false;
}
function get_question_cat_id(){
	if(is_question_cat() && get_option('permalink_structure')){
		$term = get_term_by('slug', get_query_var('question_category'), 'question_category');
		return $term->term_id;
	}else
		return get_query_var('qcat_id');
		
	return false;
}

function get_edit_question_id(){
	if(is_anspress() && get_query_var('edit_q'))
		return get_query_var('edit_q');
		
	return false;
}
function is_answer_edit(){
	if(is_anspress() && get_query_var('edit_a'))
		return true;
		
	return false;
}
function is_question_edit(){
	if(is_anspress() && get_query_var('edit_q'))
		return true;
		
	return false;
}
function get_edit_answer_id(){
	if(is_anspress() && get_query_var('edit_a'))
		return get_query_var('edit_a');
		
	return false;
}

function is_ap_user(){
	if(is_anspress() && get_query_var('ap_page') == 'user')
		return true;
		
	return false;
}

function ap_get_user_page_user(){
	if(is_ap_user()){
		$user = sanitize_text_field(str_replace('%20', ' ', get_query_var('user')));
		if($user){
			if(!is_int($user)){
				$user = get_user_by('login', $user);
				return $user->ID;
			}
			return $user;
		}else{
			return get_current_user_id();
		}
	}	
		
	return false;
}

function is_ap_profile(){
	if(is_anspress() && get_query_var('user_page') == '')
		return true;
		
	return false;
}

function is_ap_revision(){
	if(is_anspress() && get_query_var('ap_page') == 'revision')
		return true;
		
	return false;
}

function is_ap_search(){
	if(is_anspress() && get_query_var('ap_page') == 'search')
		return true;
		
	return false;
}


function is_ap_followers(){
	if(is_ap_user() && get_query_var('user_page') == 'followers')
		return true;
		
	return false;
}

function ap_current_page_is(){

	if(is_anspress()){
		
		if(is_question())
			$template = 'question';
		elseif(is_ask())
			$template = 'ask';
		elseif(is_question_categories())
			$template = 'categories';
		elseif(is_question_tags())
			$template = 'tags';
		elseif(is_question_tag())
			$template = 'tag';
		elseif(is_question_cat())
			$template = 'category';
		elseif(is_question_edit())
			$template = 'edit-question';
		elseif(is_answer_edit())
			$template = 'edit-answer';
		elseif(is_ap_users())
			$template = 'users';
		elseif(is_ap_user())
			$template = 'user';
		elseif(is_ap_search())
			$template = 'search';
		elseif(is_ap_revision())
			$template = 'revision';
		elseif(get_query_var('ap_page') == '')
			$template = 'base';
		else
			$template = 'not-found';
		
		return apply_filters('ap_current_page_is', $template);
	}
	return false;
}

function ap_get_current_page_template(){

	if(is_anspress()){
			$template = ap_current_page_is();
		
		return apply_filters('ap_current_page_template', $template.'.php');
	}
	return 'content-none.php';
}

function ap_current_user_page_is($page){
	if (get_query_var('user_page') == $page)
		return true;
	return false;
}

function is_private_question($question_id = false){
	if(!$question_id)
		$question_id = get_the_ID();
	
	if(get_post_status( $question_id ) == 'private_question')
		return true;
	
	return false;
}