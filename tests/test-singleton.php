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

	public function testMethodVisibility() {
		$reflection = new \ReflectionClass( '\AnsPress\Singleton' );
		$method = $reflection->getMethod( 'init' );
		$this->assertTrue( $method->isPublic() );

		$method = $reflection->getMethod( '__clone' );
		$this->assertTrue( $method->isPrivate() );

		$method = $reflection->getMethod( '__wakeup' );
		$this->assertTrue( $method->isPublic() );

		$method = $reflection->getMethod( 'run_once' );
		$this->assertTrue( $method->isPublic() );
	}
}
