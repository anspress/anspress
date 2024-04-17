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
	 * @covers AnsPress_Post_Table_Hooks::init
	 */
	public function testInit() {
		\AnsPress_Post_Table_Hooks::init();
		anspress()->setup_hooks();

		// Tests.
		$this->assertEquals( 10, has_filter( 'views_edit-question', [ 'AnsPress_Post_Table_Hooks', 'flag_view' ] ) );
		$this->assertEquals( 10, has_filter( 'views_edit-answer', [ 'AnsPress_Post_Table_Hooks', 'flag_view' ] ) );
		$this->assertEquals( 10, has_action( 'posts_clauses', [ 'AnsPress_Post_Table_Hooks', 'posts_clauses' ] ) );
		$this->assertEquals( 10, has_action( 'manage_answer_posts_custom_column', [ 'AnsPress_Post_Table_Hooks', 'answer_row_actions' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-question_columns', [ 'AnsPress_Post_Table_Hooks', 'cpt_question_columns' ] ) );
		$this->assertEquals( 10, has_action( 'manage_posts_custom_column', [ 'AnsPress_Post_Table_Hooks', 'custom_columns_value' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-answer_columns', [ 'AnsPress_Post_Table_Hooks', 'cpt_answer_columns' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-question_sortable_columns', [ 'AnsPress_Post_Table_Hooks', 'admin_column_sort_flag' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-answer_sortable_columns', [ 'AnsPress_Post_Table_Hooks', 'admin_column_sort_flag' ] ) );
		$this->assertEquals( 10, has_action( 'edit_form_after_title', [ 'AnsPress_Post_Table_Hooks', 'edit_form_after_title' ] ) );
		$this->assertEquals( 10, has_filter( 'manage_edit-comments_columns', [ 'AnsPress_Post_Table_Hooks', 'comment_flag_column' ] ) );
		$this->assertEquals( 10, has_filter( 'comment_status_links', [ 'AnsPress_Post_Table_Hooks', 'comment_flag_view' ] ) );
		$this->assertEquals( 10, has_action( 'current_screen', [ 'AnsPress_Post_Table_Hooks', 'comments_flag_query' ] ) );
		$this->assertEquals( 10, has_filter( 'post_updated_messages', [ 'AnsPress_Post_Table_Hooks', 'post_custom_message' ] ) );
		// $this->assertEquals( 10, has_filter( 'manage_comments_custom_column', [ 'AnsPress_Post_Table_Hooks', 'comment_flag_column_data' ] ) );
		// $this->assertEquals( 10, has_filter( 'post_row_actions', [ 'AnsPress_Post_Table_Hooks', 'add_question_flag_link' ] ) );
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

	/**
	 * @covers AnsPress_Post_Table_Hooks::add_question_flag_link
	 */
	public function testAddQuestionFlagLink() {
		$hooks = new \AnsPress_Post_Table_Hooks();
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$answer_id   = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$question    = get_post( $question_id );
		$answer      = get_post( $answer_id );

		// Test begins.
		// For question post type.
		// Test 1.
		$flag_link = $hooks::add_question_flag_link( [], $question );
		$this->assertEmpty( $flag_link );

		// Test 2.
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$flag_link = $hooks::add_question_flag_link( [], $question );
		$this->assertNotEmpty( $flag_link );
		$expected = [
			'flag' => '<a href="#" data-query="ap_clear_flag::' . wp_create_nonce( 'clear_flag_' . $question_id ) . '::' . $question_id . '" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear">Clear flag</a>',
		];
		$this->assertEquals( $expected, $flag_link );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $flag_link );
			$this->assertEquals( $value, $flag_link[ $key ] );
		}

		// For answer post type.
		// Test 1.
		$flag_link = $hooks::add_question_flag_link( [], $answer );
		$this->assertEmpty( $flag_link );

		// Test 2.
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );
		$flag_link = $hooks::add_question_flag_link( [], $answer );
		$this->assertNotEmpty( $flag_link );
		$expected = [
			'flag' => '<a href="#" data-query="ap_clear_flag::' . wp_create_nonce( 'clear_flag_' . $answer_id ) . '::' . $answer_id . '" class="ap-ajax-btn flag-clear" data-cb="afterFlagClear">Clear flag</a>',
		];
		$this->assertEquals( $expected, $flag_link );
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $flag_link );
			$this->assertEquals( $value, $flag_link[ $key ] );
		}
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::posts_clauses
	 */
	public function testPostsClauses() {
		global $pagenow, $wpdb;
		$sql = [
			'join'    => '',
			'fields'  => '',
			'where'   => '',
			'orderby' => '',
		];
		$instance = new \stdClass();
		$hooks = new \AnsPress_Post_Table_Hooks();

		// Test begins.
		// Test 1.
		$instance->query_vars = [ 'post_type' => 'post' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertEmpty( $clauses['join'] );
		$this->assertEmpty( $clauses['fields'] );

		// Test 2.
		$instance->query_vars = [ 'post_type' => 'question' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertEmpty( $clauses['where'] );
		$this->assertEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );

		// Test 3.
		$pagenow = 'edit.php';
		$instance->query_vars = [ 'post_type' => 'answer' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertEmpty( $clauses['where'] );
		$this->assertEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );

		// Test 4.
		$pagenow = 'edit.php';
		$_REQUEST['flagged'] = 1;
		$instance->query_vars = [ 'post_type' => 'answer' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertNotEmpty( $clauses['where'] );
		$this->assertNotEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );
		$this->assertEquals( " AND qameta.flags > 0", $clauses['where'] );
		$this->assertEquals( " qameta.flags DESC, ", $clauses['orderby'] );
		unset( $_REQUEST['flagged'] );
		$pagenow = '';

		// Test 5.
		$_REQUEST['orderby'] = 'flags';
		$_REQUEST['order']   = 'desc';
		$instance->query_vars = [ 'post_type' => 'question' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertEmpty( $clauses['where'] );
		$this->assertNotEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );
		$this->assertEquals( " qameta.flags desc", $clauses['orderby'] );
		unset( $_REQUEST['orderby'] );
		unset( $_REQUEST['order'] );

		// Test 6.
		$_REQUEST['orderby'] = 'votes';
		$_REQUEST['order']   = 'asc';
		$instance->query_vars = [ 'post_type' => 'question' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertEmpty( $clauses['where'] );
		$this->assertNotEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );
		$this->assertEquals( " votes_net asc", $clauses['orderby'] );
		unset( $_REQUEST['orderby'] );
		unset( $_REQUEST['order'] );

		// Test 7.
		$_REQUEST['orderby'] = 'answers';
		$_REQUEST['order']   = 'desc';
		$instance->query_vars = [ 'post_type' => 'question' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertEmpty( $clauses['where'] );
		$this->assertNotEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );
		$this->assertEquals( " qameta.answers desc", $clauses['orderby'] );
		unset( $_REQUEST['orderby'] );
		unset( $_REQUEST['order'] );

		// Test 8.
		$pagenow = 'edit.php';
		$_REQUEST['orderby'] = 'flags';
		$_REQUEST['flagged'] = 1;
		$instance->query_vars = [ 'post_type' => 'answer' ];
		$clauses = $hooks::posts_clauses( $sql, $instance );
		$this->assertNotEmpty( $clauses['join'] );
		$this->assertNotEmpty( $clauses['fields'] );
		$this->assertNotEmpty( $clauses['where'] );
		$this->assertNotEmpty( $clauses['orderby'] );
		$this->assertEquals( " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID", $clauses['join'] );
		$this->assertEquals( ", qameta.*, qameta.votes_up - qameta.votes_down AS votes_net", $clauses['fields'] );
		$this->assertEquals( " AND qameta.flags > 0", $clauses['where'] );
		$this->assertEquals( " qameta.flags desc", $clauses['orderby'] );
		$this->assertNotEquals( " qameta.flags DESC, ", $clauses['orderby'] );
		unset( $_REQUEST['orderby'] );
		unset( $_REQUEST['flagged'] );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::edit_form_after_title
	 */
	public function testEditFormAfterTitleForQuestionPostTypeEditPage() {
		global $pagenow, $post;
		$pagenow = 'post.php';
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$post = ap_get_post( $question_id );

		// Test begins.
		$hooks = new \AnsPress_Post_Table_Hooks();
		ob_start();
		@$hooks::edit_form_after_title();
		$result = ob_get_clean();
		$this->assertEmpty( $result );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::edit_form_after_title
	 */
	public function testEditFormAfterTitleForAnswerPostTypeEditPageWhichHasParentPostPassingAction() {
		global $pagenow, $post;
		$pagenow = 'post.php';
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title', 'post_content' => 'Question Content' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post = ap_get_post( $answer_id );

		// Test begins.
		$_REQUEST['action'] = 'edit';
		$hooks = new \AnsPress_Post_Table_Hooks();
		ob_start();
		@$hooks::edit_form_after_title();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<div class="ap-selected-question">', $result );
		$this->assertStringContainsString( '<a class="ap-q-title" href="' . esc_url( get_permalink( $question_id ) ) . '">', $result );
		$this->assertStringContainsString( 'Question Title', $result );
		$this->assertStringContainsString( '<div class="ap-q-meta">', $result );
		$this->assertStringContainsString( '<span class="ap-a-count">', $result );
		$this->assertStringContainsString( '1 Answer', $result );
		$this->assertStringContainsString( '<span class="ap-edit-link">', $result );
		@$this->assertStringContainsString( '<a href="' . esc_url( get_edit_post_link( $question_id ) ) . '">', $result );
		$this->assertStringContainsString( 'Edit question', $result );
		$this->assertStringContainsString( '<div class="ap-q-content">Question Content</div>', $result );
		$this->assertStringContainsString( '<input type="hidden" name="post_parent" value="' . $question_id . '" />', $result );
		unset( $_REQUEST['action'] );
	}

	/**
	 * @covers AnsPress_Post_Table_Hooks::edit_form_after_title
	 */
	public function testEditFormAfterTitleForAnswerPostTypeEditPageWhichHasParentPostPassingPostParent() {
		global $pagenow, $post;
		$pagenow = 'post.php';
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title', 'post_content' => 'Question Content' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$new_answer = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$post = ap_get_post( $answer_id );

		// Test begins.
		$_REQUEST['post_parent'] = $question_id;
		$hooks = new \AnsPress_Post_Table_Hooks();
		ob_start();
		@$hooks::edit_form_after_title();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<div class="ap-selected-question">', $result );
		$this->assertStringContainsString( '<a class="ap-q-title" href="' . esc_url( get_permalink( $question_id ) ) . '">', $result );
		$this->assertStringContainsString( 'Question Title', $result );
		$this->assertStringContainsString( '<div class="ap-q-meta">', $result );
		$this->assertStringContainsString( '<span class="ap-a-count">', $result );
		$this->assertStringContainsString( '2 Answers', $result );
		$this->assertStringContainsString( '<span class="ap-edit-link">', $result );
		@$this->assertStringContainsString( '<a href="' . esc_url( get_edit_post_link( $question_id ) ) . '">', $result );
		$this->assertStringContainsString( 'Edit question', $result );
		$this->assertStringContainsString( '<div class="ap-q-content">Question Content</div>', $result );
		$this->assertStringContainsString( '<input type="hidden" name="post_parent" value="' . $question_id . '" />', $result );
		unset( $_REQUEST['post_parent'] );
	}
}
