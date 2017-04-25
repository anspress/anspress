<?php

class AnsPress_UnitTestCase extends WP_UnitTestCase {
	public static function wpSetUpBeforeClass() {
		//AP_Activate::get_instance();
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array(AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE);
		foreach ( $cookies as $c )
			unset( $_COOKIE[ $c ] );
	}

	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	protected function _handleAjax($action) {
		// Start output buffering
		ini_set( 'implicit_flush', false );
		ob_start();
		// Build the request
		$_POST['action'] = $action;
		$_REQUEST = $_POST;
		// Call the hooks
		do_action( 'wp_ajax_' . $_REQUEST['action'] );
		// Save the output
		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) )
			$this->_last_response = $buffer;
		return $buffer;
	}
}