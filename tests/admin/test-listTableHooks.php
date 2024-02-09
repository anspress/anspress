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

	/**
	 * @covers AnsPress_Post_Table_Hooks::cpt_question_columns
	 */
	public function testCPTQuestionColumns() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		$columns = $hooks->cpt_question_columns( [] );

		// Expected columns.
		$expected = [
			'cb'                => '<input type="checkbox" />',
			'ap_author'         => 'Author',
			'title'             => 'Title',
			'question_category' => 'Category',
			'question_tag'      => 'Tag',
			'status'            => 'Status',
			'answers'           => 'Ans',
			'comments'          => 'Comments',
			'votes'             => 'Votes',
			'flags'             => 'Flags',
			'date'              => 'Date',
		];
		$this->assertEquals( $expected, $columns );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $columns );
			$this->assertEquals( $value, $columns[ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::cpt_answer_columns
	 */
	public function testCPTAnswerColumns() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		$columns = $hooks->cpt_answer_columns( [] );

		// Expected columns.
		$expected = [
			'cb'             => '<input type="checkbox" />',
			'ap_author'      => 'Author',
			'answer_content' => 'Content',
			'status'         => 'Status',
			'comments'       => 'Comments',
			'votes'          => 'Votes',
			'flags'          => 'Flags',
			'date'           => 'Date',
		];
		$this->assertEquals( $expected, $columns );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $columns );
			$this->assertEquals( $value, $columns[ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::admin_column_sort_flag
	 */
	public function testAdminColumnSortFlag() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		$columns = $hooks->admin_column_sort_flag( [] );

		// Expected columns.
		$expected = [
			'flags'   => 'flags',
			'answers' => 'answers',
			'votes'   => 'votes',
		];
		$this->assertEquals( $expected, $columns );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $columns );
			$this->assertEquals( $value, $columns[ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::comment_flag_column
	 */
	public function testCommentFlagColumn() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		$columns = $hooks->comment_flag_column( [] );

		// Expected columns.
		$expected = [
			'comment_flag' => 'Flag',
		];
		$this->assertEquals( $expected, $columns );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $columns );
			$this->assertEquals( $value, $columns[ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::comment_flag_view
	 */
	public function testCommentFlagView() {
		$hooks = new \AnsPress_Post_Table_Hooks();

		// Test begins.
		// Test 1.
		$views = $hooks->comment_flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit-comments.php?show_flagged=true">Flagged</a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}

		// Test 2.
		$_REQUEST['show_flagged'] = 1;
		$views = $hooks->comment_flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit-comments.php?show_flagged=true" class="current">Flagged</a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}

		// Reset $_REQUEST.
		unset( $_REQUEST['show_flagged'] );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::comments_flag_query
	 */
	public function testCommentsFlagQuery() {
		$hooks = new \AnsPress_Post_Table_Hooks();

		// Test begins.
		// Test 1.
		$screen = set_current_screen( 'dashboard' );
		$screen = $hooks->comments_flag_query( get_current_screen() );
		$this->assertNull( $screen );
		$this->assertFalse( has_action( 'comments_clauses', [ 'AnsPress_Admin', 'filter_comments_query' ] ) );

		// Test 2.
		$screen = set_current_screen( 'edit-comments' );
		$screen = $hooks->comments_flag_query( get_current_screen() );
		$this->assertNull( $screen );
		$this->assertFalse( has_action( 'comments_clauses', [ 'AnsPress_Admin', 'filter_comments_query' ] ) );

		// Test 2.
		$screen = set_current_screen( 'edit-comments' );
		$_REQUEST['show_flagged'] = 1;
		$screen = $hooks->comments_flag_query( get_current_screen() );
		$this->assertNull( $screen );
		$this->assertEquals( 10, has_action( 'comments_clauses', [ 'AnsPress_Admin', 'filter_comments_query' ] ) );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::flag_view
	 */
	public function testFlagView() {
		global $post_type_object;
		$post_type_object = new \stdClass();
		$hooks = new \AnsPress_Post_Table_Hooks();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );

		// Test begins.
		// Test for question post type.
		// Test 1.
		$post_type_object->name = 'question';
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit.php?flagged=true&#038;post_type=question">Flagged <span class="count">(0)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}

		// Test 2.
		$_REQUEST['flagged'] = 1;
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a class="current" href="edit.php?flagged=true&#038;post_type=question">Flagged <span class="count">(0)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}
		unset( $_REQUEST['flagged'] );

		// Test 3.
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit.php?flagged=true&#038;post_type=question">Flagged <span class="count">(1)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}

		// Test for answer post type.
		// Test 1.
		$post_type_object->name = 'answer';
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit.php?flagged=true&#038;post_type=answer">Flagged <span class="count">(0)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}

		// Test 2.
		$_REQUEST['flagged'] = 1;
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a class="current" href="edit.php?flagged=true&#038;post_type=answer">Flagged <span class="count">(0)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}
		unset( $_REQUEST['flagged'] );

		// Test 3.
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );
		$views = $hooks::flag_view( [] );
		$expected = [
			'flagged' => '<a href="edit.php?flagged=true&#038;post_type=answer">Flagged <span class="count">(1)</span></a>',
		];
		$this->assertEquals( $expected, $views );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $views );
			$this->assertEquals( $value, $views[ $key ] );
		}
	}
}
