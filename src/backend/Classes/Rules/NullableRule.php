<?php
/**
 * Nullable rule.
 *
 * @since 5.0.0
 *
 * @package AnsPress
 */

namespace AnsPress\Classes\Rules;

use AnsPress\Interfaces\ValidationRuleInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nullable rule.
 *
 * @since 5.0.0
 */
class NullableRule implements ValidationRuleInterface {
	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'nullable';
	}

	/**
	 * Validate data.
	 *
	 * @param mixed $attribute Attributes.
	 * @param mixed $value Value.
	 * @param mixed $parameters Parameters.
	 * @param mixed $validator Validator.
	 * @return bool
	 */
	public function validate( $attribute, $value, $parameters, $validator ): bool {
		return true;
	}

	/**
	 * Get validation error message.
	 *
	 * @param string $attribute Attribute.
	 * @param array  $parameters Parameters.
	 * @return string
	 */
	public function message( $attribute, $parameters ): string {
		return "The {$attribute} field can be null.";
	}
}
