<?php
/**
 * Comment controller.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Comment;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\HTTPException;
use AnsPress\Exceptions\ValidationException;
use InvalidArgumentException;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment controller.
 *
 * @package AnsPress\Modules\Comment
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
	 * Load comment form.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function loadCommentForm() {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'post_id'     => 'required|numeric|exists:posts,ID',
				'form_loaded' => 'nullable|bool',
			)
		);

		$post = ap_get_post( $data['post_id'] );

		if ( ! $post ) {
			return $this->notFound();
		}

		$commentForm = Plugin::loadView(
			'src/frontend/common/comments/comment-form.php',
			array(
				'post'        => $post,
				'form_loaded' => $data['form_loaded'] ?? true,
			),
			false
		);

		return $this->response(
			array(
				'comment-formHtml' => $commentForm,
			)
		);
	}

	/**
	 * Load edit comment form.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function loadEditCommentForm() {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'comment_id'  => 'required|numeric|exists:comments,comment_ID',
				'form_loaded' => 'nullable|bool',
			)
		);

		$comment = get_comment( $data['comment_id'] );
		$post    = ap_get_post( $comment->comment_post_ID );

		if ( ! $post ) {
			return $this->notFound();
		}

		$commentForm = Plugin::loadView(
			'src/frontend/common/comments/comment-form.php',
			array(
				'comment'     => $comment,
				'post'        => $post,
				'form_loaded' => $data['form_loaded'] ?? true,
			),
			false
		);

		return $this->response(
			array(
				'comment'     => array( 'id' => $data['comment_id'] ),
				'replaceHtml' => array( '#anspress-comment-' . $comment->comment_ID => $commentForm ),
			)
		);
	}

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
				'post_id'         => 'required|numeric|exists:posts,ID',
				'comment_content' => 'required|string|min:2|max:1000',
			)
		);

		$commentId = $this->commentService->createComment(
			array(
				'comment_post_ID' => $data['post_id'],
				'comment_content' => $data['comment_content'],
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
				'appendHtmlTo'         => array( '#anspress-comments-' . $data['post_id'] . ' [data-anspressel="comments-items"]' => $commentHtml ),
				'commentsData'         => Plugin::get( CommentService::class )->getCommentsData( get_post( $data['post_id'] ) ),
				'comment-formMessages' => array(
					array(
						'type'    => 'success',
						'message' => esc_attr__( 'Comment added successfully.', 'anspress-question-answer' ),
					),
				),
				'comment-formHtml'     => Plugin::loadView(
					'src/frontend/common/comments/comment-form.php',
					array(
						'post'        => get_post( $data['post_id'] ),
						'form_loaded' => false,
					),
					false
				),
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
				'message'          => esc_attr__( 'Comment deleted successfully.', 'anspress-question-answer' ),
				'commentsData'     => Plugin::get( CommentService::class )->getCommentsData(
					get_post( $comment->comment_post_ID )
				),
				'commentsMessages' => array(
					array(
						'type'    => 'success',
						'message' => esc_attr__( 'Comment deleted successfully.', 'anspress-question-answer' ),
					),
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

	/**
	 * Update a comment.
	 *
	 * @return WP_REST_Response Rest response.
	 */
	public function updateComment(): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'post_id'         => 'required|numeric|exists:posts,ID',
				'comment_id'      => 'required|numeric|exists:comments,comment_ID',
				'comment_content' => 'required|string|min:2|max:1000',
			)
		);

		$comment = get_comment( $data['comment_id'] );

		if ( ! $comment ) {
			return $this->notFound();
		}

		$updated = wp_update_comment(
			array(
				'comment_ID'      => $data['comment_id'],
				'comment_content' => $data['comment_content'],
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			$this->serverError( $updated->get_error_message() );
		}

		$commentHtml = Plugin::loadView(
			'src/frontend/common/comments/single-comment.php',
			array( 'comment' => get_comment( $data['comment_id'] ) ),
			false
		);

		return $this->response(
			array(
				'comment'              => array( 'id' => $data['comment_id'] ),
				'replaceHtml'          => array( '#anspress-comment-form-' . $data['comment_id'] => $commentHtml ),
				'commentsData'         => Plugin::get( CommentService::class )->getCommentsData( get_post( $data['post_id'] ) ),
				'comment-formMessages' => array(
					array(
						'type'    => 'success',
						'message' => esc_attr__( 'Comment updated successfully.', 'anspress-question-answer' ),
					),
				),
			)
		);
	}
}
