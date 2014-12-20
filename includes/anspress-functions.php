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
	$settings = wp_cache_get('ap_opt', 'options');
	
	if($settings === false){
		$settings = get_option( 'anspress_opt');
		if(!$settings)
			$settings = array();
		$settings = $settings + ap_default_options();
		
		wp_cache_set('ap_opt', $settings, 'options');
	}	
	if($value){
		$settings[$key] = $value;
		update_option( 'anspress_opt', $settings);
		return;
	}	
	if(!$key)
		return $settings;
		
	if(isset($settings[$key]))
		return $settings[$key];
	else
		return NULL;
	
	return false;
}

function ap_default_options(){
	$ap_options = get_option( 'anspress_opt');
	$page = get_page($ap_options['base_page']);
	return array(
		'base_page' 			=> get_option('ap_base_page_created'),
		'base_page_slug' 		=> $page->post_name,
		'custom_signup_url'		=> '',
		'custom_login_url'		=> '',
		'show_login_signup' 	=> true,
		'show_login' 			=> true,
		'show_signup' 			=> true,
		'double_titles'			=> false,
		'show_social_login'		=> false,
		'theme' 				=> 'default',
		'author_credits' 		=> false,
		'clear_database' 		=> false,
		'minimum_qtitle_length'	=> 3,
		'minimum_question_length'=> 5,
		'multiple_answers' 		=> false,
		'minimum_ans_length' 	=> 5,
		'avatar_size_qquestion' => 30,
		'can_private_question'	=> false,
		'avatar_size_qanswer' 	=> 30,
		'avatar_size_qcomment' 	=> 25,
		'down_vote_points' 		=> -1,
		'flag_note' 			=> array(0 => array('title' => 'it is spam', 'description' => 'This question is effectively an advertisement with no disclosure. It is not useful or relevant, but promotional.')),			
		'bootstrap' 			=> true,
		'question_per_page' 	=> '20',
		'answers_per_page' 		=> '5',
		'tags_per_page' 		=> '20',
		
		'answers_sort' 			=> 'voted',
		
		'base_page_title' 		=> 'AnsPress - Question and answer plugin',
		'ask_page_title' 		=> 'Ask a question',
		'categories_page_title' => 'AnsPress Categories',
		'tags_page_title' 		=> 'AnsPress Tags',
		'users_page_title' 		=> 'AnsPress users',
		'search_page_title' 	=> 'Search result for %s',
		
		'close_selected' 		=> true,
		'enable_tags' 			=> true,
		'max_tags'				=> 5,
		
		'enable_categories'		=> true,
		'cover_width'			=> '878',
		'cover_height'			=> '200',
		'default_rank'			=> '0',
		'users_per_page'		=> 15,
		'cover_width_small'		=> 275,
		'cover_height_small'	=> 80,
		'followers_limit'		=> 10,
		'following_limit'		=> 10,
		'captcha_ask'			=> true,
		'captcha_answer'		=> true,
		'moderate_new_question'	=> 'no_mod',
		'mod_question_point'	=> 10,
		'categories_per_page'	=> 20,
		'question_prefix'		=> 'question',
		'min_point_new_tag'		=> 100,
		'min_tags'				=> 2,
		'allow_anonymous'		=> false,
		'enable_captcha_skip'	=> false,
		'captcha_skip_rpoints'	=> 40,
		'only_admin_can_answer'	=> false,
		'logged_in_can_see_ans'	=> false,
		'logged_in_can_see_comment'	=> false
	);
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
		$template_url = get_template_directory_uri().'/anspress/'.$file;
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



function ap_human_time($time, $unix = true){
	if(!$unix)
		$time = strtotime($time);
		
	return human_time_diff( $time, current_time('timestamp') );
}


function ap_please_login(){
	$o  = '<div id="please-login">';
	$o .= '<button>x</button>';
	$o .= __('Please login or register to continue this action.', 'ap');
	$o .= '</div>';
	
	echo apply_filters('ap_please_login', $o);
}

//check if user answered on a question
function ap_is_user_answered($question_id, $user_id){
	global $wpdb;
	
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $question_id AND ( post_author = $user_id AND post_type = 'answer')");
	if($count)	
		return true;	
	return false;
}


// count numbers of answers
function ap_count_ans($id){
		
	global $wpdb;
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $id AND post_status = 'publish' AND post_type = 'answer'");

	return $count;
}

function ap_count_ans_meta($post_id =false){
	if(!$post_id) $post_id = get_the_ID();
	$count = get_post_meta($post_id, ANSPRESS_ANS_META, true);
	 return $count ? $count : 0;
}

function ap_last_active($post_id =false){
	if(!$post_id) $post_id = get_the_ID();
	return get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
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

function ap_get_link_to($sub){
	
	$base = rtrim(get_permalink(ap_opt('base_page')), '/');

	
	if(get_option('permalink_structure') != ''){
		
		if(!is_array($sub))
			$args = $sub ? '/'.$sub : '';
		elseif(is_array($sub)){
			$args = '/';
			if(!empty($sub))
				foreach($sub as $s)
					$args .= $s.'/';
		}

		$link = $base;		
	}else{
		if(!is_array($sub))
			$args = $sub ? '&ap_page='.$sub : '';
		elseif(is_array($sub)){
			$args = '';
			if(!empty($sub))
				foreach($sub as $k => $s)
					$args .= '&'.$k .'='.$s;
		}
		$link = $base;
	}
	return $link. $args ;
}

function ap_comment_btn_html(){
	if(ap_user_can_comment()){
		$action = get_post_type(get_the_ID()).'-'.get_the_ID();
		$nonce = wp_create_nonce( $action );
		echo '<a href="#ap-comment-area-'.get_the_ID().'" class="comment-btn" data-action="ap-load-comment" data-args="'. get_the_ID().'-'. $nonce .'" title="'.__('Add comment', 'ap').'">'.__('Comment', 'ap').'</a>';
	}
}
function ap_edit_q_btn_html(){
	$post_id = get_the_ID();
	if(ap_user_can_edit_question($post_id)){		
		$action = 'question-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_q' => $post_id, 'nonce' => $nonce), get_permalink( ap_opt('base_page')) );
		//$args = json_encode(array('action' => 'ap_load_edit_form', 'id'=> $post_id, 'nonce' => $nonce, 'type' => 'question'));
		echo "<a href='$edit_link' data-button='ap-edit-post' title='".__('Edit this question', 'ap')."' class='apEditBtn ".ap_icon('edit')."'><span>".__('Edit', 'ap')."</span></a>";
	}
	return;
}

