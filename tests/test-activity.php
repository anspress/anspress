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

	public function testClassPropertiesAvailable() {
		$class = new \ReflectionClass( 'AnsPress\Activity_Helper' );
		$this->assertTrue( $class->hasProperty( 'table' ) && $class->getProperty( 'table' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'actions' ) && $class->getProperty( 'actions' )->isPrivate() );
	}

	/**
	 * @covers AnsPress\Activity_Helper::hooks
	 */
	public function testAnsPressActivityHelperHooks() {
		$this->assertEquals( 10, has_action( 'before_delete_post', [ 'AnsPress\Activity_Helper', '_before_delete' ] ) );
		$this->assertEquals( 10, has_action( 'delete_comment', [ 'AnsPress\Activity_Helper', '_delete_comment' ] ) );
		$this->assertEquals( 10, has_action( 'delete_user', [ 'AnsPress\Activity_Helper', '_delete_user' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_more_activities', [ 'AnsPress\Activity_Helper', '_ajax_more_activities' ] ) );
	}
}
