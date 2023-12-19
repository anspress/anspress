<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAsyncTasks extends TestCase {

	public function testClassProperties() {
		// For \AnsPress\AsyncTasks\NewQuestion class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\NewQuestion' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\NewAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\NewAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\SelectAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\SelectAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\PublishComment class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\PublishComment' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\UpdateQuestion class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\UpdateQuestion' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\UpdateAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\UpdateAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );
	}

	public function testMethodExists() {
		// For \AnsPress\AsyncTasks\NewQuestion class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewQuestion', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewQuestion', 'run_action' ) );

		// For \AnsPress\AsyncTasks\NewAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewAnswer', 'run_action' ) );

		// For \AnsPress\AsyncTasks\SelectAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\SelectAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\SelectAnswer', 'run_action' ) );

		// For \AnsPress\AsyncTasks\PublishComment class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\PublishComment', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\PublishComment', 'run_action' ) );

		// For \AnsPress\AsyncTasks\UpdateQuestion class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateQuestion', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateQuestion', 'run_action' ) );

		// For \AnsPress\AsyncTasks\UpdateAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateAnswer', 'run_action' ) );
	}
}
