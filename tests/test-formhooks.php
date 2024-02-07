<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFormHooks extends TestCase {

	use Testcases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'question_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'answer_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'comment_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_question_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_answer_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'submit_comment_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'image_upload_save' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'image_upload_form' ) );
		$this->assertTrue( method_exists( 'AP_Form_Hooks', 'create_user' ) );
	}

	/**
	 * @covers AP_Form_Hooks::image_upload_form
	 */
	public function testImageUploadForm() {
		$form_hooks = new \AP_Form_Hooks();
		$form = $form_hooks->image_upload_form();

		// Test begins.
		$this->assertIsArray( $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( 'Upload & insert', $form['submit_label'] );
		$this->assertArrayHasKey( 'fields', $form );

		// Test for image field.
		$this->assertArrayHasKey( 'image', $form['fields'] );
		$this->assertEquals( 'Image', $form['fields']['image']['label'] );
		$this->assertEquals( 'Select image(s) to upload. Only .jpg, .png and .gif files allowed.', $form['fields']['image']['desc'] );
		$this->assertEquals( 'upload', $form['fields']['image']['type'] );
		$this->assertEquals( [ 'AP_Form_Hooks', 'image_upload_save' ], $form['fields']['image']['save'] );
		$options_args = [
			'multiple'      => false,
			'max_files'     => 1,
			'allowed_mimes' => array(
				'jpg|jpeg' => 'image/jpeg',
				'gif'      => 'image/gif',
				'png'      => 'image/png',
			),
		];
		$this->assertEquals( $options_args, $form['fields']['image']['upload_options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['image']['upload_options'] );
			$this->assertEquals( $value, $form['fields']['image']['upload_options'][ $key ] );
		}
		$this->assertEqualSets( [ 'jpg|jpeg', 'gif', 'png' ], array_keys( $form['fields']['image']['upload_options']['allowed_mimes'] ) );
		$this->assertEqualSets( [ 'image/jpeg', 'image/gif', 'image/png' ], array_values( $form['fields']['image']['upload_options']['allowed_mimes'] ) );
		$this->assertEquals( 'required', $form['fields']['image']['validate'] );
	}

	/**
	 * @covers AP_Form_Hooks::comment_form
	 */
	public function testCommentForm() {
		// Test for not logged in user.
		$form = \AP_Form_Hooks::comment_form();
		$this->assertIsArray( $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertArrayHasKey( 'fields', $form );

		// Test on submit label.
		$this->assertEquals( 'Submit Comment', $form['submit_label'] );

		// Test on fields.
		// For content.
		$this->assertArrayHasKey( 'content', $form['fields'] );
		$expected_content = [
			'type'        => 'textarea',
			'label'       => 'Comment',
			'min_length'  => 5,
			'validate'    => 'required,min_string_length,badwords',
			'attr'        => [
				'placeholder' => 'Write your comment here...',
				'rows'        => 5,
			],
			'editor_args' => [
				'quicktags'     => true,
				'textarea_rows' => 5,
			],
		];
		$this->assertEquals( $expected_content, $form['fields']['content'] );

		// For author.
		$this->assertArrayHasKey( 'author', $form['fields'] );
		$expected_author = [
			'label'      => 'Your Name',
			'attr'       => [
				'placeholder' => 'Enter your name to display.',
			],
			'validate'   => 'required,max_string_length,badwords',
			'max_length' => 64,
		];
		$this->assertEquals( $expected_author, $form['fields']['author'] );

		// For email.
		$this->assertArrayHasKey( 'email', $form['fields'] );
		$expected_email = [
			'label'      => 'Your Email',
			'attr'       => [
				'placeholder' => 'Enter your email to get follow up notifications.',
			],
			'subtype'    => 'email',
			'validate'   => 'required,is_email',
			'max_length' => 254,
		];
		$this->assertEquals( $expected_email, $form['fields']['email'] );

		// For url.
		$this->assertArrayHasKey( 'url', $form['fields'] );
		$expected_url = [
			'label'      => 'Your Website',
			'attr'       => [
				'placeholder' => 'Enter link to your website.',
			],
			'subtype'    => 'url',
			'validate'   => 'is_url',
			'max_length' => 254,
		];
		$this->assertEquals( $expected_url, $form['fields']['url'] );

		// Test for logged in user.
		$this->setRole( 'subscriber' );
		$form = \AP_Form_Hooks::comment_form();
		$this->assertIsArray( $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertArrayHasKey( 'fields', $form );

		// Test on submit label.
		$this->assertEquals( 'Submit Comment', $form['submit_label'] );

		// Test on fields.
		// For content.
		$this->assertArrayHasKey( 'content', $form['fields'] );
		$expected_content = [
			'type'        => 'textarea',
			'label'       => 'Comment',
			'min_length'  => 5,
			'validate'    => 'required,min_string_length,badwords',
			'attr'        => [
				'placeholder' => 'Write your comment here...',
				'rows'        => 5,
			],
			'editor_args' => [
				'quicktags'     => true,
				'textarea_rows' => 5,
			],
		];
		$this->assertEquals( $expected_content, $form['fields']['content'] );

		// For author.
		$this->assertArrayNotHasKey( 'author', $form['fields'] );

		// For email.
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// For url.
		$this->assertArrayNotHasKey( 'url', $form['fields'] );
	}
}
