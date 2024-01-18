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
}
