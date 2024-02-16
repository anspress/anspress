<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCaptchaCaptcha extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress\Form\Field\Captcha' );
		$this->assertTrue( $class->hasProperty( 'type' ) && $class->getProperty( 'type' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'response' ) && $class->getProperty( 'response' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'prepare' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'sanitize' ) );
		$this->assertTrue( method_exists( 'AnsPress\Form\Field\Captcha', 'field_markup' ) );
	}

	/**
	 * @covers AnsPress\Form\Field\Captcha::field_markup
	 */
	public function testFieldMarkup() {
		// Set up the action hook callback
		$callback_triggered = false;
		add_action( 'ap_after_field_markup', function( $field ) use ( &$callback_triggered ) {
			$this->assertInstanceOf( 'AnsPress\Form\Field', $field );
			$this->assertInstanceOf( 'AnsPress\Form\Field\Captcha', $field );
			$callback_triggered = true;
		} );

		// Test begins.
		// Test 1.
		$field = new \AnsPress\Form\Field\Captcha( 'Sample Form', 'sample-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( '<div class="ap-notice red">Unable to render captcha. Please add reCpatcha keys in AnsPress options.</div>', $property->getValue( $field ) );
		$this->assertFalse( $callback_triggered );

		// Test 2.
		$callback_triggered = false;
		ap_opt( 'recaptcha_site_key', 'test-site-key' );
		$field = new \AnsPress\Form\Field\Captcha( 'Test Form', 'test-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringContainsString( '<div class="g-recaptcha load-recaptcha" id="TestForm-test-form" data-sitekey="test-site-key"></div>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&onload=apCpatchaLoaded&render=explicit"></script>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( '<div class="ap-notice red">Unable to render captcha. Please add reCpatcha keys in AnsPress options.</div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );

		// Test 3.
		$callback_triggered = false;
		ap_opt( 'recaptcha_site_key', 'test-recaptcha-site-key' );
		$field = new \AnsPress\Form\Field\Captcha( 'Test Form', 'test-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_id = 'test-captcha-id';
		$field->field_markup();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertStringContainsString( '<div class="g-recaptcha load-recaptcha" id="test-captcha-id" data-sitekey="test-recaptcha-site-key"></div>', $property->getValue( $field ) );
		$this->assertStringContainsString( '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&onload=apCpatchaLoaded&render=explicit"></script>', $property->getValue( $field ) );
		$this->assertStringNotContainsString( '<div class="ap-notice red">Unable to render captcha. Please add reCpatcha keys in AnsPress options.</div>', $property->getValue( $field ) );
		$this->assertTrue( $callback_triggered );

		// Test 4.
		$callback_triggered = false;
		ap_opt( 'recaptcha_site_key', '' );
		$field = new \AnsPress\Form\Field\Captcha( 'Test Form', 'test-form', [] );
		$reflection = new \ReflectionClass( $field );
		$property = $reflection->getProperty( 'html' );
		$property->setAccessible( true );
		$this->assertEmpty( $property->getValue( $field ) );
		$field->field_markup();
		$this->assertNotEmpty( $property->getValue( $field ) );
		$this->assertEquals( '<div class="ap-notice red">Unable to render captcha. Please add reCpatcha keys in AnsPress options.</div>', $property->getValue( $field ) );
		$this->assertFalse( $callback_triggered );
	}
}
