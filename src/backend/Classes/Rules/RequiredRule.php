<?php
/**
 * Required rule.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes\Rules;

use AnsPress\Interfaces\ValidationRuleInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required rule.
 *
 * @since 5.0.0
 */
class RequiredRule implements ValidationRuleInterface {
	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'required';
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
		return isset( $value ) && ! empty( $value );
	}

	/**
	 * Get validation error message.
	 *
	 * @param string $attribute Attribute.
	 * @param array  $parameters Parameters.
	 * @return string
	 */
	public function message( $attribute, $parameters ): string {
		return "The {$attribute} field is required.";
	}
}
