<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFormHooks extends TestCase {

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
}
