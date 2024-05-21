<?php
/**
 * DB exception.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Exceptions;

use AnsPress\Classes\Logger;
use AnsPress\Classes\Plugin;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DBException
 *
 * @package AnsPress\Exceptions
 */
class DBException extends Exception {
	/**
	 * DBException constructor.
	 *
	 * @param string $message Exception message.
	 */
	public function __construct( $message ) {
		parent::__construct( $message );

		// Logging.
		Plugin::get( Logger::class )->error( $message );
	}
}
