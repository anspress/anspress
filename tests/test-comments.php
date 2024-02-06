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

	/**
	 * @covers ::ap_comment_actions
	 */
	public function testAPCommentActions() {
		$id = $this->insert_question();
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress' ) );

		// Test begins.
		// Test 1.
		$result = ap_comment_actions( $comment );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 2.
		$this->setRole( 'subscriber' );
		$result = ap_comment_actions( $comment );
		$this->assertEmpty( $result );
		$this->assertIsArray( $result );

		// Test 3.
		$this->setRole( 'administrator' );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		foreach ( $result as $key => $value ) {
			$this->assertEquals( $value, $result[ $key ] );
		}

		// Test 4.
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress', 'comment_approved' => 0 ) );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
			[
				'label' => 'Approve',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'approve_comment',
					'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		foreach ( $result as $key => $value ) {
			$this->assertEquals( $value, $result[ $key ] );
		}

		// Test 5.
		$this->setRole( 'ap_moderator' );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
			[
				'label' => 'Approve',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'approve_comment',
					'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		foreach ( $result as $key => $value ) {
			$this->assertEquals( $value, $result[ $key ] );
		}

		// Test 6.
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress' ) );
		add_role( 'user_can_edit_comment', 'Test Role', array( 'ap_edit_others_comment' => true ) );
		$this->setRole( 'user_can_edit_comment' );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		$not_expected = [
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
			[
				'label' => 'Approve',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'approve_comment',
					'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertNotContains( $not_expected, $result );

		// Test 7.
		add_role( 'user_can_delete_comment', 'Test Role', array( 'ap_delete_others_comment' => true ) );
		$this->setRole( 'user_can_delete_comment' );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		$not_expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
			[
				'label' => 'Approve',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'approve_comment',
					'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertNotContains( $not_expected, $result );

		// Test 8.
		$comment = $this->factory->comment->create_and_get( array( 'comment_post_ID' => $id, 'comment_type' => 'anspress', 'comment_approved' => 0 ) );
		add_role( 'user_can_approve_comment', 'Test Role', array( 'ap_approve_comment' => true ) );
		$this->setRole( 'user_can_approve_comment' );
		$result = ap_comment_actions( $comment );
		$this->assertNotEmpty( $result );
		$this->assertIsArray( $result );
		$expected = [
			[
				'label' => 'Approve',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'approve_comment',
					'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertEquals( $expected, $result );
		$not_expected = [
			[
				'label' => 'Edit',
				'href'  => '#',
				'query' => [
					'action'     => 'comment_modal',
					'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
					'comment_id' => $comment->comment_ID,
				],
			],
			[
				'label' => 'Delete',
				'href'  => '#',
				'query' => [
					'ap_ajax_action' => 'delete_comment',
					'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
					'comment_id'     => $comment->comment_ID,
				],
			],
		];
		$this->assertNotContains( $not_expected, $result );
	}

	/**
	 * @covers ::ap_comment_btn_html
	 */
	public function testAPCommentBtnHTML() {
		$id = $this->insert_answer();

		// Test begins.
		// Test 1.
		// For question post type.
		$result = ap_comment_btn_html( $id->q );
		$this->assertNull( $result );

		// For answer post type.
		$result = ap_comment_btn_html( $id->a );
		$this->assertNull( $result );

		// Test 2.
		$this->setRole( 'subscriber' );

		// For question post type.
		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $id->q,
				'__nonce' => wp_create_nonce( 'new_comment_' . $id->q ),
			)
		);
		$result = ap_comment_btn_html( $id->q );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );

		// For answer post type.
		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $id->a,
				'__nonce' => wp_create_nonce( 'new_comment_' . $id->a ),
			)
		);
		$result = ap_comment_btn_html( $id->a );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );

		// Test 3.
		ap_opt( 'disable_comments_on_question', true );

		// For question post type.
		$result = ap_comment_btn_html( $id->q );
		$this->assertNull( $result );

		// For answer post type.
		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $id->a,
				'__nonce' => wp_create_nonce( 'new_comment_' . $id->a ),
			)
		);
		$result = ap_comment_btn_html( $id->a );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );

		// Test 4.
		ap_opt( 'disable_comments_on_answer', true );
		ap_opt( 'disable_comments_on_question', false );

		// For question post type.
		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $id->q,
				'__nonce' => wp_create_nonce( 'new_comment_' . $id->q ),
			)
		);
		$result = ap_comment_btn_html( $id->q );
		$this->assertStringContainsString( esc_js( $btn_args ), $result );
		$this->assertStringContainsString( '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">', $result );
		$this->assertStringContainsString( 'Add a Comment', $result );

		// For answer post type.
		$result = ap_comment_btn_html( $id->a );
		$this->assertNull( $result );
		ap_opt( 'disable_comments_on_answer', false );
	}

	/**
	 * @covers AnsPress_Comment_Hooks::comments_template_query_args
	 */
	public function testCommentsTemplateQueryArgs() {
		// Test 1.
		global $question_rendered;
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 2.
		$question_rendered = true;
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 3.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertFalse( $result );

		// Test 4.
		$question_rendered = false;
		$this->go_to( '?post_type=question&p=' . $id );
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
		$this->go_to( '/' );

		// Test 5.
		$this->setRole( 'subscriber' );
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );

		// Test 6.
		$this->setRole( 'administrator' );
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'status', $result );
		$this->assertEquals( 'all', $result['status'] );

		// Test 7.
		$question_rendered = true;
		$this->go_to( '?post_type=question&p=' . $id );
		$result = \AnsPress_Comment_Hooks::comments_template_query_args( [] );
		$this->assertFalse( $result );
		$question_rendered = false;
		$this->logout();
	}
}
