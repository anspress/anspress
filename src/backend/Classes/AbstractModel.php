<?php
/**
 * Abstract data model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ModelInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract data model.
 *
 * @package AnsPress
 */
abstract class AbstractModel implements ModelInterface {
	/**
	 * Primary column key.
	 *
	 * @var string
	 */
	protected string $primaryKey = 'id';

	/**
	 * Get primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}
}
