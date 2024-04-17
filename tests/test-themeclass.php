<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestThemeClass extends TestCase {

	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'init_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'template_include' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'template_include_theme_compat' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'includes_theme' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'question_answer_post_class' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'body_class' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'ap_title' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'ap_before_html_body' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'wp_head' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'post_actions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'question_attachments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'anspress_basepage_template' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'get_the_excerpt' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'remove_hentry_class' ) );
		$this->assertTrue( method_exists( 'AnsPress_Theme', 'after_question_content' ) );
	}

	/**
	 * @covers AnsPress_Theme::init_actions
	 */
	public function testAnsPressThemeInitActions() {
		// Delete the shortcodes.
		remove_shortcode( 'anspress' );
		remove_shortcode( 'question' );
		$this->assertFalse( shortcode_exists( 'anspress' ) );
		$this->assertFalse( shortcode_exists( 'question' ) );

		// After calling the method, the shortcodes should be registered.
		\AnsPress_Theme::init_actions();
		$this->assertTrue( shortcode_exists( 'anspress' ) );
		$this->assertTrue( shortcode_exists( 'question' ) );
	}

	/**
	 * @covers AnsPress_Theme::remove_hentry_class
	 */
	public function testRemoveHentryClass() {
		$id = $this->insert_answer();
		$post_classes = [ 'hentry', 'other-classes' ];

		// Test begins.
		// For question post type.
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $id->q );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// For answer post type.
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $id->a );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// For other post types.
		$post_id = $this->factory()->post->create();
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $post_id );
		$this->assertContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );
		$page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $page_id );
		$this->assertContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// For AnsPress pages.
		// Base page.
		$base_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $base_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// Ask page.
		$ask_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'ask_page', $ask_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $ask_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// User page.
		$user_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'user_page', $user_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $user_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// Categories page.
		$categories_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'categories_page', $categories_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $categories_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// Tags page.
		$tags_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'tags_page', $tags_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $tags_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );

		// Activities page.
		$activities_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'activities_page', $activities_page_id );
		$result = \AnsPress_Theme::remove_hentry_class( $post_classes, '', $activities_page_id );
		$this->assertNotContains( 'hentry', $result );
		$this->assertContains( 'other-classes', $result );
	}

	/**
	 * @covers AnsPress_Theme::body_class
	 */
	public function testBodyClass() {
		// Test without visiting any page.
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertEmpty( $result );

		// Test with basic page.
		$post_id = $this->factory()->post->create();
		$this->go_to( '/?post_type=post&id=' . $post_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertEmpty( $result );

		// Test with AnsPress related pages.
		// Single question page.
		$question_id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-question', $result );

		// Single answer page.
		$answer_id = $this->insert_answer( $question_id );
		$this->go_to( ap_get_short_link( [ 'ap_a' => $answer_id->a ] ) );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );

		// Base page.
		$base_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page_id );
		$this->go_to( '/?page_id=' . $base_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-base', $result );

		// Ask page.
		$ask_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'ask_page', $ask_page_id );
		$this->go_to( '/?page_id=' . $ask_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-ask', $result );

		// User page.
		$user_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'user_page', $user_page_id );
		$this->go_to( '/?page_id=' . $user_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-user', $result );

		// Categories page.
		$categories_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'categories_page', $categories_page_id );
		$this->go_to( '/?page_id=' . $categories_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-categories', $result );

		// Tags page.
		$tags_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'tags_page', $tags_page_id );
		$this->go_to( '/?page_id=' . $tags_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-tags', $result );

		// Activities page.
		$activities_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'activities_page', $activities_page_id );
		$this->go_to( '/?page_id=' . $activities_page_id );
		$result = \AnsPress_Theme::body_class( [] );
		$this->assertContains( 'anspress-content', $result );
		$this->assertContains( 'ap-page-activities', $result );
	}

	/**
	 * @covers AnsPress_Theme::question_answer_post_class
	 */
	public function testQuestionAnswerPostClass() {
		// Test without visiting any page.
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertEmpty( $result );

		// Test with basic page.
		$post_id = $this->factory()->post->create();
		$this->go_to( '/?post_type=post&id=' . $post_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertEmpty( $result );

		// Test for question post type.
		$question_id = $this->insert_question();

		// For question without answer.
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'answer-count-0', $result );

		// For question with answer.
		$a_id1 = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'answer-count-1', $result );
		$a_id2 = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'answer-count-2', $result );

		// For question with selected answer.
		ap_set_selected_answer( $question_id, $a_id1 );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'answer-selected', $result );

		// For question with featured question.
		ap_set_featured_question( $question_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'featured-question', $result );

		// Test for answer post type.
		$id = $this->insert_answers( [], [], 5 );

		// For answer with selected answer.
		ap_set_selected_answer( $id['question'], $id['answers'][3] );
		ap_update_answer_selected( $id['answers'][3] );
		$this->go_to( '/?post_type=answer&p=' . $id['answers'][3] );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'best-answer', $result );
	}

	/**
	 * @covers AnsPress_Theme::ap_before_html_body
	 */
	public function testAPBeforeHtmlBody() {
		// Test with no user login.
		ob_start();

		\AnsPress_Theme::ap_before_html_body();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Test with user login.
		$this->setRole( 'subscriber' );
		ob_start();
		\AnsPress_Theme::ap_before_html_body();
		$output = ob_get_clean();
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'apCurrentUser', $output );
		$this->assertStringContainsString( 'user_login', $output );
		$this->assertStringContainsString( 'display_name', $output );
		$this->assertStringContainsString( 'user_email', $output );
		$this->assertStringContainsString( 'avatar', $output );
	}

	/**
	 * @covers AnsPress_Theme::wp_head
	 */
	public function testWPHead() {
		// Test when not viewing the base page.
		// Visiting home page.
		$this->go_to( '/' );
		ob_start();
		\AnsPress_Theme::wp_head();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Visting single question page.
		$question_id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		\AnsPress_Theme::wp_head();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Visting ask page.
		$ask_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'ask_page', $ask_page_id );
		$this->go_to( '/?page_id=' . $ask_page_id );
		ob_start();
		\AnsPress_Theme::wp_head();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Visiting base page.
		$base_page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		ap_opt( 'base_page', $base_page_id );
		$this->go_to( '/?page_id=' . $base_page_id );
		ob_start();
		\AnsPress_Theme::wp_head();
		$output = ob_get_clean();
		$this->assertNotEmpty( $output );
		$q_feed = get_post_type_archive_feed_link( 'question' );
		$a_feed = get_post_type_archive_feed_link( 'answer' );
		$this->assertStringContainsString( esc_url( $q_feed ), $output );
		$this->assertStringContainsString( esc_url( $a_feed ), $output );
		$this->assertStringContainsString( '<link rel="alternate" type="application/rss+xml" title="Question Feed" href="' . esc_url( $q_feed ) . '" />', $output );
		$this->assertStringContainsString( '<link rel="alternate" type="application/rss+xml" title="Answers Feed" href="' . esc_url( $a_feed ) . '" />', $output );
	}

	/**
	 * @covers AnsPress_Theme::ap_title
	 */
	public function testAPTitle() {
		// Test on normal post page visit.
		$post_id = $this->factory()->post->create( [ 'post_title' => 'Post Title' ] );
		$this->go_to( '/?post_type=post&p=' . $post_id );
		$result = \AnsPress_Theme::ap_title( 'Default Title' );
		$this->assertEquals( 'Default Title', $result );

		// Test on base page visit.
		$base_page_id = $this->factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Base Page Title' ] );
		ap_opt( 'base_page', $base_page_id );
		$this->go_to( '/?page_id=' . $base_page_id );
		$result = \AnsPress_Theme::ap_title( 'Default Title' );
		$this->assertEquals( 'Default Title', $result );

		// Test on single question page visit.
		$question_id = $this->factory()->post->create( [ 'post_title' => 'Question Title', 'post_type' => 'question' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::ap_title( 'Default Title' );
		$this->assertEquals( 'Question Title  | ', $result );

		// Test on single question page with with solved answer and solved prefix option disabled.
		ap_opt( 'show_solved_prefix', false );
		$question_id = $this->factory()->post->create( [ 'post_title' => 'Question Title', 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_title' => 'Answer Title', 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::ap_title( 'Default Title' );
		$this->assertEquals( 'Question Title | ', $result );

		// Test on single question page with with solved answer and solved prefix option enabled.
		ap_opt( 'show_solved_prefix', true );
		$question_id = $this->factory()->post->create( [ 'post_title' => 'Question Title', 'post_type' => 'question' ] );
		$answer_id = $this->factory()->post->create( [ 'post_title' => 'Answer Title', 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::ap_title( 'Default Title' );
		$this->assertEquals( '[Solved] Question Title  | ', $result );
	}

	/**
	 * @covers AnsPress_Theme::question_attachments
	 */
	public function testQuestionAttachments() {
		// Test without attachments.
		$question_id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		\AnsPress_Theme::question_attachments();
		$output = ob_get_clean();
		$this->assertEmpty( $output );

		// Test with attachments.
		// Test 1.
		$question_id = $this->insert_question();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		\AnsPress_Theme::question_attachments();
		$output = ob_get_clean();
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Attachment', $output );
		$this->assertStringContainsString( 'class="ap-attachments"', $output );
		$this->assertStringContainsString( 'class="ap-attachment"', $output );
		$media = get_post( $attachment_id );
		$this->assertStringContainsString( esc_url( wp_get_attachment_url( $media->ID ) ), $output );
		$this->assertStringContainsString( esc_html( basename( get_attached_file( $media->ID ) ) ), $output );
		$this->assertStringContainsString( '<i class="apicon-file-image-o"></i>', $output );

		// Test 2.
		$question_id = $this->insert_question();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		ob_start();
		\AnsPress_Theme::question_attachments();
		$output = ob_get_clean();
		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'Attachment', $output );
		$this->assertStringContainsString( 'class="ap-attachments"', $output );
		$this->assertStringContainsString( 'class="ap-attachment"', $output );
		$media = get_post( $attachment_id );
		$this->assertStringContainsString( esc_url( wp_get_attachment_url( $media->ID ) ), $output );
		$this->assertStringContainsString( esc_html( basename( get_attached_file( $media->ID ) ) ), $output );
		$this->assertStringContainsString( '<i class="apicon-file-pdf-o"></i>', $output );
	}

	/**
	 * @covers AnsPress_Theme::includes_theme
	 */
	public function testIncludesTheme() {
		// Call the method.
		\AnsPress_Theme::includes_theme();
		$this->assertEquals( 10, has_action( 'widgets_init', 'ap_widgets_positions' ) );
		$this->assertEquals( 1, has_action( 'wp_enqueue_scripts', 'ap_scripts_front' ) );
		$this->assertTrue( function_exists( 'ap_scripts_front' ) );
		$this->assertTrue( function_exists( 'ap_widgets_positions' ) );
	}

	/**
	 * @covers AnsPress_Theme::get_the_excerpt
	 */
	public function testGetTheExcerpt() {
		// Test 1.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_content' => 'This is question content', 'post_excerpt' => '' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertEquals( 'This is question content', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $question_id );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertEquals( 'This is question content', $result );

		// Test 2.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_excerpt' => 'This is question excerpt' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertEquals( 'This is question excerpt', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $question_id );
		$this->assertEquals( 'This is question excerpt', $result );

		// Test 3.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_excerpt' => '', 'post_content' => '' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::get_the_excerpt( '' );
		$this->assertEquals( '', $result );
		$result = \AnsPress_Theme::get_the_excerpt( '', $question_id );
		$this->assertEquals( '', $result );

		// Test 4.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_content' => 'This is question content', 'post_excerpt' => '' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_content' => 'This is answer content', 'post_excerpt' => '' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		set_query_var( 'answer_id', $answer_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertNotEquals( 'This is question content', $result );
		$this->assertEquals( 'This is answer content', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $question_id );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertNotEquals( 'This is question content', $result );
		$this->assertEquals( 'This is answer content', $result );

		// Test 5.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_excerpt' => 'This is question excerpt' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_excerpt' => 'This is answer excerpt' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		set_query_var( 'answer_id', $answer_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertNotEquals( 'This is question excerpt', $result );
		$this->assertEquals( 'This is answer excerpt', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $question_id );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertNotEquals( 'This is question excerpt', $result );
		$this->assertEquals( 'This is answer excerpt', $result );

		// Test 6.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_excerpt' => '', 'post_content' => '' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_excerpt' => '', 'post_content' => '' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		set_query_var( 'answer_id', $answer_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertEquals( '', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $question_id );
		$this->assertNotEquals( 'This is excerpt', $result );
		$this->assertEquals( '', $result );

		// Test 7.
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_content' => 'This is question content', 'post_excerpt' => '' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_content' => 'This is answer content', 'post_excerpt' => '' ] );
		$this->go_to( '/?post_type=answer&p=' . $answer_id );
		set_query_var( 'answer_id', $answer_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertNotEquals( 'This is question content', $result );
		$this->assertNotEquals( 'This is answer content', $result );
		$this->assertEquals( 'This is excerpt', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $answer_id );
		$this->assertNotEquals( 'This is question content', $result );
		$this->assertNotEquals( 'This is answer content', $result );
		$this->assertEquals( 'This is excerpt', $result );

		// Test 8.
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post', 'post_content' => 'This is post content', 'post_excerpt' => '' ] );
		$this->go_to( '/?post_type=post&p=' . $post_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertEquals( 'This is excerpt', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $post_id );
		$this->assertEquals( 'This is excerpt', $result );

		// Test 9.
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post', 'post_excerpt' => 'This is post excerpt' ] );
		$this->go_to( '/?post_type=post&p=' . $post_id );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt' );
		$this->assertEquals( 'This is excerpt', $result );
		$result = \AnsPress_Theme::get_the_excerpt( 'This is excerpt', $post_id );
		$this->assertEquals( 'This is excerpt', $result );

		// Test 10.
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post', 'post_excerpt' => 'This is post content', 'post_content' => 'This is post excerpt' ] );
		$this->go_to( '/?post_type=post&p=' . $post_id );
		$result = \AnsPress_Theme::get_the_excerpt( '' );
		$this->assertEquals( '', $result );
		$result = \AnsPress_Theme::get_the_excerpt( '', $post_id );
		$this->assertEquals( '', $result );
	}

	/**
	 * @covers AnsPress_Theme::template_include
	 */
	public function testTemplateInclude() {
		$template = 'some-templates.php';
		$expected = 'filtered-template.php';
		add_filter( 'ap_template_include', function( $template ) use ( $expected ) {
			return $expected;
		} );
		$result = \AnsPress_Theme::template_include( $template );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers AnsPress_Theme::template_include_theme_compat
	 */
	public function testTemplateIncludeThemeCompat() {
		// Test 1.
		$post_id = $this->factory()->post->create( [ 'post_type' => 'post' ] );
		$this->go_to( '/?post_type=post&p=' . $post_id );
		$this->assertEquals( false, anspress()->theme_compat->active );
		$result = \AnsPress_Theme::template_include_theme_compat( 'some-template.php' );
		$this->assertEquals( 'some-template.php', $result );
		$this->assertEquals( false, anspress()->theme_compat->active );
		global $post;
		$this->assertStringNotContainsString( '<div class="anspress" id="anspress">', $post->post_content );
		$this->assertStringNotContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $post->post_content );
		$this->assertStringNotContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $post->post_content );

		// Test 2.
		$id = $this->insert_question();
		$this->go_to( '/?post_type=question&p=' . $id );
		$this->assertEquals( false, anspress()->theme_compat->active );
		$result = \AnsPress_Theme::template_include_theme_compat();
		$this->assertEquals( '', $result );
		$this->assertEquals( true, anspress()->theme_compat->active );
		global $post;
		$this->assertStringContainsString( '<div class="anspress" id="anspress">', $post->post_content );
		$this->assertStringContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $post->post_content );
		$this->assertStringContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $post->post_content );

		// Test 3.
		$id = $this->insert_answer();
		$this->go_to( '/?post_type=question&p=' . $id->q );
		anspress()->theme_compat->active = false;
		$this->assertEquals( false, anspress()->theme_compat->active );
		$result = \AnsPress_Theme::template_include_theme_compat( 'some-template.php' );
		$this->assertEquals( 'some-template.php', $result );
		$this->assertEquals( true, anspress()->theme_compat->active );
		global $post;
		$this->assertStringContainsString( '<div class="anspress" id="anspress">', $post->post_content );
		$this->assertStringContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $post->post_content );
		$this->assertStringContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $post->post_content );
		$this->go_to( '/?post_type=answer&p=' . $id->a );
		anspress()->theme_compat->active = false;
		$this->assertEquals( false, anspress()->theme_compat->active );
		$result = \AnsPress_Theme::template_include_theme_compat( 'some-template.php' );
		$this->assertEquals( 'some-template.php', $result );
		$this->assertEquals( false, anspress()->theme_compat->active );
		global $post;
		$this->assertStringNotContainsString( '<div class="anspress" id="anspress">', $post->post_content );
		$this->assertStringNotContainsString( '<div id="ap-single" class="ap-q clearfix" itemscope itemtype="https://schema.org/QAPage">', $post->post_content );
		$this->assertStringNotContainsString( '<div class="ap-question-lr ap-row" itemscope itemtype="https://schema.org/Question" itemprop="mainEntity">', $post->post_content );
	}

	/**
	 * @covers AnsPress_Theme::ap_title_parts
	 */
	public function testAPTitlePartsForNonAnsPressPage() {
		$this->go_to( '/' );
		$args = [ 'title' => 'Document Title' ];
		$result = \AnsPress_Theme::ap_title_parts( $args );
		$this->assertEquals( $args, $result );
	}

	/**
	 * @covers AnsPress_Theme::ap_title_parts
	 */
	public function testAPTitlePartsForAnsPressPageButNotQuestionPage() {
		$base_page_id = $this->factory()->post->create( [ 'post_type' => 'page', 'post_title' => 'Base Page Title' ] );
		ap_opt( 'base_page', $base_page_id );
		$this->go_to( '/?page_id=' . $base_page_id );
		$args = [ 'title' => 'Document Title' ];
		$result = \AnsPress_Theme::ap_title_parts( $args );
		$this->assertEquals( $args, $result );
	}

	/**
	 * @covers AnsPress_Theme::ap_title_parts
	 */
	public function testAPTitlePartsForQuestionPage() {
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question', 'post_title' => 'Question Title' ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$args = [ 'title' => 'Document Title' ];
		$result = \AnsPress_Theme::ap_title_parts( $args );
		$this->assertNotEquals( $args, $result );
		$this->assertStringContainsString( 'Question Title', $result['title'] );
	}

	/**
	 * @covers AnsPress_Theme::after_question_content
	 */
	public function testAfterQuestionContent() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		ap_activity_add( [
			'action' => 'new_q',
			'q_id'   => $question_id,
			'date'   => '2024-01-01 00:00:00',
		] );
		global $post;
		$post = ap_get_post( $question_id );
		ob_start();
		\AnsPress_Theme::after_question_content();
		$output = ob_get_clean();
		$this->assertStringContainsString( '<postmessage><div class="ap-notice status-moderate"><i class="apicon-alert"></i><span>This Question is waiting for the approval by the moderator.</span></div></postmessage>', $output );
		$this->assertStringContainsString( '<div class="ap-post-updated"><i class="apicon-clock"></i>', $output );
		$this->assertStringContainsString( '<span class="ap-post-history"><a href="' . ap_user_link( get_current_user_id() ) . '" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">' . ap_user_display_name( get_current_user_id() ) . '</span></a> Changed status to moderate <a href="' . esc_url( ap_get_short_link( array( 'ap_q' => $question_id ) ) ) . '"><time itemprop="dateModified" datetime="' . mysql2date( 'c', date( 'Y-m-d H:i:s', strtotime( 'now' ) ) ) . '">' . ap_human_time( strtotime( 'now' ), false ) . '</time></a></span>', $output );
	}

	/**
	 * @covers AnsPress_Theme::question_answer_post_class
	 */
	public function testQuestionAnswerPostClassForUserWhoCantReadAnswer() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->insert_question( '', '', $user_id );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		global $post;
		$post = ap_get_post( $answer_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'no-permission', $result );
	}
}
