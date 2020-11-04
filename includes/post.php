<?php
/**
 * Functions used inside question and answer loop.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since     4.2.0
 */

namespace AnsPress\Post;

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print ID of current question in loop.
 *
 * @return void
 * @since 4.2.0
 */
function qustion_id() {
	echo (int) get_question_id();
}

/**
 * Get question permalink.
 *
 * @param integer $question_id Question ID.
 * @return string
 * @since 4.2.0
 */
function get_question_permalink( $question_id = 0 ) {
	$question_id = get_question_id( $question_id );
	$permalink   = get_permalink( $question_id );

	/**
	 * Filter `get_question_permalink`.
	 *
	 * @param string  $permalink   Permalink.
	 * @param integer $question_id Question ID.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_question_permalink', $permalink, $question_id );
}

/**
 * Output question permalink in a loop.
 *
 * @param integer $question_id Question id.
 * @since 4.2.0
 */
function question_permalink( $question_id = 0 ) {
	echo esc_url( get_question_permalink( $question_id ) );
}

/**
 * Get the content of question or answer in a loop.
 *
 * @param integer $post_id Question or answer id.
 * @return string
 * @since 4.2.0
 */
function get_content( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	// Check if password is required
	if ( post_password_required( $post_id ) ) {
		return get_the_password_form();
	}

	$content = get_post_field( 'post_content', $post_id );

	return $content;
}

/**
 * Output question content.
 *
 * @param integer $post_id
 * @since 4.2.0
 */
function question_content( $post_id = 0 ) {
	echo apply_filters( 'the_content', get_content( $post_id ), $post_id );
}

/**
 * Get answer content.
 *
 * @param integer $post_id Answer id.
 * @since 4.2.0
 */
function answer_content( $post_id = 0 ) {
	echo apply_filters( 'the_content', get_content( $post_id ), $post_id );
}

/**
 * Get numbers of comment of a question or answer in a loop.
 *
 * @param integer $post_id Post id.
 * @return integer
 * @since 4.2.0
 */
function get_comment_number( $post_id = 0 ) {
	$post_id       = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );
	$comment_count = get_post_field( 'comment_count', $post_id );

	return apply_filters( 'ap_get_comment_number', (int) $comment_count, $post_id );
}

/**
 * Output comment count in question or answer loop.
 *
 * @param integer $post_id Question or answer ID.
 * @since 4.2.0
 */
function comment_number( $post_id = 0 ) {
	$count = get_comment_number( $post_id );

	printf( _n( '%s Comment', '%s Comments', $count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $count . '</span>' );
}

/**
 * A wrapper function for @see ap_the_comments() for using in
 * post templates.
 *
 * @return void
 * @since 4.2.0
 */
function comments( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	echo '<apcomments id="comments-' . esc_attr( $post_id ) . '" class="have-comments">';
	ap_the_comments( $post_id, [], true );
	echo '</apcomments>';

	// New comment button.
	echo ap_comment_btn_html( $post_id );
}

/**
 * Post actions button.
 *
 * @since   4.2.0
 */
function actions_button( $post_id = 0 ) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$post_id = get_question_id( $post_id );

	$args = wp_json_encode( [
		'post_id' => $post_id,
		'nonce'   => wp_create_nonce( 'post-actions-' . $post_id ),
	] );

	echo '<postActions class="ap-dropdown"><button class="ap-btn apicon-gear ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" apquery="' . esc_js( $args ) . '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>';
}

/**
 * Output vote button for question or answer.
 *
 * @param integer $post_id Post id.
 * @return void
 * @since 4.2.0
 */
function vote_buttons( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	ap_vote_btn( $post_id );
}

/**
 * Output question metas.
 *
 * @param integer $question_id Question ID.
 * @return void
 * @since 4.2.0
 */
function question_metas( $question_id = 0 ) {
	$question_id = get_question_id( $question_id );
	ap_question_metas( $question_id );
}

/**
 * Get the ID of answer within the loop.
 *
 * @return void
 * @since  4.2.0
 */
function answer_id() {
	echo (int) ap_get_answer_id();
}

/**
 * Return select answer button HTML.
 *
 * @param integer $answer_id Answer ID.
 * @return string
 * @since 4.2.0
 */
