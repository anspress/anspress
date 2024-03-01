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
}
