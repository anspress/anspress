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
}
