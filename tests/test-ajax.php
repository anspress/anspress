<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjax extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	public static $current_post;

	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		self::$current_post = $factory->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
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

	public function _set_post_data( $query ) {
		$args            = wp_parse_args( $query );
		$_POST['action'] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * @covers ::ap_send_json
	 */
	public function testAPSendJSON() {
		// Test 1.
		$this->functionHandle( 'ap_send_json' );
		$expected = wp_json_encode( [ 'is_ap_ajax' => true ] );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$test_data = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 3.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'something_wrong',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 4.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'success',
			'key1'    => 'value1',
			'key2'    => 'value2',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );
	}
}
