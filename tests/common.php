<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Common extends TestCase {

	/**
	 * Switches between user roles.
	 *
	 * E.g. administrator, editor, author, contributor, subscriber.
	 *
	 * @param string $role The role to set.
	 */
	public static function setRole( $role ) {
		$post    = $_POST;
		$user_id = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	/**
	 * Clears login cookies, unsets the current user.
	 */
	public static function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] );
		}
	}
}
