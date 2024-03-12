<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonNotificationsFunctions extends TestCase {

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
}
