<?php
/**
 * Validation exception.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Exceptions;

use AnsPress\Classes\Logger;
use AnsPress\Classes\Plugin;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validation exception.
 *
 * @since 5.0.0
 */
class ValidationException extends Exception {
	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Constructor.
	 *
	 * @param array          $errors Validation errors.
	 * @param string         $message Error message.
	 * @param int            $code Error code.
	 * @param Exception|null $previous Previous exception.
	 * @return void
	 */
	public function __construct( $errors = array(), $message = 'Validation failed', $code = 0, Exception $previous = null ) {
		$this->errors = $errors;
		parent::__construct( $message, $code, $previous );

		// Log the exception message.
		Plugin::get( Logger::class )->log( Logger::LOG_LEVEL_ERROR, $message, $this->getErrors() );
	}

	/**
	 * Get validation errors.
	 *
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}
