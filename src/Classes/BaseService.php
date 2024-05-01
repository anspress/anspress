<?php
/**
 * Base class for services.
 *
 * @since 5.0.0
 * @package AnsPress\Core
 */

namespace AnsPress\Core\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for services.
 *
 * @since 5.0.0
 */
abstract class BaseService {
	/**
	 * Constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Placeholder function to be called in constructor.
	 *
	 * @since 5.0.0
	 */
	public function register_hooks(): void {
	}
}
