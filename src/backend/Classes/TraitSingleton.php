<?php
/**
 * Singleton trait.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rahul@zenprojects.com>
 * @since     5.0.0
 */

namespace AnsPress\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Singleton
 *
 * @package AnsPress\Classes
 */
trait TraitSingleton {
	/**
	 * Prevent cloning of the object.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cloning is not allowed.', 'anspress-question-answer' ), '5.0.0' );
	}

	/**
	 * Prevent unserializing of the object.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_attr__( 'Unserializing is not allowed.', 'anspress-question-answer' ), '5.0.0' );
	}
}
