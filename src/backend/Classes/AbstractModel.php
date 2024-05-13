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
use DateTime;

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
	/**
	 * The model's primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The model's table name.
	 *
	 * @var string
	 */
	protected $tableName;

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
	protected $columns = array();

	/**
	 * Whether to include timestamps in the model.
	 *
	 * @var bool
	 */
	protected $timestamps = false;

	/**
	 * AbstractModel constructor.
	 *
	 * @param array $attributes The model's attributes.
	 */
	public function __construct( array $attributes = array() ) {
		if ( $this->timestamps ) {
			$this->columns['created_at'] = '%s';
			$this->columns['updated_at'] = '%s';
		}

		$this->syncOriginal();

		$this->fill( $attributes );
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

			// Set created_at and updated_at if timestamps are enabled.
			if ( ! $this->exists() ) {
				if ( 'created_at' === $key ) {
					$value = $this->currentTime();
				}

				if ( 'updated_at' === $key ) {
					$value = $this->currentTime();
				}
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
		if ( is_null( $value ) ) {
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
		if ( '%d' === $this->columns[ $column ] ) {
			return 0;
		}

		if ( '%f' === $this->columns[ $column ] ) {
			return 0.0;
		}

		return '';
	}

	/**
	 * Get the model's primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	/**
	 * Get the model's table name with prefix.
	 *
	 * @return string
	 */
	public function getTableName(): string {
		return $GLOBALS['wpdb']->prefix . $this->tableName;
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
	public function isValidColumn( string $column ): bool {
		return isset( $this->columns[ $column ] );
	}

	/**
	 * Get the format string for preparing a SQL query.
	 *
	 * @param string $column The column name.
	 * @return string The format string.
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getFormatString( string $column ): string {
		$validFormats = array( '%s', '%d', '%f' );

		if ( isset( $this->columns[ $column ] ) && in_array( $this->columns[ $column ], $validFormats, true ) ) {
			return $this->columns[ $column ];
		}

		throw new InvalidColumnException( esc_attr( "Invalid column: $column" ) );
	}

	/**
	 * Get formats for all passed columns in order.
	 *
	 * @param array $columns The columns to get formats for.
	 * @return array<string> The format strings.
	 */
	public function getFormatStrings( array $columns ): array {
		return array_map( array( $this, 'getFormatString' ), $columns );
	}

	/**
	 * Get the model's original attributes.
	 *
	 * @return array
	 */
	public function getOriginal(): array {
		return $this->original;
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
	 * @return DateTime|null
	 */
	public function setCreatedAtAttribute( $value ): DateTime {
		return $value ? new DateTime( $value ) : null;
	}

	/**
	 * Updated at setter.
	 *
	 * @param string $value The value to format.
	 * @return DateTime|null
	 */
	public function setUpdatedAtAttribute( $value ): DateTime {
		return $value ? new DateTime( $value ) : null;
	}

	/**
	 * Check if the model exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return ! empty( $this->attributes[ $this->primaryKey ] );
	}

	/**
	 * Get the current time.
	 *
	 * @param string $format The format to return the time in.
	 * @return int|bool
	 */
	public function currentTime( $format = 'mysql' ): int|bool {
		return current_time( $format );
	}

	/**
	 * Magic method to get the model's attributes.
	 *
	 * @param string $name The attribute name.
	 * @throws \InvalidArgumentException If the attribute does not exist.
	 */
	public function __get( string $name ): mixed {
		if ( $this->isValidColumn( $name ) ) {
			return $this->{$name} ?? null;
		}

		// Throw an exception if the attribute does not exist.
		throw new \InvalidArgumentException( esc_attr( "Attribute $name does not exist" ) );
	}

	/**
	 * Returns an array of properties to be serialized.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array( 'attributes', 'original' );
	}
}
