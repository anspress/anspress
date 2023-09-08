<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Singleton extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( '\AnsPress\Singleton', 'init' ) );
		$this->assertTrue( method_exists( '\AnsPress\Singleton', '__clone' ) );
		$this->assertTrue( method_exists( '\AnsPress\Singleton', '__wakeup' ) );
	}
}
