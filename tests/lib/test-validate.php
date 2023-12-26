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

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_array_remove_empty
	 */
	public function testValidateSanitizeArrayRemoveEmpty() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_array_remove_empty() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_array_remove_empty( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_remove_empty() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_remove_empty( '' ) );

		// Test on array passed.
		$arr = [ 'questions', 'answers', 'comments' ];
		$this->assertEquals(
			$arr,
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [ 'questions', 'answers', '', 'comments' ];
		$this->assertEquals(
			[
				0 => 'questions',
				1 => 'answers',
				3 => 'comments',
			],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [ 'anspress', '', '', 'comments' ];
		$this->assertEquals(
			[
				0 => 'anspress',
				3 => 'comments',
			],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [ '', '', '', '' ];
		$this->assertEquals( [], \AnsPress\Form\Validate::sanitize_array_remove_empty( $arr ) );

		// Test on array passed with key and value pairs.
		$arr = [
			'q_id' => 10,
			'a_id' => 11,
			'c_id' => 15,
		];
		$this->assertEquals(
			$arr,
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => 11,
			'p_id' => '',
			'c_id' => 15,
		];
		$this->assertEquals(
			[
				'q_id' => 10,
				'a_id' => 11,
				'c_id' => 15,
			],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => null,
			'p_id' => '',
			'c_id' => null,
		];
		$this->assertEquals(
			[
				'q_id' => 10,
			],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => null,
			'p_id' => 'anspress',
			'c_id' => null,
		];
		$this->assertEquals(
			[
				'q_id' => 10,
				'p_id' => 'anspress',
			],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [
			'q_id' => null,
			'a_id' => null,
			'p_id' => null,
			'c_id' => null,
		];
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
		$arr = [
			'q_id' => '',
			'a_id' => '',
			'p_id' => '',
			'c_id' => '',
		];
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_array_remove_empty( $arr )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_wp_kses
	 */
	public function testValidateSanitizeWPKses() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_wp_kses() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_wp_kses( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_wp_kses() );

		// Test on passing values.
		$this->assertEquals(
			'AnsPress Question Answer',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<div class="site-title">AnsPress Question Answer</div>' )
		);
		$this->assertEquals(
			'<p>AnsPress Question Answer</p>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<p class="site-title">AnsPress Question Answer</p>' )
		);
		$this->assertEquals(
			'<p>AnsPress Question Answer</p>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<p style="color:black;">AnsPress Question Answer</p>' )
		);
		$this->assertEquals(
			'<p style="text-align: left">AnsPress Question Answer</p>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<p style="text-align: left;">AnsPress Question Answer</p>' )
		);
		$this->assertEquals(
			'<p>AnsPress Question Answer</p>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<p>AnsPress Question Answer</p>' )
		);
		$this->assertEquals(
			'<a href="#" title="AnsPress Question Answer">AnsPress Question Answer</a>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<a href="#" title="AnsPress Question Answer" class="link" target="_blank">AnsPress Question Answer</a>' )
		);
		$this->assertEquals(
			'<strong style="text-align: center">AnsPress Question Answer</strong>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<strong style="text-align: center;">AnsPress Question Answer</strong>' )
		);
		$this->assertEquals(
			'<del>AnsPress Question Answer</del>',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<del class="delete" style="align: center">AnsPress Question Answer</del>' )
		);
		$this->assertEquals(
			'AnsPress Question Answer Navigation Menu',
			\AnsPress\Form\Validate::sanitize_wp_kses( '<nav id="main-navigation" class="site-navigation" style="text-align: right;">AnsPress Question Answer Navigation Menu</nav>' )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_absint
	 */
	public function testValidateSanitizeAbsint() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_absint() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_absint( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_absint() );

		// Test on passing values.
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( '' ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( null ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( 0 ) );
		$this->assertEquals( 5, \AnsPress\Form\Validate::sanitize_absint( 5 ) );
		$this->assertEquals( 99, \AnsPress\Form\Validate::sanitize_absint( 99.99 ) );
		$this->assertEquals( 5, \AnsPress\Form\Validate::sanitize_absint( -5 ) );
		$this->assertEquals( 11, \AnsPress\Form\Validate::sanitize_absint( -11.22 ) );
		$this->assertEquals( 99, \AnsPress\Form\Validate::sanitize_absint( -99.99 ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( 'number' ) );
		$this->assertEquals( 1, \AnsPress\Form\Validate::sanitize_absint( true ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( false ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_absint( 0.99 * 1 ) );
		$this->assertEquals( 1, \AnsPress\Form\Validate::sanitize_absint( 1.99 * 1 ) );
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_intval
	 */
	public function testValidateSanitizeIntval() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_intval() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_intval( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_intval() );

		// Test on passing values.
		$this->assertEquals( 1, \AnsPress\Form\Validate::sanitize_intval( true ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( false ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( '' ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( null ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( 0 ) );
		$this->assertEquals( 5, \AnsPress\Form\Validate::sanitize_intval( 5 ) );
		$this->assertEquals( 99, \AnsPress\Form\Validate::sanitize_intval( 99.99 ) );
		$this->assertEquals( -5, \AnsPress\Form\Validate::sanitize_intval( -5 ) );
		$this->assertEquals( -11, \AnsPress\Form\Validate::sanitize_intval( -11.22 ) );
		$this->assertEquals( -99, \AnsPress\Form\Validate::sanitize_intval( -99.99 ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( 0.99 * 1 ) );
		$this->assertEquals( 1, \AnsPress\Form\Validate::sanitize_intval( 1.99 * 1 ) );
		$this->assertEquals( 10, \AnsPress\Form\Validate::sanitize_intval( '10' ) );
		$this->assertEquals( 10, \AnsPress\Form\Validate::sanitize_intval( '+10' ) );
		$this->assertEquals( -10, \AnsPress\Form\Validate::sanitize_intval( '-10' ) );
		$this->assertEquals( 100, \AnsPress\Form\Validate::sanitize_intval( '1e2' ) );
		$this->assertEquals( 10000000000, \AnsPress\Form\Validate::sanitize_intval( '1e10' ) );
		$this->assertEquals( 64, \AnsPress\Form\Validate::sanitize_intval( 0100 ) );
		$this->assertEquals( 200, \AnsPress\Form\Validate::sanitize_intval( 0xC8 ) );
		$this->assertEquals( 100, \AnsPress\Form\Validate::sanitize_intval( '0100' ) );
		$this->assertEquals( 0, \AnsPress\Form\Validate::sanitize_intval( '0xC8' ) );
	}
}
