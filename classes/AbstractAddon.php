<?php
/**
 * Abstract class for addons.
 *
 * @package AnsPress
 * @subpackage Classes
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use Exception;

/**
 * Class AbstractAddon.
 *
 * @package AnsPress\Classes
 */
abstract class AbstractAddon {

	/**
	 * Whether the addon is installed or not.
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Check if the addon is installed.
	 *
	 * @return bool
	 */
	final public function is_initialized(): bool {
		return self::$initialized;
	}

	/**
	 * Initialize the class.
	 *
	 * @since 5.0.0
	 * @throws \Exception If addon is already installed.
	 */
	public function init(): void {
		if ( $this->is_initialized() ) {
			throw new \Exception( 'Addon already initialized.' );
		}

		self::$initialized = true;

		// Add default options.
		$default_options = $this->default_options();

		if ( ! empty( $default_options ) ) {
			ap_add_default_options( $default_options );
		}

		// Add filters and actions.
		$this->add_filters_and_actions();
	}

	/**
	 * Install the addon.
	 *
	 * @return void
	 * @throws \Exception If addon is not initialized.
	 */
	abstract public function install(): void;

	/**
	 * Uninstall the addon.
	 *
	 * @return void
	 */
	abstract public function uninstall(): void;

	/**
	 * Add default options.
	 *
	 * @return array
	 * @since 5.0.0
	 */
	abstract protected function default_options(): array;

	/**
	 * Add filters and actions.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	protected function add_filters_and_actions() {
	}
}
