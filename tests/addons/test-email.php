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
}
