<?php
class Ap_AjaxTest extends WP_Ajax_UnitTestCase {
  protected $_last_response;

	public function triggerAjaxCapture() {
		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( Exception $e ) {
			$this->_last_response = $e->getMessage();
		}
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

	protected function ap_ajax_success( $key = false, $return_json = false ) {
		preg_match( '#<div[^>]*>(.*?)</div>#', $this->_last_response, $match );
		if ( ! isset( $match[1] ) ) {
			return false;
		}
		$res = json_decode( $match[1] );
		if ( false !== $return_json ) {
			return $res;
		}
		if ( false !== $key ) {
			$this->assertObjectHasAttribute( $key, $res );
			if ( ! isset($res->$key ) ) {
				return false;
			}
			return $res->$key;
		}
	}

	protected function _getAjaxResponse() {
		return $this->ap_ajax_success( false, true );
	}
}