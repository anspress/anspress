<?php
/**
 * AnsPress common functions.
 *
 * @package AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Get slug of base page.
 *
 * @return string
 * @since  2.0.0
 * @since  3.0.0 Return `questions` if base page is not selected.
 */
function ap_base_page_slug() {
	$base_page = get_post( ap_opt( 'base_page' ) );

	if ( ! $base_page ) {
		return 'questions';
	}

	$slug = $base_page->post_name;

	if ( $base_page->post_parent > 0 ) {
		$parent_page = get_post( $base_page->post_parent );
		$slug = $parent_page->post_name . '/' . $slug;
	}

	return apply_filters( 'ap_base_page_slug', $slug );
}

/**
 * Retrive permalink to base page.
 *
 * @return string URL to AnsPress base page
 * @since 2.0.0
 * @since 3.0.0 Return link to questions page if base page not selected.
 */
function ap_base_page_link() {
	if ( empty( ap_opt( 'base_page' ) ) ) {
		return home_url( '/questions/' );
	}
	return get_permalink( ap_opt( 'base_page' ) );
}

/**
 * Get all theme names from AnsPress themes directory.
 *
 * @return array
 */
function ap_theme_list() {
	$themes = array();
	$dirs = array_filter( glob( ANSPRESS_THEME_DIR . '/*' ), 'is_dir' );
	foreach ( $dirs as $dir ) {
		$themes[ basename( $dir ) ] = basename( $dir );
	}

	return $themes;
}

/**
 * Get currently active theme of AnsPress. If no theme is
 * selected then return `default`.
 *
 * @return string
 */
function ap_get_theme() {
	$option = ap_opt( 'theme' );
	if ( ! $option ) {
		return 'default';
	}
	return ap_opt( 'theme' );
}

/**
 * Get location to a file. First file is being searched in child theme and then active theme
 * and last fall back to AnsPress theme directory.
 *
 * @param 	string $file   file name.
 * @param 	mixed  $plugin Plugin path. File is search inside AnsPress extension.
 * @return 	string
 * @since 	0.1
 * @since   2.4.7 Added filter `ap_get_theme_location`
 */
