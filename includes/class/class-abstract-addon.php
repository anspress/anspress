<?php
/**
 * Abstract class for addons.
 *
 * @package AnsPress
 * @subpackage Classes
 * @since 5.0.0
 */

namespace AnsPress\Classes;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Exception;

/**
 * Class AbstractAddon.
 *
 * @package AnsPress\Classes
 */
abstract class AbstractAddon {

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
	 * Check if the addon is installed.
	 *
	 * @return bool
	 */
	abstract public function is_initialized(): bool;

	/**
	 * Initialize the addon.
	 *
	 * @return void
	 */
	abstract public function set_initialized(): void;

	/**
	 * Initialize the class.
	 *
	 * @since 5.0.0
	 * @throws \Exception If addon is already installed.
	 */
	public function init(): bool {
		if ( $this->is_initialized() ) {
			throw new \Exception( 'Addon already initialized.' );
		}

		// Check if addon can be initialized.
		if ( ! $this->pre_check_passed() ) {
			return false;
		}

		$this->set_initialized();

		$this->require_files();

		// Add default options.
		$default_options = $this->default_options();

		if ( ! empty( $default_options ) ) {
			ap_add_default_options( $default_options );
		}

		// Add filters and actions.
		$this->add_filters_and_actions();

		return true;
	}

	/**
	 * Get addon file.
	 *
	 * @return string
	 */
	public function addon_file() {
		return basename( __FILE__ );
	}

	/**
	 * Check if add-on can be initialized.
	 *
	 * @return bool
	 */
	public function pre_check_passed(): bool {
		return ap_is_addon_active( $this->addon_file() );
	}

	/**
	 * This method is called before the addon is initialized.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	protected function require_files(): void {
	}

	/**
	 * Add filters and actions.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	protected function add_filters_and_actions(): void {
	}
}
