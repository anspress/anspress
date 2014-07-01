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


// AnsPress options
function ap_opt($key = false, $value = false){

	$settings = get_option( 'anspress_opt');
	if($value){
		$settings[$key] = $value;
		update_option( 'anspress_opt', $settings);
		return;
	}	
	if(!$key)
		return $settings;
		
	if(isset($settings[$key]))
		return $settings[$key];
	
	return false;
}

function ap_theme_list(){
	$themes = array();
	$dirs = array_filter(glob(ANSPRESS_THEME_DIR.'/*'), 'is_dir');
	foreach($dirs as $dir)
		$themes[basename($dir)] = basename($dir);		
	return $themes;
}

function ap_get_theme(){	
	$option = ap_opt('theme');
	if(!$option)
		return 'default';
		
	return ap_opt('theme');
}

// get the location of the theme
function ap_get_theme_location($file){
	// checks if the file exists in the theme first,
	// otherwise serve the file from the plugin
	if ( $theme_file = locate_template( array( 'anspress/'.$file ) ) ) {
		$template_path = $theme_file;
	} else {
		$template_path = ANSPRESS_THEME_DIR .'/'.ap_get_theme().'/'.$file;
	}
	return $template_path;
}

// get the url theme
function ap_get_theme_url($file){
	// checks if the file exists in the theme first,
	// otherwise serve the file from the plugin
	if ( $theme_file = locate_template( array( 'anspress/'.$file ) ) ) {
		$template_url = get_template_directory().'/anspress/'.$file;
	} else {
		$template_url = ANSPRESS_THEME_URL .'/'.ap_get_theme().'/'.$file;
	}
	return $template_url;
}


//get current user id
function ap_current_user_id() {
	require_once(ABSPATH . WPINC . '/pluggable.php');
	global $current_user;
	get_currentuserinfo();
	return $current_user->ID;
}

function ap_question_content(){
	global $post;
	echo $post->post_content;
}

function ap_question_tags($list = true){
	global $post;
	if($list)
		$o =	get_the_term_list( $post->ID, ANSPRESS_TAG_TAX, '<ul class="question-tags"><li>', '</li><li>', '</li></ul>' );
	else 
		$o =	get_the_term_list( $post->ID, ANSPRESS_TAG_TAX, '', '', '' );
		
	echo $o;
}

function ap_question_categories($list = true){
	global $post;
	if($list)
		$o =	get_the_term_list( $post->ID, ANSPRESS_CAT_TAX, '<ul class="question-cats aicon-folder-close"><li>', '</li><li>', '</li></ul>' );
	else
		$o =	get_the_term_list( $post->ID, ANSPRESS_CAT_TAX, '', ', ', '' );
	echo $o;
}

function ap_human_time($time){
	return human_time_diff( $time, current_time('timestamp') );
}


function ap_please_login(){
	$o  = '<div id="please-login">';
	$o .= '<button>x</button>';
	$o .= __('Please login or register to continue this action.', 'ap');
	$o .= '</div>';
	
	echo apply_filters('ap_please_login', $o);
}

function ap_user_display_name($id = false){
	if(!$id)
		$id = get_the_author_meta('ID');
	
	if ($id > 0){
		$user = get_userdata($id);
		return '<span class="who">'.$user->display_name.'</span>';
	}
	
	return '<span class="who">'.__('Anonymous', 'ap').'</span>';
}
/* Check if a user can ask a question */
function ap_user_can_ask(){
	$is_loggedin = (is_user_logged_in()? true : (ap_opt('allow_non_loggedin') ? true : false));
	if(current_user_can('add_question') || is_super_admin() || $is_loggedin)
		return true;
	
	return false;
}

/* Check if a user can answer on a question */
function ap_user_can_answer($question_id){
	$is_loggedin = (is_user_logged_in()? true : (ap_opt('allow_non_loggedin') ? true : false));
	if(current_user_can('add_answer') || is_super_admin() || $is_loggedin ){
		if(!ap_opt('multiple_answers') && ap_is_user_answered($question_id, get_current_user_id()) && get_current_user_id() != '0')
			return false;
		else
			return true;
	}
	return false;
}

