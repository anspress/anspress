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
		$a_id1 = $this->factory->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->go_to( '/?post_type=question&p=' . $question_id );
		$result = \AnsPress_Theme::question_answer_post_class( [] );
		$this->assertContains( 'answer-count-1', $result );
		$a_id2 = $this->factory->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
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
		$this->assertEquals( 'Question Title [Solved]  | ', $result );
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
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
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
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $question_id );
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
}
