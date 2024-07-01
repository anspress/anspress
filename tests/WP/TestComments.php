<?php

namespace AnsPress\Tests\WP;

use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestComments extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_new_comment_btn
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

	/**
	 * @covers ::ap_comment_delete_locked
	 */
	public function testAPCommentDeleteLocked() {
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id ] );

		// Test 1.
		$result = ap_comment_delete_locked( $comment_id );
		$this->assertFalse( $result );

		// Test 2.
		$comment_date = date( 'Y-m-d H:i:s', strtotime( '-5 days' ) );
		wp_update_comment(
			[
				'comment_ID'       => $comment_id,
				'comment_date'     => $comment_date,
				'comment_date_gmt' => get_gmt_from_date( $comment_date ),
			]
		);
		ap_opt( 'disable_delete_after', 604800 );
		$result = ap_comment_delete_locked( $comment_id );
		$this->assertFalse( $result );

		// Test 3.
		$comment_date = date( 'Y-m-d H:i:s', strtotime( '-8 days' ) );
		wp_update_comment(
			[
				'comment_ID'       => $comment_id,
				'comment_date'     => $comment_date,
				'comment_date_gmt' => get_gmt_from_date( $comment_date ),
			]
		);
		$result = ap_comment_delete_locked( $comment_id );
		$this->assertTrue( $result );

		// Reset disable_delete_after option.
		ap_opt( 'disable_delete_after', 86400 );
	}

	/**
	 * @covers ::ap_comment_btn_html
	 */
	public function testAPCommentBtnHTMLShouldReturnNullForUsersWhoCantReadComment() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_status' => 'moderate', 'post_parent' => $question_id ] );

		// Tests.
		$this->setRole( 'subscriber' );
		$this->assertNull( ap_comment_btn_html( $question_id ) );
		$this->assertNull( ap_comment_btn_html( $answer_id ) );
	}
}
