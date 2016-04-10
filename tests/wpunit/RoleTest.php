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

	public function test_user_can_edit_question() {
		$this->_setRole( 'subscriber' );
		$asker_id = get_current_user_id();
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertTrue( ap_user_can_edit_question( $question_id ) );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_edit_question( $question_id ) );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertTrue( ap_user_can_edit_question( $question_id ) );

		$this->logout();

		$this->_setRole( 'subscriber' );

		// Check if other user can edit private question.
		$this->assertFalse( ap_user_can_edit_question( $question_id ) );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'publish' ] );
		$this->assertFalse( ap_user_can_edit_question( $question_id ) );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_edit_question( $question_id ) );

	}

	public function test_user_can_edit_answer() {
		$this->_setRole( 'subscriber' );
		$asker_id = get_current_user_id();
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->logout();
		$this->_setRole( 'subscriber' );

		$answerer_id = get_current_user_id();
		$answer_id = $this->factory->post->create( array( 'post_title' => 'Test answer for another', 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id ) );
		
		$this->assertTrue( ap_user_can_edit_answer( $answer_id ), 'User should be able to edit his answer' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_edit_answer( $answer_id ), 'User shouldn\'t be able to edit answer if question is private' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_edit_answer( $answer_id ), 'User shouldn\'t be able to edit answer if question is moderate' );

		// Publish question again.
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'publish' ] );

		// Moderate answer.
		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_edit_answer( $answer_id ), 'User shouldn\'t be able to edit answer if question is moderate' );

		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'private_post' ] );
		$this->assertTrue( ap_user_can_edit_answer( $answer_id ), 'User should be able to edit their private answer' );

		// Logout and check if 3rd user can edit answer.
		$this->logout();
		$this->_setRole( 'subscriber' );		
		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_edit_answer( $answer_id ), '3rd user shouldn\'t be able to edit others private answer' );
		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'publish' ] );
		$this->assertFalse( ap_user_can_edit_answer( $answer_id ), '3rd user shouldn\'t be able to edit others answer' );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_edit_answer( $answer_id, get_current_user_id() ), 'Moderator should be able to edit answer' );
	}

	public function test_user_can_comment() {
		$this->_setRole( 'subscriber' );

		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertTrue( ap_user_can_comment( $question_id ), 'User should be able to edit their comment' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_comment( $question_id ), 'User shouldn\'t be able to edit their comment if question is moderate' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'closed' ] );
		$this->assertTrue( ap_user_can_comment( $question_id ), 'User should be able to edit their comment if question is closed' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertTrue( ap_user_can_comment( $question_id ), 'User should be able to edit their comment if his question is private' );

		$this->logout();
		$this->_setRole( 'subscriber' );

		// Check if other user can comment on private question.
		$this->assertFalse( ap_user_can_comment( $question_id ), '2nd user shouldn\'t be able to edit their comment if question is private' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'publish' ] );
		$this->assertTrue( ap_user_can_comment( $question_id ), '2nd user should be able to edit their comment if question is publish' );

		$answer_id = $this->factory->post->create( array( 'post_title' => 'Test answer another', 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $question_id ) );

		$this->assertTrue( ap_user_can_comment( $answer_id ) );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_comment( $answer_id ), 'User shouldn\'t be able to comment on private question\'s answer.' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_comment( $answer_id ), 'User shouldn\'t be able to comment on moderate question\'s answer.' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'publish' ] );
		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_comment( $answer_id ), 'User shouldn\'t be able to comment on moderate answer.' );

		$this->_setRole( 'subscriber' );
		wp_update_post( [ 'ID' => $answer_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_comment( $answer_id ), '3rd user shouldn\'t be able to comment on private answer.' );
	}

	public function test_user_can_delete_comment() {
		$this->_setRole( 'subscriber' );

		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$comment_id = $this->factory->comment->create( array( 'comment_content' => 'Test question comment', 'comment_post_ID' => $question_id, 'user_id' => get_current_user_id() ) );

		$this->assertTrue( ap_user_can_delete_comment( $comment_id ), 'User should be able to delete their own comment' );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_delete_comment( $comment_id ), 'Moderator should be able to delete comment' );
		$this->_setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_delete_comment( $comment_id ), '3rd user shouldn\'t be be able to delete comment' );
	}

	function test_user_can_delete_post(){
		$this->_setRole( 'subscriber' );

		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertTrue( ap_user_can_delete_post( $question_id ), 'User should be able to delete their own post' );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_delete_post( $question_id ), 'Moderator should be able to delete any post' );
		$this->_setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_delete_post( $question_id ), 'User shouldn\'t be able to delete others post' );
	}

	function test_user_can_read_post(){
		$this->_setRole( 'subscriber' );

		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertTrue( ap_user_can_read_post( $question_id ), 'User should be able to read their own post' );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'trash' ] );
		$this->assertFalse( ap_user_can_read_post( $question_id ), 'User shouldn\'t be able to read trashed post' );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertTrue( ap_user_can_read_post( $question_id ), 'User should be able to read their private post' );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertTrue( ap_user_can_read_post( $question_id ), 'User should be able to read their moderate post' );

		$this->_setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_delete_post( $question_id ), 'User shouldn\'t be able to read others moderate post' );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'trash' ] );
		$this->assertFalse( ap_user_can_read_post( $question_id ), 'User shouldn\'t be able to read trashed post' );
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_read_post( $question_id ), 'User shouldn\'t be able to read others private post' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'publish' ] );
		$this->assertTrue( ap_user_can_read_post( $question_id ), 'User should be able to read others post' );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ), 'Moderator should be able to read others post' );
	}

	function test_user_can_change_status(){
		$this->_setRole( 'subscriber' );
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertTrue( ap_user_can_change_status( $question_id ), 'User should be able to change their question status' );

		$this->_setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_change_status( $question_id ), 'Moderator should be able to change question status' );

		$this->_setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_change_status( $question_id ), 'Subscriber shouldn\'t be able to change others question status' );
	}

	function test_user_can_vote_on_post(){
		$this->_setRole( 'subscriber' );
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ), 'Subscriber shouldn\'t be able to vote up on thier posts' );
		
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ), 'Subscriber shouldn\'t be able to vote down on thier posts' );

		$this->_setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ), 'Subscriber should be able to vote up on others posts' );
		
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ), 'Subscriber should be able to vote down on others posts' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'private_post' ] );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ), 'Subscriber shouldn\'t be able to vote up on private posts' );

		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ), 'Subscriber shouldn\'t be able to vote up on moderate posts' );

		$this->_setRole( 'administrator' );
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another 1', 'post_type' => 'question', 'post_status' => 'publish' ) );
		
		$this->assertTrue( ap_user_can_change_status( $question_id ), 'Administrator should be able to vote on own posts' );
	}
}
