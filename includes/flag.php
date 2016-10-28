<?php
/**
 * All functions and classes related to flagging
 *
 * This file keep all function required by flagging system.
 *
 * @link https://anspress.io
 * @since 2.3.4
 *
 * @package AnsPress
 **/

/**
 * All flag methods.
 */
class AnsPress_Flag {

	/**
	 * Ajax callback for processing comment flag button.
	 *
	 * @since 2.4
	 */
	public static function flag_comment() {
		$args = ap_sanitize_unslash( 'args', 'request' );

		// If args is empty then die.
		if ( empty( $args ) ) {
			ap_ajax_json( 'something_wrong' );
		}

	    $comment_id = (int) $args[0];
	    if ( ! ap_verify_nonce( 'flag_' . $comment_id ) || ! is_user_logged_in() ) {
	        ap_ajax_json( 'something_wrong' );
	    }

	    $userid = get_current_user_id();
	    $is_flagged = ap_is_user_flagged_comment( $comment_id );

	    // Die if already flagged comment.
	    if ( $is_flagged ) {
	        ap_ajax_json( 'already_flagged_comment' );
	    }

		ap_insert_comment_flag( $userid, $comment_id );
		$count = ap_comment_flag_count( $comment_id );
		update_comment_meta( $comment_id, ANSPRESS_FLAG_META, $count );

		ap_ajax_json( array(
			'message' 	=> 'flagged_comment',
			'action' 	=> 'flagged',
			'view' 		=> array( $comment_id . '_comment_flag' => $count ),
			'count' 	=> $count,
		) );
	}

	/**
	 * Ajax callback to process post flag button
	 *
	 * @since 2.0.0
	 */
	public static function flag_post() {
		$args = ap_sanitize_unslash( 'args', 'request' );
		$post_id = (int) $args[0];

		if ( ! ap_verify_nonce( 'flag_' . $post_id ) || ! is_user_logged_in() ) {
				ap_ajax_json( 'something_wrong' );
		}

		$userid = get_current_user_id();
		$is_flagged = ap_is_user_flagged( $post_id );

		// Die if already flagged.
		if ( $is_flagged ) {
				ap_ajax_json( 'already_flagged' );
		}

		ap_add_flag( $post_id );
		$counts = ap_update_flags_count( $post_id );
		ap_ajax_json( array(
			'message' => 'flagged',
			'action' => 'flagged',
			'view' => array( $post_id . '_flag_count' => $counts ),
			'count' => $counts,
		) );
	}

}

/**
 * Add flag vote data to ap_votes table.
 *
 * @param integer $post_id     Post ID.
 * @param integer $user_id     User ID.
 * @return integer|boolean
 */
function ap_add_flag( $post_id, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	return ap_vote_insert( $post_id, $user_id, 'flag' );
}

/**
 * Count flag votes.
 *
 * @param integer $post_id Post ID.
 * @return  integer
 * @since  4.0.0
 */
function ap_count_post_flags( $post_id ) {
	$rows = ap_count_votes( [ 'vote_post_id' => $post_id, 'vote_type' => 'flag' ] );

	if ( false !== $rows ) {
		return (int) $rows[0]->count;
	}

	return 0;
}

/**
 * Check if user already flagged a post.
 *
 * @param bool|integer $post Post.
 * @return bool
 */
function ap_is_user_flagged( $post = null ) {
	$_post = ap_get_post( $post );

	if ( is_user_logged_in() ) {
		return ap_is_user_voted( $_post->ID, 'flag' );
	}

	return false;
}

/**
 * Flag button html.
 *
 * @param mixed   $post Post.
 * @param boolean $echo Echo or return.
 * @return string
 * @since 0.9
 */
