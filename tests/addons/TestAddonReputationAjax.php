<?php

namespace AnsPress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAddonReputationAjax extends TestCaseAjax {
	use Testcases\Common;
	use Testcases\Ajax;

	public function testAPSaveEvents() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		$instance = \Anspress\Addons\Reputation::init();
		add_action( 'wp_ajax_ap_save_events', [ $instance, 'ap_save_events' ] );

		// Event data.
		ap_register_reputation_event( 'test_event', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 11,

		] );

		// Test 1.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'action=ap_save_events&__nonce=' . wp_create_nonce( 'ap-save-events' ) );
		$_POST['events'] = [
			'test_event' => 12,
		];
		$this->handle( 'ap_save_events' );
		$this->assertEmpty( $this->_last_response );
		$get_option = get_option( 'anspress_reputation_events' );
		$this->assertFalse( $get_option );

		// Test 2.
		$this->_last_response = '';
		$this->setRole( 'administrator' );
		$this->_set_post_data( 'action=ap_save_events&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$_POST['events'] = [
			'test_event' => 12,
		];
		$this->handle( 'ap_save_events' );
		$this->assertEmpty( $this->_last_response );
		$get_option = get_option( 'anspress_reputation_events' );
		$this->assertFalse( $get_option );

		// Test 3.
		$this->_last_response = '';
		$this->setRole( 'administrator' );
		$this->_set_post_data( 'action=ap_save_events&__nonce=' . wp_create_nonce( 'ap-save-events' ) );
		$_POST['events'] = [
			'test_event' => 12,
		];
		$this->handle( 'ap_save_events' );
		$this->assertEquals( '<div class="notice notice-success is-dismissible"><p>Successfully updated reputation points!</p></div>', $this->_last_response );
		$get_option = get_option( 'anspress_reputation_events' );
		$this->assertNotEmpty( $get_option );
		$this->assertEquals( [ 'test_event' => 12 ], $get_option );
	}
}
