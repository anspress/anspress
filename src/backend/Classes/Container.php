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
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

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
	 * @throws Exception If invalid service.
	 */
	public function set( string $className ): void {
		if ( ! class_exists( $className ) ) {
			throw new Exception( esc_attr( 'Not a valid class.' ) );
		}

		if ( isset( $this->instances[ $className ] ) ) {
			return;
		}

		$this->instances[ $className ] = $this->build( $className );
	}

	/**
	 * Build a class with dependencies.
	 *
	 * @param string $className Class name.
	 * @return object|null
	 * @throws Exception If the target class does not exist.
	 */
	public function build( string $className ) {
		try {
			$reflector = new ReflectionClass( $className );
		} catch ( ReflectionException $e ) {
			throw new Exception(
				esc_attr( "Target class [$className] does not exist." ),
				0,
				$e // @phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			);
		}

		// If the type is not instantiable, such as an Interface or Abstract Class.
		if ( ! $reflector->isInstantiable() ) {
			throw new Exception( esc_attr( "Target [$className] is not instantiable." ) );
		}

		$constructor = $reflector->getConstructor();

		if ( null === $constructor ) {
			return new $className();
		}

		$parameters   = $constructor->getParameters();
		$dependencies = array();

		foreach ( $parameters as $parameter ) {
			$type = $parameter->getType();

			if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin() ) {
				throw new Exception( esc_attr( "Missing type hint for {$parameter->getName()}. Please provide a class type." ) );
			}

			$name = $type->getName();

			// Check if class impliments SingletonInterface.
			if ( class_exists( $name ) && is_subclass_of( $name, SingletonInterface::class ) ) {
				$dependency     = $this->get( $name );
				$dependencies[] = $dependency;
			} else {
				throw new Exception( esc_attr( "Only classes which impliments SingletonInface can be used as dependency. For {$parameter->getName()}. Please provide a valid class type." ) );
			}
		}

		return $reflector->newInstanceArgs( $dependencies );
	}

	/**
	 * Method to load singleton classes on demand.
	 *
	 * @template T of object
	 * @param class-string<T> $className Class name.
	 * @return T|null Service object or null if not found.
	 * @throws \InvalidArgumentException If the service name is not valid.
	 */
	public function get( $className ): mixed {
		if ( $this->instances[ $className ] ?? true ) {
			$this->set( $className );
		}

		if ( ! isset( $this->instances[ $className ] ) ) {
			throw new \InvalidArgumentException( esc_attr( 'Class not found.' ) );
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
