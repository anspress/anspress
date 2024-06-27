<?php
/**
 * Module interface.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Interfaces;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface ModuleInterface.
 *
 * @since 5.0.0
 * @template T of ModuleInterface
 */
interface ModuleInterface {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks();
}
