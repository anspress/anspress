<?php
/**
 * Abstract controller.
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
 * Abstract controller.
 *
 * @since 5.0.0
 */
abstract class AbstractController {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize controller.
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Get request data.
	 *
	 * @return array
	 */
	public function getRequestData(): array {
		return $_REQUEST; // @codingStandardsIgnoreLine
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getRequest( string $key, mixed $def = null ): mixed {
		return $_REQUEST[ $key ] ?? $def; // @codingStandardsIgnoreLine
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getPost( string $key, mixed $def = null ): mixed {
		return $_POST[ $key ] ?? $def; // @codingStandardsIgnoreLine
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getQuery( string $key, mixed $def = null ): mixed {
		return $_GET[ $key ] ?? $def; // @codingStandardsIgnoreLine
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getParam( string $key, mixed $def = null ): mixed {
		return $_REQUEST[ $key ] ?? $_POST[ $key ] ?? $_GET[ $key ] ?? $def; // @codingStandardsIgnoreLine
	}

	/**
	 * Validate nonce.
	 *
	 * @param string $action Nonce action.
	 * @param string $key Nonce key.
	 * @return void
	 *
	 * @throws HTTPException If nonce is invalid.
	 */
	public function validateNonce( string $action, string $key = '_wpnonce' ): void {
		if ( ! wp_verify_nonce( $this->getRequest( $key ), $action ) ) {
			throw new HTTPException( 403, 'Invalid nonce' );
		}
	}

	/**
	 * Check permission.
	 *
	 * @param string $ability Permission ability.
	 * @param array  $context Permission context.
	 * @param string $message Error message.
	 * @return void
	 *
	 * @throws HTTPException If permission is denied.
	 */
	public function checkPermission( string $ability, array $context = array(), string $message = 'Forbidden' ): void {
		if ( ! Auth::currentUserCan( $ability, $context ) ) {
			throw new HTTPException( 403, esc_attr( $message ) );
		}
	}

	/**
	 * Validate request data.
	 *
	 * @param array $rules Validation rules.
	 * @param array $customMessages Custom messages.
	 * @param array $customAttributes Custom attributes.
	 * @return array
	 */
	public function validate( array $rules, array $customMessages = array(), array $customAttributes = array() ): array {
		$validator = new Validator( $this->getRequestData(), $rules, $customMessages, $customAttributes );
		return $validator->validated();
	}

	/**
	 * Send JSON response usig WP_REST_Response.
	 *
	 * @param mixed $data Response data.
	 * @param int   $status Response status.
	 * @return WP_REST_Response
	 */
	public function response( mixed $data, int $status = 200 ): WP_REST_Response {
		$response = new WP_REST_Response( $data );
		$response->set_status( $status );
		$response->header( 'Content-Type', 'application/json' );
		return $response;
	}

	/**
	 * Return unauthorized response.
	 *
	 * @return WP_REST_Response
	 */
	public function unauthorized(): WP_REST_Response {
		return $this->response( array( 'message' => 'Unauthorized' ), 401 );
	}

	/**
	 * Return forbidden response.
	 *
	 * @return WP_REST_Response
	 */
	public function forbidden(): WP_REST_Response {
		return $this->response( array( 'message' => 'Forbidden' ), 403 );
	}

	/**
	 * Return not found response.
	 *
	 * @return WP_REST_Response
	 */
	public function notFound(): WP_REST_Response {
		return $this->response( array( 'message' => 'Not found' ), 404 );
	}

	/**
	 * Return bad request response.
	 *
	 * @return WP_REST_Response
	 */
	public function badRequest(): WP_REST_Response {
		return $this->response( array( 'message' => 'Bad request' ), 400 );
	}
}
