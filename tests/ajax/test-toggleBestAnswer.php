<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestToggleBestAnswer extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	/**
	 * @covers AnsPress\Ajax\Toggle_Best_Answer::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Toggle_Best_Answer' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', 'logged_in' ) );
	}
}
