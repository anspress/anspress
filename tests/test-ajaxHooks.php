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
}
