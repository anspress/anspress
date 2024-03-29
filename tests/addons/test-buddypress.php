<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonBuddyPress extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'buddypress.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'buddypress.php' );
	}

	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\BuddyPress' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'bp_init' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'ap_assets_js' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'content_setup_nav' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'setup_subnav' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'ap_qa_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'ap_qa_page_content' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'page_questions' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'page_answers' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'question_answer_tracking' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'activity_buttons' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'activity_action' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'ap_the_question_content' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'ap_the_answer_content' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'registered_components' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'notifications_for_user' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'notification_new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'notification_new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'new_answer_notification' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'new_comment_notification' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'remove_answer_notify' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'remove_comment_notify' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'mark_bp_notify_as_read' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\BuddyPress', 'bp_loadmore' ) );
	}

	public function testInit() {
		$instance1 = \Anspress\Addons\BuddyPress::init();
		$this->assertInstanceOf( 'Anspress\Addons\BuddyPress', $instance1 );
		$instance2 = \Anspress\Addons\BuddyPress::init();
		$this->assertSame( $instance1, $instance2 );
	}

	public function testHooksFilters() {
		$instance = \Anspress\Addons\BuddyPress::init();

		// Tests.
		$this->assertEquals( 10, has_action( 'bp_init', [ $instance, 'bp_init' ] ) );
		$this->assertEquals( 10, has_action( 'ap_enqueue', [ $instance, 'ap_assets_js' ] ) );
		$this->assertEquals( 10, has_action( 'bp_setup_nav', [ $instance, 'content_setup_nav' ] ) );
		$this->assertEquals( 10, has_action( 'bp_init', [ $instance, 'question_answer_tracking' ] ) );
		$this->assertEquals( 10, has_action( 'bp_activity_entry_meta', [ $instance, 'activity_buttons' ] ) );
		$this->assertEquals( 10, has_filter( 'bp_activity_custom_post_type_post_action', [ $instance, 'activity_action' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_the_question_content', [ $instance, 'ap_the_question_content' ] ) );
		$this->assertEquals( 10, has_action( 'bp_notifications_get_registered_components', [ $instance, 'registered_components' ] ) );
		$this->assertEquals( 10, has_action( 'bp_notifications_get_notifications_for_user', [ $instance, 'notifications_for_user' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ $instance, 'new_answer_notification' ] ) );
		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ $instance, 'new_comment_notification' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_question', [ $instance, 'remove_answer_notify' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_answer', [ $instance, 'remove_answer_notify' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_answer', [ $instance, 'remove_comment_notify' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unpublish_comment', [ $instance, 'remove_comment_notify' ] ) );
		$this->assertEquals( 10, has_action( 'before_delete_post', [ $instance, 'remove_answer_notify' ] ) );
		$this->assertEquals( 10, has_action( 'the_post', [ $instance, 'mark_bp_notify_as_read' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_bp_loadmore', [ $instance, 'bp_loadmore' ] ) );
	}

	/**
	 * @covers Anspress\Addons\BuddyPress::bp_init
	 */
	public function testBpInit() {
		$instance = \Anspress\Addons\BuddyPress::init();
		$instance->bp_init();

		$this->assertEquals( 10, has_filter( 'the_content', [ $instance, 'ap_the_answer_content' ] ) );
	}
}
