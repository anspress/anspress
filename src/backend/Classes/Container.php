<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress container class.
 *
 * @since 5.0.0
 */
class Container {
	/**
	 * Array of services.
	 *
	 * @var ModuleInterface[]
	 */
	private $modules = array();

	/**
	 * Set service object.
	 *
	 * @param class-string $serviceObject Service object.
	 * @return void
	 *
	 * @throws \Exception If invalid service.
	 */
	public function set( string $serviceObject ) {
		if ( ! class_exists( $serviceObject ) ) {
			throw new \Exception( esc_attr( 'Not a valid class.' ) );
		}

		$instnace = new $serviceObject();

		if ( ! $instnace instanceof ModuleInterface ) {
			throw new \Exception( esc_attr( 'Invalid service object.' ) );
		}

		$this->modules[ get_class( $instnace ) ] = $instnace;
	}

	/**
	 * Method to load services on demand.
	 *
	 * @param class-string $moduleName Service name.
	 * @return ModuleInterface|null Service object or null if not found.
	 * @throws \InvalidArgumentException If the service name is not valid.
	 */
	public function get( string $moduleName ): ?ModuleInterface {
		if ( ! isset( $this->modules[ $moduleName ] ) ) {
			throw new \InvalidArgumentException(
				esc_attr( "Service '$moduleName' not found." )
			);
		}

		return $this->modules[ $moduleName ];
	}

	/**
	 * Get all services.
	 *
	 * @return array
	 */
	public function getModules(): array {
		return $this->modules;
	}
}
