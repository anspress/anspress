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
}
