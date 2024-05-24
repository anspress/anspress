<?php
/**
 * Min rule.
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
 * Min rule.
 *
 * @since 5.0.0
 */
class MinRule implements ValidationRuleInterface {
	/**
	 * Minimum length.
	 *
	 * @var int
	 */
	protected $min;

	/**
	 * Constructor.
	 *
	 * @param int $min Minimum length.
	 */
	public function __construct( $min = 0 ) {
		$this->min = $min;
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'min';
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
		if ( is_array( $value ) ) {
			return count( $value ) >= $this->min;
		}

		if ( is_numeric( $value ) ) {
			return $value >= $this->min;
		}

		return isset( $value ) && strlen( $value ) >= $this->min;
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function message(): string {
		return "The :attribute must be at least {$this->min}.";
	}
}
