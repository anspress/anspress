<?php

class RoleTest extends \Codeception\TestCase\WPTestCase
{

	protected $_roles;
	protected $_mod_caps;
	protected $_normal_caps;

	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] ); }
	}

	public function setUp() {

		// before
		parent::setUp();

		$this->_roles = new AP_Roles;
		$this->_mod_caps = array(
			'ap_edit_others_question'   => true,
			'ap_edit_others_answer'     => true,
			'ap_edit_others_comment'    => true,
			'ap_delete_others_question' => true,
			'ap_delete_others_answer'   => true,
			'ap_delete_others_comment'  => true,
			'ap_view_private'           => true,
			'ap_view_moderate'          => true,
			'ap_change_status_other'    => true,
		);

		$this->_normal_caps = array(
			'ap_read_question'          => true,
			'ap_read_answer'            => true,
			'ap_new_question'           => true,
			'ap_new_answer'             => true,
			'ap_new_comment'            => true,
			'ap_edit_question'          => true,
			'ap_edit_answer'            => true,
			'ap_edit_comment'           => true,
			'ap_delete_question'        => true,
			'ap_delete_answer'          => true,
			'ap_delete_comment'         => true,
			'ap_vote_up'                => true,
			'ap_vote_down'              => true,
			'ap_vote_flag'              => true,
			'ap_vote_close'             => true,
			'ap_upload_cover'           => true,
			'ap_change_status'          => true,
			'ap_upload_avatar'          => true,
			'ap_edit_profile'           => true,
		);
		$this->_roles->add_roles();
		$this->_roles->add_capabilities();
	}

	public function tearDown() {

		// your tear down methods here
		// then
		parent::tearDown();
	}

	public function test_roles() {
		global $wp_roles;
		$this->assertArrayHasKey('ap_moderator', (array) $wp_roles->role_names );
		$this->assertArrayHasKey('ap_participant', (array) $wp_roles->role_names );
		$this->assertArrayHasKey('ap_banned', (array) $wp_roles->role_names );
	}

	public function test_ap_moderator_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_moderator']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_moderator']['capabilities'] ); }
	}

	public function test_ap_participant_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_participant']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['ap_participant']['capabilities'] ); }
	}

	public function test_ap_banned_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_banned']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['ap_banned']['capabilities'] ); }
	}

	public function test_administrator_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['administrator']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['administrator']['capabilities'] ); }
	}

	public function test_editor_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['editor']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['editor']['capabilities'] ); }
	}

	public function test_contributor_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['contributor']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['contributor']['capabilities'] ); }
	}

	public function test_author_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['author']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['author']['capabilities'] ); }
	}

	public function test_subscriber_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		foreach ( $this->_mod_caps as $c => $val ) {
			$this->assertArrayNotHasKey( $c, (array) $wp_roles->roles['subscriber']['capabilities'] ); }

		foreach ( $this->_normal_caps as $c => $val ) {
			$this->assertArrayHasKey( $c, (array) $wp_roles->roles['subscriber']['capabilities'] ); }
	}

	public function test_user_can_ask() {
		$this->_setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );
		$this->_setRole( 'ap_participant' );
		$this->assertTrue( ap_user_can_ask() );
		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_ask() );
		$this->_setRole( 'editor' );
		$this->assertTrue( ap_user_can_ask() );
		$this->_setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_ask() );
	}

	public function test_anonymous_can_ask() {
		$this->logout();
		ap_opt('allow_anonymous', true );
		$this->assertTrue( ap_user_can_ask() );
	}

	public function test_user_can_answer() {
		$this->_setRole( 'subscriber' );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test question', 'post_type' => 'question', 'post_status' => 'publish' ) );

		ap_opt('disallow_op_to_answer', true );
		$this->assertTrue( ap_user_can_answer($post_id ) );

		ap_opt('disallow_op_to_answer', false );
		$this->assertFalse( ap_user_can_answer($post_id ) );

		$this->_setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_answer($post_id ) );
		$this->assertTrue( ap_user_can_answer($post_id ) );

		ap_opt( 'only_admin_can_answer', true );
		$this->assertFalse( ap_user_can_answer($post_id ) );

		$this->_setRole( 'subscriber' );
		ap_opt( 'only_admin_can_answer', false );
		$this->assertTrue( ap_user_can_answer($post_id ) );

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'closed' ) );

		$this->_setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_answer($post_id ) );
	}

	public function test_anonymous_can_answer() {
		$this->_setRole( 'subscriber' );
		ap_opt('allow_anonymous', 'true' );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );
		$this->logout();
		$this->assertTrue( ap_user_can_answer($post_id ) );
	}

	public function test_can_answer_on_private() {
        $this->_setRole( 'subscriber' );
        $post_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'private_post' ) );
        $this->logout();
        $this->_setRole( 'subscriber' );
        $this->assertFalse( ap_user_can_answer($post_id ) );
    }

    public function test_user_can_select_answer() {
        $this->_setRole( 'subscriber' );
        $asker_id = get_current_user_id();
        $question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );     
        $this->logout();
        $this->_setRole( 'subscriber' );
        $answer_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id ) );
        $this->assertFalse( ap_user_can_select_answer($answer_id ) );
        $this->logout();
        $this->assertTrue( ap_user_can_select_answer( $answer_id, $asker_id ) );
        $this->_setRole( 'administrator' );
        $this->assertTrue( ap_user_can_select_answer( $answer_id ) );
    }

    public function test_user_can_edit_answer() {
		$this->_setRole( 'subscriber' );
        $asker_id = get_current_user_id();
        $answer_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'answer', 'post_status' => 'publish' ) );
        $this->assertTrue( ap_user_can_edit_answer( $answer_id ) );
        wp_update_post( ['ID' => $answer_id, 'post_status' => 'moderate'] );
        $this->assertFalse( ap_user_can_edit_answer( $answer_id ) );
        wp_update_post( ['ID' => $answer_id, 'post_status' => 'private_post'] );
		$this->assertTrue( ap_user_can_edit_answer( $answer_id ) );
	}

}
