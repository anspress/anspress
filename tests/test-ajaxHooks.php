<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjaxHooks extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'suggest_similar_questions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'permanent_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_featured' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'close_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'send' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_tinymce' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'convert_to_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_filter_order_by' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'subscribe_to_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'search_tags' ) );
	}

	/**
	 * @covers AnsPress_Ajax::send
	 */
	public function testSend() {
		// Test 1.
		$test_data = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);
		$this->functionHandle( 'AnsPress_Ajax::send', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true, 'ap_responce' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'something_wrong',
		);
		$this->functionHandle( 'AnsPress_Ajax::send', $test_data );
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'Something went wrong, last action failed.',
				'message_type' => 'error',
			],
			'success'     => false,
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 3.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'something_wrong',
			'success' => true,
		);
		$this->functionHandle( 'AnsPress_Ajax::send', $test_data );
		$test_data['success'] = false;
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'Something went wrong, last action failed.',
				'message_type' => 'error',
			],
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 4.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'post_image_uploaded',
			'success' => false,
		);
		$this->functionHandle( 'AnsPress_Ajax::send', $test_data );
		$test_data['success'] = true;
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'Image uploaded successfully',
				'message_type' => 'success',
			],
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 5.
		$this->_last_response = '';
		$test_data = array(
			'key1'    => 'value1',
			'key2'    => 'value2',
			'message' => 'no_permission_to_view_private',
			'success' => true,
		);
		$this->functionHandle( 'AnsPress_Ajax::send', $test_data );
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'You do not have permission to view private posts.',
				'message_type' => 'warning',
			],
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Start output buffer.
		ob_start();
	}

	/**
	 * @covers AnsPress_Ajax::load_filter_order_by
	 */
	public function testLoadFilterOrderBy() {
		add_action( 'ap_ajax_load_filter_order_by', [ 'AnsPress_Ajax', 'load_filter_order_by' ] );

		// Test 1.
		$this->_set_post_data( 'ap_ajax_action=load_filter_order_by&__nonce=' . wp_create_nonce( 'filter_tags' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEmpty( $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=load_filter_order_by&filter=questions&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEmpty( $this->_last_response );

		// Test 3.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=load_filter_order_by&filter=answers&__nonce=' . wp_create_nonce( 'filter_answers' ) );
		$this->handle( 'ap_ajax' );
		$expected = wp_json_encode( [ 'success' => true, 'multiple' => false, 'items' => ap_get_questions_orderby(), 'is_ap_ajax' => true, 'ap_responce' => true ] );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 4.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=load_filter_order_by&filter=solved&__nonce=' . wp_create_nonce( 'filter_solved' ) );
		$this->handle( 'ap_ajax' );
		$expected = wp_json_encode( [ 'success' => true, 'multiple' => false, 'items' => ap_get_questions_orderby(), 'is_ap_ajax' => true, 'ap_responce' => true ] );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 5.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=load_filter_order_by&filter=invalid&__nonce=' . wp_create_nonce( 'filter_invalid' ) );
		$this->handle( 'ap_ajax' );
		$expected = wp_json_encode( [ 'success' => true, 'multiple' => false, 'items' => ap_get_questions_orderby(), 'is_ap_ajax' => true, 'ap_responce' => true ] );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );
	}

	/**
	 * @covers AnsPress_Ajax::load_tinymce
	 */
	public function testLoadTinyMCE() {
		add_action( 'ap_ajax_load_tinymce', [ 'AnsPress_Ajax', 'load_tinymce' ] );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question' ] );

		// Test 1.
		$this->_set_post_data( 'ap_ajax_action=load_tinymce&question_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertStringContainsString( 'You do not have permission to answer this question.', $this->_last_response );
		$this->assertStringContainsString( 'tinyMCEPreInit', $this->_last_response );
		$this->assertStringContainsString( 'tinymce', $this->_last_response );
		$this->assertStringContainsString( 'quicktags', $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'ap_ajax_action=load_tinymce&question_id=' . $question_id );
		@$this->handle( 'ap_ajax' );
		$this->assertStringNotContainsString( 'You do not have permission to answer this question.', $this->_last_response );
		$this->assertStringContainsString( '<form id="form_answer" name="form_answer" method="POST" enctype="multipart/form-data" action=""  apform>', $this->_last_response );
		$this->assertStringContainsString( '<div id="wp-form_answer-post_content-editor-container" class="wp-editor-container">', $this->_last_response );
		$this->assertStringContainsString( 'tinyMCEPreInit', $this->_last_response );
		$this->assertStringContainsString( 'tinymce', $this->_last_response );
		$this->assertStringContainsString( 'quicktags', $this->_last_response );
	}

	/**
	 * @covers AnsPress_Ajax::convert_to_post
	 */
	public function testConvertToPost() {
		global $wpdb;
		add_action( 'ap_ajax_action_convert_to_post', [ 'AnsPress_Ajax', 'convert_to_post' ] );

		// For user who do not have permission to convert question to post.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_convert_to_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'convert-post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, you are not allowed to convert this question to post' );

		// For user who have permission to convert question to post.
		$this->setRole( 'administrator' );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_convert_to_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, you are not allowed to convert this question to post' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_convert_to_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'convert-post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === ' Question &ldquo;Question title&rdquo; is converted to post and its answers are trashed' );
		$this->assertTrue( $this->ap_ajax_success( 'redirect' ) === get_permalink( $question_id ) );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_title' => 'What is Lorem Ipsum?' ] );
		$answer_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answer_id_1 = get_post( $answer_ids[0] );
		$this->assertTrue( $answer_id_1->post_status === 'publish' );
		$answer_id_2 = get_post( $answer_ids[1] );
		$this->assertTrue( $answer_id_2->post_status === 'publish' );
		$answer_id_3 = get_post( $answer_ids[2] );
		$this->assertTrue( $answer_id_3->post_status === 'publish' );
		$this->_set_post_data( 'ap_ajax_action=action_convert_to_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'convert-post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === ' Question &ldquo;What is Lorem Ipsum?&rdquo; is converted to post and its answers are trashed' );
		$answer_id_1 = get_post( $answer_ids[0] );
		$this->assertNull( $answer_id_1 );
		$answer_id_2 = get_post( $answer_ids[1] );
		$this->assertNull( $answer_id_2 );
		$answer_id_3 = get_post( $answer_ids[2] );
		$this->assertNull( $answer_id_3 );
	}
}
