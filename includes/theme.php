<?php
/**
 * AnsPress theme and template handling.
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

class AnsPress_Theme {
	/**
	 * Initial call
	 */
	public function __construct(){

		add_filter( 'the_content', array($this, 'question_single_the_content') );
		add_filter( 'post_class', array($this, 'question_post_class') );
		// Add specific CSS class by filter
		add_filter('body_class', array($this, 'body_class'));

		add_filter( 'comments_template', array($this, 'comment_template') );
		add_action( 'after_setup_theme', array($this, 'includes') );
		//add_filter('wp_title', array($this, 'ap_title'), 100, 2);
		add_filter( 'the_title', array($this, 'the_title'), 100, 2 );
		//add_filter( 'wp_head', array($this, 'feed_link'), 9);

		add_shortcode( 'anspress_questions', array( 'AnsPress_Questions_Shortcode', 'anspress_questions' ) );

		add_action('ap_before', array($this, 'ap_before_html_body'));


	}

	/**
	 * AnsPress theme function as like WordPress theme function
	 * @return void
	 */
	public function includes(){
		require_once ap_get_theme_location('functions.php');	
	}
	
	/**
	 * Append single question page content to the_content() for compatibility purpose.
	 * @param  string $content
	 * @return string
	 * @since 2.0
	 */
	public function question_single_the_content( $content ) {
		// check if is question
		If(is_singular('question')){
			/**
			 * This will prevent infinite loop
			 */

			remove_filter( current_filter(), array($this, 'question_single_the_content') );

			//check if user have permission to see the question
			if(ap_user_can_view_question()){
				echo '<div class="anspress-container">';
				/**
				 * ACTION: ap_before
				 * Action is fired before loading AnsPress body.
				 */
				do_action('ap_before');

				include ap_get_theme_location('question.php');
				echo '</div>';
			}
			else
				echo '<div class="ap-pending-notice ap-icon-clock">'.__('You do not have permission to view this question.', 'ap').'</div>';
		}else{
			return $content;
		}	
		
	}

	/**
	 * Add answer-seleted class in post_class
	 * @param  array $classes
	 * @return array
	 * @since 2.0
	 **/
	public function question_post_class($classes)
	{
		global $post;
		if($post->post_type == 'question'){
			if(ap_is_answer_selected($post->post_id))
				$classes[] = 'answer-selected';
			
			$classes[] = 'answer-count-'.ap_count_ans_meta();
		}
		
		return $classes;
	}
	
	/**
	 * Add anspress classess to body
	 * @param  array $classes
	 * @return array
	 * @since 2.0
	 */
	public function body_class($classes){
		// add anspress class to body
		if( get_the_ID() ==  ap_opt('questions_page_id') || get_the_ID() ==  ap_opt('question_page_id') || is_singular('question'))
			$classes[] = 'anspress';
			
		// return the $classes array
		return $classes;
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
	
	/**
	 * TODO: remove this as we are using specefic pages 
	 * @param unknown $title
	 * @return void
	 */
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

	public function ap_before_html_body(){
		dynamic_sidebar( 'ap-before' );
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

function is_question(){
	$return = false;
	if(is_singular('question'))
		$return = true;
	return $return;
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
	if(get_query_var('edit_q'))
		return get_query_var('edit_q');
		
	return false;
}
function is_answer_edit(){
	if(get_query_var('edit_a'))
		return true;
		
	return false;
}
function is_question_edit(){
	if(get_query_var('edit_q'))
		return true;
		
	return false;
}
function get_edit_answer_id(){
	if(get_query_var('edit_a'))
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

/**
 * Anspress pagination
 * Uses paginate_links
 * @param  mixed $current Current paged, if not set then get_query_var('paged') is used
 * @param  mixed $total   Total number of pages, if not set then global $questions is used
 * @param  string  $format 
 * @return string
 */
function ap_pagination( $current = false, $total = false, $format = '?paged=%#%'){

	$big = 999999999; // need an unlikely integer

	if(!$current)
		$current = max( 1, get_query_var('paged') );

	if(!$total){
		global $questions;
		$total = $questions->max_num_pages;
	}

	echo paginate_links( array(
		'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' 	=> $format,
		'current' 	=> $current,
		'total' 	=> $total
	) );
}

/**
 * Question meta to display 
 * @param  int $question_id
 * @return string
 * @since 2.0
 */
function ap_display_question_metas($question_id =  false){
	if (!$question_id) {
		$question_id = get_the_ID();
	}

	$metas = array();

	if(ap_is_answer_selected($question_id) && !is_singular('question')){
		$metas['selected'] = '<span class="ap-tip" title="'.__('answer accepted', 'ap').'">'.ap_icon('select', true).'</span>';

		$metas['history'] = ap_last_active_time($question_id);
	}

	if(is_singular('question')){
		$last_active = ap_last_active($question_id);
		$metas['active'] = sprintf( __( '<span>Active</span> <a class="ap-tip" title="Show all histories of this question" href="#ap-question-preview" data-action="ap-toggle-history"><time class="updated" itemprop="dateUpdated" datetime="%s">%s Ago</time></a>', 'ap' ), mysql2date('c', $last_active),  ap_human_time( mysql2date('U', $last_active)));

		$metas['created'] = sprintf( __( '<span>Created</span> <i><time itemprop="datePublished" datetime="%s">%s Ago</time></i>', 'ap' ), get_the_time('c', $question_id), ap_human_time( get_the_time('U')));

		$view_count = ap_get_qa_views();
		$metas['views'] = sprintf( __('<span>Viewed</span> <i>%d Times</i>', 'ap'), $view_count) ;
	}
	
	


	/**
	 * FILTER: ap_display_question_meta
	 * Used to filter question display meta
	 */
	$metas = apply_filters('ap_display_question_metas', $metas, $question_id );

	$output = '';
	if (!empty($metas) && is_array($metas)) {
		foreach ($metas as $meta => $display) {
			$output .= "<li class='ap-display-meta-item {$meta}'>{$display}</li>";
		}
	}

	return $output;
}

/**
 * Icons for anspress
 * @param  string $name
 * @param  boolean $html
 * @return string
 * @since 2.0
 */
function ap_icon($name, $html = false){
	$icons = array(
		'follow' 			=> 'icon-plus',
		'unfollow' 			=> 'icon-minus',
		'upload' 			=> 'icon-upload',
		'unchecked' 		=> 'icon-checkbox-unchecked',
		'checked' 			=> 'icon-checkbox-checked',
		'select' 				=> 'icon-check',
		'new_question' 		=> 'icon-question',
		'new_answer' 		=> 'icon-answer',
		'new_comment' 		=> 'icon-talk-chat',
		'new_comment_answer'=> 'icon-mail-reply',
		'edit_question' 	=> 'icon-pencil',
		'edit_answer' 		=> 'icon-pencil',
		'edit_comment' 		=> 'icon-pencil',
		'vote_up'			=> 'icon-triangle-up',
		'vote_down'			=> 'icon-triangle-down',
		'favorite'			=> 'icon-heart',
		'delete'			=> 'icon-trashcan',
		'flag'				=> 'icon-flag',
		'edit'				=> 'icon-pencil',
		'comment'			=> 'icon-mail-reply',
		'answer'			=> 'icon-comment',
		'view'				=> 'icon-eye',
		'vote'				=> 'icon-triangle-up',
	);
	
	$icons = apply_filters('ap_icon', $icons);
	$icon = '';

	if(isset($icons[$name]))
		$icon = $icons[$name];

	if($html)
		return '<i class="'.$icon.'"></i> ';

	return $icon;
		
	return '';
}

/**
 * Post actions buttoons
 * @return 	string
 * @since 	2.0
 */
function ap_post_actions_buttons()
{
	global $post;

	if(!$post->post_type == 'question' || !$post->post_type == 'answer')
		return;

	$actions = array();

	/**
	 * Comment button
	 */
	if(ap_user_can_comment())
		$actions['comment'] = ap_comment_btn_html();

	/**
	 * edit question link
	 */
	if(ap_user_can_edit_question($post->ID))
		$actions['edit_question'] = ap_edit_q_btn_html();
	
	if(is_user_logged_in())
		$actions['flag'] = ap_flag_btn_html();

	if(ap_user_can_delete($post->ID))
		$actions['delete'] = ap_post_delete_btn_html();

	/**
	 * FILTER: ap_post_actions_buttons
	 * For filtering post actions buttons
	 * @var 	string
	 * @since 	2.0
	 */
	$actions = apply_filters('ap_post_actions_buttons', $actions );

	if (!empty($actions) && count($actions) > 0) {
		foreach($actions as $k => $action){
			echo '<li class="ap-post-action ap-action-'.$k.'">'.$action.'</li>';
		}
	}
}