function ap_get_theme_location( $file, $plugin = false ) {

	$child_path = get_stylesheet_directory() . '/anspress/' . $file;
	$parent_path = get_template_directory() . '/anspress/' . $file;

	// Checks if the file exists in the theme first,
	// Otherwise serve the file from the plugin.
	if ( file_exists( $child_path ) ) {
	    $template_path = $child_path;
	} elseif ( file_exists( $parent_path ) ) {
	    $template_path = $parent_path;
	} elseif ( false !== $plugin ) {
	    $template_path = $plugin . '/theme/' . $file;
	} else {
	    $template_path = ANSPRESS_THEME_DIR . '/' . ap_get_theme() . '/' . $file;
	}

	/**
	 * Filter AnsPress template file.
	 *
	 * @param string $template_path Path to template file.
	 * @since 2.4.7
	 */
	return apply_filters( 'ap_get_theme_location', $template_path );
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
function ap_get_theme_url( $file, $plugin = false ) {
	$child_path = get_stylesheet_directory() . '/anspress/' . $file;
	$parent_path = get_template_directory() . '/anspress/' . $file;

	// Checks if the file exists in the theme first.
	// Otherwise serve the file from the plugin.
	if ( file_exists( $child_path ) ) {
	    $template_url = get_stylesheet_directory_uri() . '/anspress/' . $file;
	} elseif ( file_exists( $parent_path ) ) {
	    $template_url = get_template_directory_uri() . '/anspress/' . $file;
	} elseif ( false !== $plugin ) {
	    $template_url = $plugin . 'theme/' . $file;
	} else {
	    $template_url = ANSPRESS_THEME_URL . '/' . ap_get_theme() . '/' . $file;
	}

	return apply_filters( 'ap_theme_url', $template_url . '?v=' . AP_VERSION );
}


/**
 * Check if current page is AnsPress. Also check if showing question or
 * answer page in buddypress.
 *
 * @return boolean
 */
function is_anspress() {
	$queried_object = get_queried_object();
	// If buddypress installed.
	if ( function_exists( 'bp_current_component' ) ) {
	    $bp_com = bp_current_component();
	    if ( 'questions' === $bp_com || 'answers' === $bp_com ) {
	        return true;
	    }
	}
	if ( ! isset( $queried_object->ID ) ) {
		return false;
	}
	if ( (int) ap_opt( 'base_page' ) === $queried_object->ID ) {
		return true;
	}
	return false;
}

/**
 * Check if current page is question page.
 *
 * @return boolean
 */
function is_question() {
	if ( is_anspress() && 'question' === ap_current_page() ) {
		return true;
	}
	return false;
}

/**
 * Is if current AnsPress page is ask page.
 *
 * @return boolean
 */
function is_ask() {
	if ( is_anspress() && ap_current_page() === ap_get_ask_page_slug() ) {
		return true;
	}
	return false;
}

/**
 * Get ask page slug.
 *
 * @return string
 */
function ap_get_ask_page_slug() {
	$opt = ap_opt( 'ask_page_slug' );
	if ( $opt ) {
		return esc_attr( $opt );
	}
	return 'ask';
}

/**
 * Checks if current AnsPress page is users page.
 *
 * @return boolean
 */
function is_ap_users() {
	if ( is_anspress() && ap_current_page() === ap_get_users_page_slug() ) {
		return true;
	}
	return false;
}

/**
 * Get users page slug.
 *
 * @return string
 */
function ap_get_users_page_slug() {
	$opt = ap_opt( 'users_page_slug' );
	if ( $opt ) {
		return esc_attr( $opt );
	}
	return 'users';
}

/**
 * Check if current page is user page.
 *
 * @return boolean
 */
function is_ap_user() {
	if ( is_anspress() && ap_current_page() === ap_get_user_page_slug() ) {
		return true;
	}
	return false;
}

/**
 * Get current question ID in single question page.
 *
 * @return integer|false
 */
function get_question_id() {
	if ( is_question() && get_query_var( 'question_id' ) ) {
		return (int) get_query_var( 'question_id' );
	}
	if ( is_question() && get_query_var( 'question' ) ) {
		return get_query_var( 'question' );
	}
	if ( is_question() && get_query_var( 'question_name' ) ) {
		$post = get_page_by_path( get_query_var( 'question_name' ), OBJECT, 'question' );
		return $post->ID;
	}
	if ( get_query_var( 'edit_q' ) ) {
		return get_query_var( 'edit_q' );
	}
	if ( ap_answer_the_object() ) {
		return ap_get_post_field( 'post_parent' );
	}
	return false;
}

/**
 * Return human readable time format.
 *
 * @param  string         $time Time.
 * @param  boolean        $unix Is $time is unix.
 * @param  integer        $show_full_date Show full date after some period. Default is 7 days in epoch.
 * @param  boolean|string $format Date format.
 * @return string|null
 * @since  2.4.7 Checks if showing default date format is enabled.
 */
function ap_human_time( $time, $unix = true, $show_full_date = 604800, $format = false ) {
	if ( false === $format ) {
		$format = get_option( 'date_format' );
	}

	if ( ! is_numeric( $time ) && ! $unix ) {
		$time = strtotime( $time );
	}

	// If default date format is enabled then just return date.
	if ( ap_opt( 'default_date_format' ) ) {
		return date_i18n( $format, $time );
	}

	if ( $time ) {
		if ( $show_full_date + $time > current_time( 'timestamp', true ) ) {
			return sprintf(
				/* translators: %s: human-readable time difference */
				__( '%s ago', 'anspress-question-answer' ),
				human_time_diff( $time, current_time( 'timestamp', true ) )
			);
		} else {
			return date_i18n( $format, $time );
		}
	}
}

/**
 * Check if user answered on a question.
 *
 * @param integer $question_id 	Question ID.
 * @param integer $user_id 		User ID.
 * @return boolean
 */
function ap_is_user_answered( $question_id, $user_id ) {
	global $wpdb;

	// @TODO Cache answer count query.
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = $question_id AND ( post_author = $user_id AND post_type = 'answer')" );

	if ( $count ) {
		return true;
	}
	return false;
}

/**
 * Count all answers of a question includes all post status.
 *
 * @param int $id question id.
 * @return int
 * @since 2.0.1.1
 */
function ap_count_all_answers( $id ) {
	global $wpdb;
	// @TODO cache db query of ap_count_all_answers.
	$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_type = %s", $id, 'answer' ) );
	return $count;
}

/**
 * Return link to asnwers.
 *
 * @param  boolean|integer $question_id Question ID.
 * @return string
 */
function ap_answers_link( $question_id = false ) {
	if ( ! $question_id ) {
		return get_permalink() . '#answers';
	}
	return get_permalink( $question_id ) . '#answers';
}


/**
 * Return edit link for question and answer.
 *
 * @param integer|object $_post Post.
 * @return string
 * @since 2.0.1
 */
function ap_post_edit_link( $_post ) {
	$_post = ap_get_post( $_post );
	$nonce = wp_create_nonce( 'nonce_edit_post_' . $_post->ID );
	$edit_link = add_query_arg( array( 'ap_page' => 'edit', 'edit_post_id' => $_post->ID, '__nonce' => $nonce ), ap_base_page_link() );
	return apply_filters( 'ap_post_edit_link', $edit_link );
}

/**
 * Returns edit post button html.
 *
 * @param boolean        $echo Echo or return.
 * @param integer|object $_post Post.
 * @return null|string
 * @since 2.0.1
 */
function ap_edit_post_link_html( $echo = false, $_post = false ) {
	$_post = ap_get_post( $_post );
	$edit_link = ap_post_edit_link( $_post );
	$output = '';

	if ( 'question' === $_post->post_type && ap_user_can_edit_question( $_post->ID ) ) {
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='" . __( 'Edit this question', 'anspress-question-answer' ) . "' class='apEditBtn'>" . __( 'Edit', 'anspress-question-answer' ) . '</a>';
	} elseif ( 'answer' === $_post->post_type && ap_user_can_edit_answer( $_post->ID ) ) {
		$output = "<a href='$edit_link' data-button='ap-edit-post' title='" . __( 'Edit this answer', 'anspress-question-answer' ) . "' class='apEditBtn'>" . __( 'Edit', 'anspress-question-answer' ) . '</a>';
	}

	if ( ! $echo ) {
		return $output;
	}
	echo $output; // xss ok.
}

/**
 * Trim strings.
 *
 * @param string $text String.
 * @param int    $limit Limit string to.
 * @param string $ellipsis Ellipsis.
 * @return string
 */
function ap_truncate_chars( $text, $limit, $ellipsis = '...' ) {
	if ( strlen( $text ) > $limit ) {
		$endpos = strpos( str_replace( array( "\r\n", "\r", "\n", "\t" ), ' ', $text ), ' ', (string) $limit );
		if ( false !== $endpos ) {
			$text = trim( substr( $text, 0, $endpos ) ) . $ellipsis;
		}
	}
	return $text;
}

/**
 * Print select anser HTML button.
 *
 * @param mixed $_post Post.
 * @return null|string
 */
function ap_select_answer_btn_html( $_post = null ) {
	$ans = ap_get_post( $_post );
	if ( ! ap_user_can_select_answer( $ans->ID ) ) {
		return;
	}
	$action = 'answer-' . $ans->ID;
	$nonce = wp_create_nonce( $action );

	if ( ! ap_have_answer_selected( $ans->post_parent ) ) {
		return '<a href="#" class="ap-btn-select ap-sicon ' . ap_icon( 'check' ) . ' ap-tip" data-action="select_answer" data-query="answer_id=' . $ans->ID . '&__nonce=' . $nonce . '&ap_ajax_action=select_best_answer" title="' . __( 'Select this answer as best', 'anspress-question-answer' ) . '">' . __( 'Select', 'anspress-question-answer' ) . '</a>';
	} elseif ( ap_have_answer_selected( $ans->post_parent ) && ap_is_selected( $ans->ID ) ) {
		return '<a href="#" class="ap-btn-select ap-sicon ' . ap_icon( 'cross' ) . ' active ap-tip" data-action="select_answer" data-query="answer_id=' . $ans->ID . '&__nonce=' . $nonce . '&ap_ajax_action=select_best_answer" title="' . __( 'Unselect this answer', 'anspress-question-answer' ) . '">' . __( 'Unselect', 'anspress-question-answer' ) . '</a>';
	}
}

/**
 * Output frontend post delete button.
 *
 * @param integer $_post Post.
 * @param boolean $echo Return or echo.
 * @return void|string
 */
function ap_post_delete_btn_html( $_post = false, $echo = false ) {
	$post_id = get_the_ID();

	if ( ap_user_can_delete_post( $post_id ) ) {
		$action = 'delete_post_' . $post_id;
		$nonce = wp_create_nonce( $action );
		$output = '<a href="#" class="delete-btn" data-action="ap_delete_post" data-query="post_id=' . $post_id . '&__nonce=' . $nonce . '&ap_ajax_action=delete_post" title="' . __( 'Delete', 'anspress-question-answer' ) . '">' . __( 'Delete', 'anspress-question-answer' ) . '</a>';
		if ( ! $echo ) {
			return $output;
		}
		echo $output; // xss ok.
	}
}

/**
 * Post restore button.
 *
 * @param  mixed   $_post Post.
 * @param  boolean $echo  Echo or return.
 * @return null|string
 */
function ap_post_restore_btn_html( $_post = null, $echo = false ) {
	$_post = ap_get_post( $_post );

	if ( ap_user_can_restore() ) {
		$action = 'restore_' . $_post->ID;
		$nonce = wp_create_nonce( $action );
		$output = '<a href="#" class="delete-btn" data-action="ajax_btn" data-query="restore_post::' . $nonce . '::' . $_post->ID . '" title="' . __( 'Restore post', 'anspress-question-answer' ) . '">' . __( 'Restore', 'anspress-question-answer' ) . '</a>';

		if ( ! $echo ) {
			return $output;
		}
		echo $output; // xss ok.
	}
}

/**
 * Permanent delete button.
 *
 * @param  mixed   $post_id Post.
 * @param  boolean $echo    Echo or return.
 * @return null|string
 */
function ap_post_permanent_delete_btn_html( $post_id = false, $echo = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}
	if ( ap_user_can_permanent_delete() ) {
		$action = 'delete_post_' . $post_id;
		$nonce = wp_create_nonce( $action );

		$output = '<a href="#" class="delete-btn" data-action="ap_delete_post" data-query="post_id=' . $post_id . '&__nonce=' . $nonce . '&ap_ajax_action=permanent_delete_post" title="' . __( 'Delete Permanently', 'anspress-question-answer' ) . '">' . __( 'Delete Permanently', 'anspress-question-answer' ) . '</a>';

		if ( ! $echo ) {
			return $output;
		}
		echo $output; // xss ok.
	}
}

