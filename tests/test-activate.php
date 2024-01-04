<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestActivate extends TestCase {

	/**
	 * @covers AP_Activate::instance
	 */
	public function testInstance() {
		$class = new \ReflectionClass( 'AP_Activate' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );
	}

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AP_Activate' );
		$this->assertTrue( $class->hasProperty( 'charset_collate' ) && $class->getProperty( 'charset_collate' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'tables' ) && $class->getProperty( 'tables' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'network_wide' ) && $class->getProperty( 'network_wide' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Activate', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AP_Activate', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'disable_ext' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'delete_options' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'enable_addons' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'qameta_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'votes_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'views_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reputation_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'subscribers_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'activity_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reputation_events_table' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'insert_tables' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'activate' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'network_activate' ) );
		$this->assertTrue( method_exists( 'AP_Activate', 'reactivate_addons' ) );
	}

	/**
	 * @covers AP_Activate::get_instance
	 */
	public function testGetInstance() {
		$instacne1 = \AP_Activate::get_instance();
		$this->assertInstanceOf( 'AP_Activate', $instacne1 );
		$instacne2 = \AP_Activate::get_instance();
		$this->assertSame( $instacne1, $instacne2 );
	}

	/**
	 * @covers AP_Activate::delete_options
	 */
	public function testDeleteOptions() {
		$ap_activate = \AP_Activate::get_instance();

		// Setup initial values and test.
		$options = array(
			'user_page_title_questions' => 'Title Questions',
			'user_page_slug_questions'  => 'slug-questions',
			'user_page_title_answers'   => 'Title Answers',
			'user_page_slug_answers'    => 'slug-answers',
		);
		update_option( 'anspress_opt', $options );
		$initial_options = get_option( 'anspress_opt', array() );
		$this->assertEquals( $options, get_option( 'anspress_opt' ) );
		$this->assertArrayHasKey( 'user_page_title_questions', $initial_options );
		$this->assertArrayHasKey( 'user_page_slug_questions', $initial_options );
		$this->assertArrayHasKey( 'user_page_title_answers', $initial_options );
		$this->assertArrayHasKey( 'user_page_slug_answers', $initial_options );

		// Call the delete_options method and test.
		$ap_activate->delete_options();
		$updated_options = get_option( 'anspress_opt', array() );
		$this->assertEmpty( $updated_options );
		$this->assertEquals( array(), get_option( 'anspress_opt' ) );
		$this->assertArrayNotHasKey( 'user_page_title_questions', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_slug_questions', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_title_answers', $updated_options );
		$this->assertArrayNotHasKey( 'user_page_slug_answers', $updated_options );
		$this->assertFalse( wp_cache_get( 'anspress_opt', 'ap' ) );
		$this->assertFalse( wp_cache_get( 'ap_default_options', 'ap' ) );
	}

	/**
	 * @covers AP_Activate::enable_addons
	 */
	public function testEnableAddons() {
		// By default these addons are activated on plugin activation,
		// so we need to deactivate them first.
		ap_deactivate_addon( 'reputation.php' );
		ap_deactivate_addon( 'email.php' );
		ap_deactivate_addon( 'categories.php' );

		// Test if the addons are not active.
		$this->assertFalse( ap_is_addon_active( 'reputation.php' ) );
		$this->assertFalse( ap_is_addon_active( 'email.php' ) );
		$this->assertFalse( ap_is_addon_active( 'categories.php' ) );

		// Call the enable_addons method.
		$ap_activate = \AP_Activate::get_instance();
		$ap_activate->enable_addons();

		// Test begins.
		$this->assertTrue( ap_is_addon_active( 'reputation.php' ) );
		$this->assertTrue( ap_is_addon_active( 'email.php' ) );
		$this->assertTrue( ap_is_addon_active( 'categories.php' ) );
	}
}
