<?php
/**
 * Subscribers hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @package   AnsPress/AnsPress_Subscriber_Hooks
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register subscriber hooks
 */
class AnsPress_Subscriber_Hooks
{
	/**
	 * Update question subscribers count.
	 * @param  integer $user_id User ID.
	 * @param  integer $item_id Item id.
	 * @param  string  $activity Activity name.
	 */
	public static function subscriber_count( $user_id, $item_id, $activity ) {
		$q_activity = array( 'q_all' );
		$tax_activity = array( 'tax_new' );

		$counts = ap_subscribers_count( $item_id, $activity );

		if ( in_array( $activity, $q_activity ) ) {
			update_post_meta( $item_id, ANSPRESS_SUBSCRIBER_META, $counts );
		}
	}

	/**
	 * Subscribe post author for all activities on question.
	 * @param  integer $question_id 	Question ID.
	 * @param  object  $question     	Post object.
	 */
	public static function after_new_question($question_id, $question) {
		ap_subscribe_question( $question );
	}

	/**
	 * Subscribe post author for all activities on answer.
	 * @param  integer $answer_id 	Answer ID.
	 * @param  object  $answer    	Answer object.
	 */
	public static function after_new_answer($answer_id, $answer) {
		if ( ! ap_is_user_subscribed( $answer->ID, 'a_all', $answer->post_author ) ) {
			ap_new_subscriber( $answer->post_author, $answer->ID, 'a_all', $answer->post_parent );
		}
	}

	/**
	 * Subscribe user for post comments
	 * @param  object $comment Comment object.
	 */
	public static function after_new_comment($comment) {
		$post = get_post( $comment->comment_post_ID );

		$type = 'q_post';
		$question_id = $post->ID;
		
		if ( 'answer' == $post->post_type ) {
			$type = 'a_all';
			$question_id = $post->post_parent;
		}

		if ( ! ap_is_user_subscribed( $comment->comment_post_ID, $type, $comment->user_id ) ) {
			ap_new_subscriber( $comment->user_id, $comment->comment_post_ID, $type, $question_id );
		}
	}

	/**
	 * Action triggred after unpublishing a comment.
	 * @param  object|array $comment Comment obejct.
	 */
	public static function unpublish_comment($comment) {
		$comment = (object) $comment;
		$post = get_post( $comment->comment_post_ID );

		if ( $post->post_type == 'question' ) {
			ap_remove_subscriber( $post->ID, $comment->user_id, 'q_post' );
		} elseif ( $post->post_type == 'answer' ) {
			ap_remove_subscriber( $post->ID, $comment->user_id, 'a_all' );
		}
	}

	/**
	 * Remove question subscriptions before delete
	 * @param  integer $question_id Question ID.
	 */
	public static function delete_question( $question_id) {
		ap_remove_subscriber( $question_id, false, 'q_all' );
	}

	/**
	 * Remove answer subscriptions before delete
	 * @param  integer $answer_id answer ID.
	 */
	public static function delete_answer( $answer_id ) {
		ap_remove_subscriber( $answer_id, false, 'a_all' );
	}

	/**
	 * Process ajax subscribe request.
	 */
	public static function subscribe() {
		$args = ap_sanitize_unslash( 'args', 'request', false );

		// Check if args is empty, if so than die.
		if( false === $args ){
			ap_ajax_json('something_wrong');
		}

		// Die if user is not logged in.
		if ( ! is_user_logged_in() ) {
			ap_ajax_json( 'please_login' );
		}

		$action_id = (int) $args[0];
		$type = $args[1];

		if ( ! ap_verify_nonce( 'subscribe_'.$action_id.'_'.$type ) ) {
			ap_ajax_json('something_wrong');
		}

		$question_id = 0;

		if ( 'tax_new_q' === $type ) {
			$subscribe_type = 'tax_new_q';
		} else {
			$subscribe_type = 'q_all';
			$question_id = $action_id;
		}

		$user_id = get_current_user_id();

		$is_subscribed = ap_is_user_subscribed( $action_id, $subscribe_type, $user_id );

		$elm = '#subscribe_'.$action_id.' .ap-btn';

		// If already subscribed then unsubscribe.
		if ( $is_subscribed ) {
			$row = ap_remove_subscriber( $action_id, $user_id, $subscribe_type );

			if ( false !== $row ) {
				$count = ap_subscribers_count( $action_id, $subscribe_type );
				ap_ajax_json( array(
					'message' 		=> 'unsubscribed',
					'action' 		=> 'unsubscribed',
					'do' 			=> array( 'updateHtml' => $elm.' .text', 'toggle_active_class' => $elm ),
					'count' 		=> $count,
					'html' 			=> __( 'Follow', 'anspress-question-answer' ),
					'view' 			=> array( 'subscribe_'.$action_id => $count ),
				) );
			}
		}

		// New subscription.
		$row = ap_new_subscriber( $user_id, $action_id, $subscribe_type, $question_id );

		if ( false !== $row ) {
			$count = ap_subscribers_count( $action_id, $subscribe_type );
			ap_ajax_json( array(
				'message' 		=> 'subscribed',
				'action' 		=> 'subscribed',
				'do' 			=> array( 'updateHtml' => '#subscribe_'.$action_id.' .text', 'toggle_active_class' => $elm ),
				'count' 		=> $count,
				'html' 			=> __( 'Unfollow', 'anspress-question-answer' ),
				'view' 			=> array( 'subscribe_'.$action_id => $count ),
			) );
		}
	}

}