function ap_edit_a_btn_html(){
	if(!is_user_logged_in())
		return;
		
	$post_id = get_edit_answer_id();
	if(ap_user_can_edit_ans($post_id)){		
		$edit_link = ap_answer_edit_link();
		echo "<a href='$edit_link.' class='edit-btn aicon-edit' data-button='ap-edit-post' title='".__('Edit Answer', 'ap')."'>".__('Edit', 'ap')."</a>";
	}
	return;
}

function ap_post_edited_time() {
	if (get_the_time('s') != get_the_modified_time('s')){
		printf('<span class="edited-text">%1$s</span> <span class="edited-time">%2$s</span>',
		__('Edited on','ap'),
		get_the_modified_time()
		);
	
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

function ap_truncate_chars($text, $limit, $ellipsis = '...') {
    if( strlen($text) > $limit ) {
        $endpos = strpos(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $text), ' ', $limit);
        if($endpos !== FALSE)
            $text = trim(substr($text, 0, $endpos)) . $ellipsis;
    }
    return $text;
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

function ap_base_page_slug(){
	$base_page_slug = ap_opt('base_page_slug');
	
	// get the base slug, if base page was set to home page then dont use any slug
	$slug = ((ap_opt('base_page') !== get_option('page_on_front')) ? $base_page_slug.'/' : '');
	
	$base_page = get_post(ap_opt('base_page'));
	
	if( $base_page->post_parent != 0 ){
		$parent_page = get_post($base_page->post_parent);
		$slug = $parent_page->post_name . '/'.$slug;
	}
	
	return apply_filters('ap_base_page_slug', $slug) ;
}

function ap_answers_list($question_id, $order = 'voted'){

	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

	if(is_question()){
		$order = get_query_var('sort');
		if(empty($order ))
			$order = ap_opt('answers_sort');
	}
	
	if($order == 'voted'){
		$ans_args=array(
			'ap_query' => 'answer_sort_voted',
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => ap_opt('answers_per_page'),
			'paged' => $paged,
			'orderby' => 'meta_value_num',
			'meta_key' => ANSPRESS_VOTE_META,
			'meta_query'=>array(
				'relation' => 'OR',
				array(
					'key' => ANSPRESS_BEST_META,
					'compare' => '=',
					'value' => '1'
				),
				array(
					'key' => ANSPRESS_BEST_META,
					//'compare' => 'NOT EXISTS'
				)
			)
		);
	}elseif($order == 'oldest'){
		$ans_args=array(
			'ap_query' => 'answer_sort_newest',
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => ap_opt('answers_per_page'),
			'paged' => $paged,
			'orderby' => 'meta_value date',
			'meta_key' => ANSPRESS_BEST_META,
			'order' => 'ASC',
			'meta_query'=>array(
				'relation' => 'OR',
				array(
					'key' => ANSPRESS_BEST_META,
					//'compare' => 'NOT EXISTS'
				)
			)
		);
	}else{
		$ans_args=array(
			'ap_query' => 'answer_sort_newest',
			'post_type' => 'answer',
			'post_status' => 'publish',
			'post_parent' => get_the_ID(),
			'showposts' => ap_opt('answers_per_page'),
			'paged' 	=> $paged,			
			'orderby' 	=> 'meta_value date',
			'meta_key' => ANSPRESS_BEST_META,
			'order' 	=> 'DESC',
			'meta_query'=>array(
				'relation' => 'OR',
				array(
					'key' => ANSPRESS_BEST_META,
					//'compare' => 'NOT EXISTS'
				)
			)
		);
	}
	
	$ans_args = apply_filters('ap_answers_query_args', $ans_args);
	
	$ans = new WP_Query($ans_args);	
	
	// get answer sorting tab
	echo '<div id="answers-c">';
		if(ap_user_can_see_answers()){
			ap_ans_tab(); 	
			echo '<div id="answers">';
				while ( $ans->have_posts() ) : $ans->the_post(); 
					include(ap_get_theme_location('answer.php'));
				endwhile ;
			echo '</div>';	
			ap_pagination('', 2, $paged, $ans);
		}else{
			echo '<div class="ap-login-to-see-ans">'.sprintf(__('Please %s or %s to view answers and comments', 'ap'), '<a class="ap-open-modal ap-btn" title="Click here to login if you already have an account on this site." href="#ap_login_modal">Login</a>', '<a class="ap-open-modal ap-btn" title="Click here to signup if you do not have an account on this site." href="#ap_signup_modal">Sign Up</a>').'</div>';
			echo do_action('ap_after_answer_form');
		}
	echo '</div>';
	wp_reset_query();
}

function ap_ans_tab(){
	$order = get_query_var('sort');
	if(empty($order ))
		$order = ap_opt('answers_sort');
		
		$link = '?sort=';
		$ans_count = ap_count_ans(get_the_ID());
	?>
		<div class="ap-anstabhead clearfix">
			<h2 class="ap-answer-count pull-left" data-view="ap-answer-count-label"><?php printf(_n('<span>1 Answer</span>', '<span>%d Answers</span>', $ans_count, 'ap'), $ans_count); ?><span itemprop="answerCount" style="display:none;"><?php echo $ans_count; ?></span></h2>
			<ul class="ap-ans-tab ap-tabs clearfix" role="tablist">
				<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
				<li class="<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
				<li class="<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
			</ul>
		</div>
	<?php
}

function ap_ans_list_tab(){
	$order = get_query_var('sort');
	if(empty($order ))
		$order = ap_opt('answers_sort');
		
		$link = '?sort=';
		$ans_count = ap_count_ans(get_the_ID());
	?>
		<ul class="ap-ans-tab ap-tabs clearfix" role="tablist">
			<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
		</ul>
	<?php
}

function ap_questions_tab(){
	$order = get_query_var('sort');
	$label = sanitize_text_field(get_query_var('label'));
	$search_q = sanitize_text_field(get_query_var('ap_s'));
	if(empty($order ))
		$order = 'active';//ap_opt('answers_sort');
	
	if(empty($status ))
		$status = '';
	
	$search = '';
	if(empty($status ))
		$search = 'ap_s='.$search_q.'&';
		
	$link = '?'.$search.'sort=';
	$label_link = '?'.$search.'sort='.$order.'&label=';
	
	?>
	<div class="ap-lists-tab clearfix">
		<ul class="ap-tabs clearfix" role="tablist">			
			<li class="<?php echo $order == 'active' ? ' active' : ''; ?>"><a href="<?php echo $link.'active'; ?>"><?php _e('Active', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>			
			<li class="<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'answers' ? ' active' : ''; ?>"><a href="<?php echo $link.'answers'; ?>"><?php _e('Most answered', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'unanswered' ? ' active' : ''; ?>"><a href="<?php echo $link.'unanswered'; ?>"><?php _e('Unanswered', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'unsolved' ? ' active' : ''; ?>"><a href="<?php echo $link.'unsolved'; ?>"><?php _e('Unsolved', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>			
		</ul>
		<div class="pull-right">
			<div class="ap_status ap-dropdown">
				<a href="#" class="btn ap-btn ap-dropdown-toggle"><?php _e('Label', 'ap'); ?> &#9662;</a>
				<ul class="ap-dropdown-menu">
					<?php
						$labels = get_terms('question_label', array('orderby'=> 'name','hide_empty'=> true));
						foreach($labels as $l){
							$color = ap_get_label_color($l->term_id);
							echo '<li'. ($label == $l->slug ? ' class="active" ' : '') .'><a href="'.$label_link.$l->slug.'" title="'.$l->description.'"><span class="question-label-color" style="background:'.$color.'"> </span>'.$l->name.'</a></li>';
						}
					?>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

function ap_untrash_post( $post_id ) {
    // no post?
    if( !$post_id || !is_numeric( $post_id ) ) {
        return false;
    }
    $_wpnonce = wp_create_nonce( 'untrash-post_' . $post_id );
    $url = admin_url( 'post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce );
    return $url; 
}

function ap_user_can($id){
	get_user_meta( $id, 'ap_role', true );
}

function ap_question_tab(){
	?>
		<ul class="ap-tabs ap-question-tab clearfix" data-action="ap-tabs" role="tablist">
			<li class="active"><a href="#discussion"><?php _e('Discussion', 'ap'); ?></a></li>
			<li><a href="#history"><?php _e('History', 'ap'); ?></a></li>
			<li><a href="#related"><?php _e('Related', 'ap'); ?></a></li>
		</ul>
	<?php
}

function ap_selected_answer($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	return get_post_meta($post_id, ANSPRESS_SELECTED_META, true);
}


function ap_is_answer_selected($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	$meta = get_post_meta($post_id, ANSPRESS_SELECTED_META, true);

	if(!$meta)
		return false;
	
	return true;
}

function ap_is_best_answer($post_id = false){
	if(!$post_id){
		$post_id = get_the_ID();
	}
	$meta = get_post_meta($post_id, ANSPRESS_BEST_META, true);
	if($meta) return true;
		
	return false;
}

function ap_select_answer_btn_html($post_id){
	if(!ap_user_can_select_answer($post_id))
		return;
	
	$ans = get_post($post_id);
	$action = 'answer-'.$post_id;
	$nonce = wp_create_nonce( $action );	
	
	if(!ap_is_answer_selected($ans->post_parent)){		
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon('tick').' ap-tip" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Select this answer as best', 'ap').'"></a>';
		
	}elseif(ap_is_answer_selected($ans->post_parent) && ap_is_best_answer($ans->ID)){
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon('tick').' selected ap-tip" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Unselect this answer', 'ap').'"></a>';
		
	}
}

function ap_post_delete_btn_html($post_id = false){
	if(!$post_id){
		$post_id = get_the_ID();
	}
	if(ap_user_can_delete($post_id)){		
		$action = 'delete_post_'.$post_id;
		$nonce = wp_create_nonce( $action );
		
		echo '<a href="#" class="delete-btn" data-button="ap-delete-post" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Delete', 'ap').'">'.__('Delete', 'ap').'</a>';
	}
}

function ap_get_child_answers_comm($post_id){
	global $wpdb;
	$ids = array();
	
	$query = "SELECT p.ID, c.comment_ID from $wpdb->posts p LEFT JOIN $wpdb->comments c ON c.comment_post_ID = p.ID OR c.comment_post_ID = $post_id WHERE post_parent = $post_id";
	
	$key = md5($query);	
	$cache = wp_cache_get($key, 'count');
	
	if($cache === false){
		$cols = $wpdb->get_results( $query, ARRAY_A);
		wp_cache_set($key, $cols, 'count');
	}else
		$cols = $cache;
	
	
	if($cols){
		foreach($cols as $c){
			if(!empty($c['ID']))
				$ids['posts'][] = $c['ID'];
			
			if(!empty($c['comment_ID']))
				$ids['comments'][] = $c['comment_ID'];
		}
	}else{
		return false;
	}
	
	if(isset($ids['posts']))
		$ids['posts']= array_unique ($ids['posts']);
	
	if(isset($ids['comments']))
		$ids['comments'] = array_unique ($ids['comments']);

	return $ids;
}

function ap_short_num($num, $precision = 2) {
	if ($num >= 1000 && $num < 1000000) {
		$n_format = number_format($num/1000,$precision).'K';
	} else if ($num >= 1000000 && $num < 1000000000) {
		$n_format = number_format($num/1000000,$precision).'M';
	} else if ($num >= 1000000000) {
		$n_format=number_format($num/1000000000,$precision).'B';
	} else {
		$n_format = $num;
	}
	return $n_format;
}

function sanitize_comma_delimited($str){
	return implode(",", array_map("intval", explode(",", $str)));
}

function ap_pagi($base, $total_pages, $paged, $end_size = 1, $mid_size = 5){
	$pagi_a = paginate_links( array(
		'base' => $base, // the base URL, including query arg
		'format' => 'page/%#%', // this defines the query parameter that will be used, in this case "p"
		'prev_text' => __('&laquo; Previous', 'ap'), // text for previous page
		'next_text' => __('Next &raquo;', 'ap'), // text for next page
		'total' => $total_pages, // the total number of pages we have
		'current' => $paged, // the current page
		'end_size' => 1,
		'mid_size' => 5,
		'type' => 'array'
	));
	if($pagi_a){
		echo '<ul class="ap-pagination clearfix">';
			echo '<li><span class="page-count">'. sprintf(__('Page %d of %d', 'ap'), $paged, $total_pages) .'</span></li>';
			foreach($pagi_a as $pagi){
				echo '<li>'. $pagi .'</li>';
			}
		echo '</ul>';
	}
}

function ap_question_side_tab(){
	$links = array (
		'discussion' => array('icon' => 'ap-icon-flow-tree', 'title' => __('Discussion', 'ap'), 'url' => '#discussion')
	);
	$links = apply_filters('ap_question_tab', $links);
	$i = 1;
	if(count($links) > 1){
		echo '<ul class="ap-question-extra-nav" data-action="ap-tab">';
			foreach($links as $link){
				echo '<li'.($i == 1 ? ' class="active"' : '').'><a class="'.$link['icon'].'" href="'.$link['url'].'">'.$link['title'].'</a></li>';
				$i++;
			}
		echo '</ul>';
	}
}

function ap_read_features($type = 'addon'){
	$option = get_option('ap_addons');
	$cache = wp_cache_get('ap_'.$type.'s_list', 'array');
	
	if($cache !== FALSE)
		return $cache;
		
	$features = array();
	//load files from addons folder
	$files=glob(ANSPRESS_DIR.'/'.$type.'s/*/'.$type.'.php');
	//print_r($files);
	foreach ($files as $file){
		$data = ap_get_features_data($file);
		$data['folder'] = basename(dirname($file));
		$data['file'] = basename($file);
		$data['active'] = (isset($option[$data['name']]) && $option[$data['name']]) ? true : false;
		$features[$data['name']] = $data;
	}
	wp_cache_set( 'ap_'.$type.'s_list', $features, 'array');
	return $features;
}


function ap_get_features_data( $plugin_file) {
	$plugin_data = ap_get_file_data( $plugin_file);

	return $plugin_data;
}

function ap_get_file_data( $file) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 1000 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	$metadata=ap_features_metadata($file_data, array(
		'name' 				=> 'Name',
		'version' 			=> 'Version',
		'description' 		=> 'Description',
		'author' 			=> 'Author',
		'author_uri' 		=> 'Author URI',
		'addon_uri' 		=> 'Addon URI'
	));

	return $metadata;
}

function ap_features_metadata($contents, $fields){
	$metadata=array();

	foreach ($fields as $key => $field)
		if (preg_match('/'.str_replace(' ', '[ \t]*', preg_quote($field, '/')).':[ \t]*([^\n\f]*)[\n\f]/i', $contents, $matches))
			$metadata[$key]=trim($matches[1]);
	
	return $metadata;
}

function ap_users_tab(){
	$order = get_query_var('sort');
	
	if(empty($order ))
		$order = 'points';//ap_opt('answers_sort');
	
	$link = '?sort=';

	
	?>
	<div class="ap-lists-tab clearfix">
		<ul class="ap-tabs clearfix" role="tablist">			
			<li class="<?php echo $order == 'points' ? ' active' : ''; ?>"><a href="<?php echo $link.'points'; ?>"><?php _e('Points', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>			
		</ul>
	</div>
	<?php
}

function ap_base_page_main_query($parent_id = false){
	$order = get_query_var('sort');
	$label = sanitize_text_field(get_query_var('label'));
	if(empty($order ))
		$order = 'active';//ap_opt('answers_sort');
		
	if(empty($label ))
		$label = '';
		
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
	$question_args=array(
		'ap_query' 		=> 'main_questions',
		'post_type' 	=> 'question',
		'post_status' 	=> array('publish', 'moderate', 'private_question', 'closed'),
		'showposts' 	=> ap_opt('question_per_page'),
		'paged' 		=> $paged,
	);
	
	if($parent_id !== false)
		$question_args['post_parent'] 	= $parent_id;
	
	if(get_query_var('parent'))
		$question_args['post_parent'] 	= get_query_var('parent');
	
	if($order == 'active'){				
		$question_args['ap_query'] 		= 'main_questions_active';
		$question_args['orderby'] 		= 'meta_value';
		$question_args['meta_key'] 		= ANSPRESS_UPDATED_META;
		$question_args['meta_query'] 	= array(
			'relation' => 'OR',
			array(
				'key' => ANSPRESS_UPDATED_META,
				//'compare' => 'NOT EXISTS',
			),
		);	
		
	}elseif($order == 'voted'){
		$question_args['orderby'] 		= 'meta_value_num';
		$question_args['meta_key'] 		= ANSPRESS_VOTE_META;
	}elseif($order == 'answers'){
		$question_args['orderby'] 		= 'meta_value_num';
		$question_args['meta_key'] 		= ANSPRESS_ANS_META;
	}elseif($order == 'unanswered'){
		$question_args['orderby'] 		= 'meta_value';
		$question_args['meta_key'] 		= ANSPRESS_ANS_META;
		$question_args['meta_value'] 	= '0';

	}elseif($order == 'unsolved'){
		$question_args['orderby'] 		= 'meta_value';
		$question_args['meta_key'] 		= ANSPRESS_SELECTED_META;
		$question_args['meta_compare'] 	= 'NOT EXISTS';
	}elseif($order == 'oldest'){
		$question_args['orderby'] 		= 'date';
		$question_args['order'] 		= 'ASC';
	}
	
	if ($label != ''){
		$question_args['tax_query'] = array(
			array(
				'taxonomy' => 'question_label',
				'field' => 'slug',
				'terms' => $label
			)
		);				
	}
	
	return apply_filters('ap_main_query_args', $question_args);
			
}

function ap_qa_on_post($post_id = false){
	if(is_anspress())
		return false;
	
	wp_enqueue_style( 'ap-style', ap_get_theme_url('css/ap.css'), array(), AP_VERSION);
	
	if(!$post_id)
		$post_id = get_the_ID();
	
	$question_args = ap_base_page_main_query($post_id);
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
	$question = new WP_Query( $question_args );
	echo '<div class="anspress">';
	echo '<div class="ap-container">';
	include ap_get_theme_location('on-post.php');
	wp_reset_postdata();
	echo '<a href="'.ap_get_link_to('parent/'.get_the_ID()).'" class="ap-view-all">'.__( 'View All', 'ap' ).'</a>';
	echo '</div>';
	echo '</div>';	
}

function ap_ask_btn($parent_id = false){
	$args = array('ap_page' => 'ask');
	
	if($parent_id !== false)
		$args['parent'] = $parent_id;
	
	if(get_query_var('parent') != '')
		$args['parent'] = get_query_var('parent');
	
	echo '<a class="ap-btn ap-ask-btn-head pull-right" href="'.ap_get_link_to($args).'">'.__('Ask Question').'</a>';
}

function ap_icon($name){
	$icons = array(
		'follow' 			=> 'ap-icon-plus',
		'unfollow' 			=> 'ap-icon-minus',
		'upload' 			=> 'ap-icon-upload',
		'unchecked' 		=> 'ap-icon-checkbox-unchecked',
		'checked' 			=> 'ap-icon-checkbox-checked',
		'tick' 				=> 'ap-icon-tick',
		'new_question' 		=> 'ap-icon-question',
		'new_answer' 		=> 'ap-icon-answer',
		'new_comment' 		=> 'ap-icon-comment',
		'new_comment_answer'=> 'ap-icon-comment',
		'edit_question' 	=> 'ap-icon-pencil',
		'edit_answer' 		=> 'ap-icon-pencil',
		'edit_comment' 		=> 'ap-icon-pencil',
	);
	
	$icons = apply_filters('ap_icon', $icons);
	
	if(isset($icons[$name]))
		return $icons[$name];
		
	return '';
}