/**
 * Convert number to 1K, 1M etc.
 *
 * @param  integer $num       Number to convert.
 * @param  integer $precision Precision.
 * @return string
 */
function ap_short_num( $num, $precision = 2 ) {
	if ( $num >= 1000 && $num < 1000000 ) {
		$n_format = number_format( $num / 1000, $precision ) . 'K';
	} elseif ( $num >= 1000000 && $num < 1000000000 ) {
		$n_format = number_format( $num / 1000000, $precision ) . 'M';
	} elseif ( $num >= 1000000000 ) {
		$n_format = number_format( $num / 1000000000, $precision ) . 'B';
	} else {
		$n_format = $num;
	}
	return $n_format;
}

/**
 * Sanitize comma delimited strings.
 *
 * @param string|array $str Comma delimited string.
 * @param string       $pieces_type Type of piece, string or number.
 * @return string
 */
function sanitize_comma_delimited( $str, $pieces_type = 'int' ) {
	$str = is_array( $str ) ? implode( ',', $str ) : $str;
	if ( ! empty( $str ) ) {
		$str = wp_unslash( $str );
		$sanitize = 'int' === $pieces_type ? 'intval' : 'sanitize_text_field';
		$glue = 'int' !== $pieces_type ? '","' : ',';
		$new_str = implode( $glue, array_map( $sanitize, explode( ',', $str ) ) );
		if ( 'int' !== $pieces_type ) {
			return '"' . $new_str . '"';
		}
		return $new_str;
	}
}

/**
 * Check if doing ajax request.
 *
 * @return boolean
 * @since 2.0.1
 * @since  3.0.0 Check if `ap_ajax_action` is set.
 */
function ap_is_ajax() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['ap_ajax_action'] ) ) { // input var ok.
		return true;
	}
	return false;
}

/**
 * Allow HTML tags.
 *
 * @return array
 * @since 0.9
 */
function ap_form_allowed_tags() {
	global $ap_kses_check;
	$ap_kses_check = true;
	$allowed_style = array(
		'align' => true,
	);
	$allowed_tags = array(
		'p'          => array(
			'style'    => $allowed_style,
			'title'    => true,
			),
		'span'       => array(
			'style'    => $allowed_style,
			),
		'a'          => array(
			'href'     => true,
			'title'    => true,
			),
		'br'         => array(),
		'em'         => array(),
		'strong'     => array(
			'style'    => $allowed_style,
			),
		'pre'        => array(),
		'code'       => array(),
		'blockquote' => array(),
		'img'        => array(
			'src'      => true,
			'style'    => $allowed_style,
			),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'del'        => array(),
		'br'         => array(),
	);

	/*
	 * FILTER: ap_allowed_tags
	 * Before passing allowed tags
	 */
	return apply_filters( 'ap_allowed_tags', $allowed_tags );
}

/**
 * Send a array as a JSON.
 *
 * @param array $result Results.
 */
function ap_send_json( $result = array() ) {
	header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	$result['is_ap_ajax'] = true;
	$message_type = isset( $result['message_type'] ) ? $result['message_type'] : 'success';
	$json = '<div id="ap-response">' . wp_json_encode( $result, JSON_HEX_QUOT | JSON_HEX_TAG ) . '</div>';
	wp_die( $json ); // xss ok.
}

/**
 * Highlight matching words.
 *
 * @param string $text String.
 * @param string $words Words need to higlight.
 * @return string
 * @since 	2.0
 */
function ap_highlight_words( $text, $words ) {
	$words = explode( ' ', $words );
	foreach ( $words as $word ) {
		// Quote the text for regex.
		$word = preg_quote( $word );
		// Highlight the words.
		$text = preg_replace( "/\b($word)\b/i", '<span class="highlight_word">\1</span>', $text );
	}
	return $text;
}

/**
 * Return response with type and message.
 *
 * @param string $id           messge id.
 * @param bool   $only_message return message string instead of array.
 * @return string
 * @since 2.0.0
 */
