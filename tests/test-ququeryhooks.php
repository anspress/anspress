<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAQueryHooks extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_QA_Query_Hooks', 'sql_filter' ) );
		$this->assertTrue( method_exists( 'AP_QA_Query_Hooks', 'posts_results' ) );
		$this->assertTrue( method_exists( 'AP_QA_Query_Hooks', 'imaginary_post' ) );
		$this->assertTrue( method_exists( 'AP_QA_Query_Hooks', 'modify_main_posts' ) );
		$this->assertTrue( method_exists( 'AP_QA_Query_Hooks', 'pre_get_posts' ) );
	}
}
