<?php
/**
 * Post type rule.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes\Rules;

use AnsPress\Classes\Validator;
use AnsPress\Interfaces\ValidationRuleInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Post type rule.
 *
 * @package AnsPress\Classes\Rules
 */
class PostTypeRule implements ValidationRuleInterface {
	/**
	 * Post type to check.
	 *
	 * @var null|string
	 */
	protected ?string $postTypeToCompare;

	/**
	 * Construct.
	 *
	 * @param string $postTypeToCompare Post type to compare.
	 */
	public function __construct( string $postTypeToCompare ) {
		$this->postTypeToCompare = sanitize_key( $postTypeToCompare );
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'post_type';
	}

	/**
	 * Validate the rule.
	 *
	 * @param string    $attribute Attributes.
	 * @param mixed     $value Value.
	 * @param array     $parameters Parameters.
	 * @param Validator $validator Validator.
	 * @return bool
	 */
	public function validate( string $attribute, mixed $value, array $parameters, Validator $validator ): bool {
		$post = get_post( (int) $value );

		if ( ! $post ) {
			return false;
		}

		return $post->post_type === $this->postTypeToCompare;
	}

	/**
	 * Get error message.
	 *
	 * @return string
	 */
	public function message(): string {
		$postType = get_post_type_object( $this->postTypeToCompare );

		return 'The :attribute must be a post type of ' . $postType->label;
	}
}
