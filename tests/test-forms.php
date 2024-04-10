<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestForms extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_ask_form
	 */
	public function testAskFormForUserWhoCantEditQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$_REQUEST['id'] = $question_id;

		// Test.
		ob_start();
		ap_ask_form();
		$output = ob_get_clean();
		$this->assertEquals( '<p>You cannot edit this question.</p>', $output );

		// Reset.
		unset( $_REQUEST['id'] );
	}

	/**
	 * @covers ::ap_ask_form
	 */
	public function testAskFormForUserWhoCantPostQuestion() {
		ap_opt( 'post_question_per', 'have_cap' );
		$this->setRole( 'ap_banned' );

		// Test.
		ob_start();
		ap_ask_form();
		$output = ob_get_clean();
		$this->assertEquals( '<p>You do not have permission to ask a question.</p>', $output );

		// Reset.
		ap_opt( 'post_question_per', 'anyone' );
	}

	/**
	 * @covers ::ap_ask_form
	 */
	public function testAskFormForUserWhoCanEditQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$_REQUEST['id'] = $question_id;

		// Test.
		ob_start();
		ap_ask_form();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_question[post_title]"', $output );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $output );

		// Reset.
		unset( $_REQUEST['id'] );
	}

	/**
	 * @covers ::ap_ask_form
	 */
	public function testAskFormForUserWhoCanPostQuestion() {
		$this->setRole( 'subscriber' );

		// Test.
		ob_start();
		ap_ask_form();
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_question[post_title]"', $output );
		$this->assertStringContainsString( 'name="form_question[post_content]"', $output );
	}

	/**
	 * @covers ::ap_answer_form
	 */
	public function testAnswerFormForUserWhoCantEditAnswer() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$_REQUEST['id'] = $id->a;

		// Test.
		ob_start();
		ap_answer_form( $id->q, true );
		$output = ob_get_clean();
		$this->assertEquals( '<p>You cannot edit this answer.</p>', $output );

		// Reset.
		unset( $_REQUEST['id'] );
	}

	/**
	 * @covers ::ap_answer_form
	 */
	public function testAnswerFormForUserWhoCantPostAnswer() {
		ap_opt( 'post_answer_per', 'have_cap' );
		$id = $this->insert_answer();
		$this->setRole( 'ap_banned' );

		// Test.
		ob_start();
		ap_answer_form( $id->q );
		$output = ob_get_clean();
		$this->assertEquals( '<p>You do not have permission to answer this question.</p>', $output );

		// Reset.
		ap_opt( 'post_answer_per', 'anyone' );
	}

	/**
	 * @covers ::ap_answer_form
	 */
	public function testAnswerFormForUserWhoCanEditAnswer() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer( '', '', get_current_user_id() );
		$_REQUEST['id'] = $id->a;

		// Test.
		ob_start();
		ap_answer_form( $id->q, true );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_answer[post_content]"', $output );
		$this->assertStringContainsString( 'name="post_id" value="' . $id->a . '"', $output );

		// Reset.
		unset( $_REQUEST['id'] );
	}

	/**
	 * @covers ::ap_answer_form
	 */
	public function testAnswerFormForUserWhoCanPostAnswer() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer( '', '', get_current_user_id() );

		// Test.
		ob_start();
		ap_answer_form( $id->q );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_answer[post_content]"', $output );
		$this->assertStringNotContainsString( 'name="post_id" value="' . $id->a . '"', $output );
	}

	/**
	 * @covers ::ap_comment_form
	 */
	public function testCommentFormForUserWhoCantPostComment() {
		ap_opt( 'post_comment_per', 'have_cap' );
		$id = $this->insert_answer();
		$this->setRole( 'ap_banned' );
		$this->go_to( '?post_type=question&p=' . $id->q );

		// Test.
		ob_start();
		ap_comment_form();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Reset.
		ap_opt( 'post_comment_per', 'anyone' );
	}

	/**
	 * @covers ::ap_comment_form
	 */
	public function testCommentFormForPassingTheComment() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$comment_id = $this->factory()->comment->create( array(
			'comment_post_ID' => $id->q,
			'comment_type'    => 'anspress',
			'user_id'         => get_current_user_id(),
		) );

		// Test.
		ob_start();
		ap_comment_form( $id->q, $comment_id );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_comment[content]"', $output );
	}

	/**
	 * @covers ::ap_comment_form
	 */
	public function testCommentFormForPassingTheCommentWithEmptyUserID() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$comment_id = $this->factory()->comment->create( array(
			'comment_post_ID' => $id->q,
			'comment_type'    => 'anspress',
			'user_id'         => '',
		) );

		// Test.
		ob_start();
		ap_comment_form( $id->q, $comment_id );
		$output = ob_get_clean();
		$this->assertStringContainsString( 'name="form_comment[content]"', $output );
	}
}
