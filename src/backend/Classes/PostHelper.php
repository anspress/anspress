<?php
/**
 * An post model helper class.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Classes\AbstractSchema;
use AnsPress\Modules\Answer\AnswerModel;
use AnsPress\Modules\Question\QuestionModel;
use WP_Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Helper class for the post model.
 */
class PostHelper {
	/**
	 * Check if post is a question.
	 *
	 * @param int|WP_Post $postIdOrObject Post ID or object.
	 * @return bool
	 */
	public static function isQuestion( $postIdOrObject ): bool {
		$post = get_post( $postIdOrObject );

		return QuestionModel::postTypeSlug() === $post->post_type;
	}

	/**
	 * Check if post is an answer.
	 *
	 * @param int|WP_Post $postIdOrObject Post ID or object.
	 * @return bool
	 */
	public static function isAnswer( $postIdOrObject ): bool {
		$post = get_post( $postIdOrObject );

		return AnswerModel::postTypeSlug() === $post->post_type;
	}

	/**
	 * Get all post types.
	 *
	 * @return array
	 */
	public static function allPostTypes(): array {
		return array(
			QuestionModel::postTypeSlug(),
			AnswerModel::postTypeSlug(),
		);
	}

	/**
	 * Check if post type is valid.
	 *
	 * @param string $postType Post type.
	 * @return bool
	 */
	public static function isValidPostType( string $postType ): bool {
		return in_array( $postType, self::allPostTypes(), true );
	}

	/**
	 * Check if post is in moderate status.
	 *
	 * @param int|WP_Post $postObjOrId Post object or ID.
	 * @return bool
	 */
	public static function isModerateStatus( int|WP_Post $postObjOrId ) {
		$post = get_post( $postObjOrId );
		return 'moderate' === $post?->post_status;
	}

	/**
	 * Check if post is in private status.
	 *
	 * @param int|WP_Post $postObjOrId Post object or ID.
	 * @return bool
	 */
	public static function isPrivateStatus( int|WP_Post $postObjOrId ) {
		$post = get_post( $postObjOrId );

		return 'private_post' === $post?->post_status;
	}

	/**
	 * Check if post is in publish status.
	 *
	 * @param int|WP_Post $postObjOrId Post object or ID.
	 * @return bool
	 */
	public static function isPublishedStatus( int|WP_Post $postObjOrId ) {
		$post = get_post( $postObjOrId );

		return 'publish' === $post?->post_status;
	}

	/**
	 * Check if user is the author of the post.
	 *
	 * @param int|WP_Post $postObjOrId Post object or ID.
	 * @param int|null    $userId User ID default current user id.
	 * @return bool
	 */
	public static function isAuthor( int|WP_Post $postObjOrId, ?int $userId = null ) {
		$userId = $userId ?? Auth::getID();

		$post = get_post( $postObjOrId );

		return (int) $userId === (int) $post->post_author;
	}
}
