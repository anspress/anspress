<?php
/**
 * Base class for singleton.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @since      4.1.8
 */

namespace AnsPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * A class to be used as a base for all singleton classes.
 *
 * @since 4.1.8
 */
abstract class Singleton {

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	private function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'anspress-question-answer' ), '1.0.0' ); // WPCS: xss okay.
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 4.1.8
	 */
	private function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'anspress-question-answer' ), '1.0.0' ); // WPCS: xss okay.
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return AnsPress\Singleton A single instance of this class.
	 * @since 4.1.8
	 */
	public static function init() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
			static::$instance->run_once();
		}

		return static::$instance;
	}

	/**
	 * Placeholder function which is called only once.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public function run_once() {

	}
}
