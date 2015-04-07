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

function ap_page_title() {
	if(is_question())
		$new_title = get_the_title(get_question_id());
	elseif(is_ask()){
		if(get_query_var('parent') != '')
			$new_title = sprintf('%s about "%s"', ap_opt('ask_page_title'), get_the_title(get_query_var('parent')));
		else
			$new_title = ap_opt('ask_page_title');
	}/*
	elseif(is_question_tags())
		$new_title = ap_opt('tags_page_title');
	elseif(is_question_tag()){
		$tag = get_term_by('slug', get_query_var('question_tags'), 'question_tags');
		$new_title = sprintf(__('Question tag: %s', 'ap'), $tag->name);
	}*/
	elseif(is_ap_edit())
		$new_title = __('Edit post', 'ap');

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


function ap_edit_post_id(){
	if(is_anspress() && get_query_var('edit_post_id'))
		return get_query_var('edit_post_id');
		
	return false;
}

function is_ap_edit(){
	if(is_anspress() && get_query_var('edit_post_id'))
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
 * Get post status
 * @param  false|integer $post_id
 * @return string
 * @since 2.0.0-alpha2
 */
function ap_post_status($post_id = false){
	if(false === $post_id)
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
	$page_num_link = str_replace(array('&amp;', '&#038;'), '&', get_pagenum_link( $big ));

	echo '<div class="ap-pagination clearfix">';
	echo paginate_links( array(
		'base' 		=> str_replace( $big, '%#%', $page_num_link ),
		'format' 	=> $format,
		'current' 	=> $current,
		'total' 	=> $total
	) );
	echo '</div>';
}

/**
 * Question meta to display 
 * @param  false|integer $question_id
 * @return string
 * @since 2.0.1
 */
function ap_display_question_metas($question_id =  false){
	if (false === $question_id) {
		$question_id = get_the_ID();
	}

	$metas = array();
	if(is_question()){		
		$metas['created'] = sprintf( __( '<span>Created</span> <i><time itemprop="datePublished" datetime="%s">%s Ago</time></i>', 'ap' ), get_the_time('c', $question_id), ap_human_time( get_the_time('U')));
		
	}else{
		if(ap_is_answer_selected())
			$metas['solved'] = '<span class="ap-best-answer-label ap-tip" title="'.__('answer accepted', 'ap').'">'.__('Selected', 'ap').'</span>';

		$view_count = ap_get_qa_views();
		$metas['views'] = sprintf( __('<i>%d views</i>', 'ap'), $view_count) ;
		$metas['history'] = ap_get_latest_history_html($question_id);
	}	

	/**
	 * FILTER: ap_display_question_meta
	 * Used to filter question display meta
	 */
	$metas = apply_filters('ap_display_question_metas', $metas, $question_id );

	$output = '';
	if (!empty($metas) && is_array($metas)) {
		foreach ($metas as $meta => $display) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
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
		'mute'				=> 'apicon-mute',
		'unmute'			=> 'apicon-unmute',
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
* Register anspress pages
* @param string $page_slug slug for links
* @param string $page_title Page title
* @param callable $func Hook to run when shortcode is found.
* @return void
* @since 2.0.1
*/
function ap_register_page($page_slug, $page_title, $func){
	ap_append_to_global_var('ap_pages', $page_slug , array('title' => $page_title, 'func' => $func));
}

/**
* Output current anspress page
* @return void
* @since 2.0.0-beta
*/
function ap_page(){
	global $ap_pages;
	$current_page  = get_query_var('ap_page');

	if(is_question())
		$current_page = 'question';
	
	elseif($current_page == '' && !is_question())
		$current_page = 'base';

	if(isset($ap_pages[$current_page]['func']))
		call_user_func($ap_pages[$current_page]['func']);
	else
		include(ap_get_theme_location('not-found.php'));
}

/**
 * Post actions buttoons
 * @return 	string
 * @param  array $disable
 * @return void
 * @since 	2.0
 */
function ap_post_actions_buttons($disable = array())
{
	global $post;

	if(!$post->post_type == 'question' || !$post->post_type == 'answer')
		return;

	$actions = array();

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
		$actions['dropdown']['edit_question'] = ap_edit_post_link_html();

	if(ap_user_can_edit_ans($post->ID) && $post->post_type == 'answer')
		$actions['dropdown']['edit_answer'] = ap_edit_post_link_html();
	
	if(is_user_logged_in())
		$actions['dropdown']['flag'] = ap_flag_btn_html();

	if(ap_user_can_delete($post->ID))
		$actions['dropdown']['delete'] = ap_post_delete_btn_html();

	/**
	 * FILTER: ap_post_actions_buttons
	 * For filtering post actions buttons
	 * @var 	string
	 * @since 	2.0
	 */
	$actions = apply_filters('ap_post_actions_buttons', $actions );

	if (!empty($actions) && count($actions) > 0) {
		echo '<ul class="ap-q-actions ap-ul-inline clearfix">';
		foreach($actions as $k => $action){
			if(!empty($action) && $k != 'dropdown' && !in_array($k, $disable))
				echo '<li class="ap-post-action ap-action-'.$k.'">'.$action.'</li>';
		}
		if(!empty($actions['dropdown'])){
			echo '<li class="ap-post-action ap-action-dropdown">';
				echo '<div class="ap-dropdown">';
				echo '<a class="ap-dropdown-toggle apicon-gear" href="#"></a>';
				echo '<ul class="ap-dropdown-menu">';
					foreach($actions['dropdown'] as $sk=>$sub)
						echo '<li class="ap-post-action ap-action-'.$sk.'">'.$sub.'</li>';
				echo '</ul>';
				echo '</div>';
			echo '</li>';
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

	$search_q = sanitize_text_field(get_query_var('ap_s'));

	//$param['sort'] = $sort;

	if(!empty( $search_q ))
		$param['ap_s'] =  $search_q;

	$link = add_query_arg($param, $current_url);
	
	$navs = array(
		'active' => array('link' => add_query_arg(array('ap_sort' => 'active'), $link), 'title' => __('Active', 'ap')), 
		'newest' => array('link' => add_query_arg(array('ap_sort' => 'newest'), $link), 'title' => __('Newest', 'ap'))
	);

	if(!ap_opt('disable_voting_on_question'))
		$navs['voted'] =  array('link' => add_query_arg(array('ap_sort' => 'voted'), $link), 'title' => __('Voted', 'ap'));

	$navs['answers'] = array('link' => add_query_arg(array('ap_sort' => 'answers'), $link), 'title' => __('Answers', 'ap'));
	$navs['unanswered'] = array('link' => add_query_arg(array('ap_sort' => 'unanswered'), $link), 'title' => __('Unanswered', 'ap'));
	$navs['unsolved'] = array('link' => add_query_arg(array('ap_sort' => 'unsolved'), $link), 'title' => __('Unsolved', 'ap')); 

	
	/**
	 * FILTER: ap_questions_tab
	 * Before prepering questions list tab.
	 * @var array
	 * @since 2.0.1
	 */
	$navs = apply_filters('ap_questions_tab', $navs );

	echo '<ul class="ap-questions-tab ap-ul-inline clearfix">';
	foreach ($navs as $k => $nav) {
		echo '<li class="ap-questions-tab-'.esc_attr($k).( $sort == $k ? ' active' : '') .'"><a href="'. esc_url($nav['link']) .'">'. $nav['title'] .'</a></li>';
	}
	echo '</ul>';

	?>
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
		'active' => array('link' => add_query_arg(  array('ap_sort' => 'active'), $base), 'title' => __('Active', 'ap'))
	);
	
	if(!ap_opt('disable_voting_on_answer'))
		$navs['voted'] = array('link' => add_query_arg(  array('ap_sort' => 'voted'), $base), 'title' => __('Voted', 'ap'));

	$navs['newest'] = array('link' =>add_query_arg(  array('ap_sort' => 'newest'), $base), 'title' => __('Newest', 'ap'));
	$navs['oldest'] = array('link' => add_query_arg(  array('ap_sort' => 'oldest'), $base), 'title' => __('Oldest', 'ap'));


	echo '<ul class="ap-answers-tab ap-ul-inline ap-pull-right clearfix">';
	foreach ($navs as $k => $nav) {
		echo '<li'.( $sort == $k ? ' class="active"' : '') .'><a href="'. $nav['link'] .'">'. $nav['title'] .'</a></li>';
	}
	echo '</ul>';
}

/**
 * Answer meta to display 
 * @param  false|integer $answer_id
 * @return string
 * @since 2.0.1
 */
function ap_display_answer_metas($answer_id =  false){
	if (false === $answer_id) 
		$answer_id = get_the_ID();

	$metas = array();
	if(ap_is_best_answer($answer_id))
		$metas['best_answer'] = '<span class="ap-best-answer-label">'.__('Best answer', 'ap').'</span>';

	$metas['history'] = ap_last_active_time($answer_id);

	/**
	 * FILTER: ap_display_answer_meta
	 * Used to filter answer display meta
	 * @since 2.0.1
	 */
	$metas = apply_filters('ap_display_answer_metas', $metas, $answer_id );

	$output = '';
	if (!empty($metas) && is_array($metas)) {
		foreach ($metas as $meta => $display) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
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

/**
 * @param string $slug
 * @param string $link
 */
function ap_register_menu($slug, $title, $link){
	ap_append_to_global_var('ap_menu', $slug, array('title' => $title, 'link' => $link));
}
