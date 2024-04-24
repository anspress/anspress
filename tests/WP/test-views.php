<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestViews extends TestCase {

	use TestCases\Common;

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Views', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'insert_views' ) );
		$this->assertTrue( method_exists( 'AnsPress_Views', 'delete_views' ) );
	}

	/**
	 * @covers AnsPress_Views::init
	 */
	public function testInit() {
		\AnsPress_Views::init();
		$this->assertEquals( 10, has_action( 'shutdown', [ 'AnsPress_Views', 'insert_views' ] ) );
		$this->assertEquals( 10, has_action( 'ap_before_delete_question', [ 'AnsPress_Vote', 'delete_votes' ] ) );
	}

	/**
	 * @covers ::ap_insert_views
	 * @covers ::ap_is_viewed
	 * @covers ::ap_get_views
	 */
	public function testInsertGetViewsViewed() {
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

	/**
	 * @covers AnsPress_Views::insert_views
	 * @covers AnsPress_Views::delete_views
	 */
	public function testInsertViews() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_views}" );

		// Test 1.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();

		// Test for AnsPress_Views::insert_views.
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );

		// Test without defining specific views.
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( 1, $qameta->views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );

		// Test with defining specific views.
		ap_insert_qameta( $id, array( 'views' => 7 ) );
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( 8, $qameta->views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );

		// Test 2.
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		$id = $this->insert_question();

		// Test for AnsPress_Views::insert_views.
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );

		// Test without defining specific views.
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( 1, $qameta->views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );

		// Test with defining specific views.
		ap_insert_qameta( $id, array( 'views' => 7 ) );
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( 8, $qameta->views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );

		// Add filter.
		add_filter( 'ap_insert_view_to_db', '__return_true' );
		// Test 3.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();

		// Test for AnsPress_Views::insert_views.
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );

		// Test without defining specific views.
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 0, $views );

		// Test 4.
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $user_id );
		$id = $this->insert_question();

		// Test for AnsPress_Views::insert_views.
		$this->go_to( '/?post_type=question&p=' . $id );
		\AnsPress_Views::insert_views( '' );

		// Test without defining specific views.
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 1, $views );

		// Test for AnsPress_Views::delete_views.
		$result = \AnsPress_Views::delete_views( $id );
		$this->assertNull( $result );
		$views = ap_get_views( $id );
		$this->assertIsInt( $views );
		$this->assertEquals( 0, $views );

		// Add filter.
		add_filter( 'ap_insert_view_to_db', '__return_false' );
	}

	/**
	 * @covers ::ap_insert_views
	 */
	public function testAPInsertViewsEmptyRefID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_views}" );

		// Test.
		$this->setRole( 'subscriber' );
		$result = ap_insert_views( '' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers ::ap_is_viewed
	 */
	public function testAPIsViewedEmptyRefID() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_views}" );

		// Test.
		$this->setRole( 'subscriber' );
		$result = ap_is_viewed( '', get_current_user_id() );
		$this->assertFalse( $result );
	}

	/**
	 * @covers ::ap_is_viewed
	 */
	public function testAPIsViewedIPSetOtherThanFalse() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_views}" );

		// Test 1.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		ap_insert_views( $id, 'question', get_current_user_id(), '127.0.0.1' );
		$result = ap_is_viewed( $id, get_current_user_id(), 'question', '127.0.0.1' );
		$this->assertTrue( $result );

		// Test 2.
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$id = $this->insert_question();
		ap_insert_views( $id, 'question', $user_id, '127.0.0.1' );
		$result = ap_is_viewed( $id, $user_id, 'question', 'localhost' );
		$this->assertFalse( $result );
	}
}
