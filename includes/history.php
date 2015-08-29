<?php
/**
 * AnsPress history hooks and functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * AnsPress history class
 */
class AnsPress_History
{
	/**
	 * Construct class
	 * @param object $ap Parent class.
	 */
	public function __construct($ap) {

		$ap->add_action( 'ap_after_new_answer', $this, 'new_answer' );
		$ap->add_action( 'ap_after_update_question', $this, 'edit_question' );
		$ap->add_action( 'ap_after_update_answer', $this, 'edit_answer' );
		$ap->add_action( 'ap_publish_comment', $this, 'new_comment' );
		$ap->add_action( 'ap_select_answer', $this, 'select_answer', 10, 3 );
		$ap->add_action( 'ap_unselect_answer', $this, 'unselect_answer', 10, 3 );
	}

	/**
	 * History updated after adding new answer
	 * @param  integer $answer_id Post ID.
	 */
	public function new_answer($answer_id) {
		$post = get_post( $answer_id );
		ap_add_history( get_current_user_id(), $post->post_parent, $answer_id, 'new_answer' );
	}

	/**
	 * History updated after editing a question
	 * @param  integer $post_id Post ID.
	 */
	public function edit_question($post_id) {
		ap_add_history( get_current_user_id(), $post_id, '', 'edit_question' );
	}

	/**
	 * History updated after editing an answer.
	 * @param  integer $post_id Post ID.
	 */
	public function edit_answer($post_id) {
		ap_add_history( get_current_user_id(), $post_id, '', 'edit_answer' );
	}

