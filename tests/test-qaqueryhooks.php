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

	/**
	 * @covers AP_QA_Query_Hooks::imaginary_post
	 */
	public function testImaginaryPost() {
		// Test 1.
		$post = new \stdClass();
		$post->ID = 123;
		$post->post_title = 'Sample Question';
		$post->post_content = 'Sample content';
		$post->post_status = 'publish';

		// Call the imaginary_post method.
		$result = \AP_QA_Query_Hooks::imaginary_post( $post );

		// Test the returned object.
		$this->assertEquals( 0, $result->ID );
		$this->assertEquals( 'No permission', $result->post_title );
		$this->assertEquals( 'You do not have permission to read this question.', $result->post_content );
		$this->assertEquals( 'publish', $result->post_status );
		$this->assertEquals( 'question', $result->post_type );

		// Test 2.
		$post = new \stdClass();
		$post->post_status = 'moderate';

		// Call the imaginary_post method.
		$result = \AP_QA_Query_Hooks::imaginary_post( $post );

		// Test the returned object.
		$this->assertEquals( 0, $result->ID );
		$this->assertEquals( 'No permission', $result->post_title );
		$this->assertEquals( 'You do not have permission to read this question.', $result->post_content );
		$this->assertEquals( 'moderate', $result->post_status );
		$this->assertEquals( 'question', $result->post_type );
	}
}
