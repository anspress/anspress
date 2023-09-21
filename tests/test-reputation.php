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
	}
}
