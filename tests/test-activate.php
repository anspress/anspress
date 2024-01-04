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
}
