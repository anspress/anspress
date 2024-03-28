<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonAkismet extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'akismet.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'akismet.php' );
	}

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

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'akismet', $groups );
		$this->assertEquals( 'Akismet', $groups['akismet']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Akismet::option_form
	 */
	public function testOptionForm() {
		$instance = \Anspress\Addons\Akismet::init();

		// Add spam_post_action option.
		ap_add_default_options( array( 'spam_post_action' => 'moderate' ) );

		// Call the method.
		$form = $instance->option_form();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertEquals( 'Save add-on options', $form['submit_label'] );
		$this->assertArrayHasKey( 'spam_post_action', $form['fields'] );
		$this->assertEquals( ap_opt( 'spam_post_action' ), $form['fields']['spam_post_action']['value'] );
		$this->assertEquals( 'select', $form['fields']['spam_post_action']['type'] );
		$this->assertArrayHasKey( 'moderate', $form['fields']['spam_post_action']['options'] );
		$this->assertArrayHasKey( 'trash', $form['fields']['spam_post_action']['options'] );
		$this->assertEquals( 'What to do when post is a spam?', $form['fields']['spam_post_action']['label'] );
		$this->assertEquals( 'Change status to moderate', $form['fields']['spam_post_action']['options']['moderate'] );
		$this->assertEquals( 'Trash the post', $form['fields']['spam_post_action']['options']['trash'] );
	}

	/**
	 * @covers Anspress\Addons\Akismet::spam_post_action
	 */
	public function testSpamPostAction() {
		$instance = \Anspress\Addons\Akismet::init();

		// Add spam_post_action option.
		ap_add_default_options( array( 'spam_post_action' => 'moderate' ) );

		// Test begins.
		// For default value.
		$id = $this->insert_question();
		$instance->spam_post_action( $id );
		$question = ap_get_post( $id );
		$this->assertEquals( 'moderate', $question->post_status );

		// For modifying the spam_post_action option.
		// Test 1.
		ap_opt( 'spam_post_action', 'trash' );
		$instance->spam_post_action( $id );
		$question = ap_get_post( $id );
		$this->assertEquals( 'trash', $question->post_status );

		// Test 2.
		ap_opt( 'spam_post_action', 'spam' );
		$instance->spam_post_action( $id );
		$question = ap_get_post( $id );
		$this->assertEquals( 'spam', $question->post_status );

		// Reset the spam_post_action option.
		ap_add_default_options( array( 'spam_post_action' => 'moderate' ) );
	}

	/**
	 * @covers Anspress\Addons\Akismet::row_actions
	 */
	public function testRowActions() {
		$instance = \Anspress\Addons\Akismet::init();

		// Test begins.
		// Test for invalid post type.
		$post_id = $this->factory()->post->create();
		$result = $instance->row_actions( [], ap_get_post( $post_id ) );
		$this->assertEmpty( $result );
		$page_id = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		$result = $instance->row_actions( [], ap_get_post( $page_id ) );
		$this->assertEmpty( $result );

		// Test for valid post type but post_status is set to moderate.
		$id = $this->insert_answer();
		// For question post type.
		wp_update_post( [ 'ID' => $id->q, 'post_status' => 'moderate' ] );
		$result = $instance->row_actions( [], ap_get_post( $id->q ) );
		$this->assertEmpty( $result );

		// For answer post type.
		wp_update_post( [ 'ID' => $id->a, 'post_status' => 'moderate' ] );
		$result = $instance->row_actions( [], ap_get_post( $id->a ) );
		$this->assertEmpty( $result );

		// Test for valid post type and post_status is not set to moderate.
		$id = $this->insert_answer();
		$nonce = wp_create_nonce( 'send_spam' );

		// For question post type.
		$result = $instance->row_actions( [], ap_get_post( $id->q ) );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'report_spam', $result );
		$this->assertStringContainsString( admin_url( 'admin.php?action=ap_mark_spam&post_id=' . $id->q . '&nonce=' . $nonce ), $result['report_spam'] );
		$this->assertStringContainsString( 'Mark this post as a spam', $result['report_spam'] );
		$this->assertStringContainsString( 'Mark as spam', $result['report_spam'] );

		// For answer post type.
		$result = $instance->row_actions( [], ap_get_post( $id->a ) );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'report_spam', $result );
		$this->assertStringContainsString( admin_url( 'admin.php?action=ap_mark_spam&post_id=' . $id->a . '&nonce=' . $nonce ), $result['report_spam'] );
		$this->assertStringContainsString( 'Mark this post as a spam', $result['report_spam'] );
		$this->assertStringContainsString( 'Mark as spam', $result['report_spam'] );
	}
}
