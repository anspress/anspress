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
		$settings = get_option( 'anspress_opt') + ap_default_options();
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
		'allow_non_loggedin' 	=> true,
		'show_login' 			=> true,
		'show_signup' 			=> true,
		'login_after_signup' 	=> true,
		'theme' 				=> 'default',
		'author_credits' 		=> false,
		'clear_databse' 		=> false,
		'minimum_qtitle_length'	=> 3,
		'minimum_question_length'=> 5,
		'multiple_answers' 		=> false,
		'minimum_ans_length' 	=> 5,
		'avatar_size_question' 	=> '40',
		'comment_avatar_size' 	=> '30',
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
		
		'close_selected' 		=> true,
		'enable_tags' 			=> true,
		'max_tags'				=> 5,
		
		'enable_categories'		=> true
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

function ap_comment_btn_html(){
	$action = get_post_type(get_the_ID()).'-'.get_the_ID();
	$nonce = wp_create_nonce( $action );
	echo '<a href="#ap-comment-area-'.get_the_ID().'" class="comment-btn" data-action="ap-load-comment" data-args="'. get_the_ID().'-'. $nonce .'" title="'.__('Add comment', 'ap').'">'.__('Comment', 'ap').'</a>';
}
function ap_edit_q_btn_html(){
	$post_id = get_the_ID();
	if(ap_user_can_edit_question($post_id)){		
		$action = 'question-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_q' => $post_id, 'nonce' => $nonce), get_permalink( ap_opt('base_page')) );
		//$args = json_encode(array('action' => 'ap_load_edit_form', 'id'=> $post_id, 'nonce' => $nonce, 'type' => 'question'));
		echo "<a href='$edit_link' data-button='ap-edit-post' title='".__('Edit this question', 'ap')."'>".__('Edit', 'ap')."</a>";
	}
	return;
}

function ap_edit_a_btn_html(){
	$post_id = get_edit_answer_id();
	if(ap_user_can_edit_ans($post_id)){		
		$action = 'answer-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array('edit_a' => $post_id, 'ap_nonce' => $nonce), get_permalink( ap_opt('a_edit_page')) );
		$args = json_encode(array('action' => 'ap_load_edit_form', 'id'=> $post_id, 'nonce' => $nonce, 'type' => 'answer'));
		echo "<a href='$edit_link.' class='btn btn-xs edit-btn aicon-edit' data-button='ap-edit-post' data-args='$args' title='".__('Edit Answer', 'ap')."'>".__('Edit', 'ap')."</a>";
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


function ap_editor_content($content){
	wp_editor( apply_filters('the_content', $content), 'post_content', array('media_buttons' => false, 'quicktags' => false, 'textarea_rows' => 6, 'teeny' => true, 'statusbar' => false)); 
	remove_filter('the_content', $content);
}

function ap_base_page_slug(){
	$base_page_slug = ap_opt('base_page_slug');
	
	// get the base slug, if base page was set to home page then dont use any slug
	$slug = ((ap_opt('base_page') !== get_option('page_on_front')) ? $base_page_slug.'/' : '');
	
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
					'compare' => 'NOT EXISTS'
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
					'compare' => 'NOT EXISTS'
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
					'compare' => 'NOT EXISTS'
				)
			)
		);
	}
	
	$ans_args = apply_filters('ap_answers_query_args', $ans_args);
	
	$ans = new WP_Query($ans_args);	
	
	// get answer sorting tab
	echo '<div id="answers-c">';
	ap_ans_tab(); 	
	echo '<div id="answers">';
		while ( $ans->have_posts() ) : $ans->the_post(); 
			include(ap_get_theme_location('answer.php'));
		endwhile ;
	echo '</div>';	
	ap_pagination('', 2, $paged, $ans);
	echo '</div>';
	wp_reset_query();
}

function ap_ans_tab(){
	$order = get_query_var('sort');
	if(empty($order ))
		$order = ap_opt('answers_sort');
		
		$link = get_permalink( get_the_ID() ).'?sort=';
		$ans_count = ap_count_ans(get_the_ID());
	?>
		<ul class="ap-ans-tab ap-tabs clearfix" role="tablist">
			<li><h2 class="ap-answer-count" data-view="ap-answer-count-label"><?php printf(_n('<span>1 Answer</span>', '<span>%d Answers</span>', $ans_count, 'ap'), $ans_count); ?></h2></li>
			<li class="pull-right<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
			<li class="pull-right<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
			<li class="pull-right<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
		</ul>
	<?php
}

function ap_questions_tab(){
	$order = get_query_var('sort');
	$label = sanitize_text_field(get_query_var('label'));
	if(empty($order ))
		$order = 'active';//ap_opt('answers_sort');
	
	if(empty($status ))
		$status = '';
		
	$link = '?sort=';
	$label_link = '?sort='.$order.'&label=';
	
	?>
		<ul class="ap-tabs clearfix" role="tablist">			
			<li class="<?php echo $order == 'active' ? ' active' : ''; ?>"><a href="<?php echo $link.'active'; ?>"><?php _e('Active', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>			
			<li class="<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'answers' ? ' active' : ''; ?>"><a href="<?php echo $link.'answers'; ?>"><?php _e('Most answers', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'unanswered' ? ' active' : ''; ?>"><a href="<?php echo $link.'unanswered'; ?>"><?php _e('Unanswered', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
			<li class="pull-right">
				<ul class="ap_status ap-dropdown">
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
				</ul>
			</li>
		</ul>
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
		return '<a href="#" class="ap-select-answer-btn ap-icon-checkmark ap-btn ap-btn-small" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Select this answer as best', 'ap').'">'.__('Select answer', 'ap').'</a>';
		
	}elseif(ap_is_answer_selected($ans->post_parent) && ap_is_best_answer($ans->ID)){
		return '<a href="#" class="ap-select-answer-btn ap-icon-checkmark ap-btn ap-btn-small selected" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Unselect this answer', 'ap').'">'.__('Best answer', 'ap').'</a>';
		
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