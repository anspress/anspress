<?php
/**
 * AnsPress common functions.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Get slug of base page.
 * @return string
 * @since 2.0.0
 */
function ap_base_page_slug() {

	$base_page = get_post( ap_opt( 'base_page' ) );

	$slug = $base_page->post_name;

	if ( $base_page->post_parent > 0 ) {
		$parent_page = get_post( $base_page->post_parent );
		$slug = $parent_page->post_name.'/'.$slug;
	}

	return apply_filters( 'ap_base_page_slug', $slug );
}

/**
 * Retrive permalink to base page
 * @return string URL to AnsPress base page
 * @since 2.0.0
 */
function ap_base_page_link() {

	return get_permalink( ap_opt( 'base_page' ) );
}

function ap_theme_list() {

	$themes = array();
	$dirs = array_filter( glob( ANSPRESS_THEME_DIR.'/*' ), 'is_dir' );
	foreach ( $dirs as $dir ) {
		$themes[basename( $dir )] = basename( $dir );
	}

	return $themes;
}

function ap_get_theme() {

	$option = ap_opt( 'theme' );
	if ( ! $option ) {
		return 'default';
	}

	return ap_opt( 'theme' );
}

/**
 * Get location to a file
 * First file is looked inside active WordPress theme directory /anspress.
 *
 * @param string $file   file name
 * @param mixed  $plugin Plugin path
 *
 * @return string
 *
 * @since 	0.1
 */
function ap_get_theme_location($file, $plugin = false) {

	$child_path = get_stylesheet_directory().'/anspress/'.$file;
	$parent_path = get_template_directory().'/anspress/'.$file;

	// checks if the file exists in the theme first,
	// otherwise serve the file from the plugin
	if ( file_exists( $child_path ) ) {
	    $template_path = $child_path;
	} elseif ( file_exists( $parent_path ) ) {
	    $template_path = $parent_path;
	} elseif ( $plugin !== false ) {
	    $template_path = $plugin.'/theme/'.$file;
	} else {
	    $template_path = ANSPRESS_THEME_DIR.'/'.ap_get_theme().'/'.$file;
	}

	return $template_path;
}

/**
 * Get url to a file
 * Used for enqueue CSS or JS.
 *
 * @param  string $file   File name.
 * @param  mixed  $plugin Plugin path, if calling from AnsPress extension.
 * @return string
 * @since  2.0
 */
function ap_get_theme_url($file, $plugin = false) {

	$child_path = get_stylesheet_directory().'/anspress/'.$file;
	$parent_path = get_template_directory().'/anspress/'.$file;

	// Checks if the file exists in the theme first.
	// Otherwise serve the file from the plugin.
	if ( file_exists( $child_path ) ) {
	    $template_url = get_stylesheet_directory_uri().'/anspress/'.$file;
	} elseif ( file_exists( $parent_path ) ) {
	    $template_url = get_template_directory_uri().'/anspress/'.$file;
	} elseif ( $plugin !== false ) {
	    $template_url = $plugin.'theme/'.$file;
	} else {
	    $template_url = ANSPRESS_THEME_URL.'/'.ap_get_theme().'/'.$file;
	}

	return $template_url;
}

// get current user id
function ap_current_user_id() {

	require_once ABSPATH.WPINC.'/pluggable.php';
	global $current_user;
	get_currentuserinfo();

	return $current_user->ID;
}

function ap_question_content() {

	global $post;
	echo $post->post_content;
}

