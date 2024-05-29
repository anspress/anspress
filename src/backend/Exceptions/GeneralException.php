<?php
/**
 * General exception class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Exceptions;

use AnsPress\Classes\Logger;
use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle general exceptions.
 *
 * @package AnsPress\Exceptions
 */
class GeneralException extends \Exception {
	/**
	 * Constructor to initialize the exception.
	 *
	 * @param string $message The exception message.
	 * @param int    $code    Optional. The exception code.
	 * @param mixed  $data    Optional. Additional data to log.
	 */
	public function __construct( string $message, int $code = 0, $data = null ) {
		parent::__construct( $message, $code );

		// Log the exception message.
		Plugin::get( Logger::class )->log( Logger::LOG_LEVEL_ERROR, $message, $data );
	}
}