function ap_responce_message( $id, $only_message = false ) {
	$msg = array(
		'success'  => array( 'type' => 'success', 'message' => __( 'Success', 'anspress-question-answer' ) ),
		'please_login' => array( 'type' => 'warning', 'message' => __( 'You need to login before doing this action.', 'anspress-question-answer' ) ),
		'something_wrong' => array( 'type' => 'error', 'message' => __( 'Something went wrong, last action failed.', 'anspress-question-answer' ) ),
		'no_permission' => array( 'type' => 'warning', 'message' => __( 'You do not have permission to do this action.', 'anspress-question-answer' ) ),
		'draft_comment_not_allowed' => array( 'type' => 'warning', 'message' => __( 'You are commenting on a draft post.', 'anspress-question-answer' ) ),
		'comment_success'  => array( 'type' => 'success', 'message' => __( 'Comment successfully posted.', 'anspress-question-answer' ) ),
		'comment_edit_success'  => array( 'type' => 'success', 'message' => __( 'Comment updated successfully.', 'anspress-question-answer' ) ),
		'comment_delete_success' => array( 'type' => 'success', 'message' => __( 'Comment deleted successfully.', 'anspress-question-answer' ) ),
		'subscribed' => array( 'type' => 'success', 'message' => __( 'You are following this question.', 'anspress-question-answer' ) ),
		'unsubscribed'                  => array( 'type' => 'success', 'message' => __( 'Successfully unfollowed.', 'anspress-question-answer' ) ),
		'question_submitted'            => array( 'type' => 'success', 'message' => __( 'Question submitted successfully', 'anspress-question-answer' ) ),
		'question_updated'              => array( 'type' => 'success', 'message' => __( 'Question updated successfully', 'anspress-question-answer' ) ),
		'answer_submitted'              => array( 'type' => 'success', 'message' => __( 'Answer submitted successfully', 'anspress-question-answer' ) ),
		'answer_updated'                => array( 'type' => 'success', 'message' => __( 'Answer updated successfully', 'anspress-question-answer' ) ),
		'voted'                         => array( 'type' => 'success', 'message' => __( 'Thank you for voting.', 'anspress-question-answer' ) ),
		'undo_vote'                     => array( 'type' => 'success', 'message' => __( 'Your vote has been removed.', 'anspress-question-answer' ) ),
		'undo_vote_your_vote'           => array( 'type' => 'warning', 'message' => __( 'Undo your vote first.', 'anspress-question-answer' ) ),
		'cannot_vote_own_post'          => array( 'type' => 'warning', 'message' => __( 'You cannot vote on your own question or answer.', 'anspress-question-answer' ) ),
		'unselected_the_answer'         => array( 'type' => 'success', 'message' => __( 'Best answer is unselected for your question.', 'anspress-question-answer' ) ),
		'selected_the_answer'           => array( 'type' => 'success', 'message' => __( 'Best answer is selected for your question.', 'anspress-question-answer' ) ),
		'question_moved_to_trash'       => array( 'type' => 'success', 'message' => __( 'Question moved to trash.', 'anspress-question-answer' ) ),
		'answer_moved_to_trash'         => array( 'type' => 'success', 'message' => __( 'Answer moved to trash.', 'anspress-question-answer' ) ),
		'no_permission_to_view_private' => array( 'type' => 'warning', 'message' => __( 'You do not have permission to view private posts.', 'anspress-question-answer' ) ),
		'flagged'                       => array( 'type' => 'success', 'message' => __( 'Thank you for reporting this post.', 'anspress-question-answer' ) ),
		'already_flagged'               => array( 'type' => 'warning', 'message' => __( 'You have already reported this post.', 'anspress-question-answer' ) ),
		'captcha_error'                 => array( 'type' => 'error', 'message' => __( 'Please check captcha field and resubmit it again.', 'anspress-question-answer' ) ),
		'comment_content_empty'         => array( 'type' => 'error', 'message' => __( 'Comment content is empty.', 'anspress-question-answer' ) ),
		'status_updated'                => array( 'type' => 'success', 'message' => __( 'Post status updated successfully', 'anspress-question-answer' ) ),
		'post_image_uploaded'           => array( 'type' => 'success', 'message' => __( 'Image uploaded successfully', 'anspress-question-answer' ) ),
		'question_deleted_permanently'  => array( 'type' => 'success', 'message' => __( 'Question has been deleted permanently', 'anspress-question-answer' ) ),
		'answer_deleted_permanently'    => array( 'type' => 'success', 'message' => __( 'Answer has been deleted permanently', 'anspress-question-answer' ) ),
		'set_featured_question'         => array( 'type' => 'success', 'message' => __( 'Question is marked as featured.', 'anspress-question-answer' ) ),
		'unset_featured_question'       => array( 'type' => 'success', 'message' => __( 'Question is unmarked as featured.', 'anspress-question-answer' ) ),
		'upload_limit_crossed'          => array( 'type' => 'warning', 'message' => __( 'You have already attached maximum numbers of allowed uploads.', 'anspress-question-answer' ) ),
		'profile_updated_successfully'  => array( 'type' => 'success', 'message' => __( 'Your profile has been updated successfully.', 'anspress-question-answer' ) ),
		'unfollow'                      => array( 'type' => 'success', 'message' => __( 'Successfully unfollowed.', 'anspress-question-answer' ) ),
		'follow'                        => array( 'type' => 'success', 'message' => __( 'Successfully followed.', 'anspress-question-answer' ) ),
		'cannot_follow_yourself'        => array( 'type' => 'warning', 'message' => __( 'You cannot follow yourself.', 'anspress-question-answer' ) ),
		'delete_notification'           => array( 'type' => 'success', 'message' => __( 'Notification deleted successfully.', 'anspress-question-answer' ) ),
		'mark_read_notification'        => array( 'type' => 'success', 'message' => __( 'Notification is marked as read.', 'anspress-question-answer' ) ),
		'voting_down_disabled'          => array( 'type' => 'warning', 'message' => __( 'Voting down is disabled.', 'anspress-question-answer' ) ),
		'flagged_comment'               => array( 'type' => 'success', 'message' => __( 'This comment has been reported to site moderator', 'anspress-question-answer' ) ),
		'already_flagged_comment'       => array( 'type' => 'warning', 'message' => __( 'You have already reported this comment', 'anspress-question-answer' ) ),
		'you_cannot_vote_on_restricted' => array( 'type' => 'warning', 'message' => __( 'You cannot vote on restricted posts', 'anspress-question-answer' ) ),
		);

	/*
	* FILTER: ap_responce_message
	* Can be used to alter response messages
	* @var array
	* @since 2.0.1
	*/
	$msg = apply_filters( 'ap_responce_message', $msg );

	if ( isset( $msg[ $id ] ) && $only_message ) {
		return $msg[ $id ]['message'];
	}

	if ( isset( $msg[ $id ] ) ) {
		return $msg[ $id ];
	}

	return false;
}

/**
 * Format an array as valid AnsPress ajax response.
 *
 * @param  array $results Response to send.
 * @return array
 */
