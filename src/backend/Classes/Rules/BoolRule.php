<?php
/**
 * Array rule.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes\Rules;

use AnsPress\Classes\Validator;
use AnsPress\Interfaces\ValidationRuleInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boolean validation rule.
 *
 * @since 5.0.0
 */
class BoolRule implements ValidationRuleInterface {
	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'bool';
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
	public function validate( string $attribute, mixed &$value, array $parameters, Validator $validator ): bool {
		$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );

		return is_bool( $value );
	}

	/**
	 * Get error message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'The :attribute must be a boolean';
	}
}
