<?php
/**
 * Findable trait.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Traits;

use AnsPress\Exceptions\InvalidColumnException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait FindableTrait
 *
 * @package AnsPress\Traits
 */
trait FindableTrait {
	/**
	 * Find a model by its primary key (internal method).
	 *
	 * @param mixed $id The primary key value.
	 * @return $this|null The model instance or null if not found.
	 * @throws InvalidColumnException If the primary key is not defined.
	 */
	public function findByPrimaryKey( $id ) {
		if ( empty( $this->primaryKey ) ) {
			throw new InvalidColumnException( 'Primary key is not defined for this model.' );
		}

		global $wpdb;
		$table = $this->getTableName();
		$sql   = $wpdb->prepare( "SELECT * FROM $table WHERE $this->primaryKey = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL
		$row   = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		if ( $row ) {
			$this->fill( $row );
			return $this;
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
		return ( new static() )->findByPrimaryKey( $id );
	}

	/**
	 * Get all models.
	 *
	 * @return static[] An array of model instances.
	 */
	public static function all() {
		return ( new static() )->getAll();
	}

	/**
	 * Get all models.
	 *
	 * @return $this[] An array of model instances.
	 */
	public function getAll() {
		global $wpdb;

		$table = $this->getTableName();
		$rows  = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A ); // phpcs:ignore WordPress.DB

		$models = array();
		foreach ( $rows as $row ) {
			$model    = new static( $row );
			$models[] = $model;
		}

		return $models;
	}

	/**
	 * Get models based on where conditions (internal method).
	 *
	 * @param string $query The query string.
	 * @param array  $bindings The query bindings.
	 * @return $this[] An array of model instances.
	 */
	public function where( string $query, $bindings = array() ) {
		global $wpdb;
		$table = $this->getTableName();

		$sql  = $wpdb->prepare( "SELECT * FROM $table WHERE $query", $bindings ); // phpcs:ignore WordPress.DB
		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		$models = array();
		foreach ( $rows as $row ) {
			$model    = new static( $row );
			$models[] = $model;
		}

		return $models;
	}
}
