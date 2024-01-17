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

		// Test if the existing group are retained to the settings page.
		$this->assertArrayHasKey( 'avatar', $groups );
		$this->assertEquals( 'Dynamic Avatar', $groups['avatar']['label'] );
	}

	/**
	 * @covers Anspress\Addons\Avatar::option_form
	 */
	public function testOptionForm() {
		$instance = \Anspress\Addons\Avatar::init();

		// Add avatar_font and avatar_force options.
		ap_add_default_options(
			array(
				'avatar_font'  => 'Pacifico',
				'avatar_force' => false,
			)
		);

		// Call the method.
		$form = $instance->option_form();

		// Test begins.
		$this->assertNotEmpty( $form );
		$this->assertArrayHasKey( 'avatar_font', $form['fields'] );
		$this->assertArrayHasKey( 'avatar_force', $form['fields'] );
		$this->assertArrayHasKey( 'clear_avatar_cache', $form['fields'] );
		$this->assertArrayHasKey( 'submit_label', $form );
		$this->assertEquals( 'Save add-on options', $form['submit_label'] );

		// Test for clear_avatar_cache.
		$this->assertArrayHasKey( 'label', $form['fields']['clear_avatar_cache'] );
		$this->assertArrayHasKey( 'html', $form['fields']['clear_avatar_cache'] );
		$this->assertEquals( 'Clear Cache', $form['fields']['clear_avatar_cache']['label'] );

		// Test for avatar_font.
		$this->assertEquals( 'Font family', $form['fields']['avatar_font']['label'] );
		$this->assertEquals( 'Select font family for avatar letters.', $form['fields']['avatar_font']['desc'] );
		$this->assertEquals( 'select', $form['fields']['avatar_font']['type'] );
		$this->assertArrayHasKey( 'calibri', $form['fields']['avatar_font']['options'] );
		$this->assertEquals( 'Calibri', $form['fields']['avatar_font']['options']['calibri'] );
		$this->assertArrayHasKey( 'Pacifico', $form['fields']['avatar_font']['options'] );
		$this->assertEquals( 'Pacifico', $form['fields']['avatar_font']['options']['Pacifico'] );
		$this->assertArrayHasKey( 'OpenSans', $form['fields']['avatar_font']['options'] );
		$this->assertEquals( 'Open Sans', $form['fields']['avatar_font']['options']['OpenSans'] );
		$this->assertArrayHasKey( 'Glegoo-Bold', $form['fields']['avatar_font']['options'] );
		$this->assertEquals( 'Glegoo Bold', $form['fields']['avatar_font']['options']['Glegoo-Bold'] );
		$this->assertArrayHasKey( 'DeliusSwashCaps', $form['fields']['avatar_font']['options'] );
		$this->assertEquals( 'Delius Swash Caps', $form['fields']['avatar_font']['options']['DeliusSwashCaps'] );
		$this->assertEquals( ap_opt( 'avatar_font' ), $form['fields']['avatar_font']['value'] );

		// Test for avatar_force.
		$this->assertEquals( 'Force avatar', $form['fields']['avatar_force']['label'] );
		$this->assertEquals( 'Show AnsPress avatars by default instead of gravatar fallback. Useful in localhost development.', $form['fields']['avatar_force']['desc'] );
		$this->assertEquals( 'checkbox', $form['fields']['avatar_force']['type'] );
		$this->assertEquals( ap_opt( 'avatar_force' ), $form['fields']['avatar_force']['value'] );
	}

	/**
	 * @covers ::ap_is_avatar_exists
	 */
	public function testAPIsAvatarExists() {
		// Test for invalid user id.
		$this->assertFalse( \Anspress\Addons\ap_is_avatar_exists( 0 ) );
		$this->assertFalse( \Anspress\Addons\ap_is_avatar_exists( -1 ) );
		$this->assertFalse( \Anspress\Addons\ap_is_avatar_exists( 'invalid' ) );

		// Test for valid user id.
		// Test 1.
		$user_id = $this->factory()->user->create();
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars/';
		wp_mkdir_p( $avatar_dir );
		$filename   = md5( $user_id );
		$avatar_file = $avatar_dir . $filename . '.jpg';
		touch( $avatar_file );
		$this->assertTrue( \Anspress\Addons\ap_is_avatar_exists( $user_id ) );
		unlink( $avatar_file );
		rmdir( $avatar_dir );

		// Test 2.
		$user_id = $this->factory()->user->create();
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars/';
		wp_mkdir_p( $avatar_dir );
		$filename   = md5( $user_id );
		$avatar_file = $avatar_dir . $filename . '.jpg';
		touch( $avatar_file );
		$this->assertTrue( \Anspress\Addons\ap_is_avatar_exists( $user_id ) );
		unlink( $avatar_file );
		rmdir( $avatar_dir );
	}
}
