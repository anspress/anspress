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
 * AnsPress activity hooks
 */
class AnsPress_Activity_Hook
{
	/**
	 * Construct class
	 * @param AnsPress $ap Parent class.
	 */
	public function __construct($ap) {
		$ap->add_action( 'ap_after_new_question', $this, 'new_question' );
		$ap->add_action( 'ap_after_new_answer', $this, 'new_answer' );
		$ap->add_action( 'ap_after_update_question', $this, 'edit_question' );
		$ap->add_action( 'ap_after_update_answer', $this, 'edit_answer' );
		$ap->add_action( 'ap_publish_comment', $this, 'new_comment' );
		$ap->add_action( 'ap_select_answer', $this, 'select_answer', 10, 3 );
		$ap->add_action( 'ap_unselect_answer', $this, 'unselect_answer', 10, 3 );
		$ap->add_action( 'ap_trash_answer', $this, 'trash_post' );
		$ap->add_action( 'ap_untrash_answer', $this, 'untrash_post' );
		$ap->add_action( 'ap_before_delete_answer', $this, 'delete_post' );
		$ap->add_action( 'ap_trash_question', $this, 'trash_post' );
		$ap->add_action( 'ap_trash_question', $this, 'untrash_post' );
		$ap->add_action( 'ap_before_delete_question', $this, 'delete_post' );
		$ap->add_action( 'trashed_comment', $this, 'trash_comment' );
		$ap->add_action( 'comment_trash_to_approved', $this, 'comment_approved' );
		$ap->add_action( 'delete_comment', $this, 'delete_comment' );
		$ap->add_action( 'edit_comment', $this, 'edit_comment' );
		$ap->add_action( 'ap_added_follower', $this, 'follower', 10, 2 );
		// $ap->add_action( 'ap_vote_casted', $this, 'notify_upvote', 10, 4 );
		// $ap->add_action( 'ap_added_reputation', $this, 'ap_added_reputation', 10, 4 );
	}


	/**
	 * Add activity after inserting new question.
	 * @param  integer $question_id Post ID.
	 */
	public function new_question($question_id) {
		$question = get_post( $question_id );

		$question_title = '<a class="ap-q-link" href="'. get_permalink( $question_id ) .'">'. get_the_title( $question_id ) .'</a>';

		$txo_type = array();

		if ( taxonomy_exists( 'question_category' ) ) {
			$txo_type[] = 'question_category';
		}

		if ( taxonomy_exists( 'question_tag' ) ) {
			$txo_type[] = 'question_tag';
		}

		$term_ids = array();
		if ( ! empty( $txo_type ) ) {
			$terms = get_the_terms( $question_id, $txo_type );

			if ( $terms ) {
				foreach ( $terms as $t ) {
					$term_ids[] = $t->term_id;
				}
				$term_ids = implode(',', $term_ids );
			}
		}

		$activity_arr = array(
			'user_id' 			=> $question->post_author,
			'type' 				=> 'new_question',
			'question_id' 		=> $question_id,
			'permalink' 		=> get_permalink( $question_id ),
			'content'			=> sprintf( __( '%s asked question %s', 'anspress-question-answer' ), ap_activity_user_name( $question->post_author ), $question_title ),
			'term_ids'			=> $term_ids,
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $question_id, '__ap_activity', array( 'type' => 'new_question', 'user_id' => $question->post_author, 'date' => current_time( 'mysql' ) ) );

		$this->check_mentions( $question_id, $question->post_content, $question_title, $question->post_author, __( 'question', 'anspress-question-answer' ) );

		// Notify users.
		// ap_new_notification($activity_id, $question->post_author);
	}

