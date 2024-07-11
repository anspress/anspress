<?php
/**
 * Min rule.
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
	public function __construct( int $min ) {
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
	 * Validate the rule.
	 *
	 * @param string    $attribute Attributes.
	 * @param mixed     $value Value.
	 * @param array     $parameters Parameters.
	 * @param Validator $validator Validator.
	 * @return bool
	 */
	public function validate( string $attribute, mixed &$value, array $parameters, Validator $validator ): bool {
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
