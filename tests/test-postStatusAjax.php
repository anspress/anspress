<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestPostAjax extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNonLoggedInUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNonAllowedStatus() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=invalid&__nonce=' . wp_create_nonce( 'change-status-invalid-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForInvalidNonce() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-invalid-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNotAllowedToChangeStatusUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->insert_question( '', '', $user_id );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );

	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForQuestionCreator() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=private_post&__nonce=' . wp_create_nonce( 'change-status-private_post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'private_post' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForSuperAdminUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActionHookTriggered() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_post_status_updated', function( $post_id ) use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_post_status_updated' ) > 0 );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActivitiesUpdateForChagingPostStatusFromModerate() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=publish&__nonce=' . wp_create_nonce( 'change-status-publish-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'publish' );

		// Check activity.
		$qameta = ap_get_qameta( $question_id );
		$this->assertTrue( $qameta->activities['type'] === 'approved_question' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActivitiesUpdateForChagingPostStatusFromOther() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=private_post&__nonce=' . wp_create_nonce( 'change-status-private_post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'private_post' );

		// Check activity.
		$qameta = ap_get_qameta( $question_id );
		$this->assertTrue( $qameta->activities['type'] === 'changed_status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForAnswerBeingAlreadySelected() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		// Before calling the Ajax hook.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$answer_id_1 = $this->factory()->post->create( [ 'post_parent' => $question_id, 'post_type' => 'answer' ] );
		$answer_id_2 = $this->factory()->post->create( [ 'post_parent' => $question_id, 'post_type' => 'answer' ] );
		ap_set_selected_answer( $question_id, $answer_id_2 );
		$this->assertTrue( ap_have_answer_selected( $question_id ) );

		// After calling the Ajax hook.
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $answer_id_2 . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $answer_id_2 ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Answer status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $answer_id_2 ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
	}
}