	/**
	 * History updated after adding new comment.
	 * @param  object $comment Comment object.
	 */
	public function new_comment($comment) {
		$post = get_post( $comment->comment_post_ID );
		if ( $post->post_type == 'question' ) {
			ap_add_history( $comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment' );
		} else {
			$answer = get_post( $comment->comment_post_ID );
			ap_add_history( $comment->user_id, $answer->post_parent, $comment->comment_ID, 'new_comment_answer' );
			ap_add_history( $comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment_answer' );
		}
	}

	/**
	 * History updated after selecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function select_answer($user_id, $question_id, $answer_id) {
		ap_add_history( $user_id, $question_id, $answer_id, 'answer_selected' );
	}

	/**
	 * History updated after unselecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function unselect_answer($user_id, $question_id, $answer_id) {
		ap_add_history( $user_id, $question_id, $answer_id, 'answer_unselected' );
	}

}

/**
 * Insert history to database
 * @param  boolean|integer $userid     If not set current user_id will be used.
 * @param  integer         $post_id    Post ID.
 * @param  string          $value      Meta value.
 * @param  string          $param      Meta params.
 * @return false|integer
 */
function ap_add_history($userid = false, $post_id, $value, $param = null) {

	if ( ! $userid ) {
		$userid = get_current_user_id(); }

	$opts = array( 'userid' => $userid, 'actionid' => $post_id, 'value' => $value, 'param' => $param );
	$opts = apply_filters( 'ap_add_history_parms', $opts );

	extract( $opts );

	// Check last inserted history.
	$last_history = ap_get_latest_history( $value );

	// If last inserted data is same as current then update it.
	if ( $last_history && $last_history['user_id'] == $userid && $last_history['type'] == $param && $last_history['value'] == $value && @$last_history['action_id'] == $post_id ) {

		$row = ap_update_meta(
			array( 'apmeta_userid' => $userid, 'apmeta_actionid' => $post_id, 'apmeta_value' => $value, 'apmeta_param' => $param ),
			array( 'apmeta_userid' => $last_history['user_id'], 'apmeta_actionid' => $last_history['action_id'], 'apmeta_value' => $last_history['value'], 'apmeta_param' => $last_history['type'] )
		);

		update_post_meta( $post_id, '__ap_history', array( 'type' => $param, 'user_id' => $userid, 'date' => current_time( 'mysql' ) ) );
	} else {
		$row = ap_add_meta( $userid, 'history', $post_id, $value, $param );
		update_post_meta( $post_id, '__ap_history', array( 'type' => $param, 'user_id' => $userid, 'date' => current_time( 'mysql' ) ) );
	}

	if ( false !== $row ) {
		do_action( 'ap_after_history_'.$value, $userid, $post_id, $param );
		do_action( 'ap_after_inserting_history', $userid, $post_id, $value, $param );
	}

	return $row;
}

/**
 * Delete history meta and post history meta
 * @param  boolean|integer $user_id     If not set current user_id will be used.
 * @param  integer         $action_id  Action ID.
 * @param  string          $value      Meta value.
 * @param  string          $param      Meta params.
 * @return false|integer   False on fail and 1 on success.
 */
function ap_delete_history($user_id, $action_id, $value, $param = null) {
	$row = ap_delete_meta( array( 'apmeta_userid' => $user_id, 'apmeta_type' => 'history', 'apmeta_actionid' => $action_id, 'apmeta_value' => $value, 'apmeta_param' => $param ) );

	if ( $row ) {
		$last_activity = ap_get_latest_history( $action_id );
		update_post_meta( $action_id, '__ap_history', array( 'type' => $last_activity['type'], 'user_id' => $last_activity['user_id'], 'time' => $last_activity['date'] ) );
	}

	return $row;
}

/**
 * Get all history of a post
 * @param  integer $post_id Post ID.
 * @return Object
 */
function ap_get_post_history( $post_id = false) {
	if ( ! $post_id ) {
		return;
	}

	global $wpdb;

	$query = $wpdb->prepare( 'SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM ' .$wpdb->prefix .'ap_meta where apmeta_type = "history" AND apmeta_actionid = %d', $post_id );

	return ap_get_all_meta( false, 20, $query );
}

/**
 * Convert history slug to proper titles.
 * @param  string $slug History slug.
 * @param  string $parm Extra parameters.
 * @return string
 */
function ap_history_title($slug, $parm = '') {
	$title = array(
		'new_question' 		=> __( 'asked', 'ap' ),
		'new_answer' 		=> __( 'answered', 'ap' ),
		'new_comment' 		=> __( 'commented', 'ap' ),
		'new_comment_answer' => __( 'commented on answer', 'ap' ),
		'edit_question' 	=> __( 'edited question', 'ap' ),
		'edit_answer' 		=> __( 'edited answer', 'ap' ),
		'edit_comment' 		=> __( 'edited comment', 'ap' ),
		'answer_selected' 	=> __( 'selected answer', 'ap' ),
		'answer_unselected' => __( 'unselected answer', 'ap' ),
		'status_updated' 	=> __( 'updated status', 'ap' ),
	);

	$title = apply_filters( 'ap_history_name', $title );

	if ( isset( $title[ $slug ] ) ) {
		return $title[ $slug ];
	}

	return $slug;
}

/**
 * Get the latest history html
 * @param  integer $post_id Post ID.
 * @return string
 */
function ap_get_latest_history($post_id) {
	global $wpdb;

	$query = $wpdb->prepare( 'SELECT apmeta_id as meta_id, apmeta_userid as user_id, apmeta_actionid as post_id, apmeta_value as value, apmeta_param as type, apmeta_date as date FROM '. $wpdb->prefix .'ap_meta WHERE apmeta_type="history" AND apmeta_actionid=%d ORDER BY apmeta_date DESC', $post_id );

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'ap_meta' );

	if ( false !== $cache ) {
		return $cache;
	}

	$result = $wpdb->get_row( $query, ARRAY_A );
	wp_cache_set( $key, $result, 'ap_meta' );

	return $result;
}

/**
 * Get last active time
 * @param  integer $post_id Question or answer ID.
 * @return boolean $html    HTML formatted?
 * @since  2.0.1
 */
function ap_last_active_time($post_id = false, $html = true) {
	$post = get_post( $post_id );
	$post_id = ! $post_id ? get_the_ID() : $post_id;

	$history = ap_get_latest_history( $post_id );

	if ( ! $history ) {
		$history['date'] = get_the_time( 'c', $post_id );
		$history['user_id'] = $post->post_author;
		$history['type'] 	= 'new_'.$post->post_type;
	}

	if ( ! $html ) {
		return $history['date'];
	}

	$title = ap_history_title( $history['type'] );
	$title = esc_html( '<span class="ap-post-history">'.sprintf( __( '%s %s about %s ago', 'ap' ), ap_user_display_name( $history['user_id'] ), $title, '<time datetime="'. mysql2date( 'c', $history['date'] ) .'">'.ap_human_time( mysql2date( 'U', $history['date'] ) ).'</time>' ).'</span>' );

	return sprintf( __( 'Active %s ago', 'ap' ), '<a class="ap-tip" href="#" title="'. $title .'"><time datetime="'. mysql2date( 'c', $history['date'] ) .'">'.ap_human_time( mysql2date( 'U', $history['date'] ) ) ).'</time></a>';
}

/**
 * Get HTML formatted latest history of post.
 * @param  integer $post_id Question or answer ID.
 * @param  boolean $initial Show created time if no history found.
 * @param  boolean $avatar  Show avatar?
 * @param  boolean $icon    Show Icon?
 * @return string
 */
function ap_get_latest_history_html($post_id, $initial = false, $avatar = false, $icon = false) {
	$post = get_post( $post_id );
	$history = get_post_meta( $post_id, '__ap_history', true );

	if ( ! $history && $initial ) {
		$history['date'] 	= get_the_time( 'c', $post_id );
		$history['user_id'] = $post->post_author;
		$history['type'] 	= 'new_'.$post->post_type;
	}

	$html = '';
	if ( $history ) {
		if ( $icon ) {
			$html .= '<span class="'.ap_icon( $history['type'] ).' ap-tlicon"></span>'; }

		if ( $avatar ) {
			$html .= '<a class="ap-avatar" href="'.ap_user_link( $history['user_id'] ).'">'.get_avatar( $history['user_id'], 22 ).'</a>'; }

		$title = ap_history_title( $history['type'] );
		$html .= '<span class="ap-post-history">'.ap_icon( 'history', true ).sprintf( __( ' %s %s %s ago', 'ap' ), ap_user_display_name( $history['user_id'] ), $title, '<time datetime="'. mysql2date( 'c', $history['date'] ) .'">'.ap_human_time( $history['date'], false ).'</time>' ) .'</span>';
	}

	if ( $html ) {
		return apply_filters( 'ap_latest_history_html', $html ); }

	return false;
}

/**
 * Restore __ap_history meta of question or answer
 * @param  integer $post_id
 * @return void
 */
function ap_restore_question_history($post_id) {
	$history = ap_get_latest_history( $post_id );

	if ( ! $history ) {
		delete_post_meta( $post_id, '__ap_history' ); } else {
		update_post_meta( $post_id, '__ap_history', array( 'type' => $history['type'], 'user_id' => $history['user_id'], 'date' => $history['date'] ) ); }
}

/**
 * Remove new answer history from ap_meta table and update post meta history
 * @param  integer $answer_id
 * @return integer|false
 */
function ap_remove_new_answer_history($answer_id) {
	$row = ap_delete_meta( array( 'apmeta_type' => 'history', 'apmeta_value' => $answer_id, 'apmeta_param' => 'new_answer' ) );

	return $row;
}
