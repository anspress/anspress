<?php
/**
 * HTTP exception.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Exceptions;

use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP exception.
 *
 * @since 5.0.0
 */
class HTTPException extends Exception {
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Constructor.
	 *
	 * @param int            $status_code HTTP status code.
	 * @param string         $message Error message.
	 * @param int            $code Error code.
	 * @param Exception|null $previous Previous exception.
	 * @return void
	 */
	public function __construct( $status_code, $message = 'HTTP error', $code = 0, Exception $previous = null ) {
		$this->status_code = $status_code;
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Get HTTP status code.
	 *
	 * @return int
	 */
	public function getStatusCode() {
		return $this->status_code;
	}
}
