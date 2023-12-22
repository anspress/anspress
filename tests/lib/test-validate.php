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
}