function ap_ajax_responce( $results ) {
	if ( ! is_array( $results ) ) {
		$message_id         = $results;
		$results            = array();
		$results['message'] = $message_id;
	}

	$results['ap_responce'] = true;

	if ( isset( $results['message'] ) ) {
		$error_message = ap_responce_message( $results['message'] );

		if ( false !== $error_message ) {
			$results['message']      = $error_message['message'];
			$results['message_type'] = $error_message['type'];
		}
	}

	// Send requested template.
	if ( isset( $results['template'] ) ) {
		$template_file = ap_get_theme_url( 'js-template/' . $results['template'] . '.html' );

		if ( ap_env_dev() ) {
			$template_file = $template_file . '&time=' . time();
		}

		$results['apTemplate'] = array(
			'name'     => $results['template'],
			'template' => $template_file,
		);
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

/**
 * Array map callback.
 *
 * @param  array $a Array.
 * @return mixed
 */
function ap_meta_array_map( $a ) {
	return $a[0];
}

/**
 * Return the current page url.
 *
 * @param array $args Arguments.
 * @return string
 * @since 2.0.0
 */
function ap_current_page_url( $args ) {
	$base = rtrim( get_permalink(), '/' );
	if ( get_option( 'permalink_structure' ) !== '' ) {
		$link = $base . '/';
		if ( ! empty( $args ) ) {
			foreach ( $args as $k => $s ) {
				$link .= $k . '/' . $s . '/';
			}
		}
	} else {
		$link = add_query_arg( $args, $base );
	}

	return $link;
}

/**
 * Sort array by order value. Group array which have same order number and then sort them.
 *
 * @param array $array Array to order.
 * @return array
 * @since 2.0.0
 */
function ap_sort_array_by_order( $array ) {
	$new_array = array();
	if ( ! empty( $array ) && is_array( $array ) ) {
		$group = array();
		foreach ( (array) $array as $k => $a ) {
			if ( ! is_array( $a ) ) {
				return;
			}
			$order = $a['order'];
			$group[ $order ][] = $a;
			$group[ $order ]['order'] = $order;
		}
		usort( $group, 'ap_sort_order_callback' );
		foreach ( (array) $group as $a ) {
			foreach ( (array) $a as $k => $newa ) {
				if ( 'order' !== $k ) {
					$new_array[] = $newa;
				}
			}
		}

		return $new_array;
	}
}

/**
 * Callback for @uses ap_sort_array_by_order.
 *
 * @param  array $a Array.
 * @param  array $b Array.
 * @return integer
 */
function ap_sort_order_callback( $a, $b ) {
	return $a['order'] - $b['order'];
}

/**
 * Echo anspress links.
 *
 * @param string|array $sub Sub page.
 * @since 2.1
 */
function ap_link_to( $sub ) {
	echo ap_get_link_to( $sub ); // xss ok.
}

	/**
	 * Return link to AnsPress pages.
	 *
	 * @param string|array $sub Sub pages/s.
	 * @return string
	 */
function ap_get_link_to( $sub ) {
	/**
	 * Define default AnsPress page slugs.
	 *
	 * @var array
	 */
	$default_pages = array(
		'question' 	 => ap_opt( 'question_page_slug' ),
		'ask' 		   => ap_opt( 'ask_page_slug' ),
		'users' 	   => ap_opt( 'users_page_slug' ),
		'user' 		   => ap_opt( 'user_page_slug' ),
	);

	$default_pages = apply_filters( 'ap_default_page_slugs', $default_pages );

	if ( is_array( $sub ) && isset( $sub['ap_page'] ) && isset( $default_pages[ $sub['ap_page'] ] ) ) {
		$sub['ap_page'] = $default_pages[ $sub['ap_page'] ];
	} elseif ( ! is_array( $sub ) && ! empty( $sub ) && isset( $default_pages[ $sub ] ) ) {
		$sub = $default_pages[ $sub ];
	}

	$base = rtrim( ap_base_page_link(), '/' );
	$args = '';

	if ( get_option( 'permalink_structure' ) !== '' ) {
		if ( ! is_array( $sub ) && 'base' !== $sub ) {
			$args = $sub ? '/' . $sub : '';
		} elseif ( is_array( $sub ) ) {
			$args = '/';

			if ( ! empty( $sub ) ) {
				foreach ( (array) $sub as $s ) {
					$args .= $s . '/';
				}
			}
		}

		$args = rtrim( $args, '/' ) . '/';
	} else {
		if ( ! is_array( $sub ) ) {
			$args = $sub ? '&ap_page=' . $sub : '';
		} elseif ( is_array( $sub ) ) {
			$args = '';

			if ( ! empty( $sub ) ) {
				foreach ( $sub as $k => $s ) {
					$args .= '&' . $k . '=' . $s;
				}
			}
		}
	}

	return esc_url( apply_filters( 'ap_link_to', $base . $args, $sub ) );
}

/**
 * Return the total numbers of post.
 *
 * @param string         $post_type Post type.
 * @param boolean|string $ap_type ap_meta type.
 * @return array
 * @since  2.0.0
 * @TODO use new qameta table.
 */
function ap_total_posts_count( $post_type = 'question', $ap_type = false ) {
	global $wpdb;

	if ( 'question' === $post_type ) {
		$type = "p.post_type = 'question'";
	} elseif ( 'answer' === $post_type ) {
		$type = "p.post_type = 'answer'";
	} else {
		$type = "(p.post_type = 'question' OR p.post_type = 'answer')";
	}

	$meta = '';
	$join = '';

	if ( $ap_type ) {
		$meta = "AND m.apmeta_type='$ap_type'";
		$join = 'INNER JOIN ' . $wpdb->prefix . 'ap_meta m ON p.ID = m.apmeta_actionid';
	}

	$where = "WHERE p.post_status NOT IN ('trash', 'draft') AND $type $meta";
	$where = apply_filters( 'ap_total_posts_count', $where );
	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p $join $where GROUP BY p.post_status";
	$cache_key = md5( $query );
	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A ); // db call ok.
	$counts = array();
	foreach ( get_post_stati() as $state ) {
		$counts[ $state ] = 0;
	}

	$counts['total'] = 0;

	foreach ( (array) $count as $row ) {
		$counts[ $row['post_status'] ] = $row['count'];
		$counts['total'] += $row['count'];
	}

	wp_cache_set( $cache_key, (object) $counts, 'counts' );
	return (object) $counts;
}

/**
 * Return total numbers of published questions.
 *
 * @return integer
 */
function ap_total_published_questions() {
	$posts = ap_total_posts_count();
	return $posts->publish;
}

/**
 * Get total numbers of solved question.
 *
 * @param string $type Valid values are int or object.
 * @return int|object
 * @TODO Use new qameta table.
 */
function ap_total_solved_questions( $type = 'int' ) {
	global $wpdb;
	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p INNER JOIN " . $wpdb->prefix . "postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_ap_selected' AND m.meta_value !='' GROUP BY p.post_status";
	$cache_key = md5( $query );
	$count = wp_cache_get( $cache_key, 'counts' );

	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A ); // db call ok.
	$counts = array();

	foreach ( get_post_stati() as $state ) {
		$counts[ $state ] = 0;
	}

	$counts['total'] = 0;

	foreach ( (array) $count as $row ) {
		$counts[ $row['post_status'] ] = $row['count'];
		$counts['total'] += $row['count'];
	}

	wp_cache_set( $cache_key, (object) $counts, 'counts' );
	$counts = (object) $counts;

	if ( 'int' === $type ) {
		return $counts->publish + $counts->closed + $counts->private_post;
	}

	return $counts;
}

