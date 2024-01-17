<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonProfile extends TestCase {

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'profile.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'profile.php' );
	}

	/**
	 * @covers Anspress\Addons\Profile::instance
	 */
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

	/**
	 * @covers Anspress\Addons\Profile::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Profile::init();
		$this->assertInstanceOf( 'Anspress\Addons\Profile', $instance1 );
		$instance2 = \Anspress\Addons\Profile::init();
		$this->assertSame( $instance1, $instance2 );
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
}
