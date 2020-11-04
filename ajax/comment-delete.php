<?php
/**
 * Class used for ajax callback `comment_delete`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.1.8
 */

namespace AnsPress\Ajax;

// Die if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The `comment_delete` ajax callback.
 *
 * @since 4.1.8
 */
class Comment_Delete extends \AnsPress\Classes\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$comment_id      = ap_sanitize_unslash( 'comment_id', 'r' );
		$this->nonce_key = 'delete_comment_' . $comment_id;

		$this->req( 'comment_id', $comment_id );

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$comment_id = $this->req( 'comment_id' );

		if ( ! empty( $comment_id ) && ! ap_user_can_delete_comment( $comment_id ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$comment_id = $this->req( 'comment_id' );
		$_comment = get_comment( $comment_id );

		// Check if deleting comment is locked.
		if ( ap_comment_delete_locked( $_comment->comment_ID ) && ! is_super_admin() ) {
			$this->set_fail();
			$this->snackbar( sprintf(
				// Translators: %s contain comment created date. i.e. 10 hours.
				__( 'The comment is locked and cannot be deleted. Any comments posted before %s cannot be deleted.', 'anspress-question-answer' ),
				human_time_diff( current_time( 'U' ) + ap_opt( 'disable_delete_after' ) )
			) );

			$this->send();
		}

		$delete = wp_delete_comment( (integer) $_comment->comment_ID, true );

		if ( $delete ) {
			do_action( 'ap_unpublish_comment', $_comment );
			do_action( 'ap_after_deleting_comment', $_comment );

			$count = get_comment_count( $_comment->comment_post_ID );

			$this->set_success();
			$this->snackbar( __( 'Comment successfully deleted', 'anspress-question-answer' ) );
			$this->add_res( 'cb', 'commentDeleted' );
			$this->add_res( 'post_ID', $_comment->comment_post_ID );
			$this->add_res( 'commentsCount', array(
				'text' => sprintf(
					// Translators: %d contain comment count.
					_n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ),
					$count['all']
				),
				'number'     => $count['all'],
				'unapproved' => $count['awaiting_moderation'],
			) );
		}
	}
}
