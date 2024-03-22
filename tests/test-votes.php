<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestVotes extends TestCase {

	use Testcases\Common;

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Test beofre inserting the vote.
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$this->assertFalse( $get_vote );

		// Test after inserting the vote.
		ap_vote_insert( $id, $user_id );
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$this->assertObjectHasProperty( 'vote_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_post_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_user_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_rec_user', $get_vote );
		$this->assertObjectHasProperty( 'vote_type', $get_vote );
		$this->assertObjectHasProperty( 'vote_value', $get_vote );
		$this->assertObjectHasProperty( 'vote_date', $get_vote );
		$this->assertEquals( 'vote', $get_vote->vote_type );
		$this->assertEquals( $id, $get_vote->vote_post_id );
		$this->assertEquals( $user_id, $get_vote->vote_user_id );
		$this->logout();

		// Test on new question.
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $id, $user_id, 'vote', $new_user_id, 101, current_time( 'mysql' ) );
		$get_vote = ap_get_vote( $id, $user_id, 'vote' );
		$this->assertEquals( 'vote', $get_vote->vote_type );
		$this->assertEquals( $id, $get_vote->vote_post_id );
		$this->assertEquals( $user_id, $get_vote->vote_user_id );
		$this->assertEquals( $new_user_id, $get_vote->vote_rec_user );
		$this->assertEquals( 101, $get_vote->vote_value );
		$this->assertEquals( current_time( 'mysql' ), $get_vote->vote_date );

		// Test on new question.
		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $id, $user_id, 'flag', $new_user_id, 10 );
		$get_vote = ap_get_vote( $id, $user_id, 'flag' );
		$this->assertEquals( 'flag', $get_vote->vote_type );
		$this->assertNotEquals( 'vote', $get_vote->vote_type );
		$this->assertEquals( $id, $get_vote->vote_post_id );
		$this->assertEquals( $user_id, $get_vote->vote_user_id );
		$this->assertEquals( $new_user_id, $get_vote->vote_rec_user );
		$this->assertEquals( 10, $get_vote->vote_value );
		$this->assertNotEquals( 101, $get_vote->vote_value );
		$this->assertEquals( current_time( 'mysql' ), $get_vote->vote_date );
	}

	/**
	 * @covers ::ap_is_user_voted
	 */
	public function testAPIsUserVoted() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertEquals( 0, ap_delete_vote( $id ) );

		// Test after adding the vote.
		ap_vote_insert( $id, get_current_user_id() );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$this->assertObjectHasProperty( 'vote_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_post_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_user_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_rec_user', $get_vote );
		$this->assertObjectHasProperty( 'vote_type', $get_vote );
		$this->assertObjectHasProperty( 'vote_value', $get_vote );
		$this->assertObjectHasProperty( 'vote_date', $get_vote );
		$delete = ap_delete_vote( $id );
		$this->assertEquals( 1, $delete );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$this->assertFalse( $get_vote );

		// Test after adding the flag.
		$id = $this->insert_question();
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$this->assertObjectHasProperty( 'vote_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_post_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_user_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_rec_user', $get_vote );
		$this->assertObjectHasProperty( 'vote_type', $get_vote );
		$this->assertObjectHasProperty( 'vote_value', $get_vote );
		$this->assertObjectHasProperty( 'vote_date', $get_vote );
		$delete = ap_delete_vote( $id );
		$this->assertEquals( 0, $delete );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$this->assertObjectHasProperty( 'vote_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_post_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_user_id', $get_vote );
		$this->assertObjectHasProperty( 'vote_rec_user', $get_vote );
		$this->assertObjectHasProperty( 'vote_type', $get_vote );
		$this->assertObjectHasProperty( 'vote_value', $get_vote );
		$this->assertObjectHasProperty( 'vote_date', $get_vote );
		$delete = ap_delete_vote( $id, get_current_user_id(), 'flag' );
		$this->assertEquals( 1, $delete );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$this->assertFalse( $get_vote );

		$id = $this->insert_question();
		// Inserting votes.
		ap_vote_insert( $id, get_current_user_id() );
		ap_vote_insert( $id, get_current_user_id() );
		ap_vote_insert( $id, get_current_user_id() );
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		// Delete vote and test.
		$delete = ap_delete_vote( $id );
		$this->assertEquals( 3, $delete );
		$delete = ap_delete_vote( $id, get_current_user_id(), 'flag' );
		$this->assertEquals( 2, $delete );
	}

	/**
	 * @covers AnsPress_Vote::delete_votes
	 */
	public function testAnsPressVoteDeleteVotes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

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
			$this->assertObjectHasProperty( 'vote_id', $get_vote );
			$this->assertObjectHasProperty( 'vote_post_id', $get_vote );
			$this->assertObjectHasProperty( 'vote_user_id', $get_vote );
			$this->assertObjectHasProperty( 'vote_rec_user', $get_vote );
			$this->assertObjectHasProperty( 'vote_type', $get_vote );
			$this->assertObjectHasProperty( 'vote_value', $get_vote );
			$this->assertObjectHasProperty( 'vote_date', $get_vote );
		}

		// Actual test for this method.
		\AnsPress_Vote::delete_votes( $id );
		$this->assertNotEmpty( $get_votes );
		$get_votes = ap_get_votes();
		$this->assertEmpty( $get_votes );
	}

	/**
	 * @covers AnsPress_Vote::ap_deleted_votes
	 */
	public function testAnsPressVoteAPDeletedVotes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

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
		\AnsPress_Vote::ap_deleted_votes( $id, 'vote' );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 2, $get_qameta->flags );
		ap_delete_votes( $id, 'flag' );
		\AnsPress_Vote::ap_deleted_votes( $id, 'flag' );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 0, $get_qameta->flags );
	}

	/**
	 * @covers ::ap_get_votes
	 */
	public function testAPGetVotes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$new_id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// Test before adding a vote and flag.
		$this->assertEmpty( ap_get_votes() );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => $id ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => $new_id ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => array( $id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => array( $new_id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => array( $id, $new_id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => get_current_user_id() ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => $user_id ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id() ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => array( $user_id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id(), $user_id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => 'vote' ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => 'flag' ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => array( 'vote' ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => array( 'flag' ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => array( 'vote', 'flag' ) ) ) );

		// Test after adding a vote and flag.
		$this->setRole( 'subscriber' );
		// Adding vote.
		ap_add_post_vote( $id, get_current_user_id() );
		ap_update_votes_count( $id );
		$this->assertNotEmpty( ap_get_votes() );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => $id ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => $new_id ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => array( $id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => array( $new_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => array( $id, $new_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => get_current_user_id() ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => $user_id ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id() ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => array( $user_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id(), $user_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => 'vote' ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => 'flag' ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => array( 'vote' ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_type' => array( 'flag' ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => array( 'vote', 'flag' ) ) ) );

		// Adding flag.
		ap_add_flag( $id, get_current_user_id() );
		ap_update_flags_count( $id );
		$this->assertNotEmpty( ap_get_votes() );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => $id ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => $new_id ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => array( $id ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_post_id' => array( $new_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_post_id' => array( $id, $new_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => get_current_user_id() ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => $user_id ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id() ) ) ) );
		$this->assertEmpty( ap_get_votes( array( 'vote_user_id' => array( $user_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_user_id' => array( get_current_user_id(), $user_id ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => 'vote' ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => 'flag' ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => array( 'vote' ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => array( 'flag' ) ) ) );
		$this->assertNotEmpty( ap_get_votes( array( 'vote_type' => array( 'vote', 'flag' ) ) ) );
	}

	/**
	 * @covers ::ap_count_votes
	 */
	public function testAPCountVotes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// Test goes here.
		$count_votes = ap_count_votes( 'vote_post_id=' . $id );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_post_id' => $id ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=vote' );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'vote' ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=flag' );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'flag' ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . get_current_user_id() );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => get_current_user_id() ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . $user_id );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => $user_id ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_value=1' );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_value' => 1 ) );
		$this->assertEquals( 0, $count_votes[0]->count );

		// Test for group.
		$count_votes = ap_count_votes( 'group=vote_post_id' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_post_id' ) );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_type' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_type' ) );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_type' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_type' ) );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_user_id' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_user_id' ) );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_user_id' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_user_id' ) );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_value' );
		$this->assertEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_value' ) );
		$this->assertEmpty( $count_votes );

		// Test after adding a vote and flag.
		$this->setRole( 'subscriber' );
		// Adding vote.
		ap_add_post_vote( $id, get_current_user_id() );
		$count_votes = ap_count_votes( 'vote_post_id=' . $id );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_post_id' => $id ) );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=vote' );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'vote' ) );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=flag' );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'flag' ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . get_current_user_id() );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => get_current_user_id() ) );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . $user_id );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => $user_id ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_value=1' );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_value' => 1 ) );
		$this->assertEquals( 1, $count_votes[0]->count );

		// Adding flag.
		ap_add_flag( $id, get_current_user_id() );
		$count_votes = ap_count_votes( 'vote_post_id=' . $id );
		$this->assertEquals( 2, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_post_id' => $id ) );
		$this->assertEquals( 2, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=vote' );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'vote' ) );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_type=flag' );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_type' => 'flag' ) );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . get_current_user_id() );
		$this->assertEquals( 2, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => get_current_user_id() ) );
		$this->assertEquals( 2, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_user_id=' . $user_id );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_user_id' => $user_id ) );
		$this->assertEquals( 0, $count_votes[0]->count );
		$count_votes = ap_count_votes( 'vote_value=1' );
		$this->assertEquals( 1, $count_votes[0]->count );
		$count_votes = ap_count_votes( array( 'vote_value' => 1 ) );
		$this->assertEquals( 1, $count_votes[0]->count );

		// Testing for group.
		$count_votes = ap_count_votes( 'group=vote_post_id' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_post_id' ) );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_type' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_type' ) );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_type' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_type' ) );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_user_id' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_user_id' ) );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_user_id' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_user_id' ) );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( 'group=vote_value' );
		$this->assertNotEmpty( $count_votes );
		$count_votes = ap_count_votes( array( 'group' => 'vote_value' ) );
		$this->assertNotEmpty( $count_votes );
	}

	/**
	 * @covers ::ap_count_post_votes_by
	 */
	public function testAPCountPostVotesBy() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->setRole( 'subscriber' );

		// Test without adding a vote.
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);

		// Test adding the vote.
		ap_add_post_vote( $id, get_current_user_id() );
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 0,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 0,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);
		ap_add_post_vote( $id, get_current_user_id(), false );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);
		ap_add_post_vote( $id, $user_id );
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 1,
				'votes_up'   => 2.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 0,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);
		ap_add_post_vote( $id, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 2,
				'votes_up'   => 2.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);
		ap_add_post_vote( $id, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => -1,
				'votes_down' => 3,
				'votes_up'   => 2.
			],
			ap_count_post_votes_by( 'post_id', $id )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', get_current_user_id() )
		);
		$this->assertEquals(
			[
				'votes_net'  => -1,
				'votes_down' => 2,
				'votes_up'   => 1.
			],
			ap_count_post_votes_by( 'user_id', $user_id )
		);
	}

	/**
	 * @covers ::ap_get_vote
	 */
	public function testAPGetVote() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();

		// Test without adding a vote or flag.
		$this->setRole( 'subscriber' );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$this->assertFalse( $get_vote );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$this->assertFalse( $get_vote );

		// Test adding a vote or flag.
		// Adding vote.
		ap_vote_insert( $id, get_current_user_id() );
		ap_update_votes_count( $id );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'vote' );
		$this->assertNotEmpty( $get_vote );
		$this->assertEquals( $id, $get_vote->vote_post_id );
		$this->assertEquals( get_current_user_id(), $get_vote->vote_user_id );
		$this->assertEquals( 0, $get_vote->vote_rec_user );
		$this->assertEquals( 'vote', $get_vote->vote_type );
		$this->assertEquals( '', $get_vote->vote_value );
		$this->assertEquals( current_time( 'mysql' ), $get_vote->vote_date );

		// Adding flag.
		ap_vote_insert( $id, get_current_user_id(), 'flag' );
		ap_update_flags_count( $id );
		$get_vote = ap_get_vote( $id, get_current_user_id(), 'flag' );
		$this->assertNotEmpty( $get_vote );
		$this->assertEquals( $id, $get_vote->vote_post_id );
		$this->assertEquals( get_current_user_id(), $get_vote->vote_user_id );
		$this->assertEquals( 0, $get_vote->vote_rec_user );
		$this->assertEquals( 'flag', $get_vote->vote_type );
		$this->assertEquals( '', $get_vote->vote_value );
		$this->assertEquals( current_time( 'mysql' ), $get_vote->vote_date );

		// Testing for both type.
		$get_vote = ap_get_vote( $id, get_current_user_id(), array( 'vote', 'flag' ) );
		$this->assertNotEmpty( $get_vote );
	}

	/**
	 * @covers ::ap_add_post_vote
	 */
	public function testAPAddPostVote() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();

		// Test begins.
		$this->setRole( 'subscriber' );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );

		// Test after using the ap_add_post_vote function.
		$add_post_vote = ap_add_post_vote( $id );
		$this->assertEquals(
			[
				'votes_up'   => 1,
				'votes_down' => 0,
				'votes_net'  => 1,
			],
			$add_post_vote
		);
		$add_post_vote = ap_add_post_vote( $id );
		$this->assertEquals(
			[
				'votes_up'   => 2,
				'votes_down' => 0,
				'votes_net'  => 2,
			],
			$add_post_vote
		);
		$add_post_vote = ap_add_post_vote( $id, get_current_user_id(), true );
		$this->assertEquals(
			[
				'votes_up'   => 3,
				'votes_down' => 0,
				'votes_net'  => 3,
			],
			$add_post_vote
		);
		$add_post_vote = ap_add_post_vote( $id, get_current_user_id(), false );
		$this->assertEquals(
			[
				'votes_up'   => 3,
				'votes_down' => 1,
				'votes_net'  => 2,
			],
			$add_post_vote
		);
		$add_post_vote = ap_add_post_vote( $id, get_current_user_id(), false );
		$this->assertEquals(
			[
				'votes_up'   => 3,
				'votes_down' => 2,
				'votes_net'  => 1,
			],
			$add_post_vote
		);
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 3, $get_qameta->votes_up );
		$this->assertEquals( 2, $get_qameta->votes_down );
		$this->assertEquals( 5, $get_qameta->votes_net );
	}

	/**
	 * @covers ::ap_delete_votes
	 */
	public function testAPDeleteVotes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		$id = $this->insert_question();

		// Test begins.
		$this->setRole( 'subscriber' );
		ap_add_post_vote( $id );
		ap_add_post_vote( $id );
		ap_add_post_vote( $id, get_current_user_id(), false );
		ap_add_flag( $id, get_current_user_id() );
		ap_add_flag( $id, get_current_user_id() );
		ap_update_flags_count( $id );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 2, $get_qameta->votes_up );
		$this->assertEquals( 1, $get_qameta->votes_down );
		$this->assertEquals( 3, $get_qameta->votes_net );
		$this->assertEquals( 2, $get_qameta->flags );

		// Delete vote and test begins.
		$delete_votes = ap_delete_votes( $id );
		$this->assertTrue( $delete_votes );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 2, $get_qameta->flags );
		$delete_votes = ap_delete_votes( $id, 'flag' );
		$this->assertTrue( $delete_votes );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( 0, $get_qameta->votes_up );
		$this->assertEquals( 0, $get_qameta->votes_down );
		$this->assertEquals( 0, $get_qameta->votes_net );
		$this->assertEquals( 0, $get_qameta->flags );
	}

	/**
	 * @covers ::ap_vote_insert
	 */
	public function testAPVoteInsertWithUserIdAsFalseArg() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_insert_vote', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$insert_vote = ap_vote_insert( $question_id, false );
		$this->assertTrue( $insert_vote );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_insert_vote' ) > 0 );
	}

	/**
	 * @covers ::ap_get_votes
	 */
	public function testAPGetVotesWithEmptyArg() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$vote_id = ap_vote_insert( $question_id, get_current_user_id() );
		ap_update_votes_count( $question_id );
		$get_votes = ap_get_votes( $question_id );
		$this->assertNotEmpty( $get_votes );
		$this->assertEquals( $vote_id, $get_votes[0]->vote_id );
		$this->assertEquals( $question_id, $get_votes[0]->vote_post_id );
		$this->assertEquals( get_current_user_id(), $get_votes[0]->vote_user_id );
		$this->assertEquals( 0, $get_votes[0]->vote_rec_user );
		$this->assertEquals( 'vote', $get_votes[0]->vote_type );
		$this->assertEquals( '', $get_votes[0]->vote_value );
		$this->assertEquals( current_time( 'mysql' ), $get_votes[0]->vote_date );
	}

	/**
	 * @covers ::ap_count_votes
	 */
	public function testAPCountVotesForVoteUserIDs() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Test.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->insert_question();
		ap_vote_insert( $question_id, get_current_user_id() );
		ap_vote_insert( $question_id, $user_id, 'vote' );
		ap_update_votes_count( $question_id );
		$count_votes = ap_count_votes( array( 'vote_user_id' => array( get_current_user_id(), $user_id ) ) );
		$this->assertEquals( 2, $count_votes[0]->count );
	}

	/**
	 * @covers ::ap_count_votes
	 */
	public function testAPCountVotesForVoteTypes() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		ap_vote_insert( $question_id, get_current_user_id() );
		ap_vote_insert( $question_id, get_current_user_id(), 'flag' );
		ap_vote_insert( $question_id, get_current_user_id(), 'flag' );
		ap_update_votes_count( $question_id );
		ap_update_flags_count( $question_id );
		$count_votes = ap_count_votes( array( 'vote_type' => array( 'vote', 'flag' ) ) );
		$this->assertEquals( 3, $count_votes[0]->count );
	}

	/**
	 * @covers ::ap_count_votes
	 */
	public function testAPCountVotesForVoteValues() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		ap_vote_insert( $question_id, get_current_user_id(), 'vote', '', -1 );
		ap_vote_insert( $question_id, get_current_user_id(), 'flag', '', 1 );
		ap_update_votes_count( $question_id );
		ap_update_flags_count( $question_id );
		$count_votes = ap_count_votes( array( 'vote_value' => array( -1, 1 ) ) );
		$this->assertEquals( 2, $count_votes[0]->count );
	}

	/**
	 * @covers ::ap_count_votes
	 */
	public function testAPCountVotesForManyCasesAtOnce() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_votes}" );

		// Test.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( array( 'post_type' => 'answer', 'post_parent' => $question_id ) );
		ap_vote_insert( $question_id, get_current_user_id() );
		ap_vote_insert( $question_id, get_current_user_id(), 'flag' );
		ap_vote_insert( $question_id, get_current_user_id(), 'vote', $user_id, -1 );
		ap_vote_insert( $answer_id, $user_id, 'flag' );
		ap_vote_insert( $answer_id, $user_id, 'vote', get_current_user_id(), 1 );
		ap_update_votes_count( $question_id );
		ap_update_flags_count( $question_id );
		ap_update_votes_count( $answer_id );
		ap_update_flags_count( $answer_id );

		// Test 1.
		$count_votes = ap_count_votes(
			array(
				'vote_post_id' => $question_id,
				'vote_type'    => array( 'vote' ),
			)
		);
		$this->assertEquals( 2, $count_votes[0]->count );

		// Test 2.
		$count_votes = ap_count_votes(
			array(
				'vote_post_id' => $question_id,
				'vote_type'    => 'flag',
			)
		);
		$this->assertEquals( 1, $count_votes[0]->count );
	}
}
