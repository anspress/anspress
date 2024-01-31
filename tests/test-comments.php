<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestComments extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'the_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'load_comments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comments_template_query_args' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'approve_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comment_link' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'preprocess_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Comment_Hooks', 'comments_template' ) );
	}

	/**
	 * @covers AnsPress_Comment_Hooks::ap_new_comment_btn
	 */
	public function testAPNewCommentBtn() {
		$question_id = $this->insert_question();

		// Test begins.
		// Test 1.
		// For return value.
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_new_comment_btn( $question_id, false );
		$this->assertNull( $result );

		// For echoed value.
		ob_start();
		ap_new_comment_btn( $question_id );
		$result = ob_get_clean();
		$this->assertEmpty( $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $question_id,
				'__nonce' => wp_create_nonce( 'new_comment_' . $question_id ),
			)
		);

		// For return value.
		$this->go_to( '?post_type=question&p=' . $question_id );
		$result = ap_new_comment_btn( $question_id, false );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );

		// For echoed value.
		ob_start();
		ap_new_comment_btn( $question_id );
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );
	}
}
