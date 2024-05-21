<?php
/**
 * Data validator.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\ValidationException;
use AnsPress\Interfaces\ValidationRuleInterface;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data validator.
 *
 * @since 5.0.0
 */
class Validator {
	/**
	 * Data to validate.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Validation rules.
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Validation custom messages.
	 *
	 * @var array
	 */
	protected $customMessages;

	/**
	 * Validation attribute names.
	 *
	 * @var array
	 */
	protected $customAttributes;

	/**
	 * Validated data.
	 *
	 * @var array
	 */
	protected array $validatedData = array();

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Constructor.
	 *
	 * @param array $data Data to validate.
	 * @param array $rules Validation rules.
	 * @param array $customMessages Custom validation messages.
	 * @param array $customAttributes Custom validation attributes.
	 * @return void
	 */
	public function __construct( array $data, array $rules, array $customMessages = array(), array $customAttributes = array() ) {
		$this->data             = $data;
		$this->rules            = $rules;
		$this->customMessages   = $customMessages;
		$this->customAttributes = $customAttributes;
	}

	/**
	 * Validate data.
	 *
	 * @return true
	 * @throws ValidationException If validation fails.
	 */
	public function validate() {
		foreach ( $this->rules as $attribute => $rules ) {
			$rules = is_array( $rules ) ? $rules : explode( '|', $rules );
			$this->validateAttribute( $attribute, $rules, $this->data );
		}

		if ( ! empty( $this->errors ) ) {
			throw new ValidationException( $this->errors ); // @codingStandardsIgnoreLine
		}

		return true;
	}

	/**
	 * Validate attribute.
	 *
	 * @param mixed $attribute Attributes.
	 * @param mixed $rules Rules.
	 * @param mixed $data Data.
	 * @return void
	 * @throws ValidationException If validation rule not found.
	 */
	protected function validateAttribute( $attribute, $rules, $data ) {
		$value = $this->getValue( $attribute, $data );

		foreach ( $rules as $rule ) {
			if ( is_string( $rule ) ) {
				$parameters = array();
				if ( strpos( $rule, ':' ) !== false ) {
					list($rule, $parameterString) = explode( ':', $rule );
					$parameters                   = explode( ',', $parameterString );
				}

				$ruleInstance = ValidationRuleFactory::make( $rule, $parameters );

				if ( ! $ruleInstance->validate( $attribute, $value, array(), $this ) ) {
					$this->addError( $attribute, $ruleInstance, $parameters );
				} else {
					$this->setValidatedData( $attribute, $value );
				}
			} elseif ( $rule instanceof ValidationRuleInterface ) {
				if ( ! $rule->validate( $attribute, $value, array(), $this ) ) {
					$this->addError( $attribute, $rule, array() );
				} else {
					$this->setValidatedData( $attribute, $value );
				}
			} elseif ( is_callable( $rule ) ) {
				if ( ! $rule( $attribute, $value, array(), $this ) ) {
					$this->addError( $attribute, 'custom', array() );
				} else {
					$this->setValidatedData( $attribute, $value );
				}
			} else {
				throw new ValidationException(
					array(),
					sprintf(
						// translators: 1: rule name.
						esc_attr__( 'Validation rule %s not found.', 'anspress-question-answer' ),
						esc_attr( $rule )
					)
				);
			}
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $nestedValue ) {
				$nestedAttribute = "{$attribute}.{$key}";
				if ( isset( $this->rules[ $nestedAttribute ] ) ) {
					$this->validateAttribute( $nestedAttribute, $this->rules[ $nestedAttribute ], $data );
				}
			}
		}
	}

	/**
	 * Get value from data.
	 *
	 * @param string $attribute Attributes.
	 * @param mixed  $data Data.
	 * @return mixed
	 */
	protected function getValue( string $attribute, $data ) {
		$keys = explode( '.', $attribute );

		if ( empty( $keys ) ) {
			return null;
		}

		foreach ( $keys as $key ) {
			if ( is_array( $data ) && array_key_exists( $key, $data ) ) {
				$data = $data[ $key ];
			} else {
				return null;
			}
		}
		return $data;
	}

	/**
	 * Set validated data.
	 *
	 * @param string $attribute Attributes.
	 * @param mixed  $value Value.
	 * @return void
	 */
	protected function setValidatedData( $attribute, $value ) {
		$keys = explode( '.', $attribute );
		$data = &$this->validatedData;

		foreach ( $keys as $key ) {
			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = array();
			}
			$data = &$data[ $key ];
		}

		$data = $value;
	}

	/**
	 * Get validation errors.
	 *
	 * @return array
	 */
	public function errors() {
		return $this->errors;
	}

	/**
	 * Add error.
	 *
	 * @param mixed $attribute Attributes.
	 * @param mixed $rule    Rule.
	 * @param array $parameters Parameters.
	 * @return void
	 */
	protected function addError( $attribute, $rule, $parameters = array() ) {
		$attributeLabel = $this->customAttributes[ $attribute ] ?? $attribute;

		if ( $rule instanceof ValidationRuleInterface ) {
			$ruleName = is_object( $rule ) ? $rule->ruleName() : $rule;

			$message = $this->customMessages[ "{$attribute}.{$ruleName}" ] ?? $rule->message( $attributeLabel, $parameters );
		} else {
			$ruleName = is_object( $rule ) ? get_class( $rule ) : $rule;
			$message  = $this->customMessages[ "{$attribute}.{$ruleName}" ] ??
				"The {$attributeLabel} field failed validation for rule {$ruleName}.";
		}

		$this->errors[ $attribute ][] = $message;
	}

	/**
	 * Get validated data.
	 *
	 * @return array
	 */
	public function validated(): array {
		return $this->validatedData;
	}
}
