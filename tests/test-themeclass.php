<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestThemeClass extends TestCase {

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
}
