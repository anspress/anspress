<?php
/**
 * Api helper.
 *
 * @todo This file require doc comment.
 * @since unknown
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress REST endpoint class.
 *
 * @since unknown
 */
class AnsPress_API {
	/**
	 * Register REST route.
	 */
	public static function register() {
		register_rest_route(
			'anspress',
			'/user/avatar',
			array(
				'methods'  => 'GET',
				'callback' => array( 'AnsPress_API', 'avatar' ),
			)
		);
	}

	/**
	 * Callback for route `/anspress/user/avatar/`.
	 *
	 * @param WP_REST_Request $request Request data.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function avatar( $request ) {
		$args = $request->get_query_params();
		if ( isset( $args['id'] ) ) {
			$size   = isset( $args['size'] ) ? (int) $args['size'] : 90;
			$avatar = get_avatar_url( (int) $args['id'], $size );
			return new WP_REST_Response( $avatar, 200 );
		}
		return new WP_Error( 'wrongData', __( 'Wrong data supplied', 'anspress-question-answer' ) );
	}
}
