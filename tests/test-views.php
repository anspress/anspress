<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestViews extends TestCase {

	public function testInit() {
		$this->assertEquals( 10, has_action( 'shutdown', [ 'AnsPress_Views', 'insert_views' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Views', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'insert_views' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'delete_views' ) );
	}
}
