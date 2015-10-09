<?php
/**
 * Subscribers hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 * @package   AnsPress/AnsPress_Notifications_Hooks
 */

/**
 * Register subscriber hooks
 */
class AnsPress_Subscriber_Hooks
{
	/**
	 * AnsPress main class
	 * @var object
	 */
	protected $ap;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @param AnsPress $ap Parent class object.
	 */
	public function __construct($ap) {
		$ap->add_action( 'ap_new_subscriber', $this, 'subscriber_count', 1, 3 );
		$ap->add_action( 'ap_removed_subscriber', $this, 'subscriber_count', 1, 3 );
		$ap->add_action( 'ap_after_new_question', $this, 'after_new_question', 10, 2 );
		$ap->add_action( 'ap_after_new_answer', $this, 'after_new_answer', 10, 2 );
		$ap->add_action( 'ap_publish_comment', $this, 'after_new_comment' );
		$ap->add_action( 'ap_unpublish_comment', $this, 'unpublish_comment' );
		$ap->add_action( 'ap_before_delete_question', $this, 'delete_question' );
		$ap->add_action( 'ap_before_delete_answer', $this, 'delete_answer' );
	}

	/**
	 * Update question subscribers count.
	 * @param  integer $user_id User ID.
	 * @param  integer $item_id Item id.
	 * @param  string  $activity Activity name.
	 */
	function subscriber_count( $user_id, $item_id, $activity ) {
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
	public function after_new_question($question_id, $question) {
		ap_subscribe_question( $question );
	}

	/**
	 * Subscribe post author for all activities on answer.
	 * @param  integer $answer_id 	Answer ID.
	 * @param  object  $answer    	Answer object.
	 */
	public function after_new_answer($answer_id, $answer) {
		if ( ! ap_is_user_subscribed( $answer->ID, 'a_all', $answer->post_author ) ) {
			ap_new_subscriber( $answer->post_author, $answer->ID, 'a_all', $answer->post_parent );
		}
	}

	/**
	 * Subscribe user for post comments
	 * @param  object $comment Comment object.
	 */
	public function after_new_comment($comment) {
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
	public function unpublish_comment($comment) {
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
	public function delete_question( $question_id) {
		ap_remove_subscriber( $question_id, false, 'q_all' );
	}

	/**
	 * Remove answer subscriptions before delete
	 * @param  integer $answer_id answer ID.
	 */
	public function delete_answer( $answer_id ) {
		ap_remove_subscriber( $answer_id, false, 'a_all' );
	}

}
