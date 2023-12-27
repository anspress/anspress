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

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_boolean
	 */
	public function testValidateSanitizeBoolean() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_boolean() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_boolean( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_boolean() );
		$this->assertFalse( \AnsPress\Form\Validate::sanitize_boolean( '' ) );
		$this->assertFalse( \AnsPress\Form\Validate::sanitize_boolean( false ) );

		// Test on passing values.
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 'question' ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 'answer' ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 'comment' ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 5 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 11 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 0xC8 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( true ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 11.11 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( -144.144 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( -144.144 * 10 ) );
		$this->assertTrue( \AnsPress\Form\Validate::sanitize_boolean( 'AnsPress Question Answer Plugin' ) );
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_array_map_boolean
	 */
	public function testValidateSanitizeArrayMapBoolean() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_array_map_boolean() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_array_map_boolean( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_map_boolean() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_map_boolean( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_map_boolean( false ) );

		// Test on array passed.
		$arr = [ 'questions', 'answers', 'comments' ];
		$this->assertEquals(
			[ true, true, true ],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [ 'questions', 'answers', '', 'comments' ];
		$this->assertEquals(
			[ true, true, false, true ],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [ 'anspress', '', '', 'plugins' ];
		$this->assertEquals(
			[ true, false, false, true ],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [ '', '', '', '' ];
		$this->assertEquals(
			[ false, false, false, false ],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [ '', '', '', 'anspress' ];
		$this->assertEquals(
			[ false, false, false, true ],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);

		// Test on array passed with key and value pairs.
		$arr = [
			'q_id' => 10,
			'a_id' => 11,
			'c_id' => 15,
		];
		$this->assertEquals(
			[
				'q_id' => true,
				'a_id' => true,
				'c_id' => true,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => 11,
			'p_id' => '',
			'c_id' => 15,
		];
		$this->assertEquals(
			[
				'q_id' => true,
				'a_id' => true,
				'p_id' => false,
				'c_id' => true,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => null,
			'p_id' => '',
			'c_id' => null,
		];
		$this->assertEquals(
			[
				'q_id' => true,
				'a_id' => false,
				'p_id' => false,
				'c_id' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'q_id' => 10,
			'a_id' => null,
			'p_id' => 'anspress',
			'c_id' => null,
		];
		$this->assertEquals(
			[
				'q_id' => true,
				'a_id' => false,
				'p_id' => true,
				'c_id' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'q_id' => null,
			'a_id' => null,
			'p_id' => null,
			'c_id' => null,
		];
		$this->assertEquals(
			[
				'q_id' => false,
				'a_id' => false,
				'p_id' => false,
				'c_id' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'q_id' => '',
			'a_id' => '',
			'p_id' => '',
			'c_id' => '',
		];
		$this->assertEquals(
			[
				'q_id' => false,
				'a_id' => false,
				'p_id' => false,
				'c_id' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'question'         => 'Question title',
			'question_content' => 'Question content',
		];
		$this->assertEquals(
			[
				'question'         => true,
				'question_content' => true,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'question'         => '',
			'question_content' => null,
		];
		$this->assertEquals(
			[
				'question'         => false,
				'question_content' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'id' => '111',
		];
		$this->assertEquals(
			[
				'id' => true,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [
			'id' => null,
		];
		$this->assertEquals(
			[
				'id' => false,
			],
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
		$arr = [];
		$this->assertEquals(
			null,
			\AnsPress\Form\Validate::sanitize_array_map_boolean( $arr )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_esc_html
	 */
	public function testValidateSanitizeEscHTML() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_esc_html() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_esc_html( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_html() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_html( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_html( false ) );

		// Test on passing values.
		$this->assertEquals( 'AnsPress Question Answer Plugins', \AnsPress\Form\Validate::sanitize_esc_html( 'AnsPress Question Answer Plugins' ) );
		$this->assertEquals( '&lt;h1 class=&quot;entry-title&quot;&gt;AnsPress Question Answer Plugins&lt;/h1&gt;', \AnsPress\Form\Validate::sanitize_esc_html( '<h1 class="entry-title">AnsPress Question Answer Plugins</h1>' ) );
		$this->assertEquals( 'Go to     &lt;a href=&quot;https://anspress.net/&quot;&gt;AnsPress Question Answer Plugins&lt;/a&gt;', \AnsPress\Form\Validate::sanitize_esc_html( 'Go to     <a href="https://anspress.net/">AnsPress Question Answer Plugins</a>' ) );
		$this->assertEquals( '&lt;title&gt;AnsPress Question Answer Plugins&lt;/title&gt;', \AnsPress\Form\Validate::sanitize_esc_html( '<title>AnsPress Question Answer Plugins</title>' ) );
		$this->assertEquals( '     &lt;span&gt;Question&#039;s Navigation&lt;/span&gt;', \AnsPress\Form\Validate::sanitize_esc_html( '     <span>Question\'s Navigation</span>' ) );
		$this->assertEquals( '     Question&#039;s Answer&#039;s Comment&#039;s     ', \AnsPress\Form\Validate::sanitize_esc_html( '     Question\'s Answer\'s Comment\'s     ' ) );
		$this->assertEquals( '     AnsPress(Question Answer) Plugin     ', \AnsPress\Form\Validate::sanitize_esc_html( '     AnsPress(Question Answer) Plugin     ' ) );
		$this->assertEquals( '--&gt;  &lt;--', \AnsPress\Form\Validate::sanitize_esc_html( '-->  <--' ) );
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_email
	 */
	public function testValidateSanitizeEmail() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_email() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_email( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_email() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_email( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_email( false ) );

		// Test on passing values.
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( true ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( '' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( '@' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( 'webmaster@' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( 'webmaster@localhost' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( '@anspress.net' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( '@   local   .   io' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( '123' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_email( 'AnsPress Question Answer Plugin' ) );
		$this->assertEquals( 'test@local.io', \AnsPress\Form\Validate::sanitize_email( '     test@local.io    ' ) );
		$this->assertEquals( 'webmaster@local.io', \AnsPress\Form\Validate::sanitize_email( '     web       master     @local.io    ' ) );
		$this->assertEquals( 'webmaster@local.io', \AnsPress\Form\Validate::sanitize_email( '     webmaster     @local   .   io    ' ) );
		$this->assertEquals( 'admin@anspress.net', \AnsPress\Form\Validate::sanitize_email( '     admin@anspress.net' ) );
		$this->assertEquals( '123@anspress.net', \AnsPress\Form\Validate::sanitize_email( '     123@anspress.net     ' ) );
		$this->assertEquals( '123admin@anspress.net', \AnsPress\Form\Validate::sanitize_email( '     123admin@anspress.net' ) );
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_esc_url
	 */
	public function testValidateSanitizeEscURL() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_esc_url() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_esc_url( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_url() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_url( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_esc_url( false ) );

		// Test on passing values.
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_esc_url( '' ) );
		$this->assertEquals( '#', \AnsPress\Form\Validate::sanitize_esc_url( '#' ) );
		$this->assertEquals( 'http://1', \AnsPress\Form\Validate::sanitize_esc_url( true ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_esc_url( 'sftp://root@1.1.1.1' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_esc_url( '     sftp://root@1.1.1.1     ' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_esc_url( 'ssh://root@1.1.1.1' ) );
		$this->assertEquals( '', \AnsPress\Form\Validate::sanitize_esc_url( '     ssh://root@1.1.1.1     ' ) );
		$this->assertEquals( 'http://anspress.net', \AnsPress\Form\Validate::sanitize_esc_url( 'anspress.net' ) );
		$this->assertEquals( 'http://anspress.net%20%20%20%20%20', \AnsPress\Form\Validate::sanitize_esc_url( '     anspress.net     ' ) );
		$this->assertEquals( 'http://anspress%20%20%20%20%20.%20%20%20%20%20net%20%20%20%20%20', \AnsPress\Form\Validate::sanitize_esc_url( '     anspress     .     net     ' ) );
		$this->assertEquals( 'https://anspress.net/themes/', \AnsPress\Form\Validate::sanitize_esc_url( 'https://anspress.net/themes/' ) );
		$this->assertEquals( 'https://anspress.net/themes/', \AnsPress\Form\Validate::sanitize_esc_url( '     https://anspress.net/themes/' ) );
		$this->assertEquals( 'https://anspress.net/themes/', \AnsPress\Form\Validate::sanitize_esc_url( '     https://anspress.net/themes/' ) );
		$this->assertEquals( 'https://anspress.net/themes/', \AnsPress\Form\Validate::sanitize_esc_url( 'https;//anspress.net/themes/' ) );
		$this->assertEquals( 'http://webmaster@anspress.net', \AnsPress\Form\Validate::sanitize_esc_url( 'webmaster@anspress.net' ) );
		$this->assertEquals( 'mailto:webmaster@anspress.net', \AnsPress\Form\Validate::sanitize_esc_url( 'mailto:webmaster@anspress.net' ) );
		$this->assertEquals( 'mailto:webmaster@anspress.net', \AnsPress\Form\Validate::sanitize_esc_url( '     mailto:webmaster@anspress.net' ) );
		$this->assertEquals( 'mailto:%20%20%20webmaster%20%20%20@%20%20%20localhost%20%20%20.%20%20%20local', \AnsPress\Form\Validate::sanitize_esc_url( 'mailto:   webmaster   @   localhost   .   local' ) );
		$this->assertEquals( 'mailto:%20%20%20webmaster%20%20%20@%20%20%20localhost%20%20%20.%20%20%20local', \AnsPress\Form\Validate::sanitize_esc_url( '   mailto:   webmaster   @   localhost   .   local' ) );
		$this->assertEquals( 'ftp://anspress.net:22', \AnsPress\Form\Validate::sanitize_esc_url( 'ftp://anspress.net:22' ) );
		$this->assertEquals( 'ftp://anspress.net:22%20%20%20%20%20', \AnsPress\Form\Validate::sanitize_esc_url( '     ftp://anspress.net:22     ' ) );
		$this->assertEquals( 'ftps://anspress.net:21', \AnsPress\Form\Validate::sanitize_esc_url( 'ftps://anspress.net:21' ) );
		$this->assertEquals( 'ftps://anspress.net:21%20%20%20%20%20', \AnsPress\Form\Validate::sanitize_esc_url( '     ftps://anspress.net:21     ' ) );
		$this->assertEquals( 'tel:+1234567890', \AnsPress\Form\Validate::sanitize_esc_url( 'tel:+1234567890' ) );
		$this->assertEquals( 'tel:+1234567890%20%20%20%20%20', \AnsPress\Form\Validate::sanitize_esc_url( 'tel:+1234567890     ' ) );
	}
}
