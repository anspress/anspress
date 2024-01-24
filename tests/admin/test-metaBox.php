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
}
