<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress
 */

namespace AnsPress\Classes;

use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress Plugin class.
 *
 * @since 5.0.0
 * @package AnsPress
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var mixed
	 */
	protected static $instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $pluginVersion;

	/**
	 * Plugin database version.
	 *
	 * @var string
	 */
	private string $dbVersion;

	/**
	 * Minimum PHP version.
	 *
	 * @var string
	 */
	private string $minPHPVersion;

	/**
	 * Minimum WordPress version.
	 *
	 * @var string
	 */
	private string $minWPVersion;

	/**
	 * Migration option key.
	 */
	const MIGRATION_OPT_KEY = 'anspress_migrations';

	/**
	 * Constructor.
	 *
	 * @param string    $pluginFile Plugin file.
	 * @param Container $container Container object.
	 */
	private function __construct(
		private string $pluginFile,
		private Container $container
	) {
	}

	/**
	 * Set protected attributes.
	 *
	 * @param string $attributeName  Attribute name.
	 * @param string $value          Value.
	 * @return void
	 */
	protected function setAttribute( string $attributeName, string $value ) {
		$this->{$attributeName} = $value;
	}

	/**
	 * Get plugin instance.
	 *
	 * @param string    $pluginFile     Plugin file.
	 * @param string    $pluginVersion  Plugin version.
	 * @param string    $dbVersion      Database version.
	 * @param string    $minPHPVersion  Minimum PHP version.
	 * @param string    $minWPVersion   Minimum WordPress version.
	 * @param Container $container   Container object.
	 * @return Plugin
	 */
	public static function make(
		string $pluginFile,
		string $pluginVersion,
		string $dbVersion,
		string $minPHPVersion,
		string $minWPVersion,
		Container $container
	) {

		if ( null === self::$instance ) {
			$instance = new self( $pluginFile, $container );

			$instance->setAttribute( 'pluginVersion', $pluginVersion );

			$instance->setAttribute( 'dbVersion', $dbVersion );

			$instance->setAttribute( 'minPHPVersion', $minPHPVersion );

			$instance->setAttribute( 'minWPVersion', $minWPVersion );

			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 * Get current PHP version, useful for mocking.
	 *
	 * @return string
	 */
	public static function getCurrentPHPVersion(): string {
		return PHP_VERSION;
	}

	/**
	 * Magic method for static call.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 * @return mixed
	 * @throws InvalidArgumentException If instance is not created.
	 */
	public static function __callStatic( string $method, array $args ): mixed {
		if ( null === self::$instance ) {
			throw new InvalidArgumentException( 'Plugin instance not created.' );
		}

		if ( str_starts_with( $method, 'get' ) ) {
			$attribute = lcfirst( substr( $method, 3 ) );

			if ( ! property_exists( self::$instance, $attribute ) ) {
				throw new InvalidArgumentException( 'Attribute not found.' );
			}

			return self::$instance->{$attribute};
		}

		throw new InvalidArgumentException( 'Method not found.' );
	}
}
