<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjaxHooks extends TestCaseAjax {

	use Testcases\Ajax;
	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
		register_taxonomy( 'question_tag', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		unregister_taxonomy( 'question_tag' );
		parent::tear_down();
	}

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
	 * @covers AnsPress_Ajax::init
	 */
	public function testInit() {
		$instance = \AnsPress_Ajax::init();
		anspress()->setup_hooks();

		// Tests.
		$this->assertEquals( 10, has_action( 'ap_ajax_suggest_similar_questions', [ 'AnsPress_Ajax', 'suggest_similar_questions' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_load_tinymce', [ 'AnsPress_Ajax', 'load_tinymce' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_load_comments', [ 'AnsPress_Comment_Hooks', 'load_comments' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_edit_comment_form', [ 'AnsPress_Comment_Hooks', 'edit_comment_form' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_edit_comment', [ 'AnsPress_Comment_Hooks', 'edit_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_approve_comment', [ 'AnsPress_Comment_Hooks', 'approve_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_vote', [ 'AnsPress_Vote', 'vote' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_delete_comment', [ 'AnsPress\Ajax\Comment_Delete', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_comment_modal', [ 'AnsPress\Ajax\Comment_Modal', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_nopriv_comment_modal', [ 'AnsPress\Ajax\Comment_Modal', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_toggle_best_answer', [ 'AnsPress\Ajax\Toggle_Best_Answer', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_post_actions', [ 'AnsPress_Theme', 'post_actions' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_toggle_featured', [ 'AnsPress_Ajax', 'toggle_featured' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_close', [ 'AnsPress_Ajax', 'close_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_toggle_delete_post', [ 'AnsPress_Ajax', 'toggle_delete_post' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_delete_permanently', [ 'AnsPress_Ajax', 'permanent_delete_post' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_convert_to_post', [ 'AnsPress_Ajax', 'convert_to_post' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_delete_attachment', [ 'AnsPress_Uploader', 'delete_attachment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_load_filter_order_by', [ 'AnsPress_Ajax', 'load_filter_order_by' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_subscribe', [ 'AnsPress_Ajax', 'subscribe_to_question' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_repeatable_field', [ 'AnsPress\Ajax\Repeatable_Field', 'init' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_nopriv_ap_repeatable_field', [ 'AnsPress\Ajax\Repeatable_Field', 'init' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_ap_form_question', [ 'AP_Form_Hooks', 'submit_question_form' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_nopriv_ap_form_question', [ 'AP_Form_Hooks', 'submit_question_form' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_ap_form_answer', [ 'AP_Form_Hooks', 'submit_answer_form' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_nopriv_ap_form_answer', [ 'AP_Form_Hooks', 'submit_answer_form' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_ap_form_comment', [ 'AP_Form_Hooks', 'submit_comment_form' ] ) );
		$this->assertEquals( 11, has_action( 'wp_ajax_nopriv_ap_form_comment', [ 'AP_Form_Hooks', 'submit_comment_form' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_search_tags', [ 'AnsPress_Ajax', 'search_tags' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_nopriv_ap_search_tags', [ 'AnsPress_Ajax', 'search_tags' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_image_upload', [ 'AnsPress_Uploader', 'image_upload' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_ap_upload_modal', [ 'AnsPress_Uploader', 'upload_modal' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_nopriv_ap_upload_modal', [ 'AnsPress_Uploader', 'upload_modal' ] ) );
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
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );

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
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'What is Lorem Ipsum?' ] );
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
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
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post', 'post_title' => 'Post Title' ] );
		$this->_set_post_data( 'ap_ajax_action=subscribe&id=' . $post_id . '&__nonce=' . wp_create_nonce( 'subscribe_' . $post_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully subscribed to question: Post Title' );
		$this->assertTrue( $this->ap_ajax_success( 'count' ) === '' );
		$this->assertTrue( $this->ap_ajax_success( 'label' ) === 'Unsubscribe' );

		// Test 3.
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title', 'post_author' => $user_id ] );

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
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'What is Lorem Ipsum?', 'post_author' => $user_id ] );
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
		$answer_ids_set_1 = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id_1 ] );
		$answer_ids_set_2 = $this->factory()->post->create_many( 5, [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ] );
		$answer_ids_set_3 = $this->factory()->post->create_many( 2, [ 'post_type' => 'answer', 'post_parent' => $question_id_3 ] );
		$answer_ids_set_4 = $this->factory()->post->create_many( 4, [ 'post_type' => 'answer', 'post_parent' => $question_id_4 ] );
		$answer_ids_set_5 = $this->factory()->post->create_many( 1, [ 'post_type' => 'answer', 'post_parent' => $question_id_5 ] );
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
		$answer_ids_set_1 = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id_1 ] );
		$answer_ids_set_2 = $this->factory()->post->create_many( 5, [ 'post_type' => 'answer', 'post_parent' => $question_id_2 ] );
		$answer_ids_set_3 = $this->factory()->post->create_many( 2, [ 'post_type' => 'answer', 'post_parent' => $question_id_3 ] );
		$answer_ids_set_4 = $this->factory()->post->create_many( 4, [ 'post_type' => 'answer', 'post_parent' => $question_id_4 ] );
		$answer_ids_set_5 = $this->factory()->post->create_many( 1, [ 'post_type' => 'answer', 'post_parent' => $question_id_5 ] );
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
		$question_id = $this->factory()->post->create_and_get([
			'post_type' => 'question',
			'post_title' => 'Question title',
			'post_author' => get_current_user_id(),
		])->ID;
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
		$question_id = $this->factory()->post->create_and_get([
			'post_type' => 'question',
			'post_title' => 'Question title',
			'post_author' => get_current_user_id(),
		])->ID;
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
		$this->setRole( 'administrator', true );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->factory()->post->create_and_get([
			'post_type' => 'question',
			'post_title' => 'Question title',
			'post_author' => get_current_user_id(),
		])->ID;
		$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to trash this post' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->factory()->post->create_and_get([
			'post_type' => 'question',
			'post_title' => 'Question title',
			'post_author' => get_current_user_id(),
		])->ID;
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
		$question_id = $this->factory()->post->create_and_get([
			'post_type' => 'question',
			'post_title' => 'Question title',
			'post_author' => get_current_user_id(),
		])->ID;
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
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
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
			$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
			$this->_set_post_data( 'ap_ajax_action=action_toggle_delete_post&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'trash_post_' . $question_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertFalse( $this->ap_ajax_success( 'success' ) );
			$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'This post was created January 1, 2020, hence you cannot trash it' );

			// After granting super admin role.
			grant_super_admin( get_current_user_id() );
			$this->_last_response = '';
			$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
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
			$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_date' => '2020:01:01 00:00:00' ] );
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

	/**
	 * @covers AnsPress_Ajax::permanent_delete_post
	 */
	public function testPermanentDeletePost() {
		add_action( 'ap_ajax_action_delete_permanently', [ 'AnsPress_Ajax', 'permanent_delete_post' ] );

		// Create base page.
		$base_page = $this->factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Base Page' ] );
		ap_opt( 'base_page', $base_page );

		// For testing the actions.
		$trash_question_triggered = false;
		add_action( 'ap_wp_trash_question', function( $post_id ) use ( &$trash_question_triggered ) {
			$trash_question_triggered = true;
		} );
		$trash_answer_triggered = false;
		add_action( 'ap_wp_trash_answer', function( $post_id ) use ( &$trash_answer_triggered ) {
			$trash_answer_triggered = true;
		} );

		// For users who do not have permission to delete post.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'delete_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to delete post' );

		// For users who have permission to delete post.
		$this->setRole( 'administrator' );

		// Test 1.
		$trash_question_triggered = false;
		$trash_answer_triggered = false;
		$question_id = $this->insert_question();
		$this->assertFalse( $trash_question_triggered );
		$this->assertFalse( $trash_answer_triggered );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to delete post' );
		$this->assertFalse( did_action( 'ap_wp_trash_question' ) > 0 );
		$this->assertFalse( did_action( 'ap_wp_trash_answer' ) > 0 );

		// Test 2.
		$trash_question_triggered = false;
		$question_id = $this->insert_question();
		$this->assertFalse( $trash_question_triggered );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'delete_post_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is deleted permanently' );
		$this->assertTrue( $this->ap_ajax_success( 'redirect' ) === ap_base_page_link() );
		$this->assertTrue( $trash_question_triggered );
		$this->assertTrue( did_action( 'ap_wp_trash_question' ) > 0 );

		// Test 3.
		$trash_answer_triggered = false;
		$id = $this->insert_answer();
		$this->assertFalse( $trash_answer_triggered );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $id->a . '&__nonce=' . wp_create_nonce( 'delete_post_' . $id->a ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Answer is deleted permanently' );
		$this->assertTrue( $this->ap_ajax_success( 'deletePost' ) === $id->a );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->text === '0 Answers' );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->number === '0' );
		$this->assertTrue( $trash_answer_triggered );
		$this->assertTrue( did_action( 'ap_wp_trash_answer' ) > 0 );

		// Test 4.
		$trash_answer_triggered = false;
		$ids = $this->insert_answers( [], [], 3 );
		$this->assertFalse( $trash_answer_triggered );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $ids['answers'][0] . '&__nonce=' . wp_create_nonce( 'delete_post_' . $ids['answers'][0] ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Answer is deleted permanently' );
		$this->assertTrue( $this->ap_ajax_success( 'deletePost' ) === $ids['answers'][0] );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->text === '2 Answers' );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->number === '2' );
		$this->assertTrue( $trash_answer_triggered );
		$this->assertTrue( did_action( 'ap_wp_trash_answer' ) > 0 );

		// Test 5.
		$trash_answer_triggered = false;
		$ids = $this->insert_answers( [], [], 2 );
		$this->assertFalse( $trash_answer_triggered );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_delete_permanently&post_id=' . $ids['answers'][1] . '&__nonce=' . wp_create_nonce( 'delete_post_' . $ids['answers'][1] ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Answer is deleted permanently' );
		$this->assertTrue( $this->ap_ajax_success( 'deletePost' ) === $ids['answers'][1] );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->text === '1 Answer' );
		$this->assertTrue( $this->ap_ajax_success( 'answersCount' )->number === '1' );
		$this->assertTrue( $trash_answer_triggered );
		$this->assertTrue( did_action( 'ap_wp_trash_answer' ) > 0 );
	}

	/**
	 * @covers AnsPress_Ajax::toggle_featured
	 */
	public function testToggleFeatured() {
		add_action( 'ap_ajax_action_toggle_featured', [ 'AnsPress_Ajax', 'toggle_featured' ] );

		// For users who do not have permission to feature post.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_toggle_featured&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'set_featured_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, you cannot toggle a featured question' );

		// For users who have permission to feature post.
		$this->setRole( 'administrator' );

		// Test 1.
		$question_id = $this->insert_question();
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_toggle_featured&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, you cannot toggle a featured question' );

		// Test 2.
		$post_id = $this->factory()->post->create();
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_toggle_featured&post_id=' . $post_id . '&__nonce=' . wp_create_nonce( 'set_featured_' . $post_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Only question can be set as featured' );

		// Test 3.
		$question_id = $this->insert_question();
		$this->assertFalse( ap_is_featured_question( $question_id ) );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_toggle_featured&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'set_featured_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( ap_is_featured_question( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Unfeature' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Unmark this question as featured' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is marked as featured.' );
		$recent_activity = ap_get_recent_activity( $question_id );
		$this->assertEquals( 'question', $recent_activity->action['ref_type'] );
		$this->assertEquals( 'Marked as featured question', $recent_activity->action['verb'] );
		$this->assertEquals( 'apicon-star', $recent_activity->action['icon'] );

		// Test 4.
		$question_id = $this->insert_question();
		ap_set_featured_question( $question_id );
		$this->assertTrue( ap_is_featured_question( $question_id ) );
		$this->_last_response = '';
		$this->_set_post_data( 'ap_ajax_action=action_toggle_featured&post_id=' . $question_id . '&__nonce=' . wp_create_nonce( 'set_featured_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( ap_is_featured_question( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === false );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Feature' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Mark this question as featured' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is unmarked as featured.' );
		$recent_activity = ap_get_recent_activity( $question_id );
		$this->assertEquals( 'question', $recent_activity->action['ref_type'] );
		$this->assertEquals( 'Unfeatured the question', $recent_activity->action['verb'] );
		$this->assertEquals( 'apicon-question', $recent_activity->action['icon'] );
	}

	/**
	 * @covers AnsPress_Ajax::close_question
	 */
	public function testCloseQuestion() {
		add_action( 'ap_ajax_action_close', [ 'AnsPress_Ajax', 'close_question' ] );

		// For users who do not have permission to close question.
		// Test 1.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_close&post_id=' . $question_id . '&nonce=' . wp_create_nonce( 'close_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You cannot close a question' );

		// Test 2.
		$this->setRole( 'subscriber' );
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_close&post_id=' . $question_id . '&nonce=' . wp_create_nonce( 'close_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You cannot close a question' );

		// For users who have permission to close question.
		$this->setRole( 'administrator' );

		// Test 1.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_close&post_id=' . $question_id . '&nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You cannot close a question' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 0, $get_qameta->closed );
		$this->_set_post_data( 'ap_ajax_action=action_close&post_id=' . $question_id . '&nonce=' . wp_create_nonce( 'close_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 1, $get_qameta->closed );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Open' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Open this question for new answers' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question closed' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '<div class="ap-notice status-publish closed"><i class="apicon-x"></i><span>Question is closed for new answers.</span></div>' );
		$recent_activity = ap_get_recent_activity( $question_id );
		$this->assertEquals( 'question', $recent_activity->action['ref_type'] );
		$this->assertEquals( 'Marked as closed', $recent_activity->action['verb'] );
		$this->assertEquals( 'apicon-alert', $recent_activity->action['icon'] );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		ap_insert_qameta( $question_id, array( 'closed' => 1 ) );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 1, $get_qameta->closed );
		$this->_set_post_data( 'ap_ajax_action=action_close&post_id=' . $question_id . '&nonce=' . wp_create_nonce( 'close_' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 0, $get_qameta->closed );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->label === 'Close' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->title === 'Close this question for new answer.' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question is opened' );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === '' );
		$recent_activity = ap_get_recent_activity( $question_id );
		$this->assertEquals( 'question', $recent_activity->action['ref_type'] );
		$this->assertEquals( 'Re-opened the question', $recent_activity->action['verb'] );
		$this->assertEquals( 'apicon-question', $recent_activity->action['icon'] );
	}

	/**
	 * @covers AnsPress_Ajax::search_tags
	 */
	public function testSearchTags() {
		add_action( 'wp_ajax_ap_search_tags', [ 'AnsPress_Ajax', 'search_tags' ] );

		// Test for tags.
		// Create some tags for testing.
		$tag_id_1 = $this->factory()->term->create( [ 'name' => 'Tag 1', 'description' => 'Description for Tag 1', 'taxonomy' => 'question_tag' ] );
		$tag_id_2 = $this->factory()->term->create( [ 'name' => 'Tag 2', 'description' => 'Description for Tag 2', 'taxonomy' => 'question_tag' ] );
		$tag_id_3 = $this->factory()->term->create( [ 'name' => 'Test Tag', 'description' => 'Description for Test Tag', 'taxonomy' => 'question_tag' ] );
		$tag_id_4 = $this->factory()->term->create( [ 'name' => 'Another Tag', 'description' => 'Description for Another Tag', 'taxonomy' => 'question_tag' ] );
		$tag_id_5 = $this->factory()->term->create( [ 'name' => 'AnsPress', 'description' => 'Description for AnsPress', 'taxonomy' => 'question_tag' ] );
		$tag_id_6 = $this->factory()->term->create( [ 'name' => 'Question', 'description' => 'Description for Question', 'taxonomy' => 'question_tag' ] );
		$tag_id_7 = $this->factory()->term->create( [ 'name' => 'Answer', 'description' => 'Description for Answer', 'taxonomy' => 'question_tag' ] );
		$tag_id_8 = $this->factory()->term->create( [ 'name' => 'WordPress', 'description' => 'Description for WordPress', 'taxonomy' => 'question_tag' ] );
		$tag_id_9 = $this->factory()->term->create( [ 'name' => 'Themes', 'description' => 'Description for Themes', 'taxonomy' => 'question_tag' ] );
		$tag_id_10 = $this->factory()->term->create( [ 'name' => 'Plugins', 'description' => 'Description for Plugins', 'taxonomy' => 'question_tag' ] );

		// Create a valid form for testing.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'tags' => [
					'type'       => 'tags',
					'terms_args' => [
						'taxonomy'   => 'question_tag',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					],
				],
			],
		] );

		// Test 1.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Tag&form=Sample Form&field=tags&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_search_tags' );
		$this->assertEquals( '"{}"', $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Tag&form=Test Form&field=tags&__nonce=' . wp_create_nonce( 'tags_Test Formtags' ) );
		$this->handle( 'ap_search_tags' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Test 3.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=WordPress&form=Sample Form&field=tags&__nonce=' . wp_create_nonce( 'tags_Sample Formtags' ) );
		$this->handle( 'ap_search_tags' );
		$response = json_decode( $this->_last_response );
		$expected = [
			[
				'term_id'     => $tag_id_8,
				'name'        => 'WordPress',
				'description' => 'Description for WordPress',
				'count'       => '0 Questions',
			],
		];
		foreach ( $response as $result ) {
			$this->assertTrue( in_array( (array) $result, $expected, true ) );
		}

		// Test 4.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Tag&form=Sample Form&field=tags&__nonce=' . wp_create_nonce( 'tags_Sample Formtags' ) );
		$this->handle( 'ap_search_tags' );
		$response = json_decode( $this->_last_response );
		$expected = [
			[
				'term_id'     => $tag_id_1,
				'name'        => 'Tag 1',
				'description' => 'Description for Tag 1',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $tag_id_2,
				'name'        => 'Tag 2',
				'description' => 'Description for Tag 2',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $tag_id_3,
				'name'        => 'Test Tag',
				'description' => 'Description for Test Tag',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $tag_id_4,
				'name'        => 'Another Tag',
				'description' => 'Description for Another Tag',
				'count'       => '0 Questions',
			],
		];
		foreach ( $response as $result ) {
			$this->assertTrue( in_array( (array) $result, $expected, true ) );
		}

		// Test 5.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'tags' => [
					'type'       => 'categories',
					'terms_args' => [
						'taxonomy'   => 'question_tag',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					],
				],
			],
		] );
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Tag&form=Sample Form&field=tags&__nonce=' . wp_create_nonce( 'tags_Sample Formtags' ) );
		$this->handle( 'ap_search_tags' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Test for categories.
		// Create some categories for testing.
		$cat_id_1 = $this->factory()->term->create( [ 'name' => 'Category 1', 'description' => 'Description for Category 1', 'taxonomy' => 'question_category' ] );
		$cat_id_2 = $this->factory()->term->create( [ 'name' => 'Category 2', 'description' => 'Description for Category 2', 'taxonomy' => 'question_category' ] );
		$cat_id_3 = $this->factory()->term->create( [ 'name' => 'Test Category', 'description' => 'Description for Test Category', 'taxonomy' => 'question_category' ] );
		$cat_id_4 = $this->factory()->term->create( [ 'name' => 'Another Category', 'description' => 'Description for Another Category', 'taxonomy' => 'question_category' ] );
		$cat_id_5 = $this->factory()->term->create( [ 'name' => 'AnsPress', 'description' => 'Description for AnsPress', 'taxonomy' => 'question_category' ] );
		$cat_id_6 = $this->factory()->term->create( [ 'name' => 'Question', 'description' => 'Description for Question', 'taxonomy' => 'question_category' ] );
		$cat_id_7 = $this->factory()->term->create( [ 'name' => 'Answer', 'description' => 'Description for Answer', 'taxonomy' => 'question_category' ] );
		$cat_id_8 = $this->factory()->term->create( [ 'name' => 'WordPress', 'description' => 'Description for WordPress', 'taxonomy' => 'question_category' ] );
		$cat_id_9 = $this->factory()->term->create( [ 'name' => 'Themes', 'description' => 'Description for Themes', 'taxonomy' => 'question_category' ] );
		$cat_id_10 = $this->factory()->term->create( [ 'name' => 'Plugins', 'description' => 'Description for Plugins', 'taxonomy' => 'question_category' ] );

		// Create a valid form for testing.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [
			'fields' => [
				'categories' => [
					'type'       => 'tags',
					'terms_args' => [
						'taxonomy'   => 'question_category',
						'hide_empty' => false,
						'fields'     => 'id=>name',
					],
				],
			],
		] );

		// Test 1.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Category&form=Sample Form&field=categories&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_search_tags' );
		$this->assertEquals( '"{}"', $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=WordPress&form=Sample Form&field=categories&__nonce=' . wp_create_nonce( 'categories_Sample Formcategories' ) );
		$this->handle( 'ap_search_tags' );
		$response = json_decode( $this->_last_response );
		$expected = [
			[
				'term_id'     => $cat_id_8,
				'name'        => 'WordPress',
				'description' => 'Description for WordPress',
				'count'       => '0 Questions',
			],
		];
		foreach ( $response as $result ) {
			$this->assertTrue( in_array( (array) $result, $expected, true ) );
		}

		// Test 3.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_search_tags&q=Category&form=Sample Form&field=categories&__nonce=' . wp_create_nonce( 'categories_Sample Formcategories' ) );
		$this->handle( 'ap_search_tags' );
		$response = json_decode( $this->_last_response );
		$expected = [
			[
				'term_id'     => $cat_id_1,
				'name'        => 'Category 1',
				'description' => 'Description for Category 1',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $cat_id_2,
				'name'        => 'Category 2',
				'description' => 'Description for Category 2',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $cat_id_3,
				'name'        => 'Test Category',
				'description' => 'Description for Test Category',
				'count'       => '0 Questions',
			],
			[
				'term_id'     => $cat_id_4,
				'name'        => 'Another Category',
				'description' => 'Description for Another Category',
				'count'       => '0 Questions',
			],
		];
		foreach ( $response as $result ) {
			$this->assertTrue( in_array( (array) $result, $expected, true ) );
		}
	}
}
