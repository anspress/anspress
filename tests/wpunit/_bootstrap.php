<?php
// Here you can initialize variables that will be available to your tests

class AnsPress_Tests extends \Codeception\TestCase\WPTestCase {
	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] ); }
	}
}

// Enable all addons.
update_option( 'anspress_addons', array(
	'free/avatar.php' => true,
	'free/category.php' => true,
	'free/tag.php' => true,
	'free/tag.php' => true,
	'free/bad-words.php' => true,
	'free/buddypress.php' => true,
	'free/email.php' => true,
	'free/recaptcha.php' => true,
) );

activate_plugin( 'anspress-question-answer/anspress-question-answer.php' );