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
		$answers = array_reverse( $answers, true );
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
		$answers = array_reverse( $answers, true );
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

	/**
	 * @covers AnsPress_Admin_Ajax::ap_uninstall_data
	 */
	public function testAPUninstallData() {
		add_action( 'wp_ajax_ap_uninstall_data', array( 'AnsPress_Admin_Ajax', 'ap_uninstall_data' ) );

		// For user who do not have access to uninstall data.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'action=ap_uninstall_data&data_type=qa&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
		$this->handle( 'ap_uninstall_data' );
		$this->assertEquals( '[]', $this->_last_response );

		// For user having access to uninstall data.
		// Invalid data type.
		$this->_last_response = '';
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=invalid&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertEquals( '[]', $this->_last_response );
		} else {
			$this->setRole( 'administrator' );
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=invalid&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertEquals( '[]', $this->_last_response );
		}

		// Deleting questions and answers.
		$this->_last_response = '';
		$question_id_1 = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id_1   = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id_1,
			)
		);
		$question_id_2 = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id_2   = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id_2,
			)
		);
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );

			// Before Ajax call.
			$questions = get_posts( [ 'post_type' => 'question' ] );
			$answers   = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertNotEmpty( $questions );
			$this->assertNotEmpty( $answers );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=qa&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$questions = get_posts( [ 'post_type' => 'question' ] );
			$answers   = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertEmpty( $questions );
			$this->assertEmpty( $answers );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 4 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 4 );
		} else {
			$this->setRole( 'administrator' );

			// Before Ajax call.
			$questions = get_posts( [ 'post_type' => 'question' ] );
			$answers   = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertNotEmpty( $questions );
			$this->assertNotEmpty( $answers );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=qa&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$questions = get_posts( [ 'post_type' => 'question' ] );
			$answers   = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertEmpty( $questions );
			$this->assertEmpty( $answers );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 4 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 4 );
		}

		// Deleting answers.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_ids = $this->factory->post->create_many(
			3,
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );

			// Before Ajax call.
			$answers = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertNotEmpty( $answers );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=answers&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$answers = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertEmpty( $answers );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 3 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 3 );
		} else {
			$this->setRole( 'administrator' );

			// Before Ajax call.
			$answers = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertNotEmpty( $answers );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=answers&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$answers = get_posts( [ 'post_type' => 'answer' ] );
			$this->assertEmpty( $answers );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 3 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 3 );
		}

		// Deleting options.
		$this->_last_response = '';
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );

			// Tests.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=options&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertFalse( get_option( 'anspress_opt' ) );
			$this->assertFalse( get_option( 'anspress_reputation_events' ) );
			$this->assertFalse( get_option( 'anspress_addons' ) );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		} else {
			$this->setRole( 'administrator' );

			// Tests.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=options&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertFalse( get_option( 'anspress_opt' ) );
			$this->assertFalse( get_option( 'anspress_reputation_events' ) );
			$this->assertFalse( get_option( 'anspress_addons' ) );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		}

		// Deleting terms.
		$this->_last_response = '';
		$category_1 = $this->factory->term->create( array( 'taxonomy' => 'question_category' ) );
		$category_2 = $this->factory->term->create( array( 'taxonomy' => 'question_category' ) );
		$tag_1      = $this->factory->term->create( array( 'taxonomy' => 'question_tag' ) );
		$tag_2      = $this->factory->term->create( array( 'taxonomy' => 'question_tag' ) );
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );

			// Before Ajax call.
			$categories = get_terms( [ 'taxonomy' => 'question_category', 'hide_empty' => false ] );
			$tags       = get_terms( [ 'taxonomy' => 'question_tag', 'hide_empty' => false ] );
			$this->assertNotEmpty( $categories );
			$this->assertNotEmpty( $tags );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=terms&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$categories = get_terms( [ 'taxonomy' => 'question_category', 'hide_empty' => false ] );
			$tags       = get_terms( [ 'taxonomy' => 'question_tag', 'hide_empty' => false ] );
			$this->assertEmpty( $categories );
			$this->assertEmpty( $tags );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		} else {
			$this->setRole( 'administrator' );

			// Before Ajax call.
			$categories = get_terms( [ 'taxonomy' => 'question_category', 'hide_empty' => false ] );
			$tags       = get_terms( [ 'taxonomy' => 'question_tag', 'hide_empty' => false ] );
			$this->assertNotEmpty( $categories );
			$this->assertNotEmpty( $tags );

			// After Ajax call.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=terms&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$categories = get_terms( [ 'taxonomy' => 'question_category', 'hide_empty' => false ] );
			$tags       = get_terms( [ 'taxonomy' => 'question_tag', 'hide_empty' => false ] );
			$this->assertEmpty( $categories );
			$this->assertEmpty( $tags );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		}

		// Deleting tables.
		$this->_last_response = '';
		if ( is_multisite() ) {
			$this->setRole( 'administrator' );
			grant_super_admin( get_current_user_id() );

			// Tests.
			// Table exists could not get tested since it behaves differently on unit tests
			// and test on real site works fine.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=tables&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		} else {
			$this->setRole( 'administrator' );

			// Tests.
			// Table exists could not get tested since it behaves differently on unit tests
			// and test on real site works fine.
			$this->_set_post_data( 'action=ap_uninstall_data&data_type=tables&__nonce=' . wp_create_nonce( 'ap_uninstall_data' ) );
			$this->handle( 'ap_uninstall_data' );
			$this->assertTrue( $this->ap_ajax_success( 'done' ) === 1 );
			$this->assertTrue( $this->ap_ajax_success( 'total' ) === 0 );
		}

		// Deleting userdata test for now could not be done since it hampers other tests.
	}

	/**
	 * @covers AnsPress_Admin_Ajax::ap_toggle_addon
	 */
	public function testAPToggleAddon() {
		add_action( 'wp_ajax_ap_toggle_addon', [ 'AnsPress_Admin_Ajax', 'ap_toggle_addon' ] );

		// For user who do not have access to toggle addon.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=categories.php&__nonce=' . wp_create_nonce( 'toggle_addon' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === false );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, you do not have permission!' );

		// For user having access to toggle addon.
		$this->setRole( 'administrator' );

		// Tests.
		// For invalid nonce passed.
		$this->_last_response = '';
		set_transient( 'ap_pages_check', 'Test transient' );
		ap_activate_addon( 'categories.php' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=categories.php&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertEmpty( $this->_last_response );
		$this->assertEquals( 'Test transient', get_transient( 'ap_pages_check' ) );

		// For activated addon.
		// Test 1.
		$this->_last_response = '';
		set_transient( 'ap_pages_check', 'Test transient' );
		ap_activate_addon( 'categories.php' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=categories.php&__nonce=' . wp_create_nonce( 'toggle_addon' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully disabled addon. Redirecting!' );
		$this->assertTrue( $this->ap_ajax_success( 'addon_id' ) === 'categories.php' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'toggleAddon' );
		$this->assertFalse( ap_is_addon_active( 'categories.php' ) );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );

		// Test 2.
		$this->_last_response = '';
		set_transient( 'ap_pages_check', 'Test transient' );
		ap_activate_addon( 'tags.php' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=tags.php&__nonce=' . wp_create_nonce( 'toggle_addon' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully disabled addon. Redirecting!' );
		$this->assertTrue( $this->ap_ajax_success( 'addon_id' ) === 'tags.php' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'toggleAddon' );
		$this->assertFalse( ap_is_addon_active( 'tags.php' ) );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );

		// For deactivated addon.
		// Test 1.
		$this->_last_response = '';
		set_transient( 'ap_pages_check', 'Test transient' );
		ap_deactivate_addon( 'reputation.php' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=reputation.php&__nonce=' . wp_create_nonce( 'toggle_addon' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully enabled addon. Redirecting!' );
		$this->assertTrue( $this->ap_ajax_success( 'addon_id' ) === 'reputation.php' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'toggleAddon' );
		$this->assertTrue( ap_is_addon_active( 'reputation.php' ) );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );

		// Test 2.
		$this->_last_response = '';
		set_transient( 'ap_pages_check', 'Test transient' );
		ap_deactivate_addon( 'notifications.php' );
		$this->_set_post_data( 'action=ap_toggle_addon&addon_id=notifications.php&__nonce=' . wp_create_nonce( 'toggle_addon' ) );
		$this->handle( 'ap_toggle_addon' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully enabled addon. Redirecting!' );
		$this->assertTrue( $this->ap_ajax_success( 'addon_id' ) === 'notifications.php' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'toggleAddon' );
		$this->assertTrue( ap_is_addon_active( 'notifications.php' ) );
		$this->assertFalse( get_transient( 'ap_pages_check' ) );
	}

	/**
	 * @covers AnsPress_Admin_Ajax::recount_votes
	 */
	public function testRecountVotes() {
		add_action( 'wp_ajax_ap_recount_votes', [ 'AnsPress_Admin_Ajax', 'recount_votes' ] );

		// For user who do not have access to recount votes.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'action=ap_recount_votes&__nonce=' . wp_create_nonce( 'recount_votes' ) );
		$this->handle( 'ap_recount_votes' );
		$this->assertEmpty( $this->_last_response );

		// For user having access to recount votes.
		$this->setRole( 'administrator' );

		// Tests.
		// For invalid nonce passed.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_recount_votes&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_recount_votes' );
		$this->assertEmpty( $this->_last_response );

		// Test 1.
		$this->_last_response = '';
		$ids = $this->insert_answers( [], [], 5 );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $ids['question'], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $ids['answers'][0], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $ids['answers'][1], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $ids['answers'][2], $user_id, 'vote', get_current_user_id(), '-1' );

		// Before Ajax call.
		$test_vote_1 = ap_get_qameta( $ids['question'] );
		$this->assertEquals( 0, $test_vote_1->votes_net );
		$test_vote_2 = ap_get_qameta( $ids['answers'][0] );
		$this->assertEquals( 0, $test_vote_2->votes_net );
		$test_vote_3 = ap_get_qameta( $ids['answers'][1] );
		$this->assertEquals( 0, $test_vote_3->votes_net );
		$test_vote_4 = ap_get_qameta( $ids['answers'][2] );
		$this->assertEquals( 0, $test_vote_4->votes_net );

		// After Ajax call.
		$this->_set_post_data( 'action=ap_recount_votes&__nonce=' . wp_create_nonce( 'recount_votes' ) );
		$this->handle( 'ap_recount_votes' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '6' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-votes' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '6 done out of 6' );
		$test_vote_1 = ap_get_qameta( $ids['question'] );
		$this->assertEquals( 1, $test_vote_1->votes_net );
		$test_vote_2 = ap_get_qameta( $ids['answers'][0] );
		$this->assertEquals( 1, $test_vote_2->votes_net );
		$test_vote_4 = ap_get_qameta( $ids['answers'][1] );
		$this->assertEquals( 1, $test_vote_4->votes_net );
		$test_vote_4 = ap_get_qameta( $ids['answers'][2] );
		$this->assertEquals( 1, $test_vote_4->votes_net );

		// Delete all questions and answers so that it does not effect any other tests.
		wp_delete_post( $ids['question'] );
		wp_delete_post( $ids['answers'][0] );
		wp_delete_post( $ids['answers'][1] );
		wp_delete_post( $ids['answers'][2] );
		wp_delete_post( $ids['answers'][3] );
		wp_delete_post( $ids['answers'][4] );

		// Test 2.
		$question_ids = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$answer_ids_set_1 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[0] ] );
		$answer_ids_set_2 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[1] ] );
		$answer_ids_set_3 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[2] ] );
		$answer_ids_set_4 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[3] ] );
		$answer_ids_set_5 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[4] ] );
		$answer_ids_set_6 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[5] ] );
		$answer_ids_set_7 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[6] ] );
		$answer_ids_set_8 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[7] ] );
		$answer_ids_set_9 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[8] ] );
		$answer_ids_set_10 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids[9] ] );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		ap_vote_insert( $question_ids[0], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $question_ids[5], $user_id, 'vote', get_current_user_id(), '-1' );
		ap_vote_insert( $answer_ids_set_1[0], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $answer_ids_set_2[2], $user_id, 'vote', get_current_user_id(), '1' );
		ap_vote_insert( $answer_ids_set_3[4], $user_id, 'vote', get_current_user_id(), '-1' );

		// Before Ajax call.
		$test_vote_1 = ap_get_qameta( $question_ids[0] );
		$this->assertEquals( 0, $test_vote_1->votes_net );
		$test_vote_2 = ap_get_qameta( $question_ids[5] );
		$this->assertEquals( 0, $test_vote_2->votes_net );
		$test_vote_4 = ap_get_qameta( $answer_ids_set_1[0] );
		$this->assertEquals( 0, $test_vote_4->votes_net );
		$test_vote_4 = ap_get_qameta( $answer_ids_set_2[2] );
		$this->assertEquals( 0, $test_vote_4->votes_net );
		$test_vote_5 = ap_get_qameta( $answer_ids_set_3[4] );
		$this->assertEquals( 0, $test_vote_5->votes_net );

		// After Ajax call.
		// First set of questions and answers.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'recount_votes' );
		$this->_set_post_data( 'action=ap_recount_votes&__nonce=' . $nonce );
		$this->handle( 'ap_recount_votes' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '110' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 10 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-votes' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '100 done out of 110' );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->action === 'ap_recount_votes' );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->__nonce === $nonce );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->paged === 1 );

		// Second set of questions and answers.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_recount_votes&__nonce=' . $nonce . '&paged=1' );
		$this->handle( 'ap_recount_votes' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '110' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-votes' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '110 done out of 110' );

		// Tests after successful Ajax call.
		$test_vote_1 = ap_get_qameta( $question_ids[0] );
		$this->assertEquals( 1, $test_vote_1->votes_net );
		$test_vote_2 = ap_get_qameta( $question_ids[5] );
		$this->assertEquals( 1, $test_vote_2->votes_net );
		$test_vote_3 = ap_get_qameta( $answer_ids_set_1[0] );
		$this->assertEquals( 1, $test_vote_3->votes_net );
		$test_vote_4 = ap_get_qameta( $answer_ids_set_2[2] );
		$this->assertEquals( 1, $test_vote_4->votes_net );
		$test_vote_5 = ap_get_qameta( $answer_ids_set_3[4] );
		$this->assertEquals( 1, $test_vote_5->votes_net );
	}

	/**
	 * @covers AnsPress_Admin_Ajax::recount_answers
	 */
	public function testRecountAnswers() {
		add_action( 'wp_ajax_ap_recount_answers', [ 'AnsPress_Admin_Ajax', 'recount_answers' ] );

		// For user who do not have access to recount answers.
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'action=ap_recount_answers&__nonce=' . wp_create_nonce( 'recount_answers' ) );
		$this->handle( 'ap_recount_answers' );
		$this->assertEmpty( $this->_last_response );

		// For user having access to recount answers.
		$this->setRole( 'administrator' );

		// Tests.
		// For invalid nonce passed.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_recount_answers&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_recount_answers' );
		$this->assertEmpty( $this->_last_response );

		// Test 1.
		$this->_last_response = '';
		$ids = $this->insert_answers( [], [], 5 );

		// Before Ajax call.
		ap_insert_qameta( $ids['question'], [ 'answers' => 0 ] );
		$test_answer_1 = ap_get_qameta( $ids['question'] );
		$this->assertEquals( 0, $test_answer_1->answers );

		// After Ajax call.
		$this->_set_post_data( 'action=ap_recount_answers&__nonce=' . wp_create_nonce( 'recount_answers' ) );
		$this->handle( 'ap_recount_answers' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '1' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-answers' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '1 done out of 1' );
		$test_answer_1 = ap_get_qameta( $ids['question'] );
		$this->assertEquals( 5, $test_answer_1->answers );

		// Delete all questions and answers so that it does not effect any other tests.
		wp_delete_post( $ids['question'] );
		wp_delete_post( $ids['answers'][0] );
		wp_delete_post( $ids['answers'][1] );
		wp_delete_post( $ids['answers'][2] );
		wp_delete_post( $ids['answers'][3] );
		wp_delete_post( $ids['answers'][4] );

		// Test 2.
		$question_ids_set_1 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_2 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_3 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_4 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_5 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_6 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_7 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_8 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_9 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_10 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$question_ids_set_11 = $this->factory->post->create_many( 10, [ 'post_type' => 'question' ] );
		$answer_ids_set_1 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids_set_1[0] ] );
		$answer_ids_set_2 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids_set_1[2] ] );
		$answer_ids_set_3 = $this->factory->post->create_many( 10, [ 'post_type' => 'answer', 'post_parent' => $question_ids_set_1[4] ] );
		ap_insert_qameta( $question_ids_set_1[0], [ 'answers' => 0 ] );
		ap_insert_qameta( $question_ids_set_1[2], [ 'answers' => 0 ] );
		ap_insert_qameta( $question_ids_set_1[4], [ 'answers' => 0 ] );

		// Before Ajax call.
		$test_answer_1 = ap_get_qameta( $question_ids_set_1[0] );
		$this->assertEquals( 0, $test_answer_1->answers );
		$test_answer_2 = ap_get_qameta( $question_ids_set_1[2] );
		$this->assertEquals( 0, $test_answer_2->answers );
		$test_answer_3 = ap_get_qameta( $question_ids_set_1[4] );
		$this->assertEquals( 0, $test_answer_3->answers );

		// After Ajax call.
		// First set of questions and answers.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'recount_answers' );
		$this->_set_post_data( 'action=ap_recount_answers&__nonce=' . $nonce );
		$this->handle( 'ap_recount_answers' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '110' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 10 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-answers' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '100 done out of 110' );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->action === 'ap_recount_answers' );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->__nonce === $nonce );
		$this->assertTrue( $this->ap_ajax_success( 'q' )->paged === 1 );

		// Second set of questions and answers.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_recount_answers&__nonce=' . $nonce . '&paged=1' );
		$this->handle( 'ap_recount_answers' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) === true );
		$this->assertTrue( $this->ap_ajax_success( 'total' ) === '110' );
		$this->assertTrue( $this->ap_ajax_success( 'remain' ) === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'el' ) === '.ap-recount-answers' );
		$this->assertTrue( $this->ap_ajax_success( 'msg' ) === '110 done out of 110' );

		// Tests after successful Ajax call.
		$test_answer_1 = ap_get_qameta( $question_ids_set_1[0] );
		$this->assertEquals( 10, $test_answer_1->answers );
		$test_answer_2 = ap_get_qameta( $question_ids_set_1[2] );
		$this->assertEquals( 10, $test_answer_2->answers );
		$test_answer_3 = ap_get_qameta( $question_ids_set_1[4] );
		$this->assertEquals( 10, $test_answer_3->answers );
	}
}
