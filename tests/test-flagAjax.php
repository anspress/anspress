<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestFlagAjax extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForNonLoggedInUser() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForInvalidNonce() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=invalid_nonce&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForAlreadyFlaggedPost() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		// Before calling Ajax method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );

		// After calling Ajax method.
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You have already reported this question.' );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlag() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->assertFalse( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 0, ap_count_post_flags( $question_id ) );

		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->count === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Thank you for reporting this question.' );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );
	}
}
