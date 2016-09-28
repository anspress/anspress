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
class AnsPress_Flag
{
	/**
	 * Ajax callback for processing comment flag button.
	 * @since 2.4
	 */
	public static function flag_comment() {
		$args = ap_sanitize_unslash( 'args', 'request' );

		// If args is empty then die.
		if ( empty( $args ) ) {
			ap_ajax_json('something_wrong' );
		}

	    $comment_id = (int) $args[0];
	    if ( ! ap_verify_nonce( 'flag_'. $comment_id ) || ! is_user_logged_in() ) {
	        ap_ajax_json('something_wrong' );
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
			'view' 		=> array( $comment_id.'_comment_flag' => $count ),
			'count' 	=> $count,
		) );
	}

	/**
	 * Ajax callback to process post flag button
	 * @since 2.0.0
	 */
	public static function flag_post() {
		$args = ap_sanitize_unslash( 'args', 'request' );
	    $post_id = (int) $args[0];

	    if ( ! ap_verify_nonce( 'flag_'.$post_id ) || ! is_user_logged_in() ) {
	        ap_ajax_json('something_wrong' );
	    }

	    $userid = get_current_user_id();
	    $is_flagged = ap_is_user_flagged( $post_id );

	    // Die if already flagged.
	    if ( $is_flagged ) {
	        ap_ajax_json( 'already_flagged' );
	    }

        $count = ap_count_flag_vote( 'flag', $post_id );
        ap_add_flag( $userid, $post_id );

        $new_count = $count + 1;
		
		// Update post meta.
		update_post_meta( $post_id, ANSPRESS_FLAG_META, $new_count );
        ap_ajax_json( array(
        	'message' => 'flagged',
        	'action' => 'flagged',
        	'view' => array( $post_id.'_flag_count' => $new_count ),
        	'count' => $new_count,
        ) );
	}

}

/**
 * Add flag vote data to ap_meta table.
 *
 * @param int        $userid
 * @param int        $actionid
 * @param null|mixed $value
 * @param null|mixed $param
 *
 * @return integer
 */
function ap_add_flag($userid, $actionid, $value = null, $param = null) {
	return ap_add_meta($userid, 'flag', $actionid, $value, $param );
}

/**
 * Retrieve flag vote count
 * If $actionid is passed then it count numbers of vote for a post
 * If $userid is passed then it count votes casted by a user.
 * If $receiving_userid is passed then it count numbers of votes received.
 *
 * @param string   $type             Type of vote, "flag" or "comment_flag".
 * @param bool|int $actionid         Post ID.
 * @param bool|int $userid           User ID of user casting the vote.
 * @param int      $receiving_userid User ID of user who received the vote
 *
 * @return integer
 */
function ap_count_flag_vote($type = 'flag', $actionid = false, $userid = false, $receiving_userid = false) {

	if ( $actionid !== false ) {
		$count = ap_meta_total_count( $type, $actionid );
	} elseif ( $userid !== false ) {
		$count = ap_meta_total_count( $type, false, $userid );
	} elseif ( $receiving_userid !== false ) {
		$count = ap_meta_total_count( $type, false, false, false, $receiving_userid );
	}

	return $count > 0 ? $count : 0 ;
}

/**
 * Count post flag votes.
 *
 * @param integer $postid
 *
 * @return int
 */
function ap_post_flag_count($postid = false) {

	global $post;

	$postid = $postid ? $postid : $post->ID;

	return ap_flagged_post_meta( $postid );
}

/**
 * Return flag count of question and answer from meta.
 * @param  integer $post_id Question/Answer Id.
 * @return integer
 */
function ap_flagged_post_meta( $post_id ) {
	return (int) get_post_meta( $post_id, ANSPRESS_FLAG_META, true );
}

/**
 * Check if user already flagged a post.
 * @param bool|integer $postid Post ID.
 * @return bool
 */
