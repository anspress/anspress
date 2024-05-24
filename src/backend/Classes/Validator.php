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

	protected $data;
	protected $rules;
	protected $customMessages;
	protected $customAttributes;
	protected $errors = array();

	public function __construct( array $data, array $rules, array $customMessages = array(), array $customAttributes = array() ) {
		$this->data             = $data;
		$this->rules            = $rules;
		$this->customMessages   = $customMessages;
		$this->customAttributes = $customAttributes;

		$this->validate();
	}

	public function validate() {
		foreach ( $this->rules as $attribute => $rules ) {
			$this->validateAttribute( $attribute, $rules );
		}

		return true;
	}

	protected function validateAttribute( $attribute, $rules, $index = null, $originalAttribute = null ) {
		$data          = $this->data;
		$attributeData = $this->getAttributeData( $attribute, $data, $index );
		$parsedRules   = $this->parseRules( $rules );

		if ( strpos( $attribute, '.*' ) !== false ) {
			$this->validateWildcardAttribute( $attribute, $parsedRules, $data );
		} else {
			foreach ( $parsedRules as $rule ) {
				$parameters = $rule['parameters'];

				if ( is_object( $rule['rule'] ) && $rule['rule'] instanceof ValidationRuleInterface ) {
					if ( ! $rule['rule']->validate( $attribute, $attributeData, $parameters, $this ) ) {
						$this->addError(
							$attribute,
							$this->getMessage( $attribute, $rule['rule']->ruleName(), $rule['rule']->message() ),
							$parameters,
							$originalAttribute
						);
					}

					continue;
				}

				if ( ! is_string( $rule['rule'] ) && is_callable( $rule['rule'], true ) ) {
					if ( ! $rule['rule']( $attribute, $attributeData, $rule['parameters'], $this ) ) {
						$this->addError(
							$attribute,
							'Validation failed for ' . $attribute . '.',
							$rule['parameters'],
							$originalAttribute
						);
					}

					continue;
				}

				$validationRule = ValidationRuleFactory::make( $rule['rule'], $parameters );

				if ( ! $validationRule instanceof ValidationRuleInterface ) {
					throw new ValidationException(
						array(
							$attribute => array( 'Unhandled validation rule.' ),
						),
						'Unhandled validation rule.'
					);
				}

				if ( ! $validationRule->validate( $attribute, $attributeData, $parameters, $data ) ) {
					$this->addError(
						$attribute,
						$this->getMessage( $attribute, $rule['rule'], $validationRule->message() ),
						$parameters,
						$originalAttribute
					);
				}
			}
		}
	}

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
					$this->validateAttribute( $currentAttribute, array( $rule['rule'] ), $index, $attribute );
				}
			}
		} else {
			// Validate with the base attribute and remaining attribute for all rules if base data doesn't exist
			foreach ( $parsedRules as $index => $rule ) {
				$this->validateAttribute( $baseAttribute, array( $rule['rule'] ), $index, $attribute );
			}
		}
	}

	protected function getAttributeData( $attribute, $data, $index = null ) {
		$keys = explode( '.', $attribute );

		foreach ( $keys as $key ) {
			if ( $key === '*' ) {
				// Handle wildcard
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

		// If an index is provided, return data at that index
		if ( $index !== null && is_array( $data ) && isset( $data[ $index ] ) ) {
			return $data[ $index ];
		}

		return $data;
	}

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

	protected function parseStringRule( $rule ) {
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

	protected function addError( $attribute, $message, $parameters, $originalAttribute = null ) {
		$attributeName = $this->customAttributes[ $originalAttribute ?? $attribute ] ?? $attribute;

		$this->errors[ $attribute ][] = str_replace(
			array( ':attribute' ),
			array( $attributeName ),
			$message
		);
	}


	protected function getMessage( $attribute, $rule, $defaultMessage ) {
		$customMessageKey = $attribute . '.' . $rule;
		return $this->customMessages[ $customMessageKey ] ?? $defaultMessage;
	}

	public function errors() {
		return $this->errors;
	}

	public function fails() {
		return ! empty( $this->errors );
	}

	public function validated() {
		if ( ! empty( $this->errors ) ) {
			throw new ValidationException( $this->errors, 'Validation failed.' ); // @codingStandardsIgnoreLine
		}

		return $this->data;
	}
}
