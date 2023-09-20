<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestUpload extends TestCase {

	public function testHooks() {
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Uploader', 'create_single_schedule' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'delete_attachment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'deleted_attachment' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'create_single_schedule' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'cron_delete_temp_attachments' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'upload_modal' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'image_upload' ) );
		$this->assertTrue( method_exists( 'AnsPress_Uploader', 'image_sizes_advanced' ) );
	}

	public function allowedMimes( $mimes ) {
		$mimes['ico'] = 'image/x-icon';
		$mimes['pdf'] = 'application/pdf';
		unset( $mimes['gif'] );
		unset( $mimes['png'] );
		return $mimes;
	}

	/**
	 * @covers ::ap_allowed_mimes
	 */
	public function testAPAllowedMimes() {
		$this->assertArrayHasKey( 'jpg|jpeg', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'png', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'doc|docx', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'xls', ap_allowed_mimes() );

		// Test for adding filter.
		add_filter( 'ap_allowed_mimes', [ $this, 'allowedMimes' ] );
		$this->assertArrayHasKey( 'ico', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'pdf', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'png', ap_allowed_mimes() );
		remove_filter( 'ap_allowed_mimes', [ $this, 'allowedMimes' ] );
		$this->assertArrayNotHasKey( 'ico', ap_allowed_mimes() );
		$this->assertArrayNotHasKey( 'pdf', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'gif', ap_allowed_mimes() );
		$this->assertArrayHasKey( 'png', ap_allowed_mimes() );
	}
}
