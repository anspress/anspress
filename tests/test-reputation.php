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
}
