<?php
/**
 * Exists rule.
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
 * Exists rule.
 *
 * @package AnsPress\Classes\Rules
 */
class ExistsRule implements ValidationRuleInterface {
	/**
	 * Table name.
	 *
	 * @var mixed
	 */
	protected string $table;

	/**
	 * Column name.
	 *
	 * @var null|string
	 */
	protected ?string $column;

	/**
	 * ExistsRule constructor.
	 *
	 * @param string      $table Table name.
	 * @param string|null $column Column name.
	 */
	public function __construct( $table, $column = null ) {
		global $wpdb;

		$this->table  = $wpdb->prefix . $table;
		$this->column = $column;
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'exists';
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
		global $wpdb;

		if ( $this->column ) {
			$column = $this->column;
		} else {
			$column = $attribute;
		}

		$query = $wpdb->prepare( "SELECT 1 FROM {$this->table} WHERE {$column} = %s", $value ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$count = $wpdb->get_var( $query ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared

		return '1' === $count ? true : false;
	}

	/**
	 * Get error message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'The selected :attribute is invalid.';
	}
}
