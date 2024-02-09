<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestCommonPages extends TestCase {

	use Testcases\Common;

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
	 * @covers AnsPress_Common_Pages::register_common_pages
	 */
	public function testRegisterCommonPages() {
		// Test for base page.
		$base = anspress()->pages['base'];
		$this->assertIsArray( $base );
		$this->assertEquals( 'Questions', $base['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'base_page' ], $base['func'] );
		$this->assertEquals( true, $base['show_in_menu'] );
		$this->assertEquals( false, $base['private'] );

		// Test for question page.
		$question = anspress()->pages['question'];
		$this->assertIsArray( $question );
		$this->assertEquals( 'Question', $question['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'question_page' ], $question['func'] );
		$this->assertEquals( false, $question['show_in_menu'] );
		$this->assertEquals( false, $question['private'] );

		// Test for ask page.
		$ask = anspress()->pages['ask'];
		$this->assertIsArray( $ask );
		$this->assertEquals( 'Ask a Question', $ask['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'ask_page' ], $ask['func'] );
		$this->assertEquals( true, $ask['show_in_menu'] );
		$this->assertEquals( false, $ask['private'] );

		// Test for search page.
		$search = anspress()->pages['search'];
		$this->assertIsArray( $search );
		$this->assertEquals( 'Search', $search['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'search_page' ], $search['func'] );
		$this->assertEquals( false, $search['show_in_menu'] );
		$this->assertEquals( false, $search['private'] );

		// Test for edit page.
		$edit = anspress()->pages['edit'];
		$this->assertIsArray( $edit );
		$this->assertEquals( 'Edit Answer', $edit['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'edit_page' ], $edit['func'] );
		$this->assertEquals( false, $edit['show_in_menu'] );
		$this->assertEquals( false, $edit['private'] );

		// Test for activities page.
		$activities = anspress()->pages['activities'];
		$this->assertIsArray( $activities );
		$this->assertEquals( 'Activities', $activities['title'] );
		$this->assertEquals( [ 'AnsPress_Common_Pages', 'activities_page' ], $activities['func'] );
		$this->assertEquals( false, $activities['show_in_menu'] );
		$this->assertEquals( false, $activities['private'] );
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

	public function APQuestionPagePermissionMsg( $msg ) {
		return 'This is a custom message';
	}

	/**
	 * @covers AnsPress_Common_Pages::question_permission_msg
	 */
	public function testQuestionPermissionMsg() {
		$instance = new \AnsPress_Common_Pages();
		$reflection = new \ReflectionClass( $instance );
		$method = $reflection->getMethod( 'question_permission_msg' );
		$method->setAccessible( true );

		// Test 1.
		$question = $this->factory()->post->create_and_get( [ 'post_type' => 'question' ] );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertFalse( $result );

		// Test 2.
		$question = $this->factory()->post->create_and_get( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertEquals( 'This question is awaiting moderation and cannot be viewed. Please check back later.', $result );

		// Test 3.
		$this->setRole( 'subscriber' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertEquals( 'This question is awaiting moderation and cannot be viewed. Please check back later.', $result );

		// Test 4.
		$this->setRole( 'administrator' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertFalse( $result );
		$this->logout();

		// Test 5.
		$question = $this->factory()->post->create_and_get( [ 'post_type' => 'question', 'post_status' => 'private_post' ] );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertEquals( 'Sorry! you are not allowed to read this question.', $result );

		// Test 6.
		$this->setRole( 'subscriber' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertEquals( 'Sorry! you are not allowed to read this question.', $result );

		// Test 7.
		$this->setRole( 'administrator' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertFalse( $result );
		$this->logout();

		// Test 8.
		$question = $this->factory()->post->create_and_get( [ 'post_type' => 'question', 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59' ] );
		$time_to_publish = human_time_diff( strtotime( $question->post_date ), ap_get_current_timestamp() );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertStringContainsString( 'Question will be published in', $result );
		$this->assertStringContainsString( $time_to_publish, $result );
		$this->assertStringContainsString( '<strong>Question will be published in ' . $time_to_publish . '</strong>', $result );
		$this->assertStringContainsString( '<p>This question is not published yet and is not accessible to anyone until it get published.</p>', $result );

		// Test 9.
		$this->setRole( 'subscriber' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertStringContainsString( 'Question will be published in', $result );
		$this->assertStringContainsString( $time_to_publish, $result );
		$this->assertStringContainsString( '<strong>Question will be published in ' . $time_to_publish . '</strong>', $result );
		$this->assertStringContainsString( '<p>This question is not published yet and is not accessible to anyone until it get published.</p>', $result );

		// Test 10.
		$this->setRole( 'administrator' );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertFalse( $result );
		$this->logout();

		// Test 11.
		add_filter( 'ap_question_page_permission_msg', [ $this, 'APQuestionPagePermissionMsg' ] );
		$question = $this->factory()->post->create_and_get( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertEquals( 'This is a custom message', $result );
		$this->assertNotEquals( 'This question is awaiting moderation and cannot be viewed. Please check back later.', $result );
		remove_filter( 'ap_question_page_permission_msg', [ $this, 'APQuestionPagePermissionMsg' ] );

		// Test 12.
		$result = $method->invokeArgs( $instance, [ $question ] );
		$this->assertNotEquals( 'This is a custom message', $result );
		$this->assertEquals( 'This question is awaiting moderation and cannot be viewed. Please check back later.', $result );
	}
}
