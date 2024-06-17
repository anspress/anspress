<?php
/**
 * Comment controller.
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
 * Comment controller.
 *
 * @package AnsPress\Modules\Core
 */
class CommentController extends AbstractController {

	/**
	 * Constructor.
	 *
	 * @param CommentService $commentService Comment service.
	 * @return void
	 */
	public function __construct( private CommentService $commentService ) {}

	/**
	 * Create a new comment.
	 *
	 * @return WP_REST_Response
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

		$commentId = $this->commentService->createComment(
			array(
				'comment_post_ID' => $data['post_id'],
				'comment_content' => $data['comment'],
				'user_id'         => get_current_user_id(),
			),
			true
		);

		$commentHtml = Plugin::loadView(
			'src/frontend/common/comments/single-comment.php',
			array( 'comment' => get_comment( $commentId ) ),
			false
		);

		return $this->response(
			array(
				'html'         => $commentHtml,
				'commentsData' => Plugin::get( CommentService::class )->getCommentsData( get_post( $data['post_id'] ) ),
			)
		);
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

		$deleted = wp_delete_comment( $data['comment_id'], true );

		if ( ! $deleted ) {
			throw new ValidationException(
				esc_attr__( 'Failed to delete comment.', 'anspress-question-answer' )
			);
		}

		return $this->response(
			array(
				'message'      => esc_attr__( 'Comment deleted successfully.', 'anspress-question-answer' ),
				'commentsData' => Plugin::get( CommentService::class )->getCommentsData(
					get_post( $comment->comment_post_ID )
				),
			)
		);
	}

	/**
	 * Show comments.
	 *
	 * @return WP_REST_Response
	 */
	public function showComments(): WP_REST_Response {
		$data = $this->validate(
			array(
				'post_id' => 'required|numeric|exists:posts,ID',
			)
		);

		$post = ap_get_post( $data['post_id'] );

		if ( ! $post ) {
			return $this->notFound();
		}

		$commentHtml = Plugin::loadView(
			'src/frontend/common/comments/render.php',
			array(
				'post'             => $post,
				'offset'           => $this->getParam( 'offset', 0 ),
				'withoutContainer' => true,
			),
			false
		);

		return $this->response(
			array(
				'html'         => $commentHtml,
				'commentsData' => Plugin::get( CommentService::class )->getCommentsData(
					$post,
					absint( $this->getParam( 'offset', 0 ) )
				),
			)
		);
	}
}
