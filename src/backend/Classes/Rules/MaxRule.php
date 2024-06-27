<?php
/**
 * Max rule.
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
 * Max rule.
 *
 * @since 5.0.0
 */
class MaxRule implements ValidationRuleInterface {
	/**
	 * Max value.
	 *
	 * @var mixed
	 */
	protected $max;

	/**
	 * Constructor.
	 *
	 * @param mixed $max Max value.
	 */
	public function __construct( $max ) {
		$this->max = $max;
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'max';
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
		if ( empty( $value ) ) {
			return true;
		}

		if ( is_array( $value ) ) {
			return count( $value ) <= $this->max;
		} elseif ( is_numeric( $value ) ) {
			return $value <= $this->max;
		} elseif ( is_string( $value ) ) {
			return strlen( $value ) <= $this->max;
		}

		return false;
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function message(): string {
		return "The :attribute may not be greater than {$this->max}.";
	}
}
