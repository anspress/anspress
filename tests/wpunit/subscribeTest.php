<?php

class subscribeTest extends \Codeception\TestCase\WPTestCase
{

	public function setUp()
	{
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown()
	{
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @covers ::ap_new_subscriber
	 * @covers ::ap_get_subscriber
	 */
	public function testNewSubscriber(){
		global $wpdb;
		$id = ap_new_subscriber( 456, 'question', 222 );
		$this->assertNotEquals( false, $id );
		$this->assertGreaterThan( 0, $id );

		$subscriber = ap_get_subscriber( 456, 'question', 222 );
		$this->assertEquals( 456, $subscriber->subs_user_id );
		$this->assertEquals( 'question', $subscriber->subs_event );
		$this->assertEquals( 222, $subscriber->subs_ref_id );
	}

	/**
	 * @covers ::ap_subscribers_count
	 */
	public function testSubscribersCount() {
		global $wpdb;
		$wpdb->query("TRUNCATE {$wpdb->ap_subscribers}");

		ap_new_subscriber( 2345, 'question', 1234 );
		ap_new_subscriber( 23451, 'question', 1234 );
		ap_new_subscriber( 23452, 'question', 1234 );
		ap_new_subscriber( 23453, 'question', 1234 );
		ap_new_subscriber( 23454, 'question', 1234 );

		ap_new_subscriber( 23455, 'answer', 1234 );
		ap_new_subscriber( 23456, 'answer', 1234 );
		ap_new_subscriber( 23457, 'answer', 1234 );
		ap_new_subscriber( 23458, 'answer', 1234 );

		$count = ap_subscribers_count( 'question', 1234 );
		$this->assertEquals(5, $count);

		$count = ap_subscribers_count( 'answer', 1234 );
		$this->assertEquals(4, $count);

		$count = ap_subscribers_count( '', 1234 );
		$this->assertEquals(9, $count);

		ap_new_subscriber( 23458, 'question', 1235 );

		// Total count.
		$count = ap_subscribers_count();
		$this->assertEquals(10, $count);
	}

	/**
	 * @covers ::ap_get_subscribers
	 */
	public function testGetSubscribers() {
		global $wpdb;
		$wpdb->query("TRUNCATE {$wpdb->ap_subscribers}");

		ap_new_subscriber( 2345, 'question', 1234 );
		ap_new_subscriber( 23451, 'question', 1234 );
		ap_new_subscriber( 23452, 'question', 1234 );
		ap_new_subscriber( 23453, 'question', 1234 );
		ap_new_subscriber( 23454, 'question', 1234 );

		ap_new_subscriber( 23455, 'answer', 1234 );
		ap_new_subscriber( 23456, 'answer', 1234 );
		ap_new_subscriber( 23457, 'answer', 1234 );
		ap_new_subscriber( 23458, 'answer', 1234 );

		ap_new_subscriber( 23459, 'question', 1236 );
		ap_new_subscriber( 23466, 'question', 1236 );

		$this->assertEquals( 5, count( ap_get_subscribers( [ 'subs_event' => 'question', 'subs_ref_id' => 1234 ] ) ) );
		$this->assertEquals( 7, count( ap_get_subscribers( [ 'subs_event' => 'question' ] ) ) );
		$this->assertEquals( 4, count( ap_get_subscribers( [ 'subs_event' => 'answer' ] ) ) );
		$this->assertEquals( 9, count( ap_get_subscribers( [ 'subs_ref_id' => 1234 ] ) ) );
		$this->assertEquals( 11, count( ap_get_subscribers() ) );
	}

	/**
	 * @covers ap_delete_subscribers
	 */
	public function testDeleteSubscribers() {
		global $wpdb;
		$wpdb->query("TRUNCATE {$wpdb->ap_subscribers}");

		ap_new_subscriber( 2345, 'question', 1234 );
		ap_new_subscriber( 2345, 'answer', 1234 );
		ap_new_subscriber( 23451, 'question', 1234 );
		ap_new_subscriber( 23455, 'question', 1234 );

		ap_new_subscriber( 23459, 'question', 1236 );
		ap_new_subscriber( 23466, 'question', 1236 );
		ap_new_subscriber( 23466, 'answer', 1236 );
		ap_new_subscriber( 23467, 'comment', 1236 );
		ap_new_subscriber( 23468, 'comment', 1236 );

		// Delete subscription by all three parameters.
		ap_delete_subscribers( array(
			'subs_event'   => 'question',
			'subs_ref_id'  => 1234,
			'subs_user_id' => 2345,
		));

		$this->assertEquals( 2, count( ap_get_subscribers( [ 'subs_event' => 'question', 'subs_ref_id' => 1234 ] ) ) );

		// Delete subscription by two parameters.
		ap_delete_subscribers( array(
			'subs_event'   => 'question',
			'subs_ref_id'  => 1234,
		));
		$this->assertEquals( 2, count( ap_get_subscribers( [ 'subs_event' => 'question', 'subs_ref_id' => 1234 ] ) ) );

		ap_delete_subscribers( array(
			'subs_user_id'  => 23466,
		));

		$this->assertEquals( 0, count( ap_get_subscribers( [ 'subs_ref_id' => 23466 ] ) ) );
	}
}