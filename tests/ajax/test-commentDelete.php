<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestCommentDelete extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Comment_Delete', 'logged_in' ) );
	}

	public function testCommentDelete() {
		add_action( 'wp_ajax_comment_delete', array( 'AnsPress\Ajax\Comment_Delete', 'init' ) );

		// For action hooks triggered.
		$ap_unpublish_comment_triggered = false;
		add_action( 'ap_unpublish_comment', function () use ( &$ap_unpublish_comment_triggered ) {
			$ap_unpublish_comment_triggered = true;
		} );
		$ap_after_deleting_comment_triggered = false;
		add_action( 'ap_after_deleting_comment', function () use ( &$ap_after_deleting_comment_triggered ) {
			$ap_after_deleting_comment_triggered = true;
		} );

		// Test 1.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
		$this->handle( 'comment_delete' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
		$this->handle( 'comment_delete' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 3.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$ap_unpublish_comment_triggered = false;
		$ap_after_deleting_comment_triggered = false;
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'comment_delete' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Trying to cheat?!' );
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
		$this->assertFalse( did_action( 'ap_unpublish_comment' ) > 0 );
		$this->assertFalse( did_action( 'ap_after_deleting_comment' ) > 0 );

		// Test 4.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$ap_unpublish_comment_triggered = false;
		$ap_after_deleting_comment_triggered = false;
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
		$this->handle( 'comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment successfully deleted' );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'commentDeleted' );
		$this->assertTrue( $this->ap_ajax_success( 'post_ID' ) === (string) $question_id );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->text === '0 Comments' );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->number === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->unapproved === 0 );
		$this->assertTrue( $ap_unpublish_comment_triggered );
		$this->assertTrue( $ap_after_deleting_comment_triggered );
		$this->assertTrue( did_action( 'ap_unpublish_comment' ) > 0 );
		$this->assertTrue( did_action( 'ap_after_deleting_comment' ) > 0 );

		// Test 5.
		$this->setRole( 'subscriber' );
		$ap_unpublish_comment_triggered = false;
		$ap_after_deleting_comment_triggered = false;
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
				'comment_date'    => '2020:01:01 00:00:00',
			)
		);
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );

			// Tests.
			// Before granting super admin role.
			$this->_last_response = '';
			$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
			$this->handle( 'comment_delete' );
			$this->assertFalse( $this->ap_ajax_success( 'success' ) );
			$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'The comment is locked and cannot be deleted. Any comments posted before 1 day cannot be deleted.' );
			$this->assertFalse( $ap_unpublish_comment_triggered );
			$this->assertFalse( $ap_after_deleting_comment_triggered );

			// After granting super admin role.
			grant_super_admin( get_current_user_id() );
			$this->_last_response = '';
			$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
			$this->handle( 'comment_delete' );
			$this->assertTrue( $this->ap_ajax_success( 'success' ) );
			$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment successfully deleted' );
			$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'commentDeleted' );
			$this->assertTrue( $this->ap_ajax_success( 'post_ID' ) === (string) $question_id );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->text === '0 Comments' );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->number === 0 );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->unapproved === 0 );
			$this->assertTrue( $ap_unpublish_comment_triggered );
			$this->assertTrue( $ap_after_deleting_comment_triggered );
			$this->assertTrue( did_action( 'ap_unpublish_comment' ) > 0 );
			$this->assertTrue( did_action( 'ap_after_deleting_comment' ) > 0 );
		} else {
			$this->setRole( 'administrator' );

			// Tests.
			$this->_last_response = '';
			$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
			$this->handle( 'comment_delete' );
			$this->assertTrue( $this->ap_ajax_success( 'success' ) );
			$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment successfully deleted' );
			$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'commentDeleted' );
			$this->assertTrue( $this->ap_ajax_success( 'post_ID' ) === (string) $question_id );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->text === '0 Comments' );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->number === 0 );
			$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->unapproved === 0 );
			$this->assertTrue( $ap_unpublish_comment_triggered );
			$this->assertTrue( $ap_after_deleting_comment_triggered );
			$this->assertTrue( did_action( 'ap_unpublish_comment' ) > 0 );
			$this->assertTrue( did_action( 'ap_after_deleting_comment' ) > 0 );
		}

		// Test 6.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$ap_unpublish_comment_triggered = false;
		$ap_after_deleting_comment_triggered = false;
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
			)
		);
		$this->_set_post_data( 'comment_id=0&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );
		$this->handle( 'comment_delete' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Trying to cheat?!' );
		$this->assertFalse( $ap_unpublish_comment_triggered );
		$this->assertFalse( $ap_after_deleting_comment_triggered );
	}

	public function testShouldPreventDeletingLockedComment() {
		add_action( 'wp_ajax_comment_delete', array( 'AnsPress\Ajax\Comment_Delete', 'init' ) );

		ap_opt( 'disable_delete_after', 0 );

		// For action hooks triggered.
		$ap_unpublish_comment_triggered = false;

		add_action( 'ap_unpublish_comment', function () use ( &$ap_unpublish_comment_triggered ) {
			$ap_unpublish_comment_triggered = true;
		} );

		$ap_after_deleting_comment_triggered = false;

		add_action( 'ap_after_deleting_comment', function () use ( &$ap_after_deleting_comment_triggered ) {
			$ap_after_deleting_comment_triggered = true;
		} );

		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		$this->setRole( 'subscriber' );

		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
				'comment_date'    => '2020:01:01 00:00:00',
			)
		);

		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );

		$this->handle( 'comment_delete' );

		$this->assertFalse( $this->ap_ajax_success( 'success' ) );

		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );

		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'The comment is locked and cannot be deleted. Any comments posted before 1 second cannot be deleted.' );
	}

	public function testShouldNotPreventDeletingLockedCommentForAdmin() {
		add_action( 'wp_ajax_comment_delete', array( 'AnsPress\Ajax\Comment_Delete', 'init' ) );

		ap_opt( 'disable_delete_after', 0 );

		// For action hooks triggered.
		$ap_unpublish_comment_triggered = false;

		add_action( 'ap_unpublish_comment', function () use ( &$ap_unpublish_comment_triggered ) {
			$ap_unpublish_comment_triggered = true;
		} );

		$ap_after_deleting_comment_triggered = false;

		add_action( 'ap_after_deleting_comment', function () use ( &$ap_after_deleting_comment_triggered ) {
			$ap_after_deleting_comment_triggered = true;
		} );

		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		$this->setRole( 'administrator', true );

		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_content' => 'Donec nec nunc purus',
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
				'comment_date'    => '2020:01:01 00:00:00',
			)
		);

		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_delete&__nonce=' . wp_create_nonce( 'delete_comment_' . $comment_id ) );

		$this->handle( 'comment_delete' );

		$this->assertTrue( $this->ap_ajax_success( 'success' ) );

		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_delete' );

		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment successfully deleted' );
	}
}
