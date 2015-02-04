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
		//add_filter( 'the_title', array($this, 'the_title'), 100, 2 );
		//add_filter( 'wp_head', array($this, 'feed_link'), 9);

		add_shortcode( 'anspress_questions', array( 'AnsPress_Questions_Shortcode', 'anspress_questions' ) );
		add_shortcode( 'anspress_question_categories', array( 'AnsPress_Categories_Shortcode', 'anspress_categories' ) );
		add_shortcode( 'anspress_user', array( 'AnsPress_User_Shortcode', 'anspress_user' ) );
		add_shortcode( 'anspress_ask', array( 'AnsPress_Ask_Shortcode', 'anspress_ask' ) );
		add_shortcode( 'anspress_edit_page', array( 'AnsPress_Edit_Shortcode', 'anspress_edit' ) );
		add_shortcode( 'anspress_q_search', array( 'AnsPress_Search_Shortcode', 'anspress_search' ) );

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
	 * @since 2.0.1
	 */
	public function question_single_the_content( $content ) {
		// check if is question
		If(is_singular('question')){
			/**
			 * This will prevent infinite loop
			 */

			remove_filter( current_filter(), array($this, 'question_single_the_content') );

			
			//check if user have permission to see the question
			if(ap_user_can_view_post()){
				ob_start();
				echo '<div class="anspress-container">';
				/**
				 * ACTION: ap_before
				 * Action is fired before loading AnsPress body.
				 */
				do_action('ap_before');

				include ap_get_theme_location('question.php');
				echo '</div>';
				return ob_get_clean();
			}
			else
				return '<div class="ap-pending-notice ap-apicon-clock">'.ap_responce_message('no_permission_to_view_private', true).'</div>';
			
		}else{
			return $content;
		}	
		
	}

	/**
	 * Add answer-seleted class in post_class
	 * @param  array $classes
	 * @return array
	 * @since 2.0.1
	 **/
	public function question_post_class($classes)
	{
		global $post;
		if($post->post_type == 'question'){
			if(ap_is_answer_selected($post->post_id))
				$classes[] = 'answer-selected';
			
			$classes[] = 'answer-count-'.ap_count_answer_meta();
		}
		
		return $classes;
	}
	
	/**
	 * Add anspress classess to body
	 * @param  array $classes
	 * @return array
	 * @since 2.0.1
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

/**
 * Check if single question page.
 * @return boolean
 * @since unknown
 */
function is_question(){
	$return = false;
	if(is_singular('question'))
		$return = true;
	return $return;
}

/** 
 * Check if current page is ask page.
 * @return boolean
 * @since unlnown
 */
function is_ask(){
	if(get_the_ID() == ap_opt('ask_page_id'))
		return true;
		
	return false;
}

