<?php
/**
 * Rest route handler class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\HTTPException;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This will call rest callback function and will handle exceptions.
 *
 * @since 5.0.0
 */
class RestRouteHandler {


	/**
	 * Handle rest route.
	 *
	 * @param string $controller Controller class name.
	 * @param string $method     Method name.
	 * @return WP_REST_Response
	 */
	public static function handle( $controller, $method ): WP_REST_Response {
		try {
			$response = Plugin::get( $controller )->$method();
		} catch ( HTTPException $e ) {
			$response = new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getStatusCode() );
		} catch ( \Exception $e ) {
			$response = new WP_REST_Response( array( 'message' => $e->getMessage() ), 500 );
		}

		return rest_ensure_response( $response );
	}
}
