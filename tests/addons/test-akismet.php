<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonAkismet extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'akismet.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'akismet.php' );
	}

	/**
	 * @covers Anspress\Addons\Akismet::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Akismet' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'option_form' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'api_request' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'spam_post_action' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'new_question_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'submit_spam' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Akismet', 'row_actions' ) );
	}

	/**
	 * @covers Anspress\Addons\Akismet::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Akismet::init();
		$this->assertInstanceOf( 'Anspress\Addons\Akismet', $instance1 );
		$instance2 = \Anspress\Addons\Akismet::init();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers Anspress\Addons\Akismet::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Akismet::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Akismet group is added to the settings page.
		$this->assertArrayHasKey( 'akismet', $groups );
		$this->assertEquals( 'Akismet', $groups['akismet']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are rerained to the settings page.
		$this->assertArrayHasKey( 'akismet', $groups );
		$this->assertEquals( 'Akismet', $groups['akismet']['label'] );
	}
}
