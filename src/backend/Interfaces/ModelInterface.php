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
