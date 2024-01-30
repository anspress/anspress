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
		$this->assertArrayHasKey( 'label', $groups['some_other_group'] );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'reputation', $groups );
		$this->assertEquals( 'Reputation', $groups['reputation']['label'] );
		$this->assertArrayHasKey( 'info', $groups['reputation'] );
		$this->assertStringContainsString( 'Reputation event points can be adjusted here', $groups['reputation']['info'] );
		$this->assertStringContainsString( '<a href="' . esc_url( admin_url( 'admin.php?page=anspress_options&active_tab=reputations' ) ) . '">', $groups['reputation']['info'] );
		$this->assertStringContainsString( 'Reputation Points', $groups['reputation']['info'] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::load_options
	 */
	public function testLoadOptions() {
		$instance = \Anspress\Addons\Reputation::init();

		// Add user_page_title_reputations and user_page_slug_reputations options.
		ap_add_default_options(
			array(
				'user_page_title_reputations' => __( 'Reputations', 'anspress-question-answer' ),
				'user_page_slug_reputations'  => 'reputations',
			)
		);

		// Call the method.
		$form = $instance->load_options();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'user_page_title_reputations', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_slug_reputations', $form['fields'] );

		// Test for user_page_title_reputations field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_title_reputations'] );
		$this->assertEquals( 'Reputations page title', $form['fields']['user_page_title_reputations']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_title_reputations'] );
		$this->assertEquals( 'Custom title for user profile reputations page', $form['fields']['user_page_title_reputations']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_title_reputations'] );
		$this->assertEquals( ap_opt( 'user_page_title_reputations' ), $form['fields']['user_page_title_reputations']['value'] );

		// Test for user_page_slug_reputations field.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_slug_reputations'] );
		$this->assertEquals( 'Reputations page slug', $form['fields']['user_page_slug_reputations']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_slug_reputations'] );
		$this->assertEquals( 'Custom slug for user profile reputations page', $form['fields']['user_page_slug_reputations']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_slug_reputations'] );
		$this->assertEquals( ap_opt( 'user_page_slug_reputations' ), $form['fields']['user_page_slug_reputations']['value'] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::ap_bp_nav
	 */
	public function testapBPNav() {
		$instance = \Anspress\Addons\Reputation::init();

		// Dummy nav menu items.
		$dummy_menu_items = [
			[
				'name' => 'Item 1',
				'slug' => 'item-1',
			],
			[
				'name' => 'Item 2',
				'slug' => 'item-2',
			],
		];
		$this->assertEquals( 2, count( $dummy_menu_items ) );

		// Call the method.
		$modified_nav = $instance->ap_bp_nav( $dummy_menu_items );

		// Test begins.
		$this->assertNotEmpty( $modified_nav );
		$this->assertIsArray( $modified_nav );
		$this->assertEquals( 3, count( $modified_nav ) );
		$last_item = end( $modified_nav );
		$this->assertEquals( 'Reputations', $last_item['name'] );
		$this->assertEquals( 'reputations', $last_item['slug'] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::ap_all_options
	 */
	public function testAPAllOptions() {
		$instance = \Anspress\Addons\Reputation::init();

		// Dummy options.
		$dummy_options = [
			'option1' => [
				'label'    => 'Option 1',
				'template' => 'option-1.php',
			],
			'option2' => [
				'label'    => 'Option 2',
				'template' => 'option-2.php',
			],
		];
		$this->assertEquals( 2, count( $dummy_options ) );

		// Call the method.
		$modified_options = $instance->ap_all_options( $dummy_options );

		// Test begins.
		$this->assertNotEmpty( $modified_options );
		$this->assertIsArray( $modified_options );
		$this->assertEquals( 3, count( $modified_options ) );

		// Test for reputations options.
		$reputations_options = end( $modified_options );
		$this->assertArrayHasKey( 'reputations', $modified_options );
		$this->assertArrayHasKey( 'label', $reputations_options );
		$this->assertEquals( '⚙ Reputations', $reputations_options['label'] );
		$this->assertArrayHasKey( 'template', $reputations_options );
		$this->assertEquals( 'reputation-events.php', $reputations_options['template'] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::register_default_events
	 */
	public function testRegisterDefaultEvents() {
		$instance = \Anspress\Addons\Reputation::init();

		// Reset the reputation tables.
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputations}" );
		$wpdb->query( "TRUNCATE {$wpdb->ap_reputation_events}" );

		// Test begins.
		// Before calling the method.
		$events = ap_get_all_reputation_events();
		$events_cache = wp_cache_get( 'all', 'ap_get_all_reputation_events' );
		$this->assertEmpty( $events );
		$this->assertFalse( $events_cache );

		// After calling the method.
		$instance->register_default_events();
		$events = ap_get_all_reputation_events();
		$events_cache = wp_cache_get( 'all', 'ap_get_all_reputation_events' );
		$this->assertNotEmpty( $events );
		$this->assertCount( 10, $events );
		$this->assertNotEmpty( $events_cache );

		// Test for registered events.
		$event_slugs = [ 'register', 'ask', 'answer', 'comment', 'select_answer', 'best_answer', 'received_vote_up', 'received_vote_down', 'given_vote_up', 'given_vote_down' ];
		$event_lists = wp_list_pluck( $events, 'slug' );
		$this->assertEquals( $event_slugs, $event_lists );
		$this->assertContains( 'register', $event_lists );
		$this->assertContains( 'ask', $event_lists );
		$this->assertContains( 'answer', $event_lists );
		$this->assertContains( 'comment', $event_lists );
		$this->assertContains( 'select_answer', $event_lists );
		$this->assertContains( 'best_answer', $event_lists );
		$this->assertContains( 'received_vote_up', $event_lists );
		$this->assertContains( 'received_vote_down', $event_lists );
		$this->assertContains( 'given_vote_up', $event_lists );
		$this->assertContains( 'given_vote_down', $event_lists );
	}
}
