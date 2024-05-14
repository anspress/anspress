<?php
/**
 * Config service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Config;

use AnsPress\Classes\AbstractService;
use InvalidArgumentException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber service.
 *
 * @since 5.0.0
 */
class ConfigService extends AbstractService {
	/**
	 * Option name.
	 */
	public const OPTION_NAME = 'anspress_opt';

	/**
	 * Options cache.
	 *
	 * @var null|array
	 */
	private $optionsCache = null;

	/**
	 * Registered defaults.
	 *
	 * @var array
	 */
	private $registeredDefaults = array();

	/**
	 * Get option value.
	 *
	 * @param string $name Name of the option.
	 * @return mixed
	 */
	public function __get( string $name ) {
		return $this->get( $name );
	}

	/**
	 * Set option value.
	 *
	 * @param string $name Name of the option.
	 * @param mixed  $defaultVal Default value if missing.
	 * @return mixed
	 */
	public function get( string $name, $defaultVal = null ): mixed {
		if ( null === $this->optionsCache ) {
			$this->optionsCache = get_option( self::OPTION_NAME, array() );
		}

		if ( null === $defaultVal ) {
			$defaultVal = $this->getDefault( $name );
		}

		if ( ! isset( $this->optionsCache[ $name ] ) ) {
			return $defaultVal;
		}

		$value          = $this->optionsCache[ $name ];
		$registeredType = $this->registeredDefaults[ $name ]['type'] ?? null;

		// Force type checking.
		if ( null !== $registeredType && gettype( $value ) !== $registeredType ) {
			settype( $value, $registeredType );
		}

		return null === $value ? $defaultVal : $value;
	}

	/**
	 * Check if option is registered.
	 *
	 * @param string $name Options key.
	 * @return bool
	 */
	public function isRegistered( string $name ): bool {
		return isset( $this->registeredDefaults[ $name ] );
	}

	/**
	 * Set option value.
	 *
	 * @param string $name Option key.
	 * @param mixed  $value Option value.
	 * @return void
	 * @throws InvalidArgumentException If option is not registered.
	 */
	public function set( string $name, $value ): void {
		// If option is not registered then throw an error.
		if ( ! $this->isRegistered( $name ) ) {
			throw new InvalidArgumentException( esc_attr( 'Option not registered.' ) );
		}

		$registeredType = $this->registeredDefaults[ $name ]['type'] ?? null;

		// Type casting based on registered type.
		if ( $registeredType ) {
			settype( $value, $registeredType );
		}

		$this->optionsCache[ $name ] = $value;
		update_option( self::OPTION_NAME, $this->optionsCache );
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	public function clearCache(): void {
		$this->optionsCache = null;
	}

	/**
	 * Register options.
	 *
	 * @param array $options Options.
	 * @return void
	 * @throws InvalidArgumentException If value or type is missing.
	 */
	public function registerDefaults( array $options ): void {
		$builtInTypes = array( 'boolean', 'float', 'integer', 'string', 'double' );

		foreach ( $options as $name => $opt ) {
			// Check if value and type is set if not throw an error.
			if ( ! isset( $opt['type'] ) || ! isset( $opt['value'] ) ) {
				throw new InvalidArgumentException( esc_attr( $name . ': When registering options [value] and [type] must be present.' ) );
			}

			// Check if type is valid.
			if ( ! in_array( $opt['type'], $builtInTypes, true ) ) {
				throw new InvalidArgumentException( esc_attr( $name . ': Invalid type.' ) );
			}

			$this->registeredDefaults[ $name ] = array(
				'value' => $opt['value'],
				'type'  => $opt['type'],
			);
		}
	}

	/**
	 * Get default value.
	 *
	 * @param string $name Name of the option.
	 * @return bool
	 * @throws InvalidArgumentException If option is not registered.
	 */
	public function getDefault( string $name ) {
		if ( ! $this->isRegistered( $name ) ) {
			throw new InvalidArgumentException( esc_attr( 'Option not registered.' ) );
		}

		$defaultValue = $this->registeredDefaults[ $name ]['value'];
		$type         = $this->registeredDefaults[ $name ]['type'];

		settype( $defaultValue, $type );

		return $defaultValue;
	}
}
