<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestUpload extends TestCase {

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
