<?php
/**
 * Abstract data model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\InvalidColumnException;
use AnsPress\Interfaces\ModelInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractModel
 *
 * @package AnsPress\Classes
 */
abstract class AbstractModel implements ModelInterface {
	use \AnsPress\Traits\FindableTrait;

	/**
	 * The model's primary key.
	 *
	 * @var string
	 */
	protected static $primaryKey = 'id';

	/**
	 * The model's table name.
	 *
	 * @var string
	 */
	protected static $tableName;

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The model's original attributes.
	 *
	 * @var array
	 */
	protected $original = array();

	/**
	 * Array mapping column names to their database formats (%s, %d, or %f).
	 *
	 * @var array<string, string>
	 */
	protected static $columns = array();

	/**
	 * Whether to include timestamps in the model.
	 *
	 * @var bool
	 */
	protected static $timestamps = false;

	/**
	 * Whether the model is new.
	 *
	 * @var bool
	 */
	protected $isNew = true;

	/**
	 * AbstractModel constructor.
	 *
	 * @param array $attributes The model's attributes.
	 */
	public function __construct( array $attributes = array() ) {
		$this->fillInitial();

		$this->syncOriginal();

		$this->fill( $attributes );
	}

	/**
	 * Fill the model with the initial values.
	 *
	 * @return void
	 */
	public function fillInitial(): void {
		foreach ( get_called_class()::getColumns() as $key => $format ) {
			$this->setAttribute( $key, null );
		}
	}

	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param array $attributes The attributes to fill.
	 * @throws InvalidColumnException If an invalid column is provided.
	 */
	public function fill( array $attributes ): void {
		foreach ( $attributes as $key => $value ) {
			if ( ! $this->isValidColumn( $key ) ) {
				throw new InvalidColumnException( esc_attr( "Invalid attribute: $key" ) );
			}

			// Use setter to allow for mutators and formatting.
			$this->setAttribute( $key, $value );
		}
	}

	/**
	 * Set the model's attribute. This method will also call mutator
	 * if available.
	 *
	 * @param string $attribute The attribute name.
	 * @param mixed  $value     The attribute value.
	 */
	public function setAttribute( string $attribute, $value ): void {
		$method = 'set' . ucfirst( Str::toCamelCase( $attribute ) ) . 'Attribute';

		// Get default value if value is null.
		if ( is_null( $value ) && ! $this->exists() ) {
			$value = $this->getColumnDefaultValue( $attribute );
		}

		if ( method_exists( $this, $method ) ) {
			$this->attributes[ $attribute ] = $this->{$method}( $value );
		} else {
			$format = $this->getFormatString( $attribute );

			if ( '%d' === $format ) {
				$value = (int) $value;
			} elseif ( '%f' === $format ) {
				$value = (float) $value;
			} else {
				$value = (string) $value;
			}

			$this->attributes[ $attribute ] = $value;
		}
	}

	/**
	 * Get the model's attribute. This method will also call accessor
	 * if available.
	 *
	 * @param string $attribute The attribute name.
	 * @return mixed
	 */
	public function getAttribute( string $attribute ): mixed {
		$method = 'get' . ucfirst( Str::toCamelCase( $attribute ) ) . 'Attribute';

		if ( method_exists( $this, $method ) ) {
			return $this->{$method}( $this->attributes[ $attribute ] );
		}

		return $this->attributes[ $attribute ] ?? null;
	}

	/**
	 * Get the default value for a column.
	 *
	 * @param string $column Column name.
	 * @return mixed
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getColumnDefaultValue( string $column ): mixed {
		if ( ! $this->isValidColumn( $column ) ) {
			throw new InvalidColumnException( esc_attr( "Column $column does not exist" ) );
		}

		// Check if a method exists for the column.
		$method = 'get' . ucfirst( Str::toCamelCase( $column ) ) . 'ColumnDefaultValue';

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		// Else return based on the column type.
		if ( '%d' === get_called_class()::getFormatString( $column ) ) {
			return 0;
		}

		if ( '%f' === get_called_class()::getFormatString( $column ) ) {
			return 0.0;
		}

		return '';
	}

	/**
	 * Get the model's columns.
	 *
	 * @return array
	 */
	public static function getColumns(): array {
		if ( get_called_class()::$timestamps ) {
			get_called_class()::$columns['created_at'] = '%s';
			get_called_class()::$columns['updated_at'] = '%s';
		}

		return get_called_class()::$columns;
	}