/**
 * Get current sorting type.
 *
 * @return string
 * @since 2.1
 */
function ap_get_sort() {
	if ( isset( $_GET['ap_sort'] ) ) { // input var ok.
		return ap_sanitize_unslash( $_GET['ap_sort'] ); // input var ok, xss ok.
	}
}

/**
 * Register AnsPress menu.
 *
 * @param string $slug Menu slug.
 * @param string $title Menu title.
 * @param string $link Menu link.
 */
function ap_register_menu( $slug, $title, $link ) {
	anspress()->menu[ $slug ] = array( 'title' => $title, 'link' => $link );
}

/**
 * Upload form.
 *
 * @param  boolean|integer $post_id Post ID.
 * @return string
 */
function ap_post_upload_form( $post_id = false ) {
	$html = '
    <div class="ap-post-upload-form">
        <div class="ap-btn ap-upload-o ' . ap_icon( 'image' ) . '">
        	<span>' . __( 'Attach file', 'anspress-question-answer' ) . '</span>';
	if ( ap_user_can_upload_image() ) {
		$html .= '
                <a class="ap-upload-link" href="#" data-action="ap_post_upload_field">
	            	' . __( 'upload', 'anspress-question-answer' ) . '

	            </a> ' . __( 'or', 'anspress-question-answer' );
	}

	$html .= '<span class="ap-upload-remote-link">
            	' . __( 'add from link', 'anspress-question-answer' ) . '
            </span>
            <div class="ap-upload-link-rc">
        		<input type="text" name="post_remote_image" class="ap-form-control" placeholder="'.__( 'Enter link', 'anspress-question-answer' ) . '" data-action="post_remote_image">
                <a data-action="post_image_ok" class="apicon-check ap-btn" href="#"></a>
                <a data-action="post_image_close" class="apicon-x ap-btn" href="#"></a>
            </div>
            <input type="hidden" name="attachment_ids[]" value="" />
        </div>';

	$html .= '</div>';
	$media = get_attached_media( '', $post_id );
	$html .= '<div id="ap-upload-list">';
	$__nonce = wp_create_nonce( 'ap_ajax_nonce' );

	foreach ( (array) $media as $m ) {
		$html .= '<span id="' . $m->ID . '"><i class="apicon-cloud-upload"></i><a href="' . esc_url( wp_get_attachment_url( $m->ID ) ) . '">' . basename( get_attached_file( $m->ID ) ) . '</a><i class="close" data-action="ajax_btn" data-query="delete_attachment::' . $__nonce . '::' . $m->ID . '">&times;</i></span>';
	}

	$html .= '</div>';
	return $html;
}

/**
 * Upload form hidden form.
 *
 * @return string
 */
function ap_post_upload_hidden_form() {
	if ( ap_opt( 'allow_upload_image' ) ) {
		return '<form id="hidden-post-upload" enctype="multipart/form-data" method="POST" style="display:none">
            <input type="file" name="post_upload_image" class="ap-upload-input">
            <input type="hidden" name="ap_ajax_action" value="upload_post_image" />
            <input type="hidden" name="ap_form_action" value="upload_post_image" />
			<input type="hidden" name="__nonce" value="' . wp_create_nonce( 'upload_image_' . get_current_user_id() ) . '" />
            <input type="hidden" name="action" value="ap_ajax" />
		</form>';
	}
}

/**
 * Return allowed mime types.
 *
 * @return array
 * @since  3.0.0
 */
function ap_allowed_mimes() {
	$mimes = array(
		'jpg|jpeg' => 'image/jpeg',
		'gif'      => 'image/gif',
		'png'      => 'image/png',
		'doc|docx' => 'application/msword',
		'xls'      => 'application/vnd.ms-excel',
	);

	/**
	 * Filter allowed mimes types.
	 *
	 * @param array $mimes Default mimes types.
	 * @since 3.0.0
	 */
	return apply_filters( 'ap_allowed_mimes', $mimes );
}

/**
 * Upload and create an attachment. Set attachment meta _ap_temp_image,
 * later it will be removed when post parent is set.
 *
 * If no post parent is set then probably user canceled form submission hence we
 * don't need to keep this attachment and will removed while saving question or answer.
 *
 * @param array   $file           $_FILE variable.
 * @param boolean $temp           Is temproary image? If so it will be deleted if no post parent.
 * @param boolean $parent_post    Attachment parent post ID.
 * @return integer|boolean
 * @since  3.0.0 Added new argument `$post_parent`.
 */
function ap_upload_user_file( $file = array(), $temp = true, $parent_post = false ) {
	require_once ABSPATH . 'wp-admin/includes/admin.php';

	$file_return = wp_handle_upload($file, array(
		'test_form' => false,
		'mimes'     => ap_allowed_mimes(),
	));

	if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		return false;
	} else {
		$filename = $file_return['file'];
		$attachment = array(
			'post_mime_type' => $file_return['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $file_return['url'],
		);

		// Set post parent post if passed.
		if ( false !== $post_parent ) {
			$attachment['post_parent'] = $post_id;
		}

		$attachment_id = wp_insert_attachment( $attachment, $file_return['file'] );

		// Set this post as temp if no post_parent is passed.
		if ( $temp && false === $post_parent ) {
			update_post_meta( $attachment_id, '_ap_temp_image', true );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		if ( 0 < intval( $attachment_id ) ) {
			return $attachment_id;
		}
	}

	return false;
}

/**
 * Return set featured question action button.
 *
 * @param  boolean|integer $post_id Post ID.
 * @return string
 */
function ap_featured_post_btn( $post_id = false ) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( false === $post_id ) {
		$post_id = get_question_id();
	}

	if ( is_super_admin() ) {
		$output = '<a href="#" class="ap-btn-set-featured" id="set_featured_' . $post_id . '" data-action="set_featured" data-query="ap_ajax_action=set_featured&post_id=' . $post_id . '&__nonce=' . wp_create_nonce( 'set_featured_' . $post_id ) . '" title="' . __( 'Make this question featured', 'anspress-question-answer' ) . '">' . (ap_is_featured_question( $post_id ) ? __( 'Unset as featured', 'anspress-question-answer' ) : __( 'Set as featured', 'anspress-question-answer' )) . '</a>';
	}

	return $output;
}

