<?php
/**
 * Service interface.
 *
 * @since 5.0.0
 * @package AnsPress\Core
 */

namespace AnsPress\Core\Interfaces;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface ServiceInterface.
 *
 * @since 5.0.0
 * @template T of ServiceInterface
 */
interface ServiceInterface {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct();

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks();
}
