<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestRewrite extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Rewrite' );
		$this->assertTrue( $class->hasProperty( 'counter' ) && $class->getProperty( 'counter' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'counter' ) && $class->getProperty( 'counter' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'alter_the_query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'query_var' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'rewrites' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'incr_hash' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'bp_com_paged' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'add_query_var' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'shortlink' ) );
	}

	/**
	 * @covers AnsPress_Rewrite::query_var
	 */
	public function testAnsPressRewriteQueryVar() {
		$this->assertEquals( 10, has_filter( 'query_vars', [ 'AnsPress_Rewrite', 'query_var' ] ) );

		// Test begins.
		$anspress_rewrite = \AnsPress_Rewrite::query_var( [] );
		$this->assertTrue( in_array( 'edit_post_id', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ap_nonce', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'question_id', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'answer_id', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'answer', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ask', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ap_page', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'qcat_id', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'qcat', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'qtag_id', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'q_tag', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ap_s', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'parent', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ap_user', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'user_page', $anspress_rewrite, true ) );
		$this->assertTrue( in_array( 'ap_paged', $anspress_rewrite, true ) );
	}
}
