<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonNotificationsQuery extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'notifications.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'notifications.php' );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Notifications' );
		$this->assertTrue( $class->hasProperty( 'verbs' ) && $class->getProperty( 'verbs' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Notifications', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'query' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_posts' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_comments' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_actors' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'prefetch_reputations' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'verb_args' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'item_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_notification' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_id' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_type' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_permalink' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_permalink' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'actor_avatar' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_actor_avatar' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_verb' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_verb' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_date' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_date' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_ref_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_ref_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_reputation_points' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_reputation_points' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'hide_actor' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'get_icon' ) );
		$this->assertTrue( method_exists( 'Anspress\Notifications', 'the_icon' ) );
	}

	/**
	 * @covers Anspress\Notifications::__construct
	 */
	public function testConstructor() {
		$notifications = new \Anspress\Notifications();
		$this->assertInstanceOf( 'Anspress\Notifications', $notifications );
	}

	/**
	 * @covers Anspress\Notifications::__construct
	 */
	public function testConstructorWithoutArgs() {
		$this->setRole( 'subscriber' );
		$notifications = new \Anspress\Notifications();
		$this->assertInstanceOf( 'Anspress\Notifications', $notifications );

		// Tests.
		$this->assertArrayHasKey( 'reputation', $notifications->ids );
		$this->assertArrayHasKey( 'reputation', $notifications->pos );
		$this->assertEquals( ap_notification_verbs(), $notifications->verbs );
		$this->assertEquals( 1, $notifications->paged );
		$this->assertEquals( 0, $notifications->offset );
		$this->assertEquals( get_current_user_id(), $notifications->args['user_id'] );
		$this->assertEquals( 20, $notifications->args['number'] );
		$this->assertEquals( 0, $notifications->args['offset'] );
		$this->assertEquals( 'DESC', $notifications->args['order'] );
		$this->assertEquals( 20, $notifications->per_page );
	}

	/**
	 * @covers Anspress\Notifications::__construct
	 */
	public function testConstructorWithArgs() {
		$user_id = $this->factory()->user->create();
		$notifications = new \Anspress\Notifications( [
			'user_id' => $user_id,
			'number'  => 10,
			'offset'  => 2,
			'order'   => 'ASC',
			'seen'    => 'all',
			'paged'   => 2,
		] );
		$this->assertInstanceOf( 'Anspress\Notifications', $notifications );

		// Tests.
		$this->assertArrayHasKey( 'reputation', $notifications->ids );
		$this->assertArrayHasKey( 'reputation', $notifications->pos );
		$this->assertEquals( ap_notification_verbs(), $notifications->verbs );
		$this->assertEquals( 2, $notifications->paged );
		$this->assertEquals( 20, $notifications->offset );
		$this->assertEquals( $user_id, $notifications->args['user_id'] );
		$this->assertEquals( 10, $notifications->args['number'] );
		$this->assertEquals( 2, $notifications->args['offset'] );
		$this->assertEquals( 'ASC', $notifications->args['order'] );
		$this->assertEquals( 'all', $notifications->args['seen'] );
		$this->assertEquals( 2, $notifications->args['paged'] );
		$this->assertEquals( 10, $notifications->per_page );
	}

	/**
	 * @covers Anspress\Notifications::verb_args
	 */
	public function testVerbArgs() {
		$instance = \AnsPress\Addons\Notifications::init();
		add_action( 'ap_notification_verbs', [ $instance, 'register_verbs' ] );
		$notifications = new \Anspress\Notifications();
		$this->assertInstanceOf( 'Anspress\Notifications', $notifications );

		// Tests.
		$this->assertEquals( ap_notification_verbs(), $notifications->verbs );
		$new_answer_verb = [
			'ref_type'   => 'post',
			'label'      => 'posted an answer on your question',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $new_answer_verb, $notifications->verb_args( 'new_answer' ) );
		$new_comment_verb = [
			'ref_type'   => 'comment',
			'label'      => 'commented on your %cpt%',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $new_comment_verb, $notifications->verb_args( 'new_comment' ) );
		$best_answer_verb = [
			'ref_type'   => 'post',
			'label'      => 'selected your answer',
			'hide_actor' => false,
			'icon'       => '',
		];
		$this->assertEquals( $best_answer_verb, $notifications->verb_args( 'best_answer' ) );
	}
}
