<?php
/**
 * Answers content
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check question is set or not.
if ( ! isset( $args['question'] ) ) {
	throw new GeneralException( 'Question not set.' );
}
