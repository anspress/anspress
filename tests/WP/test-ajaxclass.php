<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAjaxClass extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Classes\Ajax' );
		$this->assertTrue( $class->hasProperty( 'res' ) && $class->getProperty( 'res' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'success' ) && $class->getProperty( 'success' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'nonce_key' ) && $class->getProperty( 'nonce_key' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'req' ) && $class->getProperty( 'req' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'set_action' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'logged_in' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'nopriv' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'add_res' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'verify_nonce' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'set_success' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'set_fail' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'snackbar' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'req' ) );
		$this->assertTrue( method_exists( 'AnsPress\Classes\Ajax', 'send' ) );
	}
}
