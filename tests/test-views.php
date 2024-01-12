<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestViews extends TestCase {

	use TestCases\Common;

	public function testInit() {
		$this->assertEquals( 10, has_action( 'shutdown', [ 'AnsPress_Views', 'insert_views' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Views', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'insert_views' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'delete_views' ) );
	}

	/**
	 * @covers ::ap_insert_views
	 * @covers ::ap_is_viewed
	 * @covers ::ap_get_views
	 */
	public function testInsertViews() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_views}" );

		// Test 1.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();

		// For ap_insert_views.
		$insert_view = ap_insert_views( $id );
		$this->assertIsInt( $insert_view );
		$this->assertGreaterThan( 0, $insert_view );

		// For ap_is_viewed.
		$is_viewed = ap_is_viewed( $id, get_current_user_id() );
		$this->assertTrue( $is_viewed );

		// For ap_get_views.
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );

		// Test after adding another view.
		$insert_view = ap_insert_views( $id );
		$this->assertFalse( $insert_view );
		$is_viewed = ap_is_viewed( $id, get_current_user_id() );
		$this->assertTrue( $is_viewed );
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );

		// Test 2.
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		$id = $this->insert_question();

		// For ap_insert_views.
		$insert_view = ap_insert_views( $id, '', $user_id );
		$this->assertIsInt( $insert_view );
		$this->assertGreaterThan( 0, $insert_view );

		// For ap_is_viewed.
		$is_viewed = ap_is_viewed( $id, $user_id );
		$this->assertTrue( $is_viewed );

		// For ap_get_views.
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );

		// Test after adding another view.
		$insert_view = ap_insert_views( $id, '', $user_id );
		$this->assertFalse( $insert_view );
		$is_viewed = ap_is_viewed( $id, $user_id );
		$this->assertTrue( $is_viewed );
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );
	}
}
