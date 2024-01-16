<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCaptcha extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'recaptcha.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'recaptcha.php' );
	}

	/**
	 * @covers Anspress\Addons\Captcha::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Captcha' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'enqueue_scripts' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'ap_question_form_fields' ) );
	}

	/**
	 * @covers Anspress\Addons\Captcha::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Captcha::init();
		$this->assertInstanceOf( 'Anspress\Addons\Captcha', $instance1 );
		$instance2 = \Anspress\Addons\Captcha::init();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers Anspress\Addons\Captcha::enqueue_scripts
	 */
	public function testEnqueueScripts() {
		$instance = \Anspress\Addons\Captcha::init();

		// Call the method.
		$instance->enqueue_scripts();

		// Test if the script is enqueued.
		$this->assertTrue( wp_script_is( 'ap-recaptcha' ) );
	}

	/**
	 * @covers Anspress\Addons\Captcha::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Captcha::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the reCaptcha group is added to the settings page.
		$this->assertArrayHasKey( 'recaptcha', $groups );
		$this->assertEquals( 'reCaptcha', $groups['recaptcha']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are rerained to the settings page.
		$this->assertArrayHasKey( 'recaptcha', $groups );
		$this->assertEquals( 'reCaptcha', $groups['recaptcha']['label'] );
	}
}
