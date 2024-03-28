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
		// Remove all pages.
		anspress()->pages = [];
		$this->assertTrue( empty( anspress()->pages ) );
		$this->assertTrue( empty( anspress()->pages['base'] ) );
		$this->assertTrue( empty( anspress()->pages['question'] ) );
		$this->assertTrue( empty( anspress()->pages['ask'] ) );
		$this->assertTrue( empty( anspress()->pages['search'] ) );
		$this->assertTrue( empty( anspress()->pages['edit'] ) );
		$this->assertTrue( empty( anspress()->pages['activities'] ) );

		// Register common pages.
		\AnsPress_Common_Pages::register_common_pages();

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

	/**
	 * @covers AnsPress_Common_Pages::question_page
	 */
	public function testQuestionPage() {
		global $question_rendered, $answers;
		add_action( 'ap_after_question', function() {} );

		// Test begins.
		// Test 1.
		$this->assertFalse( $question_rendered );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$question = get_post( $question_id );
		ob_start();
		\AnsPress_Common_Pages::question_page();
		$output = ob_get_clean();
		$this->assertEquals( '<div class="ap-no-permission">This question is awaiting moderation and cannot be viewed. Please check back later.</div>', $output );
		$this->assertStringNotContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $output );
		$this->assertStringNotContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $output );
		$this->assertStringNotContainsString( '<meta itemprop="@id" content="' . $question->ID . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringNotContainsString( '<meta itemprop="name" content="' . $question->post_title . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertFalse( did_action( 'ap_after_question' ) > 0 );
		$this->assertTrue( $question_rendered );

		// Test 2.
		$question_rendered = false;
		$this->assertFalse( $question_rendered );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'future', 'post_date' => '9999-12-31 23:59:59' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$question = get_post( $question_id );
		$time_to_publish = human_time_diff( strtotime( $question->post_date ), ap_get_current_timestamp() );
		ob_start();
		\AnsPress_Common_Pages::question_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="ap-no-permission"><strong>Question will be published in ' . $time_to_publish . '</strong><p>This question is not published yet and is not accessible to anyone until it get published.</p></div>', $output );
		$this->assertStringNotContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $output );
		$this->assertStringNotContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $output );
		$this->assertStringNotContainsString( '<meta itemprop="@id" content="' . $question->ID . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringNotContainsString( '<meta itemprop="name" content="' . $question->post_title . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertFalse( did_action( 'ap_after_question' ) > 0 );
		$this->assertTrue( $question_rendered );

		// Test 3.
		$question_rendered = false;
		$this->assertFalse( $question_rendered );
		$ids = $this->insert_answers( [], [], 5 );
		ap_set_selected_answer( $ids['question'], $ids['answers'][3] );
		$this->go_to( '?post_type=question&p=' . $ids['question'] );
		$question = get_post( $ids['question'] );
		ob_start();
		\AnsPress_Common_Pages::question_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $output );
		$this->assertStringContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $output );
		$this->assertStringContainsString( '<meta itemprop="@id" content="' . $question->ID . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringContainsString( '<meta itemprop="name" content="' . $question->post_title . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringContainsString( '<div class="ap-question-meta clearfix">', $output );
		$this->assertStringContainsString( '<div ap="question" apid="' . $question->ID . '">', $output );
		$this->assertStringContainsString( '<div id="question" role="main" class="ap-content">', $output );
		$this->assertStringContainsString( '<div class="ap-single-vote">', $output );
		$this->assertStringContainsString( '<div class="ap-avatar">', $output );
		$this->assertStringContainsString( '<div class="ap-cell clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-q-metas">', $output );
		$this->assertStringContainsString( '<span class="ap-author" itemprop="author" itemscope itemtype="http://schema.org/Person">', $output );
		$this->assertStringContainsString( '<span class="ap-comments-count">', $output );
		$this->assertStringContainsString( '<div class="question-content ap-q-content" itemprop="text">', $output );
		$this->assertStringContainsString( '<div class="ap-post-footer clearfix">', $output );
		$this->assertStringContainsString( '<apcomments id="comments-' . esc_attr( $question->ID ) . '" class="have-comments">', $output );
		$this->assertStringContainsString( '<apanswersw style="">', $output );
		$this->assertStringContainsString( '<div id="ap-answers-c">', $output );
		$this->assertStringContainsString( '<div class="ap-sorting-tab clearfix">', $output );
		$this->assertStringContainsString( '<div id="answers">', $output );
		$this->assertStringContainsString( '<div id="post-' . $ids['answers'][0] . '" class="answer" apid="' . $ids['answers'][0] . '" ap="answer">', $output );
		$this->assertStringContainsString( '<div class="ap-content" itemprop="suggestedAnswer" itemscope itemtype="https://schema.org/Answer">', $output );
		$this->assertStringContainsString( '<div id="post-' . $ids['answers'][3] . '" class="answer best-answer" apid="' . $ids['answers'][3] . '" ap="answer">', $output );
		$this->assertStringContainsString( '<div class="ap-content" itemprop="suggestedAnswer acceptedAnswer" itemscope itemtype="https://schema.org/Answer">', $output );
		$this->assertStringContainsString( '<div class="ap-login">', $output );
		$this->assertStringContainsString( '<div class="ap-pagination clearfix">', $output );
		$this->assertTrue( did_action( 'ap_after_question' ) > 0 );
		$this->assertTrue( $question_rendered );

		// Test 4.
		$question_rendered = false;
		$this->assertFalse( $question_rendered );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$question = get_post( $question_id );
		ob_start();
		\AnsPress_Common_Pages::question_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $output );
		$this->assertStringContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $output );
		$this->assertStringContainsString( '<meta itemprop="@id" content="' . $question->ID . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringContainsString( '<meta itemprop="name" content="' . $question->post_title . '" /> <!-- This is for structured data, do not delete. -->', $output );
		$this->assertStringContainsString( '<div class="ap-question-meta clearfix">', $output );
		$this->assertStringContainsString( '<div ap="question" apid="' . $question->ID . '">', $output );
		$this->assertStringContainsString( '<div id="question" role="main" class="ap-content">', $output );
		$this->assertStringContainsString( '<div class="ap-single-vote">', $output );
		$this->assertStringContainsString( '<div class="ap-avatar">', $output );
		$this->assertStringContainsString( '<div class="ap-cell clearfix">', $output );
		$this->assertStringContainsString( '<div class="ap-q-metas">', $output );
		$this->assertStringContainsString( '<span class="ap-author" itemprop="author" itemscope itemtype="http://schema.org/Person">', $output );
		$this->assertStringContainsString( '<span class="ap-comments-count">', $output );
		$this->assertStringContainsString( '<div class="question-content ap-q-content" itemprop="text">', $output );
		$this->assertStringContainsString( '<div class="ap-post-footer clearfix">', $output );
		$this->assertStringContainsString( '<apcomments id="comments-' . esc_attr( $question->ID ) . '" class="have-comments">', $output );
		$this->assertStringContainsString( '<apanswersw style="display:none">', $output );
		$this->assertStringContainsString( '<div id="ap-answers-c">', $output );
		$this->assertStringContainsString( '<div class="ap-sorting-tab clearfix">', $output );
		$this->assertStringContainsString( '<div id="answers">', $output );
		$this->assertStringContainsString( '<div class="ap-login">', $output );
		$this->assertStringNotContainsString( '<div class="ap-pagination clearfix">', $output );
		$this->assertTrue( did_action( 'ap_after_question' ) > 0 );
		$this->assertTrue( $question_rendered );

		// Reset global variables.
		$question_rendered = false;
		$answers = '';
	}

	/**
	 * @covers AnsPress_Common_Pages::ask_page
	 */
	public function testAskPage() {
		$this->setRole( 'subscriber' );
		ob_start();
		\AnsPress_Common_Pages::ask_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div id="ap-ask-page" class="clearfix">', $output );
		$this->assertStringContainsString( 'name="form_question[post_title]"', $output );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $output );
	}

	/**
	 * @covers AnsPress_Common_Pages::ask_page
	 */
	public function testAskPageForPostIDAndValidNonce() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$_REQUEST['id'] = $question_id;
		$_REQUEST['__nonce'] = wp_create_nonce( 'edit-post-' . $question_id );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::ask_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div id="ap-ask-page" class="clearfix">', $output );
		$this->assertStringContainsString( 'name="form_question[post_title]"', $output );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $output );
		unset( $_REQUEST['id'] );
		unset( $_REQUEST['__nonce'] );
	}

	/**
	 * @covers AnsPress_Common_Pages::ask_page
	 */
	public function testAskPageForHookTriggerTest() {
		$this->setRole( 'subscriber' );

		// Action hook triggered.
		$callback_triggered = false;
		add_action( 'ap_after_ask_page', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::ask_page();
		ob_end_clean();
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_after_ask_page' ) > 0 );
	}

	/**
	 * @covers AnsPress_Common_Pages::ask_page
	 */
	public function testAskPageIfPostIDAndInvalidNonce() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$_REQUEST['id'] = $question_id;
		$_REQUEST['__nonce'] = wp_create_nonce( 'invalid_nonce' );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::ask_page();
		$output = ob_get_clean();
		$this->assertEquals( 'Something went wrong, please try again', $output );
		unset( $_REQUEST['id'] );
		unset( $_REQUEST['__nonce'] );
	}

	/**
	 * @covers AnsPress_Common_Pages::edit_page
	 */
	public function testEditPage() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$_REQUEST['id'] = $answer_id;
		$_REQUEST['__nonce'] = wp_create_nonce( 'edit-post-' . $answer_id );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::edit_page();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_answer[post_content]"', $output );
		$this->assertStringContainsString( 'form_answer[post_id]', $output );
		$this->assertStringContainsString( 'name="question_id" value="' . $question_id . '"', $output );
		$this->assertStringContainsString( 'name="post_id" value="' . $answer_id . '"', $output );
		$this->assertStringContainsString( 'name="form_answer[post_id]"', $output );
		unset( $_REQUEST['id'] );
		unset( $_REQUEST['__nonce'] );
	}

	/**
	 * @covers AnsPress_Common_Pages::edit_page
	 */
	public function testEditPageForNotPostID() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$_REQUEST['__nonce'] = wp_create_nonce( 'edit-post-' . $answer_id );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::edit_page();
		$output = ob_get_clean();
		$this->assertEquals( '<p>Sorry, you cannot edit this answer.</p>', $output );
		unset( $_REQUEST['__nonce'] );
	}

	/**
	 * @covers AnsPress_Common_Pages::edit_page
	 */
	public function testEditPageForInvalidNonce() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$_REQUEST['id'] = $answer_id;
		$_REQUEST['__nonce'] = wp_create_nonce( 'invalid_nonce' );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::edit_page();
		$output = ob_get_clean();
		$this->assertEquals( '<p>Sorry, you cannot edit this answer.</p>', $output );
		unset( $_REQUEST['id'] );
		unset( $_REQUEST['__nonce'] );
	}

	/**
	 * @covers AnsPress_Common_Pages::edit_page
	 */
	public function testEditPageForUserWhoDoNotHaveAccessToEditAnswer() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_author' => $user_id, 'post_status' => 'private_post' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$_REQUEST['id'] = $answer_id;
		$_REQUEST['__nonce'] = wp_create_nonce( 'edit-post-' . $answer_id );

		// Test.
		ob_start();
		\AnsPress_Common_Pages::edit_page();
		$output = ob_get_clean();
		$this->assertEquals( '<p>Sorry, you cannot edit this answer.</p>', $output );
		unset( $_REQUEST['id'] );
		unset( $_REQUEST['__nonce'] );
	}
}
