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
}
