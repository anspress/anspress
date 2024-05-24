<?php
/**
 * Max rule.
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
	 * Validate data.
	 *
	 * @param mixed $attribute Attributes.
	 * @param mixed $value Value.
	 * @param mixed $parameters Parameters.
	 * @param mixed $validator Validator.
	 * @return bool
	 */
	public function validate( $attribute, $value, $parameters, $validator ): bool {
		return isset( $value ) && strlen( $value ) <= $this->max;
	}

	public function message(): string {
		return "The :attribute may not be greater than {$this->max} characters.";
	}
}
