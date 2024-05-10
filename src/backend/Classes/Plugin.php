<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ServiceInterface;

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
	 * Plugin database verison.
	 *
	 * @change 5.0.0 Last updated at 2024-05-08
	 */
	const PLUGIN_DB_VERSION = '38';

	/**
	 * Minimum PHP version.
	 */
	const MIN_PHP_VERSION = '8.1';

	/**
	 * Minimum WordPress version.
	 */
	const MIN_WP_VERSION = '5.8';

	/**
	 * Constructor.
	 *
	 * @param string    $pluginFile Plugin file.
	 * @param Container $container Container object.
	 */
	public function __construct(
		private string $pluginFile,
		private Container $container
	) {
	}

	/**
	 * Get minimum PHP version.
	 *
	 * @return string
	 */
	public function getMinPHPVersion(): string {
		return self::MIN_PHP_VERSION;
	}

	/**
	 * Get minimum WordPress version.
	 *
	 * @return string
	 */
	public function getMinWPVersion(): string {
		return self::MIN_WP_VERSION;
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function getPluginVersion(): string {
		return ANSPRESS_PLUGIN_VERSION;
	}

	/**
	 * Get database version.
	 *
	 * @return string
	 */
	public function getDbVersion(): string {
		return ANSPRESS_DB_VERSION;
	}

	/**
	 * Get current PHP version.
	 *
	 * @return string
	 */
	public function getCurrentPHPVersion(): string {
		return PHP_VERSION;
	}

	/**
	 * Get container object.
	 *
	 * @return Container
	 */
	public function getContainer(): Container {
		return $this->container;
	}

	/**
	 * Get service object.
	 *
	 * @param class-string $serviceClass Service class name.
	 */
	public function getService( string $serviceClass ) {
		return $this->container->get( $serviceClass );
	}

	/**
	 * Register services.
	 *
	 * @param class-string[] $services Services.
	 * @return void
	 */
	public function registerModules( array $services ) {
		foreach ( $services as $service ) {
			$this->container->set( $service );
		}
	}

	/**
	 * Get plugin file.
	 *
	 * @return string
	 */
	public function getPluginFile() {
		return $this->pluginFile;
	}

	/**
	 * Get services.
	 *
	 * @return array
	 */
	public function getModules(): array {
		return $this->container->getModules();
	}
}
