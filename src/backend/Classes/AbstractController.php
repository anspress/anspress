<?php
/**
 * Abstract controller.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\HTTPException;
use WP_REST_Request;
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
	 * Request data.
	 *
	 * @var WP_REST_Request
	 */
	protected WP_REST_Request $request;

	/**
	 * Messages.
	 *
	 * @var array
	 */
	protected array $messages = array();

	/**
	 * Events.
	 *
	 * @var array
	 */
	protected array $events = array();

	/**
	 * Set data.
	 *
	 * @var array
	 */
	protected array $setData = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Set request data.
	 *
	 * @param WP_REST_Request $req Request data.
	 * @return void
	 */
	public function setRequest( WP_REST_Request $req ): void {
		$this->request = $req;
	}

	/**
	 * Get all params.
	 *
	 * @return array
	 */
	public function getParams(): array {
		return $this->request->get_params();
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getQuery( string $key, mixed $def = null ): mixed {
		return $this->request->get_query_params()[ $key ] ?? $def;
	}

	/**
	 * Get request data.
	 *
	 * @param string $key Request key.
	 * @param mixed  $def Default value.
	 * @return mixed
	 */
	public function getParam( string $key, mixed $def = null ): mixed {
		return $this->request->get_param( $key ) ?? $def;
	}

	/**
	 * Get nonce from cookie.
	 *
	 * @return string
	 */
	protected function getNonceFromCookie(): string {
		return sanitize_text_field( wp_unslash( $_COOKIE['anspress_nonce'] ?? '' ) );
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
	public function validateNonce( string $action = 'anspress_default_nonce', string $key = '_wpnonce' ): void {
		$nonce = sanitize_text_field( $this->getParam( $key ) );

		// If default nonce then get from cookie.
		if ( 'anspress_default_nonce' === $action ) {
			$nonce = $this->getNonceFromCookie();
		}

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			throw new HTTPException( 400, 'Invalid nonce' );
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
		$validator = new Validator( $this->getParams(), $rules, $customMessages, $customAttributes );
		return $validator->validated();
	}

	/**
	 * Send JSON response usig WP_REST_Response.
	 *
	 * @param mixed $data Response data.
	 * @param int   $status Response status.
	 * @return WP_REST_Response
	 */
	public function response( mixed $data = array(), int $status = 200 ): WP_REST_Response {
		// If messages are set then add to response.
		if ( ! empty( $this->messages ) ) {
			$data['messages'] = ! empty( $data['messages'] ) ? array_merge( $data['messages'], $this->messages ) : $this->messages;
		}

		// If events are set then add to response.
		if ( ! empty( $this->events ) ) {
			$data['triggerEvents'] = ! empty( $data['triggerEvents'] ) ? array_merge( $data['triggerEvents'], $this->events ) : $this->events;
		}

		// If set data is set then add to response.
		if ( ! empty( $this->setData ) ) {
			$data['setData'] = ! empty( $data['setData'] ) ? array_merge( $data['setData'], $this->setData ) : $this->setData;
		}

		$response = new WP_REST_Response( array( 'anspress' => $data ) );
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
	 * @param string $message Error message.
	 *
	 * @throws HTTPException If not found.
	 */
	public function notFound( string $message = '' ): never {
		$message = $message ? $message : __( 'Not found', 'anspress-question-answer' );
		throw new HTTPException( 404, esc_html( $message ) );
	}

	/**
	 * Return bad request response.
	 *
	 * @return WP_REST_Response
	 */
	public function badRequest(): WP_REST_Response {
		return $this->response( array( 'message' => 'Bad request' ), 400 );
	}

	/**
	 * Return server error response.
	 *
	 * @param string $message Error message.
	 *
	 * @return WP_REST_Response
	 */
	public function serverError( string $message = '' ): WP_REST_Response {
		$message = $message ? $message : __( 'Internal error', 'anspress-question-answer' );
		return $this->response( array( 'message' => $message ), 500 );
	}

	/**
	 * Add message.
	 *
	 * @param string $type Message type.
	 * @param string $message Message.
	 * @return void
	 */
	public function addMessage( string $type, string $message ): void {
		$this->messages[] = array(
			'type'    => $type,
			'message' => $message,
		);
	}

	/**
	 * Add event.
	 *
	 * @param string $name Event name.
	 * @param array  $data Event data.
	 * @return void
	 */
	public function addEvent( string $name, array $data ): void {
		$this->events[ $name ] = $data;
	}

	/**
	 * Set data.
	 *
	 * @param string $key Data key.
	 * @param mixed  $data Data.
	 * @return void
	 */
	public function setData( string $key, mixed $data ): void {
		$this->setData[ $key ] = $data;
	}
}
