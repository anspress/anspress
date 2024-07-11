<?php
/**
 * Validation rule interface.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Interfaces;

use AnsPress\Classes\Validator;

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
	 * @param string    $attribute Attributes.
	 * @param mixed     $value Value.
	 * @param array     $parameters Parameters.
	 * @param Validator $validator Validator.
	 * @return bool
	 */
	public function validate( string $attribute, &$value, array $parameters, Validator $validator ): bool;

	/**
	 * Get error message.
	 *
	 * @return string
	 */
	public function message(): string;

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string;
}
