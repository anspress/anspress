<?php
/**
 * Comment service.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Comment;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;
use WP_Comment_Query;
use WP_Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment service.
 *
 * @package AnsPress\Modules\Comment
 */
class CommentService extends AbstractService {
	/**
	 * Get comment attributes.
	 *
	 * @return array Comment attributes.
	 */
	public function commentAttributes(): array {
		return array(
			'comment_content' => __( 'Comment content', 'anspress-question-answer' ),
			'comment_id'      => __( 'Comment ID', 'anspress-question-answer' ),
			'user_id'         => __( 'User ID', 'anspress-question-answer' ),
			'comment_post_ID' => __( 'Comment post ID', 'anspress-question-answer' ),
		);
	}

	/**
	 * Create a new comment.
	 *
	 * @param array $data Comment data.
	 * @return int Comment ID.
	 * @throws ValidationException If validation fails.
	 */
	public function createComment( $data ) {
		$validator = new Validator(
			$data,
			array(
				'comment_post_ID' => 'required|numeric|exists:posts,ID',
				'comment_content' => 'required|string',
				'user_id'         => 'required|numeric',
			),
			array(),
			$this->commentAttributes()
		);

		$validated = $validator->validated();

		$commentId = wp_new_comment(
			array(
				'comment_post_ID'  => $validated['comment_post_ID'],
				'comment_content'  => sanitize_textarea_field( wp_unslash( $validated['comment_content'] ) ),
				'user_id'          => $validated['user_id'],
				'comment_type'     => 'anspress',
				'comment_approved' => 1,
			),
			true
		);

		if ( is_wp_error( $commentId ) ) {
			throw new ValidationException( ['*' => $commentId->get_error_messages()] ); // @codingStandardsIgnoreLine
		}

		return $commentId;
	}

	/**
	 * Delete a comment.
	 *
	 * @param int $commentId Comment ID.
	 * @return bool True on success, false on failure.
	 * @throws ValidationException If validation fails.
	 */
	public function deleteComment( $commentId ) {
		$validated = new Validator(
			array(
				'comment_id' => $commentId,
			),
			array(
				'comment_id' => 'required|numeric|exists:comments,comment_ID',
			),
			array(),
			$this->commentAttributes()
		);

		$comment = get_comment( $commentId );

		$deleted = wp_delete_comment( $commentId, true );

		if ( ! $deleted ) {
			throw new ValidationException( array( '*' => esc_attr__( 'Failed to delete comment.', 'anspress-question-answer' ) ) );
		}

		return true;
	}

	/**
	 * Get comments data.
	 *
	 * @param WP_Post  $post Post.
	 * @param int|null $offset Offset.
	 * @param int|null $number Number.
	 * @return array Comments data.
	 */
	public function getCommentsData( WP_Post $post, ?int $offset = null, ?int $number = null ) {
		$offset = absint( $offset ?? 0 );
		$number = absint( $number ?? 3 );

		$commentsCount = (int) get_comments_number( $post->ID );

		$loaded = $number + $offset;

		if ( $loaded > $commentsCount ) {
			$number = $commentsCount;
		}

		return array(
			'postId'        => $post->ID,
			'totalComments' => $commentsCount,
			'showing'       => $number,
			'canComment'    => Auth::currentUserCan( 'comment:create', array( 'post' => $post ) ),
			'offset'        => $offset,
			'hasMore'       => $commentsCount > $loaded ? true : false,
		);
	}

	/**
	 * Get comments query.
	 *
	 * @param int   $postId Post ID.
	 * @param array $args Query arguments.
	 * @return WP_Comment_Query Comments query.
	 */
	public function getCommentsQuery( int $postId, array $args = array() ): WP_Comment_Query {
		$showingComments = 3;
		$offset          = absint( $args['offset'] ?? 0 );

		$args = wp_parse_args(
			$args,
			array(
				array(
					'type'    => 'anspress',
					'post_id' => $postId,
					'status'  => 'approve',
					'orderby' => 'comment_ID',
					'order'   => 'DESC',
					'number'  => $showingComments,
					'offset'  => $offset,
				),
			)
		);

		$args = apply_filters( 'anspress/comments/query_args', $args, $postId );

		return new WP_Comment_Query( $args );
	}
}