//check if user answered on a question
function ap_is_user_answered($question_id, $user_id){
	global $wpdb;
	
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $question_id AND ( post_author = $user_id AND post_type = 'answer')");
	if($count)	
		return true;	
	return false;
}

/* Check if a user can edit answer on a question */
function ap_user_can_edit_ans($post_id){
	if(current_user_can('edit_own_answer') || current_user_can('mod_question') || is_super_admin()){
		$post = get_post($post_id);
		global $current_user;
		$user_id		= $current_user->ID;
		if(($post->author_id ==  $user_id) || current_user_can('mod_question') || is_super_admin())
			return true;
		else
			return false;
	}
	return false;
}

function ap_user_can_edit_question($post_id = false){
	if(current_user_can('edit_own_answer') || current_user_can('mod_question') || is_super_admin()){
		global $current_user;
		if($post_id )
			$post = get_post($post_id);
		else
			global $post;

		if(is_super_admin() || current_user_can('mod_question') )
			return true;
			
		if(($current_user->ID == $post->post_author) && current_user_can('edit_own_question'))
			return true;
	}
	return false;
}

function ap_user_can_change_status(){
	if(is_super_admin() || current_user_can('mod_question'))
		return true;

	return false;
}

function ap_user_can_comment(){
	if(is_super_admin() || current_user_can('add_comment') || ap_opt('anonymous_comment'))
		return true;

	return false;
}
function ap_user_can_edit_comment($comment_id){
	if(is_super_admin() || current_user_can('mod_comment'))
		return true;
	
	global $current_user;	
	if( current_user_can('edit_comment') && ($current_user->ID == $comment_id))
		return true;

	return false;
}

function ap_user_can_delete_comment($comment_id){
	if(is_super_admin() || current_user_can('mod_comment'))
		return true;
	
	global $current_user;	
	if( current_user_can('delete_comment') && ($current_user->ID == $comment_id))
		return true;

	return false;
}


function ap_status_type(){
	$types = array('open', 'solved', 'closed', 'duplicate');
	return apply_filters('ap_status_types', $types);
}

function ap_set_question_status($post_id, $status = 'open'){
	$status = in_array( $status, ap_status_type()) ? $status : 'open';
	
	update_post_meta( $post_id, '_status', apply_filters('ap_set_status', $status));
}

function ap_get_question_status($post_id = NULL){
	if(!$post_id) $post_id = get_the_ID();
	$status = get_post_meta( $post_id, '_status', true );
	
	if(!strlen($status))
		return 'open';
	
	return $status;
}

// count numbers of answers
function ap_count_ans($id){
		
	global $wpdb;
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $id AND post_status = 'publish' AND post_type = 'answer'");

	return $count;
}

//check if current questions have answers
function ap_have_ans($id){
	
	if(ap_count_ans($id) > 0)
		return true;	
	
	return false;
}

// link to asnwers
function ap_answers_link(){
	return get_permalink().'#answers';
}

function ap_comment_btn_html(){
	$action = get_post_type(get_the_ID()).'-'.get_the_ID();
	$nonce = wp_create_nonce( $action );
	echo '<a href="#comments-'.get_the_ID().'" class="comment-btn" data-form="comment" data-args="'. get_the_ID().'-'. $nonce .'" title="'.__('Add comment', 'ap').'">'.__('Comment', 'ap').'</a>';
}


function ap_question_edit_link($post_id){

	if(ap_user_can_edit_question($post_id)){		
		$action = get_post_type($post_id).'-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_q' => $post_id, 'ap_nonce' => $nonce), get_permalink( ap_opt('base_page')) );
		
		return apply_filters( 'ap_question_edit_link', $edit_link );
	}
	return;
}
function ap_edit_a_btn_html(){
	$post_id = get_edit_answer_id();
	if(ap_user_can_edit_ans($post_id)){		
		$action = get_post_type($post_id).'-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_a' => $post_id, 'ap_nonce' => $nonce), get_permalink( ap_opt('a_edit_page')) );
		
		echo '<a href="'.$edit_link.'" class="btn btn-xs edit-btn aicon-edit" title="'.__('Edit Answer', 'ap').'">'.__('Edit', 'ap').'</a>';
	}
	return;
}
function ap_answer_edit_link(){
	$post_id = get_the_ID();
	if(ap_user_can_edit_ans($post_id)){		
		$action = get_post_type($post_id).'-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_a' => $post_id, 'ap_nonce' => $nonce), get_permalink( ap_opt('base_page')) );
		return apply_filters( 'ap_answer_edit_link', $edit_link );
	}
	return;
}