function ap_is_user_flagged($postid = false) {
	if ( is_user_logged_in() ) {
		global $post;
		$postid = $postid ? $postid : $post->ID;
		$userid = get_current_user_id();
		$done = ap_meta_user_done('flag', $userid, $postid );

		return $done > 0 ? true : false;
	}

	return false;
}

/**
 * Flag button html.
 *
 * @return string
 *
 * @since 0.9
 */
function ap_flag_btn_html($echo = false) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	global $post;
	$flagged = ap_is_user_flagged();
	$total_flag = ap_flagged_post_meta( $post->ID );
	$nonce = wp_create_nonce('flag_'.$post->ID );
	$title = ( ! $flagged) ? (__('Flag this post', 'anspress-question-answer' )) : (__('You have flagged this post', 'anspress-question-answer' ));

	$output = '<a id="flag_'.$post->ID.'" data-action="ajax_btn" data-query="flag_post::'.$nonce.'::'.$post->ID.'" class="flag-btn'.( ! $flagged ? ' can-flagged' : '').'" href="#" title="'.$title.'">'.__('Flag', 'anspress-question-answer' ).' <span class="ap-data-view ap-view-count-'.$total_flag.'" data-view="'.$post->ID.'_flag_count">'.$total_flag.'</span></a>';

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Insert flag vote for comment.
 *
 * @param int   $user_id
 * @param int   $action_id
 * @param mixed $value
 * @param mixed $param
 *
 * @return integer
 */
function ap_insert_comment_flag($user_id, $action_id, $value = null, $param = null) {
	return ap_add_meta($user_id, 'comment_flag', $action_id, $value, $param );
}

/**
 * Output flag button for the comment.
 *
 * @param bool|int $comment_id
 *
 * @since  2.4
 *
 * @return string
 */
function ap_comment_flag_btn($comment_id = false, $label = false) {
	echo ap_get_comment_flag_btn($comment_id, $label );
}

/**
 * Return flag button for the comment.
 *
 * @param bool|int $comment_id
 *
 * @since  2.4
 *
 * @return string
 */
function ap_get_comment_flag_btn($comment_id = false, $label = false) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( false === $label ) {
		$label = __('Flag', 'anspress-question-answer' );
	}

	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	$flagged = ap_is_user_flagged_comment($comment_id );
	$total_flag = ap_comment_flag_count($comment_id );

	$nonce = wp_create_nonce('flag_'.$comment_id );

	$output = '<a id="flag_'.$comment_id.'" data-query="flag_comment::'.$nonce.'::'.$comment_id.'"
    	data-action="ajax_btn" class="flag-btn'.( ! $flagged ? ' can-flag' : '').'" href="#" title="'.__('Report this comment to moderaor', 'anspress-question-answer' ).'">
    	'.$label.'<span class="ap-data-view ap-view-count-'.$total_flag.'" data-view="'.$comment_id.'_comment_flag">'.$total_flag.'</span>
    </a>';

	return $output;
}

/**
 * Return total flag count for comment.
 * @param boolean|integer $comment_id Comment ID.
 * @return integer
 */
function ap_comment_flag_count( $comment_id = false ) {
	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	$count = ap_meta_total_count('comment_flag', $comment_id );
	return apply_filters( 'ap_comment_flag_count', $count );
}

/**
 * Check if user flagged comment.
 *
 * @param bool|int $comment_id
 * @param bool|int $user_id
 *
 * @since  2.4
 *
 * @return bool
 */
function ap_is_user_flagged_comment($comment_id = false, $user_id = false) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( false === $comment_id ) {
		$comment_id = get_comment_ID();
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$done = ap_meta_user_done('comment_flag', $user_id, $comment_id );

	return $done > 0 ? true : false;
}

/**
 * Delete all flags vote of a post.
 * @param  integer $post_id Post id.
 * @return boolean
 */
function ap_delete_all_post_flags( $post_id ) {
	return ap_delete_meta( array( 'apmeta_actionid' => (int) $post_id, 'apmeta_type' => 'flag' ) );
}