function is_ap_users(){
	$queried_object = get_queried_object();
	if(isset($queried_object->ID) && $queried_object->ID == ap_opt('users_page_id'))
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

/**
 * Check if current page is user page
 * @return boolean
 * @since unknown
 */
function is_ap_user(){
	$queried_object = get_queried_object();
	if(isset($queried_object->ID) && $queried_object->ID == ap_opt('user_page_id'))
		return true;
		
	return false;
}

function ap_user_page_user_id(){
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

/**
 * @param string $page
 */
function ap_current_user_page_is($page){
	if (get_query_var('user_page') == $page)
		return true;
	return false;
}

/**
 * Get post status
 * @param  integer $post_id
 * @return string
 * @since 2.0.0-alpha2
 */
function ap_post_status($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
	
	return get_post_status( $post_id );
}

/**
 * Check if current post is private
 * @return boolean
 */
function is_private_post($post_id = false){
	
	if(ap_post_status( $post_id ) == 'private_post')
		return true;
	
	return false;
}

/**
 * Check if post is waiting moderation
 * @return boolean
 */
function is_post_waiting_moderation($post_id = false){
	
	if(get_post_status( $post_id ) == 'moderate')
		return true;
	
	return false;
}

/**
 * Check if question is closed
 * @return boolean
 * @since 2.0.0-alpha2
 */
function is_post_closed($post_id = false){
	if(get_post_status( $post_id ) == 'closed')
		return true;
	
	return false;
}

/**
 * Check if question have a parent post
 * @param  boolean|integer $post_id
 * @return boolean
 * @since   2.0.0-alpha2
 */
function ap_have_parent_post($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
	
	$post = get_post($post_id);
	
	if($post->post_parent > 0 && 'question' == $post->post_type)
		return true;
	
	return false;
}

/**
 * Anspress pagination
 * Uses paginate_links
 * @param  double $current Current paged, if not set then get_query_var('paged') is used
 * @param  integer $total   Total number of pages, if not set then global $questions is used
 * @param  string  $format 
 * @return string
 */
function ap_pagination( $current = false, $total = false, $format = '?paged=%#%'){
	global $ap_max_num_pages, $ap_current;

	$big = 999999999; // need an unlikely integer

	if($current === false)
		$current = max( 1, get_query_var('paged') );
	elseif(!empty($ap_current))
		$current = $ap_current;


	if(!empty($ap_max_num_pages))
	{
		$total = $ap_max_num_pages;

	}elseif($total === false)
	{
		global $questions;
		$total = $questions->max_num_pages;
	}
	echo '<div class="ap-pagination clearfix">';
	echo paginate_links( array(
		'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' 	=> $format,
		'current' 	=> $current,
		'total' 	=> $total
	) );
	echo '</div>';
}

/**
 * Question meta to display 
 * @param  int $question_id
 * @return string
 * @since 2.0.1
 */
function ap_display_question_metas($question_id =  false){
	if (!$question_id) {
		$question_id = get_the_ID();
	}

	$metas = array();
	if(is_singular('question')){		
		$metas['created'] = sprintf( __( '<span>Created</span> <i><time itemprop="datePublished" datetime="%s">%s Ago</time></i>', 'ap' ), get_the_time('c', $question_id), ap_human_time( get_the_time('U')));
		
	}else{
		$ans_count = ap_count_answer_meta();
		$net_vote = ap_net_vote();

		$metas['answers'] = sprintf( _n('<span>1 answer</span>', '<span>%d answers</span>', $ans_count, 'ap'), $ans_count) ;
		$metas['vote'] = sprintf( _n('<span>1 vote</span>', '<span>%d votes</span>', $net_vote, 'ap'), $net_vote) ;
		
		$view_count = ap_get_qa_views();
		$metas['views'] = sprintf( __('<i>%d views</i>', 'ap'), $view_count) ;
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
 * @since 2.0.1
 */
function ap_icon($name, $html = false){
	$icons = array(
		'follow' 			=> 'apicon-plus',
		'unfollow' 			=> 'apicon-minus',
		'upload' 			=> 'apicon-upload',
		'unchecked' 		=> 'apicon-checkbox-unchecked',
		'checked' 			=> 'apicon-checkbox-checked',
		'check' 			=> 'apicon-check',
		'select' 			=> 'apicon-check',
		'new_question' 		=> 'apicon-question',
		'new_answer' 		=> 'apicon-answer',
		'new_comment' 		=> 'apicon-talk-chat',
		'new_comment_answer'=> 'apicon-mail-reply',
		'edit_question' 	=> 'apicon-pencil',
		'edit_answer' 		=> 'apicon-pencil',
		'edit_comment' 		=> 'apicon-pencil',
		'vote_up'			=> 'apicon-thumb-up',
		'vote_down'			=> 'apicon-thumb-down',
		'favorite'			=> 'apicon-heart',
		'delete'			=> 'apicon-trashcan',
		'flag'				=> 'apicon-flag',
		'edit'				=> 'apicon-pencil',
		'comment'			=> 'apicon-mail-reply',
		'answer'			=> 'apicon-comment',
		'view'				=> 'apicon-eye',
		'vote'				=> 'apicon-triangle-up',
		'cross'				=> 'apicon-x',
		'more'				=> 'apicon-ellipsis',
		'category'			=> 'apicon-file-directory',
		'home'				=> 'apicon-home',
		'question'			=> 'apicon-comment-discussion',
		'upload'			=> 'apicon-cloud-upload',
		'link'				=> 'apicon-link',
		'help'				=> 'apicon-question',
		'error'				=> 'apicon-x',
		'warning'			=> 'apicon-alert',
		'success'			=> 'apicon-check',
		'history'			=> 'apicon-history',
		'mail'				=> 'apicon-mail',
		'link'				=> 'apicon-link',
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

	$actions['vote'] = '<div class="ap-single-vote ap-pull-left">'.ap_vote_btn($post, false).'</div>';

	/**
	 * Select answer button
	 * @var string
	 */
	if($post->post_type == 'answer')
		$actions['select_answer'] = ap_select_answer_btn_html(get_the_ID());

	/**
	 * Comment button
	 */
	if(ap_user_can_comment())
		$actions['comment'] = ap_comment_btn_html();

	/**
	 * edit question link
	 */
	if(ap_user_can_edit_question($post->ID) && $post->post_type == 'question')
		$actions['edit_question'] = ap_edit_post_link_html();

	if(ap_user_can_edit_ans($post->ID) && $post->post_type == 'answer')
		$actions['edit_answer'] = ap_edit_post_link_html();
	
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
		echo '<ul class="ap-user-actions ap-ul-inline clearfix">';
		foreach($actions as $k => $action){
			if(!empty($action))
				echo '<li class="ap-post-action ap-action-'.$k.'">'.$action.'</li>';
		}
		echo '</ul>';
	}
}

/**
 * Output questions list tab
 * @return string
 */
function ap_questions_tab($current_url){
	$param = array();

	$sort = isset($_GET['ap_sort']) ? $_GET['ap_sort'] : 'active';

	$label = sanitize_text_field(get_query_var('label'));
	$search_q = sanitize_text_field(get_query_var('ap_s'));

	//$param['sort'] = $sort;

	if(!empty( $search_q ))
		$param['ap_s'] =  $search_q;

	$link = add_query_arg($param, $current_url);
	
	$navs = array(
		'active' => array('link' => add_query_arg(array('ap_sort' => 'active'), $link), 'title' => __('Active', 'ap')), 
		'newest' => array('link' => add_query_arg(array('ap_sort' => 'newest'), $link), 'title' => __('Newest', 'ap')), 
		'voted' => array('link' => add_query_arg(array('ap_sort' => 'voted'), $link), 'title' => __('Voted', 'ap')), 
		'answers' => array('link' => add_query_arg(array('ap_sort' => 'answers'), $link), 'title' => __('Answers', 'ap')), 
		'unanswered' => array('link' => add_query_arg(array('ap_sort' => 'unanswered'), $link), 'title' => __('Unanswered', 'ap')), 
		'unsolved' => array('link' => add_query_arg(array('ap_sort' => 'unsolved'), $link), 'title' => __('Unsolved', 'ap')), 
		//'oldest' => array('link' => $link.'oldest', 'title' => __('Oldest', 'ap')), 
		);
	
	/**
	 * FILTER: ap_questions_tab
	 * Before prepering questions list tab.
	 * @var array
	 * @since 2.0.1
	 */
	$navs = apply_filters('ap_questions_tab', $navs );

	echo '<ul class="ap-questions-tab ap-ul-inline clearfix">';
	foreach ($navs as $k => $nav) {
		echo '<li'.( $sort == $k ? ' class="active"' : '') .'><a href="'. $nav['link'] .'">'. $nav['title'] .'</a></li>';
	}
	echo '</ul>';

	?>
	
	

		<!-- TODO - LABEL Extension -->
		<!-- <div class="pull-right">
			<div class="ap_status ap-dropdown">
				<a href="#" class="btn ap-btn ap-dropdown-toggle"><?php _e('Label', 'ap'); ?> &#9662;</a>
				<ul class="ap-dropdown-menu">
					<?php
						/*$labels = get_terms('question_label', array('orderby'=> 'name','hide_empty'=> true));
						foreach($labels as $l){
							$color = ap_get_label_color($l->term_id);
							echo '<li'. ($label == $l->slug ? ' class="active" ' : '') .'><a href="'.$label_link.$l->slug.'" title="'.$l->description.'"><span class="question-label-color" style="background:'.$color.'"> </span>'.$l->name.'</a></li>';
						}*/
					?>
				</ul>
			</div>
		</div> -->

	<?php
}

/**
 * Output answers tab
 * @return void
 * @since 2.0.1
 */
function ap_answers_tab($base = false){
	$sort = isset($_GET['ap_sort']) ? $_GET['ap_sort'] : ap_opt('answers_sort');
		
	if(!$base)
		$base = get_permalink();
	
	$navs = array(
		'active' => array('link' => add_query_arg(  array('ap_sort' => 'active'), $base), 'title' => __('Active', 'ap')),
		'voted' => array('link' => add_query_arg(  array('ap_sort' => 'voted'), $base), 'title' => __('Voted', 'ap')), 
		'newest' => array('link' =>add_query_arg(  array('ap_sort' => 'newest'), $base), 'title' => __('Newest', 'ap')), 
		'oldest' => array('link' => add_query_arg(  array('ap_sort' => 'oldest'), $base), 'title' => __('Oldest', 'ap')),			
		);

	echo '<ul class="ap-answers-tab ap-ul-inline ap-pull-right clearfix">';
	foreach ($navs as $k => $nav) {
		echo '<li'.( $sort == $k ? ' class="active"' : '') .'><a href="'. $nav['link'] .'">'. $nav['title'] .'</a></li>';
	}
	echo '</ul>';
}

/**
 * Answer meta to display 
 * @param  int $answer_id
 * @return string
 * @since 2.0.1
 */
function ap_display_answer_metas($answer_id =  false){
	if (!$answer_id) {
		$answer_id = get_the_ID();
	}

	$metas = array();
	if(is_ap_user() && ap_is_best_answer($answer_id))
		$metas['best_answer'] = '<span class="ap-best-answer-label">'.__('Best answer', 'ap').'</span>';

	$metas['history'] = ap_last_active_time($answer_id);
	$metas['created'] = sprintf( __( '<span>Created</span> <i><time itemprop="datePublished" datetime="%s">%s Ago</time></i>', 'ap' ), get_the_time('c', $answer_id), ap_human_time( get_the_time('U')));

	
	
	/**
	 * FILTER: ap_display_answer_meta
	 * Used to filter answer display meta
	 * @since 2.0.1
	 */
	$metas = apply_filters('ap_display_answer_metas', $metas, $answer_id );

	$output = '';
	if (!empty($metas) && is_array($metas)) {
		foreach ($metas as $meta => $display) {
			$output .= "<li class='ap-display-meta-item {$meta}'>{$display}</li>";
		}
	}

	return $output;
}


function ap_comment_actions_buttons()
{
	global $comment;
	$post = get_post($comment->comment_post_ID);

	if(!$post->post_type == 'question' || !$post->post_type == 'answer')
		return;

	$actions = array();

	if(ap_user_can_edit_comment(get_comment_ID())){
		$nonce = wp_create_nonce( 'edit_comment_'. get_comment_ID() );
		$actions['edit'] = '<a class="comment-edit-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__('Edit', 'ap').'</a>';
	}

	if(ap_user_can_delete_comment(get_comment_ID())){
		$nonce = wp_create_nonce( 'delete_comment' );
		$actions['delete'] = '<a class="comment-delete-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="delete_comment" data-query="ap_ajax_action=delete_comment&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__('Delete', 'ap').'</a>';
	}

	/**
	 * FILTER: ap_comment_actions_buttons
	 * For filtering post actions buttons
	 * @var 	string
	 * @since 	2.0
	 */
	$actions = apply_filters('ap_comment_actions_buttons', $actions );

	if (!empty($actions) && count($actions) > 0) {
		foreach($actions as $k => $action){
			echo '<span class="ap-comment-action ap-action-'.$k.'">'.$action.'</span>';
		}
	}
}
