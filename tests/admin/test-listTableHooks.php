<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

// Since this file is required only on admin pages so,
// we include it directly for testing.
require_once ANSPRESS_DIR . 'admin/class-list-table-hooks.php';

class TestListTableHooks extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'flag_view' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'posts_clauses' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'answer_row_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'add_question_flag_link' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'cpt_question_columns' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'custom_columns_value' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'cpt_answer_columns' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'admin_column_sort_flag' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'edit_form_after_title' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'comment_flag_column' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'comment_flag_column_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'comment_flag_view' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'comments_flag_query' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'post_custom_message' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Table_Hooks', 'ans_notice' ) );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::ans_notice
	 */
	public function testAnsNotice() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		ob_start();
		$hooks->ans_notice();
		$result = ob_get_clean();
		$this->assertStringContainsString( '<div class="error">', $result );
		$this->assertStringContainsString( '<p>Please fill parent question field, Answer was not saved!</p>', $result );
		$this->assertStringContainsString( '</div>', $result );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::post_custom_message
	 */
	public function testPostCustomMessage() {
		$hooks = new \AnsPress_Post_Table_Hooks();

		// Test for question post type.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		$message = 'This is custom message for question post type.';
		$result = $hooks->post_custom_message( $message );
		$this->assertEquals( $message, $result );
		$this->assertFalse( has_action( 'admin_notices', array( 'AnsPress_Post_Table_Hooks', 'ans_notice' ) ) );

		// Test for answer post type.
		// Test 1.
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer' ] );
		$this->go_to( '?post_type=answer&p=' . $answer_id );
		$message = 'This is custom message for answer post type.';
		$result = $hooks->post_custom_message( $message );
		$this->assertEquals( $message, $result );
		$this->assertFalse( has_action( 'admin_notices', array( 'AnsPress_Post_Table_Hooks', 'ans_notice' ) ) );

		// Test 2.
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer' ] );
		$this->go_to( '?post_type=answer&p=' . $answer_id );
		$_REQUEST['message'] = 10;
		$message = 'This is custom message for answer post type.';
		$result = $hooks->post_custom_message( $message );
		$this->assertEquals( $message, $result );
		$this->assertFalse( has_action( 'admin_notices', array( 'AnsPress_Post_Table_Hooks', 'ans_notice' ) ) );

		// Test 3.
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer' ] );
		$this->go_to( '?post_type=answer&p=' . $answer_id );
		$_REQUEST['message'] = 99;
		$message = 'This is custom message for answer post type.';
		$result = $hooks->post_custom_message( $message );
		$this->assertEquals( $message, $result );
		$this->assertEquals( 10, has_action( 'admin_notices', array( 'AnsPress_Post_Table_Hooks', 'ans_notice' ) ) );
	}
}
