<?php
/**
 * Register all ajax hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @package   AnsPress/ajax
 */

/**
 * Register all ajax callback
 */
class AP_Notification
{
	/**
	 * AnsPress main class
	 * @var [type]
	 */
	protected $ap;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @param AnsPress $ap Parent class object.
	 */
	public function __construct($ap) {
	    $ap->add_action( 'ap_added_follower', $this, 'notify_user_about_follower', 10, 2 );
	    $ap->add_action( 'ap_vote_casted', $this, 'notify_upvote', 10, 4 );
	    $ap->add_action( 'ap_after_new_answer', $this, 'after_new_answer', 10, 2 );
	    $ap->add_action( 'ap_after_update_question', $this, 'after_update_question', 10, 2 );
	    $ap->add_action( 'ap_after_update_answer', $this, 'after_update_answer', 10, 2 );
	    $ap->add_action( 'ap_publish_comment', $this, 'publish_comment' );
	}

	/**
	 * Insert notification about upvote
	 * @param  integer $userid           User ID who is voting.
	 * @param  string  $type             Vote type.
	 * @param  integer $actionid         Post ID.
	 * @param  integer $receiving_userid User who is receiving vote.
	 */
	public function notify_upvote($userid, $type, $actionid, $receiving_userid) {
		if ( 'vote_up' == $type && ap_is_profile_active() ) {
			ap_insert_notification( $userid, $receiving_userid, 'vote_up', array( 'post_id' => $actionid ) );
		}
	}

	/**
	 * Insert notification about new follower
	 * @param  integer $user_to_follow  Whom to follow.
	 * @param  integer $current_user_id Current user ID.
	 */
	public function notify_user_about_follower($user_to_follow, $current_user_id) {
		if ( ap_is_profile_active() ) {
			ap_insert_notification( $current_user_id, $user_to_follow, 'new_follower' );
		}
	}

	/**
	 * Insert notification when new answer is received
	 * @param  integer $post_id Answer ID.
	 * @param  object  $post    WP post object.
	 */
	public function after_new_answer($post_id, $post) {
		if ( ap_is_profile_active() ) {
			$question = get_post( $post->post_parent );
			ap_insert_notification( $post->post_author, $question->post_author, 'new_answer', array( 'post_id' => $post_id ) );
		}
	}

	/**
	 * Insert notification when answer is updated
	 * @param  integer $post_id Answer ID.
	 * @param  object  $post    WP post object.
	 */
	public function after_update_question($post_id, $post) {
		if ( ap_is_profile_active() ) {
			ap_insert_notification( get_current_user_id(), $post->post_author, 'question_update', array( 'post_id' => $post_id ) );
		}
	}

	/**
	 * Insert notification when answer is updated
	 * @param  integer $post_id Answer ID.
	 * @param  object  $post    WP post object.
	 */
	public function after_update_answer($post_id, $post) {
		if ( ap_is_profile_active() ) {
			ap_insert_notification( get_current_user_id(), $post->post_author, 'answer_update', array( 'post_id' => $post_id ) );
		}
	}

	/**
	 * Actions to run after posting a comment
	 * @param  object|array $comment Comment object.
	 */
	public function publish_comment($comment) {
		if ( ap_is_profile_active() ) {
		    $comment = (object) $comment;

		    $post = get_post( $comment->comment_post_ID );
		    if ( $post->post_type == 'question' ) {
		    	ap_insert_notification( $comment->user_id, $post->post_author, 'comment_on_question', array( 'post_id' => $post->ID, 'comment_id' => $comment->comment_ID ) );
		    } elseif ( $post->post_type == 'answer' ) {
		    	ap_insert_notification( $comment->user_id, $post->post_author, 'comment_on_answer', array( 'post_id' => $post->ID, 'comment_id' => $comment->comment_ID ) );
		    }
		}
	}
}
