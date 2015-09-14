<?php
/**
 * AnsPress history hooks and functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2015 Rahul Aryan
 */

/**
 * AnsPress activity hooks
 */
class AnsPress_Activity_Hook
{
	/**
	 * Construct class
	 * @param object $ap Parent class.
	 */
	public function __construct($ap) {
		$ap->add_action( 'ap_after_new_question', $this, 'new_question' );
		$ap->add_action( 'ap_after_new_answer', $this, 'new_answer' );
		$ap->add_action( 'ap_after_update_question', $this, 'edit_question' );
		$ap->add_action( 'ap_after_update_answer', $this, 'edit_answer' );
		$ap->add_action( 'ap_publish_comment', $this, 'new_comment' );
		$ap->add_action( 'ap_select_answer', $this, 'select_answer', 10, 3 );
		$ap->add_action( 'ap_unselect_answer', $this, 'unselect_answer', 10, 3 );
	}


	/**
	 * Add activity after inserting new question.
	 * @param  integer $question_id Post ID.
	 */
	public function new_question($question_id) {
		$question = get_post( $question_id );

		$activity_arr = array(
			'user_id' 			=> $question->post_author,
			'type' 				=> 'new_question',
			'question_id' 		=> $question->post_parent,
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $answer->post_parent, '__ap_activity', array( 'type' => 'new_question', 'user_id' => $question->post_author, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after adding new answer
	 * @param  integer $answer_id Post ID.
	 */
	public function new_answer($answer_id) {
		$answer = get_post( $answer_id );
		$question = get_post( $answer->post_parent );

		$activity_arr = array(
			'user_id' 			=> $answer->post_author,
			'secondary_user' 	=> $question->post_author,
			'type' 				=> 'new_answer',
			'item_id'			=> $answer_id,
			'question_id' 		=> $answer->post_parent,
			'for_post' 			=> true,
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $answer->post_parent, '__ap_activity', array( 'type' => 'new_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'new_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after editing a question
	 * @param  integer $post_id Post ID.
	 */
	public function edit_question($post_id) {
		$question = get_post( $post_id );

		$activity_arr = array(
			'user_id' 			=> get_current_user_id(),
			'type' 				=> 'edit_question',
			'question_id'		=> $post_id,
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $post_id, '__ap_activity', array( 'type' => 'edit_question', 'user_id' => $question->post_author, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after editing an answer.
	 * @param  integer $post_id Post ID.
	 */
	public function edit_answer($post_id) {
		$answer = get_post( $post_id );

		$activity_arr = array(
			'secondary_user' 	=> $answer->post_author,
			'type' 				=> 'edit_answer',
			'item_id'			=> $post_id,
			'question_id'		=> $answer->post_parent,
		);

		ap_new_activity( $activity_arr );

		// Add answer activity meta.
		update_post_meta( $post_id, '__ap_activity', array( 'type' => 'edit_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after adding new comment.
	 * @param  object $comment Comment object.
	 */
	public function new_comment($comment) {
		$post = get_post( $comment->comment_post_ID );

		if ( 'question' != $post->post_type && 'answer' != $post->post_type ) {
			return;
		}

		$activity_arr = array(
			'item_id'			=> $comment->comment_ID,
		);

		if ( $post->post_type == 'question' ) {
			$activity_arr['type'] = 'new_comment';
			$activity_arr['question_id'] = $comment->comment_post_ID;
		} else {
			$activity_arr['type'] = 'new_comment_answer';
			$activity_arr['question_id'] = $post->post_parent;
		}

		ap_new_activity( $activity_arr );

		// Add comment activity meta.
		update_comment_meta( $comment->comment_ID, '__ap_activity', array( 'type' => 'new_comment', 'user_id' => $comment->user_id, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after selecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function select_answer($user_id, $question_id, $answer_id) {
		$activity_arr = array(
			'user_id' 			=> $user_id,
			'type' 				=> 'answer_selected',
			'item_id'			=> $answer_id,
			'question_id'		=> $question_id,
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $question_id, '__ap_activity', array( 'type' => 'answer_selected', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'best_answer', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * History updated after unselecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function unselect_answer($user_id, $question_id, $answer_id) {
		$activity_arr = array(
			'user_id' 			=> $user_id,
			'type' 				=> 'answer_unselected',
			'item_id'			=> $answer_id,
			'question_id'		=> $question_id,
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $question_id, '__ap_activity', array( 'type' => 'answer_unselected', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'unselected_best_answer', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );
	}

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
