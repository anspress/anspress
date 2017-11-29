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

	protected function _go_to_question( $id ) {
		$this->go_to( site_url( "?post_type=question&p=$id" ) );
	}

	protected function insert_question( $title, $content ) {
		return $this->factory->post->create( array( 'post_title' => $title, 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => $content ) );
	}
}