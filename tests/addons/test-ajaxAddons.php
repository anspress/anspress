<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjaxAddons extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'reputation.php' );
		ap_activate_addon( 'notifications.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'reputation.php' );
		ap_deactivate_addon( 'notifications.php' );
	}

	/**
	 * @covers Anspress\Addons\Reputation::ap_save_events
	 */
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

	/**
	 * @covers Anspress\Addons\Notifications::mark_notifications_seen
	 */
	public function testMarkNotificationsSeenForNonLoggedInUsers() {
		$instance = \Anspress\Addons\Notifications::init();
		add_action( 'ap_ajax_mark_notifications_seen', [ $instance, 'mark_notifications_seen' ] );

		// Insert some dummy notifications.
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );
		ap_insert_notification();
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 0, $noti_seen[0]->noti_seen );
		$this->logout();

		// Test.
		$this->_set_post_data( 'ap_ajax_action=mark_notifications_seen&__nonce=' . wp_create_nonce( 'mark_notifications_seen' ) );
		$this->handle( 'ap_ajax' );
		wp_set_current_user( $user_id );
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 0, $noti_seen[0]->noti_seen );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'There was a problem processing your request' );
	}

	/**
	 * @covers Anspress\Addons\Notifications::mark_notifications_seen
	 */
	public function testMarkNotificationsSeenForInvalidNonce() {
		$instance = \Anspress\Addons\Notifications::init();
		add_action( 'ap_ajax_mark_notifications_seen', [ $instance, 'mark_notifications_seen' ] );

		// Insert some dummy notifications.
		$this->setRole( 'subscriber' );
		ap_insert_notification();
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 0, $noti_seen[0]->noti_seen );

		// Test.
		$this->_set_post_data( 'ap_ajax_action=mark_notifications_seen&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 0, $noti_seen[0]->noti_seen );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'There was a problem processing your request' );

	}

	/**
	 * @covers Anspress\Addons\Notifications::mark_notifications_seen
	 */
	public function testMarkNotificationsSeen() {
		$instance = \Anspress\Addons\Notifications::init();
		add_action( 'ap_ajax_mark_notifications_seen', [ $instance, 'mark_notifications_seen' ] );

		// Insert some dummy notifications.
		$this->setRole( 'subscriber' );
		ap_insert_notification();
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 0, $noti_seen[0]->noti_seen );

		// Test.
		$this->_set_post_data( 'ap_ajax_action=mark_notifications_seen&__nonce=' . wp_create_nonce( 'mark_notifications_seen' ) );
		$this->handle( 'ap_ajax' );
		$noti_seen = ap_get_notifications( [] );
		$this->assertEquals( 1, $noti_seen[0]->noti_seen );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'btn' )->hide === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully updated all notifications' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'notificationAllRead' );
	}
}
