<?php

namespace Tests\Unit\src\backend\Classes;

use Yoast\WPTestUtils\BrainMonkey\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberService
 * @package Tests\WP
 */
class TestSubscriberService extends TestCase {

	public function setUp() : void {
		parent::setUp();

		global $wpdb;

		// Remove all data from subscribers table.
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'ap_subscribers' );
	}

	public function testCreatePassed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();
		$subscriber = $service->create( array(
			'subs_user_id' => 1,
			'subs_ref_id'  => 1,
			'subs_event'   => 'test_event',
		) );

		$this->assertInstanceOf( \AnsPress\Modules\Subscriber\SubscriberModel::class, $subscriber );
	}

	public function testUpdatePassed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();
		$subscriber = $service->create( array(
			'subs_user_id' => 1,
			'subs_ref_id'  => 1,
			'subs_event'   => 'test_event',
		) );

		$updatedSubscriber = $service->update( $subscriber->subs_id, array(
			'subs_user_id' => 2,
			'subs_ref_id'  => 2,
			'subs_event'   => 'test_event2',
		) );

		$this->assertEquals( $updatedSubscriber->subs_user_id, 2 );
		$this->assertEquals( $updatedSubscriber->subs_ref_id, 2 );
		$this->assertEquals( $updatedSubscriber->subs_event, 'test_event2' );
	}

	public function testUpdateException() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();

		$this->expectException( \Exception::class );
		$service->update( 999, array(
			'subs_user_id' => 2,
			'subs_ref_id'  => 2,
			'subs_event'   => 'test_event2',
		) );
	}

	public function testGetByIdPassed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();
		$subscriber = $service->create( array(
			'subs_user_id' => 1,
			'subs_ref_id'  => 1,
			'subs_event'   => 'test_event',
		) );

		$foundSubscriber = $service->getById( $subscriber->subs_id );

		$this->assertEquals( $foundSubscriber->subs_user_id, 1 );
		$this->assertEquals( $foundSubscriber->subs_ref_id, 1 );
		$this->assertEquals( $foundSubscriber->subs_event, 'test_event' );
	}

	public function testGetByUserAndEventPassed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();
		$service->create( array(
			'subs_user_id' => 1,
			'subs_ref_id'  => 1,
			'subs_event'   => 'test_event',
		) );

		$foundSubscriber = $service->getByUserAndEvent( 1, 'test_event', 1 );

		$this->assertCount( 1, $foundSubscriber );

		$this->assertEquals( $foundSubscriber[0]->subs_user_id, 1 );
		$this->assertEquals( $foundSubscriber[0]->subs_ref_id, 1 );
		$this->assertEquals( $foundSubscriber[0]->subs_event, 'test_event' );
	}

	public function testDestroyPassed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();
		$subscriber = $service->create( array(
			'subs_user_id' => 1,
			'subs_ref_id'  => 1,
			'subs_event'   => 'test_event',
		) );

		$service->destroy( $subscriber->subs_id );

		$this->assertNull( $service->getById( $subscriber->subs_id ) );
	}

	public function testDestroyFailed() {
		$service = new \AnsPress\Modules\Subscriber\SubscriberService();

		$this->expectException( \Exception::class );
		$service->destroy( 999 );
	}
}
