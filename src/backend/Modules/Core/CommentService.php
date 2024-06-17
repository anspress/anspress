<?php
/**
 * Comment service.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Core;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Validator;
use AnsPress\Exceptions\ValidationException;
use WP_Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment service.
 *
 * @package AnsPress\Modules\Core
 */
class CommentService extends AbstractService {

	/**
	 * Create a new comment.
	 *
	 * @param array $data Comment data.
	 * @return int Comment ID.
	 * @throws ValidationException If validation fails.
	 */
	public function createComment( $data ) {
		$commentId = wp_new_comment(
			array(
				'comment_post_ID'  => $data['comment_post_ID'],
				'comment_content'  => sanitize_textarea_field( wp_unslash( $data['comment_content'] ) ),
				'user_id'          => $data['user_id'],
				'comment_type'     => 'anspress',
				'comment_approved' => 1,
			),
			true
		);

		if ( is_wp_error( $commentId ) ) {
			throw new ValidationException( $commentId->get_error_message() ); // @codingStandardsIgnoreLine
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
			compact( 'commentId' ),
			array(
				'commentId' => 'required|numeric|exists:comments,comment_ID',
			)
		);

		$comment = get_comment( $commentId );

		$deleted = wp_delete_comment( $commentId, true );

		if ( ! $deleted ) {
			throw new ValidationException( esc_attr__( 'Failed to delete comment.', 'anspress-question-answer' ) );
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
			'canComment'    => ap_user_can_comment( $post->ID ),
			'offset'        => $offset,
			'hasMore'       => $commentsCount > $loaded ? true : false,

		);
	}
}