/**
 * Remove white space from string.
 *
 * @param string $contents String.
 * @return string
 */
function ap_trim_traling_space( $contents ) {
	return preg_replace( '#(^(&nbsp;|\s)+|(&nbsp;|\s)+$)#', '', $contents );
}

/**
 * Replace square brackets in a string.
 *
 * @param string $contents String.
 */
function ap_replace_square_bracket( $contents ) {
	$contents = str_replace( '[', '&#91;', $contents );
	$contents = str_replace( ']', '&#93;', $contents );
	return $contents;
}

/**
 * Clear unused attachments of a user.
 */
function ap_clear_unused_attachments() {
	$attach = get_posts( array(
		'post_type' => 'attachment',
		'orderby'   => 'meta_value',
		'meta_key'  => '_ap_temp_image'
	) );

	if ( $attach ) {
		foreach ( $attach as $a ) {
			// Delete unused attachments permanently.
			wp_delete_attachment( $a->ID, true );
		}
	}
}

/**
 * Set parent post for an attachment.
 *
 * @param integer $attachment_id Attachment ID.
 * @param integer $post_parent   Attachment ID.
 */
function ap_set_attachment_post_parent( $attachment_id, $post_parent ) {
	$attach = ap_get_post( $attachment_id );

	if ( $attach && 'attachment' === $attach->post_type  ) {
		$postarr = array(
			'ID'          => $attach->ID,
			'post_parent' => $post_parent,
		);

		wp_update_post( $postarr );
		delete_post_meta( $attach->ID, '_ap_temp_image' );
		return true;
	}

	return false;
}

/**
 * Count temproary attachments of a user.
 *
 * @param  integer $user_id User ID.
 * @return integer
 */
function ap_count_users_temproary_attachments( $user_id ) {
	$attachments = get_posts( array(
		'post_type' => 'attachment',
		'orderby'   => 'meta_value',
		'meta_key'  => '_ap_temp_image',
		'author'    => $user_id,
	) );

	return count( $attachments );
}

/**
 * Check if user have uploaded maximum numbers of allowed attachments.
 *
 * @param  integer $user_id User ID.
 * @return boolean
 */
function ap_user_upload_limit_crossed( $user_id ) {
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
	// Check if page already exists.
	$page_id = ap_opt( 'base_page' );
	$post = ap_get_post( $page_id );

	if ( ! $post ) {
		$args                   = array();
		$args['post_type']      = 'page';
		$args['post_content']   = '[anspress]';
		$args['post_status']    = 'publish';
		$args['post_title']     = __( 'Questions', 'anspress-question-answer' );
		$args['post_name']      = 'questions';
		$args['comment_status'] = 'closed';

		// Now create post.
		$new_page_id = wp_insert_post( $args );

		if ( $new_page_id ) {
			$page = ap_get_post( $new_page_id );
			ap_opt( 'base_page', $page->ID );
			ap_opt( 'base_page_id', $page->post_name );
		}
	} else {
		if ( 'ANSPRESS_TITLE' === $post->post_title ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_title' => ap_opt( 'base_page_title' ) ) );
		}
	}
}

/**
 * Return question id with solved prefix if answer is accepted.
 *
 * @param boolean|integer $question_id Question ID.
 * @return string
 *
 * @since  	2.3 [@see ap_page_title]
 */
function ap_question_title_with_solved_prefix( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	$solved = ap_have_answer_selected( $question_id );

	if ( ap_opt( 'show_solved_prefix' ) ) {
		return get_the_title( $question_id ) . ' ' . ($solved ? __( '[Solved] ', 'anspress-question-answer' ) : '');
	}

	return get_the_title( $question_id );
}

/**
 * Verify the __nonce field.
 *
 * @param string $action Action.
 * @return bool
 * @since  2.4
 */
function ap_verify_nonce( $action ) {
	return wp_verify_nonce( ap_sanitize_unslash( $_REQUEST['__nonce'] ), $action ); // input var ok, xss ok.
}

/**
 * Verify default ajax nonce field.
 *
 * @return boolean
 */
function ap_verify_default_nonce() {
	$nonce_name = isset( $_REQUEST['ap_ajax_nonce'] ) ? 'ap_ajax_nonce' : '__nonce'; // input var okay.

	if ( ! isset( $_REQUEST[ $nonce_name ] ) ) { // input var okay.
		return false;
	}

	return wp_verify_nonce( $_REQUEST[ $nonce_name ], 'ap_ajax_nonce' ); // xss ok, input var okay.
}

/**
 * Parse search string to array.
 *
 * @param  string $str search string.
 * @return array
 */
