<?php
/**
 * Invalid column exception.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Exceptions;

use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class InvalidColumnException
 *
 * @package AnsPress\Exceptions
 */
class InvalidColumnException extends Exception {
	/**
	 * Serialize the exception.
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return array(
			'message' => $this->getMessage(),
			'code'    => $this->getCode(),
		);
	}

	/**
	 * Unserialize the exception.
	 *
	 * @param array $data The exception data.
	 * @return void
	 */
	public function __unserialize( array $data ): void {
		parent::__construct( $data['message'], $data['code'] );
	}
}
