<?php
/**
 * Response class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use WP_REST_Response;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Response class for WordPress using WP_REST_Response.
 *
 * @since 5.0.0
 */
class Response {

	/**
	 * Create a response.
	 *
	 * @param mixed $data The response data.
	 * @param int   $status HTTP status code.
	 * @param array $headers HTTP headers.
	 * @return WP_REST_Response
	 */
	public static function create( $data, int $status = 200, array $headers = array() ): WP_REST_Response {
		$response = new WP_REST_Response( $data, $status );

		foreach ( $headers as $key => $value ) {
			$response->header( $key, $value );
		}

		return $response;
	}

	/**
	 * Create a JSON response.
	 *
	 * @param mixed $data The response data.
	 * @param int   $status HTTP status code.
	 * @param array $headers HTTP headers.
	 * @return WP_REST_Response
	 */
	public static function json( $data, int $status = 200, array $headers = array() ): WP_REST_Response {
		$headers = array_merge( $headers, array( 'Content-Type' => 'application/json' ) );
		return self::create( $data, $status, $headers );
	}

	/**
	 * Create an error response.
	 *
	 * @param string $message The error message.
	 * @param int    $status HTTP status code.
	 * @param string $code Optional. Error code. Default empty.
	 * @return WP_REST_Response
	 */
	public static function error( string $message, int $status = 400, string $code = '' ): WP_REST_Response {
		$error = new WP_Error( $code, $message, array( 'status' => $status ) );
		return self::create( $error, $status );
	}

	/**
	 * Create a validation error response.
	 *
	 * @param Validator|array $validator Array of validation errors.
	 * @param string          $code Optional. Error code. Default 'rest_invalid_param'.
	 * @return WP_REST_Response
	 */
	public static function validationError( Validator|array $validator, string $code = 'rest_invalid_param' ): WP_REST_Response {

		$errors = $validator instanceof Validator ? $validator->errors() : $validator;

		$error = new WP_Error(
			$code,
			'Validation Error',
			array(
				'status' => 422,
				'errors' => $errors,
			)
		);
		return self::create( $error, 422 );
	}

	/**
	 * Create a success response.
	 *
	 * @param mixed $data The response data.
	 * @return WP_REST_Response
	 */
	public static function success( $data ): WP_REST_Response {
		return self::json( $data, 200 );
	}
}