function ap_parse_search_string( $str ) {
	$output = array();

	// Split by space.
	$bits = explode( ' ', $str );

	// Process pairs.
	foreach ( $bits as $id => $pair ) {
		// Split the pair.
		$pair_bits = explode( ':', $pair );

		// This was actually a pair.
		if ( count( $pair_bits ) === 2 ) {

			$values = explode( ',', $pair_bits[1] );
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
				if ( ! empty( $pair_bits[0] ) ) {
					$output[ sanitize_text_field( $pair_bits[0] ) ] = $sanitized;
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

/**
 * Send properly formatted AnsPress json string.
 *
 * @param  array|string $response Response array or string.
 */
function ap_ajax_json( $response ) {
	ap_send_json( ap_ajax_responce( $response ) );
}

/**
 * Check if object is notification menu item.
 *
 * @param  object $menu Menu Object.
 * @return boolean
 */
function ap_is_notification_menu( $menu ) {
	return in_array( 'anspress-page-notification', $menu->classes, true );
}

/**
 * Check if object is profile menu item.
 *
 * @param  object $menu Menu Object.
 * @return boolean
 */
function ap_is_profile_menu( $menu ) {
	return in_array( 'anspress-page-profile', $menu->classes, true );
}

/**
 * Get the IDs of answer by question ID.
 *
 * @param  integer $question_id Question post ID.
 * @return object
 * @since  2.4
 */
function ap_questions_answer_ids( $question_id ) {
	global $wpdb;
	return $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'answer' AND post_parent=%d", $question_id ) ); // db call ok.
}

/**
 * Whitelist array items.
 *
 * @param  array $master_keys Master keys.
 * @param  array $array       Array to filter.
 * @return array
 */
function ap_whitelist_array( $master_keys, $array ) {
	return array_intersect_key( $array, array_flip( $master_keys ) );
}

/**
 * Read env file of AnsPress.
 *
 * @return string
 */
function ap_read_env() {
	$file = ANSPRESS_DIR . '/env';
	$cache = wp_cache_get( 'ap_env', 'ap' );
	if ( false !== $cache ) {
		return $cache;
	}

	if ( file_exists( $file ) ) {
		// Get the contents of env file.
		$content = file_get_contents( $file );
		wp_cache_set( 'ap_env', $content, 'ap' );
		return $content;
	}

}

/**
 * Check if anspress environment is development.
 *
 * @return boolean
 */
function ap_env_dev() {
	if ( 'development' === ap_read_env() ) {
		return true;
	}

	return false;
}

/**
 * Append table name in $wpdb.
 */
function ap_append_table_names() {
	global $wpdb;

	$wpdb->ap_qameta 		     = $wpdb->prefix . 'ap_qameta';
	$wpdb->ap_votes 		     = $wpdb->prefix . 'ap_votes';
	$wpdb->ap_meta 			     = $wpdb->prefix . 'ap_meta';
	$wpdb->ap_activity 		   = $wpdb->prefix . 'ap_activity';
	$wpdb->ap_activitymeta 	 = $wpdb->prefix . 'ap_activitymeta';
	$wpdb->ap_notifications  = $wpdb->prefix . 'ap_notifications';
	$wpdb->ap_subscribers	   = $wpdb->prefix . 'ap_subscribers';

}
ap_append_table_names();

/**
 * Remove stop words from a string.
 *
 * @param  string $str String from need to be filtered.
 * @return string
 */
function ap_remove_stop_words( $str ) {
	// EEEEEEK Stop words.
	$commonWords = array( 'a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero' );

	return preg_replace( '/\b(' . implode( '|', $commonWords ) . ')\b/', '', $str );
}

/**
 * Check if $_REQUEST var exists and get value. If not return default.
 *
 * @param  string $var     Variable name.
 * @param  mixed  $default Default value.
 * @return mixed
 * @since  3.0.0
 */
function ap_isset_post_value( $var, $default = '' ) {
	if ( isset( $_REQUEST[ $var ] ) ) { // input var okay.
		return $_REQUEST[ $var ]; // input var okay, xss ok.
	}

	return $default;
}

/**
 * Get active list filter by filter key.
 *
 * @param  string $filter Filter key.
 * @return false|string|array
 * @since  3.0.0
 */
function ap_list_filters_get_active( $filter ) {
	if ( ! isset( $_GET['ap_filter'], $_GET['ap_filter'][ $filter ] ) ) { // input var okay.
		return false;
	}

	$filters = $_GET['ap_filter'][ $filter ]; // input var okay.

	if ( empty( $filters ) ) {
		return false;
	}

	return $filters;
}

/**
 * Sanitize and unslash string or array or post/get value at the same time.
 *
 * @param  string|array   $str    String or array to sanitize. Or post/get key name.
 * @param  boolean|string $from   Get value from `$_REQUEST` or `query_var`. Valid values: request, query_var.
 * @param  mixed          $default   Default value if variable not found.
 * @return array|string
 * @since  3.0.0
 */
function ap_sanitize_unslash( $str, $from = false, $default = '' ) {
	// If not false then get from $_REQUEST or query_var.
	if ( false !== $from ) {
		if ( 'request' === $from ) {
			$str = ap_isset_post_value( $str, $default );
		} elseif ( 'query_var' === $from ) {
			$str = get_query_var( $str );
		}
	}

	if ( empty( $str ) ) {
		return $default;
	}

	if ( is_array( $str ) ) {
		$str = wp_unslash( $str );
		return array_map( 'sanitize_text_field', $str );
	}

	return sanitize_text_field( wp_unslash( $str ) );
}

/**
 * Return post status based on AnsPress options.
 *
 * @param  boolean|integer $user_id    ID of user creating question.
 * @param  string          $post_type  Post type, question or answer.
 * @param  boolean         $edit       Is editing post.
 * @return string
 * @since  3.0.0
 */
function ap_new_edit_post_status( $user_id = false, $post_type = 'question', $edit = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$new_edit = $edit ? 'edit' : 'new';
	$option_key = $new_edit . '_' . $post_type . '_status';
	$status = 'publish';

	// If super admin or user have no_moderation cap.
	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_no_moderation' ) ) {
		return $status;
	}

	if ( ap_opt( $option_key ) === 'moderate' && ! ( user_can( $user_id, 'ap_moderator' ) || is_super_admin( $user_id ) ) ) {
		$status = 'moderate';
	}

	// If anonymous post status is set to moderate.
	if ( empty( $user_id ) && ap_opt( 'anonymous_post_status' ) === 'moderate' ) {
		$status = 'moderate';
	}

	return $status;
}

/**
 * Find duplicate post by content.
 *
 * @param  string $content   Post content.
 * @param  string $post_type Post type.
 * @return boolean|false
 * @since  3.0.0
 */
function ap_find_duplicate_post( $content, $post_type = 'question', $question_id = false ) {
	global $wpdb;
	$content = ap_sanitize_description_field( $content );

	// Return if content is empty. But blank content will be checked.
	if ( empty( $content ) ) {
		return false;
	}

	$question_q = false !== $question_id ? $wpdb->prepare( ' AND post_parent= %d', $question_id ) : '';

	$var = (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_content = %s AND post_type = %s {$question_q} LIMIT 1", $content, $post_type ) );

	if ( $var > 0 ) {
		return $var;
	}

	return false;
}

/**
 * Check if question suggestion is disabled.
 *
 * @return boolean
 * @since  3.0.0
 */
function ap_disable_question_suggestion() {
	/**
	 * Modify ap_disable_question_suggestion.
	 * @param boolean $enable Default is false.
	 * @since  3.0.0
	 */
	return (bool) apply_filters( 'ap_disable_question_suggestion', false );
}

/**
 * Pre fetch and cache all question and answer attachments.
 *
 * @param  array $ids Post IDs.
 * @since  4.0.0
 */
function ap_post_attach_pre_fetch( $ids ) {
	if ( $ids && is_user_logged_in() ) {
		$args = array(
		    'post_type' => 'attachment',
		    'include'   => $ids,
		);
		get_posts($args);
	}
}

/**
 * Pre fetch users and update cache.
 *
 * @param  array $ids User ids.
 * @since 4.0.0
 */
function ap_post_author_pre_fetch( $ids ) {
	$users = get_users( [ 'include' => $ids, 'fields' => array( 'ID', 'user_login', 'user_nicename', 'user_email', 'display_name' ) ] );

	foreach ( (array) $users as $user ) {
		update_user_caches( $user );
	}

	update_meta_cache( 'user', $ids );
}
