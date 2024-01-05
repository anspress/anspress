<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestSingleton extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( '\AnsPress\Singleton', 'init' ) );
		$this->assertTrue( method_exists( '\AnsPress\Singleton', '__clone' ) );
		$this->assertTrue( method_exists( '\AnsPress\Singleton', '__wakeup' ) );
		$this->assertTrue( method_exists( '\AnsPress\Singleton', 'run_once' ) );
	}
}
