<?php
/**
 * Abstract data model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\DBException;
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
	 * The model's schema.
	 *
	 * @var AbstractSchema
	 */
	protected AbstractSchema $schema;

	/**
	 * AbstractModel constructor.
	 *
	 * @param array $attributes The model's attributes.
	 */
	public function __construct( array $attributes = array() ) {
		$this->schema = self::getSchema();

		$this->fillInitial();

		$this->syncOriginal();

		$this->fill( $attributes );
	}

	/**
	 * Create the model's schema.
	 *
	 * @return AbstractSchema
	 */
	abstract protected static function createSchema(): AbstractSchema;

	/**
	 * Get the model's schema.
	 *
	 * @return AbstractSchema
	 */
	public static function getSchema(): AbstractSchema {
		return static::createSchema();
	}

	/**
	 * Fill the model with the initial values.
	 *
	 * @return void
	 */
	public function fillInitial(): void {
		foreach ( $this->schema->getColumns() as $key => $format ) {
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
			if ( ! $this->schema->isValidColumn( $key ) ) {
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
			$format = $this->schema->getFormatString( $attribute );

			if ( '%d' === $format ) {
				$value = (int) $value;
			} elseif ( '%f' === $format ) {
				$value = (float) $value;
			} else {
				$value = (string) $value;
			}

			$this->attributes[ $attribute ] = $value;
		}

		// Check if primary key is set and the model is not new.
		if ( $this->schema->getPrimaryKey() === $attribute && $this->isNew && ! empty( $value ) ) {
			$this->setIsNew( false );
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
		if ( ! $this->schema->isValidColumn( $column ) ) {
			throw new InvalidColumnException( esc_attr( "Column $column does not exist" ) );
		}

		// Check if a method exists for the column.
		$method = 'get' . ucfirst( Str::toCamelCase( $column ) ) . 'ColumnDefaultValue';

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		// Else return based on the column type.
		if ( '%d' === $this->schema->getFormatString( $column ) ) {
			return 0;
		}

		if ( '%f' === $this->schema->getFormatString( $column ) ) {
			return 0.0;
		}

		return '';
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
	 * Get the model's original attributes.
	 *
	 * @param string $columnName The column name.
	 * @return mixed
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getOriginal( string $columnName ): mixed {
		if ( ! $this->schema->isValidColumn( $columnName ) ) {
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
		if ( $this->schema->isValidColumn( $name ) ) {
			return $this->attributes[ $name ] ?? null;
		}

		// Throw an exception if the attribute does not exist.
		throw new \InvalidArgumentException( esc_attr( "Attribute $name does not exist" ) );
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param mixed $id The primary key value.
	 * @return self|null The model instance or null if not found.
	 */
	public static function findByPrimaryKey( $id ): self|null {
		global $wpdb;
		$table      = self::getSchema()->getTableName();
		$primaryKey = self::getSchema()->getPrimaryKey();
		$sql        = $wpdb->prepare( "SELECT * FROM $table WHERE $primaryKey = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL
		$row        = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		if ( $row ) {
			$model = new static(
				$row
			);
			$model->setIsNew( false );
			return $model;
		}

		return null;
	}

	/**
	 * Find a model by its primary key.
	 *
	 * @param mixed $id The primary key value.
	 * @return static|null The model instance or null if not found.
	 * @throws InvalidColumnException If the primary key is not defined.
	 */
	public static function find( $id ) {
		return self::findByPrimaryKey( $id );
	}

	/**
	 * Create a new model instance and save it to the database.
	 *
	 * @param array $attributes Attributes to save.
	 * @return self New instance of the model after creation.
	 * @throws DBException If failed to insert.
	 */
	public static function create( array $attributes ): self {
		global $wpdb;

		// Remove primary key from attributes.
		unset( $attributes[ static::getSchema()->getPrimaryKey() ] );

		$format = self::getSchema()->getFormatStrings( array_keys( $attributes ) );

        $inserted = $wpdb->insert( // @codingStandardsIgnoreLine
			self::getSchema()->getTableName(),
			$attributes,
			$format
		);

		if ( ! $inserted ) {
			/**
			 * Hook triggered when failed to insert a model.
			 *
			 * @param string $table The table name.
			 * @param array $attributes The attributes that failed to insert.
			 * @param string $error The error message.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/failed_to_insert', self::getSchema()->getTableName(), $attributes, $wpdb->last_error );

			/**
			 * Hook triggered when failed to insert a model.
			 *
			 * @param array $attributes The attributes that failed to insert.
			 * @param string $error The error message.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/failed_to_insert/' . self::getSchema()->getShortTableName(), $attributes, $wpdb->last_error );

			throw new DBException( esc_html( $wpdb->last_error ) );
		}

		$inserted = self::find( $wpdb->insert_id );

		/**
		 * Hook triggered after inserting a model.
		 *
		 * @param string $table The table name.
		 * @param object $model Inserted model.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/after_insert', self::getSchema()->getTableName(), $inserted );

		/**
		 * Hook triggered after inserting a model.
		 *
		 * @param object $model Inserted model.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/after_insert/' . self::getSchema()->getShortTableName(), $inserted );

		return $inserted;
	}

	/**
	 * Save the model instance to the database.
	 * Determines whether to create a new record or update an existing one.
	 *
	 * @return static The model instance after saving.
	 * @throws DBException If failed to insert or update.
	 */
	public function save(): static {
		return $this->exists() ? $this->update() : static::create( $this->getAttributes() );
	}

	/**
	 * Update the model instance in the database.
	 *
	 * @return static|null The model instance after updating.
	 */
	public function update(): ?static {
		global $wpdb;

		$attributes        = $this->getAttributes();
		$primary_key       = static::getSchema()->getPrimaryKey();
		$primary_key_value = $this->getAttribute( $primary_key );

		// Remove primary key from attributes.
		unset( $attributes[ $primary_key ] );

		$format = self::getSchema()->getFormatStrings( array_keys( $attributes ) );

		$updated = $wpdb->update( // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery
			self::getSchema()->getTableName(),
			$attributes,
			array( $primary_key => $primary_key_value ),
			$format,
			array( '%d' )
		);

		if ( ! $updated ) {
			/**
			 * Hook triggered when failed to update a model.
			 *
			 * @param string $table The table name.
			 * @param array $attributes The attributes that failed to update.
			 */
			do_action( 'anspress/model/failed_to_update', self::getSchema()->getTableName(), $attributes );

			/**
			 * Hook triggered when failed to update a model.
			 *
			 * @param array $attributes The attributes that failed to update.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/failed_to_update/' . self::getSchema()->getShortTableName(), $attributes );

			return null;
		}

		$updated = self::find( $primary_key_value );

		/**
		 * Hook triggered after updating a model.
		 *
		 * @param string $table The table name.
		 * @param object $model Updated model.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/after_update', self::getSchema()->getTableName(), $updated );

		/**
		 * Hook triggered after updating a model.
		 *
		 * @param object $model Updated model.
		 */
		do_action( 'anspress/model/after_update/' . self::getSchema()->getShortTableName(), $updated );

		return $updated;
	}

	/**
	 * Delete the model instance from the database.
	 *
	 * @return bool Current model.
	 * @throws DBException If an error occurs during deletion.
	 */
	public function delete(): bool {
		global $wpdb;

		if ( ! $this->exists() ) {
			$this->setIsNew( true );
			return false;
		}

		$deleted = $wpdb->delete( // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery
			$this->schema->getTableName(),
			array(
				$this->schema->getPrimaryKey() => $this->getAttribute( $this->schema->getPrimaryKey() ),
			),
			array( $this->schema->getFormatString( $this->schema->getPrimaryKey() ) )
		);

		if ( ! $deleted ) {
			/**
			 * Hook triggered when failed to delete a model.
			 *
			 * @param string $table The table name.
			 * @param array $attributes The attributes that failed to delete.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/failed_to_delete', $this->schema->getTableName(), $this->getAttributes() );

			/**
			 * Hook triggered when failed to delete a model.
			 *
			 * @param array $attributes The attributes that failed to delete.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/failed_to_delete/' . $this->schema->getShortTableName(), $this->getAttributes() );

			return false;
		}

		$this->setIsNew( true );

		/**
		 * Hook triggered after deleting a model.
		 *
		 * @param string $table The table name.
		 * @param object $model Deleted model.
		 */
		do_action( 'anspress/model/after_delete', $this->schema->getTableName(), $this );

		/**
		 * Hook triggered after deleting a model.
		 *
		 * @param object $model Deleted model.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/after_delete/' . $this->schema->getShortTableName(), $this );

		return true;
	}

	/**
	 * Find models by a SQL query.
	 *
	 * @param string $sql The SQL query.
	 * @return self[] The models found.
	 */
	public static function findMany( string $sql ): array {
		global $wpdb;

		$rows = $wpdb->get_results( $sql, ARRAY_A ); // @codingStandardsIgnoreLine WordPress.DB

		$models = array();

		foreach ( $rows as $row ) {
			$model = new static( $row );

			$models[] = $model;
		}

		return $models;
	}
}
