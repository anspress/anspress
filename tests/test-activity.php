<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivity extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	/**
	 * @covers AnsPress\Activity_Helper::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Activity_Helper' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}
}
