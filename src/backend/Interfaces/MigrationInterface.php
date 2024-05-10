<?php
/**
 * Migration interface.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration interface.
 *
 * @since 5.0.0
 */
interface MigrationInterface {
	/**
	 * Method to run migration.
	 *
	 * @return void
	 */
	public function run();
}