function ap_change_status_btn_html($post_id = null){
	if(!$post_id)
		$post_id = get_the_ID();
		
	if(ap_user_can_change_status() && get_post_type($post_id) == 'question'){		
		$action = get_post_type($post_id).'-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$status = ap_get_question_status($post_id);
		echo '<ul id="change-status" class="btn-group">';
			echo '<li>';
				echo '<a data-action="change-status" href="#" class="dropdown-toggle status-btn '.$status.'" title="'.__('Current status of question', 'ap').'" data-toggle="dropdown">'.$status.'</a>';
				echo '<ul class="dropdown-menu">';
					foreach (ap_status_type() as $type){
						if ($status != $type )
							echo '<li><a data-action="set-status" href="#" data-args="'.$post_id.'-'.$nonce.'-'.$type.'" class="'.$type.'" title="'.__('Mark as ','ap').$type.'">'.$type.'</a></li>';
					}
				echo '</ul>';
			echo '</li>';
		echo '</ul>';
	}
	return;
}

function ap_truncate_chars($text, $limit, $ellipsis = '...') {
    if( strlen($text) > $limit ) {
        $endpos = strpos(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $text), ' ', $limit);
        if($endpos !== FALSE)
            $text = trim(substr($text, 0, $endpos)) . $ellipsis;
    }
    return $text;
}


function ap_child_cat_list($parent){
	$categories = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $parent, 'hide_empty' => false ));
	
	if($categories){
		echo '<ul class="child clearfix">';	
		foreach	($categories as $cat){
			echo '<li><a href="'.get_category_link( $cat ).'">' .$cat->name.'<span>'.$cat->count.'</span></a></li>';
		}
		echo '</ul>';
	}
}


function ap_category_details(){
	$var = get_query_var('question_category');

	$category = get_term_by('slug', $var, 'question_category');

	echo '<div class="clearfix">';
	echo '<h3><a href="'.get_category_link( $category ).'">'. $category->name .'</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">'. $category->count .' '.__('Questions', 'ap').'</span>';	
	echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($category->term_id, 'question_category') . '" title="Subscribe to '. $category->name .'" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';
	
	echo '<p class="desc clearfix">'. $category->description .'</p>';
	
	$child = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $category->term_id, 'hierarchical' => false, 'hide_empty' => false )); 
				   
	if($child) : 
		echo '<ul class="ap-child-list clearfix">';
			foreach($child as $key => $c) :
				echo '<li><a class="taxo-title" href="'.get_category_link( $c ).'">'.$c->name.'<span>'.$c->count.'</span></a>';
				echo '</li>';
			endforeach;
		echo'</ul>';
	endif;	
}


function ap_tag_details(){

	$var = get_query_var('question_tags');

	$tag = get_term_by('slug', $var, 'question_tags');
	echo '<div class="clearfix">';
	echo '<h3><a href="'.get_category_link( $tag ).'">'. $tag->name .'</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">'. $tag->count .' '.__('Questions', 'ap').'</span>';	
	echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($tag->term_id, 'question_category') . '" title="Subscribe to '. $tag->name .'" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';
	
	echo '<p class="desc clearfix">'. $tag->description .'</p>';
}


