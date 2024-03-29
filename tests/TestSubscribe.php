<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestSubscribe extends TestCase {

	use Testcases\Common;

	public function set_up()
	{
		parent::set_up();

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->ap_subscribers}" );
	}

	public function testNewSubscriberForNonExistingUser() {
		$id = ap_new_subscriber( 456, 'question', 222 );
		$this->assertFalse( $id );
	}

	public function testNewSubscriberForUser() {
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;

		$id = ap_new_subscriber( $user_id, 'question', $question_id );
		$this->assertNotFalse( $id );

		global $wpdb;
		$subscriber = $wpdb->get_row( "SELECT * FROM {$wpdb->ap_subscribers} WHERE subs_user_id = $user_id AND subs_event = 'question' AND subs_ref_id = {$question_id}" );

		$this->assertSame( $user_id, (int) $subscriber->subs_user_id );
		$this->assertSame( 'question', $subscriber->subs_event );
		$this->assertSame( $question_id, (int) $subscriber->subs_ref_id );
	}

	public function testSubscribersCount() {
		$question_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;

		$answer_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'answer' ) )
			->ID;

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'question',
			$question_id
		);

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'question',
			$question_id
		);

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'question',
			$question_id
		);


		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'answer',
			$answer_id
		);

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'answer',
			$answer_id
		);

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'answer',
			$answer_id
		);

		$count = ap_subscribers_count( 'question', $question_id );
		$this->assertEquals( 3, $count );

		$count = ap_subscribers_count( 'answer', $answer_id );
		$this->assertEquals( 3, $count );

		$count = ap_subscribers_count( '', $question_id );
		$this->assertEquals( 3, $count );
	}

	public function testGetSubscribers() {
		$question_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;

		$answer_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'answer' ) )
			->ID;

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'question',
			$question_id
		);

		ap_new_subscriber(
			$this->factory()->user->create_and_get()->ID,
			'answer',
			$answer_id
		);

		$this->assertEquals(
			1,
			count(
				ap_get_subscribers(
					[
						'subs_event'  => 'question',
						'subs_ref_id' => $question_id,
					]
				)
			)
		);

		$this->assertEquals(
			1,
			count(
				ap_get_subscribers(
					[
						'subs_event'  => 'answer',
						'subs_ref_id' => $answer_id,
					]
				)
			)
		);
	}

	public function testDeleteSubscribers() {
		$question_id = $this->factory()
				->post->create_and_get( array( 'post_type' => 'question' ) )
				->ID;

		$user_id = $this->factory()->user->create_and_get()->ID;

		$subscriber_id = ap_new_subscriber(
			$user_id,
			'question',
			$question_id
		);

		$subscriber = ap_get_subscriber( $user_id, 'question', $question_id );

		// Delete subscription by all three parameters.
		ap_delete_subscribers(
			array(
				'subs_event'   => 'question',
				'subs_ref_id'  => $subscriber->subs_ref_id,
				'subs_user_id' => $subscriber->subs_user_id,
			)
		);

		$this->assertFalse( ap_get_subscriber( $subscriber_id ) );
	}

	/**
	 * @covers ::ap_delete_subscriber
	 */
	public function testDeleteSubscriber() {
		$question_id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;

		$user_id = $this->factory()->user->create_and_get()->ID;

		ap_new_subscriber(
			$user_id,
			'question',
			$question_id
		);

		$this->assertEquals( 1, ap_subscribers_count( 'question', $question_id ) );
		ap_delete_subscriber( $question_id, $user_id, 'question' );

		$this->assertEquals( 0, ap_subscribers_count( 'question', 1236 ) );
	}

	public function testQuestionSubscriptionHook() {
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
	 */
	public function testNewSubscriberHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_new_subscriber', [ 'AnsPress_Hooks', 'new_subscriber' ] ) );

		$this->setRole( 'subscriber' );
		$id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;
		$this->assertEquals( 1, ap_get_post( $id )->subscribers );
		ap_new_subscriber( false, 'question', $id );
		ap_new_subscriber( $this->factory()->user->create_and_get()->ID, 'question', $id );
		$this->assertEquals( 2, ap_get_post( $id )->subscribers );
	}

	/**
	 * Check if qameta subscribers count is getting updated on deleting subscribers.
	 */
	public function testDeleteSubscribersHook() {
		// Check hook exists.
		$this->assertEquals( 10, has_action( 'ap_delete_subscribers', [ 'AnsPress_Hooks', 'delete_subscribers' ] ) );

		$id = $this->factory()
			->post->create_and_get( array( 'post_type' => 'question' ) )
			->ID;

		ap_new_subscriber( $this->factory()->user->create_and_get()->ID, 'question', $id );
		ap_new_subscriber( $this->factory()->user->create_and_get()->ID, 'question', $id );
		ap_new_subscriber( $this->factory()->user->create_and_get()->ID, 'question', $id );

		$this->assertEquals( 3, ap_get_post( $id )->subscribers );
		ap_delete_subscribers(
			array(
				'subs_ref_id' => $id,
				'subs_event'  => 'question',
			)
		);
		$this->assertEquals( 0, ap_get_post( $id )->subscribers );
	}

	public function testEscSubscriberEvent() {
		$this->assertEquals( 'answer', ap_esc_subscriber_event( 'answer_99899' ) );
		$this->assertEquals( 'answer', ap_esc_subscriber_event( 'answer' ) );
		$this->assertEquals( 'question', ap_esc_subscriber_event( 'question' ) );
		$this->assertEquals( 'question', ap_esc_subscriber_event( 'question_12345' ) );
	}

	public function testEscSubscriberEventId() {
		$this->assertEquals( 99899, ap_esc_subscriber_event_id( 'answer_99899' ) );
		$this->assertNotEquals( -100, ap_esc_subscriber_event_id( 'answer_99899' ) );
		$this->assertEquals( 0, ap_esc_subscriber_event_id( 'question' ) );
		$this->assertNotEquals( -1, ap_esc_subscriber_event_id( 'question' ) );
	}

	public function testAPIsUserSubscriber() {
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

	public function testAPNewSubscriberWithoutPassingUserID() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$this->assertTrue( ap_is_user_subscriber( 'question', $id ) );
	}

	public function testAPGetSubscriberWithoutPassingUserID() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, 'question', $id );
		$this->assertEquals( get_current_user_id(), $subscriber->subs_user_id );
	}

	public function testAPGetSubscriberWithoutPassingAnEvent() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, '', $id );
		$this->assertFalse( $subscriber );
	}

	public function testAPGetSubscriberWithoutPassingARefID() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( false, 'question', $id );
		$subscriber = ap_get_subscriber( false, 'question', '' );
		$this->assertFalse( $subscriber );
	}

	public function testAPGetSubscribersForPassingSubsUserID() {

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_new_subscriber( get_current_user_id(), 'question', $id );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_new_subscriber( $user_id, 'question', $id );
		$subscriber = ap_get_subscribers( [ 'subs_user_id' => get_current_user_id() ] );
		$this->assertEquals( 1, count( $subscriber ) );
	}
}
