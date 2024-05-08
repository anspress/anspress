<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Core\Classes;

use Exception;
use WP_Error;

// @codeCoverageIgnoreStart
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// @codeCoverageIgnoreEnd

/**
 * AnsPress Plugin class.
 *
 * @since 5.0.0
 * @package AnsPress\Core
 */
class Plugin {
	/**
	 * Constructor.
	 *
	 * @param string    $minPhpVersion Minimum PHP version.
	 * @param string    $minWpVersion Minimum WordPress version.
	 * @param string    $pluginVersion Plugin version.
	 * @param string    $dbVersion Database version.
	 * @param Container $container Container object.
	 */
	private function __construct(
		private string $minPhpVersion,
		private string $minWpVersion,
		private string $pluginVersion,
		private string $dbVersion,
		private Container $container
	) {
	}

	/**
	 * Get minimum PHP version.
	 *
	 * @return string
	 */
	public function getMinPHPVersion(): string {
		return $this->minPhpVersion;
	}

	/**
	 * Get minimum WordPress version.
	 *
	 * @return string
	 */
	public function getMinWPVersion(): string {
		return $this->minWpVersion;
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function getPluginVersion(): string {
		return $this->pluginVersion;
	}

	/**
	 * Get database version.
	 *
	 * @return string
	 */
	public function getDbVersion(): string {
		return $this->dbVersion;
	}

	/**
	 * Get current WP version.
	 *
	 * @return string
	 */
	public function getCurrentWPVersion(): string {
		return get_bloginfo( 'version' );
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
	 * @param BaseService $service Service object.
	 * @return void
	 */
	public function registerService( BaseService $service ) {
		$this->container->set( $service );
	}

	/**
	 * Check for minimum requirements.
	 */
	public function getRequirmentErrors() {
		$errors = array();

		if ( version_compare( $this->getCurrentPHPVersion(), $this->getMinPHPVersion(), '<' ) ) {
			$errors[] = sprintf(
				/* translators: 1: PHP version, 2: Required PHP version */
				__( 'AnsPress requires PHP version %1$s or higher. You are running version %2$s.', 'anspress-question-answer' ),
				self::getMinPHPVersion(),
				self::getCurrentPHPVersion()
			);
		}

		if ( version_compare( $this->getCurrentWPVersion(), $this->getMinWPVersion(), '<' ) ) {
			$errors[] = sprintf(
				/* translators: 1: WordPress version, 2: Required WordPress version */
				__( 'AnsPress requires WordPress version %1$s or higher. You are running version %2$s.', 'anspress-question-answer' ),
				self::getMinWPVersion(),
				self::getCurrentWPVersion()
			);
		}

		return $errors;
	}

	/**
	 * Admin notice for minimum requirements.
	 */
	public static function adminNoticesWhenRequirementsNotMet() {
		$errors = self::getRequirmentErrors();

		if ( ! empty( $errors ) ) {
			include FileSystem::getPathTo( 'src/Views/Admin/Notices/minimum-requirments-not-met.php' );
		}
	}
}
