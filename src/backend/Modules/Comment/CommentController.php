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
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Str;
use AnsPress\Classes\TemplateHelper;
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
	 * Comment action handler.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function actions(): WP_REST_Response {
		$data = $this->validate(
			array(
				'comment_id' => 'required|numeric|exists:comments,comment_ID',
				'action'     => 'required|string',
			)
		);

		$action = Str::toCamelCase( 'action' . $data['action'] );

		if ( method_exists( $this, $action ) ) {
			return $this->$action( (int) $data['comment_id'] );
		}

		return $this->notFound( __( 'Invalid action.', 'anspress-question-answer' ) );
	}

	/**
	 * Load comment form.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function loadCommentForm(): WP_REST_Response {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_id'     => 'required|numeric|exists:posts,ID',
				'form_loaded' => 'nullable|bool',
				'comment_id'  => 'nullable|numeric|exists:comments,comment_ID',
			)
		);

		$post = ap_get_post( $data['post_id'] );

		// Check if user can comment.
		$this->checkPermission( 'comment:create', array( 'post' => $post ) );

		$this->addEvent(
			'appendTo',
			array(
				'selector' => '[data-anspress-id="comment:form:placeholder:' . (int) $data['post_id'] . '"]',
				'html'     => TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/common/comments/comment-form.php',
					array(
						'post'        => $post,
						'form_loaded' => $data['form_loaded'] ?? true,
					),
				),
			)
		);

		return $this->response();
	}

	/**
	 * Load edit comment form.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function loadCommentEditForm() {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_id'     => 'required|numeric|exists:posts,ID',
				'comment_id'  => 'required|numeric|exists:comments,comment_ID',
				'form_loaded' => 'nullable|bool',
			)
		);

		$comment = get_comment( $data['comment_id'] );
		$post    = ap_get_post( $comment->comment_post_ID );

		if ( ! $post ) {
			return $this->notFound( __( 'Invalid post.', 'anspress-question-answer' ) );
		}

		// Check if user can edit comment.
		$this->checkPermission( 'comment:update', array( 'comment' => $comment ) );

		$this->addEvent(
			'appendTo',
			array(
				'selector' => '[data-anspress-id="comment:form:placeholder:' . (int) $data['post_id'] . '"]',
				'html'     => TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/common/comments/comment-form.php',
					array(
						'post'        => $post,
						'form_loaded' => $data['form_loaded'] ?? true,
						'comment'     => $comment,
					),
				),
			)
		);

		return $this->response(
			array(
				'comment' => array( 'id' => $data['comment_id'] ),
			)
		);
	}

	/**
	 * Create a new comment.
	 *
	 * @return WP_REST_Response
	 */
	public function createComment() {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_id'         => 'required|numeric|exists:posts,ID',
				'comment_content' => 'required|string|min:2|max:1000',
			),
			array(),
			$this->commentService->commentAttributes()
		);

		$commentPost = get_post( $data['post_id'] );

		if ( ! $commentPost ) {
			return $this->notFound();
		}

		// Check if user can comment.
		$this->checkPermission( 'comment:create', array( 'post' => $commentPost ) );

		$commentId = $this->commentService->createComment(
			array(
				'comment_post_ID' => $data['post_id'],
				'comment_content' => $data['comment_content'],
				'user_id'         => get_current_user_id(),
			),
			true
		);

		$commentHtml = TemplateHelper::loadRestBlockPart(
			$this->request,
			'src/frontend/common/comments/single-comment.php',
			array( 'comment' => get_comment( $commentId ) )
		);

		$this->addMessage( 'success', esc_attr__( 'Comment added successfully.', 'anspress-question-answer' ) );

		$this->addEvent(
			'anspress-comments-' . (int) $data['post_id'] . '-added',
			array(
				'html' => $commentHtml,
			)
		);

		$this->setData(
			'comment-list-' . (int) $data['post_id'],
			Plugin::get( CommentService::class )->getCommentsData( get_post( $data['post_id'] ) )
		);

		return $this->response();
	}

	/**
	 * Delete a comment.
	 *
	 * @param int $commentId The ID of the comment.
	 * @return WP_REST_Response
	 * @throws ValidationException If validation fails.
	 */
	public function actionDeleteComment( int $commentId ) {
		$this->assureLoggedIn();

		$comment = get_comment( $commentId );

		if ( 'anspress' !== $comment?->comment_type ) {
			return $this->badRequest( __( 'Invalid comment.', 'anspress-question-answer' ) );
		}

		// Check if user can delete comment.
		$this->checkPermission( 'comment:delete', array( 'comment' => $comment ) );

		$deleted = wp_delete_comment( $comment->comment_ID, true );

		if ( ! $deleted ) {
			throw new ValidationException(
				esc_attr__( 'Failed to delete comment.', 'anspress-question-answer' )
			);
		}

		$this->addEvent(
			'anspress-comments-' . (int) $comment->comment_post_ID . '-deleted',
			array(
				'commentId' => $comment->comment_ID,
			)
		);

		$this->setData(
			'comment-list-' . (int) $comment->comment_post_ID,
			Plugin::get( CommentService::class )->getCommentsData( get_post( $comment->comment_post_ID ) )
		);

		$this->addMessage( 'success', esc_attr__( 'Comment deleted successfully.', 'anspress-question-answer' ) );

		return $this->response();
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
				'offset'  => 'nullable|numeric',
			)
		);

		$post = ap_get_post( $data['post_id'] );

		// Check if user can view comments.
		$this->checkPermission( 'comment:list', array( 'post' => $post ) );

		if ( ! $post ) {
			return $this->notFound();
		}

		$this->addEvent(
			'anspress-comments-' . (int) $data['post_id'] . '-added',
			array(
				'html' => TemplateHelper::loadRestBlockPart(
					$this->request,
					'src/frontend/common/comments/render.php',
					array(
						'post'             => $post,
						'offset'           => $this->getParam( 'offset', 0 ),
						'withoutContainer' => true,
					),
				),
			)
		);

		$this->setData(
			'comment-list-' . (int) $data['post_id'],
			Plugin::get( CommentService::class )->getCommentsData(
				$post,
				absint( $this->getParam( 'offset', 0 ) )
			)
		);

		return $this->response();
	}

	/**
	 * Update a comment.
	 *
	 * @return WP_REST_Response Rest response.
	 */
	public function updateComment(): WP_REST_Response {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'comment_id'      => 'required|numeric|exists:comments,comment_ID',
				'comment_content' => 'required|string|min:2|max:1000',
			),
			array(),
			$this->commentService->commentAttributes()
		);

		$comment = get_comment( $data['comment_id'] );

		if ( ! $comment ) {
			return $this->notFound();
		}

		// Check if user can update comment.
		$this->checkPermission( 'comment:update', array( 'comment' => $comment ) );

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

		$commentHtml = TemplateHelper::loadRestBlockPart(
			$this->request,
			'src/frontend/common/comments/single-comment.php',
			array( 'comment' => get_comment( $data['comment_id'] ) )
		);

		$this->addMessage( 'success', esc_attr__( 'Comment updated successfully.', 'anspress-question-answer' ) );
		$this->setData(
			'comment-list-' . (int) $comment->comment_post_ID,
			Plugin::get( CommentService::class )->getCommentsData( get_post( $comment->comment_post_ID ) )
		);

		return $this->response(
			array(
				'comment'     => array( 'id' => $data['comment_id'] ),
				'replaceHtml' => array( '[data-anspress-id="comment-' . $data['comment_id'] . '"]' => $commentHtml ),
			)
		);
	}
}
