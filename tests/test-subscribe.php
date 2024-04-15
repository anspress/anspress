<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestSubscribe extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_new_subscriber
	 * @covers ::ap_get_subscriber
	 */
	public function testNewSubscriber() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

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
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

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
		$this->assertEquals( 5, $count );

		$count = ap_subscribers_count( 'answer', 1234 );
		$this->assertEquals( 4, $count );

		$count = ap_subscribers_count( '', 1234 );
		$this->assertEquals( 9, $count );

		ap_new_subscriber( 23458, 'question', 1235 );

		// Total count.
		$count = ap_subscribers_count();
		$this->assertEquals( 10, $count );
	}

	/**
	 * @covers ::ap_get_subscribers
	 */
	public function testGetSubscribers() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

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

		$this->assertEquals(
			5, count(
				ap_get_subscribers(
					[
						'subs_event'  => 'question',
						'subs_ref_id' => 1234,
					]
				)
			)
		);
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
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

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
		ap_delete_subscribers(
			array(
				'subs_event'   => 'question',
				'subs_ref_id'  => 1234,
				'subs_user_id' => 2345,
			)
		);

		$this->assertEquals(
			2, count(
				ap_get_subscribers(
					[
						'subs_event'  => 'question',
						'subs_ref_id' => 1234,
					]
				)
			)
		);

		// Delete subscription by two parameters.
		ap_delete_subscribers(
			array(
				'subs_event'  => 'question',
				'subs_ref_id' => 1234,
			)
		);
		$this->assertEquals(
			0, count(
				ap_get_subscribers(
					[
						'subs_event'  => 'question',
						'subs_ref_id' => 1234,
					]
				)
			)
		);

		ap_delete_subscribers(
			array(
				'subs_user_id' => 23466,
			)
		);

		$this->assertEquals( 0, count( ap_get_subscribers( [ 'subs_ref_id' => 23466 ] ) ) );
	}

	/**
	 * @covers ::ap_delete_subscriber
	 */
	public function testDeleteSubscriber() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		ap_new_subscriber( 23459, 'question', 1236 );

		$this->assertEquals( 1, ap_subscribers_count( 'question', 1236 ) );
		ap_delete_subscriber( 1236, 23459, 'question' );

		$this->assertEquals( 0, ap_subscribers_count( 'question', 1236 ) );
	}

	public function testQuestionSubscriptionHook() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Hooks', 'question_subscription' ] ) );

		$this->setRole( 'subscriber' );

		// Check if question created without author set current user as subscriber.
		$id = $this->insert_question( 'Suspendisse aliqua', 'Donec ultricies blandit venenatis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.' );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Hooks', 'answer_subscription' ] ) );

		$this->setRole( 'subscriber' );

		// Check if answer created without author set current user as subscriber.
		$ids = $this->insert_answers(
			array(
				'post_title'   => 'Pellentesque odio purus, egestas ac luctus gravida, rutrum ut quam.',
				'post_content' => 'Pellentesque eget quam dui, sit amet eleifend mauris.',
			)
		);

		// Run action so that ap_after_new_answer hook can trigger.
		do_action( 'ap_processed_new_answer', $ids['answers'][0], get_post( $ids['answers'][0] ) );

		$this->assertFalse( ap_is_user_subscriber( 'answer_' . $ids['answers'][0], $ids['question'] ) );

		// Check if answer author get subscribed.
		$ids = $this->insert_answers(
			array(
				'post_title'   => 'Pellentesque odio purus, egestas ac luctus gravida, rutrum ut quam.',
				'post_content' => 'Pellentesque eget quam dui, sit amet eleifend mauris.',
			), array(
				'post_author' => get_current_user_id(),
			)
		);

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_new_subscriber', [ 'AnsPress_Hooks', 'new_subscriber' ] ) );

		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_get_post( $id )->subscribers );
		ap_new_subscriber( false, 'question', $id );
		ap_new_subscriber( 2324324, 'question', $id );
		ap_new_subscriber( 23232, 'question', $id );
		$this->assertEquals( 3, ap_get_post( $id )->subscribers );
	}

	/**
	 * Check if qameta subscribers count is getting updated on deleting subscribers.
	 *
	 * @covers AnsPress_Hooks::delete_subscribers
	 */
	public function testDeleteSubscribersHook() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_delete_subscribers', [ 'AnsPress_Hooks', 'delete_subscribers' ] ) );

		$id = $this->insert_question();
		ap_new_subscriber( 2345, 'question', $id );
		ap_new_subscriber( 23451, 'question', $id );
		ap_new_subscriber( 23455, 'question', $id );
		$this->assertEquals( 3, ap_get_post( $id )->subscribers );
		ap_delete_subscribers(
			array(
				'subs_ref_id' => $id,
				'subs_event'  => 'question',
			)
		);
		$this->assertEquals( 0, ap_get_post( $id )->subscribers );
	}

	/**
	 * @covers ::ap_esc_subscriber_event
	 */
	public function testEscSubscriberEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 'answer', ap_esc_subscriber_event( 'answer_99899' ) );
		$this->assertEquals( 'answer', ap_esc_subscriber_event( 'answer' ) );
		$this->assertEquals( 'question', ap_esc_subscriber_event( 'question' ) );
		$this->assertEquals( 'question', ap_esc_subscriber_event( 'question_12345' ) );
	}

	/**
	 * @covers ::ap_esc_subscriber_event_id
	 */
	public function testEscSubscriberEventId() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 99899, ap_esc_subscriber_event_id( 'answer_99899' ) );
		$this->assertNotEquals( -100, ap_esc_subscriber_event_id( 'answer_99899' ) );
		$this->assertEquals( 0, ap_esc_subscriber_event_id( 'question' ) );
		$this->assertNotEquals( -1, ap_esc_subscriber_event_id( 'question' ) );
	}

	/**
	 * @covers ::ap_is_user_subscriber
	 */
	public function testAPIsUserSubscriber() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// Test begins.
		$this->setRole( 'subscriber' );
		ap_new_subscriber( get_current_user_id(), 'question', $id );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
		$this->assertFalse( ap_is_user_subscriber( 'question', $id, $user_id ) );
		ap_new_subscriber( $user_id, 'question', $id );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id, $user_id ) );

		// Test after deleting the subscriber.
		ap_delete_subscriber( $id, get_current_user_id(), 'question' );
		$this->assertFalse( ap_is_user_subscriber( 'question', $id ) );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id, $user_id ) );
		ap_delete_subscriber( $id, $user_id, 'question' );
		$this->assertFalse( ap_is_user_subscriber( 'question', $id ) );
		$this->assertFalse( ap_is_user_subscriber( 'question', $id, $user_id ) );
	}

	/**
	 * @covers ::ap_delete_subscribers_cache
	 */
	public function testAPDeleteSubscribersCache() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Set some caches for testing.
		wp_cache_set( 'question_1234', 1, 'ap_subscribers_count' );
		wp_cache_set( 'question_0', 1, 'ap_subscribers_count' );
		wp_cache_set( '_01234', 1, 'ap_subscribers_count' );
		wp_cache_set( '_', 1, 'ap_subscribers_count' );
		$this->assertNotNull( wp_cache_get( 'question_1234', 'ap_subscribers_count' ) );
		$this->assertNotNull( wp_cache_get( 'question_0', 'ap_subscribers_count' ) );
		$this->assertNotNull( wp_cache_get( '_01234', 'ap_subscribers_count' ) );
		$this->assertNotNull( wp_cache_get( '_', 'ap_subscribers_count' ) );

		// Test begins.
		ap_delete_subscribers_cache( 1234, 'question' );
		$this->assertFalse( wp_cache_get( 'question_1234', 'ap_subscribers_count' ) );
		$this->assertFalse( wp_cache_get( 'question_0', 'ap_subscribers_count' ) );
		$this->assertFalse( wp_cache_get( '_01234', 'ap_subscribers_count' ) );
		$this->assertFalse( wp_cache_get( '_', 'ap_subscribers_count' ) );
	}

	/**
	 * @covers ::ap_new_subscriber
	 */
	public function testAPNewSubscriberWithoutPassingUserID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
	}

	/**
	 * @covers ::ap_get_subscriber
	 */
	public function testAPGetSubscriberWithoutPassingUserID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, 'question', $id );
		$this->assertEquals( get_current_user_id(), $subscriber->subs_user_id );
	}

	/**
	 * @covers ::ap_get_subscriber
	 */
	public function testAPGetSubscriberWithoutPassingAnEvent() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, '', $id );
		$this->assertFalse( $subscriber );
	}

	/**
	 * @covers ::ap_get_subscriber
	 */
	public function testAPGetSubscriberWithoutPassingARefID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, 'question', '' );
		$this->assertFalse( $subscriber );
	}

	/**
	 * @covers ::ap_get_subscribers
	 */
	public function testAPGetSubscribersForPassingSubsUserID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( get_current_user_id(), 'question', $id );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_new_subscriber( $user_id, 'question', $id );
		$subscriber = ap_get_subscribers( [ 'subs_user_id' => get_current_user_id() ] );
		$this->assertEquals( 1, count( $subscriber ) );
	}

	/**
	 * @covers ::ap_delete_subscribers
	 */
	public function testAPDeleteSubscribersForPassingEmptyArray() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		// Callback triggered check.
		$callback_triggered = false;
		add_action( 'ap_before_delete_subscribers', function () use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( get_current_user_id(), 'question', $id );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
		$delete = ap_delete_subscribers( [] );
		$this->assertNull( $delete );
		$this->assertFalse( $callback_triggered );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
	}
}
