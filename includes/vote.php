<?php
/**
 * Handle all function related to voting system.
 *
 * @link http://anspress.io
 */

/**
 * Add vote meta.
 *
 * @param int    $current_userid    User ID of user casting the vote
 * @param string $type              Type of vote, "vote_up" or "vote_down"
 * @param int    $actionid          Post ID
 * @param int    $receiving_userid User ID of user receiving the vote. @since 2.3
 *
 * @return integer
 */
function ap_add_vote($current_userid, $type, $actionid, $receiving_userid) {

	$row = ap_add_meta( $current_userid, $type, $actionid, $receiving_userid );

	if ( $row !== false ) {
		do_action( 'ap_vote_casted', $current_userid, $type, $actionid, $receiving_userid );
	}

	return $row;
}

/**
 * @param string $type
 * @param integer $actionid
 */
function ap_remove_vote($type, $userid, $actionid, $receiving_userid) {

	$row = ap_delete_meta( array( 'apmeta_type' => $type, 'apmeta_userid' => $userid, 'apmeta_actionid' => $actionid ) );

	if ( $row !== false ) {
		do_action( 'ap_vote_removed', $userid, $type, $actionid, $receiving_userid );
	}

	return $row;
}

/**
 * Retrieve vote count
 * If $actionid is passed then it count numbers of vote for a post
 * If $userid is passed then it count votes casted by a user.
 * If $receiving_userid is passed then it count numbers of votes received.
 *
 * @param bool|int $userid           User ID of user casting the vote
 * @param string   $type             Type of vote, "vote_up" or "vote_down"
 * @param boolean $actionid         Post ID
 * @param integer $receiving_userid User ID of user who received the vote
 *
 * @return int
 */
function ap_count_vote($userid = false, $type, $actionid = false, $receiving_userid = false) {

	if ( $actionid !== false ) {
		return ap_meta_total_count( $type, $actionid );
	} elseif ( $userid !== false ) {
		return ap_meta_total_count( $type, false, $userid );
	} elseif ( $receiving_userid !== false ) {
		return ap_meta_total_count( $type, false, false, false, $receiving_userid );
	}

	return 0;
}

// get $post up votes
function ap_up_vote($echo = false) {

	global $post;

	if ( $echo ) {
		echo $post->voted_up;
	} else {
		return $post->voted_up;
	}
}

// get $post down votes
function ap_down_vote($echo = false) {

	global $post;

	if ( $echo ) {
		echo $post->voted_down;
	} else {
		return $post->voted_down;
	}
}

// get $post net votes
function ap_net_vote($post = false) {

	if ( ! $post ) {
		global $post;
	}

	$net = $post->net_vote;

	return $net ? $net : 0;
}

function ap_net_vote_meta($post_id = false) {

	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	$net = get_post_meta( $post_id, ANSPRESS_VOTE_META, true );

	return $net ? $net : 0;
}

/**
 * @param int $postid
 */
function ap_post_votes($postid) {

	$vote = array();
	// voted up count
	$vote['voted_up'] = ap_meta_total_count( 'vote_up', $postid );

	// voted down count
	$vote['voted_down'] = ap_meta_total_count( 'vote_down', $postid );

	// net vote
	$vote['net_vote'] = $vote['voted_up'] - $vote['voted_down'];

	return $vote;
}

/**
 * Check if user voted on given post.
 *
 * @param int    $actionid
 * @param string $type
 * @param int    $userid
 *
 * @return bool
 *
 * @since 	2.0
 */
