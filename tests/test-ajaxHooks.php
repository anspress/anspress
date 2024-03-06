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

	/**
	 * @covers AnsPress_Ajax::subscribe_to_question
	 */
	public function testSubscribeToQuestion() {
		add_action( 'ap_ajax_subscribe', [ 'AnsPress_Ajax', 'subscribe_to_question' ] );

		// For users who do not have permission to subscribe to question.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You must be logged in to subscribe to a question' );

		// For users who have permission to subscribe to question.
		$this->setRole( 'subscriber' );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to subscribe' );

		// Test 2.
		$this->_last_response = '';
		$post_id = $this->factory->post->create( [ 'post_type' => 'post', 'post_title' => 'Post Title' ] );
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $post_id . '&__nonce=' . wp_create_nonce( 'subscribe_' . $post_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully subscribed to question: Post Title' );
		$this->assertTrue( $this->ap_ajax_success( 'count' ) === '' );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Unsubscribe' );

		// Test 3.
		$user_id = $this->factory->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title', 'post_author' => $user_id ] );

		// Before Ajax call.
		$this->assertEquals( 1, ap_subscribers_count( 'question', $question_id ) );

		// After Ajax call.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $question_id . '&__nonce=' . wp_create_nonce( 'subscribe_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully subscribed to question: Question Title' );
		$this->assertTrue( $this->ap_ajax_success( 'count' ) === '2' );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Unsubscribe' );
		$this->assertEquals( 2, ap_subscribers_count( 'question', $question_id ) );

		// Test 4.
		$user_id = $this->factory->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_title' => 'What is Lorem Ipsum?', 'post_author' => $user_id ] );
		ap_new_subscriber( get_current_user_id(), 'question', $question_id );

		// Before Ajax call.
		$this->assertEquals( 2, ap_subscribers_count( 'question', $question_id ) );

		// After Ajax call.
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $question_id . '&__nonce=' . wp_create_nonce( 'subscribe_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully unsubscribed from question: What is Lorem Ipsum?' );
		$this->assertTrue( $this->ap_ajax_success( 'count' ) === '1' );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Subscribe' );
		$this->assertEquals( 1, ap_subscribers_count( 'question', $question_id ) );
	}

	/**
	 * @covers AnsPress_Ajax::suggest_similar_questions
	 */
	public function testSuggestSimilarQuestions() {
		add_action( 'ap_ajax_suggest_similar_questions', [ 'AnsPress_Ajax', 'suggest_similar_questions' ] );

		// Test for users who do not have permission to suggest similar questions.
		$this->setRole( 'subscriber' );
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEmpty( $this->_last_response );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );

		// Test for users who have permission to suggest similar questions.
		$this->setRole( 'administrator' );

		// Test 1.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEmpty( $this->_last_response );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );

		// Test 2.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'status' ) );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<div class="ap-similar-questions-head">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<p><strong>1 similar question found</strong></p>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'We have found some similar questions that have been asked earlier.', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-similar-questions">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_2 ) . '">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">0 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="highlight_word">Test</span> <span class="highlight_word">Question</span></span>', $this->ap_ajax_success( 'html' ) );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );

		// Test 3.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		add_filter( 'ap_disable_question_suggestion', '__return_true' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEmpty( $this->_last_response );
		remove_filter( 'ap_disable_question_suggestion', '__return_true' );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );

		// Test 4.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&ap_ajax_nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'status' ) );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<div class="ap-similar-questions-head">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<p><strong>1 similar question found</strong></p>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'We have found some similar questions that have been asked earlier.', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-similar-questions">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_2 ) . '">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">0 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="highlight_word">Test</span> <span class="highlight_word">Question</span></span>', $this->ap_ajax_success( 'html' ) );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );

		// Test 5.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$question_id_3 = $this->insert_question( 'What is Lorem Ipsum?' );
		$question_id_4 = $this->insert_question( 'This is another Test' );
		$question_id_5 = $this->insert_question( 'This is another Question' );
		$answer_ids_set_1 = $this->factory->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id_1 ] );
		$answer_ids_set_2 = $this->factory->post->create_many( 5, [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ] );
		$answer_ids_set_3 = $this->factory->post->create_many( 2, [ 'post_type' => 'answer', 'post_parent' => $question_id_3 ] );
		$answer_ids_set_4 = $this->factory->post->create_many( 4, [ 'post_type' => 'answer', 'post_parent' => $question_id_4 ] );
		$answer_ids_set_5 = $this->factory->post->create_many( 1, [ 'post_type' => 'answer', 'post_parent' => $question_id_5 ] );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'status' ) );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<div class="ap-similar-questions-head">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<p><strong>2 similar questions found</strong></p>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'We have found some similar questions that have been asked earlier.', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-similar-questions">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_2 ) . '">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">5 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="highlight_word">Test</span> <span class="highlight_word">Question</span></span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_4 ) . '">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">4 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'This is another <span class="highlight_word">Test</span></span>', $this->ap_ajax_success( 'html' ) );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );
		wp_delete_post( $question_id_3, true );
		wp_delete_post( $question_id_4, true );
		wp_delete_post( $question_id_5, true );

		// Test 6.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&is_admin=true&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'status' ) );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<div class="ap-similar-questions-head">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<p><strong>1 similar question found</strong></p>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'We have found some similar questions that have been asked earlier.', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-similar-questions">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-q-suggestion-item clearfix">', $this->ap_ajax_success( 'html' ) );
		$query_args = add_query_arg( [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ], admin_url( 'post-new.php' ) );
		$this->assertStringContainsString( '<a class="select-question-button button button-primary button-small" href="' . $query_args . '">Select</a>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="highlight_word">Test</span> <span class="highlight_word">Question</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">0 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringNotContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_2 ) . '">', $this->ap_ajax_success( 'html' ) );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );
		wp_delete_post( $question_id_3, true );

		// Test 7.
		$this->_last_response = '';
		$question_id_1 = $this->insert_question();
		$question_id_2 = $this->insert_question( 'Test Question' );
		$question_id_3 = $this->insert_question( 'What is Lorem Ipsum?' );
		$question_id_4 = $this->insert_question( 'This is another Test' );
		$question_id_5 = $this->insert_question( 'This is another Question' );
		$answer_ids_set_1 = $this->factory->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id_1 ] );
		$answer_ids_set_2 = $this->factory->post->create_many( 5, [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ] );
		$answer_ids_set_3 = $this->factory->post->create_many( 2, [ 'post_type' => 'answer', 'post_parent' => $question_id_3 ] );
		$answer_ids_set_4 = $this->factory->post->create_many( 4, [ 'post_type' => 'answer', 'post_parent' => $question_id_4 ] );
		$answer_ids_set_5 = $this->factory->post->create_many( 1, [ 'post_type' => 'answer', 'post_parent' => $question_id_5 ] );
		$this->_set_post_data( 'ap_ajax_action=suggest_similar_questions&value=Test Question&is_admin=true&__nonce=' . wp_create_nonce( 'suggest_similar_questions' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'status' ) );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<div class="ap-similar-questions-head">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<p><strong>2 similar questions found</strong></p>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'We have found some similar questions that have been asked earlier.', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-similar-questions">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-q-suggestion-item clearfix">', $this->ap_ajax_success( 'html' ) );
		$query_args = add_query_arg( [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ], admin_url( 'post-new.php' ) );
		$this->assertStringContainsString( '<a class="select-question-button button button-primary button-small" href="' . $query_args . '">Select</a>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="highlight_word">Test</span> <span class="highlight_word">Question</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">5 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-q-suggestion-item clearfix">', $this->ap_ajax_success( 'html' ) );
		$query_args = add_query_arg( [ 'post_type' => 'answer', 'post_parent' => $question_id_4 ], admin_url( 'post-new.php' ) );
		$this->assertStringContainsString( '<a class="select-question-button button button-primary button-small" href="' . $query_args . '">Select</a>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( 'This is another <span class="highlight_word">Test</span></span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<span class="acount">4 Answers</span>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringNotContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_2 ) . '">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringNotContainsString( '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $question_id_4 ) . '">', $this->ap_ajax_success( 'html' ) );

		// Delete already created questions.
		wp_delete_post( $question_id_1, true );
		wp_delete_post( $question_id_2, true );
		wp_delete_post( $question_id_3, true );
		wp_delete_post( $question_id_4, true );
		wp_delete_post( $question_id_5, true );
	}

	/**
	 * @covers AnsPress_Ajax::toggle_delete_post
	 */
	public function testToggleDeletePost() {
		add_action( 'ap_ajax_action_toggle_delete_post', [ 'AnsPress_Ajax', 'toggle_delete_post' ] );

		// For users who do not have permission to delete post.
		$question_id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to trash this post' );

		// For users who have permission to delete post.
		// For normal user.
		$this->setRole( 'subscriber' );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to trash this post' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Undelete' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Restore this question' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is trashed' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'trash' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div>' );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'trash' ] );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === false );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Delete' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Delete this question (can be restored again)' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is restored' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'publish' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '' );

		// For administrator.
		$this->setRole( 'administrator' );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to trash this post' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Undelete' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Restore this question' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is trashed' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'trash' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div>' );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'trash' ] );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === false );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Delete' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Delete this question (can be restored again)' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is restored' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'publish' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '' );

		// Test for delete after.
		// Test 1.
		$this->setRole( 'ap_moderator' );
		$this->_last_response = '';
		$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'This post was created January 1, 2020, hence you cannot trash it' );

		// Test 2.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );

			// Tests.
			// Before granting super admin role.
			$this->_last_response = '';
			$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
			$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertFalse( $this->ap_ajax_success( 'success' ) );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'This post was created January 1, 2020, hence you cannot trash it' );

			// After granting super admin role.
			grant_super_admin( get_current_user_id() );
			$this->_last_response = '';
			$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
			$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Undelete' );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Restore this question' );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is trashed' );
			$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'trash' );
			$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div>' );
		} else {
			$this->setRole( 'administrator' );

			// Tests.
			$this->_last_response = '';
			$question_id = $this->factory->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
			$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Undelete' );
			$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Restore this question' );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is trashed' );
			$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'trash' );
			$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '<div class="ap-notice status-trash"><i class="apicon-trashcan"></i><span>This Question has been trashed, you can delete it permanently from wp-admin.</span></div>' );
		}
	}
}
