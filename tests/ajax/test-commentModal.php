<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestCommentModal extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function set_up()
	{
		parent::set_up();
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );
	}

	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Comment_Modal' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'logged_in' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Modal', 'nopriv' ) );
	}

	public function testCommentModal() {
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );

		// Test 1.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		@$this->handle( 'comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Add a comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $comment_id ) );
		$this->handle( 'comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Edit comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content );

		// Test 4.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_type'    => 'anspress',
				'user_id'         => $user_id,
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $comment_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 5.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Trying to cheat?!' );
	}

	public function testShouldNotAllowNonLoggedInToDelete() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_type'    => 'anspress',
			)
		);

		$this->logout();
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $comment_id ) );

		$this->handle( 'comment_modal' );

		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );
	}
}