function ap_is_user_voted($actionid, $type, $userid = false) {

	if ( false === $userid ) {
		$userid = get_current_user_id();
	}

	if ( $type == 'vote' && is_user_logged_in() ) {
		global $wpdb;

		$query = $wpdb->prepare( 'SELECT apmeta_type as type, IFNULL(count(*), 0) as count FROM '.$wpdb->prefix.'ap_meta where (apmeta_type = "vote_up" OR apmeta_type = "vote_down") and apmeta_userid = %d and apmeta_actionid = %d GROUP BY apmeta_type', $userid, $actionid );

		$key = md5( $query );

		$user_done = wp_cache_get( $key, 'counts' );

		if ( $user_done === false ) {
			$user_done = $wpdb->get_row( $query );
			wp_cache_set( $key, $user_done, 'counts' );
		}

		return $user_done;
	} elseif ( is_user_logged_in() ) {
		$done = ap_meta_user_done( $type, $userid, $actionid );

		return $done > 0 ? true : false;
	}

	return false;
}

/**
 * Output voting button.
 *
 * @param int $post
 *
 * @return null|string
 *
 * @since 0.1
 */
function ap_vote_btn($post = false, $echo = true) {

	if ( false === $post ) {
		global $post;
	}

	if ( 'answer' == $post->post_type && ap_opt( 'disable_voting_on_answer' ) ) {
		return;
	}

	if ( 'question' == $post->post_type && ap_opt( 'disable_voting_on_question' ) ) {
		return;
	}

	$nonce = wp_create_nonce( 'vote_'.$post->ID );
	$vote = ap_is_user_voted( $post->ID, 'vote' );

	$voted = $vote ? true : false;

	$type = $vote ? $vote->type : '';

	$html = '';
	$html .= '<div data-id="'.$post->ID.'" class="ap-vote net-vote" data-action="vote">';
	$html .= '<a class="'.ap_icon( 'vote_up' ).' ap-tip vote-up'.($voted ? ' voted' : '').($type == 'vote_down' ? ' disable' : '').'" data-query="ap_ajax_action=vote&type=up&post_id='.$post->ID.'&__nonce='.$nonce.'" href="#" title="'.__( 'Up vote this post', 'anspress-question-answer' ).'"></a>';

	$html .= '<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount">'.ap_net_vote().'</span>';

	if ( ('question' == $post->post_type && ! ap_opt( 'disable_down_vote_on_question' )) ||
		('answer' == $post->post_type && ! ap_opt( 'disable_down_vote_on_answer' )) ) {
		$html .= '<a data-tipposition="bottom center" class="'.ap_icon( 'vote_down' ).' ap-tip vote-down'.($voted ? ' voted' : '').($type == 'vote_up' ? ' disable' : '').'" data-query="ap_ajax_action=vote&type=down&post_id='.$post->ID.'&__nonce='.$nonce.'" href="#" title="'.__( 'Down vote this post', 'anspress-question-answer' ).'"></a>';
	}

	$html .= '</div>';

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * post close vote count.
 *
 * @param bool|int $postid
 *
 * @return int
 */
function ap_post_close_vote($postid = false) {

	global $post;

	$postid = $postid ? $postid : $post->ID;

	return ap_meta_total_count( 'close', $postid );
}

// check if user voted for close
function ap_is_user_voted_closed($postid = false) {

	if ( is_user_logged_in() ) {
		global $post;
		$postid = $postid ? $postid : $post->ID;
		$userid = get_current_user_id();
		$done = ap_meta_user_done( 'close', $userid, $postid );

		return $done > 0 ? true : false;
	}

	return false;
}

// TODO: re-add closing system as an extension
function ap_close_vote_html() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	global $post;
	$nonce = wp_create_nonce( 'close_'.$post->ID );
	$title = ( ! $post->voted_closed) ? (__( 'Vote for closing', 'anspress-question-answer' )) : (__( 'Undo your vote', 'anspress-question-answer' ));
	?>
		<a id="<?php echo 'close_'.$post->ID;
	?>" data-action="close-question" class="close-btn<?php echo ($post->voted_closed) ? ' closed' : '';
	?>" data-args="<?php echo $post->ID.'-'.$nonce;
	?>" href="#" title="<?php echo $title;
	?>">
			<?php _e( 'Close ', 'anspress-question-answer' );
			echo($post->closed > 0 ? '<span>('.$post->closed.')</span>' : '');
	?>
        </a>
	<?php

}
