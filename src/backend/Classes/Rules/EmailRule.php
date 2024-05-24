<?php
/**
 * Email rule.
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
 * Email rule.
 *
 * @since 5.0.0
 */
class EmailRule implements ValidationRuleInterface {
	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'email';
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
		return is_string( $value ) && filter_var( $value, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'The :attribute must be a valid email address.';
	}
}
