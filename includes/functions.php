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

/**
 * Get location to a file
 * First file is looked inside active WordPress theme directory /anspress.
 * @param  	string  	$file   	file name
 * @param  	mixed 		$plugin   	Plugin path
 * @return 	string 
 * @since 	0.1         
 */
function ap_get_theme_location($file, $plugin = false){
	// checks if the file exists in the theme first,
	// otherwise serve the file from the plugin
	if ( $theme_file = locate_template( array( 'anspress/'.$file ) ) ) {
		$template_path = $theme_file;
	} elseif($plugin !== false) {
		$template_path = $plugin .'/theme/'.$file;
	}else {
		$template_path = ANSPRESS_THEME_DIR .'/'.ap_get_theme().'/'.$file;
	}
	return $template_path;
}

/**
 * Get url to a file
 * Used for enqueue CSS or JS
 * @param  		string  $file   
 * @param  		mixed $plugin 
 * @return 		string          
 * @since  		2.0
 */
function ap_get_theme_url($file, $plugin = false){
	// checks if the file exists in the theme first,
	// otherwise serve the file from the plugin
	if ( $theme_file = locate_template( array( 'anspress/'.$file ) ) ) {
		$template_url = get_template_directory_uri().'/anspress/'.$file;
	} elseif($plugin !== false) {
		$template_url = $plugin .'theme/'.$file;
	}else {
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

/**
 * Count all answers of a question includes all post status
 * @param  int $id question id
 * @return int
 * @since 2.0.1.1
 */
function ap_count_all_answers($id){
		
	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_type = %s", $id, 'answer'));

	return $count;
}

function ap_count_published_answers($id){
		
	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_status = %s AND post_type = %s", $id, 'publish', 'answer'));

	return $count;
}

function ap_count_answer_meta($post_id =false){
	if(!$post_id) $post_id = get_the_ID();
	$count = get_post_meta($post_id, ANSPRESS_ANS_META, true);
	 return $count ? $count : 0;
}

/**
 * Count all answers excluding best answer
 * @return int
 */
function ap_count_other_answer($question_id =false){
	if(!$question_id) $question_id = get_the_ID();

	$count = ap_count_answer_meta($question_id);
	
	if(ap_is_answer_selected($question_id))
		return ($count - 1);

	return $count;
	
}

function ap_last_active($post_id =false){
	if(!$post_id) $post_id = get_the_ID();
	return get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
}

//check if current questions have answers
function ap_have_ans($id){
	
	if(ap_count_all_answers($id) > 0)
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

/**
 * Load comment form button
 * @param  boolean $echo
 * @return string        
 * @since 0.1
 */
function ap_comment_btn_html($echo = false){
	if(ap_user_can_comment()){
		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$output = '<a href="#ap-comment-area-'.get_the_ID().'" class="comment-btn" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&post='.get_the_ID().'&__nonce='.$nonce.'" title="'.__('Add comment', 'ap').'">'.ap_icon('comment', true).__('Comment', 'ap').'</a>';

		if($echo)
			echo $output;
		else
			return $output;
	}
}

/**
 * Return edit link for question and answer
 * @param  int| object $post_id_or_object
 * @return string                 
 * @since 2.0.1
 */
function ap_post_edit_link($post_id_or_object){
	if(!is_object($post_id_or_object))
		$post_id_or_object = get_post($post_id_or_object);

	$post = $post_id_or_object;

	$nonce = wp_create_nonce( 'nonce_edit_post_'.$post->ID );

	$edit_link = add_query_arg( array('edit_post_id' => $post->ID,  '__nonce' => $nonce), get_permalink( ap_opt('edit_page')) );

	return apply_filters( 'ap_post_edit_link', $edit_link );
}

/**
 * Returns edit post button html
 * @param  boolean $echo
 * @param  int | object $post_id_or_object
 * @return void
 * @since 2.0.1
 */
function ap_edit_post_link_html($echo = false, $post_id_or_object = false){
	if(!is_object($post_id_or_object))
		$post_id_or_object = get_post($post_id_or_object);

	$post = $post_id_or_object;
	
	$edit_link = ap_post_edit_link($post);

	$output = '';

	if($post->post_type == 'question' && ap_user_can_edit_question($post->ID)){		
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='".__('Edit this question', 'ap')."' class='apEditBtn'>".ap_icon('edit', true)."<span>".__('Edit', 'ap')."</span></a>";	
	}elseif($post->post_type == 'answer' && ap_user_can_edit_ans($post->ID)){
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='".__('Edit this answer', 'ap')."' class='apEditBtn'>".ap_icon('edit', true)."<span>".__('Edit', 'ap')."</span></a>";
	}

	if($echo)
			echo $output;
		else
			return $output;
}

function ap_edit_a_btn_html( $echo = false ){
	if(!is_user_logged_in())
		return;
	$output = '';	
	$post_id = get_edit_answer_id();
	if(ap_user_can_edit_ans($post_id)){		
		$edit_link = ap_answer_edit_link();
		$output .= "<a href='$edit_link.' class='edit-btn aicon-edit' data-button='ap-edit-post' title='".__('Edit Answer', 'ap')."'>".ap_icon('edit', true).__('Edit', 'ap')."</a>";
	}
	if($echo)
			echo $output;
	else
		return $output;
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

/* TODO: REMOVE - base page slug*/
/*function ap_base_page_slug(){
	$base_page_slug = ap_opt('base_page_slug');
	
	// get the base slug, if base page was set to home page then dont use any slug
	$slug = ((ap_opt('base_page') !== get_option('page_on_front')) ? $base_page_slug.'/' : '');
	
	$base_page = get_post(ap_opt('base_page'));
	
	if( $base_page->post_parent != 0 ){
		$parent_page = get_post($base_page->post_parent);
		$slug = $parent_page->post_name . '/'.$slug;
	}
	
	return apply_filters('ap_base_page_slug', $slug) ;
}*/





function ap_ans_list_tab(){
	$order = get_query_var('sort');
	if(empty($order ))
		$order = ap_opt('answers_sort');
		
		$link = '?sort=';
		$ans_count = ap_count_all_answers(get_the_ID());
	?>
		<ul class="ap-ans-tab ap-tabs clearfix" role="tablist">
			<li class="<?php echo $order == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'oldest' ? ' active' : ''; ?>"><a href="<?php echo $link.'oldest'; ?>"><?php _e('Oldest', 'ap'); ?></a></li>
			<li class="<?php echo $order == 'voted' ? ' active' : ''; ?>"><a href="<?php echo $link.'voted'; ?>"><?php _e('Voted', 'ap'); ?></a></li>
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


function ap_selected_answer($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	return get_post_meta($post_id, ANSPRESS_SELECTED_META, true);
}

/**
 * Check if answer is selected for given question
 * @param  int $question_id
 * @return boolean
 */
function ap_is_answer_selected($question_id = false){
	if(!$question_id)
		$question_id = get_the_ID();
		
	$meta = get_post_meta($question_id, ANSPRESS_SELECTED_META, true);

	if(!$meta)
		return false;
	
	return true;
}

/**
 * Check if given post is selected answer
 * @param  int $post_id 
 * @return boolean
 * @since unknown
 */
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
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon('check').' ap-tip" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Select this answer as best', 'ap').'">'.__('Select answer', 'ap').'</a>';
		
	}elseif(ap_is_answer_selected($ans->post_parent) && ap_is_best_answer($ans->ID)){
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon('cross').' selected ap-tip" data-button="ap-select-answer" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Unselect this answer', 'ap').'">'.__('Unselect answer', 'ap').'</a>';
		
	}
}

function ap_post_delete_btn_html($post_id = false, $echo = false){
	if(!$post_id){
		$post_id = get_the_ID();
	}
	if(ap_user_can_delete($post_id)){		
		$action = 'delete_post_'.$post_id;
		$nonce = wp_create_nonce( $action );
		
		$output = '<a href="#" class="delete-btn" data-button="ap-delete-post ap-tip" data-args="'. $post_id.'-'. $nonce .'" title="'.__('Delete', 'ap').'">'.ap_icon('delete', true).__('Delete', 'ap').'</a>';

		if($echo)
			echo $output;
		else
			return $output;
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

/**
 * Check if doing ajax request
 * @return boolean
 * @since 2.0.1
 */
function ap_is_ajax(){
	if (defined('DOING_AJAX') && DOING_AJAX)
		return true;

	return false;
}

/**
 * Allow HTML tags
 * @return array
 * @since 0.9
 */
function ap_form_allowed_tags(){
	$allowed_tags = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
		'pre' => array(),
		'code' => array(),
		'blockquote' => array(),
		'img' => array(
			'src' => array(),
		),
	);
	
	/**
	 * FILTER: ap_allowed_tags
	 * Before passing allowed tags
	 */
	return apply_filters( 'ap_allowed_tags', $allowed_tags);
}

function ap_send_json($result = array()){
	$result['is_ap_ajax'] = true;

	wp_send_json( $result );
}

/**
 * Highlight matching words
 * @param  	string $text 
 * @param  	string $words
 * @return 	string 
 * @since 	2.0
 */
function ap_highlight_words($text, $words) {
	$words = explode(' ', $words);
	foreach ($words as $word)
    {
        //quote the text for regex
        $word = preg_quote($word);
        
        //highlight the words
        $text = preg_replace("/\b($word)\b/i", '<span class="highlight_word">\1</span>', $text);
    }

    return $text;
}

/**
 * Return response with type and message
 * @param  string $id error id
 * @return string
 * @since 2.0.1
 */
function ap_responce_message($id)
{
	$msg =array(
		'please_login' => array('type' => 'warning', 'message' => __('You need to login before doing this action.', 'ap')),
		'something_wrong' => array('type' => 'error', 'message' => __('Something went wrong, last action failed.', 'ap')),
		'no_permission' => array('type' => 'warning', 'message' => __('You do not have permission to do this action.', 'ap')),
		'draft_comment_not_allowed' => array('type' => 'warning', 'message' => __('You are commenting on a draft post.', 'ap')),
		'comment_success' => array('type' => 'success', 'message' => __('Comment successfully posted.', 'ap')),
		'comment_edit_success' => array('type' => 'success', 'message' => __('Comment updated successfully.', 'ap')),
		'comment_delete_success' => array('type' => 'success', 'message' => __('Comment deleted successfully.', 'ap')),
		'subscribed' => array('type' => 'success', 'message' => __('You are subscribed to this question.', 'ap')),
		'unsubscribed' => array('type' => 'success', 'message' => __('Successfully unsubscribed.', 'ap')),
		'question_submitted' => array('type' => 'success', 'message' => __('Question submitted successfully', 'ap')),
		'question_updated' => array('type' => 'success', 'message' => __('Question updated successfully', 'ap')),
		'answer_submitted' => array('type' => 'success', 'message' => __('Answer submitted successfully', 'ap')),
		'answer_updated' => array('type' => 'success', 'message' => __('Answer updated successfully', 'ap')),
		'voted' => array('type' => 'success', 'message' => __('Thank you for voting.', 'ap')),
		'undo_vote' => array('type' => 'success', 'message' => __('Your vote has been removed.', 'ap')),
		'undo_vote_your_vote' => array('type' => 'warning', 'message' => __('Undo your vote first.', 'ap')),
		'cannot_vote_own_post' => array('type' => 'warning', 'message' => __('You cannot vote on your own question or answer.', 'ap')),
	);

	/**
	 * FILTER: ap_responce_message
	 * Can be used to alter response messages
	 * @var array
	 * @since 2.0.1 
	 */
	$msg = apply_filters( 'ap_responce_message', $msg );

	if(isset($msg[$id]))
		return $msg[$id];

	return false;
}

function ap_ajax_responce($results)
{

	if(!is_array($results)){
		$message_id = $results;
		$results = array();
		$results['message'] = $message_id;
	}

	$results['ap_responce'] = true;

	if( isset($results['message']) ){
		$error_message = ap_responce_message($results['message']);

		if($error_message !== false){
			$results['message'] = $error_message['message'];
			$results['message_type'] = $error_message['type'];
		}
	}

	/**
	 * FILTER: ap_ajax_responce
	 * Can be used to alter ap_ajax_responce
	 * @var array
	 * @since 2.0.1
	 */
	$results = apply_filters( 'ap_ajax_responce', $results );

	return $results;
}
