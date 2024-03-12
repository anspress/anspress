<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestRepeatableField extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Repeatable_Field' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Repeatable_Field', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Repeatable_Field', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Repeatable_Field', 'logged_in' ) );
	}
}
