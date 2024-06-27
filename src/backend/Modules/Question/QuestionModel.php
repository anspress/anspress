<?php
/**
 * A wrapper class for question model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question model class.
 */
class QuestionModel extends AbstractModel {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	public const POST_TYPE = 'question';

	/**
	 * Create the model's schema.
	 *
	 * @return AbstractSchema
	 */
	protected static function createSchema(): AbstractSchema {
		return Plugin::get( QuestionSchema::class );
	}

	/**
	 * Get the post type.
	 *
	 * @param int|WP_Post $postIdOrObject Post ID or object.
	 * @return bool
	 */
	public static function isQuestion( $postIdOrObject ): bool {
		$post = get_post( $postIdOrObject );

		return self::POST_TYPE === $post->post_type;
	}
}
