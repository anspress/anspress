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
	 * Get primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string;

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName(): string;

	/**
	 * Set model attributes.
	 *
	 * @param array $attributes Attributes.
	 * @return void
	 */
	public function setAttributes( array $attributes ): void;

	/**
	 * Get model attribute.
	 *
	 * @param string $key Attribute key.
	 * @return mixed
	 */
	public function getAttribute( string $key );

	/**
	 * Set model attribute.
	 *
	 * @param string $key Attribute key.
	 * @param mixed  $value Attribute value.
	 * @return void
	 */
	public function setAttribute( string $key, mixed $value ): void;

	/**
	 * Update model.
	 *
	 * @param array $data Data to update.
	 * @return self
	 */
	public function update( array $data ): self;

	/**
	 * Save model.
	 *
	 * @return mixed
	 */
	public function save();

	/**
	 * Delete model.
	 *
	 * @return mixed
	 */
	public function delete();

	/**
	 * Find model by primary key.
	 *
	 * @param int|string $id Primary key.
	 * @return mixed
	 */
	public static function find( int|string $id );

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
}
