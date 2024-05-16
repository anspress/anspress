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
	 * @return self|null The model instance or null if not found.
	 */
	public function findByPrimaryKey( $id ): self|null {
		global $wpdb;
		$table = $this->getTableName();
		$sql   = $wpdb->prepare( "SELECT * FROM $table WHERE $this->primaryKey = %d", $id ); // phpcs:ignore WordPress.DB.PreparedSQL
		$row   = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		if ( $row ) {
			$this->fill( $row );
			$this->setIsNew( false );
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
	 * Get models based on where conditions.
	 *
	 * @param string $query The query string.
	 * @param array  $bindings The query bindings.
	 * @return array An array of model instances.
	 */
	public function where( string $query, $bindings = array() ): array {
		global $wpdb;
		$table = $this->getTableName();

		$sql  = $wpdb->prepare( "SELECT * FROM $table WHERE $query", $bindings ); // phpcs:ignore WordPress.DB
		$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB

		if ( ! $rows ) {
			return array();
		}

		$models = array();
		foreach ( $rows as $row ) {
			$model = new static( $row );
			$model->setIsNew( false );
			$models[] = $model;
		}

		return $models;
	}
}
