<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonEmailHelper extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Anspress\Addons\Email\Helper' );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'emails' ) && $class->getProperty( 'emails' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'subject' ) && $class->getProperty( 'subject' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'body' ) && $class->getProperty( 'body' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'event' ) && $class->getProperty( 'event' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'template' ) && $class->getProperty( 'template' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'template_tags' ) && $class->getProperty( 'template_tags' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'email_headers' ) && $class->getProperty( 'email_headers' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'add_email' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'add_user' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'add_template_tag' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'add_template_tags' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'get_default_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'prepare_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'prepare_emails' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Email\Helper', 'send_emails' ) );
	}

	public function GetDefaultTemplate() {
		return '<html><body>This is the default email template for the test event.</body></html>';
	}

	/**
	 * @covers Anspress\Addons\Email\Helper::get_default_template
	 */
	public function testGetDefaultTemplate() {
		// Test 1.
		$instance = new \Anspress\Addons\Email\Helper( 'test_event' );
		$this->assertEquals( '', $instance->get_default_template() );

		// Test 2.
		$instance = new \Anspress\Addons\Email\Helper( 'test_event' );
		add_filter( 'ap_email_default_template_test_event', [ $this, 'GetDefaultTemplate' ] );
		$this->assertEquals( $this->GetDefaultTemplate(), $instance->get_default_template() );
		remove_filter( 'ap_email_default_template_test_event', [ $this, 'GetDefaultTemplate' ] );

		// Test 3.
		$instance = new \Anspress\Addons\Email\Helper( 'new_event' );
		$this->assertEquals( '', $instance->get_default_template() );

		// Test 4.
		$instance = new \Anspress\Addons\Email\Helper( 'new_event' );
		add_filter( 'ap_email_default_template_new_event', [ $this, 'GetDefaultTemplate' ] );
		$this->assertEquals( $this->GetDefaultTemplate(), $instance->get_default_template() );
		remove_filter( 'ap_email_default_template_new_event', [ $this, 'GetDefaultTemplate' ] );
	}
}
