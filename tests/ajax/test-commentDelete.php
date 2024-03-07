<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestCommentDelete extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	/**
	 * @covers AnsPress\Ajax\Comment_Delete::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Comment_Delete' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', 'logged_in' ) );
	}
}
