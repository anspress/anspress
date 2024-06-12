<?php
/**
 * Rest route handler class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\GeneralException;
use AnsPress\Exceptions\HTTPException;
use AnsPress\Exceptions\ValidationException;
use WP_REST_Request;
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
	 * Request data.
	 *
	 * @var WP_REST_Request
	 */
	private WP_REST_Request $request;

	/**
	 * Controller class.
	 *
	 * @var string
	 */
	private string $controller;

	/**
	 * Method name.
	 *
	 * @var string
	 */
	private string $method;

	/**
	 * Handler.
	 *
	 * @param array           $controllerMethod Controller method.
	 * @param WP_REST_Request $request          Request data.
	 * @return mixed
	 * @throws GeneralException If controller class not found.
	 */
	public function __construct( array $controllerMethod, WP_REST_Request $request ) {
		$this->request = $request;

		list($controller, $method) = $controllerMethod;

		$this->controller = $controller;
		$this->method     = $method;

		if ( ! class_exists( $controller ) ) {
			throw new GeneralException( 'Controller class not found.' );
		}

		if ( ! is_subclass_of( $controller, AbstractController::class ) ) {
			throw new GeneralException( 'Controller class must be subclass of AbstractController.' );
		}

		if ( ! method_exists( $controller, $method ) ) {
			throw new GeneralException( 'Method not found in controller.' );
		}
	}

	/**
	 * Handle.
	 *
	 * @return WP_REST_Response
	 */
	public function handle(): WP_REST_Response {
		try {
			$controller = Plugin::get( $this->controller );
			$controller->setRequest( $this->request );

			return $controller->{$this->method}();
		} catch ( HTTPException $e ) {
			$response = new WP_REST_Response( array( 'message' => $e->getMessage() ), $e->getStatusCode() );
		} catch ( ValidationException $e ) {
			$response = new WP_REST_Response(
				array(
					'message' => $e->getMessage(),
					'errors'  => $e->getErrors(),
				),
				400
			);
		} catch ( \Exception $e ) {
			$response = new WP_REST_Response( array( 'message' => $e->getMessage() ), 500 );
		}

		return rest_ensure_response( $response );
	}
}
