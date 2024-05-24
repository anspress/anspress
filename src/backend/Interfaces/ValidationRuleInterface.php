<?php
/**
 * Validation rule interface.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Interfaces;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation rule interface.
 *
 * @since 5.0.0
 */
interface ValidationRuleInterface {
	/**
	 * Validate data.
	 *
	 * @param mixed $attribute Attributes.
	 * @param mixed $value Value.
	 * @param mixed $parameters Parameters.
	 * @param mixed $validator Validator.
	 * @return bool
	 */
	public function validate( $attribute, $value, $parameters, $validator ): bool;

	public function message(): string;

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string;
}