function ap_flag_btn_html( $post = null, $echo = false ) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$_post = ap_get_post( $post );
	$flagged = ap_is_user_flagged();
	$nonce = wp_create_nonce( 'flag_' . $_post->ID );
	$title = ( ! $flagged) ? (__( 'Flag this post', 'anspress-question-answer' )) : (__( 'You have flagged this post', 'anspress-question-answer' ));

	$output = '<a id="flag_' . $_post->ID . '" data-action="ajax_btn" data-query="flag_post::' . $nonce . '::' . $_post->ID . '" class="flag-btn' . ( ! $flagged ? ' can-flagged' : '') . '" href="#" title="' . $title . '">' . __( 'Flag', 'anspress-question-answer' ) . ' <span class="ap-data-view ap-view-count-' . $_post->flags . '" data-view="' . $_post->ID . '_flag_count">' . $_post->flags . '</span></a>';

	if ( ! $echo ) {
		return $output;
	}

	echo $output; // xss okay.
}

/**
 * Insert flag vote for comment.
 *
 * @param integer $user_id User ID.
 * @param integer $comment_id Comment ID.
 * @return integer
 */
function ap_insert_comment_flag( $user_id, $comment_id ) {
	return add_comment_meta( $action_id, '__ap_flag',  $comment_id );
}

/**
 * Output flag button for the comment.
 *
 * @param bool|integer $comment_id Comment ID.
 * @param bool|string  $label Label.
 * @since  2.4
 */
function ap_comment_flag_btn( $comment_id = false, $label = false ) {
	echo ap_get_comment_flag_btn( $comment_id, $label ); // xss okay.
}

/**
 * Return flag button for the comment.
 *
 * @param bool|integer $comment_id Comment ID.
 * @param bool|integer $label Label.
 * @since  2.4
 * @return string
 */
function ap_get_comment_flag_btn( $comment_id = false, $label = false ) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( false === $label ) {
		$label = __( 'Flag', 'anspress-question-answer' );
	}

	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	$flagged = ap_is_user_flagged_comment( $comment_id );
	$total_flag = ap_comment_flag_count( $comment_id );

	$nonce = wp_create_nonce( 'flag_' . $comment_id );

	$output = '<a id="flag_' . $comment_id . '" data-query="flag_comment::' . $nonce . '::' . $comment_id . '"
    	data-action="ajax_btn" class="flag-btn' . ( ! $flagged ? ' can-flag' : '') . '" href="#" title="' . __( 'Report this comment to moderaor', 'anspress-question-answer' ) . '">
    	' . $label . '<span class="ap-data-view ap-view-count-' . $total_flag . '" data-view="' . $comment_id . '_comment_flag">' . $total_flag . '</span>
    </a>';

	return $output;
}

/**
 * Return total flag count for comment.
 *
 * @param boolean|integer $comment_id Comment ID.
 * @return integer
 */
function ap_comment_flag_count( $comment_id = false ) {
	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	$cache = wp_cache_get( $comment_id, 'ap_comment_flags' );

	if ( false !== $cache ) {
		return $cache;
	}

	global $wpdb;
	$counts = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) as count, c.comment_ID FROM $wpdb->comments c INNER JOIN {$wpdb->commentmeta} m ON c.comment_ID = m.comment_id WHERE m.meta_key = '%s' AND m.meta_value NOT NULL", '__ap_flag' ) ); // db call okay.

	wp_cache_set( $comment_id, $counts, 'ap_comment_flags' );

	return apply_filters( 'ap_comment_flag_count', $counts );
}

/**
 * Check if user flagged comment.
 *
 * @param bool|int $comment_id Comment ID.
 * @param bool|int $user_id User ID.
 * @since  2.4
 * @return bool
 */
function ap_is_user_flagged_comment( $comment_id = false, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	// If user_id or comment id is empty then return false.
	if ( empty( $user_id ) || empty( $comment_id ) ) {
		return false;
	}

	$cache = wp_cache_get( $user_id, 'ap_user_flagged_comment' );

	if ( false !== $cache ) {
		return $cache;
	}

	$counts = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->comments c INNER JOIN {$wpdb->commentmeta} m ON c.comment_ID = m.comment_id WHERE m.meta_key = '%s' AND m.meta_value = %d", '__ap_flag', $user_id ) ); // db call okay.

	$ret = $counts > 0 ? true : false;
	wp_cache_set( $user_id, $ret, 'ap_user_flagged_comment' );

	return $ret;
}

