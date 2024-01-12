<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestPostStatus extends TestCase {

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Post_Status', 'register_post_status' ) );
		$this->assertTrue( method_exists( 'AnsPress_Post_Status', 'change_post_status' ) );
	}

	public function TestHooks() {
		$this->assertEquals( 10, has_action( 'init', [ 'AnsPress_Post_Status', 'register_post_status' ] ) );
	}

	public function testRegisterPostStatuses() {
		\AnsPress_Post_Status::register_post_status();
		global $wp_post_statuses;

		$this->assertArrayHasKey( 'moderate', $wp_post_statuses );
		$this->assertArrayHasKey( 'private_post', $wp_post_statuses );
	}
}
