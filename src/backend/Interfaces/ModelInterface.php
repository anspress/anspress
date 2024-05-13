<?php
/**
 * Model interface.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Interfaces;

use DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface DataModelInterface
 *
 * @package AnsPress\Interfaces
 */
interface ModelInterface {
	/**
	 * AbstractModel constructor.
	 *
	 * @param array $attributes The model's attributes.
	 */
	public function __construct( array $attributes = array() );

	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param array $attributes The attributes to fill.
	 * @throws InvalidColumnException If an invalid column is provided.
	 */
	public function fill( array $attributes ): void;

	/**
	 * Set the model's attribute. This method will also call mutator
	 * if available.
	 *
	 * @param string $attribute The attribute name.
	 * @param mixed  $value     The attribute value.
	 */
	public function setAttribute( string $attribute, $value ): void;

	/**
	 * Get the model's attribute. This method will also call accessor
	 * if available.
	 *
	 * @param string $attribute The attribute name.
	 * @return mixed
	 */
	public function getAttribute( string $attribute ): mixed;

	/**
	 * Get the default value for a column.
	 *
	 * @param string $column Column name.
	 * @return mixed
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getColumnDefaultValue( string $column ): mixed;

	/**
	 * Get the model's primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string;

	/**
	 * Get the model's table name with prefix.
	 *
	 * @return string
	 */
	public function getTableName(): string;

	/**
	 * Get the model's attributes.
	 *
	 * @return array
	 */
	public function getAttributes(): array;

	/**
	 * Get the format string for preparing a SQL query.
	 *
	 * @param string $column The column name.
	 * @return string The format string.
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getFormatString( string $column ): string;

	/**
	 * Get formats for all passed columns in order.
	 *
	 * @param array $columns The columns to get formats for.
	 * @return array<string> The format strings.
	 */
	public function getFormatStrings( array $columns ): array;

	/**
	 * Get the model's original attributes.
	 *
	 * @return array
	 */
	public function getOriginal(): array;

	/**
	 * Sync the original attributes with the current attributes.
	 */
	public function syncOriginal(): void;

	/**
	 * Created at setter.
	 *
	 * @param string $value The value to format.
	 * @return DateTime|null
	 */
	public function setCreatedAtAttribute( $value ): DateTime;

	/**
	 * Updated at setter.
	 *
	 * @param string $value The value to format.
	 * @return DateTime|null
	 */
	public function setUpdatedAtAttribute( $value ): DateTime;

	/**
	 * Check if the model exists.
	 *
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * Get the current time.
	 *
	 * @param string $format The format to return the time in.
	 * @return int|bool
	 */
	public function currentTime( $format = 'mysql' ): int|bool;

	/**
	 * Get model data as array.
	 *
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Get model data as json.
	 *
	 * @return string
	 */
	public function toJson(): string;

	/**
	 * Magic method to get the model's attributes.
	 *
	 * @param string $name The attribute name.
	 * @throws \InvalidArgumentException If the attribute does not exist.
	 */
	public function __get( string $name ): mixed;

	/**
	 * Returns an array of properties to be serialized.
	 *
	 * @return array
	 */
	public function __sleep();
}
