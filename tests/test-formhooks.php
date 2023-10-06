<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFormHooks extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'question_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'answer_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'comment_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_question_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_answer_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_comment_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'image_upload_save' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'image_upload_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'create_user' ) );
	}
}