	/**
	 * History updated after adding new answer
	 * @param  integer $answer_id Post ID.
	 */
	public function new_answer($answer_id) {
		$answer = get_post( $answer_id );
		$question = get_post( $answer->post_parent );

		$answer_title = '<a class="ap-q-link" href="'. get_permalink( $answer_id ) .'">'. get_the_title( $answer_id ) .'</a>';

		$activity_arr = array(
			'user_id' 			=> $answer->post_author,
			'secondary_user' 	=> $question->post_author,
			'type' 				=> 'new_answer',
			'question_id' 		=> $answer->post_parent,
			'answer_id' 		=> $answer_id,
			'permalink' 		=> get_permalink( $answer_id ),
			'content'			=> sprintf( __( '%s answered on %s', 'anspress-question-answer' ), ap_activity_user_name( $answer->post_author ), $answer_title ),
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $answer->post_parent, '__ap_activity', array( 'type' => 'new_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'new_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );

		// Notify users.
		$subscribers = ap_subscriber_ids( $answer->post_parent, 'q_all' );

		// Remove current user from subscribers.
		$subscribers = ap_unset_current_user_from_subscribers( $subscribers );

		ap_new_notification( $activity_id, $subscribers );

		$this->check_mentions( $answer->post_parent, $answer->post_content, $answer_title, $question->post_author, __( 'answer', 'anspress-question-answer' ), $answer_id );
	}

	/**
	 * History updated after editing a question
	 * @param  integer $post_id Post ID.
	 */
	public function edit_question($post_id) {
		$question = get_post( $post_id );

		$question_title = '<a class="ap-q-link" href="'. get_permalink( $post_id ) .'">'. get_the_title( $post_id ) .'</a>';

		$activity_arr = array(
			'user_id' 			=> get_current_user_id(),
			'type' 				=> 'edit_question',
			'question_id'		=> $post_id,
			'permalink' 		=> get_permalink( $post_id ),
			'content' 			=> sprintf( __( '%s edited question %s', 'anspress-question-answer' ), ap_activity_user_name( get_current_user_id() ), $question_title ),
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $post_id, '__ap_activity', array( 'type' => 'edit_question', 'user_id' => $question->post_author, 'date' => current_time( 'mysql' ) ) );

		// Notify users.
		$subscribers = ap_subscriber_ids( false, array( 'q_all', 'a_all' ), $post_id );

		// Remove current user from subscribers.
		$subscribers = ap_unset_current_user_from_subscribers( $subscribers );

		ap_new_notification( $activity_id, $subscribers );
	}

	/**
	 * History updated after editing an answer.
	 * @param  integer $post_id Post ID.
	 */
	public function edit_answer($post_id) {
		$answer = get_post( $post_id );

		$answer_title = '<a class="ap-q-link" href="'. get_permalink( $post_id ) .'">'. get_the_title( $post_id ) .'</a>';

		$activity_arr = array(
			'secondary_user' 	=> $answer->post_author,
			'type' 				=> 'edit_answer',
			'question_id'		=> $answer->post_parent,
			'answer_id' 		=> $post_id,
			'permalink' 		=> get_permalink( $post_id ),
			'content'			=> sprintf( __( '%s edited answer %s', 'anspress-question-answer' ), ap_activity_user_name( $answer->post_author ), $answer_title ),
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Add answer activity meta.
		update_post_meta( $post_id, '__ap_activity', array( 'type' => 'edit_answer', 'user_id' => $answer->post_author, 'date' => current_time( 'mysql' ) ) );

		// Notify users.
		$subscribers = ap_subscriber_ids( $post_id, 'a_all' );

		// Remove current user from subscribers.
		$subscribers = ap_unset_current_user_from_subscribers( $subscribers );

		ap_new_notification( $activity_id, $subscribers );
	}

	/**
	 * History updated after adding new comment.
	 * @param  object $comment Comment object.
	 */
	public function new_comment($comment) {
		$post = get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		$activity_arr = array(
			'item_id'	=> $comment->comment_ID,
			'permalink' => get_comment_link( $comment ),
			'parent_type' => 'comment',
		);

		$user = ap_activity_user_name( get_current_user_id() );

		$comment_excerpt = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $comment->comment_ID ) .'">'. get_comment_excerpt( $comment->comment_ID ) .'</a></span>';

		$post_title = '<a class="ap-q-link" href="'. get_permalink( $comment->comment_post_ID ) .'">'. get_the_title( $comment->comment_post_ID ) .'</a>';

		if ( $post->post_type == 'question' ) {
			$activity_arr['type'] = 'new_comment';
			$activity_arr['question_id'] = $comment->comment_post_ID;
			$activity_arr['content'] = sprintf( __( '%s commented on question %s %s', 'anspress-question-answer' ), $user, $post_title, $comment_excerpt );
		} else {
			$activity_arr['type'] = 'new_comment_answer';
			$activity_arr['question_id'] = $post->post_parent;
			$activity_arr['answer_id'] = $comment->comment_post_ID;
			$activity_arr['content'] = sprintf( __( '%s commented on answer %s %s', 'anspress-question-answer' ), $user, $post_title, $comment_excerpt );
		}

		$activity_id = ap_new_activity( $activity_arr );

		// Add comment activity meta.
		update_post_meta( $comment->comment_post_ID, '__ap_activity', array( 'type' => 'new_comment', 'user_id' => $comment->user_id, 'date' => current_time( 'mysql' ) ) );

		if ( $post->post_type == 'question' ) {
			$subscribers = ap_subscriber_ids( $comment->comment_post_ID, array( 'q_post', 'q_all' ) );
		} else {
			$subscribers = ap_subscriber_ids( $comment->comment_post_ID, 'a_all' );
		}

		// Remove current user from subscribers.
		$subscribers = ap_unset_current_user_from_subscribers( $subscribers );

		ap_new_notification( $activity_id, $subscribers );
	}

	/**
	 * History updated after selecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function select_answer($user_id, $question_id, $answer_id) {

		$question = get_post( $question_id );
		$answer = get_post( $answer_id );

		$question_title = '<a class="ap-q-link" href="'. get_permalink( $question_id ) .'">'. $question->post_title .'</a>';

		$activity_arr = array(
			'user_id' 			=> $user_id,
			'type' 				=> 'answer_selected',
			'question_id'		=> $question_id,
			'answer_id' 		=> $answer_id,
			'permalink' 		=> get_permalink( $answer_id ),
			'content' 			=> sprintf( __( '%s selected best answer for %s', 'anspress-question-answer' ), ap_activity_user_name( $user_id ), $question_title ),
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $question_id, '__ap_activity', array( 'type' => 'answer_selected', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'best_answer', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );

		$user_ids = array( $answer->post_author );

		if ( get_current_user_id() != $question->post_author ) {
			$user_ids[] = $question->post_author;
		}

		ap_new_notification( $activity_id, $user_ids );
	}

	/**
	 * History updated after unselecting an answer.
	 * @param  integer $user_id     User ID.
	 * @param  integer $question_id Question ID.
	 * @param  integer $answer_id   Answer ID.
	 */
	public function unselect_answer($user_id, $question_id, $answer_id) {
		$question_title = '<a class="ap-q-link" href="'. get_permalink( $question_id ) .'">'. get_the_title( $question_id ) .'</a>';

		$activity_arr = array(
			'user_id' 			=> $user_id,
			'type' 				=> 'answer_unselected',
			'question_id'		=> $question_id,
			'answer_id' 		=> $answer_id,
			'permalink' 		=> get_permalink( $answer_id ),
			'content' 			=> sprintf( __( '%s unselected best answer for question %s', 'anspress-question-answer' ), ap_activity_user_name( $user_id ), $question_title ),
		);

		ap_new_activity( $activity_arr );

		// Add question activity meta.
		update_post_meta( $question_id, '__ap_activity', array( 'type' => 'answer_unselected', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );

		// Add answer activity meta.
		update_post_meta( $answer_id, '__ap_activity', array( 'type' => 'unselected_best_answer', 'user_id' => $user_id, 'date' => current_time( 'mysql' ) ) );
	}

	/**
	 * Change status of activity status after question/answer is trashed.
	 * @param  integer $post_id Post ID.
	 */
	public function trash_post( $post_id ) {
		ap_change_post_activities_status( $post_id, 'trash' );
	}

	/**
	 * Change status of activity status after answer/question is untrashed.
	 * @param  integer $answer_id Answer id.
	 */
	public function untrash_post( $answer_id ) {
		ap_change_post_activities_status( $answer_id, 'publish' );
	}

	/**
	 * Delete activities of an answer when its get deleted.
	 * @param  object $answer_id    Post object.
	 */
	public function delete_post( $answer_id ) {
		$activity_ids = ap_post_activities_id( $answer_id );

		if ( $activity_ids ) {
			foreach ( $activity_ids as $ids ) {
				ap_delete_activity( $ids );
			}
		}
	}

	/**
	 * Change status of comment after comment is trashed
	 * @param  object $comment_id Comment object.
	 */
	public function trash_comment($comment_id) {
		$comment = get_comment( $comment_id );
		$post = get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		return ap_update_activities( array( 'item_id' => $comment_id, 'parent_type' => 'comment' ) , array( 'status' => 'trash' ) );
	}

	/**
	 * Change status of comment after comment is untrashed
	 * @param  object $comment Comment object.
	 */
	public function comment_approved($comment) {
		$post = get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		return ap_update_activities( array( 'item_id' => $comment->comment_ID, 'parent_type' => 'comment' ) , array( 'status' => 'publish' ) );
	}

	/**
	 * Unpublish comment
	 * @param integer $comment_id Comment object.
	 */
	public function delete_comment( $comment_id ) {

		$comment = get_comment( $comment_id );
		$post = get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		$activity_ids = ap_activity_ids_by_item_id( $comment->comment_ID, 'comment' );

		if ( $activity_ids ) {
			foreach ( $activity_ids as $ids ) {
				ap_delete_activity( $ids );
			}
		}
	}

	/**
	 * Insert activity about new follower
	 * @param  integer $user_to_follow  Whom to follow.
	 * @param  integer $current_user_id Current user ID.
	 */
	public function follower($user_to_follow, $current_user_id) {

		$activity_arr = array(
			'user_id' 			=> $current_user_id,
			'type' 				=> 'follower',
			'secondary_user' 	=> $user_to_follow,
			'item_id' 			=> $user_to_follow,
			'parent_type' 		=> 'user',
			'permalink' 		=> ap_user_link( $user_to_follow ),
			'content' 			=> sprintf( __( '%s started following %s', 'anspress-question-answer' ), ap_activity_user_name( $current_user_id ), ap_activity_user_name( $user_to_follow ) ),
		);

		$activity_id = ap_new_activity( $activity_arr );
		ap_new_notification( $activity_id, $user_to_follow );
	}

	/**
	 * Insert activity about upvote
	 * @param  integer $userid           User ID who is voting.
	 * @param  string  $type             Vote type.
	 * @param  integer $actionid         Post ID.
	 * @param  integer $receiving_userid User who is receiving vote.
	 */
	public function notify_upvote($userid, $type, $actionid, $receiving_userid) {

		if ( 'vote_up' == $type ) {

			$activity_arr = array(
				'user_id' 			=> $userid,
				'type' 				=> 'vote_up',
				'secondary_user' 	=> $receiving_userid,
				'item_id' 			=> $actionid,
				'parent_type' 		=> 'post',
				'permalink' 		=> get_permalink( $actionid ),
			);

			$activity_id = ap_new_activity( $activity_arr );

			// Insert a notification.
			// ap_new_notification( $activity_id, $receiving_userid );
		}
	}

	/**
	 * Add activitt for new reputation
	 * @param  integer $user_id    User ID.
	 * @param  integer $action_id  Action ID.
	 * @param  integer $reputation Points earned.
	 * @param  string  $type       Vote type.
	 */
	public function ap_added_reputation( $user_id, $action_id, $reputation, $type ) {
		$activity_arr = array(
			'user_id' 			=> $user_id,
			'type' 				=> 'reputation_gain',
			'item_id' 			=> $action_id,
			'parent_type' 		=> 'user',
			'permalink' 		=> get_permalink( $action_id ),
			'reputation' 		=> $reputation,
			'reputation_type' 	=> $type,
		);

		$activity_id = ap_new_activity( $activity_arr );

		// Insert a notification.
		// ap_new_notification( $activity_id, $user_id );
	}

	public function edit_comment($comment_id) {
		$comment = get_comment( $comment_id );
		$post = get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		$activity_arr = array(
			'item_id'	=> $comment->comment_ID,
			'permalink' => get_comment_link( $comment ),
			'parent_type' => 'comment',
		);

		$user = ap_activity_user_name( get_current_user_id() );

		$comment_excerpt = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $comment->comment_ID ) .'">'. get_comment_excerpt( $comment->comment_ID ) .'</a></span>';

		$post_title = '<a class="ap-q-link" href="'. get_permalink( $comment->comment_post_ID ) .'">'. get_the_title( $comment->comment_post_ID ) .'</a>';

		if ( $post->post_type == 'question' ) {
			$activity_arr['type'] = 'new_comment';
			$activity_arr['question_id'] = $comment->comment_post_ID;
			$activity_arr['content'] = sprintf( __( '%s commented on question %s %s', 'anspress-question-answer' ), $user, $post_title, $comment_excerpt );
		} else {
			$activity_arr['type'] = 'new_comment_answer';
			$activity_arr['question_id'] = $post->post_parent;
			$activity_arr['answer_id'] = $comment->comment_post_ID;
			$activity_arr['content'] = sprintf( __( '%s commented on answer %s %s', 'anspress-question-answer' ), $user, $post_title, $comment_excerpt );
		}

		$activity_id = ap_new_activity( $activity_arr );

		// Add comment activity meta.
		update_post_meta( $comment->comment_post_ID, '__ap_activity', array( 'type' => 'new_comment', 'user_id' => $comment->user_id, 'date' => current_time( 'mysql' ) ) );

		if ( $post->post_type == 'question' ) {
			$subscribers = ap_subscriber_ids( $comment->comment_post_ID, array( 'q_post', 'q_all' ) );
		} else {
			$subscribers = ap_subscriber_ids( $comment->comment_post_ID, 'a_all' );
		}

		// Remove current user from subscribers.
		$subscribers = ap_unset_current_user_from_subscribers( $subscribers );

		ap_new_notification( $activity_id, $subscribers );
	}

	public function check_mentions($question_id, $contents, $title, $user_id, $type, $answer_id = 0) {
		$users = ap_find_mentioned_users( $contents );

		if ( false !== $users ) {
			$user_title = ap_activity_user_name( $user_id );
			foreach ( $users as $user ) {
				if ( $user->id != $user_id ) {
					$activity_arr = array(
						'user_id' 			=> $user_id,
						'type' 				=> 'mention',
						'secondary_user' 	=> $user->id,
						'question_id' 		=> $question_id,
						'answer_id' 		=> $answer_id,
						'permalink' 		=> get_permalink( $question_id ),
						'content'			=> sprintf( __( '%s mentioned you in %s %s', 'anspress-question-answer' ), $user_title, $type, $title ),
					);

					$activity_id = ap_new_activity( $activity_arr );
					ap_new_notification( $activity_id, $user->id );
				}
			}
		}
	}

}

