<?php

namespace AnsPress\Tests\WP;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestToggleBestAnswer extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function testInstance() {
		$class = new \ReflectionClass( 'AnsPress\Ajax\Toggle_Best_Answer' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', 'verify_permission' ) );
		$this->assertTrue( method_exists( 'AnsPress\Ajax\Toggle_Best_Answer', 'logged_in' ) );
	}

	public function testToggleBestAnswer() {
		add_action( 'wp_ajax_toggle_best_answer', array( 'AnsPress\Ajax\Toggle_Best_Answer', 'init' ) );

		// Test 1.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
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
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 3.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertTrue( ap_have_answer_selected( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertTrue( $this->ap_ajax_success( 'selected' ) );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Unselect' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Best answer is selected for your question.' );

		// Test 4.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$user_id_1 = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$user_id_2 = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id_1 = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_1,
			)
		);
		$answer_id_2 = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id_2,
			)
		);
		ap_set_selected_answer( $question_id, $answer_id_1 );
		$this->assertTrue( ap_have_answer_selected( $question_id ) );
		$this->_set_post_data( 'answer_id=' . $answer_id_2 . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id_2 ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertFalse( $this->ap_ajax_success( 'selected' ) );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Select' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Best answer is unselected for your question.' );

		// Test 5.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Trying to cheat?!' );

		// Test 6.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'This answer cannot be selected as best, update status to select as best answer.' );

		// Test 7.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'private',
			)
		);
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->_set_post_data( 'answer_id=' . $answer_id . '&action=toggle_best_answer&__nonce=' . wp_create_nonce( 'select-answer-' . $answer_id ) );
		$this->handle( 'toggle_best_answer' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_toggle_best_answer' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'This answer cannot be selected as best, update status to select as best answer.' );
	}
}
