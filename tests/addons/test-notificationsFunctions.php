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
}
