<?php
/**
 * Model interface.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Interfaces;

use AnsPress\Classes\AbstractSchema;

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
	 * Get the model's schema.
	 *
	 * @return AbstractSchema
	 */
	public static function getSchema(): AbstractSchema;

	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param array $attributes The attributes to fill.
	 * @throws InvalidColumnException If an invalid column is provided.
	 */
	public function fill( array $attributes ): void;

	/**
	 * Set the model's attribute.
	 *
	 * @param string $attribute The attribute name.
	 * @param mixed  $value     The attribute value.
	 */
	public function setAttribute( string $attribute, $value ): void;

	/**
	 * Get the model's attribute.
	 *
	 * @param string $attribute The attribute name.
	 * @return mixed
	 */
	public function getAttribute( string $attribute ): mixed;

	/**
	 * Get the model's attributes.
	 *
	 * @return array
	 */
	public function getAttributes(): array;

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
	 * Check if the model exists.
	 *
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * Set whether the model is new.
	 *
	 * @param bool $isNew Whether the model is new.
	 */
	public function setIsNew( bool $isNew ): void;

	/**
	 * Convert the model to an array.
	 *
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Convert the model to JSON.
	 *
	 * @return string
	 */
	public function toJson(): string;
}
