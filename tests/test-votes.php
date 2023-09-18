<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestVotes extends TestCase {

	use AnsPress\Tests\Testcases\Common;


	public function testVoteHooks() {
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_answer', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_deleted_votes', [ 'AnsPress_Vote', 'ap_deleted_votes' ], 10, 2 ) );
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
}
