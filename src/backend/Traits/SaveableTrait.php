<?php
/**
 * Saveable trait.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Traits;

use AnsPress\Exceptions\DBException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait SaveableTrait
 *
 * @package AnsPress\Traits
 */
trait SaveableTrait {

	/**
	 * Create a new model instance and save it to the database.
	 *
	 * @param array $attributes Attributes to save.
	 * @return static New instance of the model after creation.
	 * @throws DBException If failed to insert.
	 */
	public static function create( array $attributes ): static {
		global $wpdb;

		$format = self::getFormatStrings( $attributes );

        $inserted = $wpdb->insert( // @codingStandardsIgnoreLine
			self::getTableName(),
			$attributes,
			$format
		);

		if ( ! $inserted ) {
			throw new DBException( esc_html( $wpdb->last_error ) );
		}

		return self::find( $wpdb->insert_id );
	}

	/**
	 * Save the model instance to the database.
	 * Determines whether to create a new record or update an existing one.
	 *
	 * @return static The model instance after saving.
	 * @throws DBException If failed to insert or update.
	 */
	public function save(): static {
		if ( $this->exists() ) {
			return $this->update();
		} else {
			return static::create( $this->getAttributes() );
		}
	}

	/**
	 * Update the model instance in the database.
	 *
	 * @return static The model instance after updating.
	 * @throws DBException If failed to update.
	 */
	public function update(): static {
		global $wpdb;

		$format = self::getFormatStrings( $this->getAttributes() );

        $updated = $wpdb->update( // @codingStandardsIgnoreLine
			self::getTableName(),
			$this->getAttributes(),
			array( static::getPrimaryKey() => $this->getAttribute( static::getPrimaryKey() ) ),
			$format,
			array( '%d' )
		);

		if ( ! $updated ) {
			throw new DBException( esc_html( $wpdb->last_error ) );
		}

		return $this;
	}

	/**
	 * Delete the model instance from the database.
	 *
	 * @return self Current model.
	 * @throws DBException If an error occurs during deletion.
	 */
	public function delete(): bool {
		global $wpdb;

		if ( ! $this->exists() ) {
			$this->setIsNew( true );
			return $this;
		}

		$deleted = $wpdb->delete( // @codingStandardsIgnoreLine WordPress.DB.DirectDatabaseQuery
			self::getTableName(),
			array( static::getPrimaryKey() => $this->getAttribute( static::getPrimaryKey() ) ),
			array( static::getFormatString( self::getPrimaryKey() ) )
		);

		if ( ! $deleted ) {
			throw new DBException( esc_html( $wpdb->last_error ) );
		}

		$this->setIsNew( true );

		return $this;
	}
}
