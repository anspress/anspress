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
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'pagination_fix' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'add_query_var' ) );
		$this->assertTrue( method_exists( 'AnsPress_Rewrite', 'shortlink' ) );
	}

	/**
	 * @covers AnsPress_Rewrite::query_var
	 */
	public function testAnsPressRewriteQueryVar() {
		$this->assertEquals( 10, has_filter( 'query_vars', [ 'AnsPress_Rewrite', 'query_var' ] ) );

		// Test begins.
		$query_var = \AnsPress_Rewrite::query_var( [] );
		$this->assertTrue( in_array( 'edit_post_id', $query_var, true ) );
		$this->assertTrue( in_array( 'ap_nonce', $query_var, true ) );
		$this->assertTrue( in_array( 'question_id', $query_var, true ) );
		$this->assertTrue( in_array( 'answer_id', $query_var, true ) );
		$this->assertTrue( in_array( 'answer', $query_var, true ) );
		$this->assertTrue( in_array( 'ask', $query_var, true ) );
		$this->assertTrue( in_array( 'ap_page', $query_var, true ) );
		$this->assertTrue( in_array( 'qcat_id', $query_var, true ) );
		$this->assertTrue( in_array( 'qcat', $query_var, true ) );
		$this->assertTrue( in_array( 'qtag_id', $query_var, true ) );
		$this->assertTrue( in_array( 'q_tag', $query_var, true ) );
		$this->assertTrue( in_array( 'ap_s', $query_var, true ) );
		$this->assertTrue( in_array( 'parent', $query_var, true ) );
		$this->assertTrue( in_array( 'ap_user', $query_var, true ) );
		$this->assertTrue( in_array( 'user_page', $query_var, true ) );
		$this->assertTrue( in_array( 'ap_paged', $query_var, true ) );
	}

	/**
	 * Covers AnsPress_Rewrite::incr_hash
	 */
	public function testAnsPressRewriteIncrHash() {
		// Test begins.
		$anspress_rewrite = \AnsPress_Rewrite::incr_hash( '' );
		$this->assertEquals( 1, $anspress_rewrite );
		$anspress_rewrite = \AnsPress_Rewrite::incr_hash( '' );
		$this->assertEquals( 2, $anspress_rewrite );
		$anspress_rewrite = \AnsPress_Rewrite::incr_hash( '' );
		$this->assertEquals( 3, $anspress_rewrite );
		$anspress_rewrite = \AnsPress_Rewrite::incr_hash( '' );
		$this->assertEquals( 4, $anspress_rewrite );
		$anspress_rewrite = \AnsPress_Rewrite::incr_hash( '' );
		$this->assertEquals( 5, $anspress_rewrite );
	}

	/**
	 * Covers AnsPress_Rewrite::alter_the_query
	 */
	public function testAlterTheQuery() {
		// Test 1.
		$request = [
			'post_type'   => 'answer',
			'feed'        => 'rss',
			'question_id' => 123,
			'answer'      => 456,
		];
		$result  = \AnsPress_Rewrite::alter_the_query( $request );
		$this->assertArrayNotHasKey( 'question_id', $result );
		$this->assertArrayNotHasKey( 'answer', $result );
		$expected = [
			'post_type' => 'answer',
			'feed'      => 'rss',
		];
		$this->assertEquals( $expected, $result );

		// Test 2.
		$request = [
			'post_type' => 'answer',
			'embed'     => 'true',
			'answer_id' => 123,
		];
		$result  = \AnsPress_Rewrite::alter_the_query( $request );
		$this->assertArrayNotHasKey( 'question_id', $result );
		$this->assertArrayNotHasKey( 'answer', $result );
		$this->assertArrayHasKey( 'p', $result );
		$expected = [
			'post_type' => 'answer',
			'embed'     => 'true',
			'p'         => 123,
			'answer_id' => 123,
		];
		$this->assertEquals( $expected, $result );

		// Test 3.
		$request = [
			'post_type'   => 'question',
			'feed'        => 'rss',
			'embed'       => 'true',
			'question_id' => 123,
			'answer_id'   => 456,
		];
		$result  = \AnsPress_Rewrite::alter_the_query( $request );
		$this->assertArrayHasKey( 'question_id', $result );
		$this->assertArrayHasKey( 'feed', $result );
		$this->assertArrayHasKey( 'embed', $result );
		$this->assertArrayHasKey( 'question_id', $result );
		$this->assertArrayHasKey( 'answer_id', $result );
		$expected = [
			'post_type'   => 'question',
			'feed'        => 'rss',
			'embed'       => 'true',
			'question_id' => 123,
			'answer_id'   => 456,
		];
		$this->assertEquals( $expected, $result );

		// Test 4.
		$request = [
			'post_type'   => 'answer',
			'feed'        => 'rss',
			'embed'       => 'true',
			'question_id' => 123,
			'answer_id'   => 456,
			'answer'      => 456,
		];
		$result  = \AnsPress_Rewrite::alter_the_query( $request );
		$this->assertArrayNotHasKey( 'question_id', $result );
		$this->assertArrayNotHasKey( 'answer', $result );
		$this->assertArrayHasKey( 'p', $result );
		$expected = [
			'post_type' => 'answer',
			'feed'      => 'rss',
			'p'         => 456,
			'embed'     => 'true',
			'answer_id' => 456,
		];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Covers AnsPress_Rewrite::bp_com_paged
	 */
	public function testBPComPaged() {
		$this->setExpectedDeprecated( 'AnsPress_Rewrite::bp_com_paged' );
		$args = 'test/args';
		$result = \AnsPress_Rewrite::bp_com_paged( [ $args ] );
		$this->assertEquals( [ $args ], $result );
	}

	/**
	 * Covers AnsPress_Rewrite::add_query_var
	 */
	public function testAddQueryVarEmptyUser() {
		global $wp;
		$wp->query_vars['ap_user'] = '';
		\AnsPress_Rewrite::add_query_var( $wp );
		$this->assertFalse( isset( $wp->query_vars['ap_user_id'] ) );
	}

	/**
	 * Covers AnsPress_Rewrite::add_query_var
	 */
	public function testAddQueryVarUserFound() {
		$user_login = 'test_user';
		$user_id = $this->factory->user->create( [ 'user_login' => $user_login ] );
		global $wp;
		$wp->query_vars['ap_user'] = urldecode( $user_login );
		\AnsPress_Rewrite::add_query_var( $wp );
		$this->assertEquals( $user_id, $wp->query_vars['ap_user_id'] );
	}
}
