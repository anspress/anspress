<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonEmail extends TestCase {

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
}
