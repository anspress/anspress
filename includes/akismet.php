<?php
/**
 * Check post for spam, if spam then hold it for moderation.
 *
 * @param  integer $post_id Post id.
 */
function ap_check_spam( $post_id ) {
	// Return if akisment is not enabled.
	if ( ! class_exists( 'Akismet' ) || is_super_admin() ) {
		return;
	}

	$post = ap_get_post( $post_id );

	// Set default arguments to pass.
	$defaults = array(
		'blog'                 => home_url( '/' ),
		'user_ip'              => get_post_meta( $post->ID, 'create_ip', true ),
		'user_agent'           => $_SERVER['HTTP_USER_AGENT'],
		'referrer'             => $_SERVER['HTTP_REFERER'],
		'permalink'            => get_permalink( $post->ID ),
		'comment_type'         => 'forum-post',
		'comment_author'       => get_the_author_meta( 'nicename', $post->post_author ),
		'comment_author_email' => get_the_author_meta( 'user_email', $post->post_author ),
		'comment_author_url'   => get_the_author_meta( 'url', $post->post_author ),
		'comment_content'      => $post->post_content,
	);

	$akismet_ua = sprintf( 'WordPress/%s | Akismet/%s', $GLOBALS['wp_version'], constant( 'AKISMET_VERSION' ) );
	$akismet_ua = apply_filters( 'akismet_ua', $akismet_ua );
	$api_key    = Akismet::get_api_key();
	$host       = Akismet::API_HOST;

	if ( ! empty( $api_key ) ) {
		$host = $api_key . '.' . $host;
	}

	$http_host = $host;
	$http_args = array(
		'body'        => $defaults,
		'headers'     => array(
			'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
			'Host'         => $host,
			'User-Agent'   => $akismet_ua,
		),
		'httpversion' => '1.0',
		'timeout'     => 15,
	);

	$akismet_url = $http_akismet_url = "http://{$http_host}/1.1/comment-check";

	/**
	 * Try SSL first; if that fails, try without it and don't try it again for a while.
	 */
	$ssl = $ssl_failed = false;

	// Check if SSL requests were disabled fewer than X hours ago.
	$ssl_disabled = get_option( 'akismet_ssl_disabled' );

	if ( $ssl_disabled && $ssl_disabled < ( time() - 60 * 60 * 24 ) ) { // 24 hours
		$ssl_disabled = false;
		delete_option( 'akismet_ssl_disabled' );
	} elseif ( $ssl_disabled ) {
		do_action( 'akismet_ssl_disabled' );
	}
	if ( ! $ssl_disabled && function_exists( 'wp_http_supports' ) && ( $ssl = wp_http_supports( array( 'ssl' ) ) ) ) {
		$akismet_url = set_url_scheme( $akismet_url, 'https' );
	}

	$response = wp_remote_post( $akismet_url, $http_args );

	Akismet::log( compact( 'akismet_url', 'http_args', 'response' ) );

	if ( $ssl && is_wp_error( $response ) ) {
		// Intermittent connection problems may cause the first HTTPS
		// request to fail and subsequent HTTP requests to succeed randomly.
		// Retry the HTTPS request once before disabling SSL for a time.
		$response = wp_remote_post( $akismet_url, $http_args );

		Akismet::log( compact( 'akismet_url', 'http_args', 'response' ) );
		if ( is_wp_error( $response ) ) {
			$ssl_failed = true;
			// Try the request again without SSL.
			$response = wp_remote_post( $http_akismet_url, $http_args );
			Akismet::log( compact( 'http_akismet_url', 'http_args', 'response' ) );
		}
	}

	if ( is_wp_error( $response ) ) {
		return array( '', '' );
	}

	if ( $ssl_failed ) {
		// The request failed when using SSL but succeeded without it. Disable SSL for future requests.
		update_option( 'akismet_ssl_disabled', time() );

		do_action( 'akismet_https_disabled' );
	}

	if ( $response['body'] ) {
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'moderate',
			)
		);
	}

}
