<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\SingletonInterface;

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
	 * Array of singleton.
	 *
	 * @var SingletonInterface[]
	 */
	private $instances = array();

	/**
	 * Set service object.
	 *
	 * @param string $className Class name.
	 * @return void
	 *
	 * @throws \Exception If invalid service.
	 */
	public function set( string $className ): void {
		if ( ! class_exists( $className ) ) {
			throw new \Exception( esc_attr( 'Not a valid class.' ) );
		}

		if ( isset( $this->instances[ $className ] ) ) {
			return;
		}

		$reflectionClass = new \ReflectionClass( $className );
		$constructor     = $reflectionClass->getConstructor();

		// Check if constructor exists and if its first parameter is of type ServiceInterface.
		if ( $constructor && $constructor->getParameters() && $constructor->getParameters()[0]->getType() && $constructor->getParameters()[0]->getType()->getName() === 'AnsPress\Interfaces\ServiceInterface' ) {
			// Get the requested service instance.
			$requestedService = $this->get( 'AnsPress\Interfaces\ServiceInterface' );

			// Instantiate the class with the injected service.
			$instance = $reflectionClass->newInstanceArgs( array( $requestedService ) );
		} else {
			// If the constructor does not require ServiceInterface, instantiate the class without injection.
			$instance = new $className();
		}

		if ( ! $instance instanceof SingletonInterface ) {
			throw new \Exception( esc_attr( 'Invalid class, does not implement SingletonInterface.' ) );
		}

		$this->instances[ $className ] = $instance;
	}

	/**
	 * Method to load singleton classes on demand.
	 *
	 * @param class-string $className Class name.
	 * @return SingletonInterface|null Service object or null if not found.
	 * @throws \InvalidArgumentException If the service name is not valid.
	 */
	public function get( string $className ): ?SingletonInterface {
		if ( ! isset( $this->instances[ $className ] ) ) {
			$this->set( $className );
		}

		return $this->instances[ $className ];
	}

	/**
	 * Get all registered singletons.
	 *
	 * @return array
	 */
	public function getAll(): array {
		return $this->instances;
	}
}
