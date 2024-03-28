<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressFormValidate extends TestCase {

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
		register_taxonomy( 'question_tag', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		unregister_taxonomy( 'question_tag' );
		parent::tear_down();
	}

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
		$this->assertEquals( [], \AnsPress\Form\Validate::sanitize_text_field( [] ) );

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
		$this->assertEquals( [], \AnsPress\Form\Validate::sanitize_textarea_field( [] ) );

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
		$this->assertEquals( [], \AnsPress\Form\Validate::sanitize_title( [] ) );

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
		$this->assertEquals( [], \AnsPress\Form\Validate::sanitize_array_remove_empty( [] ) );

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
		$this->assertNull( \AnsPress\Form\Validate::sanitize_array_map_boolean( [] ) );
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

	// Allowed shortcode filter.
	public function shortcodes_allowed( $allowed ) {
		$allowed[] = 'anspress';

		return $allowed;
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_description
	 */
	public function testValidateSanitizeDescription() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_description() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_description( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_description() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_description( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_description( false ) );

		// Test on passing values.
		$this->assertEquals( 1, \AnsPress\Form\Validate::sanitize_description( true ) );
		$this->assertEquals( 'AnsPress Question Answer', \AnsPress\Form\Validate::sanitize_description( 'AnsPress Question Answer<!--more-->' ) );
		$this->assertEquals( 'AnsPress Question Answer', \AnsPress\Form\Validate::sanitize_description( '<!--more-->AnsPress Question Answer<!--more-->' ) );
		$this->assertEquals( '<--more-->AnsPress Question Answer<--more-->', \AnsPress\Form\Validate::sanitize_description( '<--more-->AnsPress Question Answer<--more-->' ) );
		$this->assertEquals( '<h1 class="entry-title">AnsPress Question Answer</h1>', \AnsPress\Form\Validate::sanitize_description( '<h1 class="entry-title">AnsPress Question Answer</h1>' ) );
		$this->assertEquals( '<p class="entry-content">Question Answer Plugin</p>', \AnsPress\Form\Validate::sanitize_description( '<p class="entry-content">Question Answer Plugin</p>' ) );
		$this->assertEquals( '&#91;anspress&#93;', \AnsPress\Form\Validate::sanitize_description( '[anspress]' ) );
		// $this->assertEquals( '[apcode]', \AnsPress\Form\Validate::sanitize_description( '[apcode]' ) );

		// Test on unregistered shortcodes.
		$this->assertEquals( '[anspress_question]', \AnsPress\Form\Validate::sanitize_description( '[anspress_question]' ) );
		$this->assertEquals( '[anspress_answer]', \AnsPress\Form\Validate::sanitize_description( '[anspress_answer]' ) );
		$this->assertEquals( '[anspress_comment]', \AnsPress\Form\Validate::sanitize_description( '[anspress_comment]' ) );

		// Test on filtering the shortcode whitelist.
		$this->assertEquals( '&#91;anspress&#93;', \AnsPress\Form\Validate::sanitize_description( '[anspress]' ) );
		add_filter( 'ap_allowed_shortcodes', [ $this, 'shortcodes_allowed' ] );
		$this->assertEquals( '[anspress]', \AnsPress\Form\Validate::sanitize_description( '[anspress]' ) );

		// Test after removing the shortcode whitelist filter.
		remove_filter( 'ap_allowed_shortcodes', [ $this, 'shortcodes_allowed' ] );
		$this->assertEquals( '&#91;anspress&#93;', \AnsPress\Form\Validate::sanitize_description( '[anspress]' ) );

		// Test on removing multiple lines.
		$str = 'AnsPress Question Answer


		The above lines should be replaced with single line.
		';
		$ex_str = 'AnsPress Question Answer

		The above lines should be replaced with single line.
		';
		$this->assertEquals( $ex_str, \AnsPress\Form\Validate::sanitize_description( $str ) );
		$str = 'AnsPress Question Answer


		The above lines should be replaced with single line.
		<!--more-->
		';
		$ex_str = 'AnsPress Question Answer

		The above lines should be replaced with single line.

		';
		$this->assertEquals( $ex_str, \AnsPress\Form\Validate::sanitize_description( $str ) );
		$str = 'AnsPress Question Answer





		The above lines should be replaced with single line.
		';
		$ex_str = 'AnsPress Question Answer

		The above lines should be replaced with single line.
		';
		$this->assertEquals( $ex_str, \AnsPress\Form\Validate::sanitize_description( $str ) );

		// Test on removing space declaration.
		$str = 'AnsPress Question Answer&nbsp;';
		$ex_str = 'AnsPress Question Answer
';
		$this->assertEquals( $ex_str, \AnsPress\Form\Validate::sanitize_description( $str ) );
		$str = 'AnsPress Question Answer&nbsp;&nbsp;&nbsp;';
		$ex_str = 'AnsPress Question Answer


';
		$this->assertEquals( $ex_str, \AnsPress\Form\Validate::sanitize_description( $str ) );

		// Test on pre code.
		$this->assertEquals(
			'AnsPress Question Answer<pre>This is a question answer plugin</pre>',
			\AnsPress\Form\Validate::sanitize_description( 'AnsPress Question Answer<pre>This is a question answer plugin</pre>' )
		);
		$this->assertEquals(
			'AnsPress Question Answer<pre>&lt;?php echo &quot;This is a question answer plugin&quot;; ?&gt;</pre>',
			\AnsPress\Form\Validate::sanitize_description( 'AnsPress Question Answer<pre aplang="php"><?php echo "This is a question answer plugin"; ?></pre>' )
		);
		$this->assertEquals(
			'<pre>&lt;h1&gt;AnsPress Question Answer&lt;/h1&gt;</pre>',
			\AnsPress\Form\Validate::sanitize_description( '<pre aplang="xhtml"><h1>AnsPress Question Answer</h1></pre>' )
		);

		// Test on code.
		$this->assertEquals(
			'AnsPress Question Answer<code>This is a question answer plugin</code>',
			\AnsPress\Form\Validate::sanitize_description( 'AnsPress Question Answer<code>This is a question answer plugin</code>' )
		);
		$this->assertEquals(
			'AnsPress Question Answer<code>&lt;?php echo &quot;This is a question answer plugin&quot;; ?&gt;</code>',
			\AnsPress\Form\Validate::sanitize_description( 'AnsPress Question Answer<code><?php echo "This is a question answer plugin"; ?></code>' )
		);
		$this->assertEquals(
			'<code>&lt;h1&gt;AnsPress Question Answer&lt;/h1&gt;</code>',
			\AnsPress\Form\Validate::sanitize_description( '<code><h1>AnsPress Question Answer</h1></code>' )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_tags_field
	 */
	public function testValidateSanitizeTagsField() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_tags_field() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_tags_field( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_tags_field() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_tags_field( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_tags_field( [] ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_tags_field( false ) );

		// Test on passing values.
		// Test for single category.
		$cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'Question category',
			)
		);
		$this->assertEquals(
			[ $cid ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $cid ], [ 'value_field' => 'id', 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);
		$this->assertEquals(
			[ 'Question category' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $cid ], [ 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for single tag.
		$tid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'Question tag',
			)
		);
		$this->assertEquals(
			[ $tid ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $tid ], [ 'value_field' => 'id' ] )
		);
		$this->assertEquals(
			[ 'Question tag' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $tid ] )
		);

		// Test for multiple categories values passed.
		$cid1 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'Question category 1',
			)
		);
		$cid2 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'Question category 2',
			)
		);
		$cid3 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'Question category 3',
			)
		);
		$this->assertEquals(
			[ $cid1, $cid2, $cid3 ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $cid1, $cid2, $cid3 ], [ 'value_field' => 'id', 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);
		$this->assertEquals(
			[ 'Question category 1', 'Question category 2', 'Question category 3' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $cid1, $cid2, $cid3 ], [ 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for multiple tags values passed.
		$tid1 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'Question tag 1',
			)
		);
		$tid2 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'Question tag 2',
			)
		);
		$tid3 = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'Question tag 3',
			)
		);
		$this->assertEquals(
			[ $tid1, $tid2, $tid3 ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $tid1, $tid2, $tid3 ], [ 'value_field' => 'id' ] )
		);
		$this->assertEquals(
			[ 'Question tag 1', 'Question tag 2', 'Question tag 3' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $tid1, $tid2, $tid3 ] )
		);

		// Test for new question category creation with id passed.
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 101, 111 ], [ 'value_field' => 'id', 'js_options' => [ 'create' => true ], 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for new question tag creation with id passed.
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 101, 111 ], [ 'value_field' => 'id', 'js_options' => [ 'create' => true ] ] )
		);

		// Test for new question category creation with name passed.
		$this->assertEquals(
			[ 'New Question Category 1', 'New Question Category 2' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 'New Question Category 1', 'New Question Category 2' ], [ 'js_options' => [ 'create' => true ], 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for new question tag creation with name passed.
		$this->assertEquals(
			[ 'New Question Tag 1', 'New Question Tag 2' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 'New Question Tag 1', 'New Question Tag 2' ], [ 'js_options' => [ 'create' => true ] ] )
		);

		// Test for existing and new question category creation.
		$new_cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'New question category',
			)
		);
		$this->assertEquals(
			[ 'Question Category 2', 'New question category' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $new_cid, 'Question Category 2' ], [ 'js_options' => [ 'create' => true ], 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for existing and new question tag creation.
		$new_cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'New question tag',
			)
		);
		$this->assertEquals(
			[ 'Question Tag 2', 'New question tag' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $new_cid, 'Question Tag 2' ], [ 'js_options' => [ 'create' => true ] ] )
		);

		// Test for no new question category creation with name passed.
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 'New Question Category 1', 'New Question Category 2' ], [ 'js_options' => [ 'create' => false ], 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for no new question tag creation with name passed.
		$this->assertEquals(
			[],
			\AnsPress\Form\Validate::sanitize_tags_field( [ 'New Question Tag 1', 'New Question Tag 2' ], [ 'js_options' => [ 'create' => false ] ] )
		);

		// Test for existing and no new question category creation.
		$new_cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_category',
				'name'     => 'New new question category',
			)
		);
		$this->assertEquals(
			[ 'New new question category' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $new_cid, 'Question Category 2' ], [ 'js_options' => [ 'create' => false ], 'terms_args' => [ 'taxonomy' => 'question_category' ] ] )
		);

		// Test for existing and no new question tag creation.
		$new_cid = $this->factory()->term->create(
			array(
				'taxonomy' => 'question_tag',
				'name'     => 'New new question tag',
			)
		);
		$this->assertEquals(
			[ 'New new question tag' ],
			\AnsPress\Form\Validate::sanitize_tags_field( [ $new_cid, 'Question Tag 2' ], [ 'js_options' => [ 'create' => false ] ] )
		);
	}

	/**
	 * @covers \AnsPress\Form\Validate::sanitize_upload
	 */
	public function testValidateSanitizeUpload() {
		// Test on empty values.
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_upload() );
		$this->assertEquals( null, \AnsPress\Form\Validate::sanitize_upload( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_upload() );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_upload( '' ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_upload( [] ) );
		$this->assertNull( \AnsPress\Form\Validate::sanitize_upload( false ) );

		// Test on passing values.
		// Test for single file upload.
		$value = [
			'error' => 0,
			'name'  => 'anspress.txt',
		];
		$upload_options = [
			'multiple' => false,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $value, $upload_options );
		$this->assertEquals( 0, $result['error'] );
		$this->assertEquals( 'anspress.txt', $result['name'] );

		// Test for multiple file uploads.
		// For all uploads possible.
		$values = [
			[ 'error' => 0, 'name' => 'anspress.txt' ],
			[ 'error' => 0, 'name' => 'anspress.pdf' ],
			[ 'error' => 0, 'name' => 'anspress.docx' ],
			[ 'error' => 0, 'name' => 'anspress.png' ],
			[ 'error' => 0, 'name' => 'anspress.gif' ],
		];
		$upload_options = [
			'multiple' => true,
			'max_files' => 5,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $values, $upload_options );
		$this->assertCount( 5, $result );
		$this->assertEquals( 0, $result[ 0 ]['error'] );
		$this->assertEquals( 'anspress.txt', $result[ 0 ]['name'] );
		$this->assertEquals( 0, $result[ 1 ]['error'] );
		$this->assertEquals( 'anspress.pdf', $result[ 1 ]['name'] );
		$this->assertEquals( 0, $result[ 2 ]['error'] );
		$this->assertEquals( 'anspress.docx', $result[ 2 ]['name'] );
		$this->assertEquals( 0, $result[ 3 ]['error'] );
		$this->assertEquals( 'anspress.png', $result[ 3 ]['name'] );
		$this->assertEquals( 0, $result[ 4 ]['error'] );
		$this->assertEquals( 'anspress.gif', $result[ 4 ]['name'] );

		// For less uploads possible.
		$values = [
			[ 'error' => 0, 'name' => 'anspress.txt' ],
			[ 'error' => 0, 'name' => 'anspress.pdf' ],
			[ 'error' => 0, 'name' => 'anspress.docx' ],
			[ 'error' => 0, 'name' => 'anspress.png' ],
			[ 'error' => 0, 'name' => 'anspress.gif' ],
		];
		$upload_options = [
			'multiple' => true,
			'max_files' => 3,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $values, $upload_options );
		$this->assertCount( 3, $result );
		$this->assertEquals( 0, $result[ 0 ]['error'] );
		$this->assertEquals( 'anspress.txt', $result[ 0 ]['name'] );
		$this->assertEquals( 0, $result[ 1 ]['error'] );
		$this->assertEquals( 'anspress.pdf', $result[ 1 ]['name'] );
		$this->assertEquals( 0, $result[ 2 ]['error'] );
		$this->assertEquals( 'anspress.docx', $result[ 2 ]['name'] );

		// For more than provided uploads possible.
		$values = [
			[ 'error' => 0, 'name' => 'anspress.txt' ],
			[ 'error' => 0, 'name' => 'anspress.pdf' ],
			[ 'error' => 0, 'name' => 'anspress.docx' ],
			[ 'error' => 0, 'name' => 'anspress.png' ],
			[ 'error' => 0, 'name' => 'anspress.gif' ],
			[ 'error' => 0, 'name' => 'anspress.svg' ],
			[ 'error' => 0, 'name' => 'anspress.pptx' ],
		];
		$upload_options = [
			'multiple' => true,
			'max_files' => 10,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $values, $upload_options );
		$this->assertCount( 7, $result );
		$this->assertEquals( 0, $result[ 0 ]['error'] );
		$this->assertEquals( 'anspress.txt', $result[ 0 ]['name'] );
		$this->assertEquals( 0, $result[ 1 ]['error'] );
		$this->assertEquals( 'anspress.pdf', $result[ 1 ]['name'] );
		$this->assertEquals( 0, $result[ 2 ]['error'] );
		$this->assertEquals( 'anspress.docx', $result[ 2 ]['name'] );
		$this->assertEquals( 0, $result[ 3 ]['error'] );
		$this->assertEquals( 'anspress.png', $result[ 3 ]['name'] );
		$this->assertEquals( 0, $result[ 4 ]['error'] );
		$this->assertEquals( 'anspress.gif', $result[ 4 ]['name'] );
		$this->assertEquals( 0, $result[ 5 ]['error'] );
		$this->assertEquals( 'anspress.svg', $result[ 5 ]['name'] );
		$this->assertEquals( 0, $result[ 6 ]['error'] );
		$this->assertEquals( 'anspress.pptx', $result[ 6 ]['name'] );

		// Test for no upload.
		$value = [];
		$upload_options = [];
		$result = \AnsPress\Form\Validate::sanitize_upload( $value, $upload_options );
		$this->assertEquals( '', $result );
		$value = [];
		$upload_options = [
			'multiple' => true,
			'max_files' => 10,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $value, $upload_options );
		$this->assertEquals( '', $result );
		$value = [
			'error' => 0,
			'name'  => 'anspress.txt'
		];
		$upload_options = [];
		$result = \AnsPress\Form\Validate::sanitize_upload( $value, $upload_options );
		$this->assertEquals( '', $result );
		$values = [
			[ 'error' => 0, 'name' => 'anspress.txt' ],
			[ 'error' => 0, 'name' => 'anspress.pdf' ],
			[ 'error' => 0, 'name' => 'anspress.docx' ],
			[ 'error' => 0, 'name' => 'anspress.png' ],
			[ 'error' => 0, 'name' => 'anspress.gif' ],
		];
		$upload_options = [
			'multiple' => false,
			'max_files' => 5,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $values, $upload_options );
		$this->assertEquals( '', $result );
		$value = [
			'error' => 0,
			'name'  => 'anspress.txt'
		];
		$upload_options = [
			'multiple' => true,
			'max_files' => 5,
		];
		$result = \AnsPress\Form\Validate::sanitize_upload( $value, $upload_options );
		$this->assertEquals( '', $result );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_required
	 */
	public function testValidateRequired() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'required',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'required' =>  'Simple text field is required.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Other text',
			'validate' => 'required',
			'value'    => 'AnsPress Question Answer Plugin',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertEmpty( $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'required',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'required' =>  'Question Label field is required.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'required',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'required' =>  'Answer Label field is required.' ], $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'required',
			'value'    => 'AnsPress Question Answer Plugin',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'required,not_zero',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_required( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'required' =>  'Description Label field is required.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_not_zero
	 */
	public function testValidateNotZero() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'not_zero',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertEmpty( $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Other text',
			'validate' => 'not_zero',
			'value'    => '1',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Other text',
			'validate' => 'not_zero',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-zero' =>  'Other text field is required.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'not_zero',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertEmpty( $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'not_zero',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-zero' =>  'Answer Label field is required.' ], $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'not_zero',
			'value'    => '1',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'required,not_zero',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'required,not_zero',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_not_zero( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-zero' =>  'Label field is required.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_is_email
	 */
	public function testValidateIsEmail() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_email',
			'value'    => 'invalid_email',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-email' =>  'Value provided in field Simple text is not a valid email.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Other text',
			'validate' => 'is_email',
			'value'    => 'user1@example.com',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Other text',
			'validate' => 'is_email',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertEmpty( $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'is_email',
			'value'    => 'invalid_email',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-email' =>  'Value provided in field Question Label is not a valid email.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'is_email',
			'value'    => 'user1@example.com',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'is_email',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'required,is_email',
			'value'    => 'user1 @example.com',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-email' =>  'Value provided in field Description Label is not a valid email.' ], $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'required,is_email',
			'value'    => '',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_email( $field );
		$this->assertEmpty( $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_is_url
	 */
	public function testValidateIsURL() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'subtype'  => 'url',
			'validate' => 'is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'invalid_url',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-url' => 'Value provided in field Simple text is not a valid URL.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'subtype'  => 'url',
			'validate' => 'is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'https://example.com/',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'subtype'  => 'url',
			'validate' => 'is_url,required,is_email',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => '/example.com/',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-url' => 'Value provided in field Simple text is not a valid URL.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'subtype'  => 'url',
			'validate' => 'is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'invalid_url',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-url' => 'Value provided in field Question Label is not a valid URL.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'subtype'  => 'url',
			'validate' => 'is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'https://example.com/',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'subtype'  => 'url',
			'validate' => 'is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => '',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'subtype'  => 'url',
			'validate' => 'required,is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'https://example.com/sample-page/#section',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'subtype'  => 'url',
			'validate' => 'required,is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => '/example/',
			]
		];
		\AnsPress\Form\Validate::validate_is_url( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-url' => 'Value provided in field Label is not a valid URL.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_is_numeric
	 */
	public function testValidateIsNumeric() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'invalid_number',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-numeric' =>  'Value provided in field Simple text is not numeric.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => '123',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_numeric,required,is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => '123.45',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertEmpty( $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'invalid_number',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-numeric' =>  'Value provided in field Question Label is not numeric.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => '123',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => '',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'required,is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => '123.45',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'required,is_numeric',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'invalid_number',
			]
		];
		\AnsPress\Form\Validate::validate_is_numeric( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-numeric' =>  'Value provided in field Label is not numeric.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_min_string_length
	 */
	public function testValidateMinStringLength() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'min_string_length',
			'min_length' => '10',
			'value'      => '123',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'min-string-length' => 'Value provided in field Simple text must be at least 10 characters long.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'min_string_length',
			'min_length' => '5',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'min_string_length,is_url',
			'min_length' => '100',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'min-string-length' => 'Value provided in field Simple text must be at least 100 characters long.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Question Label',
			'validate'   => 'min_string_length',
			'min_length' => '24',
			'value'      => '123',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'min-string-length' => 'Value provided in field Question Label must be at least 24 characters long.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Answer Label',
			'validate'   => 'min_string_length',
			'min_length' => '24',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Comment Label',
			'validate'   => 'min_string_length',
			'min_length' => '24',
			'value'      => '',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Description Label',
			'validate'   => 'min_string_length,required,is_url',
			'min_length' => '24',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Label',
			'validate'   => 'min_string_length,required,is_url',
			'min_length' => '24',
			'value'      => '123',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_min_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'min-string-length' => 'Value provided in field Label must be at least 24 characters long.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_max_string_length
	 */
	public function testValidateMaxStringLength() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'max_string_length',
			'max_length' => '24',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'max-string-length' => 'Value provided in field Simple text must not exceeds 24 characters.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'max_string_length',
			'max_length' => '100',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'max_string_length',
			'max_length' => '100',
			'value'      => '',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'      => 'Simple text',
			'validate'   => 'max_string_length,is_url',
			'max_length' => '36',
			'value'      => 'https://example.com/sample-page/#section',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'max-string-length' => 'Value provided in field Simple text must not exceeds 36 characters.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Question Label',
			'validate'   => 'max_string_length',
			'max_length' => '24',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'max-string-length' => 'Value provided in field Question Label must not exceeds 24 characters.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Answer Label',
			'validate'   => 'max_string_length',
			'max_length' => '24',
			'value'      => '123',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Comment Label',
			'validate'   => 'max_string_length',
			'max_length' => '24',
			'value'      => '',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Description Label',
			'validate'   => 'max_string_length,required,is_url',
			'max_length' => '100',
			'value'      => 'AnsPress Question Answer Plugn',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'      => 'Label',
			'validate'   => 'max_string_length,required,is_url',
			'max_length' => '24',
			'value'      => 'https://example.com/sample-page/#section',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_max_string_length( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'max-string-length' => 'Value provided in field Label must not exceeds 24 characters.' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_is_checked
	 */
	public function testValidateIsChecked() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_checked',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-checked' =>  'You are required to check Simple text field' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_checked',
			'value'    => '1',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_checked,is_url',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-checked' =>  'You are required to check Simple text field' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'is_checked',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-checked' =>  'You are required to check Question Label field' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'is_checked',
			'value'    => '1',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'is_checked',
			'value'    => '',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-checked' =>  'You are required to check Comment Label field' ], $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'is_checked,required,is_url',
			'value'    => '1',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'is_checked,required,is_url',
			'value'    => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_checked( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-checked' =>  'You are required to check Label field' ], $field->errors );
	}

	/**
	 * @covers \AnsPress\Form\Validate::validate_is_array
	 */
	public function testValidateIsArray() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_array',
			'value'    => 'invalid_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-array' =>  'Value provided in field Simple text is not an array.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_array',
			'value'    => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_array,required,is_url',
			'value'    => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'is_array,required,is_url',
			'value'    => [ 'item1' => 'Item 1', 'item2' => 'Item 2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'is_array',
			'value'    => 'invalid_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-array' =>  'Value provided in field Question Label is not an array.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'is_array',
			'value'    => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'is_array',
			'value'    => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'is_array,required,is_url',
			'value'    => [ 'item1' => 'Item 1', 'item2' => 'Item 2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'is_array,required,is_url',
			'value'    => 'invalid_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_is_array( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'is-array' =>  'Value provided in field Label is not an array.' ], $field->errors );
	}

	/**
	 * @covers AnsPress\Form\Validate::validate_array_min
	 */
	public function testValidateArrayMin() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_min' => '5',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 5 values are required in field Simple text.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_min' => '2',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_min' => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_min' => '2',
			'value'     => 'invalid_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 2 values are required in field Simple text.' ], $field->errors );

		// Test 5.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_min' => '3',
			'value'     => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 3 values are required in field Simple text.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Question Label',
			'array_min' => '5',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 5 values are required in field Question Label.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Answer Label',
			'array_min' => '2',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Comment Label',
			'array_min' => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Description Label',
			'array_min' => '2',
			'value'     => 'invalid_array',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 2 values are required in field Description Label.' ], $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Label',
			'array_min' => '3',
			'value'     => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_min( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-min' => 'Minimum 3 values are required in field Label.' ], $field->errors );
	}

	/**
	 * @covers AnsPress\Form\Validate::validate_array_max
	 */
	public function testValidateArrayMax() {
		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_max' => '1',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-max' => 'Maximum values allowed in field Simple text is 1.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_max' => '2',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_max' => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_max' => '2',
			'value'     => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'     => 'Simple text',
			'array_max' => '0',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-max' => 'Maximum values allowed in field Simple text is 0.' ], $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Question Label',
			'array_max' => '1',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-max' => 'Maximum values allowed in field Question Label is 1.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Answer Label',
			'array_max' => '2',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Comment Label',
			'array_max' => '0',
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Description Label',
			'array_max' => '2',
			'value'     => [],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertEmpty( $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'     => 'Label',
			'array_max' => '0',
			'value'     => [ 'item1', 'item2' ],
		] );
		$this->assertEmpty( $field->errors );
		\AnsPress\Form\Validate::validate_array_max( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'array-max' => 'Maximum values allowed in field Label is 0.' ], $field->errors );
	}

	public function APGetThemeLocation() {
		return __DIR__ . '/badwords.txt';
	}

	/**
	 * @covers AnsPress\Form\Validate::get_bad_words
	 */
	public function testGetBadWords() {
		// Test 1.
		$result = \AnsPress\Form\Validate::get_bad_words();
		$this->assertEmpty( $result );

		// Test 2.
		$bad_words_content = "badword1\nbadword2\nbadword3";
		$bad_words_file = __DIR__ . '/badwords.txt';
		file_put_contents( $bad_words_file, $bad_words_content );
		add_filter( 'ap_get_theme_location', [ $this, 'APGetThemeLocation' ] );
		$result = \AnsPress\Form\Validate::get_bad_words();
		$this->assertEquals( [ 'badword1', 'badword2', 'badword3' ], $result );
		unlink( $bad_words_file );
		remove_filter( 'ap_get_theme_location', [ $this, 'APGetThemeLocation' ] );

		// Test 3.
		ap_opt( 'bad_words', 'badword1,badword2,badword3' );
		$result = \AnsPress\Form\Validate::get_bad_words();
		$this->assertEquals( [ 'badword1', 'badword2', 'badword3' ], $result );
		ap_opt( 'bad_words', '' );
	}

	/**
	 * @covers AnsPress\Form\Validate::validate_badwords
	 */
	public function testValidateBadwords() {
		// Add some badwords first for testing.
		ap_opt( 'bad_words', 'badword1,badword2,badword3' );

		// Test with different forms.
		// Test 1.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'badword1,badword2,okword'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'bad-words' => 'Found bad words in field Simple text. Remove them and try again.' ], $field->errors );

		// Test 2.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'okword1,okword2'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => ''
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'badwords,is_email',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'badword1,badword2,badword3'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'bad-words' => 'Found bad words in field Simple text. Remove them and try again.' ], $field->errors );

		// Test 5.
		anspress()->forms['Sample Form'] = new \AnsPress\Form( 'Sample Form', [] );
		$field = new \AnsPress\Form\Field( 'Sample Form', 'sample-field', [
			'label'    => 'Simple text',
			'validate' => 'badwords,is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Sample Form' => [
				'sample-field' => 'badword'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Test with same form multiple times.
		anspress()->forms['Test Form'] = new \AnsPress\Form( 'Test Form', [] );

		// Test 1.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Question Label',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'badword1,badword2,okword'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'bad-words' => 'Found bad words in field Question Label. Remove them and try again.' ], $field->errors );

		// Test 2.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Answer Label',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'okword1,okword2'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Test 3.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Comment Label',
			'validate' => 'badwords',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => ''
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Test 4.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Description Label',
			'validate' => 'badwords,is_email',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'badword1,badword2,badword3'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertNotEmpty( $field->errors );
		$this->assertEquals( [ 'bad-words' => 'Found bad words in field Description Label. Remove them and try again.' ], $field->errors );

		// Test 5.
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [
			'label'    => 'Label',
			'validate' => 'badwords,is_url',
		] );
		$this->assertEmpty( $field->errors );
		$_REQUEST = [
			'Test Form' => [
				'test-field' => 'badword'
			]
		];
		\AnsPress\Form\Validate::validate_badwords( $field );
		$this->assertEmpty( $field->errors );

		// Remove added badwords.
		ap_opt( 'bad_words', '' );
	}

	/**
	 * @covers AnsPress\Form\Validate::code_content
	 */
	public function testCodeContentWithContents() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '<code>echo "Hello World!";</code>', 'echo "Hello World!";' ];
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'code_content' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '<code>' . esc_html( 'echo "Hello World!";' ) . '</code>', $result );
	}

	/**
	 * @covers AnsPress\Form\Validate::code_content
	 */
	public function testCodeContentWithoutContents() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '<code></code>', '' ];
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'code_content' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '<code></code>', $result );
	}

	/**
	 * @covers AnsPress\Form\Validate::pre_content
	 */
	public function testPreContentWithContents() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '<pre aplang="php">echo "Hello World!";</pre>', 'php', 'echo "Hello World!";' ];
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'pre_content' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '<pre>' . esc_html( 'echo "Hello World!";' ) . '</pre>', $result );
	}

	/**
	 * @covers AnsPress\Form\Validate::pre_content
	 */
	public function testPreContentWithoutContents() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '<pre aplang="php"></pre>', 'php', '' ];
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'pre_content' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '<pre></pre>', $result );
	}

	public static function WhitelistShortcodes() {
		return [ 'shortcode' ];
	}

	/**
	 * @covers AnsPress\Form\Validate::whitelist_shortcodes
	 */
	public function testWhitelistShortcodesForAllowedShortcode() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '[shortcode]', '', 'shortcode' ];
		add_filter( 'ap_allowed_shortcodes', [ $this, 'WhitelistShortcodes' ] );
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'whitelist_shortcodes' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '[shortcode]', $result );
		remove_filter( 'ap_allowed_shortcodes', [ $this, 'WhitelistShortcodes' ] );
	}

	/**
	 * @covers AnsPress\Form\Validate::whitelist_shortcodes
	 */
	public function testWhitelistShortcodesForNotAllowedShortcode() {
		$field = new \AnsPress\Form\Field( 'Test Form', 'test-field', [] );
		$arr = [ '[test_shortcode]', '', 'test_shortcode' ];
		$method = new \ReflectionMethod( '\AnsPress\Form\Validate', 'whitelist_shortcodes' );
		$method->setAccessible( true );
		$result = $method->invokeArgs( $field, [ $arr ] );
		$this->assertEquals( '&#91;test_shortcode&#93;', $result );
	}
}
