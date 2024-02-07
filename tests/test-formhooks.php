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

	/**
	 * @covers AP_Form_Hooks::answer_form
	 */
	public function testAnswerForm() {
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertIsArray( $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertArrayHasKey( 'fields', $form );
		$this->assertArrayNotHasKey( 'editing', $form['fields'] );
		$this->assertArrayNotHasKey( 'editing_id', $form['fields'] );

		// Test on submit label.
		$this->assertEquals( 'Post Answer', $form['submit_label'] );

		// Test on fields.
		// For post_content.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'post_content', $form['fields'] );
		$expected_post_content = [
			'type'        => 'editor',
			'label'       => 'Description',
			'min_length'  => ap_opt( 'minimum_ans_length' ),
			'validate'    => 'required,min_string_length,badwords',
			'editor_args' => array(
				'quicktags' => ap_opt( 'answer_text_editor' ) ? true : false,
			),
		];
		$this->assertEquals( $expected_post_content, $form['fields']['post_content'] );

		// Test 2.
		ap_opt( 'minimum_ans_length', 10 );
		ap_opt( 'answer_text_editor', true );
		$form = \AP_Form_Hooks::answer_form();
		$expected_post_content = [
			'type'        => 'editor',
			'label'       => 'Description',
			'min_length'  => 10,
			'validate'    => 'required,min_string_length,badwords',
			'editor_args' => array(
				'quicktags' => true,
			),
		];
		$this->assertEquals( $expected_post_content, $form['fields']['post_content'] );
		ap_opt( 'minimum_ans_length', 5 );
		ap_opt( 'answer_text_editor', false );

		// For is_private.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'is_private', $form['fields'] );
		$expected_is_private = [
			'type'  => 'checkbox',
			'label' => 'Is private?',
			'desc'  => 'Only visible to admin and moderator.',
		];
		$this->assertEquals( $expected_is_private, $form['fields']['is_private'] );

		// Test 2.
		ap_opt( 'allow_private_posts', false );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'is_private', $form['fields'] );
		ap_opt( 'allow_private_posts', true );

		// For anonymous_name.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'anonymous_name', $form['fields'] );
		$expected_anonymous_name = [
			'label'      => 'Your Name',
			'attr'       => array(
				'placeholder' => 'Enter your name to display',
			),
			'order'      => 20,
			'validate'   => 'max_string_length,badwords',
			'max_length' => 20,
		];
		$this->assertEquals( $expected_anonymous_name, $form['fields']['anonymous_name'] );

		// Test 2.
		ap_opt( 'post_question_per', 'logged_in' );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'anonymous_name', $form['fields'] );
		ap_opt( 'post_question_per', 'anyone' );

		// Test 3.
		$this->setRole( 'subscriber' );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'anonymous_name', $form['fields'] );

		// For email.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 2.
		ap_opt( 'post_question_per', 'logged_in' );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		ap_opt( 'post_question_per', 'anyone' );

		// Test 3.
		$this->setRole( 'subscriber' );
		$_REQUEST['id'] = 1;
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		$this->logout();

		// Test 4.
		$_REQUEST['id'] = 1;
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 5.
		update_option( 'users_can_register', true );
		unset( $_REQUEST['id'] );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'email', $form['fields'] );
		$expected_email = [
			'label'      => 'Your Email',
			'attr'       => array(
				'placeholder' => 'Enter your email',
			),
			'desc'       => 'An account for you will be created and a confirmation link will be sent to you with the password.',
			'order'      => 20,
			'validate'   => 'is_email,required',
			'sanitize'   => 'email,required',
			'max_length' => 64,
		];
		$this->assertEquals( $expected_email, $form['fields']['email'] );

		// Test 6.
		ap_opt( 'create_account', false );
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 7.
		ap_opt( 'create_account', true );
		$_REQUEST['id'] = 1;
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		update_option( 'users_can_register', false );
		unset( $_REQUEST['id'] );

		// For post_id.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'post_id', $form['fields'] );
		$expected_post_id = [
			'type'     => 'input',
			'subtype'  => 'hidden',
			'value'    => '',
			'sanitize' => 'absint',
		];
		$this->assertEquals( $expected_post_id, $form['fields']['post_id'] );

		// Test 2.
		$_REQUEST['id'] = 1;
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'post_id', $form['fields'] );
		$expected_post_id = [
			'type'     => 'input',
			'subtype'  => 'hidden',
			'value'    => 1,
			'sanitize' => 'absint',
		];
		$this->assertEquals( $expected_post_id, $form['fields']['post_id'] );
		unset( $_REQUEST['id'] );

		// Test on editing.
		// Test 1.
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayNotHasKey( 'editing', $form );
		$this->assertArrayNotHasKey( 'editing_id', $form );
		$this->assertArrayHasKey( 'submit_label', $form );

		// Test 2.
		$_REQUEST['id'] = 1;
		$form = \AP_Form_Hooks::answer_form();
		$this->assertArrayHasKey( 'editing', $form );
		$this->assertArrayHasKey( 'editing_id', $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( true, $form['editing'] );
		$this->assertEquals( 1, $form['editing_id'] );
		$this->assertEquals( 'Update Answer', $form['submit_label'] );
		unset( $_REQUEST['id'] );
	}
}
