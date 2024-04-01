<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonReputation extends TestCase {

	use Testcases\Common;

	public function set_up()
	{
		parent::set_up();

		anspress()->reputation_events = [];
	}

	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Reputation' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		// Count the number of public methods.
		$class = new \ReflectionClass( 'Anspress\Addons\Reputation' );
		$methods = $class->getMethods( \ReflectionMethod::IS_PUBLIC );
		$this->assertCount( 31, $methods );

		// Count the number of protected methods.
		$methods = $class->getMethods( \ReflectionMethod::IS_PROTECTED );
		$this->assertCount( 1, $methods );

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
	}

	public function testInit() {
		$instance1 = \Anspress\Addons\Reputation::init();
		$this->assertInstanceOf( 'Anspress\Addons\Reputation', $instance1 );
		$instance2 = \Anspress\Addons\Reputation::init();
		$this->assertSame( $instance1, $instance2 );
	}

	public function testHooksFilters() {
		$reflectionClass = new \ReflectionClass( 'Anspress\Addons\Reputation' );
		$property = $reflectionClass->getProperty( 'instance' );
		$property->setAccessible( true );
		$property->setValue( null );
		$instance = \Anspress\Addons\Reputation::init();

		$this->assertEquals( 10, has_action( 'wp_ajax_ap_save_events', [ $instance, 'ap_save_events' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ $instance, 'new_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ $instance, 'new_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_untrash_question', [ $instance, 'new_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_question', [ $instance, 'trash_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ $instance, 'trash_question' ] ) );
		$this->assertEquals( 10, has_action( 'ap_untrash_answer', [ $instance, 'new_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_trash_answer', [ $instance, 'trash_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_answer', [ $instance, 'trash_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_select_answer', [ $instance, 'select_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_up', [ $instance, 'vote_up' ] ) );
		$this->assertEquals( 10, has_action( 'ap_vote_down', [ $instance, 'vote_down' ] ) );
		$this->assertEquals( 10, has_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] ) );
		$this->assertEquals( 10, has_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] ) );
		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ $instance, 'new_comment' ] ) );
		$this->assertEquals( 10, has_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] ) );
		$this->assertEquals( 10, has_filter( 'user_register', [ $instance, 'user_register' ] ) );
		$this->assertEquals( 10, has_action( 'delete_user', [ $instance, 'delete_user' ] ) );

		$this->assertEquals( 10, has_filter( 'ap_pre_fetch_question_data', [ $instance, 'pre_fetch_post' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_pre_fetch_answer_data', [ $instance, 'pre_fetch_post' ] ) );
		$this->assertEquals( 10, has_filter( 'bp_before_member_header_meta', [ $instance, 'bp_profile_header_meta' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_user_pages', [ $instance, 'ap_user_pages' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_ajax_load_more_reputation', [ $instance, 'load_more_reputation' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_bp_nav', [ $instance, 'ap_bp_nav' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_bp_page', [ $instance, 'ap_bp_page' ] ) );


	}

	public function testDefaultOptions() {
		$this->assertArrayHasKey( 'user_page_title_reputations', ap_default_options() );
		$this->assertArrayHasKey( 'user_page_slug_reputations', ap_default_options() );
		$this->assertArrayHasKey( 'show_reputation_in_author_link', ap_default_options() );
		$this->assertArrayHasKey( 'enable_reputation', ap_default_options() );
	}

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

	public function testAPAllOptions() {
		$options = apply_filters('ap_all_options', []);

		$this->assertArrayHasKey('reputation', $options);

		$this->assertSame('Reputation', $options['reputation']['label']);
		$this->assertSame(
			array(
				'reputation_settings' => array(
					'label' => __( 'Settings', 'anspress-question-answer' ),
				),
				'reputation_events'   => array(
					'label'    => __( 'Reputation Events', 'anspress-question-answer' ),
					'template' => 'reputation-events.php',
				),
			),
			$options['reputation']['groups']
		);
	}

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


	public function testUserRegister() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$user_id = $this->factory()->user->create();
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$instance->user_register( $user_id );
		$this->assertEquals( 20, ap_get_user_reputation( $user_id ) );

		// Test by creating a new user.
		add_filter( 'user_register', [ $instance, 'user_register' ] );
		$user_id = $this->factory()->user->create();
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		remove_filter( 'user_register', [ $instance, 'user_register' ] );
	}

	public function testNewQuestion() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_question( $question_id, get_post( $question_id ) );
		$this->assertEquals( 14, ap_get_user_reputation( get_current_user_id() ) );

		// Test by creating a new question.
		$this->setRole( 'subscriber' );
		add_action( 'ap_after_new_question', [ $instance, 'new_question' ], 10, 2 );
		$question_id = $this->factory()->post->create( [ 'post_type' => 'question' ] );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_after_new_question', [ $instance, 'new_question' ], 10, 2 );
	}

	public function testNewAnswer() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 15, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 20, ap_get_user_reputation( get_current_user_id() ) );

		// Test by creating a new answer.
		$this->setRole( 'subscriber' );
		add_action( 'ap_after_new_answer', [ $instance, 'new_answer' ], 10, 2 );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 15, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_after_new_answer', [ $instance, 'new_answer' ], 10, 2 );
	}

	public function testTrashQuestion() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_question( $question_id, get_post( $question_id ) );
		$this->assertEquals( 14, ap_get_user_reputation( get_current_user_id() ) );
		$instance->trash_question( $question_id, get_post( $question_id ) );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );

		// Test by trashing a question.
		$this->setRole( 'subscriber' );
		add_action( 'ap_trash_question', [ $instance, 'trash_question' ], 10, 2 );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_question( $question_id, get_post( $question_id ) );
		$this->assertEquals( 14, ap_get_user_reputation( get_current_user_id() ) );
		wp_trash_post( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_trash_question', [ $instance, 'trash_question' ], 10, 2 );
	}

	public function testTrashAnswer() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 15, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 20, ap_get_user_reputation( get_current_user_id() ) );
		$instance->trash_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );

		// Test by trashing an answer.
		$this->setRole( 'subscriber' );
		add_action( 'ap_trash_answer', [ $instance, 'trash_answer' ], 10, 2 );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 15, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 20, ap_get_user_reputation( get_current_user_id() ) );
		wp_trash_post( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_trash_answer', [ $instance, 'trash_answer' ], 10, 2 );
	}

	public function testSelectAnswer() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 17, ap_get_user_reputation( get_current_user_id() ) );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 29, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$user_id = $this->factory()->user->create();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 25, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 14, ap_get_user_reputation( get_current_user_id() ) );

		// Test by selecting an answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_select_answer', [ $instance, 'select_answer' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$this->assertEquals( 29, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_select_answer', [ $instance, 'select_answer' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_select_answer', [ $instance, 'select_answer' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$user_id = $this->factory()->user->create();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$this->assertEquals( 25, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 14, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_select_answer', [ $instance, 'select_answer' ] );
	}

	public function testUnselectAnswer() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		$instance->unselect_answer( get_post( $answer_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$user_id = $this->factory()->user->create();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		$instance->unselect_answer( get_post( $answer_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by unselecting an answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 12, ap_get_user_reputation( get_current_user_id() ) );
		ap_unset_selected_answer( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$user_id = $this->factory()->user->create();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_set_selected_answer( $question_id, $answer_id );
		$instance->select_answer( get_post( $answer_id ) );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		ap_unset_selected_answer( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_unselect_answer', [ $instance, 'unselect_answer' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::vote_up
	 */
	public function testVoteUp() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by voting up.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_up', [ $instance, 'vote_up' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_up', [ $instance, 'vote_up' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_up', [ $instance, 'vote_up' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_up', [ $instance, 'vote_up' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_up', [ $instance, 'vote_up' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_up', [ $instance, 'vote_up' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_up', [ $instance, 'vote_up' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_up', [ $instance, 'vote_up' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::vote_down
	 */
	public function testVoteDown() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by voting down.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_down', [ $instance, 'vote_down' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_down', [ $instance, 'vote_down' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_down', [ $instance, 'vote_down' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_down', [ $instance, 'vote_down' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_down', [ $instance, 'vote_down' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_down', [ $instance, 'vote_down' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_vote_down', [ $instance, 'vote_down' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_vote_down', [ $instance, 'vote_down' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::undo_vote_up
	 */
	public function testUndoVoteUp() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_up( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_up( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_up( $answer_id );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_up( $answer_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by undoing vote up.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $question_id, get_current_user_id(), 'vote_up' );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_up( $question_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $question_id, get_current_user_id(), 'vote_up' );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $answer_id, get_current_user_id(), 'vote_up' );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_up( $answer_id );
		$this->assertEquals( 10, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $answer_id, get_current_user_id(), 'vote_up' );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_up', [ $instance, 'undo_vote_up' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::undo_vote_down
	 */
	public function testUndoVoteDown() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_down( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_down( $question_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_down( $answer_id );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->undo_vote_down( $answer_id );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by undoing vote down.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $question_id, get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question( '', '', $user_id );
		ap_add_post_vote( $question_id, get_current_user_id(), false );
		$instance->vote_down( $question_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $question_id, get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $answer_id, get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		ap_add_post_vote( $answer_id, get_current_user_id(), false );
		$instance->vote_down( $answer_id );
		$this->assertEquals( -2, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		ap_delete_post_vote( $answer_id, get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_undo_vote_down', [ $instance, 'undo_vote_down' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::new_comment
	 */
	public function testNewComment() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );

		// Test by adding a new comment.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::delete_comment
	 */
	public function testDeleteComment() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		$instance->delete_comment( get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		$instance->delete_comment( get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		$instance->delete_comment( get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test 2.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		$instance->new_comment( get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		$instance->delete_comment( get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );

		// Test by deleting a comment.
		// For question.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		do_action( 'ap_unpublish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		remove_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $question_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		do_action( 'ap_unpublish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		remove_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );

		// For answer.
		// Test 1.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => get_current_user_id(), 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( get_current_user_id() ) );
		do_action( 'ap_unpublish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		remove_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );

		// Test 2.
		$this->setRole( 'subscriber' );
		add_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );
		add_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		$user_id = $this->factory()->user->create();
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$comment_id = $this->factory()->comment->create( [ 'comment_post_ID' => $answer_id, 'user_id' => $user_id, 'comment_type' => 'anspress' ] );
		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 2, ap_get_user_reputation( $user_id ) );
		do_action( 'ap_unpublish_comment', get_comment( $comment_id ) );
		$this->assertEquals( 0, ap_get_user_reputation( $user_id ) );
		remove_action( 'ap_publish_comment', [ $instance, 'new_comment' ] );
		remove_action( 'ap_unpublish_comment', [ $instance, 'delete_comment' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::delete_user
	 */
	public function testDeleteUser() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test by directly calling the method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => get_current_user_id() ] );
		$instance->new_question( $question_id, get_post( $question_id ) );
		$instance->new_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 7, ap_get_user_reputation( get_current_user_id() ) );
		$instance->delete_user( get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );

		// Test by deleting a user.
		add_action( 'delete_user', [ $instance, 'delete_user' ] );
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => get_current_user_id() ] );
		$instance->new_question( $question_id, get_post( $question_id ) );
		$instance->new_answer( $answer_id, get_post( $answer_id ) );
		$this->assertEquals( 7, ap_get_user_reputation( get_current_user_id() ) );
		wp_delete_user( get_current_user_id() );
		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
		remove_action( 'delete_user', [ $instance, 'delete_user' ] );
	}

	/**
	 * @covers Anspress\Addons\Reputation::ap_user_pages
	 */
	public function testAPUserPages() {
		$instance = \Anspress\Addons\Reputation::init();

		// Test begins.
		// Before calling the method.
		anspress()->user_pages = null;
		$user_pages = anspress()->user_pages;
		$this->assertNull( $user_pages );

		// After calling the method.
		$instance->ap_user_pages();
		$user_pages = anspress()->user_pages;
		$this->assertNotNull( $user_pages );
		$expected = [
			[
				'slug'  => 'reputations',
				'label' => 'Reputations',
				'icon'  => 'apicon-reputation',
				'cb'    => array( $instance, 'reputation_page' ),
				'order' => 5,
			]
		];
		$this->assertEquals( $expected, $user_pages );
	}
}
