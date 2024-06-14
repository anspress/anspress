<?php
/**
 * Core controller.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Core;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\ValidationException;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core controller.
 *
 * @package AnsPress\Modules\Core
 */
class CoreController extends AbstractController {
	/**
	 * Create a new comment.
	 *
	 * @return WP_REST_Response
	 * @throws ValidationException If validation fails.
	 */
	public function createComment() {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'post_id' => 'required|numeric|exists:posts,ID',
				'comment' => 'required|string|max:1000',
			)
		);

		$commentId = wp_new_comment(
			array(
				'comment_post_ID'  => $data['post_id'],
				'comment_content'  => sanitize_textarea_field( wp_unslash( $data['comment'] ) ),
				'user_id'          => get_current_user_id(),
				'comment_type'     => 'anspress',
				'comment_approved' => 1,
			),
			true
		);

		if ( is_wp_error( $commentId ) ) {
			throw new ValidationException(
				$commentId->get_error_messages(), // @codingStandardsIgnoreLine WordPress.WP.ErrorReporting
				esc_attr__( 'Failed to create comment.', 'anspress-question-answer' )
			);
		}

		$commentHtml = Plugin::loadView(
			'src/frontend/common/comments/single-comment.php',
			array( 'comment' => get_comment( $commentId ) ),
			false
		);

		return $this->response( array( 'html' => $commentHtml ) );
	}

	/**
	 * Delete a comment.
	 *
	 * @return WP_REST_Response
	 * @throws ValidationException If validation fails.
	 */
	public function deleteComment() {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'comment_id' => 'required|numeric|exists:comments,comment_ID',
			)
		);

		$comment = get_comment( $data['comment_id'] );

		if ( ! $comment ) {
			throw new ValidationException(
				esc_attr__( 'Comment not found.', 'anspress-question-answer' )
			);
		}

		$deleted = wp_delete_comment( $data['comment_id'], true );

		if ( ! $deleted ) {
			throw new ValidationException(
				esc_attr__( 'Failed to delete comment.', 'anspress-question-answer' )
			);
		}

		return $this->response( array( 'message' => esc_attr__( 'Comment deleted successfully.', 'anspress-question-answer' ) ) );
	}
}
