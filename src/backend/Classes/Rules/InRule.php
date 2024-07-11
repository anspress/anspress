<?php
/**
 * In rule.
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
 * In rule.
 *
 * @since 5.0.0
 */
class InRule implements ValidationRuleInterface {
	/**
	 * Allowed values.
	 *
	 * @var array
	 */
	protected $allowed = array();

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'in';
	}

	/**
	 * Constructor.
	 *
	 * @param mixed ...$args Rule arguments.
	 */
	public function __construct( ...$args ) {
		$this->allowed = $args;
	}

	/**
	 * Validate rule.
	 *
	 * @param string    $attribute Attribute name.
	 * @param mixed     $value Attribute value.
	 * @param array     $parameters Rule parameters.
	 * @param Validator $validator Validator instance.
	 * @return bool
	 */
	public function validate( string $attribute, mixed &$value, array $parameters, Validator $validator ): bool {
		return in_array( $value, $parameters, true );
	}

	/**
	 * Get error message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'The :attribute must be one of ' . implode( ', ', $this->allowed );
	}
}