function is_anspress() {

	$queried_object = get_queried_object();

	// if buddypress installed
	if ( function_exists( 'bp_current_component' ) ) {
	    $bp_com = bp_current_component();
	    if ( 'questions' == $bp_com || 'answers' == $bp_com ) {
	        return true;
	    }
	}

	if ( ! isset( $queried_object->ID ) ) {
		return false;
	}

	if ( $queried_object->ID == ap_opt( 'base_page' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if current page is question page
 * @return boolean
 */
function is_question() {
	$question_id = (int) get_query_var( 'question_id' );

	if ( is_anspress() && $question_id > 0 ) {
		return true;
	}

	return false;
}

/**
 * Is ask page.
 * @return boolean
 */
function is_ask() {
	if ( is_anspress() && ap_current_page() == ap_get_ask_page_slug() ) {
		return true;
	}

	return false;
}

/**
 * Ask page slug.
 * @return string
 */
function ap_get_ask_page_slug() {
	$opt = ap_opt( 'ask_page_slug' );

	if ( $opt ) {
		return $opt;
	}

	return 'ask';
}

function is_ap_users() {
	if ( is_anspress() && ap_current_page() == ap_get_users_page_slug() ) {
		return true;
	}

	return false;
}

/**
 * Get users page slug
 * @return string
 */
function ap_get_users_page_slug() {
	$opt = ap_opt( 'users_page_slug' );

	if ( $opt ) {
		return $opt;
	}

	return 'users';
}

/**
 * Check if current page is user page
 * @return boolean
 */
function is_ap_user() {
	if ( is_anspress() && ap_current_page() == ap_get_user_page_slug() ) {
		return true;
	}

	return false;
}


function get_question_id() {

	if ( is_question() && get_query_var( 'question_id' ) ) {
		return (int) get_query_var( 'question_id' );
	} elseif ( is_question() && get_query_var( 'question' ) ) {
		return get_query_var( 'question' );
	} elseif ( is_question() && get_query_var( 'question_name' ) ) {
		$post = get_page_by_path( get_query_var( 'question_name' ), OBJECT, 'question' );

		return $post->ID;
	} elseif ( get_query_var( 'edit_q' ) ) {
		return get_query_var( 'edit_q' );
	} elseif ( ap_answer_the_object() ) {
		return ap_answer_get_the_question_id();
	}

	return false;
}

/**
 * Return human readable time format
 * @param  string  $time 			Time.
 * @param  boolean $unix 			Is $time is unix?
 * @param  integer $show_full_date 	Show full date after some period. Default is 7 days in epoch.
 * @return string|null
 */
function ap_human_time($time, $unix = true, $show_full_date = 604800, $format = 'd M, Y') {
	if(!is_numeric($time) && !$unix ){
		$time = strtotime($time);
	}

	if ( $time ) {
		if ( $show_full_date + $time > current_time( 'timestamp', true ) ) {
			return human_time_diff( $time, current_time( 'timestamp', true ) ) .' '.__( 'ago', 'ap' );
		} else { 			return date( $format, $time ); }
	}
}

function ap_please_login() {

	$o = '<div id="please-login">';
	$o .= '<button>x</button>';
	$o .= __( 'Please login or register to continue this action.', 'ap' );
	$o .= '</div>';

	echo apply_filters( 'ap_please_login', $o );
}

// check if user answered on a question
/**
 * @param integer $question_id
 */
function ap_is_user_answered($question_id, $user_id) {

	global $wpdb;

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $question_id AND ( post_author = $user_id AND post_type = 'answer')" );
	if ( $count ) {
		return true;
	}

	return false;
}

/**
 * Count all answers of a question includes all post status.
 *
 * @param int $id question id
 *
 * @return int
 *
 * @since 2.0.1.1
 */
function ap_count_all_answers($id) {

	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_type = %s", $id, 'answer' ) );

	return $count;
}

function ap_count_published_answers($id) {

	global $wpdb;
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND (post_status = %s OR post_status = %s) AND post_type = %s", $id, 'publish', 'closed', 'answer' ) );

	return $count;
}

function ap_count_answer_meta($post_id = false) {

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$count = get_post_meta( $post_id, ANSPRESS_ANS_META, true );

	return $count ? $count : 0;
}

/**
 * Count all answers excluding best answer.
 *
 * @return int
 */
function ap_count_other_answer($question_id = false) {

	if ( ! $question_id ) {
		$question_id = get_question_id();
	}

	$count = ap_count_answer_meta( $question_id );

	if ( ap_question_best_answer_selected( $question_id ) ) {
		return (int) ($count - 1);
	}

	return (int) $count;
}

function ap_last_active($post_id = false) {

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_meta( $post_id, ANSPRESS_UPDATED_META, true );
}

// link to asnwers
function ap_answers_link($question_id = false) {

	if ( ! $question_id ) {
		return get_permalink().'#answers';
	}

	return get_permalink( $question_id ).'#answers';
}

/**
 * Load comment form button.
 *
 * @param bool $echo
 *
 * @return string
 *
 * @since 0.1
 */
function ap_comment_btn_html($echo = false) {

	if ( ap_user_can_comment() ) {
		global $post;

		if ( $post->post_type == 'question' && ap_opt( 'disable_comments_on_question' ) ) {
			return;
		}

		if ( $post->post_type == 'answer' && ap_opt( 'disable_comments_on_answer' ) ) {
			return;
		}

		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$comment_count = get_comments_number( get_the_ID() );
		$output = '<a href="#comments-'.get_the_ID().'" class="comment-btn ap-tip" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&post='.get_the_ID().'&__nonce='.$nonce.'" title="'.__( 'Comments', 'ap' ).'">'.__( 'Comment', 'ap' ).'<span class="ap-data-view ap-view-count-'.$comment_count.'" data-view="comments_count_'.get_the_ID().'">('.$comment_count.')</span></a>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}

/**
 * Return edit link for question and answer.
 *
 * @param int| object $post_id_or_object
 *
 * @return string
 *
 * @since 2.0.1
 */
function ap_post_edit_link($post_id_or_object) {

	if ( ! is_object( $post_id_or_object ) ) {
		$post_id_or_object = get_post( $post_id_or_object );
	}

	$post = $post_id_or_object;

	$nonce = wp_create_nonce( 'nonce_edit_post_'.$post->ID );

	$edit_link = add_query_arg( array( 'ap_page' => 'edit', 'edit_post_id' => $post->ID, '__nonce' => $nonce ), ap_base_page_link() );

	return apply_filters( 'ap_post_edit_link', $edit_link );
}

/**
 * Returns edit post button html.
 *
 * @param bool         $echo
 * @param int | object $post_id_or_object
 *
 * @return null|string
 *
 * @since 2.0.1
 */
function ap_edit_post_link_html($echo = false, $post_id_or_object = false) {

	if ( ! is_object( $post_id_or_object ) ) {
		$post_id_or_object = get_post( $post_id_or_object );
	}

	$post = $post_id_or_object;

	$edit_link = ap_post_edit_link( $post );

	$output = '';

	if ( $post->post_type == 'question' && ap_user_can_edit_question( $post->ID ) ) {
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='".__( 'Edit this question', 'ap' )."' class='apEditBtn'>".__( 'Edit', 'ap' ).'</a>';
	} elseif ( $post->post_type == 'answer' && ap_user_can_edit_ans( $post->ID ) ) {
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='".__( 'Edit this answer', 'ap' )."' class='apEditBtn'>".__( 'Edit', 'ap' ).'</a>';
	}

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

function ap_edit_a_btn_html($echo = false) {

	if ( ! is_user_logged_in() ) {
		return;
	}
	$output = '';
	$post_id = get_edit_answer_id();
	if ( ap_user_can_edit_ans( $post_id ) ) {
		$edit_link = ap_answer_edit_link();
		$output .= "<a href='$edit_link.' class='edit-btn ' data-button='ap-edit-post' title='".__( 'Edit Answer', 'ap' )."'>".__( 'Edit', 'ap' ).'</a>';
	}
	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

function ap_post_edited_time() {

	if ( get_the_time( 's' ) != get_the_modified_time( 's' ) ) {
		printf('<span class="edited-text">%1$s</span> <span class="edited-time">%2$s</span>',
			__( 'Edited on', 'ap' ),
			get_the_modified_time()
		);
	}

	return;
}

function ap_answer_edit_link() {

	$post_id = get_the_ID();
	if ( ap_user_can_edit_ans( $post_id ) ) {
		$action = get_post_type( $post_id ).'-'.$post_id;
		$nonce = wp_create_nonce( $action );
		$edit_link = add_query_arg( array( 'edit_a' => $post_id, 'ap_nonce' => $nonce ), get_permalink( ap_opt( 'base_page' ) ) );

		return apply_filters( 'ap_answer_edit_link', $edit_link );
	}

	return;
}

/**
 * @param string $text
 * @param int    $limit
 */
function ap_truncate_chars($text, $limit, $ellipsis = '...') {

	if ( strlen( $text ) > $limit ) {
		$endpos = strpos( str_replace( array( "\r\n", "\r", "\n", "\t" ), ' ', $text ), ' ', $limit );
		if ( $endpos !== false ) {
			$text = trim( substr( $text, 0, $endpos ) ).$ellipsis;
		}
	}

	return $text;
}

function ap_get_all_users() {

	$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
	$per_page = ap_opt( 'tags_per_page' );
	$total_terms = wp_count_terms( 'question_tags' );
	$offset = $per_page * ($paged - 1);

	$args = array(
		'number' => $per_page,
		'offset' => $offset,
		);

	$users = get_users( $args );

	echo '<ul class="ap-tags-list">';
	foreach ( $users as $key => $user ) :

		echo '<li>';
		echo $user->display_name;
		echo '</li>';

	endforeach;
	echo'</ul>';

	ap_pagination( ceil( $total_terms / $per_page ), $range = 1, $paged );
}

function ap_ans_list_tab() {

	$order = isset( $_GET['ap_sort'] ) ? $_GET['ap_sort'] : ap_opt( 'answers_sort' );

	$link = '?ap_sort=';
	?>
    <ul class="ap-ans-tab ap-tabs clearfix" role="tablist">
		<li class="<?php echo $order == 'newest' ? ' active' : '';
	?>"><a href="<?php echo $link.'newest';
	?>"><?php _e( 'Newest', 'ap' );
	?></a></li>
		<li class="<?php echo $order == 'oldest' ? ' active' : '';
	?>"><a href="<?php echo $link.'oldest';
	?>"><?php _e( 'Oldest', 'ap' );
	?></a></li>
		<li class="<?php echo $order == 'voted' ? ' active' : '';
	?>"><a href="<?php echo $link.'voted';
	?>"><?php _e( 'Voted', 'ap' );
	?></a></li>
    </ul>
	<?php

}

function ap_untrash_post($post_id) {

	// no post?
	if ( ! $post_id || ! is_numeric( $post_id ) ) {
	    return false;
	}
	$_wpnonce = wp_create_nonce( 'untrash-post_'.$post_id );
	$url = admin_url( 'post.php?post='.$post_id.'&action=untrash&_wpnonce='.$_wpnonce );

	return $url;
}

function ap_user_can($id) {

	get_user_meta( $id, 'ap_role', true );
}

/**
 * Return the ID of selected answer of a question.
 *
 * @param false|int $post_id
 *
 * @return int
 */
function ap_selected_answer($post_id = false) {

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_meta( $post_id, ANSPRESS_SELECTED_META, true );
}

/**
 * Print select anser HTML button.
 *
 * @param int $post_id
 *
 * @return null|string
 */
function ap_select_answer_btn_html($post_id) {

	if ( ! ap_user_can_select_answer( $post_id ) ) {
		return;
	}

	$ans = get_post( $post_id );
	$action = 'answer-'.$post_id;
	$nonce = wp_create_nonce( $action );

	if ( ! ap_question_best_answer_selected( $ans->post_parent ) ) {
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon( 'check' ).' ap-tip" data-action="select_answer" data-query="answer_id='.$post_id.'&__nonce='.$nonce.'&ap_ajax_action=select_best_answer" title="'.__( 'Select this answer as best', 'ap' ).'">'.__( 'Select', 'ap' ).'</a>';
	} elseif ( ap_question_best_answer_selected( $ans->post_parent ) && ap_answer_is_best( $ans->ID ) ) {
		return '<a href="#" class="ap-btn-select ap-sicon '.ap_icon( 'cross' ).' active ap-tip" data-action="select_answer" data-query="answer_id='.$post_id.'&__nonce='.$nonce.'&ap_ajax_action=select_best_answer" title="'.__( 'Unselect this answer', 'ap' ).'">'.__( 'Unselect', 'ap' ).'</a>';
	}
}

/**
 * Output frontend post delete button.
 *
 * @param int  $post_id
 * @param bool $echo
 *
 * @return void|string
 */
function ap_post_delete_btn_html($post_id = false, $echo = false) {

	if ( $post_id === false ) {
		$post_id = get_the_ID();
	}
	if ( ap_user_can_delete( $post_id ) ) {
		$action = 'delete_post_'.$post_id;
		$nonce = wp_create_nonce( $action );

		$output = '<a href="#" class="delete-btn" data-action="ap_delete_post" data-query="post_id='.$post_id.'&__nonce='.$nonce.'&ap_ajax_action=delete_post" title="'.__( 'Delete', 'ap' ).'">'.__( 'Delete', 'ap' ).'</a>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}

function ap_post_permanent_delete_btn_html($post_id = false, $echo = false) {

	if ( $post_id === false ) {
		$post_id = get_the_ID();
	}
	if ( ap_user_can_permanent_delete() ) {
		$action = 'delete_post_'.$post_id;
		$nonce = wp_create_nonce( $action );

		$output = '<a href="#" class="delete-btn" data-action="ap_delete_post" data-query="post_id='.$post_id.'&__nonce='.$nonce.'&ap_ajax_action=permanent_delete_post" title="'.__( 'Delete permanently', 'ap' ).'">'.__( 'Delete permanently', 'ap' ).'</a>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}

/**
 * Output chnage post status button.
 *
 * @param bool|int $post_id
 *
 * @return null|string
 */
function ap_post_change_status_btn_html($post_id = false) {

	$post = get_post( $post_id );

	if ( ap_user_can_change_status( $post_id ) ) {
		$action = 'change_post_status_'.$post_id;
		$nonce = wp_create_nonce( $action );

		$status = apply_filters( 'ap_change_status_dropdown', array( 'closed' => __( 'Close', 'ap' ), 'publish' => __( 'Open', 'ap' ), 'moderate' => __( 'Moderate', 'ap' ), 'private_post' => __( 'Private', 'ap' ) ) );

		$output = '<div class="ap-dropdown">
			<a class="ap-tip ap-dropdown-toggle" title="'.__( 'Change status of post', 'ap' ).'" href="#" >
				'.__( 'Status', 'ap' ).' <i class="caret"></i>
            </a>
			<ul id="ap_post_status_toggle_'.$post_id.'" class="ap-dropdown-menu" role="menu">';

		foreach ( $status as $k => $title ) {
			$can = true;

			if ( $k == 'closed' && ( ! ap_user_can_change_status_to_closed() || $post->post_type == 'answer') ) {
				$can = false;
			} elseif ( $k == 'moderate' && ! ap_user_can_change_status_to_moderate() ) {
				$can = false;
			}

			if ( $can ) {
				$output .= '<li class="'.$k.($k == $post->post_status ? ' active' : '').'">
						<a href="#" data-action="ap_change_status" data-query="post_id='.$post_id.'&__nonce='.$nonce.'&ap_ajax_action=change_post_status&status='.$k.'">'.$title.'</a>
					</li>';
			}
		}
		$output .= '</ul>
		</div>';

		return $output;
	}
}

function ap_get_child_answers_comm($post_id) {

	global $wpdb;
	$ids = array();

	$query = "SELECT p.ID, c.comment_ID from $wpdb->posts p LEFT JOIN $wpdb->comments c ON c.comment_post_ID = p.ID OR c.comment_post_ID = $post_id WHERE post_parent = $post_id";

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'count' );

	if ( $cache === false ) {
		$cols = $wpdb->get_results( $query, ARRAY_A );
		wp_cache_set( $key, $cols, 'count' );
	} else {
		$cols = $cache;
	}

	if ( $cols ) {
		foreach ( $cols as $c ) {
			if ( ! empty( $c['ID'] ) ) {
				$ids['posts'][] = $c['ID'];
			}

			if ( ! empty( $c['comment_ID'] ) ) {
				$ids['comments'][] = $c['comment_ID'];
			}
		}
	} else {
		return false;
	}

	if ( isset( $ids['posts'] ) ) {
		$ids['posts'] = array_unique( $ids['posts'] );
	}

	if ( isset( $ids['comments'] ) ) {
		$ids['comments'] = array_unique( $ids['comments'] );
	}

	return $ids;
}

function ap_short_num($num, $precision = 2) {

	if ( $num >= 1000 && $num < 1000000 ) {
		$n_format = number_format( $num / 1000, $precision ).'K';
	} elseif ( $num >= 1000000 && $num < 1000000000 ) {
		$n_format = number_format( $num / 1000000, $precision ).'M';
	} elseif ( $num >= 1000000000 ) {
		$n_format = number_format( $num / 1000000000, $precision ).'B';
	} else {
		$n_format = $num;
	}

	return $n_format;
}

/**
 * Sanitize comma delimited strings
 * @param  string $str Comma delimited string.	
 * @return string
 */
function sanitize_comma_delimited($str) {
	if( !empty($str) && !is_array( $str ) ){
		$str = wp_unslash( $str );
		return implode( ',', array_map( 'intval', explode( ',', $str ) ) );
	}
}

/**
 * Check if doing ajax request.
 *
 * @return bool
 *
 * @since 2.0.1
 */
function ap_is_ajax() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return true;
	}

	return false;
}

/**
 * Allow HTML tags.
 *
 * @return array
 *
 * @since 0.9
 */
function ap_form_allowed_tags() {

	global $ap_kses_check;
	$ap_kses_check = true;

	$allowed_style = array(
		'align' => true,
	);
	$allowed_tags = array(
		'p' => array(
			'style' => $allowed_style,
			'title' => true,
			),
		'span' => array(
			'style' => $allowed_style,
			),
		'a' => array(
			'href' => true,
			'title' => true,
			),
		'br' => array(),
		'em' => array(),
		'strong' => array(
			'style' => $allowed_style,
			),
		'pre' => array(),
		'code' => array(),
		'blockquote' => array(),
		'img' => array(
			'src' => true,
			'style' => $allowed_style,
			),
		'ul' => array(),
		'ol' => array(),
		'li' => array(),
		'del' => array(),
		'br' => array(),
		);

	/*
     * FILTER: ap_allowed_tags
     * Before passing allowed tags
	 */
	return apply_filters( 'ap_allowed_tags', $allowed_tags );
}

function ap_send_json($result = array()) {
	$result['is_ap_ajax'] = true;
	$message_type = isset( $result['message_type'] ) ? $result['message_type'] : 'success';

	header( sprintf( 'X-ANSPRESS-MT: %s', $message_type ) );

	if ( isset( $result['message'] ) ) {
		header( sprintf( 'X-ANSPRESS-MESSAGE: %s', json_encode( $result['message'] ) ) );
	}

	if ( isset( $result['do'] ) ) {
		$do = is_array( $result['do'] ) ? json_encode( $result['do'] ) : $result['do'];
		header( sprintf( 'X-ANSPRESS-DO: %s', $do ) );
	}

	// echo 'dfdfd';
	wp_send_json( $result );
}

/**
 * Highlight matching words.
 *
 * @param string $text
 * @param string $words
 *
 * @return string
 *
 * @since 	2.0
 */
function ap_highlight_words($text, $words) {

	$words = explode( ' ', $words );
	foreach ( $words as $word ) {
		// quote the text for regex
		$word = preg_quote( $word );

		// highlight the words
		$text = preg_replace( "/\b($word)\b/i", '<span class="highlight_word">\1</span>', $text );
	}

	return $text;
}

/**
 * Return response with type and message.
 *
 * @param string $id           messge id
 * @param bool   $only_message return message string instead of array
 *
 * @return string
 *
 * @since 2.0.0-alpha2
 */
function ap_responce_message($id, $only_message = false) {

	$msg = array(
		'success' => array( 'type' => 'success', 'message' => __( 'Success', 'ap' ) ),
		'please_login' => array( 'type' => 'warning', 'message' => __( 'You need to login before doing this action.', 'ap' ) ),
		'something_wrong' => array( 'type' => 'error', 'message' => __( 'Something went wrong, last action failed.', 'ap' ) ),
		'no_permission' => array( 'type' => 'warning', 'message' => __( 'You do not have permission to do this action.', 'ap' ) ),
		'draft_comment_not_allowed' => array( 'type' => 'warning', 'message' => __( 'You are commenting on a draft post.', 'ap' ) ),
		'comment_success' => array( 'type' => 'success', 'message' => __( 'Comment successfully posted.', 'ap' ) ),
		'comment_edit_success' => array( 'type' => 'success', 'message' => __( 'Comment updated successfully.', 'ap' ) ),
		'comment_delete_success' => array( 'type' => 'success', 'message' => __( 'Comment deleted successfully.', 'ap' ) ),
		'subscribed' => array( 'type' => 'success', 'message' => __( 'You are following this question.', 'ap' ) ),
		'unsubscribed' => array( 'type' => 'success', 'message' => __( 'Successfully unfollowed.', 'ap' ) ),
		'question_submitted' => array( 'type' => 'success', 'message' => __( 'Question submitted successfully', 'ap' ) ),
		'question_updated' => array( 'type' => 'success', 'message' => __( 'Question updated successfully', 'ap' ) ),
		'answer_submitted' => array( 'type' => 'success', 'message' => __( 'Answer submitted successfully', 'ap' ) ),
		'answer_updated' => array( 'type' => 'success', 'message' => __( 'Answer updated successfully', 'ap' ) ),
		'voted' => array( 'type' => 'success', 'message' => __( 'Thank you for voting.', 'ap' ) ),
		'undo_vote' => array( 'type' => 'success', 'message' => __( 'Your vote has been removed.', 'ap' ) ),
		'undo_vote_your_vote' => array( 'type' => 'warning', 'message' => __( 'Undo your vote first.', 'ap' ) ),
		'cannot_vote_own_post' => array( 'type' => 'warning', 'message' => __( 'You cannot vote on your own question or answer.', 'ap' ) ),
		'unselected_the_answer' => array( 'type' => 'success', 'message' => __( 'Best answer is unselected for your question.', 'ap' ) ),
		'selected_the_answer' => array( 'type' => 'success', 'message' => __( 'Best answer is selected for your question.', 'ap' ) ),
		'question_moved_to_trash' => array( 'type' => 'success', 'message' => __( 'Question moved to trash.', 'ap' ) ),
		'answer_moved_to_trash' => array( 'type' => 'success', 'message' => __( 'Answer moved to trash.', 'ap' ) ),
		'no_permission_to_view_private' => array( 'type' => 'warning', 'message' => __( 'You dont have permission to view private posts.', 'ap' ) ),
		'flagged' => array( 'type' => 'success', 'message' => __( 'Thank you for reporting this post.', 'ap' ) ),
		'already_flagged' => array( 'type' => 'warning', 'message' => __( 'You have already reported this post.', 'ap' ) ),
		'captcha_error' => array( 'type' => 'error', 'message' => __( 'Please check captcha field and resubmit it again.', 'ap' ) ),
		'comment_content_empty' => array( 'type' => 'error', 'message' => __( 'Comment content is empty.', 'ap' ) ),
		'status_updated' => array( 'type' => 'success', 'message' => __( 'Post status updated successfully', 'ap' ) ),
		'post_image_uploaded' => array( 'type' => 'success', 'message' => __( 'Image uploaded successfully', 'ap' ) ),
		'question_deleted_permanently' => array( 'type' => 'success', 'message' => __( 'Question has been deleted permanently', 'ap' ) ),
		'answer_deleted_permanently' => array( 'type' => 'success', 'message' => __( 'Answer has been deleted permanently', 'ap' ) ),
		'set_featured_question' => array( 'type' => 'success', 'message' => __( 'Question is marked as featured.', 'ap' ) ),
		'unset_featured_question' => array( 'type' => 'success', 'message' => __( 'Question is unmarked as featured.', 'ap' ) ),
		'upload_limit_crossed' => array( 'type' => 'warning', 'message' => __( 'You have already attached maximum numbers of allowed uploads.', 'ap' ) ),
		'profile_updated_successfully' => array( 'type' => 'success', 'message' => __( 'Your profile has been updated successfully.', 'ap' ) ),
		'unfollow' => array( 'type' => 'success', 'message' => __( 'Successfully unfollowed.', 'ap' ) ),
		'follow' => array( 'type' => 'success', 'message' => __( 'Successfully followed.', 'ap' ) ),
		'cannot_follow_yourself' => array( 'type' => 'warning', 'message' => __( 'You cannot follow yourself.', 'ap' ) ),
		'delete_notification' => array( 'type' => 'success', 'message' => __( 'Notification deleted successfully.', 'ap' ) ),
		'mark_read_notification' => array( 'type' => 'success', 'message' => __( 'Notification is marked as read.', 'ap' ) ),
		'voting_down_disabled' => array( 'type' => 'warning', 'message' => __( 'Voting down is disabled.', 'ap' ) ),
		'flagged_comment' => array( 'type' => 'success', 'message' => __( 'This comment has been reported to site moderator', 'ap' ) ),
		'already_flagged_comment' => array( 'type' => 'warning', 'message' => __( 'You have already reported this comment', 'ap' ) ),
		);

	/*
     * FILTER: ap_responce_message
     * Can be used to alter response messages
     * @var array
     * @since 2.0.1
	 */
	$msg = apply_filters( 'ap_responce_message', $msg );

	if ( isset( $msg[$id] ) && $only_message ) {
		return $msg[$id]['message'];
	}

	if ( isset( $msg[$id] ) ) {
		return $msg[$id];
	}

	return false;
}

function ap_ajax_responce($results) {

	if ( ! is_array( $results ) ) {
		$message_id = $results;
		$results = array();
		$results['message'] = $message_id;
	}

	$results['ap_responce'] = true;

	if ( isset( $results['message'] ) ) {
		$error_message = ap_responce_message( $results['message'] );

		if ( $error_message !== false ) {
			$results['message'] = $error_message['message'];
			$results['message_type'] = $error_message['type'];
		}
	}

	/*
     * FILTER: ap_ajax_responce
     * Can be used to alter ap_ajax_responce
     * @var array
     * @since 2.0.1
	 */
	$results = apply_filters( 'ap_ajax_responce', $results );

	return $results;
}

function ap_meta_array_map($a) {

	return $a[0];
}

/**
 * Return the current page url.
 *
 * @param array $args
 *
 * @return string
 *
 * @since 2.0.0-alpha2
 */
function ap_current_page_url($args) {

	$base = rtrim( get_permalink(), '/' );

	if ( get_option( 'permalink_structure' ) != '' ) {
		$link = $base.'/';
		if ( ! empty( $args ) ) {
			foreach ( $args as $k => $s ) {
				$link .= $k.'/'.$s.'/';
			}
		}
	} else {
		$link = add_query_arg( $args, $base );
	}

	return $link;
}

/**
 * Sort array by order value. Group array which have same order number and then sort them.
 * @param array $array
 * @return array
 * @since 2.0.0
 */
function ap_sort_array_by_order($array) {

	$new_array = array();
	if ( ! empty( $array ) && is_array( $array ) ) {
		$group = array();
		foreach ( $array as $k => $a ) {
			$order = $a['order'];
			$group[$order][] = $a;
			$group[$order]['order'] = $order;
		}

		usort( $group, 'ap_sort_order_callback' );

		foreach ( $group as $a ) {
			foreach ( $a as $k => $newa ) {
				if ( $k !== 'order' ) {
					$new_array[] = $newa;
				}
			}
		}

		return $new_array;
	}
}

function ap_sort_order_callback($a, $b) {
	return $a['order'] - $b['order'];
}

/**
 * Append array to global var.
 *
 * @param string $key
 * @param array  $args
 * @param string $var
 *
 * @since 2.0.0-alpha2
 */
function ap_append_to_global_var($var, $key, $args) {

	if ( ! isset( $GLOBALS[$var] ) ) {
		$GLOBALS[$var] = array();
	}

	$GLOBALS[$var][$key] = $args;
}

/**
 * Register an event.
 *
 * @since 0.1
 */
function ap_do_event() {

	$args = func_get_args();
	do_action( 'ap_event', $args );
	// do_action('ap_event_'.$args[0], $args);
	$action = 'ap_event_'.$args[0];
	$args[0] = $action;
	call_user_func_array( 'do_action', $args );
}

/**
 * Echo anspress links.
 *
 * @since 2.1
 */
function ap_link_to($sub) {

	echo ap_get_link_to( $sub );
}

	/**
	 * Return link to AnsPress pages.
	 *
	 * @param string|array $sub
	 */
function ap_get_link_to($sub) {

	/**
	 * Define default AnsPress page slugs
	 * @var array
	 */
	$default_pages = array(
		'question' 	=> ap_opt( 'question_page_slug' ),
		'ask' 		=> ap_opt( 'ask_page_slug' ),
		'users' 	=> ap_opt( 'users_page_slug' ),
		'user' 		=> ap_opt( 'user_page_slug' ),
	);

	$default_pages = apply_filters( 'ap_default_page_slugs', $default_pages );

	if ( is_array( $sub ) && isset( $sub['ap_page'] ) && @isset( $default_pages[$sub] ) ) {
		$sub['ap_page'] = $default_pages[$sub['ap_page']];
	} elseif ( ! empty( $sub ) && @isset( $default_pages[$sub] ) ) {
		$sub = $default_pages[$sub];
	}

	$base = rtrim( get_permalink( ap_opt( 'base_page' ) ), '/' );
	$args = '';

	if ( get_option( 'permalink_structure' ) != '' ) {
		if ( ! is_array( $sub ) && $sub != 'base' ) {
			$args = $sub ? '/'.$sub : '';
		} elseif ( is_array( $sub ) ) {
			$args = '/';

			if ( ! empty( $sub ) ) {
				foreach ( $sub as $s ) {
					$args .= $s.'/';
				}
			}
		}

		$args = rtrim( $args, '/' ).'/';
	} else {
		if ( ! is_array( $sub ) ) {
			$args = $sub ? '&ap_page='.$sub : '';
		} elseif ( is_array( $sub ) ) {
			$args = '';

			if ( ! empty( $sub ) ) {
				foreach ( $sub as $k => $s ) {
					$args .= '&'.$k.'='.$s;
				}
			}
		}
	}

	return esc_url( apply_filters( 'ap_link_to', $base.$args, $sub ) );
}

/**
 * Return the total numbers of post.
 *
 * @param string      $post_type
 * @param bool|string $ap_type
 *
 * @return array
 *
 * @since  2.0.0-alpha2
 */
function ap_total_posts_count($post_type = 'question', $ap_type = false) {

	global $wpdb;

	if ( 'question' == $post_type ) {
		$type = "p.post_type = 'question'";
	} elseif ( 'answer' == $post_type ) {
		$type = "p.post_type = 'answer'";
	} else {
		$type = "(p.post_type = 'question' OR p.post_type = 'answer')";
	}

	$meta = '';
	$join = '';

	if ( $ap_type ) {
		$meta = "AND m.apmeta_type='$ap_type'";
		$join = 'INNER JOIN '.$wpdb->prefix.'ap_meta m ON p.ID = m.apmeta_actionid';
	}

	$where = "WHERE $type $meta";

	$where = apply_filters( 'ap_total_posts_count', $where );

	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p $join $where GROUP BY p.post_status";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );

	$counts = array();
	foreach ( get_post_stati() as $state ) {
		$counts[$state] = 0;
	}

	$counts['total'] = 0;

	foreach ( (array) $count as $row ) {
		$counts[$row['post_status']] = $row['count'];
		$counts['total'] += $row['count'];
	}
	wp_cache_set( $cache_key, (object) $counts, 'counts' );

	return (object) $counts;
}

function ap_total_published_questions() {

	$posts = ap_total_posts_count();

	return $posts->publish;
}

/**
 * Get total numbers of solved question.
 *
 * @param string $type int|object
 *
 * @return int|object
 */
function ap_total_solved_questions($type = 'int') {

	global $wpdb;

	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p INNER JOIN ".$wpdb->prefix."postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_ap_selected' AND m.meta_value !='' GROUP BY p.post_status";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );

	$counts = array();
	foreach ( get_post_stati() as $state ) {
		$counts[$state] = 0;
	}

	$counts['total'] = 0;

	foreach ( (array) $count as $row ) {
		$counts[$row['post_status']] = $row['count'];
		$counts['total'] += $row['count'];
	}
	wp_cache_set( $cache_key, (object) $counts, 'counts' );

	$counts = (object) $counts;

	if ( $type == 'int' ) {
		return $counts->publish + $counts->closed + $counts->private_post;
	}

	return $counts;
}

/**
 * Get current sorting type.
 *
 * @return string
 *
 * @since 2.1
 */
function ap_get_sort() {

	if ( isset( $_GET['ap_sort'] ) ) {
		return sanitize_text_field( $_GET['ap_sort'] );
	}
}

/**
 * Register AnsPress menu.
 *
 * @param string $slug
 * @param string $title
 * @param string $link
 */
function ap_register_menu($slug, $title, $link) {

	anspress()->menu[$slug] = array( 'title' => $title, 'link' => $link );
}

/**
 * Check if first parameter is false, if yes then return other parameter.
 *
 * @param mixed $param
 * @param mixed $return
 *
 * @return mixed
 *
 * @since 2.1
 */
function ap_parameter_empty($param = false, $return) {

	if ( $param === false || $param == '' ) {
		return $return;
	}

	return $param;
}

function ap_post_status_description($post_id = false) {

	$post_id = ap_parameter_empty( $post_id, @ap_question_get_the_ID() );
	$post = get_post( $post_id );
	$post_type = $post->post_type == 'question' ? __( 'Question', 'ap' ) : __( 'Answer', 'ap' );

	if ( ap_have_parent_post( $post_id ) && $post->post_type != 'answer' ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id;
	?>" class="ap-notice blue clearfix">
            <?php echo ap_icon( 'link', true ) ?>
            <span><?php printf( __( 'Question is asked for %s.', 'ap' ), '<a href="'.get_permalink( ap_question_get_the_post_parent() ).'">'.get_the_title( ap_question_get_the_post_parent() ).'</a>' );
	?></span>
        </div>
    <?php endif;

	if ( is_private_post( $post_id ) ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id;
	?>" class="ap-notice gray clearfix">
            <i class="apicon-lock"></i><span><?php printf( __( '%s is marked as a private, only admin and post author can see.', 'ap' ), $post_type );
	?></span>
        </div>
    <?php endif;

	if ( is_post_waiting_moderation( $post_id ) ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id;
	?>" class="ap-notice yellow clearfix">
            <i class="apicon-info"></i><span><?php printf( __( '%s is waiting for approval by moderator.', 'ap' ), $post_type );
	?></span>
        </div>
    <?php endif;

	if ( is_post_closed( $post_id ) && $post->post_type != 'answer' ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id;
	?>" class="ap-notice red clearfix">
            <?php echo ap_icon( 'cross', true ) ?><span><?php printf( __( '%s is closed, new answer are not accepted.', 'ap' ), $post_type );
	?></span>
        </div>
    <?php endif;

	if ( $post->post_status == 'trash' ) : ?>
        <div id="ap_post_status_desc_<?php echo $post_id;
	?>" class="ap-notice red clearfix">
            <?php echo ap_icon( 'cross', true ) ?><span><?php printf( __( '%s has been trashed, you can delete it permanently from wp-admin.', 'ap' ), $post_type );
	?></span>
        </div>
    <?php endif;
}

function ap_post_upload_form($post_id = false) {

	$html = '
    <div class="ap-post-upload-form">
        <div class="ap-btn ap-upload-o '.ap_icon( 'image' ).'">
        	<span>'.__( 'Add image to editor', 'ap' ).'</span>';
	if ( ap_user_can_upload_image() ) {
		$html .= '
                <a class="ap-upload-link" href="#" data-action="ap_post_upload_field">
	            	'.__( 'upload', 'ap' ).'

	            </a> '.__( 'or', 'ap' );
	}

	$html .= '<span class="ap-upload-remote-link">
            	'.__( 'add image from link', 'ap' ).'
            </span>
            <div class="ap-upload-link-rc">
        		<input type="text" name="post_remote_image" class="ap-form-control" placeholder="'.__( 'Enter images link', 'ap' ).'" data-action="post_remote_image">
                <a data-action="post_image_ok" class="apicon-check ap-btn" href="#"></a>
                <a data-action="post_image_close" class="apicon-x ap-btn" href="#"></a>
            </div>
            <input type="hidden" name="attachment_ids[]" value="" />
        </div>';

	$html .= '</div>';

	return $html;
}

function ap_post_upload_hidden_form() {

	if ( ap_opt( 'allow_upload_image' ) ) {
		return '<form id="hidden-post-upload" enctype="multipart/form-data" method="POST" style="display:none">
            <input type="file" name="post_upload_image" class="ap-upload-input">
            <input type="hidden" name="ap_ajax_action" value="upload_post_image" />
            <input type="hidden" name="ap_form_action" value="upload_post_image" />
			<input type="hidden" name="__nonce" value="'.wp_create_nonce( 'upload_image_'.get_current_user_id() ).'" />
            <input type="hidden" name="action" value="ap_ajax" />
		</form>';
	}
}

/**
 * Upload and create an attachment. Set attachment meta _ap_temp_image,
 * later it will be removed when post parent is set.
 *
 * If no post parent is set then probably user canceled form submission hence we
 * don't need to keep this attachment and will removed while saving question or answer.
 *
 * @param array $file    $_FILE variable
 *
 * @return int|bool
 */
function ap_upload_user_file($file = array()) {

	require_once ABSPATH.'wp-admin/includes/admin.php';

	$file_return = wp_handle_upload($file, array(
		'test_form' => false,
		'mimes' => array(
		'jpg|jpeg' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png',
		),
	));

	if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		return false;
	} else {
		$filename = $file_return['file'];

		$attachment = array(
			// 'post_parent'         => $post_id,
			'post_mime_type' => $file_return['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $file_return['url'],
		);

		$attachment_id = wp_insert_attachment( $attachment, $file_return['file'] );

		update_post_meta( $attachment_id, '_ap_temp_image', true );

		require_once ABSPATH.'wp-admin/includes/image.php';

		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );

		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		if ( 0 < intval( $attachment_id ) ) {
			return $attachment_id;
		}
	}

	return false;
}

function ap_featured_post_btn($post_id = false) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( $post_id == false ) {
		$post_id = get_question_id();
	}

	if ( is_super_admin() ) {
		$output = '<a href="#" class="ap-btn-set-featured" id="set_featured_'.$post_id.'" data-action="set_featured" data-query="ap_ajax_action=set_featured&post_id='.$post_id.'&__nonce='.wp_create_nonce( 'set_featured_'.$post_id ).'" title="'.__( 'Make this question featured', 'ap' ).'">'.(ap_is_featured_question( $post_id ) ? __( 'Unset as featured', 'ap' ) : __( 'Set as featured', 'ap' )).'</a>';
	}

	return $output;
}

/**
 * Remove white space from string.
 *
 * @param string $contents
 *
 * @return string
 */
function ap_trim_traling_space($contents) {

	return preg_replace( '#(^(&nbsp;|\s)+|(&nbsp;|\s)+$)#', '', $contents );
}

/**
 * @param string $contents
 */
function ap_replace_square_bracket($contents) {

	$contents = str_replace( '[', '&#91;', $contents );

	$contents = str_replace( ']', '&#93;', $contents );

	return $contents;
}

function ap_clear_unused_attachments() {

	$attach = get_posts( array( 'post_type' => 'attachment', 'orderby' => 'meta_value', 'meta_key' => '_ap_temp_image' ) );

	if ( $attach ) {
		foreach ( $attach as $a ) {
			// delete unused attachments permanently
			wp_delete_attachment( $a->ID, true );
		}
	}
}

function ap_set_attachment_post_parent($attachment_id, $post_parent) {

	$attach = get_post( $attachment_id );

	if ( $attach && $attach->post_type == 'attachment' ) {
		$postarr = array(
			'ID' => $attach->ID,
			'post_parent' => $post_parent,
		);

		wp_update_post( $postarr );

		delete_post_meta( $attach->ID, '_ap_temp_image' );

		return true;
	}

	return false;
}

function ap_count_users_temproary_attachments($user_id) {

	$attachments = get_posts( array( 'post_type' => 'attachment', 'orderby' => 'meta_value', 'meta_key' => '_ap_temp_image', 'author' => $user_id ) );

	return count( $attachments );
}

function ap_user_upload_limit_crossed($user_id) {

	if ( ap_count_users_temproary_attachments( $user_id ) >= ap_opt( 'image_per_post' ) ) {
		return true;
	}

	return false;
}

/**
 * Create base page for AnsPress.
 *
 * This function is called in plugin activation. This function checks if base page already exists,
 * if not then it create a new one and update the option.
 *
 * @see anspress_activate
 * @since 2.3
 */
function ap_create_base_page() {

	// check if page already exists
	$page_id = ap_opt( 'base_page' );

	$post = get_post( $page_id );

	if ( ! $post ) {
		$args = array();
		$args['post_type'] = 'page';
		$args['post_content'] = '[anspress]';
		$args['post_status'] = 'publish';
		$args['post_title'] = __('Questions', 'ap');
		$args['post_name'] = 'questions';
		$args['comment_status'] = 'closed';

		// now create post
		$new_page_id = wp_insert_post( $args );

		if ( $new_page_id ) {
			$page = get_post( $new_page_id );
			ap_opt( 'base_page', $page->ID );
			ap_opt( 'base_page_id', $page->post_name );
		}
	}else{
		if( $post->post_title == 'ANSPRESS_TITLE' ){
			wp_update_post( array( 'ID' => $page->ID, 'post_title' => ap_opt('base_page_title') )  );
		}
	}
}

/**
 * vsprintf, sprintf, and printf do not allow for associative arrays to perform replacements `sprintf_assoc`
 * resolves this by using the key of the array in the lookup for string replacement.
 * http://php.net/manual/en/function.vsprintf.php.
 *
 * @param string $string           hey stack
 * @param array  $replacement_vars needles
 * @param string $prefix_character
 *
 * @return string
 *
 * @author codearachnid <https://gist.github.com/codearachnid/4462713>
 */
function ap_sprintf_assoc($string = '', $replacement_vars = array(), $prefix_character = '##') {

	if ( ! $string ) {
		return '';
	}

	if ( is_array( $replacement_vars ) && count( $replacement_vars ) > 0 ) {
		foreach ( $replacement_vars as $key => $value ) {
			$string = str_replace( $prefix_character.$key, $value, $string );
		}
	}

	return $string;
}

function ap_printf_assoc($string = '', $replacement_vars = array(), $prefix_character = '##') {

	echo sprintf_assoc( $string, $replacement_vars, $prefix_character );
}

/**
 * Return question id with solved prefix if answer is accepted.
 *
 * @param bool|int $question_id
 *
 * @return string
 *
 * @since  	2.3 [@see ap_page_title]
 */
function ap_question_title_with_solved_prefix($question_id = false) {

	if ( $question_id === false ) {
		$question_id = get_question_id();
	}

	$solved = ap_question_best_answer_selected( $question_id );

	if ( ap_opt( 'show_solved_prefix' ) ) {
		return ($solved ? __( '[Solved] ', 'ap' ) : '').get_the_title( $question_id );
	}

	return get_the_title( $question_id );
}

/**
 * Verify the __nonce field.
 *
 * @param string $action
 *
 * @return bool
 *
 * @since  2.4
 */
function ap_verify_nonce($action) {

	return wp_verify_nonce( $_REQUEST['__nonce'], $action );
}

/**
 * Verify default ajax nonce field
 * @return boolean
 */
function ap_verify_default_nonce() {
	if ( ! isset( $_REQUEST['ap_ajax_nonce'] ) ) {
		return false;
	}

	return wp_verify_nonce( $_REQUEST['ap_ajax_nonce'], 'ap_ajax_nonce' );
}

/**
 * Parse search string to array
 * @param  string $str search string.
 * @return array
 */
function ap_parse_search_string($str) {

	$output = array();

	// Split by space.
	$bits = explode( ' ', $str );

	// Process pairs.
	foreach ( $bits as $id => $pair ) {

		// Split the pair.
		$pairBits = explode( ':', $pair );

		// This was actually a pair.
		if ( count( $pairBits ) == 2 ) {

			$values = explode( ',', $pairBits[1] );
			$sanitized = array();

			if ( is_array( $values ) && ! empty( $values ) ) {
				foreach ( $values as $value ) {
					if ( ! empty( $value ) ) {
						$sanitized[] = sanitize_text_field( $value );
					}
				}
			}

			if ( count( $sanitized ) > 0 ) {
				// Use left part of pair as index and push right part to array.
				if ( ! empty( $pairBits[0] ) ) {
					$output[ sanitize_text_field( $pairBits[0] ) ] = $sanitized;
				}
			}

			// Remove this pair from $bits.
			unset( $bits[ $id ] );
		} // Not a pair, presumably reached the query.
		else {

			// Exit the loop.
			break;
		}
	}

	// Rebuild query with remains of $bits.
	$output['q'] = sanitize_text_field( implode( ' ', $bits ) );

	return $output;
}

function ap_ajax_json( $response ) {
	ap_send_json( ap_ajax_responce( $response ) );
}

/**
 * check if object is notification menu item
 * @param  object $menu   Menu Object.
 * @return boolean
 */
function ap_is_notification_menu($menu) {
	return in_array( 'anspress-page-notification', $menu->classes );
}

/**
 * Check if object is profile menu item
 * @param  object $menu   Menu Object.
 * @return boolean
 */
function ap_is_profile_menu($menu) {
	return in_array( 'anspress-page-profile', $menu->classes );
}

/**
 * Get the IDs of answer by question ID.
 * @param  integer $question_id Question post ID.
 * @return object
 * @since  2.4
 */
function ap_questions_answer_ids( $question_id ) {
	global $wpdb;
	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'answer' AND post_parent=%d", $question_id ) );
}

/**
 * Whitelist array items
 * @param  array $master_keys Master keys.
 * @param  array $array       Array to filter.
 * @return array
 */
function ap_whitelist_array( $master_keys, $array ) {
	return array_intersect_key( $array, array_flip( $master_keys ) );
}

/**
 * Read env file of AnsPress
 * @return string
 */
function ap_read_env() {
	$file = ANSPRESS_DIR.'/env';
	$cache = wp_cache_get( 'ap_env', 'ap' );
	if ( false !== $cache ) {
		return $cache;
	}

	if ( file_exists( $file ) ) {
		// Get the contents of env file
		$content = file_get_contents( $file );
		wp_cache_set( 'ap_env', $content, 'ap' );

		return $content;
	}

}

/**
 * Check if anspress environment is development.
 * @return boolean
 */
function ap_env_dev() {
	if ( ap_read_env() == 'development' ) {
		return true;
	}

	return false;
}
