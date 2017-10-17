<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Echo post status of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 *
 * @deprecated 4.1.0 This function is replaced by `AP_Question::get_status_object()`.
 * @see AP_Question::get_status_object()
 */
function ap_question_status( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.0', 'AP_Question::get_status_object()' );

	$_post = ap_get_post( $_post );

	if ( 'publish' === $_post->post_status ) {
		return;
	}

	$status_obj = get_post_status_object( $_post->post_status );
	echo '<span class="ap-post-status ' . esc_attr( $_post->post_status ) . '">' . esc_attr( $status_obj->label ) . '</span>';
}

/**
 * Question meta to display.
 *
 * @param false|integer $question_id Question id.
 * @deprecated 4.1.0 Use @see AP_Question::get_display_meta() instead.
 */
function ap_question_metas( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_the_ID();
	}

	$metas = array();

	// If featured question.
	if ( ap_is_featured_question( $question_id ) ) {
		$metas['featured'] = __( 'Featured', 'anspress-question-answer' );
	}

	if ( ap_have_answer_selected() ) {
		$metas['solved'] = '<i class="apicon-check"></i><i>' . __( 'Solved', 'anspress-question-answer' ) . '</i>';
	}

	$view_count = ap_get_post_field( 'views' );
	$metas['views'] = '<i class="apicon-eye"></i><i>' . sprintf( __( '%d views', 'anspress-question-answer' ), $view_count ) . '</i>';

	if ( is_question() ) {
		$last_active 	= ap_get_last_active( get_question_id() );
		$metas['active'] = '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime="' . mysql2date( 'c', $last_active ) . '">' . $last_active . '</time></i>';
	}

	if ( ! is_question() ) {
		$metas['history'] = '<i class="apicon-pulse"></i>' . ap_latest_post_activity_html( $question_id, ! is_question() );
	}

	$metas = apply_filters( 'ap_display_question_metas', $metas, $question_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	echo $output; // xss ok.
}
