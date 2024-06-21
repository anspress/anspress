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
	protected array $data;

	/**
	 * Validation rules.
	 *
	 * @var array
	 */
	protected array $rules;

	/**
	 * Custom messages.
	 *
	 * @var array
	 */
	protected array $customMessages;

	/**
	 * Custom attributes.
	 *
	 * @var array
	 */
	protected array $customAttributes;

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected array $errors = array();

	/**
	 * Validated data.
	 *
	 * @var array
	 */
	protected array $validatedData = array();

	/**
	 * Transformations to apply before validation.
	 *
	 * @var array
	 */
	protected array $transformations = array(
		'bool' => 'transformToBool',
	);

	/**
	 * Custom transformations.
	 *
	 * @var array
	 */
	protected array $customTransformations = array();

	/**
	 * Constructor.
	 *
	 * @param array $data Data to validate.
	 * @param array $rules Validation rules.
	 * @param array $customMessages Custom messages.
	 * @param array $customAttributes Custom attributes.
	 * @param array $customTransformations Custom transformations.
	 */
	public function __construct( array $data, array $rules, array $customMessages = array(), array $customAttributes = array(), array $customTransformations = array() ) {
		$this->data                  = $data;
		$this->rules                 = $rules;
		$this->customMessages        = $customMessages;
		$this->customAttributes      = $customAttributes;
		$this->customTransformations = $customTransformations;

		$this->applyTransformations();
		$this->validate();
	}

	/**
	 * Apply transformations to data.
	 */
	protected function applyTransformations(): void {
		foreach ( $this->rules as $attribute => $rules ) {
			$rules = is_string( $rules ) ? explode( '|', $rules ) : $rules;

			foreach ( $rules as $rule ) {
				$ruleName = null;

				if ( is_string( $rule ) ) {
					$ruleName = explode( ':', $rule )[0] ?? null;
				} elseif ( is_array( $rule ) ) {
					$ruleName = $rule[0] ?? null;
				}

				if ( ! is_string( $ruleName ) ) {
					continue;
				}

				// Apply built-in transformations.
				if ( isset( $this->transformations[ $ruleName ] ) ) {
					$transformationMethod     = $this->transformations[ $ruleName ];
					$this->data[ $attribute ] = $this->$transformationMethod( $this->data[ $attribute ] );
				}

				// Apply custom transformations.
				if ( isset( $this->customTransformations[ $ruleName ] ) ) {
					$this->data[ $attribute ] = call_user_func( $this->customTransformations[ $ruleName ], $this->data[ $attribute ] );
				}
			}
		}
	}

	/**
	 * Validate data.
	 */
	public function validate(): void {
		foreach ( $this->rules as $attribute => $rules ) {
			$this->validateAttribute( $attribute, $rules );
		}
	}

	/**
	 * Validate an attribute.
	 *
	 * @param string      $attribute Attribute name.
	 * @param array       $rules Validation rules.
	 * @param string|null $originalAttribute Original attribute name.
	 */
	protected function validateAttribute( $attribute, $rules, $originalAttribute = null ) {
		$data          = $this->data;
		$attributeData = $this->getAttributeData( $attribute, $data );
		$parsedRules   = $this->parseRules( $rules );

		// Check for nullable rule.
		$isNullable = false;
		foreach ( $parsedRules as $rule ) {
			if ( 'nullable' === $rule['rule'] ) {
				$isNullable = true;
				break;
			}
		}

		// Skip further validation if the attribute is nullable and its value is null.
		if ( $isNullable && is_null( $attributeData ) ) {
			$this->setAttributeData( $attribute, $attributeData );
			return;
		}

		// Handle wildcard attribute validation.
		if ( strpos( $attribute, '.*' ) !== false ) {
			$this->validateWildcardAttribute( $attribute, $parsedRules, $data );
		} else {
			foreach ( $parsedRules as $rule ) {
				$parameters = $rule['parameters'];

				// Check if rule is an instance of ValidationRuleInterface.
				if ( $this->isValidationRuleObject( $rule ) ) {
					$this->validateWithObjectRule( $attribute, $attributeData, $parameters, $rule, $originalAttribute );
				} elseif ( $this->isCallableFunction( $rule ) ) {
					$this->validateWithCallable( $attribute, $attributeData, $parameters, $rule, $originalAttribute );
				} else {
					// Create validation rule instance.
					$validationRule = $this->createValidationRule( $rule, $parameters );

					// Validate with the created rule instance.
					$this->validateWithRuleInstance( $attribute, $attributeData, $parameters, $data, $rule, $validationRule, $originalAttribute );
				}

				$this->setAttributeData( $attribute, $attributeData );
			}
		}
	}

	/**
	 * Check if rule is an instance of ValidationRuleInterface.
	 *
	 * @param array $rule Rule.
	 * @return bool
	 */
	protected function isValidationRuleObject( $rule ) {
		return is_object( $rule['rule'] ) && $rule['rule'] instanceof ValidationRuleInterface;
	}

	/**
	 * Validate with an object rule.
	 *
	 * @param string $attribute Attribute name.
	 * @param mixed  $attributeData Attribute data.
	 * @param array  $parameters Rule parameters.
	 * @param array  $rule Rule.
	 * @param string $originalAttribute Original attribute name.
	 */
	protected function validateWithObjectRule( $attribute, $attributeData, $parameters, $rule, $originalAttribute ) {
		if ( ! $rule['rule']->validate( $attribute, $attributeData, $parameters, $this ) ) {
			$this->addError(
				$attribute,
				$this->getMessage( $attribute, $rule['rule']->ruleName(), $rule['rule']->message() ),
				$parameters,
				$originalAttribute
			);
		}
	}

	/**
	 * Check if rule is a callable function.
	 *
	 * @param array $rule Rule.
	 * @return bool
	 */
	protected function isCallableFunction( $rule ) {
		return ! is_string( $rule['rule'] ) && is_callable( $rule['rule'], true );
	}

	/**
	 * Validate with a callable function.
	 *
	 * @param string $attribute Attribute name.
	 * @param mixed  $attributeData Attribute data.
	 * @param array  $parameters Rule parameters.
	 * @param array  $rule Rule.
	 * @param string $originalAttribute Original attribute name.
	 */
	protected function validateWithCallable( $attribute, $attributeData, $parameters, $rule, $originalAttribute ) {
		if ( ! $rule['rule']( $attribute, $attributeData, $parameters, $this ) ) {
			$this->addError(
				$attribute,
				'Validation failed for ' . $attribute . '.',
				$parameters,
				$originalAttribute
			);
		}
	}

	/**
	 * Create a validation rule instance.
	 *
	 * @param array $rule Rule.
	 * @param array $parameters Rule parameters.
	 * @return ValidationRuleInterface
	 */
	public function createValidationRule( $rule, $parameters ) {
		$validationRule = ValidationRuleFactory::make( $rule['rule'], $parameters );
		return $validationRule;
	}

	/**
	 * Validate with a rule instance.
	 *
	 * @param string                  $attribute Attribute name.
	 * @param mixed                   $attributeData Attribute data.
	 * @param array                   $parameters Rule parameters.
	 * @param array                   $data Data.
	 * @param array                   $rule Rule.
	 * @param ValidationRuleInterface $validationRule Validation rule instance.
	 * @param string|null             $originalAttribute Original attribute name.
	 */
	public function validateWithRuleInstance( $attribute, $attributeData, $parameters, $data, $rule, $validationRule, $originalAttribute ): void {
		if ( ! $validationRule->validate( $attribute, $attributeData, $parameters, $this ) ) {
			$this->addError(
				$attribute,
				$this->getMessage( $attribute, $rule['rule'], $validationRule->message() ),
				$parameters,
				$originalAttribute
			);
		}
	}

	/**
	 * Validate wildcard attribute.
	 *
	 * @param string $attribute Attribute name.
	 * @param array  $parsedRules Parsed rules.
	 * @param array  $data Data.
	 */
	protected function validateWildcardAttribute( $attribute, $parsedRules, $data ) {
		$segments           = explode( '.*', $attribute );
		$baseAttribute      = $segments[0];
		$remainingAttribute = isset( $segments[1] ) ? substr( $attribute, strpos( $attribute, '.*' ) + 2 ) : null;
		$baseData           = $this->getAttributeData( $baseAttribute, $data );

		if ( is_array( $baseData ) ) {
			foreach ( $baseData as $index => $item ) {
				$currentAttribute = $baseAttribute . '.' . $index;
				if ( $remainingAttribute ) {
					$currentAttribute .= $remainingAttribute;
				}

				foreach ( $parsedRules as $rule ) {
					$this->validateAttribute( $currentAttribute, array( $rule['rule'] ), $attribute );
				}
			}
		} else {
			// Validate with the base attribute and remaining attribute for all rules if base data doesn't exist.
			foreach ( $parsedRules as $index => $rule ) {
				$this->validateAttribute( $baseAttribute, array( $rule['rule'] ), $attribute );
			}
		}
	}

	/**
	 * Get attribute data.
	 *
	 * @param string $attribute Attribute name.
	 * @param array  $data Data.
	 * @return mixed
	 */
	protected function getAttributeData( $attribute, $data ): mixed {
		$keys = explode( '.', $attribute );

		foreach ( $keys as $key ) {
			if ( '*' === $key ) {
				if ( is_array( $data ) ) {
					$result = array();
					foreach ( $data as $item ) {
						$result[] = $item;
					}
					return $result;
				} else {
					return null;
				}
			}

			if ( isset( $data[ $key ] ) ) {
				$data = $data[ $key ];
			} else {
				return null;
			}
		}

		return $data;
	}

	/**
	 * Parse validation rules.
	 *
	 * @param array $rules Validation rules.
	 * @return array
	 */
	protected function parseRules( $rules ) {
		$parsedRules = array();

		foreach ( (array) $rules as $rule ) {
			if ( is_string( $rule ) ) {
				foreach ( explode( '|', $rule ) as $rulePart ) {
					$parsedRules[] = $this->parseStringRule( $rulePart );
				}
			} elseif ( is_callable( $rule ) ) {
				$parsedRules[] = array(
					'rule'       => $rule,
					'parameters' => array(),
				);
			} else {
				$parsedRules[] = array(
					'rule'       => $rule,
					'parameters' => array(),
				);
			}
		}

		return $parsedRules;
	}

	/**
	 * Parse a string rule.
	 *
	 * @param string $rule Rule.
	 * @return array
	 */
	protected function parseStringRule( $rule ): array {
		if ( strpos( $rule, ':' ) !== false ) {
			list($rule, $parameters) = explode( ':', $rule, 2 );
			$parameters              = explode( ',', $parameters );
		} else {
			$parameters = array();
		}

		return array(
			'rule'       => $rule,
			'parameters' => $parameters,
		);
	}

	/**
	 * Add an error message.
	 *
	 * @param string      $attribute Attribute name.
	 * @param string      $message Message.
	 * @param array       $parameters Parameters.
	 * @param string|null $originalAttribute Original attribute name.
	 */
	protected function addError( $attribute, $message, $parameters, $originalAttribute = null ) {
		$attributeName = $this->customAttributes[ $originalAttribute ?? $attribute ] ?? $attribute;

		$this->errors[ $attribute ][] = str_replace(
			array( ':attribute' ),
			array( $attributeName ),
			$message
		);
	}

	/**
	 * Get a custom message.
	 *
	 * @param string $attribute Attribute name.
	 * @param string $rule Rule.
	 * @param string $defaultMessage Default message.
	 * @return string
	 */
	protected function getMessage( $attribute, $rule, $defaultMessage ) {
		$customMessageKey = $attribute . '.' . $rule;
		return $this->customMessages[ $customMessageKey ] ?? $defaultMessage;
	}

	/**
	 * Get validation errors.
	 *
	 * @return array
	 */
	public function errors(): array {
		return $this->errors;
	}

	/**
	 * Check if validation fails.
	 *
	 * @return bool
	 */
	public function fails() {
		return ! empty( $this->errors );
	}

	/**
	 * Get validated data.
	 *
	 * @return array
	 * @throws ValidationException If validation fails.
	 */
	public function validated() {
		if ( ! empty( $this->errors ) ) {
			throw new ValidationException( $this->errors, 'Validation failed.' ); // @codingStandardsIgnoreLine
		}

		return $this->validatedData;
	}

	/**
	 * Set attribute data.
	 *
	 * @param string $attribute Attribute name.
	 * @param mixed  $value Value.
	 */
	public function setAttributeData( $attribute, $value ) {
		$keys = explode( '.', $attribute );
		$data = &$this->validatedData;

		foreach ( $keys as $key ) {
			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = array();
			}
			$data = &$data[ $key ];
		}

		// Set the value to the final key.
		$data = $value;
	}

	/**
	 * Transform value to boolean.
	 *
	 * @param mixed $value Value to transform.
	 * @return bool Transformed value.
	 */
	protected function transformToBool( $value ): bool {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? $value;
	}
}
