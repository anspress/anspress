<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonNotifications extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'notifications.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'notifications.php' );
	}

	/**
	 * @covers Anspress\Addons\Notifications::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Notifications' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'load_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'ap_menu_object' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'register_verbs' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'ap_user_pages' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'notification_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'trash_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'trash_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'select_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'unselect_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'delete_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'undo_vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'undo_vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'insert_reputation' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'delete_reputation' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'mark_notifications_seen' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'load_more_notifications' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Notifications', 'get_notifications' ) );
	}

	/**
	 * @covers Anspress\Addons\Notifications::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Notifications::init();
		$this->assertInstanceOf( 'Anspress\Addons\Notifications', $instance1 );
		$instance2 = \Anspress\Addons\Notifications::init();
		$this->assertSame( $instance1, $instance2 );
	}
}