function get_select_button( $answer_id = 0 ) {
	if ( ! ap_user_can_select_answer( $answer_id ) ) {
		return;
	}

	$_post = ap_get_post( $answer_id );
	$nonce = wp_create_nonce( 'select-answer-' . $_post->ID );

	$q = esc_js( wp_json_encode( [
		'answer_id' => $_post->ID,
		'__nonce'   => $nonce,
	] ) );

	$active = false;

	$title = __( 'Select this answer as best', 'anspress-question-answer' );
	$label = __( 'Select', 'anspress-question-answer' );

	$have_best = ap_have_answer_selected( $_post->post_parent );
	$selected  = ap_is_selected( $_post );
	$hide      = false;

	if ( $have_best && $selected ) {
		$title  = __( 'Unselect this answer', 'anspress-question-answer' );
		$label  = __( 'Unselect', 'anspress-question-answer' );
		$active = true;
	}

	if ( $have_best && ! $selected ) {
		$hide = true;
	}

	return '<a href="#" class="ap-btn-select ap-btn ' . ( $active ? ' active' : '' ) . ( $hide ? ' hide' : '' ) . '" ap="select_answer" apquery="' . $q . '" title="' . $title . '">' . $label . '</a>';
}

/**
 * Output select answer button in a loop.
 *
 * @param integer $answer_id Answer ID.
 * @since 4.2.0
 */
function select_button( $answer_id = 0 ) {
	echo get_select_button( $answer_id );
}

function get_answer_count() {
	return (int) anspress()->answer_query->found_posts;
}

function answer_count() {
	echo (int) get_answer_count();
}

/**
 * Answer pagination count.
 *
 * @return string
 * @since 4.2.0
 */
function get_answer_pagination_count() {
	$query     = anspress()->answer_query;
	$ret       = '';
	$start_num = intval( ( $query->paged - 1 ) * $query->posts_per_page ) + 1;
	$from_num  = number_format_i18n( $start_num );
	$to_num    = ( $start_num + ( $query->posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts: $start_num + ( $query->posts_per_page - 1 );
	$total_int = (int) $query->found_posts;
	$total     = number_format_i18n( $total_int );

	$ret = sprintf(
		_n( 'Viewing %2$s answer (of %4$s total)', 'Viewing %1$s answers - %2$s through %3$s (of %4$s total)', $query->post_count, 'anspress-question-answer' ),
		$query->post_count, $from_num, $to_num, $total
	);

	// Filter and return
	return apply_filters( 'ap_answer_pagination_count', esc_html( $ret ) );
}

/**
 * Output answers pagination count.
 *
 * @return void
 */
function answer_pagination_count() {
	echo esc_html( get_answer_pagination_count() );
}

/**
 * Get answer pagination links.
 *
 * @return string
 * @since 4.2.0
 */
function get_answer_pagination_links() {
	$query = anspress()->answer_query;

	if ( ! isset( $query->pagination_links ) || empty( $query->pagination_links ) ) {
		return false;
	}

	return apply_filters( 'ap_get_answer_pagination_links', $query->pagination_links );
}

/**
 * Output answers pagination links.
 *
 * @return void
 * @since 4.2.0
 */
function answer_pagination_links() {
	echo get_answer_pagination_links();
}

/**
 * Answers tab links.
 *
 * @param string|boolean $base Current page url.
 * @since 4.2.0
 */
function get_answers_tab_links( $base = false ) {
	$active = ap_sanitize_unslash( 'order_by', 'r', ap_opt( 'answers_sort' ) );

	if ( false === $base ) {
		$base = get_permalink();
	}

	$links = array(
		'active' => array(
			'link'  => add_query_arg( [ 'order_by' => 'active' ], $base ),
			'title' => __( 'Active', 'anspress-question-answer' ),
		),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$links['voted'] = array(
			'link'  => add_query_arg( [ 'order_by' => 'voted' ], $base ),
			'title' => __( 'Voted', 'anspress-question-answer' ),
		);
	}

	$links['newest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'newest' ], $base ),
		'title' => __( 'Newest', 'anspress-question-answer' ),
	);

	$links['oldest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'oldest' ], $base ),
		'title' => __( 'Oldest', 'anspress-question-answer' ),
	);

	foreach ( $links as $slug => $args ) {
		if ( $slug === $active ) {
			$links[ $slug ]['active'] = true;
		}
	}

	/**
	 * Answers tabs links.
	 *
	 * @param array $links Answers link.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_answers_tab_links', $links );
}
