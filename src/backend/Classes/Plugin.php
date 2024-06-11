<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Config\ConfigService;
use AnsPress\Modules\Subscriber\SubscriberModel;
use AnsPress\Modules\Subscriber\SubscriberSchema;
use InvalidArgumentException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress Plugin class.
 *
 * @since 5.0.0
 * @package AnsPress
 * @method static string getPluginVersion() Gets the plugin version.
 * @method static int getDbVersion() Gets the database version.
 * @method static string getMinPHPVersion() Gets the minimum PHP version.
 * @method static string getMinWPVersion() Gets the minimum WordPress version.
 * @method static string getPluginFile() Gets the plugin file.
 */
class Plugin {

	/**
	 * Modules.
	 *
	 * @var array
	 */
	private array $modules = array(
		\AnsPress\Modules\Core\CoreModule::class,
		\AnsPress\Modules\Category\CategoryModule::class,
		\AnsPress\Modules\Tag\TagModule::class,
		\AnsPress\Modules\Reputation\ReputationModule::class,
		\AnsPress\Modules\Profile\ProfileModule::class,
	);

	/**
	 * Plugin instance.
	 *
	 * @var null|self
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
	 * Container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Model schema.
	 *
	 * @var array
	 */
	private array $modelSchema = array(
		SubscriberModel::class => SubscriberSchema::class,
	);

	/**
	 * Database version option key.
	 *
	 * @var string
	 */
	const DB_VERSION_OPT_KEY = 'anspress_db_version';

	/**
	 * Constructor.
	 *
	 * @param string    $pluginFile Plugin file.
	 * @param Container $container Container object.
	 */
	private function __construct(
		private string $pluginFile,
		Container $container
	) {
		$this->container = $container;
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
	 * @param int       $dbVersion      Database version.
	 * @param string    $minPHPVersion  Minimum PHP version.
	 * @param string    $minWPVersion   Minimum WordPress version.
	 * @param Container $container   Container object.
	 * @return Plugin
	 */
	public static function make(
		string $pluginFile,
		string $pluginVersion,
		int $dbVersion,
		string $minPHPVersion,
		string $minWPVersion,
		Container $container
	) {
		$instance = new self( $pluginFile, $container );

		$instance->setAttribute( 'pluginVersion', $pluginVersion );

		$instance->setAttribute( 'dbVersion', $dbVersion );

		$instance->setAttribute( 'minPHPVersion', $minPHPVersion );

		$instance->setAttribute( 'minWPVersion', $minWPVersion );

		self::$instance = $instance;

		return self::$instance;
	}

	/**
	 * Register modules.
	 *
	 * @return void
	 * @throws GeneralException If modules already registered.
	 */
	public function registerModules(): void {
		static $registered = false;

		if ( $registered ) {
			throw new GeneralException( 'Modules already registered.' );
		}

		foreach ( $this->modules as $module ) {
			$this->container->set(
				$module,
				function () use ( $module ) {
					return new $module( $this->container );
				}
			);
		}

		foreach ( $this->modules as $module ) {
			$this->container->get( $module )->register_hooks();
		}
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
	 * Get installed databse version.
	 *
	 * @return int
	 */
	public static function getInstalledDbVersion(): int {
		return self::get( ConfigService::class )
			->get( 'migration.installed_version' );
	}

	/**
	 * Updated installed database version.
	 *
	 * @return void
	 */
	public static function updateInstalledDbVersion() {
		self::get( ConfigService::class )
			->set( 'migration.installed_version', self::$instance->dbVersion );
	}

	/**
	 * Get path relative to plugin directory.
	 *
	 * @param string $path Path.
	 * @return string Full path.
	 */
	public static function getPathTo( string $path ): string {
		return plugin_dir_path( self::$instance->pluginFile ) . $path;
	}

	/**
	 * Get URL relative to plugin directory.
	 *
	 * @param string $path Path.
	 * @return string Full URL.
	 */
	public static function getUrlTo( string $path ): string {
		return plugin_dir_url( self::$instance->pluginFile ) . $path;
	}

	/**
	 * Get plugin version.
	 *
	 * @return Container
	 */
	public static function getContainer(): Container {
		return self::$instance->container;
	}

	/**
	 * Method to load singleton classes on demand.
	 *
	 * @template T of object
	 * @param class-string<T> $className Class name.
	 * @return T|null Service object or null if not found.
	 * @throws \InvalidArgumentException If the service name is not valid.
	 */
	public static function get( $className ) {
		return self::$instance->container->get( $className );
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
		if ( str_starts_with( $method, 'get' ) ) {
			$attribute = lcfirst( substr( $method, 3 ) );

			if ( ! property_exists( self::$instance, $attribute ) ) {
				throw new InvalidArgumentException( 'Attribute not found.' );
			}

			return self::$instance->{$attribute};
		}

		throw new InvalidArgumentException( 'Method not found.' );
	}

	/**
	 * Get model schema.
	 *
	 * @param string $model Model class name.
	 * @return string Model schema class name.
	 * @throws InvalidArgumentException  If model schema not found.
	 */
	public static function getModelSchema( string $model ): AbstractSchema {
		if ( ! isset( self::$instance->modelSchema[ $model ] ) ) {
			throw new InvalidArgumentException( 'Model schema not found.' );
		}

		return self::$instance->get( self::$instance->modelSchema[ $model ] );
	}
}
