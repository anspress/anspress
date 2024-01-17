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

	/**
	 * @covers Anspress\Addons\Notifications::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Notifications::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Notification group is added to the settings page.
		$this->assertArrayHasKey( 'notification', $groups );
		$this->assertArrayHasKey( 'label', $groups['notification'] );
		$this->assertEquals( 'Notification', $groups['notification']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertArrayHasKey( 'label', $groups['some_other_group'] );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'notification', $groups );
		$this->assertArrayHasKey( 'label', $groups['notification'] );
		$this->assertEquals( 'Notification', $groups['notification']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Notifications::load_options
	 */
	public function testLoadOptions() {
		$instance = \Anspress\Addons\Notifications::init();

		// Add user_page_title_notifications and user_page_slug_notifications options.
		ap_add_default_options(
			array(
				'user_page_title_notifications' => __( 'Notifications', 'anspress-question-answer' ),
				'user_page_slug_notifications'  => 'notifications',
			)
		);

		// Call the method.
		$form = $instance->load_options();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'user_page_title_notifications', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_slug_notifications', $form['fields'] );

		// Test for user_page_title_notifications field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( 'Notifications page title', $form['fields']['user_page_title_notifications']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( 'Custom title for user profile notifications page', $form['fields']['user_page_title_notifications']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_title_notifications'] );
		$this->assertEquals( ap_opt( 'user_page_title_notifications' ), $form['fields']['user_page_title_notifications']['value'] );

		// Test for user_page_slug_notifications field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( 'Notifications page slug', $form['fields']['user_page_slug_notifications']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( 'Custom slug for user profile notifications page', $form['fields']['user_page_slug_notifications']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_slug_notifications'] );
		$this->assertEquals( ap_opt( 'user_page_slug_notifications' ), $form['fields']['user_page_slug_notifications']['value'] );
	}
}
