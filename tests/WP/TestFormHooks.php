<?php

namespace AnsPress\Tests\WP;

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
		$this->assertEquals( 'jpeg, jpg, png, and gif file types allowed.', $form['fields']['image']['desc'] );
		$this->assertEquals( 'upload', $form['fields']['image']['type'] );
		$this->assertEquals( [ 'AP_Form_Hooks', 'image_upload_save' ], $form['fields']['image']['save'] );
		$options_args = [
			'multiple'      => false,
			'max_files'     => 1,
			'allowed_mimes' => array(
				'jpeg|jpg' => 'image/jpeg',
				'png'      => 'image/png',
				'gif'      => 'image/gif',
			),
		];
		$this->assertEquals( $options_args, $form['fields']['image']['upload_options'] );
		foreach ( $options_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $form['fields']['image']['upload_options'] );
			$this->assertEquals( $value, $form['fields']['image']['upload_options'][ $key ] );
		}
		$this->assertEqualSets( [ 'jpeg|jpg', 'png', 'gif' ], array_keys( $form['fields']['image']['upload_options']['allowed_mimes'] ) );
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
		if ( ! \is_multisite() ) {
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
		}

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

	/**
	 * @covers AP_Form_Hooks::question_form
	 */
	public function testQuestionForm() {
		$question_id = $this->insert_question();

		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertIsArray( $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertArrayHasKey( 'fields', $form );
		$this->assertArrayNotHasKey( 'editing', $form['fields'] );
		$this->assertArrayNotHasKey( 'editing_id', $form['fields'] );
		$this->assertArrayNotHasKey( 'hidden_fields', $form['fields'] );

		// Test on submit label.
		$this->assertEquals( 'Submit Question', $form['submit_label'] );

		// Test on fields.
		// For post_title.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'post_title', $form['fields'] );
		$expected_post_title = [
			'type'       => 'input',
			'label'      => 'Title',
			'desc'       => 'Question in one sentence',
			'attr'       => array(
				'autocomplete'   => 'off',
				'placeholder'    => 'Question title',
				'data-action'    => 'suggest_similar_questions',
				'data-loadclass' => 'q-title',
			),
			'min_length' => ap_opt( 'minimum_qtitle_length' ),
			'max_length' => 100,
			'validate'   => 'required,min_string_length,max_string_length,badwords',
			'order'      => 2,
		];
		$this->assertEquals( $expected_post_title, $form['fields']['post_title'] );

		// Test 2.
		ap_opt( 'minimum_qtitle_length', 24 );
		$form = \AP_Form_Hooks::question_form();
		$expected_post_title = [
			'type'       => 'input',
			'label'      => 'Title',
			'desc'       => 'Question in one sentence',
			'attr'       => array(
				'autocomplete'   => 'off',
				'placeholder'    => 'Question title',
				'data-action'    => 'suggest_similar_questions',
				'data-loadclass' => 'q-title',
			),
			'min_length' => 24,
			'max_length' => 100,
			'validate'   => 'required,min_string_length,max_string_length,badwords',
			'order'      => 2,
		];
		$this->assertEquals( $expected_post_title, $form['fields']['post_title'] );
		ap_opt( 'minimum_qtitle_length', 10 );

		// For post_content.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'post_content', $form['fields'] );
		$expected_post_content = [
			'type'        => 'editor',
			'label'       => 'Description',
			'min_length'  => ap_opt( 'minimum_question_length' ),
			'validate'    => 'required,min_string_length,badwords',
			'editor_args' => array(
				'quicktags' => ap_opt( 'question_text_editor' ) ? true : false,
			),
		];
		$this->assertEquals( $expected_post_content, $form['fields']['post_content'] );

		// Test 2.
		ap_opt( 'minimum_question_length', 24 );
		ap_opt( 'question_text_editor', true );
		$form = \AP_Form_Hooks::question_form();
		$expected_post_content = [
			'type'        => 'editor',
			'label'       => 'Description',
			'min_length'  => 24,
			'validate'    => 'required,min_string_length,badwords',
			'editor_args' => array(
				'quicktags' => true,
			),
		];
		$this->assertEquals( $expected_post_content, $form['fields']['post_content'] );
		ap_opt( 'minimum_question_length', 10 );
		ap_opt( 'question_text_editor', false );

		// For is_private.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'is_private', $form['fields'] );
		$expected_is_private = [
			'type'  => 'checkbox',
			'label' => 'Is private?',
			'desc'  => 'Only visible to admin and moderator.',
		];
		$this->assertEquals( $expected_is_private, $form['fields']['is_private'] );

		// Test 2.
		ap_opt( 'allow_private_posts', false );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'is_private', $form['fields'] );
		ap_opt( 'allow_private_posts', true );

		// For anonymous_name.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'anonymous_name', $form['fields'] );
		$expected_anonymous_name = [
			'label'      => 'Your Name',
			'attr'       => array(
				'placeholder' => 'Enter your name to display',
			),
			'order'      => 20,
			'validate'   => 'max_string_length,badwords',
			'max_length' => 64,
		];
		$this->assertEquals( $expected_anonymous_name, $form['fields']['anonymous_name'] );

		// Test 2.
		ap_opt( 'post_question_per', 'logged_in' );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'anonymous_name', $form['fields'] );
		ap_opt( 'post_question_per', 'anyone' );

		// Test 3.
		$this->setRole( 'subscriber' );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'anonymous_name', $form['fields'] );

		// For email.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 2.
		ap_opt( 'post_question_per', 'logged_in' );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		ap_opt( 'post_question_per', 'anyone' );

		// Test 3.
		$this->setRole( 'subscriber' );
		$_REQUEST['id'] = $question_id;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		$this->logout();

		// Test 4.
		$_REQUEST['id'] = $question_id;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 5.
		update_option( 'users_can_register', true );
		unset( $_REQUEST['id'] );
		$form = \AP_Form_Hooks::question_form();
		if ( ! \is_multisite() ) {
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
		}

		// Test 6.
		ap_opt( 'create_account', false );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );

		// Test 7.
		ap_opt( 'create_account', true );
		$_REQUEST['id'] = $question_id;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'email', $form['fields'] );
		update_option( 'users_can_register', false );
		unset( $_REQUEST['id'] );

		// For post_id.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'post_id', $form['fields'] );
		$expected_post_id = [
			'type'     => 'input',
			'subtype'  => 'hidden',
			'value'    => '',
			'sanitize' => 'absint',
		];
		$this->assertEquals( $expected_post_id, $form['fields']['post_id'] );

		// Test 2.
		$_REQUEST['id'] = $question_id;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'post_id', $form['fields'] );
		$expected_post_id = [
			'type'     => 'input',
			'subtype'  => 'hidden',
			'value'    => $question_id,
			'sanitize' => 'absint',
		];
		$this->assertEquals( $expected_post_id, $form['fields']['post_id'] );
		unset( $_REQUEST['id'] );

		// Test on editing.
		// Test 1.
		$question = $this->factory()->post->create_and_get( [
			'post_title'   => 'Question Title',
			'post_content' => 'Question Content',
			'post_type'    => 'question',
		] );
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'editing', $form );
		$this->assertArrayNotHasKey( 'editing_id', $form );
		$this->assertArrayHasKey( 'submit_label', $form );

		// Test 2.
		$_REQUEST['id'] = $question->ID;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'editing', $form );
		$this->assertArrayHasKey( 'editing_id', $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( true, $form['editing'] );
		$this->assertEquals( $question->ID, $form['editing_id'] );
		$this->assertEquals( 'Update Question', $form['submit_label'] );
		$this->assertEquals( 'Question Title', $form['fields']['post_title']['value'] );
		$this->assertEquals( 'Question Content', $form['fields']['post_content']['value'] );
		$this->assertEquals( false, $form['fields']['is_private']['value'] );
		unset( $_REQUEST['id'] );

		// Test 3.
		$question = $this->factory()->post->create_and_get( [
			'post_title'   => 'Question Title',
			'post_content' => 'Question Content',
			'post_type'    => 'question',
			'post_status'  => 'private_post',
		] );
		$_REQUEST['id'] = $question->ID;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'editing', $form );
		$this->assertArrayHasKey( 'editing_id', $form );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( true, $form['editing'] );
		$this->assertEquals( $question->ID, $form['editing_id'] );
		$this->assertEquals( 'Update Question', $form['submit_label'] );
		$this->assertEquals( 'Question Title', $form['fields']['post_title']['value'] );
		$this->assertEquals( 'Question Content', $form['fields']['post_content']['value'] );
		$this->assertEquals( true, $form['fields']['is_private']['value'] );
		unset( $_REQUEST['id'] );

		// Test on hidden_fields.
		// Test 1.
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayNotHasKey( 'hidden_fields', $form );

		// Test 2.
		$parent_question = $this->factory()->post->create_and_get( [
			'post_title'   => 'Parent Question Title',
			'post_content' => 'Parent Question Content',
			'post_type'    => 'question',
		] );
		$child_question = $this->factory()->post->create_and_get( [
			'post_title'   => 'Child Question Title',
			'post_content' => 'Child Question Content',
			'post_type'    => 'question',
			'post_parent'  => $parent_question->ID,
		] );
		$nonce = wp_create_nonce( 'post_parent_' . $parent_question->ID );
		$_REQUEST['post_parent'] = $parent_question->ID;
		$_REQUEST['__nonce_pp'] = $nonce;
		$form = \AP_Form_Hooks::question_form();
		$this->assertArrayHasKey( 'hidden_fields', $form );
		$expected_hidden_fields = [
			[
				'name'  => 'post_parent',
				'value' => $parent_question->ID,
			],
			[
				'name'  => '__nonce_pp',
				'value' => $nonce
			],
		];
		$this->assertEquals( $expected_hidden_fields, $form['hidden_fields'] );
		unset( $_REQUEST['post_parent'] );
		unset( $_REQUEST['__nonce_pp'] );
	}
}
