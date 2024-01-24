<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAPQuestionMetaBox extends TestCase {

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
}
