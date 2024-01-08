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
}
