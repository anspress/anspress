<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;
use AnsPress\Tests\Testcases\Common;

class Test_Roles extends TestCase {

	/**
	 * @covers ::ap_role_caps
	 */
	public function testApRoleCaps() {
		// Check participant caps.
		$caps = ap_role_caps( 'participant' );
		$this->assertArrayHasKey( 'ap_read_question', $caps );
		$this->assertArrayHasKey( 'ap_read_answer', $caps );
		$this->assertArrayHasKey( 'ap_read_comment', $caps );
		$this->assertArrayHasKey( 'ap_new_question', $caps );
		$this->assertArrayHasKey( 'ap_new_answer', $caps );
		$this->assertArrayHasKey( 'ap_new_comment', $caps );
		$this->assertArrayHasKey( 'ap_edit_question', $caps );
		$this->assertArrayHasKey( 'ap_edit_answer', $caps );
		$this->assertArrayHasKey( 'ap_edit_comment', $caps );
		$this->assertArrayHasKey( 'ap_delete_question', $caps );
		$this->assertArrayHasKey( 'ap_delete_answer', $caps );
		$this->assertArrayHasKey( 'ap_delete_comment', $caps );
		$this->assertArrayHasKey( 'ap_vote_up', $caps );
		$this->assertArrayHasKey( 'ap_vote_down', $caps );
		$this->assertArrayHasKey( 'ap_vote_flag', $caps );
		$this->assertArrayHasKey( 'ap_vote_close', $caps );
		$this->assertArrayHasKey( 'ap_upload_cover', $caps );
		$this->assertArrayHasKey( 'ap_change_status', $caps );

		// Check moderator caps.
		$caps = ap_role_caps( 'moderator' );
		$this->assertArrayHasKey( 'ap_edit_others_question', $caps );
		$this->assertArrayHasKey( 'ap_edit_others_answer', $caps );
		$this->assertArrayHasKey( 'ap_edit_others_comment', $caps );
		$this->assertArrayHasKey( 'ap_delete_others_question', $caps );
		$this->assertArrayHasKey( 'ap_delete_others_answer', $caps );
		$this->assertArrayHasKey( 'ap_delete_others_comment', $caps );
		$this->assertArrayHasKey( 'ap_delete_post_permanent', $caps );
		$this->assertArrayHasKey( 'ap_view_private', $caps );
		$this->assertArrayHasKey( 'ap_view_moderate', $caps );
		$this->assertArrayHasKey( 'ap_change_status_other', $caps );
		$this->assertArrayHasKey( 'ap_approve_comment', $caps );
		$this->assertArrayHasKey( 'ap_no_moderation', $caps );
		$this->assertArrayHasKey( 'ap_restore_posts', $caps );
		$this->assertArrayHasKey( 'ap_toggle_featured', $caps );
		$this->assertArrayHasKey( 'ap_toggle_best_answer', $caps );

		$this->assertFalse( ap_role_caps( '' ) );
	}

	/**
	 * @covers AP_Roles::__construct
	 */
	public function testConstruct() {
		$role = new \AP_Roles();

		$participants_caps = ap_role_caps( 'participant' );
		$this->assertTrue( count( $role->base_caps ) === count( $participants_caps ) );

		$mod_caps = ap_role_caps( 'moderator' );
		$this->assertTrue( count( $role->mod_caps ) === count( $mod_caps ) );
	}

	/**
	 * @covers AP_Roles::add_roles
	 */
	public function testAddRoles() {
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_participant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_moderator' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_banned' ) );
	}

	/**
	 * @covers AP_Roles::add_capabilities
	 */
	public function testAddCapabilities() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		// Check moderator caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_moderator']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_moderator']['capabilities'] );
		}

		// Check participants caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_participant']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_participant']['capabilities'] );
		}

		// Check banned caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_banned']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_banned']['capabilities'] );
		}

		// Test administrator caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['administrator']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['administrator']['capabilities'] );
		}

		// Test editor caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['editor']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['editor']['capabilities'] );
		}

		// Test contributor caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['contributor']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['contributor']['capabilities'] );
		}

		// Test author caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['author']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['author']['capabilities'] );
		}

		// Test subscriber caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['subscriber']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['subscriber']['capabilities'] );
		}
	}

	/**
	 * @covers ::ap_user_can_ask
	 */
	public function testApUserCanAsk() {
		// Check if user roles can ask.
		Common::setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );
		Common::setRole( 'ap_participant' );
		$this->assertTrue( ap_user_can_ask() );
		Common::setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_ask() );
		Common::setRole( 'editor' );
		$this->assertTrue( ap_user_can_ask() );

		// Check user having ap_new_question can ask.
		$option = ap_opt( 'post_question_per' );
		ap_opt( 'post_question_per', 'have_cap' );
		add_role( 'ap_test_ask', 'Test user can ask', [ 'ap_new_question' => true ] );
		Common::setRole( 'ap_test_ask' );
		$this->assertTrue( ap_user_can_ask() );
		Common::logout();
		$this->assertFalse( ap_user_can_ask() );

		// Verify anyone can ask option.
		ap_opt( 'post_question_per', 'anyone' );
		$this->assertTrue( ap_user_can_ask() );
		Common::setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );

		// Check logged-in can ask permission.
		ap_opt( 'post_question_per', 'logged_in' );
		Common::logout();
		$this->assertFalse( ap_user_can_ask() );
		Common::setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );
	}
}
