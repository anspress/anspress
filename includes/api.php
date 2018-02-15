<?php

class AnsPress_API {
	public static function register() {
		register_rest_route(
			'anspress', '/user/avatar', array(
				'methods'  => 'GET',
				'callback' => [ 'AnsPress_API', 'avatar' ],
			)
		);
	}

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
