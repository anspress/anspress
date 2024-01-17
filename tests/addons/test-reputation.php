<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonReputation extends TestCase {

	/**
	 * @covers Anspress\Addons\Reputation::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Reputation' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'load_options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'register_default_events' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'ap_save_events' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'new_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'new_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'trash_question' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'trash_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'select_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'unselect_answer' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'undo_vote_up' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'undo_vote_down' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'new_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'delete_comment' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'user_register' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'delete_user' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'display_name' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'pre_fetch_post' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'bp_profile_header_meta' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'ap_user_pages' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'reputation_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'load_more_reputation' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'ap_bp_nav' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'ap_bp_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'bp_reputation_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Reputation', 'ap_all_options' ) );
	}

	/**
	 * @covers Anspress\Addons\Reputation::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Reputation::init();
		$this->assertInstanceOf( 'Anspress\Addons\Reputation', $instance1 );
		$instance2 = \Anspress\Addons\Reputation::init();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers Anspress\Addons\Reputation::add_to_settings_page
	 */
	public function testAddtoSettingsPage() {
		$instance = \Anspress\Addons\Reputation::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Reputation group is added to the settings page.
		$this->assertArrayHasKey( 'reputation', $groups );
		$this->assertEquals( 'Reputation', $groups['reputation']['label'] );
		$this->assertArrayHasKey( 'info', $groups['reputation'] );
		$this->assertStringContainsString( 'Reputation event points can be adjusted here', $groups['reputation']['info'] );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'admin.php?page=anspress_options&active_tab=reputations' ) ) . '">', $groups['reputation']['info'] );
		$this->assertStringContainsString( 'Reputation Points', $groups['reputation']['info'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'reputation', $groups );
		$this->assertEquals( 'Reputation', $groups['reputation']['label'] );
		$this->assertArrayHasKey( 'info', $groups['reputation'] );
		$this->assertStringContainsString( 'Reputation event points can be adjusted here', $groups['reputation']['info'] );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'admin.php?page=anspress_options&active_tab=reputations' ) ) . '">', $groups['reputation']['info'] );
		$this->assertStringContainsString( 'Reputation Points', $groups['reputation']['info'] );
	}
}
