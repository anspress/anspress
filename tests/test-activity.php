<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivity extends TestCase {

	use Testcases\Common;

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

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'hooks' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_before_delete' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_delete_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_ajax_more_activities' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', '_delete_user' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'prepare_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_action' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'action_exists' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'insert' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'get_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress\Activity_Helper', 'delete' ) );
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

	/**
	 * @covers AnsPress\Activity_Helper::get_actions
	 */
	public function testAnsPressActivityHelperGetActions() {
		$activity = \AnsPress\Activity_Helper::get_instance();
		$get_actions = $activity->get_actions();

		// Test begins.
		$this->assertNotEmpty( $get_actions );
		$this->assertIsArray( $get_actions );

		// Test if exists the array key.
		$this->assertArrayHasKey( 'new_q', $get_actions );
		$this->assertArrayHasKey( 'edit_q', $get_actions );
		$this->assertArrayHasKey( 'new_a', $get_actions );
		$this->assertArrayHasKey( 'edit_a', $get_actions );
		$this->assertArrayHasKey( 'status_publish', $get_actions );
		$this->assertArrayHasKey( 'status_future', $get_actions );
		$this->assertArrayHasKey( 'status_moderate', $get_actions );
		$this->assertArrayHasKey( 'status_private_post', $get_actions );
		$this->assertArrayHasKey( 'status_trash', $get_actions );
		$this->assertArrayHasKey( 'featured', $get_actions );
		$this->assertArrayHasKey( 'closed_q', $get_actions );
		$this->assertArrayHasKey( 'new_c', $get_actions );
		$this->assertArrayHasKey( 'edit_c', $get_actions );
		$this->assertArrayHasKey( 'selected', $get_actions );
		$this->assertArrayHasKey( 'unselected', $get_actions );

		// Test if the needed activity details is present inside the array or not.
		foreach ( $get_actions as $action ) {
			$this->assertArrayHasKey( 'ref_type', $action );
			$this->assertArrayHasKey( 'verb', $action );
			$this->assertArrayHasKey( 'icon', $action );
		}
	}
}
