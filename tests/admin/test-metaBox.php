<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAPQuestionMetaBox extends TestCase {

	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'add_meta_box' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'answers_meta_box_content' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'question_meta_box_content' ) );
		$this->assertTrue( method_exists( 'AP_Question_Meta_Box', 'flag_meta_box' ) );
	}

	/**
	 * @covers AP_Question_Meta_Box::__construct
	 */
	public function testConstruct() {
		$meta_box = new \AP_Question_Meta_Box();
		$this->assertEquals( 10, has_action( 'add_meta_boxes', [ $meta_box, 'add_meta_box' ] ) );
	}

	/**
	 * @covers AP_Question_Meta_Box::add_meta_box
	 */
	public function testadd_meta_box() {
		// Metabox for question post type has ap_get_answers_count which returns falls
		// if there is no question id in url so we test via visiting the question page.
		$id = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $id->q );
		$meta_box = new \AP_Question_Meta_Box();

		// Test for question post type.
		$GLOBALS['wp_meta_boxes']['question']['normal']['high'] = [];
		$GLOBALS['wp_meta_boxes']['question']['side']['high'] = [];
		$GLOBALS['wp_meta_boxes']['answer']['side']['high'] = [];
		$meta_box->add_meta_box( 'question' );

		// Test on Answers meta box.
		$this->assertArrayHasKey( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high'] );
		$this->assertEquals( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['id'] );
		$this->assertEquals( ' 1 Answers', $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'answers_meta_box_content' ], $GLOBALS['wp_meta_boxes']['question']['normal']['high']['ap_answers_meta_box']['callback'] );

		// Test on Question meta box.
		$this->assertArrayHasKey( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['question']['side']['high'] );
		$this->assertEquals( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['id'] );
		$this->assertEquals( 'Question', $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'question_meta_box_content' ], $GLOBALS['wp_meta_boxes']['question']['side']['high']['ap_question_meta_box']['callback'] );

		// Test for answer post type.
		$GLOBALS['wp_meta_boxes']['question']['normal']['high'] = [];
		$GLOBALS['wp_meta_boxes']['question']['side']['high'] = [];
		$GLOBALS['wp_meta_boxes']['answer']['side']['high'] = [];
		$meta_box->add_meta_box( 'answer' );

		// Test on Answers meta box.
		$this->assertArrayNotHasKey( 'ap_answers_meta_box', $GLOBALS['wp_meta_boxes']['question']['normal']['high'] );

		// Test on Question meta box.
		$this->assertArrayHasKey( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['answer']['side']['high'] );
		$this->assertEquals( 'ap_question_meta_box', $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['id'] );
		$this->assertEquals( 'Question', $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['title'] );
		$this->assertEquals( [ $meta_box, 'question_meta_box_content' ], $GLOBALS['wp_meta_boxes']['answer']['side']['high']['ap_question_meta_box']['callback'] );
	}

	/**
	 * @covers AP_Question_Meta_Box::flag_meta_box
	 */
	public function testFlagMetaBox() {
		$meta_box = new \AP_Question_Meta_Box();
		$id = $this->insert_answer();

		// Store the result of the method in a variable.
		ob_start();
		$meta_box->flag_meta_box( get_post( $id->q ) );
		$result = ob_get_clean();

		// Test begins.
		// Test 1.
		$args = array(
			'action'         => 'ap_ajax',
			'ap_ajax_action' => 'ap_clear_flag',
			'__nonce'        => wp_create_nonce( 'clear_flag_' . $id->q ),
			'post_id'        => $id->q,
		);
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">0</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );
		$this->assertStringContainsString( '<script type="text/javascript">', $result );
		$this->assertStringContainsString( '$(\'#ap-clear-flag\')', $result );
		$this->assertStringContainsString( '$.ajax', $result );

		// Test 2.
		ap_add_flag( $id->q );
		$user_id = $this->factory()->user->create();
		ap_add_flag( $id->q, $user_id );
		ap_update_flags_count( $id->q );
		ob_start();
		$meta_box->flag_meta_box( get_post( $id->q ) );
		$result = ob_get_clean();
		$this->assertStringContainsString( '<i class="apicon-flag"></i>', $result );
		$this->assertStringContainsString( '<strong class="ap-question-flag-count">2</strong> ', $result );
		$this->assertStringContainsString( 'Flag', $result );
		$this->assertStringContainsString( '<a id="ap-clear-flag" href="#" data-query="' . esc_js( wp_json_encode( $args ) ) . '" class="flag-clear" data-cb="afterFlagClear">Clear flag</a>', $result );
		$this->assertStringContainsString( '<script type="text/javascript">', $result );
		$this->assertStringContainsString( '$(\'#ap-clear-flag\')', $result );
		$this->assertStringContainsString( '$.ajax', $result );
	}
}
