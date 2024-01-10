<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonAvatar extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'avatar.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'avatar.php' );
	}

	/**
	 * @covers Anspress\Addons\Avatar::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Avatar' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar', 'option_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar', 'get_avatar' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Avatar', 'clear_avatar_cache' ) );
	}

	/**
	 * @covers Anspress\Addons\Avatar::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Avatar::init();
		$this->assertInstanceOf( 'Anspress\Addons\Avatar', $instance1 );
		$instance2 = \Anspress\Addons\Avatar::init();
		$this->assertSame( $instance1, $instance2 );
	}
}
