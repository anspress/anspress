<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestCommonPages extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'register_common_pages' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'base_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'question_permission_msg' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'question_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'ask_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'search_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'edit_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'activities_page' ) );
		$this->assertTrue( method_exists( 'AnsPress_Common_Pages', 'set_404' ) );
	}

	/**
	 * @covers AnsPress_Common_Pages::set_404
	 */
	public function testSet404() {
		ob_start();
		\AnsPress_Common_Pages::set_404();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'Error 404', $output );
	}
}
