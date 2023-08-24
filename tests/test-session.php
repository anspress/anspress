<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Session extends TestCase {

	/**
	 * @covers AnsPress\Session::init
	 */
	public function testInit() {
		$class = new \ReflectionClass('AnsPress\Session');
		$this->assertTrue($class->hasProperty('instance') && $class->getProperty('instance')->isStatic());
	}
}
