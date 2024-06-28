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

use WP_REST_Request;
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
	 * Array to store routes configurations.
	 *
	 * @var array
	 */
	protected static array $routes = array();

	/**
	 * Array to store route names.
	 *
	 * @var array
	 */
	protected static array $routeNames = array();

	/**
	 * Stack to manage current group settings.
	 *
	 * @var array
	 */
	protected static array $groupStack = array();

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

		add_action(
			'rest_api_init',
			array( $this, 'registerRestRoutesCallback' ),
			1
		);
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
		include __DIR__ . '/../routes/rest.php';

		add_action(
			'rest_api_init',
			array( $this, 'registerRestRoutesCallback' ),
			1
		);
	}

	/**
	 * Register REST API routes callback.
	 */
	public function registerRestRoutesCallback() {
		foreach ( self::$routes as $route ) {
			register_rest_route(
				$route['namespace'],
				$route['route'],
				array(
					'methods'             => $route['method'],
					'callback'            => fn( WP_REST_Request $req ) => RestRouteHandler::run( $route['controller'], $req ),
					'permission_callback' => $route['permission_callback'],
					'args'                => $route['args'],
				)
			);
		}
	}

	/**
	 * Register WP-CLI commands.
	 */
	protected function registerWpCliCommands() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			include __DIR__ . '/../routes/cli.php';
		}
	}

	/**
	 * Add a REST route.
	 *
	 * @param string $method HTTP method.
	 * @param string $route Route path.
	 * @param mixed  $controller Controller.
	 * @param array  $options Route options.
	 */
	public static function addRoute( string $method, string $route, $controller, array $options = array() ) {
		$prefix              = $options['prefix'] ?? '';
		$name                = $options['name'] ?? null;
		$permission_callback = $options['permission_callback'] ?? '__return_true';
		$args                = $options['args'] ?? array();

		$groupSettings = end( self::$groupStack );

		if ( $groupSettings ) {
			// Handle prefix.
			$groupPrefix = $groupSettings['prefix'] ?? '';
			$prefix      = rtrim( $groupPrefix, '/' ) . '/' . ltrim( $prefix, '/' );

			// Handle controller.
			if ( is_string( $controller ) && isset( $groupSettings['controller'] ) ) {
				$controller = array( $groupSettings['controller'], $controller );
			}

			// Handle name.
			if ( isset( $groupSettings['name'] ) ) {
				$name = $groupSettings['name'] . '.' . $name;
			}
		}

		// Construct full route.
		$fullRoute = '/' . trim( $prefix, '/' ) . '/' . ltrim( $route, '/' );
		$fullRoute = trim( $fullRoute, '/' ); // Ensure no trailing slash.

		self::$routes[] = array(
			'namespace'           => $groupSettings['namespace'] ?? '',
			'route'               => $fullRoute,
			'method'              => $method,
			'controller'          => $controller,
			'permission_callback' => $permission_callback,
			'args'                => $args,
			'name'                => $name,
		);

		if ( $name ) {
			self::$routeNames[ $name ] = $fullRoute;
		}
	}



	/**
	 * Add a route group with a common prefix.
	 *
	 * @param array    $options Group options.
	 * @param callable $callback Callback function.
	 */
	public static function group( array $options, callable $callback ): void {
		$currentGroup = end( self::$groupStack );

		if ( $currentGroup ) {
			// Handle namespace.
			if ( isset( $currentGroup['namespace'] ) ) {
				$currentNamespace     = trim( $currentGroup['namespace'], '/' );
				$newNamespace         = ! empty( $options['namespace'] ) ? '/' . trim( $options['namespace'], '/' ) : '';
				$options['namespace'] = $currentNamespace . $newNamespace;
			}

			// Handle name.
			if ( isset( $currentGroup['name'] ) && isset( $options['name'] ) ) {
				$options['name'] = $currentGroup['name'] . '.' . $options['name'];
			}

			// Handle prefix.
			if ( isset( $currentGroup['prefix'] ) ) {
				$currentPrefix     = trim( $currentGroup['prefix'], '/' );
				$newPrefix         = isset( $options['prefix'] ) ? '/' . trim( $options['prefix'], '/' ) : '';
				$options['prefix'] = $currentPrefix . $newPrefix;
			}
		}

		self::$groupStack[] = $options;
		call_user_func( $callback );
		array_pop( self::$groupStack );
	}


	/**
	 * Add a prefix to a route.
	 *
	 * @param string   $prefix Prefix.
	 * @param callable $callback Callback function.
	 */
	public static function prefix( string $prefix, callable $callback ): void {
		self::group( array( 'prefix' => $prefix ), $callback );
	}

	/**
	 * Specify a controller for routes within a group.
	 *
	 * @param string   $controller Controller.
	 * @param callable $callback Callback function.
	 */
	public static function controller( string $controller, callable $callback ): void {
		self::group( array( 'controller' => $controller ), $callback );
	}

	/**
	 * Add a GET route.
	 *
	 * @param string $route Route path.
	 * @param mixed  $controller Controller.
	 * @param array  $options Route options.
	 */
	public static function get( string $route, $controller, array $options = array() ) {
		self::addRoute( 'GET', $route, $controller, $options );
	}

	/**
	 * Add a POST route.
	 *
	 * @param string $route Route path.
	 * @param mixed  $controller Controller.
	 * @param array  $options Route options.
	 */
	public static function post( string $route, $controller, array $options = array() ) {
		self::addRoute( 'POST', $route, $controller, $options );
	}

	/**
	 * Add a DELETE route.
	 *
	 * @param string $route Route path.
	 * @param mixed  $controller Controller.
	 * @param array  $options Route options.
	 */
	public static function delete( string $route, $controller, array $options = array() ) {
		self::addRoute( 'DELETE', $route, $controller, $options );
	}

	/**
	 * Name a route.
	 *
	 * @param string $name Route name.
	 * @param string $route Route path.
	 */
	public static function name( string $name, string $route ) {
		self::$routeNames[ $name ] = $route;
	}

	/**
	 * Get a route URL by its name.
	 *
	 * @param string $name Route name.
	 * @param array  $args Route arguments.
	 * @return string
	 */
	public static function route( string $name, array $args = array() ) {
		$route = self::$routeNames[ $name ] ?? null;
		if ( ! $route ) {
			return '';
		}

		// Retrieve namespace and prefix from the route settings.
		$namespace = '';
		$prefix    = '';

		foreach ( self::$routes as $r ) {
			if ( $r['route'] === $route ) {
				$namespace = $r['namespace'];
				$prefix    = $r['prefix'] ?? '';
				break;
			}
		}

		$fullRoute = $namespace . ( ! empty( $namespace ) ? '/' : '' ) . ( ! empty( $prefix ) ? '/' : '' . $prefix ) . $route;

		// Replace named regex groups in the route patterns with the provided arguments.
		foreach ( $args as $key => $value ) {
			$fullRoute = preg_replace( '/\(\?P<' . $key . '>[^\)]+\)/', $value, $fullRoute );
		}

		return ltrim( $fullRoute, '/' ); // Remove leading slash.
	}
}
