<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAPLicense extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_License', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_License', 'menu' ) );
		$this->assertTrue( method_exists( 'AP_License', 'display_plugin_licenses' ) );
		$this->assertTrue( method_exists( 'AP_License', 'ap_product_license' ) );
		$this->assertTrue( method_exists( 'AP_License', 'ap_plugin_updater' ) );
	}

	/**
	 * @covers AP_License::__construct
	 */
	public function testConstruct() {
		$license = new \AP_License();
		$this->assertEquals( 10, has_action( 'ap_admin_menu', [ $license, 'menu' ] ) );
		$this->assertEquals( 0, has_action( 'admin_init', [ $license, 'ap_plugin_updater' ] ) );
	}
}
