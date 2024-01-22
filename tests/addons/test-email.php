<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonEmail extends TestCase {

	use Testcases\Common;

	/**
	 * @covers Anspress\Addons\Email::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Email' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Addons\Email' );
		$this->assertTrue( $class->hasProperty( 'emails' ) && $class->getProperty( 'emails' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'subject' ) && $class->getProperty( 'subject' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'message' ) && $class->getProperty( 'message' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'default_recipients' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_default_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'load_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'register_option' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'register_email_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'get_admin_emails' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_after_new_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_after_new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'select_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_after_update_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_after_update_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_trash_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_trash_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_all_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'save_email_template_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'ap_email_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'get_default_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_new_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_select_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_edit_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_edit_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_trash_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'template_trash_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email', 'form_allowed_tags' ) );
	}

	/**
	 * @covers Anspress\Addons\Email::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Email::init();
		$this->assertInstanceOf( 'Anspress\Addons\Email', $instance1 );
		$instance2 = \Anspress\Addons\Email::init();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers Anspress\Addons\Email::ap_default_options
	 */
	public function testAPDefaultOptions() {
		$instance = \Anspress\Addons\Email::init();

		// Get all available options.
		$ap_options = ap_opt();

		// Call the method.
		$instance->ap_default_options();

		// Test begins.
		$expected_options = [
			'email_admin_emails'         => get_option( 'admin_email' ),
			'email_admin_new_question'   => true,
			'email_admin_new_answer'     => true,
			'email_admin_new_comment'    => true,
			'email_admin_edit_question'  => true,
			'email_admin_edit_answer'    => true,
			'email_admin_trash_question' => true,
			'email_admin_trash_answer'   => true,
			'email_user_new_question'    => true,
			'email_user_new_answer'      => true,
			'email_user_select_answer'   => true,
			'email_user_new_comment'     => true,
			'email_user_edit_question'   => true,
			'email_user_edit_answer'     => true,
			'trash_answer_email_subject' => 'An answer is trashed by {user}',
			'trash_answer_email_body'    => "Hello!\nAnswer on '{question_title}' is trashed by {user}.\n",
		];
		foreach ( $expected_options as $key => $value ) {
			$this->assertSame( $value, $ap_options[ $key ] );
		}
	}

	/**
	 * @covers Anspress\Addons\Email::load_options
	 */
	public function testLoadOptions() {
		$instance = \Anspress\Addons\Email::init();

		// Call the method.
		$groups = $instance->load_options( [] );

		// Test if the Email group is added to the settings page.
		$this->assertArrayHasKey( 'email', $groups );
		$this->assertEquals( 'Email', $groups['email']['label'] );
		$this->assertStringContainsString( 'Email templates can be customized here', $groups['email']['info'] );
		$this->assertStringContainsString( '<a href="' . admin_url( 'admin.php?page=anspress_options&active_tab=emails' ) . '">', $groups['email']['info'] );
		$this->assertStringContainsString( 'Customize email templates', $groups['email']['info'] );

		// Test by adding new group.
		$groups = $instance->load_options( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'email', $groups );
		$this->assertEquals( 'Email', $groups['email']['label'] );
		$this->assertStringContainsString( 'Email templates can be customized here', $groups['email']['info'] );
		$this->assertStringContainsString( '<a href="' . admin_url( 'admin.php?page=anspress_options&active_tab=emails' ) . '">', $groups['email']['info'] );
		$this->assertStringContainsString( 'Customize email templates', $groups['email']['info'] );
	}

	/**
	 * @covers Anspress\Addons\Email::register_option
	 */
	public function testRegisterOption() {
		$instance = \Anspress\Addons\Email::init();

		// Call the method.
		$form = $instance->register_option();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'sep1', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_emails', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_new_question', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_new_answer', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_new_comment', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_edit_question', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_edit_answer', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_trash_question', $form['fields'] );
		$this->assertArrayHasKey( 'email_admin_trash_answer', $form['fields'] );
		$this->assertArrayHasKey( 'sep2', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_new_question', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_new_answer', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_new_comment', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_edit_question', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_edit_answer', $form['fields'] );
		$this->assertArrayHasKey( 'email_user_select_answer', $form['fields'] );

		// Test for sep1 field.
		$this->assertArrayHasKey( 'html', $form['fields']['sep1'] );
		$this->assertStringContainsString( 'Admin Notifications', $form['fields']['sep1']['html'] );
		$this->assertStringContainsString( 'Select types of notification which will be sent to admin.', $form['fields']['sep1']['html'] );

		// Test for email_admin_emails field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_emails'] );
		$this->assertEquals( 'Admin email(s)', $form['fields']['email_admin_emails']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_emails'] );
		$this->assertEquals( 'Email where all admin notification will be sent. It can have multiple emails separated by comma.', $form['fields']['email_admin_emails']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_emails'] );
		$this->assertEquals( ap_opt( 'email_admin_emails' ), $form['fields']['email_admin_emails']['value'] );

		// Test for email_admin_new_question field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_new_question'] );
		$this->assertEquals( 'New question', $form['fields']['email_admin_new_question']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_new_question'] );
		$this->assertEquals( 'Send new question notification to admin.', $form['fields']['email_admin_new_question']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_new_question'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_new_question']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_new_question'] );
		$this->assertEquals( ap_opt( 'email_admin_new_question' ), $form['fields']['email_admin_new_question']['value'] );

		// Test for email_admin_new_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_new_answer'] );
		$this->assertEquals( 'New answer', $form['fields']['email_admin_new_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_new_answer'] );
		$this->assertEquals( 'Send new answer notification to admin.', $form['fields']['email_admin_new_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_new_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_new_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_new_answer'] );
		$this->assertEquals( ap_opt( 'email_admin_new_answer' ), $form['fields']['email_admin_new_answer']['value'] );

		// Test for email_admin_new_comment field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_new_comment'] );
		$this->assertEquals( 'New comment', $form['fields']['email_admin_new_comment']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_new_comment'] );
		$this->assertEquals( 'Send new comment notification to admin.', $form['fields']['email_admin_new_comment']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_new_comment'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_new_comment']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_new_comment'] );
		$this->assertEquals( ap_opt( 'email_admin_new_comment' ), $form['fields']['email_admin_new_comment']['value'] );

		// Test for email_admin_edit_question field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_edit_question'] );
		$this->assertEquals( 'Edit question', $form['fields']['email_admin_edit_question']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_edit_question'] );
		$this->assertEquals( 'Send notification to admin when question is edited.', $form['fields']['email_admin_edit_question']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_edit_question'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_edit_question']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_edit_question'] );
		$this->assertEquals( ap_opt( 'email_admin_edit_question' ), $form['fields']['email_admin_edit_question']['value'] );

		// Test for email_admin_edit_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_edit_answer'] );
		$this->assertEquals( 'Edit answer', $form['fields']['email_admin_edit_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_edit_answer'] );
		$this->assertEquals( 'Send email to admin when answer is edited.', $form['fields']['email_admin_edit_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_edit_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_edit_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_edit_answer'] );
		$this->assertEquals( ap_opt( 'email_admin_edit_answer' ), $form['fields']['email_admin_edit_answer']['value'] );

		// Test for email_admin_trash_question field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_trash_question'] );
		$this->assertEquals( 'Delete question', $form['fields']['email_admin_trash_question']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_trash_question'] );
		$this->assertEquals( 'Send email to admin when question is trashed.', $form['fields']['email_admin_trash_question']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_trash_question'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_trash_question']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_trash_question'] );
		$this->assertEquals( ap_opt( 'email_admin_trash_question' ), $form['fields']['email_admin_trash_question']['value'] );

		// Test for email_admin_trash_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_admin_trash_answer'] );
		$this->assertEquals( 'Delete answer', $form['fields']['email_admin_trash_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_admin_trash_answer'] );
		$this->assertEquals( 'Send email to admin when answer is trashed.', $form['fields']['email_admin_trash_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_admin_trash_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_admin_trash_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_admin_trash_answer'] );
		$this->assertEquals( ap_opt( 'email_admin_trash_answer' ), $form['fields']['email_admin_trash_answer']['value'] );

		// Test for sep2 field.
		$this->assertArrayHasKey( 'html', $form['fields']['sep2'] );
		$this->assertStringContainsString( 'User Notifications', $form['fields']['sep2']['html'] );
		$this->assertStringContainsString( 'Select the types of notification which will be sent to user.', $form['fields']['sep2']['html'] );

		// Test for email_user_new_question field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_new_question'] );
		$this->assertEquals( 'New question', $form['fields']['email_user_new_question']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_new_question'] );
		$this->assertEquals( 'Send new question notification to user?', $form['fields']['email_user_new_question']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_new_question'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_new_question']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_new_question'] );
		$this->assertEquals( ap_opt( 'email_user_new_question' ), $form['fields']['email_user_new_question']['value'] );

		// Test for email_user_new_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_new_answer'] );
		$this->assertEquals( 'New answer', $form['fields']['email_user_new_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_new_answer'] );
		$this->assertEquals( 'Send new answer notification to user?', $form['fields']['email_user_new_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_new_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_new_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_new_answer'] );
		$this->assertEquals( ap_opt( 'email_user_new_answer' ), $form['fields']['email_user_new_answer']['value'] );

		// Test for email_user_new_comment field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_new_comment'] );
		$this->assertEquals( 'New comment', $form['fields']['email_user_new_comment']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_new_comment'] );
		$this->assertEquals( 'Send new comment notification to user?', $form['fields']['email_user_new_comment']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_new_comment'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_new_comment']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_new_comment'] );
		$this->assertEquals( ap_opt( 'email_user_new_comment' ), $form['fields']['email_user_new_comment']['value'] );

		// Test for email_user_edit_question field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_edit_question'] );
		$this->assertEquals( 'Edit question', $form['fields']['email_user_edit_question']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_edit_question'] );
		$this->assertEquals( 'Send edit question notification to user?', $form['fields']['email_user_edit_question']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_edit_question'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_edit_question']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_edit_question'] );
		$this->assertEquals( ap_opt( 'email_user_edit_question' ), $form['fields']['email_user_edit_question']['value'] );

		// Test for email_user_edit_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_edit_answer'] );
		$this->assertEquals( 'Edit answer', $form['fields']['email_user_edit_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_edit_answer'] );
		$this->assertEquals( 'Send edit answer notification to user?', $form['fields']['email_user_edit_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_edit_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_edit_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_edit_answer'] );
		$this->assertEquals( ap_opt( 'email_user_edit_answer' ), $form['fields']['email_user_edit_answer']['value'] );

		// Test for email_user_select_answer field.
		$this->assertArrayHasKey( 'label', $form['fields']['email_user_select_answer'] );
		$this->assertEquals( 'Answer selected', $form['fields']['email_user_select_answer']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['email_user_select_answer'] );
		$this->assertEquals( 'Send notification to user when their answer get selected?', $form['fields']['email_user_select_answer']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['email_user_select_answer'] );
		$this->assertEquals( 'checkbox', $form['fields']['email_user_select_answer']['type'] );
		$this->assertArrayHasKey( 'value', $form['fields']['email_user_select_answer'] );
		$this->assertEquals( ap_opt( 'email_user_select_answer' ), $form['fields']['email_user_select_answer']['value'] );
	}

	/**
	 * @covers Anspress\Addons\Email::register_email_template
	 */
	public function testRegisterEmailTemplate() {
		$instance = \Anspress\Addons\Email::init();

		// Call the method.
		$template = $instance->register_email_template();

		// Test begins.
		$expected_template = [
			'fields' => [
				'subject' => [
					'label' => 'Email subject',
				],
				'body'    => [
					'label'       => 'Email body',
					'type'        => 'editor',
					'editor_args' => [
						'quicktags' => true,
						'tinymce'   => true,
					],
				],
				'tags'    => [
					'html' => '<label class="ap-form-label" for="form_email_template-allowed_tags">Allowed tags</label><div class="ap-email-allowed-tags">' . apply_filters( 'ap_email_form_allowed_tags', '' ) . '</div>',
				],
			],
		];
		$this->assertEquals( $expected_template, $template );
	}

	/**
	 * @covers Anspress\Addons\Email::ap_all_options
	 */
	public function testAPAllOptions() {
		$instance = \Anspress\Addons\Email::init();

		// Dummy options.
		$dummy_options = [
			'option1' => [
				'label'    => 'Option 1',
				'template' => 'option-1.php',
			],
			'option2' => [
				'label'    => 'Option 2',
				'template' => 'option-2.php',
			],
		];
		$this->assertEquals( 2, count( $dummy_options ) );

		// Call the method.
		$modified_options = $instance->ap_all_options( $dummy_options );

		// Test begins.
		$this->assertNotEmpty( $modified_options );
		$this->assertIsArray( $modified_options );
		$this->assertEquals( 3, count( $modified_options ) );

		// Test for Emails options.
		$emails_options = end( $modified_options );
		$this->assertArrayHasKey( 'emails', $modified_options );
		$this->assertArrayHasKey( 'label', $emails_options );
		$this->assertEquals( '📧 Email Templates', $emails_options['label'] );
		$this->assertArrayHasKey( 'template', $emails_options );
		$this->assertEquals( 'emails.php', $emails_options['template'] );
	}

	/**
	 * @covers Anspress\Addons\Email::form_allowed_tags
	 */
	public function testform_allowed_tags() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		// Test for new_question.
		$_REQUEST['template'] = 'new_question';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{asker}</pre><pre>{question_title}</pre><pre>{question_link}</pre><pre>{question_content}</pre><pre>{question_excerpt}</pre>', $result );

		// Test for edit_question.
		$_REQUEST['template'] = 'edit_question';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{asker}</pre><pre>{question_title}</pre><pre>{question_link}</pre><pre>{question_content}</pre><pre>{question_excerpt}</pre>', $result );

		// Test for new_answer.
		$_REQUEST['template'] = 'new_answer';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{answerer}</pre><pre>{question_title}</pre><pre>{answer_link}</pre><pre>{answer_content}</pre><pre>{answer_excerpt}</pre>', $result );

		// Test for edit_answer.
		$_REQUEST['template'] = 'edit_answer';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{answerer}</pre><pre>{question_title}</pre><pre>{answer_link}</pre><pre>{answer_content}</pre><pre>{answer_excerpt}</pre>', $result );

		// Test for select_answer.
		$_REQUEST['template'] = 'select_answer';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{selector}</pre><pre>{question_title}</pre><pre>{answer_link}</pre><pre>{answer_content}</pre><pre>{answer_excerpt}</pre>', $result );

		// Test for new_comment.
		$_REQUEST['template'] = 'new_comment';
		$result = $instance->form_allowed_tags();
		$this->assertEquals( '<pre>{site_name}</pre><pre>{site_url}</pre><pre>{site_description}</pre><pre>{commenter}</pre><pre>{question_title}</pre><pre>{comment_link}</pre><pre>{comment_content}</pre>', $result );
	}

	public function GetDefaultTemplate( $template ) {
		$template['subject'] = 'Some subject';
		$template['body']    = 'Some body';

		return $template;
	}

	public function GetDefaultTemplateAdditional( $template ) {
		$template['subject'] = 'Other subject';
		$template['body']    = 'Other body';
		$template['data']    = 'Other data';

		return $template;
	}

	/**
	 * @covers Anspress\Addons\Email::get_default_template
	 */
	public function testGetDefaultTemplate() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		// Basic test.
		$template = $instance->get_default_template( 'some_event' );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertEquals( '', $template['subject'] );
		$this->assertEquals( '', $template['body'] );

		// Test with filter.
		add_filter( 'ap_email_default_template_some_event', [ $this, 'GetDefaultTemplate' ] );
		$template = $instance->get_default_template( 'some_event' );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertEquals( 'Some subject', $template['subject'] );
		$this->assertEquals( 'Some body', $template['body'] );
		remove_filter( 'ap_email_default_template_some_event', [ $this, 'GetDefaultTemplate' ] );

		// Test with filter and additional datas.
		add_filter( 'ap_email_default_template_other_event', [ $this, 'GetDefaultTemplateAdditional' ] );
		$template = $instance->get_default_template( 'other_event' );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertArrayHasKey( 'data', $template );
		$this->assertEquals( 'Other subject', $template['subject'] );
		$this->assertEquals( 'Other body', $template['body'] );
		$this->assertEquals( 'Other data', $template['data'] );
		remove_filter( 'ap_email_default_template_other_event', [ $this, 'GetDefaultTemplateAdditional' ] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_new_question
	 */
	public function testTemplateNewQuestion() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_new_question( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{asker}', $template['subject'] );
		$this->assertEquals( '{asker} have posted a new question', $template['subject'] );
		$this->assertStringContainsString( '{asker}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{question_link}', $template['body'] );
		$this->assertStringContainsString( '{question_content}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A new question is posted by <b class="user-name">{asker}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{question_content}</div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A new question is posted by <b class="user-name">{asker}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1><div class="ap-email-content">{question_content}</div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_new_answer
	 */
	public function testTemplateNewAnswer() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_new_answer( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{answerer}', $template['subject'] );
		$this->assertEquals( 'New answer posted by {answerer}', $template['subject'] );
		$this->assertStringContainsString( '{answerer}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{answer_link}', $template['body'] );
		$this->assertStringContainsString( '{answer_excerpt}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A new answer is posted by <b class="user-name">{answerer}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{answer_excerpt} </div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A new answer is posted by <b class="user-name">{answerer}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1><div class="ap-email-content">{answer_excerpt} </div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_select_answer
	 */
	public function testTemplateSelectAnswer() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_select_answer( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertEquals( 'Your answer is selected as best!', $template['subject'] );
		$this->assertStringContainsString( '{selector}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{answer_link}', $template['body'] );
		$this->assertStringContainsString( '{answer_content}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">Your answer is selected as best by  <b class="user-name">{selector}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{answer_content}</div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">Your answer is selected as best by  <b class="user-name">{selector}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1><div class="ap-email-content">{answer_content}</div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_new_comment
	 */
	public function testTemplateNewComment() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_new_comment( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{commenter}', $template['subject'] );
		$this->assertEquals( 'New comment by {commenter}', $template['subject'] );
		$this->assertStringContainsString( '{commenter}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{comment_link}', $template['body'] );
		$this->assertStringContainsString( '{comment_content}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A new comment posted by <b class="user-name">{commenter}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{comment_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{comment_content}</div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A new comment posted by <b class="user-name">{commenter}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{comment_link}">{question_title}</a></h1><div class="ap-email-content">{comment_content}</div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_edit_question
	 */
	public function testTemplateEditQuestion() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_edit_question( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{editor}', $template['subject'] );
		$this->assertEquals( 'A question is edited by {editor}', $template['subject'] );
		$this->assertStringContainsString( '{editor}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{question_link}', $template['body'] );
		$this->assertStringContainsString( '{question_content}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A question is edited by <b class="user-name">{editor}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{question_content}</div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A question is edited by <b class="user-name">{editor}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1><div class="ap-email-content">{question_content}</div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_edit_answer
	 */
	public function testTemplateEditAnswer() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_edit_answer( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{editor}', $template['subject'] );
		$this->assertEquals( 'A answer is edited by {editor}', $template['subject'] );
		$this->assertStringContainsString( '{editor}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{answer_link}', $template['body'] );
		$this->assertStringContainsString( '{answer_content}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A answer is edited by <b class="user-name">{editor}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-content">{answer_content}</div></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A answer is edited by <b class="user-name">{editor}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1><div class="ap-email-content">{answer_content}</div></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_trash_question
	 */
	public function testTemplateTrashQuestion() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_trash_question( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{user}', $template['subject'] );
		$this->assertEquals( 'A question is trashed by {user}', $template['subject'] );
		$this->assertStringContainsString( '{user}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{question_link}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">A question is trashed by <b class="user-name">{user}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">A question is trashed by <b class="user-name">{user}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{question_link}">{question_title}</a></h1></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::template_trash_answer
	 */
	public function testTemplateTrashAnswer() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$sample_template = [
			'subject' => '',
			'body'    => '',
		];
		$template = $instance->template_trash_answer( $sample_template );
		$this->assertIsArray( $template );
		$this->assertArrayHasKey( 'subject', $template );
		$this->assertArrayHasKey( 'body', $template );
		$this->assertStringContainsString( '{user}', $template['subject'] );
		$this->assertEquals( 'An answer is trashed by {user}', $template['subject'] );
		$this->assertStringContainsString( '{user}', $template['body'] );
		$this->assertStringContainsString( '{question_title}', $template['body'] );
		$this->assertStringContainsString( '{answer_link}', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-event">An answer is trashed by <b class="user-name">{user}</b></div>', $template['body'] );
		$this->assertStringContainsString( '<div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1></div>', $template['body'] );
		$this->assertEquals( '<div class="ap-email-event">An answer is trashed by <b class="user-name">{user}</b></div><div class="ap-email-body"><h1 class="ap-email-title"><a href="{answer_link}">{question_title}</a></h1></div>', $template['body'] );
	}

	/**
	 * @covers Anspress\Addons\Email::default_recipients
	 */
	public function testDefaultRecipients() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		$original_recipients = [ 'admin@example.com', 'webmaster@example.com', 'info@example.com' ];

		// For non AnsPress comment type.
		$comment_id = $this->factory->comment->create();
		$recipients = $instance->default_recipients( $original_recipients, $comment_id );
		$this->assertNotEmpty( $recipients );
		$this->assertIsArray( $recipients );
		$this->assertEquals( $original_recipients, $recipients );

		// For anspress comment type.
		$comment_id = $this->factory->comment->create( [ 'comment_type' => 'anspress' ] );
		$recipients = $instance->default_recipients( $original_recipients, $comment_id );
		$this->assertEmpty( $recipients );
		$this->assertIsArray( $recipients );
	}

	/**
	 * @covers Anspress\Addons\Email::get_admin_emails
	 */
	public function testGetAdminEmails() {
		$instance = \Anspress\Addons\Email::init();

		// Test begins.
		// Test for invalid option id.
		$emails = $instance->get_admin_emails( 'sample_option' );
		$this->assertFalse( $emails );

		// Admin email and current user email does not match on test,
		// so we create a new user and set it as admin on the test.
		ap_opt( 'email_admin_emails', 'admin@example.com' );
		$user_id = $this->factory->user->create( [ 'role' => 'administrator', 'user_email' => 'admin@example.com' ] );
		wp_set_current_user( $user_id );
		$emails = $instance->get_admin_emails( 'email_admin_new_question' );
		$this->assertFalse( $emails );

		// Test for valid option id with email_admin_emails set as empty.
		ap_opt( 'email_admin_emails', null );
		$emails = $instance->get_admin_emails( 'email_admin_new_question' );
		$this->assertFalse( $emails );
		$this->logout();

		// Test for valid option id with email_admin_emails set some email ids.
		ap_opt( 'email_admin_emails', 'admin@example.com, webmaster@example.com, info@example.com' );
		$emails = $instance->get_admin_emails( 'email_admin_new_question' );
		$this->assertNotEmpty( $emails );
		$this->assertIsArray( $emails );
		$this->assertEquals( [ 'admin@example.com', 'webmaster@example.com', 'info@example.com' ], $emails );
	}
}
