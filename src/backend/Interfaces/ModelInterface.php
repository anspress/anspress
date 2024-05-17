<?php
/**
 * Model interface.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Interfaces;

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
	 * Fill the model with the initial values.
	 *
	 * @return void
	 */
	public function fillInitial(): void;

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
	 * Get foramt of a column by name.
	 *
	 * @param string $column The column name.
	 * @return string|null The column format.
	 */
	public static function getColumnFormat( string $column ): string;

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
	public static function getPrimaryKey(): string;

	/**
	 * Get the model's table name with prefix.
	 *
	 * @return string
	 */
	public static function getTableName(): string;

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
	public static function getFormatString( string $column ): string;

	/**
	 * Get formats for all passed columns in order.
	 *
	 * @param array $columns The columns to get formats for.
	 * @return array<string> The format strings.
	 */
	public static function getFormatStrings( array $columns ): array;

	/**
	 * Get the model's original attributes.
	 *
	 * @param string $columnName The column name.
	 * @return mixed
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getOriginal( string $columnName ): mixed;

	/**
	 * Sync the original attributes with the current attributes.
	 */
	public function syncOriginal(): void;

	/**
	 * Created at setter.
	 *
	 * @param string $value The value to format.
	 * @return string|null
	 */
	public function setCreatedAtAttribute( $value ): string;

	/**
	 * Updated at setter.
	 *
	 * @param string $value The value to format.
	 * @return string|null
	 */
	public function setUpdatedAtAttribute( $value ): string;

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
	 * @return mixed
	 */
	public function currentTime( $format = 'mysql' ): mixed;

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
	 * Set isNew flag.
	 *
	 * @param bool $isNew The flag to set.
	 */
	public function setIsNew( bool $isNew ): void;

	/**
	 * Hydrate array.
	 *
	 * @param array $data The data to convert.
	 * @return array The converted models.
	 */
	public static function hydrate( array $data ): array;

	/**
	 * Magic method to get the model's attributes.
	 *
	 * @param string $name The attribute name.
	 * @throws \InvalidArgumentException If the attribute does not exist.
	 */
	public function __get( string $name ): mixed;
}
