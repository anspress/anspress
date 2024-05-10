<?php
/**
 * Singleton interface.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rahul@zenprojects.com>
 * @since     5.0.0
 */

namespace AnsPress\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface SingletonInterface
 *
 * @package AnsPress\Interfaces
 */
interface SingletonInterface {
	/**
	 * Prevent cloning of the object.
	 */
	public function __clone();

	/**
	 * Prevent unserializing of the object.
	 */
	public function __wakeup();
}