	/**
	 * Get foramt of a column by name.
	 *
	 * @param string $column The column name.
	 * @return string|null The column format.
	 */
	public static function getColumnFormat( string $column ): string {
		return get_called_class()::$columns[ $column ] ?? null;
	}

	/**
	 * Get the model's primary key.
	 *
	 * @return string
	 */
	public static function getPrimaryKey(): string {
		return get_called_class()::$primaryKey;
	}

	/**
	 * Get the model's table name with prefix.
	 *
	 * @return string
	 */
	public static function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . ( get_called_class()::$tableName );
	}

	/**
	 * Get the model's attributes.
	 *
	 * @return array
	 */
	public function getAttributes(): array {
		return $this->attributes;
	}

	/**
	 * Check if a column is valid.
	 *
	 * @param string $column The column name.
	 * @return bool
	 */
	public static function isValidColumn( string $column ): bool {
		return isset( get_called_class()::getColumns()[ $column ] );
	}

	/**
	 * Get the format string for preparing a SQL query.
	 *
	 * @param string $column The column name.
	 * @return string The format string.
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public static function getFormatString( string $column ): string {
		$validFormats = array( '%s', '%d', '%f' );

		$columns = get_called_class()::getColumns();

		if ( isset( $columns[ $column ] ) && in_array( $columns[ $column ], $validFormats, true ) ) {
			return $columns[ $column ];
		}

		throw new InvalidColumnException( esc_attr( "Invalid column: $column" ) );
	}

	/**
	 * Get formats for all passed columns in order.
	 *
	 * @param array $columns The columns to get formats for.
	 * @return string[] The format strings.
	 */
	public static function getFormatStrings( array $columns ): array {
		return array_map( array( get_called_class(), 'getFormatString' ), $columns );
	}

	/**
	 * Get the model's original attributes.
	 *
	 * @param string $columnName The column name.
	 * @return mixed
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getOriginal( string $columnName ): mixed {
		if ( ! $this->isValidColumn( $columnName ) ) {
			throw new InvalidColumnException( esc_attr( "Invalid column: $columnName" ) );
		}

		return $this->original[ $columnName ] ?? null;
	}

	/**
	 * Sync the original attributes with the current attributes.
	 */
	public function syncOriginal(): void {
		$this->original = $this->attributes;
	}

	/**
	 * Created at setter.
	 *
	 * @param string $value The value to format.
	 * @return string
	 */
	public function setCreatedAtAttribute( $value ): string {
		return $value ? $value : $this->currentTime( 'mysql' );
	}

	/**
	 * Updated at setter.
	 *
	 * @param string $value The value to format.
	 * @return string
	 */
	public function setUpdatedAtAttribute( $value ): string {
		return $value ? $value : $this->currentTime( 'mysql' );
	}

	/**
	 * Check if the model exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return ! $this->isNew;
	}

	/**
	 * Get the current time.
	 *
	 * @param string $format The format to return the time in.
	 * @return mixed
	 */
	public function currentTime( $format = 'mysql' ): mixed {
		return current_time( $format );
	}

	/**
	 * Set whether the model is new.
	 *
	 * @param bool $isNew Whether the model is new.
	 * @return void
	 */
	public function setIsNew( bool $isNew ): void {
		$this->isNew = $isNew;
	}

	/**
	 * Convert the model to an array.
	 *
	 * @return array The model's attributes.
	 */
	public function toArray(): array {
		return $this->attributes;
	}

	/**
	 * Convert the model to JSON.
	 *
	 * @return string
	 */
	public function toJson(): string {
		return wp_json_encode( $this->toArray() );
	}

	/**
	 * Hydrate array.
	 *
	 * @param array $data The data to convert.
	 * @return array The converted models.
	 */
	public static function hydrate( array $data ): array {
		$models = array();

		foreach ( $data as $attributes ) {
			$model = new static( $attributes );
			$model->setIsNew( false );

			$models[] = $model;
		}

		return $models;
	}

	/**
	 * Magic method to get the model's attributes.
	 *
	 * @param string $name The attribute name.
	 * @throws \InvalidArgumentException If the attribute does not exist.
	 */
	public function __get( string $name ): mixed {
		if ( $this->isValidColumn( $name ) ) {
			return $this->attributes[ $name ] ?? null;
		}

		// Throw an exception if the attribute does not exist.
		throw new \InvalidArgumentException( esc_attr( "Attribute $name does not exist" ) );
	}
}
