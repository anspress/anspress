<?php
/**
 * Validation rule factory.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Classes\Rules\ArrayRule;
use AnsPress\Classes\Rules\EmailRule;
use AnsPress\Classes\Rules\IntegerRule;
use AnsPress\Classes\Rules\MaxRule;
use AnsPress\Classes\Rules\MinRule;
use AnsPress\Classes\Rules\NullableRule;
use AnsPress\Classes\Rules\RequiredRule;
use AnsPress\Classes\Rules\StringRule;
use AnsPress\Exceptions\ValidationException;
use Requests;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation rule factory.
 *
 * @since 5.0.0
 */
class ValidationRuleFactory {
	/**
	 * Map of validation rules.
	 *
	 * @var class-string<RequiredRule>[]
	 */
	protected static $ruleMap = array(
		'nullable' => NullableRule::class,
		'required' => RequiredRule::class,
		'string'   => StringRule::class,
		'integer'  => IntegerRule::class,
		'array'    => ArrayRule::class,
		'min'      => MinRule::class,
		'max'      => MaxRule::class,
		'email'    => EmailRule::class,
	);

	/**
	 * Create a validation rule.
	 *
	 * @param string $rule Rule name.
	 * @param array  $parameters Rule parameters.
	 * @return ValidationRuleInterface
	 * @throws ValidationException If rule not found.
	 */
	public static function make( $rule, $parameters = array() ) {
		if ( ! array_key_exists( $rule, self::$ruleMap ) ) {
			throw new ValidationException(
				array(),
				wp_sprintf(
					/* translators: 1: rule name */
					esc_attr__( 'Validation rule %1$s not found.', 'anspress-question-answer' ),
					esc_attr( $rule )
				)
			);
		}

		$ruleClass = self::$ruleMap[ $rule ];

		return new $ruleClass( ...$parameters );
	}
}
