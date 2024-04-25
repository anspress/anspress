<?php

namespace AnsPress\Tests\WP;

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

	/**
	 * @covers AP_License::display_plugin_licenses
	 */
	public function testDisplayPluginLicenses() {
		$license = new \AP_License();
		ob_start();
		$license->display_plugin_licenses();
		$result = ob_get_clean();
		$this->assertStringContainsString( 'Licenses', $result );
		$this->assertStringContainsString( 'License keys for AnsPress products, i.e. extensions and themes.', $result );
	}

	public function AnsPressLicenseFields( $fields ) {
		$fields['product'] = [
			'name'      => 'Product Name',
			'version'   => '1.0.0',
			'author'    => 'Product Author',
			'file'      => __FILE__,
			'is_plugin' => false,
		];

		return $fields;
	}

	public function testAPProductLicenseFields() {
		// Test by directly calling the function.
		$fields = ap_product_license_fields();
		$this->assertEmpty( $fields );
		$this->assertIsArray( $fields );

		// Test by filtering the anspress_license_fields hook.
		add_filter( 'anspress_license_fields', [ $this, 'AnsPressLicenseFields' ] );
		$fields = ap_product_license_fields();
		$this->assertNotEmpty( $fields );
		$this->assertIsArray( $fields );
		$this->assertArrayHasKey( 'product', $fields );
		$this->assertArrayHasKey( 'name', $fields['product'] );
		$this->assertArrayHasKey( 'version', $fields['product'] );
		$this->assertArrayHasKey( 'author', $fields['product'] );
		$this->assertArrayHasKey( 'file', $fields['product'] );
		$this->assertArrayHasKey( 'is_plugin', $fields['product'] );

		// Test by removing the filter.
		remove_filter( 'anspress_license_fields', [ $this, 'AnsPressLicenseFields' ] );
		$fields = ap_product_license_fields();
		$this->assertEmpty( $fields );
		$this->assertIsArray( $fields );
		$this->assertArrayNotHasKey( 'product', $fields );
	}
}