function ap_get_all_users(){
	$paged 			= (get_query_var('paged')) ? get_query_var('paged') : 1;
	$per_page    	= ap_opt('tags_per_page');
	$total_terms 	= wp_count_terms('question_tags'); 	
	$offset      	= $per_page * ( $paged - 1) ;
	
	$args = array(
		'number'		=> $per_page,
		'offset'       	=> $offset
	);
	
	$users = get_users( $args); 
		
	echo '<ul class="ap-tags-list">';
		foreach($users as $key => $user) :

			echo '<li>';
			echo $user->display_name;			
			echo '</li>';

		endforeach;
	echo'</ul>';
	
	ap_pagination(ceil( $total_terms / $per_page ), $range = 1, $paged);
}
function is_anspress(){
	$queried_object = get_queried_object();
	if( $queried_object->ID ==  ap_opt('base_page'))
		return true;
		
	return false;
}
function is_question(){
	if(is_anspress() && get_query_var('question_id') || get_query_var('question'))
		return true;
		
	return false;
}
function get_question_id(){
	if(is_question() && get_query_var('question_id'))
		$id = get_query_var('question_id');
	elseif(is_question() && get_query_var('question'))
		$id = get_query_var('question');
	return $id;
	
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
function is_question_tag(){
	if(is_anspress() && get_query_var('qtag_id'))
		return true;
		
	return false;
}
function get_question_tag_id(){
	if(is_question_tag())
		return get_query_var('qtag_id');
		
	return false;
}
function is_question_cat(){
	if(is_anspress() && get_query_var('qcat_id'))
		return true;
		
	return false;
}
function get_question_cat_id(){
	if(is_question_cat())
		return get_query_var('qcat_id');
		
	return false;
}
function is_question_edit(){
	if(is_anspress() && get_query_var('edit_q'))
		return true;
		
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
function get_edit_answer_id(){
	if(is_anspress() && get_query_var('edit_a'))
		return get_query_var('edit_a');
		
	return false;
}
function ap_get_current_page_template(){

	if(is_anspress()){
		
		if(is_question())
			$template = 'question.php';
		elseif(is_ask())
			$template = 'ask.php';
		elseif(is_question_categories())
			$template = 'categories.php';
		elseif(is_question_tags())
			$template = 'tags.php';
		elseif(is_question_tag())
			$template = 'tag.php';
		elseif(is_question_cat())
			$template = 'category.php';
		elseif(is_question_edit())
			$template = 'edit-question.php';
		elseif(is_answer_edit())
			$template = 'edit-answer.php';
		else
			$template = 'base.php';
		
		return $template;
	}
	return 'content-none.php';
}

function ap_editor_content($content){
	wp_editor( apply_filters('the_content', $content), 'post_content', array('media_buttons' => false, 'quicktags' => false, 'textarea_rows' => 5, 'teeny' => true, 'statusbar' => false)); 
	remove_filter('the_content', $post->post_content);
}



function ap_base_page_slug(){
	$base_page_slug = ap_opt('base_page_slug');
	
	// get the base slug, if base page was set to home page then dont use any slug
	$slug = ((ap_opt('base_page') !== get_option('page_on_front')) ? $base_page_slug.'/' : '');
	
	return apply_filters('ap_base_page_slug', $slug) ;
}

function ap_ans_query($question_id, $order = 'voted'){
	if(is_question()){
		$order = get_query_var('sort');
		if(empty($order ))
			$order = 'voted';
	}
	
	if($order == 'voted'){
		$ans_args=array(
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => -1,
			'orderby' => 'meta_value_num',
			'meta_key' => ANSPRESS_VOTE_META,
		);
	}elseif($order == 'oldest'){
		$ans_args=array(
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => -1,
			'orderby' => 'date',
			'order' => 'ASC'
		);
	}else{
		$ans_args=array(
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => -1,
			'orderby' => 'date',
			'order' => 'DESC'
		);
	}
	return new WP_Query($ans_args);
}

function ap_ans_tab(){
$order = get_query_var('sort');
if(empty($order ))
	$order = 'voted';
	
	$link = get_permalink( get_the_ID() ).'?sort=';
?>
	<ul class="nav nav-tabs" role="tablist">
		<li><h2 class="ap-answer-count"><span><?php echo ap_count_ans(get_the_ID()); ?></span> <?php _e('Answers', 'ap'); ?></h2></li>
		<li class="pull-right<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
		<li class="pull-right<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
		<li class="pull-right<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
	</ul>
<?php
}