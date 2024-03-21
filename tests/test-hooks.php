<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestHooks extends TestCase {

	use Testcases\Common;

	/**
	 * @covers AnsPress_Hooks::init
	 */
	public function testInit() {
		\AnsPress_Hooks::init();

		// Action hooks.
		$this->assertEquals( 10, has_action( 'wp_loaded', [ 'AnsPress_Hooks', 'flush_rules' ] ) );
		$this->assertEquals( 10, has_action( 'registered_taxonomy', [ 'AnsPress_Hooks', 'add_ap_tables' ] ) );
		$this->assertEquals( 1, has_action( 'ap_processed_new_question', [ 'AnsPress_Hooks', 'after_new_question' ] ) );
		$this->assertEquals( 1, has_action( 'ap_processed_new_answer', [ 'AnsPress_Hooks', 'after_new_answer' ] ) );
		$this->assertEquals( 10, has_action( 'before_delete_post', [ 'AnsPress_Hooks', 'before_delete' ] ) );
		$this->assertEquals( 10, has_action( 'wp_trash_post', [ 'AnsPress_Hooks', 'trash_post_action' ] ) );
		$this->assertEquals( 10, has_action( 'untrash_post', [ 'AnsPress_Hooks', 'untrash_posts' ] ) );
		$this->assertEquals( 10, has_action( 'comment_post', [ 'AnsPress_Hooks', 'new_comment_approve' ] ) );
		$this->assertEquals( 10, has_action( 'comment_unapproved_to_approved', [ 'AnsPress_Hooks', 'comment_approve' ] ) );
		$this->assertEquals( 10, has_action( 'comment_approved_to_unapproved', [ 'AnsPress_Hooks', 'comment_unapprove' ] ) );
		$this->assertEquals( 10, has_action( 'trashed_comment', [ 'AnsPress_Hooks', 'comment_trash' ] ) );
		$this->assertEquals( 10, has_action( 'delete_comment', [ 'AnsPress_Hooks', 'comment_trash' ] ) );
		$this->assertEquals( 10, has_action( 'edit_comment', [ 'AnsPress_Hooks', 'edit_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ 'AnsPress_Hooks', 'publish_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unpublish_comment', [ 'AnsPress_Hooks', 'unpublish_comment' ] ) );
		$this->assertEquals( 10, has_action( 'wp_loaded', [ 'AnsPress_Hooks', 'flush_rules' ] ) );
		$this->assertEquals( 11, has_action( 'safe_style_css', [ 'AnsPress_Hooks', 'safe_style_css' ] ) );
		$this->assertEquals( 10, has_action( 'save_post', [ 'AnsPress_Hooks', 'base_page_update' ] ) );
		$this->assertEquals( 1, has_action( 'save_post_question', [ 'AnsPress_Hooks', 'save_question_hooks' ] ) );
		$this->assertEquals( 1, has_action( 'save_post_answer', [ 'AnsPress_Hooks', 'save_answer_hooks' ] ) );
		$this->assertEquals( 10, has_action( 'transition_post_status', [ 'AnsPress_Hooks', 'transition_post_status' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_casted', [ 'AnsPress_Hooks', 'update_user_vote_casted_count' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_removed', [ 'AnsPress_Hooks', 'update_user_vote_casted_count' ] ) );
		$this->assertEquals( 100, has_action( 'ap_display_question_metas', [ 'AnsPress_Hooks', 'display_question_metas' ] ) );
		$this->assertEquals( 10, has_action( 'widget_comments_args', [ 'AnsPress_Hooks', 'widget_comments_args' ] ) );
		$this->assertEquals( 10, has_action( 'show_admin_bar', [ 'AnsPress_Hooks', 'show_admin_bar' ] ) );

		// Filter hooks.
		$this->assertEquals( 1, has_filter( 'posts_clauses', [ 'AP_QA_Query_Hooks', 'sql_filter' ] ) );
		$this->assertEquals( 1, has_filter( 'posts_results', [ 'AP_QA_Query_Hooks', 'posts_results' ] ) );
		$this->assertEquals( 999999, has_filter( 'posts_pre_query', [ 'AP_QA_Query_Hooks', 'modify_main_posts' ] ) );
		$this->assertEquals( 10, has_filter( 'pre_get_posts', [ 'AP_QA_Query_Hooks', 'pre_get_posts' ] ) );

		// Theme hooks.
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Theme', 'init_actions' ] ) );
		$this->assertEquals( 10, has_filter( 'template_include', [ 'AnsPress_Theme', 'template_include' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_template_include', [ 'AnsPress_Theme', 'template_include_theme_compat' ] ) );
		$this->assertEquals( 10, has_filter( 'post_class', [ 'AnsPress_Theme', 'question_answer_post_class' ] ) );
		$this->assertEquals( 10, has_filter( 'body_class', [ 'AnsPress_Theme', 'body_class' ] ) );
		$this->assertEquals( 10, has_action( 'after_setup_theme', [ 'AnsPress_Theme', 'includes_theme' ] ) );
		$this->assertEquals( 0, has_filter( 'wp_title', [ 'AnsPress_Theme', 'ap_title' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before', [ 'AnsPress_Theme', 'ap_before_html_body' ] ) );
		$this->assertEquals( 11, has_action( 'wp_head', [ 'AnsPress_Theme', 'wp_head' ] ) );
		$this->assertEquals( 11, has_action( 'ap_after_question_content', [ 'AnsPress_Theme', 'question_attachments' ] ) );
		$this->assertEquals( 11, has_action( 'ap_after_answer_content', [ 'AnsPress_Theme', 'question_attachments' ] ) );
		$this->assertEquals( 10, has_filter( 'nav_menu_css_class', [ 'AnsPress_Hooks', 'fix_nav_current_class' ] ) );
		$this->assertEquals( 1000, has_filter( 'wp_insert_post_data', [ 'AnsPress_Hooks', 'wp_insert_post_data' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_form_contents_filter', [ 'AnsPress_Hooks', 'sanitize_description' ] ) );

		$this->assertEquals( 9999, has_filter( 'template_include', [ 'AnsPress_Theme', 'anspress_basepage_template' ] ) );
		$this->assertEquals( 9999, has_filter( 'get_the_excerpt', [ 'AnsPress_Theme', 'get_the_excerpt' ] ) );
		$this->assertEquals( 10, has_filter( 'post_class', [ 'AnsPress_Theme', 'remove_hentry_class' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_question_content', [ 'AnsPress_Theme', 'after_question_content' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_after_answer_content', [ 'AnsPress_Theme', 'after_question_content' ] ) );

		$this->assertEquals( 10, has_filter( 'the_comments', [ 'AnsPress_Comment_Hooks', 'the_comments' ] ) );
		$this->assertEquals( 10, has_filter( 'get_comment_link', [ 'AnsPress_Comment_Hooks', 'comment_link' ] ) );
		$this->assertEquals( 10, has_filter( 'preprocess_comment', [ 'AnsPress_Comment_Hooks', 'preprocess_comment' ] ) );
		$this->assertEquals( 10, has_filter( 'comments_template', [ 'AnsPress_Comment_Hooks', 'comments_template' ] ) );

		// Common pages hooks.
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Common_Pages', 'register_common_pages' ] ) );

		// Register post status.
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Post_Status', 'register_post_status' ] ) );

		// Rewrite rules hooks.
		$this->assertEquals( 10, has_filter( 'request', [ 'AnsPress_Rewrite', 'alter_the_query' ] ) );
		$this->assertEquals( 10, has_filter( 'query_vars', [ 'AnsPress_Rewrite', 'query_var' ] ) );
		$this->assertEquals( 1, has_action( 'generate_rewrite_rules', [ 'AnsPress_Rewrite', 'rewrites' ] ) );
		$this->assertEquals( 10, has_filter( 'paginate_links', [ 'AnsPress_Rewrite', 'pagination_fix' ] ) );
		$this->assertEquals( 10, has_filter( 'parse_request', [ 'AnsPress_Rewrite', 'add_query_var' ] ) );
		$this->assertEquals( 10, has_action( 'template_redirect', [ 'AnsPress_Rewrite', 'shortlink' ] ) );

		// Upload hooks.
		$this->assertEquals( 10, has_action( 'deleted_post', [ 'AnsPress_Uploader', 'deleted_attachment' ] ) );
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Uploader', 'create_single_schedule' ] ) );
		$this->assertEquals( 10, has_action( 'ap_delete_temp_attachments', [ 'AnsPress_Uploader', 'cron_delete_temp_attachments' ] ) );
		$this->assertEquals( 10, has_action( 'intermediate_image_sizes_advanced', [ 'AnsPress_Uploader', 'image_sizes_advanced' ] ) );

		// Vote hooks.
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_answer', [ 'AnsPress_Vote', 'delete_votes' ] ) );
		$this->assertEquals( 10, has_action( 'ap_deleted_votes', [ 'AnsPress_Vote', 'ap_deleted_votes' ] ) );

		// Form hooks.
		$this->assertEquals( 11, has_action( 'ap_form_question', [ 'AP_Form_Hooks', 'question_form' ] ) );
		$this->assertEquals( 11, has_action( 'ap_form_answer', [ 'AP_Form_Hooks', 'answer_form' ] ) );
		$this->assertEquals( 11, has_action( 'ap_form_comment', [ 'AP_Form_Hooks', 'comment_form' ] ) );
		$this->assertEquals( 11, has_action( 'ap_form_image_upload', [ 'AP_Form_Hooks', 'image_upload_form' ] ) );

		// Subscriptions.
		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Hooks', 'question_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Hooks', 'answer_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'ap_new_subscriber', [ 'AnsPress_Hooks', 'new_subscriber' ] ) );
		$this->assertEquals( 10, has_action( 'ap_delete_subscribers', [ 'AnsPress_Hooks', 'delete_subscribers' ] ) );
		$this->assertEquals( 10, has_action( 'ap_delete_subscriber', [ 'AnsPress_Hooks', 'delete_subscriber' ] ) );
		$this->assertEquals( 10, has_action( 'before_delete_post', [ 'AnsPress_Hooks', 'delete_subscriptions' ] ) );
		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ 'AnsPress_Hooks', 'comment_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'deleted_comment', [ 'AnsPress_Hooks', 'delete_comment_subscriptions' ] ) );
		$this->assertEquals( 11, has_action( 'get_comments_number', [ 'AnsPress_Hooks', 'get_comments_number' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'add_ap_tables' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'after_new_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'after_new_answer' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'before_delete' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'delete_answer' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'trash_post_action' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'untrash_posts' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'new_comment_approve' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'comment_approve' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'comment_unapprove' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'comment_trash' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'publish_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'unpublish_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'edit_comment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'fix_nav_current_class' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'flush_rules' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'wp_insert_post_data' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'sanitize_description' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'safe_style_css' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'base_page_update' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'save_question_hooks' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'save_answer_hooks' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'transition_post_status' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'update_user_vote_casted_count' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'delete_subscriber' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'display_question_metas' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'human_time_diff' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'widget_comments_args' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'question_subscription' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'answer_subscription' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'new_subscriber' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'delete_subscribers' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'delete_subscriptions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'comment_subscription' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'delete_comment_subscriptions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Hooks', 'get_comments_number' ) );
	}

	/**
	 * @covers AnsPress_Hooks::comment_subscription
	 * @covers AnsPress_Hooks::delete_comment_subscriptions
	 */
	public function testCommentSubscription() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ 'AnsPress_Hooks', 'comment_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'deleted_comment', [ 'AnsPress_Hooks', 'delete_comment_subscriptions' ] ) );
		$this->setRole( 'subscriber' );

		$question_id = $this->insert_question();
		$comment_id  = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $question_id,
				'user_id'         => get_current_user_id(),
			)
		);
		$comment = get_comment( $comment_id );

		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertTrue( null !== ap_get_subscriber( false, 'question_' . $question_id, $comment_id ) );

		wp_delete_comment( $comment_id, true );
		$this->assertTrue(
			[] === ap_get_subscribers(
				[
					'subs_event'  => 'question_' . $question_id,
					'subs_ref_id' => $comment_id,
				]
			)
		);

		$this->setRole( 'subscriber' );
		$ids        = $this->insert_answer();
		$comment_id = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $ids->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$comment = get_comment( $comment_id );

		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertTrue( null !== ap_get_subscriber( false, 'answer_' . $ids->a, $comment_id ) );

		wp_delete_comment( $comment_id, true );
		$this->assertTrue(
			[] === ap_get_subscribers(
				[
					'subs_event'  => 'answer_' . $ids->a,
					'subs_ref_id' => $comment_id,
				]
			)
		);
	}

	/**
	 * @covers AnsPress_Hooks::question_subscription
	 * @covers AnsPress_Hooks::answer_subscription
	 */
	public function testQuestionAnswerAuthorSubscribe() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Hooks', 'question_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Hooks', 'answer_subscription' ] ) );

		// Question subscription.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$post_obj    = get_post( $question_id );
		$this->assertTrue( null !== ap_new_subscriber( $post_obj->post_author, 'question', $post_obj->ID ) );

		// Answer subscription.
		$this->setRole( 'subscriber' );
		$answer_id = $this->insert_answer( '', '', get_current_user_id() );
		$post_obj    = get_post( $answer_id );
		$this->assertTrue( null !== ap_new_subscriber( $post_obj->post_author, 'answer_' . $answer_id->a, $post_obj->post_parent ) );
	}

	/**
	 * @covers AnsPress_Hooks::add_ap_tables
	 */
	public function testAddAPTables() {
		// Call the method.
		$this->assertEquals( 10, has_action( 'registered_taxonomy', [ 'AnsPress_Hooks', 'add_ap_tables' ] ) );
		\AnsPress_Hooks::add_ap_tables();

		// Test begins.
		global $wpdb;
		$expected_tables = [ 'ap_qameta', 'ap_votes', 'ap_views', 'ap_reputations', 'ap_subscribers', 'ap_activity', 'ap_reputation_events' ];
		foreach ( $expected_tables as $table ) {
			$this->assertTrue( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}{$table}'" ) === $wpdb->prefix . $table );
		}
	}

	/**
	 * @covers AnsPress_Hooks::after_new_question
	 */
	public function testAfterNewQuestion() {
		$this->assertEquals( 1, has_action( 'ap_processed_new_question', [ 'AnsPress_Hooks', 'after_new_question' ] ) );

		// Test begins.
		$hook_triggered = false;
		add_action( 'ap_after_new_question', function ( $triggered_post_id, $triggered_post ) use ( &$hook_triggered ) {
			$hook_triggered = true;
		}, 10, 2 );

		// Test by creating a new question.
		$question = $this->factory->post->create_and_get(
			array(
				'post_title'   => 'Test question',
				'post_content' => 'Test question content',
				'post_type'    => 'question',
			)
		);
		$this->assertTrue( $hook_triggered );

		// Test by directly calling the method.
		$hook_triggered = false;
		\AnsPress_Hooks::after_new_question( $question->ID, $question );
		$this->assertTrue( $hook_triggered );
	}

	/**
	 * @covers AnsPress_Hooks::after_new_answer
	 */
	public function testAfterNewAnswer() {
		$this->assertEquals( 1, has_action( 'ap_processed_new_answer', [ 'AnsPress_Hooks', 'after_new_answer' ] ) );

		// Test begins.
		$hook_triggered = false;
		add_action( 'ap_after_new_answer', function ( $triggered_post_id, $triggered_post ) use ( &$hook_triggered ) {
			$hook_triggered = true;
		}, 10, 2 );

		// Test by creating a new answer.
		$question_id = $this->insert_question();
		$answer = $this->factory->post->create_and_get(
			array(
				'post_title'   => 'Test answer',
				'post_content' => 'Test answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertEquals( 1, ap_get_answers_count( $question_id ) );
		$this->assertTrue( $hook_triggered );

		// Test by directly calling the method.
		$hook_triggered = false;
		\AnsPress_Hooks::after_new_answer( $answer->ID, $answer );
		$this->assertEquals( 1, ap_get_answers_count( $question_id ) );
		$this->assertTrue( $hook_triggered );
	}

	/**
	 * @covers AnsPress_Hooks::widget_comments_args
	 */
	public function testWidgetCommentsArgs() {
		$this->assertEquals( 10, has_action( 'widget_comments_args', [ 'AnsPress_Hooks', 'widget_comments_args' ] ) );

		// Test begins.
		// Without adding any comments.
		$modified_args = \AnsPress_Hooks::widget_comments_args( [] );
		$this->assertArrayHasKey( 'type__not_in', $modified_args );
		$this->assertEquals( array( 'anspress' ), $modified_args['type__not_in'] );
	}

	/**
	 * @covers AnsPress_Hooks::sanitize_description
	 */
	public function testSanitizeDescription() {
		$this->assertEquals( 10, has_filter( 'ap_form_contents_filter', [ 'AnsPress_Hooks', 'sanitize_description' ] ) );

		// Test begins.
		$this->assertEquals( 'Test description', \AnsPress_Hooks::sanitize_description( '     Test description    ' ) );
		$this->assertEquals( 'Test description', \AnsPress_Hooks::sanitize_description( 'Test description' ) );
		$this->assertEquals( '<p>Test description</p>', \AnsPress_Hooks::sanitize_description( '<p>Test description</p>' ) );
		$this->assertEquals( 'Test description', \AnsPress_Hooks::sanitize_description( 'Test description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ) );
		$this->assertEquals( '<p>Test description</p><script>alert("test");</script>', \AnsPress_Hooks::sanitize_description( '<p>Test description</p><script>alert("test");</script>' ) );
		$this->assertEquals( 'Test &nbsp;&nbsp;    description', \AnsPress_Hooks::sanitize_description( 'Test &nbsp;&nbsp;    description' ) );
		$this->assertEquals( 'Test     description', \AnsPress_Hooks::sanitize_description( 'Test     description' ) );
	}

	/**
	 * @covers AnsPress_Hooks::safe_style_css
	 */
	public function testSafeStyleCSS() {
		$this->assertEquals( 11, has_action( 'safe_style_css', [ 'AnsPress_Hooks', 'safe_style_css' ] ) );

		// Test for allowing custom style attributes.
		global $ap_kses_check;
		$ap_kses_check = true;
		$result = \AnsPress_Hooks::safe_style_css( [] );
		$this->assertTrue( in_array( 'text-decoration', $result ) );
		$this->assertTrue( in_array( 'text-align', $result ) );
		$this->assertEquals( [ 'text-decoration', 'text-align' ], $result );

		// Test for disallowing custom style attributes.
		$ap_kses_check = false;
		$result = \AnsPress_Hooks::safe_style_css( [] );
		$this->assertEquals( [], $result );
		$this->assertEmpty( $result );
	}

	/**
	 * @covers AnsPress_Hooks::human_time_diff
	 */
	public function testHumanTimeDiff() {
		// Test begins.
		$this->assertEquals( '1 minute', \AnsPress_Hooks::human_time_diff( '1 min' ) );
		$this->assertEquals( '5 minutes', \AnsPress_Hooks::human_time_diff( '5 mins' ) );
		$this->assertEquals( '1 hour', \AnsPress_Hooks::human_time_diff( '1 hour' ) );
		$this->assertEquals( '5 hours', \AnsPress_Hooks::human_time_diff( '5 hours' ) );
		$this->assertEquals( '1 day', \AnsPress_Hooks::human_time_diff( '1 day' ) );
		$this->assertEquals( '5 days', \AnsPress_Hooks::human_time_diff( '5 days' ) );
		$this->assertEquals( '1 week', \AnsPress_Hooks::human_time_diff( '1 week' ) );
		$this->assertEquals( '5 weeks', \AnsPress_Hooks::human_time_diff( '5 weeks' ) );
		$this->assertEquals( '1 year', \AnsPress_Hooks::human_time_diff( '1 year' ) );
		$this->assertEquals( '5 years', \AnsPress_Hooks::human_time_diff( '5 years' ) );
	}

	/**
	 * @covers AnsPress_Hooks::flush_rules
	 */
	public function testFlushRules() {
		$this->assertEquals( 10, has_action( 'wp_loaded', [ 'AnsPress_Hooks', 'flush_rules' ] ) );

		// Test begins.
		// Test by directly calling the method.
		$this->assertNull( \AnsPress_Hooks::flush_rules() );

		// Test by modifying the ap_flush option.
		// Test 1.
		ap_opt( 'ap_flush', 'true' );
		$this->assertEquals( 'true', ap_opt( 'ap_flush' ) );
		\AnsPress_Hooks::flush_rules();
		$this->assertEquals( 'false', ap_opt( 'ap_flush' ) );

		// Test 2.
		ap_opt( 'ap_flush', 'false' );
		$this->assertEquals( 'false', ap_opt( 'ap_flush' ) );
		$this->assertNull( \AnsPress_Hooks::flush_rules() );
	}

	/**
	 * @covers AnsPress_Hooks::show_admin_bar
	 */
	public function testShowAdminBar() {
		$this->assertEquals( 10, has_action( 'show_admin_bar', [ 'AnsPress_Hooks', 'show_admin_bar' ] ) );

		// Test begins.
		// Test 1.
		$this->assertTrue( \AnsPress_Hooks::show_admin_bar() );

		// Test 2.
		ap_opt( 'show_admin_bar', false );
		$this->assertFalse( \AnsPress_Hooks::show_admin_bar() );

		// Test 3.
		add_filter( 'ap_show_admin_bar', '__return_true' );
		$this->assertTrue( \AnsPress_Hooks::show_admin_bar() );
		remove_filter( 'ap_show_admin_bar', '__return_true' );
		$this->assertFalse( \AnsPress_Hooks::show_admin_bar() );

		// Test 4.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );

			// Before granting super admin role.
			$this->assertFalse( \AnsPress_Hooks::show_admin_bar() );

			// After granting super admin role.
			grant_super_admin( get_current_user_id() );
			$this->assertTrue( \AnsPress_Hooks::show_admin_bar() );
		} else {
			$this->setRole( 'administrator' );
			$this->assertTrue( \AnsPress_Hooks::show_admin_bar() );
		}

		// Reset the option.
		ap_opt( 'show_admin_bar', true );
	}

	/**
	 * @covers AnsPress_Hooks::fix_nav_current_class
	 */
	public function testFixNavCurrentClass() {
		$class = [ 'menu-item' ];
		$item = null;
		$result = \AnsPress_Hooks::fix_nav_current_class( $class, $item );
		$this->assertFalse( in_array( 'current-menu-item', $result ) );
		$this->assertEquals( $class, $result );
	}

	/**
	 * @covers AnsPress_Hooks::fix_nav_current_class
	 */
	public function testFixNavCurrentClassWithNotValidItem() {
		$class = [ 'menu-item' ];
		$item = 'invalid_object';
		$result = \AnsPress_Hooks::fix_nav_current_class( $class, $item );
		$this->assertFalse( in_array( 'current-menu-item', $result ) );
		$this->assertEquals( $class, $result );
	}

	/**
	 * @covers AnsPress_Hooks::fix_nav_current_class
	 */
	public function testFixNavCurrentClassWithValidItem() {
		$class = [ 'menu-item' ];
		$item = (object) [ 'object' => 'user' ];
		set_query_var( 'ap_page', 'user' );
		$result = \AnsPress_Hooks::fix_nav_current_class( $class, $item );
		$this->assertTrue( in_array( 'current-menu-item', $result ) );
		$expected = [ 'menu-item', 'current-menu-item' ];
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress_Hooks::fix_nav_current_class
	 */
	public function testFixNavCurrentClassWithValidItemAndNotMatchingPage() {
		$class = [ 'menu-item' ];
		$item = (object) [ 'object' => 'user' ];
		set_query_var( 'ap_page', 'question' );
		$result = \AnsPress_Hooks::fix_nav_current_class( $class, $item );
		$this->assertFalse( in_array( 'current-menu-item', $result ) );
		$this->assertEquals( $class, $result );
	}
}
