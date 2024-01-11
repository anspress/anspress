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

	/**
	 * @covers Anspress\Addons\Avatar::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Avatar::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Avatar group is added to the settings page.
		$this->assertArrayHasKey( 'avatar', $groups );
		$this->assertEquals( 'Dynamic Avatar', $groups['avatar']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are rerained to the settings page.
		$this->assertArrayHasKey( 'avatar', $groups );
		$this->assertEquals( 'Dynamic Avatar', $groups['avatar']['label'] );
	}
}
