<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormValidate extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_text_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_textarea_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_title' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_array_remove_empty' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_wp_kses' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_absint' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_intval' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_boolean' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_array_map_boolean' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_esc_html' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_email' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_esc_url' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_description' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'whitelist_shortcodes' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'pre_content' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'code_content' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_tags_field' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'sanitize_upload' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_required' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_not_zero' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_is_email' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_is_url' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_is_numeric' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_min_string_length' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_max_string_length' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_is_array' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_array_min' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_array_max' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'get_bad_words' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_badwords' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'file_have_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'file_size_error' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'file_valid_type' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_upload' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Validate', 'validate_is_checked' ) );
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_text_field
	 */
	public function testValidateSanitizeTextField() {
		// Normal test on strings.
		$this->assertEquals( 'AnsPress Question Answer', \AnsPress\Form\Validate::sanitize_text_field( 'AnsPress   Question   Answer' ) );
		$this->assertEquals( '\AnsPress', \AnsPress\Form\Validate::sanitize_text_field( '\\AnsPress     ' ) );
		$this->assertEquals( '\AnsPress', \AnsPress\Form\Validate::sanitize_text_field( '     \\AnsPress     ' ) );
		$this->assertEquals( 'Question title', \AnsPress\Form\Validate::sanitize_text_field( '<h1 class="entry-title">Question title</h1>' ) );
		$this->assertEquals( 'Answer content', \AnsPress\Form\Validate::sanitize_text_field( '<p class="entry-content">Answer content</p>' ) );
		$this->assertEquals( '&lt; AnsPress Question Answer &lt; Plugin', \AnsPress\Form\Validate::sanitize_text_field( '   <          AnsPress Question Answer < Plugin          ' ) );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_text_field() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_text_field( '' ) );

		// Test on arrays.
		$arr = [
			'AnsPress   Question   Answer',
			'\\AnsPress     ',
			'     \\AnsPress     ',
			'<h1 class="entry-title">Question title</h1>',
			'<p class="entry-content">Answer content</p>',
			'   <          AnsPress Question Answer < Plugin          ',
			'',
			'',
		];
		$this->assertEquals(
			[
				'AnsPress Question Answer',
				'\AnsPress',
				'\AnsPress',
				'Question title',
				'Answer content',
				'&lt; AnsPress Question Answer &lt; Plugin',
				null,
				null,
			],
			\AnsPress\Form\Validate::sanitize_text_field( $arr )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_textarea_field
	 */
	public function testValidateSanitizeTextareaField() {
		// Normal test on strings.
		$this->assertEquals( 'AnsPress   Question   Answer', \AnsPress\Form\Validate::sanitize_textarea_field( 'AnsPress   Question   Answer' ) );
		$this->assertEquals( '\AnsPress', \AnsPress\Form\Validate::sanitize_textarea_field( '\\AnsPress     ' ) );
		$this->assertEquals( '\AnsPress', \AnsPress\Form\Validate::sanitize_textarea_field( '     \\AnsPress     ' ) );
		$this->assertEquals( 'Question title', \AnsPress\Form\Validate::sanitize_textarea_field( '<h1 class="entry-title">Question title</h1>' ) );
		$this->assertEquals( 'Answer content', \AnsPress\Form\Validate::sanitize_textarea_field( '<p class="entry-content">Answer content</p>' ) );
		$this->assertEquals( '&lt;          AnsPress Question Answer &lt; Plugin', \AnsPress\Form\Validate::sanitize_textarea_field( '   <          AnsPress Question Answer < Plugin          ' ) );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_textarea_field() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_textarea_field( '' ) );

		// Test on arrays.
		$arr = [
			'AnsPress   Question   Answer',
			'\\AnsPress     ',
			'     \\AnsPress     ',
			'<h1 class="entry-title">Question title</h1>',
			'<p class="entry-content">Answer content</p>',
			'   <          AnsPress Question Answer < Plugin          ',
			'',
			'',
		];
		$this->assertEquals(
			[
				'AnsPress   Question   Answer',
				'\AnsPress',
				'\AnsPress',
				'Question title',
				'Answer content',
				'&lt;          AnsPress Question Answer &lt; Plugin',
				null,
				null,
			],
			\AnsPress\Form\Validate::sanitize_textarea_field( $arr )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_title
	 */
	public function testValidateSanitizeTitle() {
		// Normal test on strings.
		$this->assertEquals( 'anspress-question-answer', \AnsPress\Form\Validate::sanitize_title( 'AnsPress   Question   Answer' ) );
		$this->assertEquals( 'anspress', \AnsPress\Form\Validate::sanitize_title( '\\AnsPress     ' ) );
		$this->assertEquals( 'anspress', \AnsPress\Form\Validate::sanitize_title( '     \\AnsPress     ' ) );
		$this->assertEquals( 'question-title', \AnsPress\Form\Validate::sanitize_title( '<h1 class="entry-title">Question title</h1>' ) );
		$this->assertEquals( 'answer-content', \AnsPress\Form\Validate::sanitize_title( '<p class="entry-content">Answer content</p>' ) );
		$this->assertEquals( 'anspress-question-answer-plugin', \AnsPress\Form\Validate::sanitize_title( '   <          AnsPress Question Answer < Plugin          ' ) );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_title() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_title( '' ) );

		// Test on arrays.
		$arr = [
			'AnsPress   Question   Answer',
			'\\AnsPress     ',
			'     \\AnsPress     ',
			'<h1 class="entry-title">Question title</h1>',
			'<p class="entry-content">Answer content</p>',
			'   <          AnsPress Question Answer < Plugin          ',
			'',
			'',
		];
		$this->assertEquals(
			[
				'anspress-question-answer',
				'anspress',
				'anspress',
				'question-title',
				'answer-content',
				'anspress-question-answer-plugin',
				null,
				null,
			],
			\AnsPress\Form\Validate::sanitize_title( $arr )
		);
	}
}
