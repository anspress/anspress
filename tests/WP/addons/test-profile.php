<?php

namespace AnsPress\Tests\WP;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonProfile extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'profile.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'profile.php' );
	}

	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Profile' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'user_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'rewrite_rules' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'user_pages' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'user_menu' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'user_page_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'page_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'filter_page_title' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'sub_page_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'question_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'answer_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'load_more_answers' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'ap_current_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'modify_query_archive' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'page_template' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Profile', 'current_user_id' ) );
	}

	public function testInit() {
		$instance1 = \Anspress\Addons\Profile::init();
		$this->assertInstanceOf( 'Anspress\Addons\Profile', $instance1 );
		$instance2 = \Anspress\Addons\Profile::init();
		$this->assertSame( $instance1, $instance2 );
	}

	public function testHooksFilters() {
		$instance = \Anspress\Addons\Profile::init();

		// Tests.
		$this->assertEquals( 10, has_filter( 'ap_settings_menu_features_groups', [ $instance, 'add_to_settings_page' ] ) );
		$this->assertEquals( 10, has_action( 'ap_form_options_features_profile', [ $instance, 'options' ] ) );
		$this->assertEquals( 10, has_action( 'ap_rewrites', [ $instance, 'rewrite_rules' ] ) );
		$this->assertEquals( 10, has_action( 'ap_ajax_user_more_answers', [ $instance, 'load_more_answers' ] ) );
		$this->assertEquals( 10, has_filter( 'wp_title', [ $instance, 'page_title' ] ) );
		$this->assertEquals( 10, has_action( 'the_post', [ $instance, 'filter_page_title' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_breadcrumbs', [ $instance, 'ap_breadcrumbs' ] ) );
		$this->assertEquals( 10, has_filter( 'ap_current_page', [ $instance, 'ap_current_page' ] ) );
		$this->assertEquals( 999, has_filter( 'posts_pre_query', [ $instance, 'modify_query_archive' ] ) );
	}

	/**
	 * @covers Anspress\Addons\Profile::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Profile::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the Profile group is added to the settings page.
		$this->assertArrayHasKey( 'profile', $groups );
		$this->assertEquals( 'Profile', $groups['profile']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'profile', $groups );
		$this->assertEquals( 'Profile', $groups['profile']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Profile::options
	 */
	public function testOptions() {
		$instance = \Anspress\Addons\Profile::init();

		// Add user_page_slug_questions, user_page_slug_answers, user_page_title_questions and user_page_title_answers options.
		ap_add_default_options(
			array(
				'user_page_slug_questions'  => 'questions',
				'user_page_slug_answers'    => 'answers',
				'user_page_title_questions' => 'Questions',
				'user_page_title_answers'   => 'Answers',
			)
		);

		// Call the method.
		$form = $instance->options();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'user_page_title_questions', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_slug_questions', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_title_answers', $form['fields'] );
		$this->assertArrayHasKey( 'user_page_slug_answers', $form['fields'] );

		// Test for user_page_slug_questions.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_title_questions'] );
		$this->assertEquals( 'Questions page title', $form['fields']['user_page_title_questions']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_title_questions'] );
		$this->assertEquals( 'Custom title for user profile questions page', $form['fields']['user_page_title_questions']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_title_questions'] );
		$this->assertEquals( ap_opt( 'user_page_title_questions' ), $form['fields']['user_page_title_questions']['value'] );

		// Test for user_page_slug_answers.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_slug_questions'] );
		$this->assertEquals( 'Questions page slug', $form['fields']['user_page_slug_questions']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_slug_questions'] );
		$this->assertEquals( 'Custom slug for user profile questions page', $form['fields']['user_page_slug_questions']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_slug_questions'] );
		$this->assertEquals( ap_opt( 'user_page_slug_questions' ), $form['fields']['user_page_slug_questions']['value'] );

		// Test for user_page_title_answers.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_title_answers'] );
		$this->assertEquals( 'Answers page title', $form['fields']['user_page_title_answers']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_title_answers'] );
		$this->assertEquals( 'Custom title for user profile answers page', $form['fields']['user_page_title_answers']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_title_answers'] );
		$this->assertEquals( ap_opt( 'user_page_title_answers' ), $form['fields']['user_page_title_answers']['value'] );

		// Test for user_page_slug_answers.
		$this->assertArrayHasKey( 'label', $form['fields']['user_page_slug_answers'] );
		$this->assertEquals( 'Answers page slug', $form['fields']['user_page_slug_answers']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['user_page_slug_answers'] );
		$this->assertEquals( 'Custom slug for user profile answers page', $form['fields']['user_page_slug_answers']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['user_page_slug_answers'] );
		$this->assertEquals( ap_opt( 'user_page_slug_answers' ), $form['fields']['user_page_slug_answers']['value'] );
	}

	public function testUserPageRegistered() {
		$instance = \Anspress\Addons\Profile::init();

		// Test if user page is registered or not.
		$user_page = anspress()->pages['user'];
		$this->assertIsArray( $user_page );
		$this->assertEquals( 'User profile', $user_page['title'] );
		$this->assertEquals( [ $instance, 'user_page' ], $user_page['func'] );
		$this->assertEquals( true, $user_page['show_in_menu'] );
		$this->assertEquals( true, $user_page['private'] );
	}

	/**
	 * @covers Anspress\Addons\Profile::current_user_id
	 */
	public function testCurrentUserID() {
		$instance = \Anspress\Addons\Profile::init();

		// Test for user id without visiting the user profile page.
		$this->setRole( 'subscriber' );
		$this->assertEquals( get_current_user_id(), $instance->current_user_id() );
		$this->logout();

		// Test for user id with visiting the user profile page.
		// Test 1.
		$user = $this->factory()->user->create_and_get();
		$this->assertNotEquals( $user->ID, $instance->current_user_id() );
		$user_page = $this->factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'User profile',
			)
		);
		ap_opt( 'user_page', $user_page );
		$this->go_to( '/?post_type=page&p=' . $user_page );
		set_query_var( 'user_page', 'profile' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$wp_query->queried_object_id = $user->ID;
		$this->assertEquals( $user->ID, $instance->current_user_id() );
		$this->go_to( '/' );

		// Test 2.
		wp_set_current_user( $user->ID );
		$this->assertEquals( $user->ID, $instance->current_user_id() );
		$new_user = $this->factory()->user->create_and_get();
		$this->assertNotEquals( $new_user->ID, $instance->current_user_id() );
		$this->go_to( '/?post_type=page&p=' . $user_page );
		set_query_var( 'user_page', 'profile' );
		global $wp_query;
		$wp_query->queried_object = $new_user;
		$wp_query->queried_object_id = $new_user->ID;
		$this->assertEquals( $new_user->ID, $instance->current_user_id() );
		$this->go_to( '/' );
		$this->logout();
	}

	/**
	 * @covers Anspress\Addons\Profile::ap_current_page
	 */
	public function testAPCurrentPage() {
		$instance = \Anspress\Addons\Profile::init();

		// Test by visting other pages.
		$this->go_to( '/' );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );

		// Test by visting user profile page.
		$user_page = $this->factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'User profile',
			)
		);
		ap_opt( 'user_page', $user_page );
		$user = $this->factory()->user->create_and_get();
		$this->go_to( '/?post_type=page&p=' . $user_page );
		set_query_var( 'user_page', 'profile' );
		global $wp_query;
		$wp_query->queried_object = $user;
		$wp_query->queried_object_id = $user->ID;
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'other_query_var', $method );

		// Test by visting/setting the user author archive page.
		$this->setRole( 'editor' );
		$this->go_to( get_author_posts_url( get_current_user_id() ) );
		set_query_var( 'ap_page', 'user' );
		$method = $instance->ap_current_page( 'other_query_var' );
		$this->assertEquals( 'user', $method );
	}

	public static function APUserPages() {
		anspress()->user_pages[] = array(
			'slug' => 'test',
			'icon' => 'apicon-test',
		);
	}

	/**
	 * @covers Anspress\Addons\Profile::user_pages
	 */
	public function testUserPages() {
		$instance = \Anspress\Addons\Profile::init();

		// Test for action hook trigger.
		$callback_triggered = false;
		add_action( 'ap_user_pages', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Before calling the method.
		anspress()->user_pages = null;
		$user_pages = anspress()->user_pages;
		$this->assertNull( $user_pages );

		// After calling the method.
		// Test 1.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$instance->user_pages();
		$user_pages = anspress()->user_pages;
		$this->assertNotNull( $user_pages );
		$expected = [
			[
				'slug'  => 'questions',
				'label' => 'Questions',
				'icon'  => 'apicon-question',
				'cb'    => [ $instance, 'question_page' ],
				'order' => 2,
				'rewrite' => 'questions',
			],
			[
				'slug'  => 'answers',
				'label' => 'Answers',
				'icon'  => 'apicon-answer',
				'cb'    => [ $instance, 'answer_page' ],
				'order' => 2,
				'rewrite' => 'answers',
			],
		];
		$this->assertEquals( $expected, $user_pages );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_user_pages' ) > 0 );

		// Test 2.
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		$instance->user_pages();
		$this->assertFalse( $callback_triggered );

		// Test for adding custom pages via action hook.
		anspress()->user_pages = null;
		$callback_triggered = false;
		$this->assertFalse( $callback_triggered );
		add_action( 'ap_user_pages', [ $this, 'APUserPages' ], 11 );
		ap_opt( 'user_page_slug_test', 'tests-link' );
		ap_opt( 'user_page_title_test', 'Test title' );
		$instance->user_pages();
		$user_pages = anspress()->user_pages;
		$expected = [
			[
				'slug'  => 'questions',
				'label' => 'Questions',
				'icon'  => 'apicon-question',
				'cb'    => [ $instance, 'question_page' ],
				'order' => 2,
				'rewrite' => 'questions',
			],
			[
				'slug'  => 'answers',
				'label' => 'Answers',
				'icon'  => 'apicon-answer',
				'cb'    => [ $instance, 'answer_page' ],
				'order' => 2,
				'rewrite' => 'answers',
			],
			[
				'slug'    => 'test',
				'icon'    => 'apicon-test',
				'rewrite' => 'tests-link',
				'label'   => 'Test title',
				'order'   => 10,
			]
		];
		$this->assertEquals( $expected, $user_pages );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_user_pages' ) > 0 );
		ap_opt( 'user_page_slug_test', '' );
		ap_opt( 'user_page_title_test', '' );
		remove_action( 'ap_user_pages', [ $this, 'APUserPages' ], 11 );
	}
}
