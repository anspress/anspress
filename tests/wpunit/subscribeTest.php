<?php

class subscribeTest extends \Codeception\TestCase\WPTestCase
{
	use AnsPress\Tests\Testcases\Common;

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
	 * @covers ::ap_delete_subscribers
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

	/**
	 * @covers ::ap_delete_subscriber
	 */
	public function testDeleteSubscriber() {
		global $wpdb;
		$wpdb->query("TRUNCATE {$wpdb->ap_subscribers}");

		ap_new_subscriber( 23459, 'question', 1236 );

		$this->assertEquals(1, ap_subscribers_count( 'question', 1236 ));
		ap_delete_subscriber( 1236, 23459, 'question' );

		$this->assertEquals(0, ap_subscribers_count( 'question', 1236 ));
	}

	/**
	 * @covers AnsPress_Hooks::ap_after_new_question
	 */
	public function testQuestionSubscriptionHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Hooks', 'question_subscription' ] ) );

		$this->setRole('subscriber');

		// Check if question created without author set current user as subscriber.
		$id = $this->insert_question( 'Suspendisse aliqua', 'Donec ultricies blandit venenatis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.');

		// Run action so that ap_after_new_question hook can trigger.
		do_action( 'ap_processed_new_question', $id, get_post( $id ) );

		$this->assertFalse( ap_is_user_subscriber( 'question', $id ) );

		// Check if question author get subscribed.
		$id = $this->insert_question( 'Suspendisse aliqua', 'Donec ultricies blandit venenatis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.', get_current_user_id() );

		// Run action so that ap_after_new_question hook can trigger.
		do_action( 'ap_processed_new_question', $id, get_post( $id ) );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
	}

	/**
	 * @covers AnsPress_Hooks::answer_subscription
	 */
	public function testAnswerSubscriptionHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Hooks', 'answer_subscription' ] ) );

		$this->setRole('subscriber');

		// Check if answer created without author set current user as subscriber.
		$ids = $this->insert_answers(array(
			'post_title' => 'Pellentesque odio purus, egestas ac luctus gravida, rutrum ut quam.',
			'post_content' => 'Pellentesque eget quam dui, sit amet eleifend mauris.',
		));

		// Run action so that ap_after_new_answer hook can trigger.
		do_action( 'ap_processed_new_answer', $ids['answers'][0], get_post( $ids['answers'][0] ) );

		$this->assertFalse( ap_is_user_subscriber( 'answer_' . $ids['answers'][0], $ids['question'] ) );

		// Check if answer author get subscribed.
		$ids = $this->insert_answers(array(
			'post_title' => 'Pellentesque odio purus, egestas ac luctus gravida, rutrum ut quam.',
			'post_content' => 'Pellentesque eget quam dui, sit amet eleifend mauris.',
		), array(
			'post_author' => get_current_user_id(),
		));

		// Run action so that ap_after_new_answer hook can trigger.
		do_action( 'ap_processed_new_answer', $ids['answers'][0], get_post( $ids['answers'][0] ) );

		$this->assertTrue( ap_is_user_subscriber( 'answer_' . $ids['answers'][0], $ids['question'] ) );
	}

	/**
	 * Check if qameta subscribers count is getting updated on adding new subscribers.
	 *
	 * @covers AnsPress_Hooks::new_subscriber
	 */
	public function testNewSubscriberHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_new_subscriber', [ 'AnsPress_Hooks', 'new_subscriber' ] ) );

		$this->setRole('subscriber');
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_get_post( $id )->subscribers );
		ap_new_subscriber( false, 'question', $id );
		ap_new_subscriber( 2324324, 'question', $id );
		ap_new_subscriber( 23232, 'question', $id );

		$this->assertEquals( 3, ap_get_post( $id )->subscribers );

		// $ids = $this->insert_answer();
		// ap_new_subscriber( 2345, 'answer_' . $ids->a, $ids->q );
		// ap_new_subscriber( 23451, 'answer_' . $ids->a, $ids->q );
		// ap_new_subscriber( 23455, 'answer_' . $ids->a, $ids->q );

		// $this->assertEquals( 3, ap_get_post( $ids->a )->subscribers );
	}

	/**
	 * Check if qameta subscribers count is getting updated on deleting subscribers.
	 *
	 * @covers AnsPress_Hooks::delete_subscribers
	 */
	public function testDeleteSubscribersHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_delete_subscribers', [ 'AnsPress_Hooks', 'delete_subscribers' ] ) );

		$id = $this->insert_question();

		ap_new_subscriber( 2345, 'question', $id );
		ap_new_subscriber( 23451, 'question', $id );
		ap_new_subscriber( 23455, 'question', $id );

		$this->assertEquals( 3, ap_get_post( $id )->subscribers );

		ap_delete_subscribers( array(
			'subs_ref_id' => $id,
			'subs_event' => 'question',
		) );

		$this->assertEquals( 0, ap_get_post( $id )->subscribers );

		// $ids = $this->insert_answer();
		// ap_new_subscriber( 2345, 'answer_' . $ids->a, $ids->q );
		// ap_new_subscriber( 23451, 'answer_' . $ids->a, $ids->q );
		// ap_new_subscriber( 23455, 'answer_' . $ids->a, $ids->q );
		// $this->assertEquals( 3, ap_get_post( $ids->a )->subscribers );

		// ap_delete_subscribers( array(
		// 	'subs_ref_id' => $ids->q,
		// 	'subs_event' => 'question',
		// ) );
	}

	/**
	 * @covers ::ap_esc_subscriber_event
	 */
	public function testEscSubscriberEvent() {
		$this->assertEquals( 'answer', ap_esc_subscriber_event( 'answer_99899' ) );
		$this->assertEquals( 'question', ap_esc_subscriber_event( 'question' ) );
	}

	/**
	 * @covers ::ap_esc_subscriber_event_id
	 */
	public function testEscSubscriberEventId() {
		$this->assertEquals( 99899, ap_esc_subscriber_event_id( 'answer_99899' ) );
		$this->assertEquals( 0, ap_esc_subscriber_event_id( 'question' ) );
	}
}