<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAdminAjax extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_delete_flag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'clear_flag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_admin_vote' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'get_all_answers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_uninstall_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'ap_toggle_addon' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_votes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_answers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_flagged' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_subscribers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_reputation' ) );
		$this->assertTrue( method_exists( 'AnsPress_Admin_Ajax', 'recount_views' ) );
	}

	public function _set_post_data( $query ) {
		$args            = wp_parse_args( $query );
		$_POST['action'] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * @covers AnsPress_Admin_Ajax::ap_delete_flag
	 */
	public function testAPDeleteFlag() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );
		add_action( 'wp_ajax_ap_delete_flag', array( 'AnsPress_Admin_Ajax', 'ap_delete_flag' ) );

		// For user who do not have access to delete flag.
		$this->setRole( 'subscriber' );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'id=' . $question_id . '&action=ap_delete_flag&__nonce=' . wp_create_nonce( 'flag_delete' . $question_id ) );
		$this->handle( 'ap_delete_flag' );
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'id=' . $answer_id . '&action=ap_delete_flag&__nonce=' . wp_create_nonce( 'flag_delete' . $answer_id ) );
		$this->handle( 'ap_delete_flag' );
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// Delete question and answer created previously
		// to avoid conflicts with other tests.
		wp_delete_post( $question_id, true );
		wp_delete_post( $answer_id, true );

		// For user having access to delete flag.
		$this->setRole( 'administrator' );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'id=' . $question_id . '&action=ap_delete_flag&__nonce=' . wp_create_nonce( 'flag_delete' . $question_id ) );
		$this->handle( 'ap_delete_flag' );
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertEmpty( $flag_count );
		$this->assertEquals( 0, $flag_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 1, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'id=' . $answer_id . '&action=ap_delete_flag&__nonce=' . wp_create_nonce( 'flag_delete' . $answer_id ) );
		$this->handle( 'ap_delete_flag' );
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertEmpty( $flag_count );
		$this->assertEquals( 0, $flag_count );
	}

	/**
	 * @covers AnsPress_Admin_Ajax::clear_flag
	 */
	public function testClearFlag() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );
		add_action( 'ap_ajax_ap_clear_flag', array( 'AnsPress_Admin_Ajax', 'clear_flag' ) );

		// For user who do not have access to clear flag.
		$this->setRole( 'subscriber' );
		$user_id_1 = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user_id_2 = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		ap_add_flag( $question_id );
		ap_add_flag( $question_id, $user_id_1 );
		ap_add_flag( $question_id, $user_id_2 );
		ap_update_flags_count( $question_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'post_id=' . $question_id . '&ap_ajax_action=ap_clear_flag&__nonce=' . wp_create_nonce( 'clear_flag_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		ap_add_flag( $answer_id );
		ap_add_flag( $answer_id, $user_id_1 );
		ap_add_flag( $answer_id, $user_id_2 );
		ap_update_flags_count( $answer_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'post_id=' . $answer_id . '&ap_ajax_action=ap_clear_flag&__nonce=' . wp_create_nonce( 'clear_flag_' . $answer_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// Delete question and answer created previously
		// to avoid conflicts with other tests.
		wp_delete_post( $question_id, true );
		wp_delete_post( $answer_id, true );

		// For user having access to clear flag.
		$this->setRole( 'administrator' );
		$user_id_1 = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$user_id_2 = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		ap_add_flag( $question_id );
		ap_add_flag( $question_id, $user_id_1 );
		ap_add_flag( $question_id, $user_id_2 );
		ap_update_flags_count( $question_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'post_id=' . $question_id . '&ap_ajax_action=ap_clear_flag&__nonce=' . wp_create_nonce( 'clear_flag_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $question_id );
		$flag_count = $get_qameta->flags;
		$this->assertEmpty( $flag_count );
		$this->assertEquals( 0, $flag_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		ap_add_flag( $answer_id );
		ap_add_flag( $answer_id, $user_id_1 );
		ap_add_flag( $answer_id, $user_id_2 );
		ap_update_flags_count( $answer_id );

		// Before Ajax call.
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertNotEmpty( $flag_count );
		$this->assertEquals( 3, $flag_count );

		// After Ajax call.
		$this->_set_post_data( 'post_id=' . $answer_id . '&ap_ajax_action=ap_clear_flag&__nonce=' . wp_create_nonce( 'clear_flag_' . $answer_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $answer_id );
		$flag_count = $get_qameta->flags;
		$this->assertEmpty( $flag_count );
		$this->assertEquals( 0, $flag_count );
	}

	/**
	 * @covers AnsPress_Admin_Ajax::ap_admin_vote
	 */
	public function testAPAdminVote() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );
		add_action( 'ap_ajax_ap_admin_vote', array( 'AnsPress_Admin_Ajax', 'ap_admin_vote' ) );

		// For user who do not have access to vote.
		$this->setRole( 'subscriber' );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $question_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertEmpty( $vote_count );

		// Vote down.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $question_id, 'down' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertEmpty( $vote_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $answer_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertEmpty( $vote_count );

		// Vote down.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $answer_id, 'down' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertEmpty( $vote_count );

		// Delete question and answer created previously
		// to avoid conflicts with other tests.
		wp_delete_post( $question_id, true );
		wp_delete_post( $answer_id, true );

		// For user having access to vote.
		$this->setRole( 'administrator' );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $question_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $question_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 2, $vote_count );

		// Vote down.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 2, $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $question_id, 'down' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $question_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertEmpty( $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $answer_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );

		// Vote up.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $answer_id, 'up' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 2, $vote_count );

		// Vote down.
		// Before Ajax call.
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 2, $vote_count );

		// After Ajax call.
		$this->_set_post_data( 'ap_ajax_action=ap_admin_vote&__nonce=' . wp_create_nonce( 'admin_vote' ) );
		$_POST['args'] = [ $answer_id, 'down' ];
		$this->handle( 'ap_ajax' );
		$vote_count = ap_get_votes_net( $answer_id );
		$this->assertNotEmpty( $vote_count );
		$this->assertEquals( 1, $vote_count );
	}

	/**
	 * @covers AnsPress_Admin_Ajax::get_all_answers
	 */
	public function testGetAllAnswers() {
		add_action( 'ap_ajax_ap_get_all_answers', array( 'AnsPress_Admin_Ajax', 'get_all_answers' ) );

		// For tests.
		$user_id_1   = $this->factory->user->create( array( 'role' => 'subscriber', 'display_name' => 'User 1' ) );
		$user_id_2   = $this->factory->user->create( array( 'role' => 'subscriber', 'display_name' => 'User 2' ) );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id_1 = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_1,
			)
		);
		$answer_id_2 = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Lorem ipsum dolor sit amit',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_2,
			)
		);
		$answers     = [
			[
				'ID'        => $answer_id_1,
				'content'   => 'Donec nec nunc purus',
				'avatar'    => ap_get_author_avatar( 30, $answer_id_1 ),
				'author'    => 'User 1',
				'activity'  => ap_get_recent_post_activity( $answer_id_1 ),
				'editLink'  => get_edit_post_link( $answer_id_1 ),
				'trashLink' => get_delete_post_link( $answer_id_1 ),
				'status'    => 'Published',
				'selected'  => ap_get_post_field( 'selected', $answer_id_1 ),
			],
			[
				'ID'        => $answer_id_2,
				'content'   => 'Lorem ipsum dolor sit amit',
				'avatar'    => ap_get_author_avatar( 30, $answer_id_2 ),
				'author'    => 'User 2',
				'activity'  => ap_get_recent_post_activity( $answer_id_2 ),
				'editLink'  => get_edit_post_link( $answer_id_2 ),
				'trashLink' => get_delete_post_link( $answer_id_2 ),
				'status'    => 'Published',
				'selected'  => ap_get_post_field( 'selected', $answer_id_2 ),
			],
		];

		// Test 1.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'question_id=' . $question_id . '&ap_ajax_action=ap_get_all_answers' );
		@$this->handle( 'ap_ajax' );
		$response = json_decode( $this->_last_response, true );
		foreach ( $response as $idx => $answer ) {
			$this->assertEquals( $answers[ $idx ]['ID'], $answer['ID'] );
			$this->assertEquals( $answers[ $idx ]['content'], $answer['content'] );
			$this->assertEquals( $answers[ $idx ]['avatar'], $answer['avatar'] );
			$this->assertEquals( $answers[ $idx ]['author'], $answer['author'] );
			$this->assertEquals( $answers[ $idx ]['activity'], $answer['activity'] );
			$this->assertEquals( $answers[ $idx ]['editLink'], $answer['editLink'] );
			$this->assertEquals( $answers[ $idx ]['trashLink'], $answer['trashLink'] );
			$this->assertEquals( $answers[ $idx ]['status'], $answer['status'] );
			$this->assertEquals( $answers[ $idx ]['selected'], $answer['selected'] );
		}

		// Test 2.
		$this->_last_response = '';
		$this->setRole( 'administrator' );
		$user_id_1   = $this->factory->user->create( array( 'role' => 'subscriber', 'display_name' => 'User 1' ) );
		$user_id_2   = $this->factory->user->create( array( 'role' => 'subscriber', 'display_name' => 'User 2' ) );
		$user_id_3   = $this->factory->user->create( array( 'role' => 'subscriber', 'display_name' => 'User 3' ) );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id_1 = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_1,
			)
		);
		$answer_id_2 = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Lorem ipsum dolor sit amit',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_2,
			)
		);
		$answer_id_3 = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a consectetur magna, eu volutpat ipsum',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_3,
			)
		);
		$answers     = [
			[
				'ID'        => $answer_id_1,
				'content'   => 'Donec nec nunc purus',
				'avatar'    => ap_get_author_avatar( 30, $answer_id_1 ),
				'author'    => 'User 1',
				'activity'  => ap_get_recent_post_activity( $answer_id_1 ),
				'editLink'  => get_edit_post_link( $answer_id_1 ),
				'trashLink' => get_delete_post_link( $answer_id_1 ),
				'status'    => 'Published',
				'selected'  => ap_get_post_field( 'selected', $answer_id_1 ),
			],
			[
				'ID'        => $answer_id_2,
				'content'   => 'Lorem ipsum dolor sit amit',
				'avatar'    => ap_get_author_avatar( 30, $answer_id_2 ),
				'author'    => 'User 2',
				'activity'  => ap_get_recent_post_activity( $answer_id_2 ),
				'editLink'  => get_edit_post_link( $answer_id_2 ),
				'trashLink' => get_delete_post_link( $answer_id_2 ),
				'status'    => 'Published',
				'selected'  => ap_get_post_field( 'selected', $answer_id_2 ),
			],
			[
				'ID'        => $answer_id_3,
				'content'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a consectetur magna, eu volutpat ipsum',
				'avatar'    => ap_get_author_avatar( 30, $answer_id_3 ),
				'author'    => 'User 3',
				'activity'  => ap_get_recent_post_activity( $answer_id_3 ),
				'editLink'  => get_edit_post_link( $answer_id_3 ),
				'trashLink' => get_delete_post_link( $answer_id_3 ),
				'status'    => 'Moderate',
				'selected'  => ap_get_post_field( 'selected', $answer_id_3 ),
			],
		];
		$this->_set_post_data( 'question_id=' . $question_id . '&ap_ajax_action=ap_get_all_answers' );
		@$this->handle( 'ap_ajax' );
		$response = json_decode( $this->_last_response, true );
		foreach ( $response as $idx => $answer ) {
			$this->assertEquals( $answers[ $idx ]['ID'], $answer['ID'] );
			$this->assertEquals( $answers[ $idx ]['content'], $answer['content'] );
			$this->assertEquals( $answers[ $idx ]['avatar'], $answer['avatar'] );
			$this->assertEquals( $answers[ $idx ]['author'], $answer['author'] );
			$this->assertEquals( $answers[ $idx ]['activity'], $answer['activity'] );
			$this->assertEquals( $answers[ $idx ]['editLink'], $answer['editLink'] );
			$this->assertEquals( $answers[ $idx ]['trashLink'], $answer['trashLink'] );
			$this->assertEquals( $answers[ $idx ]['status'], $answer['status'] );
			$this->assertEquals( $answers[ $idx ]['selected'], $answer['selected'] );
		}
	}
}
