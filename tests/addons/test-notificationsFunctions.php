<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonNotificationsFunctions extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'notifications.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'notifications.php' );
	}

	/**
	 * @covers ::ap_register_notification_verb
	 */
	public function testAPRegisterNotificationVerb() {
		global $ap_notification_verbs;

		// Test begins.
		// Test 1.
		$key = 'test-verb';
		$args = [
			'ref_type' => 'test-ref',
			'label'    => 'Test Verb',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'test-ref',
			'label'      => 'Test Verb',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 2.
		$key = 'test-verb-2';
		$args = [
			'ref_type'   => 'test-ref-2',
			'label'      => 'Test Verb 2',
			'hide_actor' => true,
			'icon'       => 'test-icon',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'test-ref-2',
			'label'      => 'Test Verb 2',
			'hide_actor' => true,
			'icon'       => 'test-icon',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 3.
		$key = 'test-verb-3';
		$args = [
			'label' => 'Test Verb 3',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'post',
			'label'      => 'Test Verb 3',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 4.
		$key = 'test-verb-4';
		$args = [];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'post',
			'label'      => '',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 5.
		$key = 'test-verb-5';
		$args = [
			'ref_type' => 'comment',
			'label'    => 'Test Verb 5',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'comment',
			'label'      => 'Test Verb 5',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 6.
		$key = 'test-verb-6';
		$args = [
			'custom' => 'value',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'post',
			'label'      => '',
			'hide_actor' => false,
			'icon'       => '',
			'custom'     => 'value',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 7.
		$key = 'test-verb-7';
		$args = [
			'ref_type' => 'comment',
			'icon'     => 'test-icon-7',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'comment',
			'label'      => '',
			'hide_actor' => false,
			'icon'       => 'test-icon-7',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Test 8.
		$key = 'test-verb-8';
		$args = [
			'hide_actor' => true,
			'label'      => 'Test Verb 8',
		];
		ap_register_notification_verb( $key, $args );
		$this->assertArrayHasKey( $key, $ap_notification_verbs );
		$expected = [
			'ref_type'   => 'post',
			'label'      => 'Test Verb 8',
			'hide_actor' => true,
			'icon'       => '',
		];
		$this->assertEquals( $expected, $ap_notification_verbs[ $key ] );

		// Reset global variable.
		$ap_notification_verbs = [];
	}

	/**
	 * @covers ::ap_notification_verbs
	 */
	public function testAPNotificationVerbs() {
		global $ap_notification_verbs;
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_notification_verbs', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$this->assertFalse( $callback_triggered );
		ap_notification_verbs();
		$this->assertIsArray( $ap_notification_verbs );
		$this->assertEmpty( $ap_notification_verbs );
		$this->assertTrue( $callback_triggered );

		// Test 2.
		$callback_triggered = false;
		$ap_notification_verbs = [ 'test-verb' => [ 'label' => 'Test Verb' ] ];
		$this->assertFalse( $callback_triggered );
		ap_notification_verbs();
		$this->assertIsArray( $ap_notification_verbs );
		$this->assertArrayHasKey( 'test-verb', $ap_notification_verbs );
		$expected = [ 'label' => 'Test Verb' ];
		$this->assertEquals( $expected, $ap_notification_verbs[ 'test-verb' ] );
		$this->assertFalse( $callback_triggered );

		// Test 3.
		$callback_triggered = false;
		$ap_notification_verbs = [];
		$this->assertFalse( $callback_triggered );
		ap_notification_verbs();
		$this->assertIsArray( $ap_notification_verbs );
		$this->assertEmpty( $ap_notification_verbs );
		$this->assertTrue( $callback_triggered );

		// Test 4.
		$callback_triggered = false;
		$ap_notification_verbs = [ 'test-verb-2' => [ 'label' => 'Test Verb 2' ] ];
		$this->assertFalse( $callback_triggered );
		ap_notification_verbs();
		$this->assertIsArray( $ap_notification_verbs );
		$this->assertArrayHasKey( 'test-verb-2', $ap_notification_verbs );
		$expected = [ 'label' => 'Test Verb 2' ];
		$this->assertEquals( $expected, $ap_notification_verbs[ 'test-verb-2' ] );
		$this->assertFalse( $callback_triggered );

		// Test 5.
		$callback_triggered = false;
		$ap_notification_verbs = [
			'test-verb-3' => [ 'label' => 'Test Verb 3' ],
			'test-verb-4' => [ 'label' => 'Test Verb 4' ],
			'test-verb-5' => [ 'label' => 'Test Verb 5' ],
		];
		$this->assertFalse( $callback_triggered );
		ap_notification_verbs();
		$this->assertIsArray( $ap_notification_verbs );
		$this->assertArrayHasKey( 'test-verb-3', $ap_notification_verbs );
		$expected = [ 'label' => 'Test Verb 3' ];
		$this->assertEquals( $expected, $ap_notification_verbs[ 'test-verb-3' ] );
		$this->assertArrayHasKey( 'test-verb-4', $ap_notification_verbs );
		$expected = [ 'label' => 'Test Verb 4' ];
		$this->assertEquals( $expected, $ap_notification_verbs[ 'test-verb-4' ] );
		$this->assertArrayHasKey( 'test-verb-5', $ap_notification_verbs );
		$expected = [ 'label' => 'Test Verb 5' ];
		$this->assertEquals( $expected, $ap_notification_verbs[ 'test-verb-5' ] );
		$this->assertFalse( $callback_triggered );

		// Reset global variable.
		$ap_notification_verbs = [];
	}

	/**
	 * @covers ::ap_insert_notification
	 */
	public function testAPInsertNotificationEmptyUserId() {
		// Test 1.
		$args = [ 'user_id' => 0 ];
		$this->assertFalse( ap_insert_notification( $args ) );

		// Test 2.
		$args = [ 'user_id' => '' ];
		$this->assertFalse( ap_insert_notification( $args ) );

		// Test 3.
		$args = [];
		$this->assertFalse( ap_insert_notification( $args ) );
	}

	/**
	 * @covers ::ap_insert_notification
	 */
	public function testAPInsertNotificationNewNotification() {
		// Test 1.
		$args = [ 'user_id' => 1 ];
		$this->assertIsInt( ap_insert_notification( $args ) );
		$get_notification = ap_get_notifications( $args );
		$this->assertNotEmpty( $get_notification );
		$this->assertEquals( 1, $get_notification[0]->noti_user_id );
		$this->assertEquals( 0, $get_notification[0]->noti_seen );

		// Test 2.
		$this->setRole( 'subscriber' );
		$args = [];
		$this->assertIsInt( ap_insert_notification( $args ) );
		$get_notification = ap_get_notifications( $args );
		$this->assertNotEmpty( $get_notification );
		$this->assertEquals( get_current_user_id(), $get_notification[0]->noti_user_id );
		$this->assertEquals( 0, $get_notification[0]->noti_seen );

		// Test 3.
		$args = [
			'user_id'  => 2,
			'actor'    => 11,
			'parent'   => 5,
			'ref_id'   => 3,
			'ref_type' => 'question',
			'verb'     => 'best_answer',
			'seen'     => 0,
			'date'     => current_time( 'mysql' ),
		];
		$this->assertIsInt( ap_insert_notification( $args ) );
		$get_notification = ap_get_notifications( $args );
		$this->assertNotEmpty( $get_notification );
		$this->assertEquals( 2, $get_notification[0]->noti_user_id );
		$this->assertEquals( 11, $get_notification[0]->noti_actor );
		$this->assertEquals( 5, $get_notification[0]->noti_parent );
		$this->assertEquals( 3, $get_notification[0]->noti_ref_id );
		$this->assertEquals( 'question', $get_notification[0]->noti_ref_type );
		$this->assertEquals( 'best_answer', $get_notification[0]->noti_verb );
		$this->assertEquals( 0, $get_notification[0]->noti_seen );
		$this->assertEquals( current_time( 'mysql' ), $get_notification[0]->noti_date );

		// Test 4.
		$this->setRole( 'subscriber' );
		$args = [ 'seen' => 1 ];
		$this->assertIsInt( ap_insert_notification( $args ) );
		$get_notification = ap_get_notifications( $args );
		$this->assertEquals( 1, $get_notification[0]->noti_seen );
	}

	/**
	 * @covers ::ap_insert_notification
	 */
	public function testAPInsertNotificationUpdateNotification() {
		// Test 1.
		$args = [ 'user_id' => 1, 'seen' => 1 ];
		$insert = ap_insert_notification( $args );
		$this->assertIsInt( $insert );
		$get_notification = ap_get_notifications( [ 'user_id' => 1 ] );
		$this->assertEquals( 1, $get_notification[0]->noti_seen );
		$insert = ap_insert_notification( $args );
		$this->assertIsInt( $insert );
		$get_notification = ap_get_notifications( [ 'user_id' => 1 ] );
		$this->assertEquals( 0, $get_notification[0]->noti_seen );

		// Test 2.
		$args = [
			'user_id'  => 2,
			'actor'    => 11,
			'parent'   => 5,
			'ref_id'   => 3,
			'ref_type' => 'question',
			'verb'     => 'best_answer',
			'seen'     => 1,
			'date'     => current_time( 'mysql' ),
		];
		$insert = ap_insert_notification( $args );
		$this->assertIsInt( $insert );
		$get_notification = ap_get_notifications( [ 'user_id' => 2 ] );
		$this->assertEquals( 1, $get_notification[0]->noti_seen );
		$insert = ap_insert_notification( $args );
		$this->assertIsInt( $insert );
		$get_notification = ap_get_notifications( [ 'user_id' => 2 ] );
		$this->assertEquals( 0, $get_notification[0]->noti_seen );
	}
}
