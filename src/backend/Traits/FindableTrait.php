<?php
/**
 * Findable trait.
 *
 * @package AnsPress
 * @since 5.0.0
 */
namespace AnsPress\Traits;

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
	 * Find all models.
	 *
	 * @param string $query Query.
	 * @return array
	 */
	public static function where( string $query ) {
		global $wpdb;

		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB

		$models = array();

		foreach ( $results as $result ) {
			$model = new static();
			$model->setAttributes( $result );
			$models[] = $model;
		}

		return $models;
	}

	/**
	 * Find model by primary key.
	 *
	 * @param mixed $id Primary key value.
	 * @return mixed|null Model instance or null if not found.
	 */
	public static function find( $id ) {
		global $wpdb;

		$query = self::where(
			$wpdb->prepare(
				"SELECT * FROM {$this->getTableName()} WHERE {$this->getPrimaryKey()} = %d AND {$this->getPrimaryKey()} IS NOT NULL LIMIT 1",
				$id
			)
		);

		$result = $wpdb->get_row( $query, ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		// Set attributes to the retrieved row.
		$instance->setAttributes( $result );

		return $instance;
	}
}
