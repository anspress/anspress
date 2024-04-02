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

		$page_groups = apply_filters( 'ap_all_options', [] );

		$this->assertArrayHasKey( 'reputation', $page_groups );
		$this->assertSame( 'Reputation', $page_groups['reputation']['label'] );
		$this->assertArrayHasKey( 'reputation_settings', $page_groups['reputation']['groups'] );
		$this->assertSame( __( 'Settings', 'anspress-question-answer' ), $page_groups['reputation']['groups']['reputation_settings']['label'] );
		$this->assertArrayHasKey( 'reputation_events', $page_groups['reputation']['groups'] );
		$this->assertSame( __( 'Reputation Events', 'anspress-question-answer' ), $page_groups['reputation']['groups']['reputation_events']['label'] );
		$this->assertSame( 'reputation-events.php', $page_groups['reputation']['groups']['reputation_events']['template'] );
	}

	public function testFormFields() {
		$form = anspress()->get_form('options_reputation_reputation_settings');

		$this->assertEquals(
			[
				'enable_reputation' => [
					'label' => 'Enable reputation',
					'desc'  => 'Enable reputation system',
					'type'  => 'checkbox',
					'value' => true,
				],
				'show_reputation_in_author_link' => [
					'label' => 'Show reputation in author link',
					'desc'  => 'Show reputation points in author link',
					'type'  => 'checkbox',
					'value' => false,
				],
				'user_page_title_reputations' => [
					'label' => 'Reputations page title',
					'desc'  => 'Custom title for user profile reputations page',
					'value' => 'Reputations',
				],
				'user_page_slug_reputations' => [
					'label' => 'Reputations page slug',
					'value' => 'reputations',
					'desc'  => 'Custom slug for user profile reputations page',
				],
			],
			$form->args['fields']
		);
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

	public function testRegisterDefaultEvents() {
		anspress()->reputation_events = [];

		$instance = \Anspress\Addons\Reputation::init();

		$this->assertCount( 0, anspress()->reputation_events );

		$instance->register_default_events();

		$this->assertCount( 10, anspress()->reputation_events );

		$events = anspress()->reputation_events;

		$this->assertArrayHasKey( 'register', $events );
		$this->assertArrayHasKey( 'ask', $events );
		$this->assertArrayHasKey( 'answer', $events );
		$this->assertArrayHasKey( 'comment', $events );
		$this->assertArrayHasKey( 'given_vote_up', $events );
		$this->assertArrayHasKey( 'given_vote_down', $events );
		$this->assertArrayHasKey( 'select_answer', $events );
		$this->assertArrayHasKey( 'best_answer', $events );
		$this->assertArrayHasKey( 'received_vote_up', $events );
		$this->assertArrayHasKey( 'received_vote_down', $events );
	}


	public function testUserRegister() {
		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'register', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 999,
		] );

		$this->setRole( 'subscriber' );

		$this->assertEquals(999, ap_get_user_reputation(get_current_user_id()));
	}

	public function testNewQuestion() {

		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'ask', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 999,

		] );

		$this->setRole( 'subscriber' );

		$this->factory()->post->create( [
			'post_type' => 'question',
			'post_author' => get_current_user_id()
		] );

		$this->assertEquals( 999, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testNewAnswer() {
		ap_register_reputation_event( 'answer', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 9991,

		] );

		$this->setRole( 'subscriber' );

		$this->factory()->post->create( [
			'post_type' => 'answer',
			'post_author' => get_current_user_id()
		] );

		$this->assertEquals( 9991, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testTrashQuestion() {
		ap_register_reputation_event( 'ask', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 9992,

		] );

		$this->setRole( 'subscriber' );

		$question_id = $this->factory()->post->create( [
			'post_type' => 'question',
			'post_author' => get_current_user_id()
		] );

		$this->assertEquals( 9992, ap_get_user_reputation( get_current_user_id() ) );

		wp_delete_post( $question_id );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testTrashAnswer() {
		ap_register_reputation_event( 'answer', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 9993,

		] );

		$this->setRole( 'subscriber' );

		$answer_id = $this->factory()->post->create( [
			'post_type' => 'answer',
			'post_author' => get_current_user_id()
		] );

		$this->assertEquals( 9993, ap_get_user_reputation( get_current_user_id() ) );

		wp_delete_post( $answer_id );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testSelectAnswer() {
		ap_register_reputation_event( 'best_answer', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 9994,
		] );

		$this->setRole( 'subscriber' );

		$question_id = $this->factory()->post->create( [
			'post_type' => 'question',
			'post_author' => get_current_user_id()
		] );

		$answer_id = $this->factory()->post->create( [
			'post_type' => 'answer',
			'post_parent' => $question_id,
			'post_author' => get_current_user_id()
		] );

		ap_set_selected_answer( $question_id, $answer_id );

		$this->assertEquals( 9994, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testUnselectAnswer() {
		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'best_answer', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'answer',
			'points'        => 9995,
		] );

		ap_register_reputation_event( 'select_answer', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'question',
			'points'        => 991,
		] );

		$answerer = $this->factory()->user->create();

		$this->setRole( 'subscriber' );

		$question_id = $this->factory()->post->create( [
			'post_type' => 'question',
			'post_author' => get_current_user_id()
		] );

		$answer_id = $this->factory()->post->create( [
			'post_type' => 'answer',
			'post_parent' => $question_id,
			'post_author' => $answerer,
		] );

		ap_set_selected_answer( $question_id, $answer_id );

		$this->assertEquals( 9995, ap_get_user_reputation( $answerer ) );

		$this->assertEquals( 991, ap_get_user_reputation( get_current_user_id() ) );

		ap_unset_selected_answer( $question_id, $answer_id );

		$this->assertEquals( 0, ap_get_user_reputation( $answer_id ) );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testVoteUpOnQuestion() {
		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'given_vote_up', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'question',
			'points'        => 9996,
		] );

		ap_register_reputation_event( 'received_vote_up', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'question',
			'points'        => 996,
		] );

		$op = $this->factory()->user->create();

		$question_id = $this->factory()->post->create( [
			'post_type'   => 'question',
			'post_author' => $op,
		] );

		$this->setRole( 'subscriber' );

		ap_add_post_vote( $question_id, get_current_user_id(), true );

		$this->assertEquals( 996, ap_get_user_reputation( $op ) );

		$this->assertEquals( 9996, ap_get_user_reputation( get_current_user_id() ) );

		// Undo vote up.
		ap_delete_post_vote( $question_id, get_current_user_id(), true );

		$this->assertEquals( 0, ap_get_user_reputation( $op ) );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testDownVoteOnQuestion() {
		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'given_vote_down', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'question',
			'points'        => -111,
		] );

		ap_register_reputation_event( 'received_vote_down', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => 'question',
			'points'        => -113,
		] );

		$op = $this->factory()->user->create();

		$question_id = $this->factory()->post->create( [
			'post_type'   => 'question',
			'post_author' => $op,
		] );

		$this->setRole( 'subscriber' );

		ap_add_post_vote( $question_id, get_current_user_id(), false );

		$this->assertEquals( -113, ap_get_user_reputation( $op ) );

		$this->assertEquals( -111, ap_get_user_reputation( get_current_user_id() ) );

		// Undo vote up.
		ap_delete_post_vote( $question_id, get_current_user_id(), false );

		$this->assertEquals( 0, ap_get_user_reputation( $op ) );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
	}

	public function testDeleteUser() {
		ap_opt( 'enable_reputation', true );

		ap_register_reputation_event( 'test', [
			'label'         => 'Test reputation event',
			'description'   => 'Lorem ipsum dolor sit amet',
			'icon'          => 'apicon-test-reputation',
			'activity'      => 'Reputation registered',
			'parent'        => '',
			'points'        => 9911,
		] );

		$this->setRole( 'subscriber' );

		ap_insert_reputation( 'test', get_current_user_id(), get_current_user_id() );

		$this->assertEquals( 9911, ap_get_user_reputation( get_current_user_id() ) );

		wp_delete_user( get_current_user_id() );

		$this->assertEquals( 0, ap_get_user_reputation( get_current_user_id() ) );
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
