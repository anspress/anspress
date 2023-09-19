<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestVotes extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	public function testVoteHooks() {
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_answer', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_deleted_votes', [ 'AnsPress_Vote', 'ap_deleted_votes' ], 10, 2 ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Vote', 'vote' ) );
		$this->assertTrue( method_exists( 'AnsPress_Vote', 'delete_votes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Vote', 'ap_deleted_votes' ) );
	}

	/**
	 * @covers ::ap_vote_insert
	 */
	public function testAPVoteInsert() {
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Test beofre inserting the vote.
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$get_vote = (array) $get_vote;
		$this->assertArrayNotHasKey( 'vote_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayNotHasKey( 'vote_type', $get_vote );
		$this->assertArrayNotHasKey( 'vote_value', $get_vote );
		$this->assertArrayNotHasKey( 'vote_date', $get_vote );

		// Test after inserting the vote.
		ap_vote_insert( $id, $user_id );
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$get_vote = (array) $get_vote;
		$this->assertArrayHasKey( 'vote_id', $get_vote );
		$this->assertArrayHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayHasKey( 'vote_type', $get_vote );
		$this->assertArrayHasKey( 'vote_value', $get_vote );
		$this->assertArrayHasKey( 'vote_date', $get_vote );
		$this->assertEquals( 'vote', $get_vote['vote_type'] );
		$this->assertEquals( $id, $get_vote['vote_post_id'] );
		$this->assertEquals( $user_id, $get_vote['vote_user_id'] );
		$this->logout();

		// Test on new question.
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $id, $user_id, 'vote', $new_user_id, 101, current_time( 'mysql' ) );
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$get_vote = (array) $get_vote;
		$this->assertEquals( 'vote', $get_vote['vote_type'] );
		$this->assertEquals( $id, $get_vote['vote_post_id'] );
		$this->assertEquals( $user_id, $get_vote['vote_user_id'] );
		$this->assertEquals( $new_user_id, $get_vote['vote_rec_user'] );
		$this->assertEquals( 101, $get_vote['vote_value'] );
		$this->assertEquals( current_time( 'mysql' ), $get_vote['vote_date'] );

		// Test on new question.
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $id, $user_id, 'flag', $new_user_id, 10 );
		$get_vote = ap_get_vote( $id, $user_id, 'flag' );
		$get_vote = (array) $get_vote;
		$this->assertEquals( 'flag', $get_vote['vote_type'] );
		$this->assertNotEquals( 'vote', $get_vote['vote_type'] );
		$this->assertEquals( $id, $get_vote['vote_post_id'] );
		$this->assertEquals( $user_id, $get_vote['vote_user_id'] );
		$this->assertEquals( $new_user_id, $get_vote['vote_rec_user'] );
		$this->assertEquals( 10, $get_vote['vote_value'] );
		$this->assertNotEquals( 101, $get_vote['vote_value'] );
		$this->assertEquals( current_time( 'mysql' ), $get_vote['vote_date'] );
	}

	/**
	 * @covers ::ap_is_user_voted
	 */
	public function testAPIsUserVoted() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_is_user_voted( $id ) );

		// Test after inserting a vote.
		ap_vote_insert( $id, get_current_user_id() );
		$this->assertTrue( ap_is_user_voted( $id ) );
		$this->logout();

		// Test for inserting a flag.
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_is_user_voted( $id ) );
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		$this->assertFalse( ap_is_user_voted( $id ) );
		$this->assertTrue( ap_is_user_voted( $id, 'flag' ) );

		// New test.
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->assertFalse( ap_is_user_voted( $id, 'vote', $user_id ) );
		$this->assertFalse( ap_is_user_voted( $id, 'vote', $new_user_id ) );
		$this->assertFalse( ap_is_user_voted( $id, 'flag', $user_id ) );
		$this->assertFalse( ap_is_user_voted( $id, 'flag', $new_user_id ) );
		ap_vote_insert( $id, $user_id, 'vote', $new_user_id, 10 );
		$this->assertTrue( ap_is_user_voted( $id, 'vote', $user_id ) );
		$this->assertFalse( ap_is_user_voted( $id, 'flag', $user_id ) );
		ap_vote_insert( $id, $new_user_id, 'vote', $user_id, 10 );
		$this->assertTrue( ap_is_user_voted( $id, 'vote', $new_user_id ) );
		$this->assertFalse( ap_is_user_voted( $id, 'flag', $new_user_id ) );
		ap_vote_insert( $id, $user_id, 'flag', $new_user_id, 10 );
		$this->assertTrue( ap_is_user_voted( $id, 'vote', $user_id ) );
		$this->assertTrue( ap_is_user_voted( $id, 'flag', $user_id ) );
		ap_vote_insert( $id, $new_user_id, 'flag', $user_id, 10 );
		$this->assertTrue( ap_is_user_voted( $id, 'vote', $new_user_id ) );
		$this->assertTrue( ap_is_user_voted( $id, 'flag', $new_user_id ) );
	}

	/**
	 * @covers ::ap_delete_vote
	 */
	public function testAPDeleteVote() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertEquals( 0, ap_delete_vote( $id ) );

		// Test after adding the vote.
		ap_vote_insert( $id, get_current_user_id() );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$get_vote = (array) $get_vote;
		$this->assertArrayHasKey( 'vote_id', $get_vote );
		$this->assertArrayHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayHasKey( 'vote_type', $get_vote );
		$this->assertArrayHasKey( 'vote_value', $get_vote );
		$this->assertArrayHasKey( 'vote_date', $get_vote );
		ap_delete_vote( $id );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$get_vote = (array) $get_vote;
		$this->assertArrayNotHasKey( 'vote_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayNotHasKey( 'vote_type', $get_vote );
		$this->assertArrayNotHasKey( 'vote_value', $get_vote );
		$this->assertArrayNotHasKey( 'vote_date', $get_vote );

		// Test after adding the flag.
		$id = $this->insert_question();
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$get_vote = (array) $get_vote;
		$this->assertArrayHasKey( 'vote_id', $get_vote );
		$this->assertArrayHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayHasKey( 'vote_type', $get_vote );
		$this->assertArrayHasKey( 'vote_value', $get_vote );
		$this->assertArrayHasKey( 'vote_date', $get_vote );
		ap_delete_vote( $id );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$get_vote = (array) $get_vote;
		$this->assertArrayHasKey( 'vote_id', $get_vote );
		$this->assertArrayHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayHasKey( 'vote_type', $get_vote );
		$this->assertArrayHasKey( 'vote_value', $get_vote );
		$this->assertArrayHasKey( 'vote_date', $get_vote );
		ap_delete_vote( $id, get_current_user_id(), 'flag' );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$get_vote = (array) $get_vote;
		$this->assertArrayNotHasKey( 'vote_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_post_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_user_id', $get_vote );
		$this->assertArrayNotHasKey( 'vote_rec_user', $get_vote );
		$this->assertArrayNotHasKey( 'vote_type', $get_vote );
		$this->assertArrayNotHasKey( 'vote_value', $get_vote );
		$this->assertArrayNotHasKey( 'vote_date', $get_vote );
	}

	/**
	 * @covers AnsPress_Vote::delete_votes
	 */
	public function testAnsPressVoteDeleteVotes() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_vote_insert( $id, get_current_user_id() );
		$this->setRole( 'subscriber' );
		ap_vote_insert( $id, get_current_user_id() );
		$this->setRole( 'ap_participant' );
		ap_vote_insert( $id, get_current_user_id() );
		$this->logout();

		// Test on get_votes before the actual test is applied.
		$get_votes = ap_get_votes();
		foreach ( $get_votes as $get_vote ) {
			$get_vote = (array) $get_vote;
			$this->assertArrayHasKey( 'vote_id', $get_vote );
			$this->assertArrayHasKey( 'vote_post_id', $get_vote );
			$this->assertArrayHasKey( 'vote_user_id', $get_vote );
			$this->assertArrayHasKey( 'vote_rec_user', $get_vote );
			$this->assertArrayHasKey( 'vote_type', $get_vote );
			$this->assertArrayHasKey( 'vote_value', $get_vote );
			$this->assertArrayHasKey( 'vote_date', $get_vote );
		}

		// Actual test for this method.
		AnsPress_Vote::delete_votes( $id );
		$this->assertNotEmpty( $get_votes );
		$get_votes = ap_get_votes();
		$this->assertEmpty( $get_votes );
	}

	/**
	 * @covers AnsPress_Vote::ap_deleted_votes
	 */
	public function testAnsPressVoteAPDeletedVotes() {
		$id = $this->insert_question();

		// Test for adding first vote and flag.
		$this->setRole( 'subscriber' );
		ap_add_post_vote( $id, get_current_user_id() );
		ap_update_votes_count( $id );
		ap_add_flag( $id, get_current_user_id() );
		ap_update_flags_count( $id );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 1, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 1, $get_qameta->votes_net );
		$this->assertEquals( 1, $get_qameta->flags );

		// Test for adding second vote and flag.
		$this->setRole( 'subscriber' );
		ap_add_post_vote( $id, get_current_user_id(), false );
		ap_update_votes_count( $id );
		ap_add_flag( $id, get_current_user_id() );
		ap_update_flags_count( $id );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 1, $get_qameta->votes_up );
		$this->assertEquals( 1, $get_qameta->votes_down );
		$this->assertEquals( 2, $get_qameta->votes_net );
		$this->assertEquals( 2, $get_qameta->flags );

		// Actual test for this method.
		ap_delete_votes( $id );
		AnsPress_Vote::ap_deleted_votes( $id, 'vote' );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 2, $get_qameta->flags );
		ap_delete_votes( $id, 'flag' );
		AnsPress_Vote::ap_deleted_votes( $id, 'flag' );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 0, $get_qameta->flags );
	}
}
