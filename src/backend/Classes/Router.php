<?php
/**
 * Router class.
 *
 * This class is responsible for registering all routes, WP-CLI commands, and REST API endpoints.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Classes\Container;

/**
 * Router class.
 */
class Router {
	/**
	 * The container instance.
	 *
	 * @var AnsPress\Core\Container
	 */
	protected Container $container;

	/**
	 * Router constructor.
	 *
	 * @param Container $container The container instance.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Register all routes, WP-CLI commands, and REST API endpoints.
	 */
	public function register() {
		$this->registerWebRoutes();
		$this->registerRestRoutes();
		$this->registerWpCliCommands();
	}

	/**
	 * Register web routes.
	 */
	protected function registerWebRoutes() {
		include __DIR__ . '/../routes/web.php';
	}

	/**
	 * Register REST API routes.
	 */
	protected function registerRestRoutes() {
		add_action(
			'rest_api_init',
			function () {
				include __DIR__ . '/../routes/rest.php';
			}
		);
	}

	/**
	 * Register WP-CLI commands.
	 */
	protected function registerWpCliCommands() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			include __DIR__ . '/../routes/cli.php';
		}
	}
}
