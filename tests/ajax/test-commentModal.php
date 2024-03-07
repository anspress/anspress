<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestCommentModal extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	/**
	 * @covers AnsPress\Ajax\Comment_Modal::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Comment_Modal' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'logged_in' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'nopriv' ) );
	}
}
