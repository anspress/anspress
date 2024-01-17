<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAddonCaptcha extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		ap_activate_addon( 'recaptcha.php' );
	}

	public function tear_down() {
		parent::tear_down();
		ap_deactivate_addon( 'recaptcha.php' );
	}

	/**
	 * @covers Anspress\Addons\Captcha::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'Anspress\Addons\Captcha' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', '__construct' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'enqueue_scripts' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'add_to_settings_page' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'options' ) );
		$this->assertTrue( method_exists( 'Anspress\Addons\Captcha', 'ap_question_form_fields' ) );
	}

	/**
	 * @covers Anspress\Addons\Captcha::instance
	 */
	public function testInit() {
		$instance1 = \Anspress\Addons\Captcha::init();
		$this->assertInstanceOf( 'Anspress\Addons\Captcha', $instance1 );
		$instance2 = \Anspress\Addons\Captcha::init();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * @covers Anspress\Addons\Captcha::enqueue_scripts
	 */
	public function testEnqueueScripts() {
		$instance = \Anspress\Addons\Captcha::init();

		// Call the method.
		$instance->enqueue_scripts();

		// Test if the script is enqueued.
		$this->assertTrue( wp_script_is( 'ap-recaptcha' ) );
	}

	/**
	 * @covers Anspress\Addons\Captcha::add_to_settings_page
	 */
	public function testAddToSettingsPage() {
		$instance = \Anspress\Addons\Captcha::init();

		// Call the method.
		$groups = $instance->add_to_settings_page( [] );

		// Test if the reCaptcha group is added to the settings page.
		$this->assertArrayHasKey( 'recaptcha', $groups );
		$this->assertEquals( 'reCaptcha', $groups['recaptcha']['label'] );

		// Test by adding new group.
		$groups = $instance->add_to_settings_page( [ 'some_other_group' => [ 'label' => 'Some Other Group' ] ] );

		// Test if the new group is added to the settings page.
		$this->assertArrayHasKey( 'some_other_group', $groups );
		$this->assertEquals( 'Some Other Group', $groups['some_other_group']['label'] );

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'recaptcha', $groups );
		$this->assertEquals( 'reCaptcha', $groups['recaptcha']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Captcha::options
	 */
	public function testOptions() {
		$instance = \Anspress\Addons\Captcha::init();

		// Add recaptcha_method and recaptcha_exclude_roles options.
		ap_add_default_options(
			array(
				'recaptcha_method'        => 'post',
				'recaptcha_exclude_roles' => array( 'ap_moderator' => 1 ),
			)
		);

		// Call the method.
		$form = $instance->options();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'recaptcha_site_key', $form['fields'] );
		$this->assertArrayHasKey( 'recaptcha_secret_key', $form['fields'] );
		$this->assertArrayHasKey( 'recaptcha_method', $form['fields'] );
		$this->assertArrayHasKey( 'recaptcha_exclude_roles', $form['fields'] );

		// Test for recaptcha_site_key field.
		$this->assertArrayHasKey( 'label', $form['fields']['recaptcha_site_key'] );
		$this->assertEquals( 'Recaptcha site key', $form['fields']['recaptcha_site_key']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['recaptcha_site_key'] );
		$this->assertEquals( 'Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', $form['fields']['recaptcha_site_key']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['recaptcha_site_key'] );
		$this->assertEquals( ap_opt( 'recaptcha_site_key' ), $form['fields']['recaptcha_site_key']['value'] );

		// Test for recaptcha_secret_key field.
		$this->assertArrayHasKey( 'label', $form['fields']['recaptcha_secret_key'] );
		$this->assertEquals( 'Recaptcha secret key', $form['fields']['recaptcha_secret_key']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['recaptcha_secret_key'] );
		$this->assertEquals( 'Enter your secret key', $form['fields']['recaptcha_secret_key']['desc'] );
		$this->assertArrayHasKey( 'value', $form['fields']['recaptcha_secret_key'] );
		$this->assertEquals( ap_opt( 'recaptcha_secret_key' ), $form['fields']['recaptcha_secret_key']['value'] );

		// Test for recaptcha_method field.
		$this->assertArrayHasKey( 'label', $form['fields']['recaptcha_method'] );
		$this->assertEquals( 'Recaptcha Method', $form['fields']['recaptcha_method']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['recaptcha_method'] );
		$this->assertEquals( 'Select method to use when verification keeps failing', $form['fields']['recaptcha_method']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['recaptcha_method'] );
		$this->assertEquals( 'select', $form['fields']['recaptcha_method']['type'] );
		$this->assertArrayHasKey( 'options', $form['fields']['recaptcha_method'] );
		$this->assertArrayHasKey( 'curl', $form['fields']['recaptcha_method']['options'] );
		$this->assertArrayHasKey( 'post', $form['fields']['recaptcha_method']['options'] );
		$this->assertEquals( array( 'curl' => 'CURL', 'post' => 'POST' ), $form['fields']['recaptcha_method']['options'] );
		$this->assertArrayHasKey( 'value', $form['fields']['recaptcha_method'] );
		$this->assertEquals( ap_opt( 'recaptcha_method' ), $form['fields']['recaptcha_method']['value'] );

		// Test for recaptcha_exclude_roles field.
		global $wp_roles;
		$this->assertArrayHasKey( 'label', $form['fields']['recaptcha_exclude_roles'] );
		$this->assertEquals( 'Hide reCaptcha for roles', $form['fields']['recaptcha_exclude_roles']['label'] );
		$this->assertArrayHasKey( 'desc', $form['fields']['recaptcha_exclude_roles'] );
		$this->assertEquals( 'Select roles for which reCaptcha will be hidden.', $form['fields']['recaptcha_exclude_roles']['desc'] );
		$this->assertArrayHasKey( 'type', $form['fields']['recaptcha_exclude_roles'] );
		$this->assertEquals( 'checkbox', $form['fields']['recaptcha_exclude_roles']['type'] );
		$this->assertArrayHasKey( 'options', $form['fields']['recaptcha_exclude_roles'] );
		foreach ( $wp_roles->roles as $role => $role_data ) {
			$this->assertArrayHasKey( $role, $form['fields']['recaptcha_exclude_roles']['options'] );
			$this->assertEquals( $role_data['name'], $form['fields']['recaptcha_exclude_roles']['options'][ $role ] );
		}
	}

	/**
	 * @covers Anspress\Addons\Captcha::ap_question_form_fields
	 */
	public function testAPQuestionFormFields() {
		$instance = \Anspress\Addons\Captcha::init();

		// Required dummy reCaptcha Site Key.
		ap_opt( 'recaptcha_site_key', 'anspressSamplereCaptchaSiteKey' );

		// Create and assign ask page.
		$ask_page = $this->factory->post->create( array( 'post_type' => 'page' ) );
		ap_opt( 'ask_page', $ask_page );

		// Test for showing captcha in form for non logged in users.
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'captcha', $form['fields'] );
		$this->assertArrayHasKey( 'label', $form['fields']['captcha'] );
		$this->assertEquals( 'Prove that you are a human', $form['fields']['captcha']['label'] );
		$this->assertArrayHasKey( 'type', $form['fields']['captcha'] );
		$this->assertEquals( 'captcha', $form['fields']['captcha']['type'] );
		$this->assertArrayHasKey( 'order', $form['fields']['captcha'] );
		$this->assertEquals( 100, $form['fields']['captcha']['order'] );

		// Test for subscriber.
		$this->setRole( 'subscriber' );
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'captcha', $form['fields'] );
		$this->assertArrayHasKey( 'label', $form['fields']['captcha'] );
		$this->assertEquals( 'Prove that you are a human', $form['fields']['captcha']['label'] );
		$this->assertArrayHasKey( 'type', $form['fields']['captcha'] );
		$this->assertEquals( 'captcha', $form['fields']['captcha']['type'] );
		$this->assertArrayHasKey( 'order', $form['fields']['captcha'] );
		$this->assertEquals( 100, $form['fields']['captcha']['order'] );

		// Test for contributor.
		$this->setRole( 'contributor' );
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'captcha', $form['fields'] );
		$this->assertArrayHasKey( 'label', $form['fields']['captcha'] );
		$this->assertEquals( 'Prove that you are a human', $form['fields']['captcha']['label'] );
		$this->assertArrayHasKey( 'type', $form['fields']['captcha'] );
		$this->assertEquals( 'captcha', $form['fields']['captcha']['type'] );
		$this->assertArrayHasKey( 'order', $form['fields']['captcha'] );
		$this->assertEquals( 100, $form['fields']['captcha']['order'] );

		// Test for ap_moderator.
		$this->setRole( 'ap_moderator' );
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertEmpty( $form );
		$this->assertArrayNotHasKey( 'fields', $form );

		// Test for subscriber user role with exclude recaptcha enabled.
		ap_opt( 'recaptcha_exclude_roles', [ 'subscriber' => 1 ] );
		$this->setRole( 'subscriber' );
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertEmpty( $form );
		$this->assertArrayNotHasKey( 'fields', $form );

		// Test for administrator user role.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->go_to( '/?post_type=page&p=' . $ask_page );
			$form = $instance->ap_question_form_fields( [] );
			$this->assertNotEmpty( $form );
			$this->assertArrayHasKey( 'captcha', $form['fields'] );
			$this->assertArrayHasKey( 'label', $form['fields']['captcha'] );
			$this->assertEquals( 'Prove that you are a human', $form['fields']['captcha']['label'] );
			$this->assertArrayHasKey( 'type', $form['fields']['captcha'] );
			$this->assertEquals( 'captcha', $form['fields']['captcha']['type'] );
			$this->assertArrayHasKey( 'order', $form['fields']['captcha'] );
			$this->assertEquals( 100, $form['fields']['captcha']['order'] );

			// Test for super admin user role.
			$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->go_to( '/?post_type=page&p=' . $ask_page );
			$form = $instance->ap_question_form_fields( [] );
			$this->assertNotEmpty( $form );
			$this->assertArrayHasKey( 'captcha', $form['fields'] );
			$this->assertArrayHasKey( 'label', $form['fields']['captcha'] );
			$this->assertEquals( 'Prove that you are a human', $form['fields']['captcha']['label'] );
			$this->assertArrayHasKey( 'type', $form['fields']['captcha'] );
			$this->assertEquals( 'captcha', $form['fields']['captcha']['type'] );
			$this->assertArrayHasKey( 'order', $form['fields']['captcha'] );
			$this->assertEquals( 100, $form['fields']['captcha']['order'] );

			// After granting super admin role.
			grant_super_admin( $user_id );
			$this->go_to( '/?post_type=page&p=' . $ask_page );
			$form = $instance->ap_question_form_fields( [] );
			$this->assertEmpty( $form );
			$this->assertArrayNotHasKey( 'fields', $form );
		} else {
			$this->setRole( 'administrator' );
			$this->go_to( '/?post_type=page&p=' . $ask_page );
			$form = $instance->ap_question_form_fields( [] );
			$this->assertEmpty( $form );
			$this->assertArrayNotHasKey( 'fields', $form );
		}

		// Logout current user.
		$this->logout();

		// Reset the reCaptcha Site Key.
		ap_opt( 'recaptcha_site_key', '' );

		// Test if reCaptcha Site Key is not added.
		// Test for logged in user.
		$this->setRole( 'subscriber' );
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertEmpty( $form );
		$this->assertArrayNotHasKey( 'fields', $form );

		// Test for non logged in user.
		$this->logout();
		$this->go_to( '/?post_type=page&p=' . $ask_page );
		$form = $instance->ap_question_form_fields( [] );
		$this->assertEmpty( $form );
		$this->assertArrayNotHasKey( 'fields', $form );
	}
}
