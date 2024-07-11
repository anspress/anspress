<?php
/**
 * Unique rule.
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
 * Unique rule.
 *
 * @package AnsPress\Classes\Rules
 */
class UniqueRule implements ValidationRuleInterface {
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
	 * Ignore id.
	 *
	 * @var null|int
	 */
	protected ?int $ignoreId;

	/**
	 * Ignore column.
	 *
	 * @var mixed
	 */
	protected $ignoreColumn;

	/**
	 * UniqueRule constructor.
	 *
	 * @param string      $table Table name.
	 * @param string|null $column Column name.
	 * @param int|null    $ignoreId Ignore id.
	 * @param string      $ignoreColumn Ignore column.
	 */
	public function __construct( $table, $column = null, $ignoreId = null, $ignoreColumn = 'id' ) {
		global $wpdb;

		$this->table        = $wpdb->prefix . $table;
		$this->column       = $column;
		$this->ignoreId     = $ignoreId;
		$this->ignoreColumn = $ignoreColumn;
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'unique';
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

		if ( $this->ignoreId ) {
			$query .= $wpdb->prepare( " AND {$this->ignoreColumn} != %d", $this->ignoreId ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$count = $wpdb->get_var( $query ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared

		return '1' === $count ? false : true;
	}

	/**
	 * Get rule error message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'The :attribute has already been taken.';
	}
}
