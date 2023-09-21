<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestReputation extends TestCase {

	use \Anspress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_insert_reputation
	 */
	public function testAPInsertReputation() {
		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_insert_reputation( '', $id->q ) );
		$this->assertFalse( ap_insert_reputation( 'ask', $id->q, 0 ) );
		$this->assertIsInt( ap_insert_reputation( 'ask', $id->q ) );
		$this->assertIsInt( ap_insert_reputation( 'answer', $id->a ) );
		$this->assertIsInt( ap_insert_reputation( 'select_answer', $id->a ) );
		$this->assertIsInt( ap_insert_reputation( 'best_answer', $id->a ) );
	}

	/**
	 * @covers ::ap_get_reputation
	 */
	public function testAPGetReputation() {
		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertNull( ap_get_reputation( 'ask', $id->q ) );
		$this->assertNull( ap_get_reputation( 'answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'best_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'select_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'received_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_down', $id->q ) );

		// After inserting reputation.
		ap_insert_reputation( '', $id->q );
		$this->assertNull( ap_get_reputation( '', $id->q ) );
		ap_insert_reputation( 'ask', $id->q );
		$get_reputation = ap_get_reputation( 'ask', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'ask', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'best_answer', $id->a );
		$get_reputation = ap_get_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'best_answer', $get_reputation->rep_event );
		$this->assertEquals( $id->a, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'best_answer', $id->a );
		$get_reputation = ap_get_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'best_answer', $get_reputation->rep_event );
		$this->assertEquals( $id->a, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'received_vote_up', $id->q );
		$get_reputation = ap_get_reputation( 'received_vote_up', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'received_vote_up', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'received_vote_down', $id->q );
		$get_reputation = ap_get_reputation( 'received_vote_down', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'received_vote_down', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'given_vote_up', $id->q );
		$get_reputation = ap_get_reputation( 'given_vote_up', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'given_vote_up', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
		ap_insert_reputation( 'given_vote_down', $id->q );
		$get_reputation = ap_get_reputation( 'given_vote_down', $id->q );
		$this->assertNotEmpty( $get_reputation );
		$this->assertEquals( get_current_user_id(), $get_reputation->rep_user_id );
		$this->assertEquals( 'given_vote_down', $get_reputation->rep_event );
		$this->assertEquals( $id->q, $get_reputation->rep_ref_id );
		$get_reputation = (array) $get_reputation;
		$this->assertArrayHasKey( 'rep_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_user_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_event', $get_reputation );
		$this->assertArrayHasKey( 'rep_ref_id', $get_reputation );
		$this->assertArrayHasKey( 'rep_date', $get_reputation );
	}

	/**
	 * @covers ::ap_delete_reputation
	 */
	public function testAPDeleteReputation() {
		$id = $this->insert_answer();

		// Test begins.
		$this->setRole( 'subscriber' );
		$this->assertEquals( 0, ap_delete_reputation( 'question', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'best_answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'select_answer', $id->a ) );
		$this->assertEquals( 0, ap_delete_reputation( 'received_vote_up', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'received_vote_down', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'given_vote_up', $id->q ) );
		$this->assertEquals( 0, ap_delete_reputation( 'given_vote_down', $id->q ) );

		// After inserting reputation and deleting them.
		ap_insert_reputation( 'question', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'question', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'question', $id->q ) );
		$this->assertNull( ap_get_reputation( 'question', $id->q ) );
		ap_insert_reputation( 'answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'answer', $id->a ) );
		ap_insert_reputation( 'best_answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'best_answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'best_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'best_answer', $id->a ) );
		ap_insert_reputation( 'select_answer', $id->a );
		$this->assertNotEmpty( ap_get_reputation( 'select_answer', $id->a ) );
		$this->assertEquals( 1, ap_delete_reputation( 'select_answer', $id->a ) );
		$this->assertNull( ap_get_reputation( 'select_answer', $id->a ) );
		ap_insert_reputation( 'received_vote_up', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'received_vote_up', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'received_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_up', $id->q ) );
		ap_insert_reputation( 'received_vote_down', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'received_vote_down', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'received_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'received_vote_down', $id->q ) );
		ap_insert_reputation( 'given_vote_up', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'given_vote_up', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'given_vote_up', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_up', $id->q ) );
		ap_insert_reputation( 'given_vote_down', $id->q );
		$this->assertNotEmpty( ap_get_reputation( 'given_vote_down', $id->q ) );
		$this->assertEquals( 1, ap_delete_reputation( 'given_vote_down', $id->q ) );
		$this->assertNull( ap_get_reputation( 'given_vote_down', $id->q ) );
	}

	/**
	 * @covers ::ap_register_reputation_event
	 */
	public function testAPRegisterReputationEvent() {
		// First test.
		$args = [
			'label'         => 'Test reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 8,
			'rep_events_id' => 11,
		];
		ap_register_reputation_event( 'test_register_reputation_event', $args );
		$reputation_events = anspress()->reputation_events;
		$this->assertArrayNotHasKey( 'test', $reputation_events );
		$this->assertArrayHasKey( 'test_register_reputation_event', $reputation_events );
		$test_register_reputation_event = $reputation_events[ 'test_register_reputation_event' ];
		$this->assertEquals( 'Test reputation event register', $test_register_reputation_event['label'] );
		$this->assertEquals( 'Lorem ipsum dolor sit amet', $test_register_reputation_event['description'] );
		$this->assertEquals( 'apicon-test-reputation', $test_register_reputation_event['icon'] );
		$this->assertEquals( 'Reputation registered', $test_register_reputation_event['activity'] );
		$this->assertEquals( '', $test_register_reputation_event['parent'] );
		$this->assertEquals( 8, $test_register_reputation_event['points'] );
		$this->assertEquals( 11, $test_register_reputation_event['rep_events_id'] );

		// Second test.
		$args = [
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Reputation',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		];
		ap_register_reputation_event( 'register_reputation_event', $args );
		$reputation_events = anspress()->reputation_events;
		$this->assertArrayNotHasKey( 'test', $reputation_events );
		$this->assertArrayHasKey( 'register_reputation_event', $reputation_events );
		$register_reputation_event = $reputation_events[ 'register_reputation_event' ];
		$this->assertEquals( 'Reputation event register', $register_reputation_event['label'] );
		$this->assertEquals( 'Lorem ipsum dolor sit amet', $register_reputation_event['description'] );
		$this->assertEquals( 'apicon-reputation', $register_reputation_event['icon'] );
		$this->assertEquals( 'Reputation', $register_reputation_event['activity'] );
		$this->assertEquals( '', $register_reputation_event['parent'] );
		$this->assertEquals( 12, $register_reputation_event['points'] );
		$this->assertEquals( 12, $register_reputation_event['rep_events_id'] );
	}

	/**
	 * @covers ::ap_get_reputation_events()
	 */
	public function testAPGetReputationEvents() {
		$reputation_events = ap_get_reputation_events();
		$this->assertArrayHasKey( 'register', $reputation_events );
		$this->assertArrayHasKey( 'ask', $reputation_events );
		$this->assertArrayHasKey( 'answer', $reputation_events );
		$this->assertArrayHasKey( 'comment', $reputation_events );
		$this->assertArrayHasKey( 'select_answer', $reputation_events );
		$this->assertArrayHasKey( 'best_answer', $reputation_events );
		$this->assertArrayHasKey( 'received_vote_up', $reputation_events );
		$this->assertArrayHasKey( 'received_vote_down', $reputation_events );
		$this->assertArrayHasKey( 'given_vote_up', $reputation_events );
		$this->assertArrayHasKey( 'given_vote_down', $reputation_events );

		// Test for the inner array.
		foreach ( $reputation_events as $reputation_event ) {
			$this->assertArrayHasKey( 'icon', $reputation_event );
			$this->assertArrayHasKey( 'parent', $reputation_event );
			$this->assertArrayHasKey( 'rep_events_id', $reputation_event );
			$this->assertArrayHasKey( 'label', $reputation_event );
			$this->assertArrayHasKey( 'description', $reputation_event );
			$this->assertArrayHasKey( 'activity', $reputation_event );
			$this->assertArrayHasKey( 'points', $reputation_event );
		}
	}

	/**
	 * @covers ::ap_get_reputation_event_points
	 */
	public function testAPGetReputationEventPoints() {
		// Test for non existance events.
		$this->assertEquals( 0, ap_get_reputation_event_points( 'test' ) );
		$this->assertEquals( 0, ap_get_reputation_event_points( 'new_event' ) );

		// Test for pre-existing events.
		$this->assertEquals( 10, ap_get_reputation_event_points( 'register' ) );
		$this->assertEquals( 2, ap_get_reputation_event_points( 'ask' ) );
		$this->assertEquals( 5, ap_get_reputation_event_points( 'answer' ) );
		$this->assertEquals( 2, ap_get_reputation_event_points( 'comment' ) );
		$this->assertEquals( 2, ap_get_reputation_event_points( 'select_answer' ) );
		$this->assertEquals( 10, ap_get_reputation_event_points( 'best_answer' ) );
		$this->assertEquals( 10, ap_get_reputation_event_points( 'received_vote_up' ) );
		$this->assertEquals( -2, ap_get_reputation_event_points( 'received_vote_down' ) );
		$this->assertEquals( 0, ap_get_reputation_event_points( 'given_vote_up' ) );
		$this->assertEquals( 0, ap_get_reputation_event_points( 'given_vote_down' ) );

		// Test for new reputation event.
		$args = [
			'label'         => 'Test reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 8,
			'rep_events_id' => 11,
		];
		ap_register_reputation_event( 'test_register_reputation_event', $args );
		$this->assertEquals( 8, ap_get_reputation_event_points( 'test_register_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Reputation',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		];
		ap_register_reputation_event( 'register_reputation_event', $args );
		$this->assertEquals( 12, ap_get_reputation_event_points( 'register_reputation_event' ) );
	}

	/**
	 * @covers ::ap_get_reputation_event_icon
	 */
	public function testAPGetReputationEventIcon() {
		// Test for non existance events.
		$this->assertEquals( 'apicon-reputation', ap_get_reputation_event_icon( 'test' ) );
		$this->assertEquals( 'apicon-reputation', ap_get_reputation_event_icon( 'new_event' ) );

		// Test for pre-existing events.
		$this->assertEquals( '', ap_get_reputation_event_icon( 'register' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'ask' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'answer' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'comment' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'select_answer' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'best_answer' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'received_vote_up' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'received_vote_down' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'given_vote_up' ) );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'given_vote_down' ) );

		// Test for new reputation event.
		$args = [
			'label'         => 'Test reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 8,
			'rep_events_id' => 11,
		];
		ap_register_reputation_event( 'test_register_reputation_event', $args );
		$this->assertEquals( 'apicon-test-reputation', ap_get_reputation_event_icon( 'test_register_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Reputation',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		];
		ap_register_reputation_event( 'register_reputation_event', $args );
		$this->assertEquals( 'apicon-reputation', ap_get_reputation_event_icon( 'register_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => '',
			'activity'      => 'Reputation',
			'parent'        => '',
			'points'        => 15,
			'rep_events_id' => 13,
		];
		ap_register_reputation_event( 'new_reputation_event', $args );
		$this->assertEquals( '', ap_get_reputation_event_icon( 'new_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'activity'      => 'Reputation',
			'points'        => 15,
			'rep_events_id' => 14,
		];
		ap_register_reputation_event( 'latest_reputation_event', $args );
		$this->assertEquals( 'apicon-reputation', ap_get_reputation_event_icon( 'latest_reputation_event' ) );
	}

	/**
	 * @covers ::ap_get_reputation_event_activity
	 */
	public function testAPGetReputationEventActivity() {
		// Test for non existance events.
		$this->assertEquals( 'test', ap_get_reputation_event_activity( 'test' ) );
		$this->assertEquals( 'new_event', ap_get_reputation_event_activity( 'new_event' ) );

		// Test for pre-existing events.
		$this->assertEquals( 'Registered', ap_get_reputation_event_activity( 'register' ) );
		$this->assertEquals( 'Asked a question', ap_get_reputation_event_activity( 'ask' ) );
		$this->assertEquals( 'Posted an answer', ap_get_reputation_event_activity( 'answer' ) );
		$this->assertEquals( 'Commented on a post', ap_get_reputation_event_activity( 'comment' ) );
		$this->assertEquals( 'Selected an answer as best', ap_get_reputation_event_activity( 'select_answer' ) );
		$this->assertEquals( 'Answer was selected as best', ap_get_reputation_event_activity( 'best_answer' ) );
		$this->assertEquals( 'Received an upvote', ap_get_reputation_event_activity( 'received_vote_up' ) );
		$this->assertEquals( 'Received a down vote', ap_get_reputation_event_activity( 'received_vote_down' ) );
		$this->assertEquals( 'Given an up vote', ap_get_reputation_event_activity( 'given_vote_up' ) );
		$this->assertEquals( 'Given a down vote', ap_get_reputation_event_activity( 'given_vote_down' ) );

		// Test for new reputation event.
		$args = [
			'label'         => 'Test reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 8,
			'rep_events_id' => 11,
		];
		ap_register_reputation_event( 'test_register_reputation_event', $args );
		$this->assertEquals( 'Reputation registered', ap_get_reputation_event_activity( 'test_register_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event register',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-reputation',
			'activity'      => 'Reputation created',
			'parent'        => '',
			'points'        => 12,
			'rep_events_id' => 12,
		];
		ap_register_reputation_event( 'register_reputation_event', $args );
		$this->assertEquals( 'Reputation created', ap_get_reputation_event_activity( 'register_reputation_event' ) );
		$args = [
			'label'         => 'Reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => '',
			'activity'      => 'Reputation updated',
			'parent'        => '',
			'points'        => 15,
			'rep_events_id' => 13,
		];
		ap_register_reputation_event( 'new_reputation_event', $args );
		$this->assertEquals( 'Reputation updated', ap_get_reputation_event_activity( 'new_reputation_event' ) );
	}

	/**
	 * @covers ::ap_get_user_reputation
	 */
	public function testAPGetUserReputation() {
		// Test for manually adding the user reputation.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $question_id );
		ap_insert_reputation( 'ask', $question_id, $post->post_author );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$post = get_post( $answer_id );
		ap_insert_reputation( 'answer', $answer_id, $post->post_author );
		$this->assertEquals( 7, ap_get_user_reputation( $user_id ) );
		$new_question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$post = get_post( $new_question_id );
		ap_insert_reputation( 'ask', $new_question_id, $post->post_author );
		$this->assertEquals( 9, ap_get_user_reputation( $user_id ) );
		ap_insert_reputation( 'best_answer', $answer_id, $post->post_author );
		$this->assertEquals( 19, ap_get_user_reputation( $user_id ) );
		ap_insert_reputation( 'received_vote_up', $question_id, get_post( $question_id )->post_author );
		$this->assertEquals( 29, ap_get_user_reputation( $user_id ) );
		ap_insert_reputation( 'given_vote_up', $question_id );
		$this->assertEquals( 29, ap_get_user_reputation( $user_id ) );

		// Test on the group.
		$get_user_reputation = ap_get_user_reputation( $user_id, true );
		$this->assertEquals( 0, $get_user_reputation['register'] );
		$this->assertEquals( 4, $get_user_reputation['ask'] );
		$this->assertEquals( 5, $get_user_reputation['answer'] );
		$this->assertEquals( 0, $get_user_reputation['comment'] );
		$this->assertEquals( 0, $get_user_reputation['select_answer'] );
		$this->assertEquals( 10, $get_user_reputation['best_answer'] );
		$this->assertEquals( 10, $get_user_reputation['received_vote_up'] );
		$this->assertEquals( 0, $get_user_reputation['received_vote_down'] );
		$this->assertEquals( 0, $get_user_reputation['given_vote_up'] );
		$this->assertEquals( 0, $get_user_reputation['given_vote_down'] );
	}
}
