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
}
