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
	 * Find a model by its primary key.
	 *
	 * @param mixed $id The primary key value.
	 * @return self|null The model instance or null if not found.
	 */
	public static function findByPrimaryKey( $id ): self|null {
		global $wpdb;
		$table      = self::getTableName();
		$primaryKey = self::getPrimaryKey();
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
		return ( new static() )->findByPrimaryKey( $id );
	}
}
