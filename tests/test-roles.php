<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Roles extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AP_Roles' );
		$this->assertTrue( $class->hasProperty( 'base_caps' ) && $class->getProperty( 'base_caps' )->isPublic() );
		$this->assertTrue( $class->hasProperty( 'mod_caps' ) && $class->getProperty( 'mod_caps' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AP_Roles', '__construct' ) );
		$this->assertTrue( method_exists( 'AP_Roles', 'add_roles' ) );
		$this->assertTrue( method_exists( 'AP_Roles', 'add_capabilities' ) );
		$this->assertTrue( method_exists( 'AP_Roles', 'remove_roles' ) );
	}

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
		$this->assertArrayHasKey( 'ap_view_future', $caps );
		$this->assertArrayHasKey( 'ap_change_status_other', $caps );
		$this->assertArrayHasKey( 'ap_approve_comment', $caps );
		$this->assertArrayHasKey( 'ap_no_moderation', $caps );
		$this->assertArrayHasKey( 'ap_restore_posts', $caps );
		$this->assertArrayHasKey( 'ap_toggle_featured', $caps );
		$this->assertArrayHasKey( 'ap_toggle_best_answer', $caps );
		$this->assertArrayHasKey( 'ap_close_question', $caps );

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
		global $wp_roles;
		// Remove roles.
		$role = new \AP_Roles();
		$role->remove_roles();
		$this->assertFalse( isset( $wp_roles->roles['ap_participant'] ) );
		$this->assertFalse( isset( $wp_roles->roles['ap_moderator'] ) );
		$this->assertFalse( isset( $wp_roles->roles['ap_banned'] ) );

		// Call the method and test.
		$role->add_roles();
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_participant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_moderator' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_banned' ) );
	}

	/**
	 * @covers AP_Roles::remove_roles
	 */
	public function testRemoveRoles() {
		global $wp_roles;
		// Add roles.
		$role = new \AP_Roles();
		$role->add_roles();
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_participant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_moderator' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'ap_banned' ) );

		// Call the method and test.
		$role->remove_roles();
		$this->assertFalse( isset( $wp_roles->roles['ap_participant'] ) );
		$this->assertFalse( isset( $wp_roles->roles['ap_moderator'] ) );
		$this->assertFalse( isset( $wp_roles->roles['ap_banned'] ) );

		// Re-add roles since it hampers other tests.
		add_role( 'ap_moderator', 'AnsPress Moderator', ap_role_caps( 'moderator' ) );
		add_role( 'ap_participant', 'AnsPress Participants', ap_role_caps( 'participant' ) );
		add_role( 'ap_banned', 'AnsPress Banned', [ 'read' => true ] );
	}

	/**
	 * @covers AP_Roles::add_capabilities
	 */
	public function testAddCapabilities() {
		global $wp_roles;

		(new \AP_Roles())->add_roles();
		(new \AP_Roles())->add_capabilities();

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles(); }
		}

		// Check moderator caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['ap_moderator']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['ap_moderator']['capabilities'] );
		}

		// Check participants caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['ap_participant']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['ap_participant']['capabilities'] );
		}

		// Check banned caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['ap_banned']['capabilities'] );
		}

		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['ap_banned']['capabilities'] );
		}

		// Test administrator caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['administrator']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['administrator']['capabilities'] );
		}

		// Test editor caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['editor']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['editor']['capabilities'] );
		}

		// Test contributor caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['contributor']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['contributor']['capabilities'] );
		}

		// Test author caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['author']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['author']['capabilities'] );
		}

		// Test subscriber caps.
		foreach ( ap_role_caps( 'moderator' ) as $c => $val ) {
			$this->assertArrayNotHasKey( $c, $wp_roles->roles['subscriber']['capabilities'] );
		}
		foreach ( ap_role_caps( 'participant' ) as $c => $val ) {
			$this->assertArrayHasKey( $c, $wp_roles->roles['subscriber']['capabilities'] );
		}
	}

	/**
	 * @covers ::ap_user_can_ask
	 */
	public function testApUserCanAsk() {
		// Check if user roles can ask.
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );
		$this->setRole( 'ap_participant' );
		$this->assertTrue( ap_user_can_ask() );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_ask() );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_ask() );

		// Check user having ap_new_question can ask.
		$option = ap_opt( 'post_question_per' );
		ap_opt( 'post_question_per', 'have_cap' );
		add_role( 'ap_test_ask', 'Test user can ask', [ 'ap_new_question' => true ] );
		$this->setRole( 'ap_test_ask' );
		$this->assertTrue( ap_user_can_ask() );
		$this->logout();
		$this->assertFalse( ap_user_can_ask() );

		// Verify anyone can ask option.
		ap_opt( 'post_question_per', 'anyone' );
		$this->assertTrue( ap_user_can_ask() );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );

		// Check logged-in can ask permission.
		ap_opt( 'post_question_per', 'logged_in' );
		$this->logout();
		$this->assertFalse( ap_user_can_ask() );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_ask() );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswer() {
		$question_id = $this->insert_question();
		$post_id     = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$page_id     = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'page',
			)
		);

		// Check if user roles can answer.
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_answer( $question_id ) );
		$this->assertFalse( ap_user_can_answer( $post_id ) );
		$this->assertFalse( ap_user_can_answer( $page_id ) );

		// Check for the answer selected option.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
			)
		);
		ap_opt( 'disallow_op_to_answer', false );
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		ap_set_selected_answer( $qid, $aid );
		$this->assertFalse( ap_user_can_answer( $qid, $user_id ) );

		// Check for the original poster can answer.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		ap_opt( 'disallow_op_to_answer', true );
		$this->assertFalse( ap_user_can_answer( $qid, $user_id ) );
		// If trying via a new user.
		$new_user = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user );
		$this->assertTrue( ap_user_can_answer( $qid, $new_user ) );
		ap_opt( 'disallow_op_to_answer', false );
		$this->assertTrue( ap_user_can_answer( $qid, $new_user ) );
		ap_opt( 'disallow_op_to_answer', true );
		$this->assertTrue( ap_user_can_answer( $qid, $new_user ) );
		ap_opt( 'disallow_op_to_answer', false );

		// Check for multiple answer posted by the author can answer again.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		ap_opt( 'multiple_answers', false );
		$this->assertFalse( ap_user_can_answer( $qid, $user_id ) );
		ap_opt( 'multiple_answers', true );
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );

		// Check user having ap_new_answer can answer the question.
		ap_opt( 'post_answer_per', 'have_cap' );
		add_role( 'ap_test_can_answer', 'Test user can answer', [ 'ap_new_answer' => true ] );
		$this->setRole( 'ap_test_can_answer' );
		$this->assertTrue( ap_user_can_answer( $question_id ) );
		$this->logout();
		$this->assertFalse( ap_user_can_answer( $question_id ) );

		// Check anyone can answer.
		ap_opt( 'post_answer_per', 'anyone' );
		$this->assertTrue( ap_user_can_answer( $question_id ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_answer( $question_id ) );

		// Check logged-in can answer.
		ap_opt( 'post_answer_per', 'logged_in' );
		$this->logout();
		$this->assertFalse( ap_user_can_answer( $question_id ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_answer( $question_id ) );
	}

	/**
	 * @covers ::ap_user_can_select_answer
	 */
	public function testAPUserCanSelectAnswer() {
		$id = $this->insert_answer();

		// Check if user roles can select answer.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_select_answer( $id->a ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_select_answer( $id->a ) );
		$this->setRole( 'ap_participants' );
		$this->assertFalse( ap_user_can_select_answer( $id->a ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_select_answer( $id->a ) );
		$this->logout();
		$this->assertFalse( ap_user_can_select_answer( $id->a ) );

		// Test for new role.
		add_role( 'ap_test_can_select_answer', 'Test user can select answer', [ 'ap_toggle_best_answer' => true ] );
		$this->setRole( 'ap_test_can_select_answer' );
		$this->assertTrue( ap_user_can_select_answer( $id->a ) );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 * @covers ::ap_user_can_edit_question
	 * @covers ::ap_user_can_edit_answer
	 */
	public function testAPUserCanEditPostQuestionAnswer() {
		$id = $this->insert_answer();
		// Check if user roles can edit question and answer.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_edit_post( $id->q ) );
		$this->assertFalse( ap_user_can_edit_post( $id->a ) );
		$this->assertFalse( ap_user_can_edit_question( $id->q ) );
		$this->assertFalse( ap_user_can_edit_answer( $id->a ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_edit_post( $id->q ) );
		$this->assertTrue( ap_user_can_edit_post( $id->a ) );
		$this->assertTrue( ap_user_can_edit_question( $id->q ) );
		$this->assertTrue( ap_user_can_edit_answer( $id->a ) );

		// Test for the question and answer edit capability for the same user.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_edit_post( $qid ) );
		$this->assertTrue( ap_user_can_edit_post( $aid ) );
		$this->assertTrue( ap_user_can_edit_question( $qid ) );
		$this->assertTrue( ap_user_can_edit_answer( $aid ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_edit_post( $qid ) );
		$this->assertFalse( ap_user_can_edit_post( $aid ) );
		$this->assertFalse( ap_user_can_edit_question( $qid ) );
		$this->assertFalse( ap_user_can_edit_answer( $aid ) );

		// Test for new role.
		add_role(
			'ap_test_can_edit_post_question_answer',
			'Test user can edit post, question, and answer',
			[
				'ap_edit_others_question' => true,
				'ap_edit_others_answer' => true,
			]
		);
		$this->setRole( 'ap_test_can_edit_post_question_answer' );
		$this->assertTrue( ap_user_can_edit_post( $id->q ) );
		$this->assertTrue( ap_user_can_edit_post( $id->a ) );
		$this->assertTrue( ap_user_can_edit_question( $id->q ) );
		$this->assertTrue( ap_user_can_edit_answer( $id->a ) );
		$this->logout();
		$this->assertFalse( ap_user_can_edit_post( $id->q ) );
		$this->assertFalse( ap_user_can_edit_post( $id->a ) );
		$this->assertFalse( ap_user_can_edit_question( $id->q ) );
		$this->assertFalse( ap_user_can_edit_answer( $id->a ) );
	}

	/**
	 * @covers ::ap_user_can_change_label
	 */
	public function testAPUserCanChangeLabel() {
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'ap_moderator' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'author' );
		$this->assertFalse( ap_user_can_change_label() );
		$this->setRole( 'editor' );
		$this->assertFalse( ap_user_can_change_label() );
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->assertFalse( ap_user_can_change_label() );

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->assertFalse( ap_user_can_change_label() );
			grant_super_admin( $user_id );
			$this->assertTrue( ap_user_can_change_label() );
		} else {
			$this->setRole( 'administrator' );
			$this->assertTrue( ap_user_can_change_label() );
		}

		// Test for new role.
		add_role( 'ap_test_can_change_label', 'Test user can change label', [ 'ap_change_label' => true ] );
		$this->setRole( 'ap_test_can_change_label' );
		$this->assertTrue( ap_user_can_change_label() );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanComment() {
		$id = $this->insert_answer();
		// Check if user roles can comment on question and answer.
		$this->assertFalse( ap_user_can_comment( $id->q ) );
		$this->assertFalse( ap_user_can_comment( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_comment( $id->q ) );
		$this->assertTrue( ap_user_can_comment( $id->a ) );

		// Check user having ap_new_comment can answer the question.
		ap_opt( 'post_comment_per', 'have_cap' );
		add_role( 'ap_test_can_comment', 'Test user can answer', [ 'ap_new_comment' => true ] );
		$this->setRole( 'ap_test_can_comment' );
		$this->assertTrue( ap_user_can_comment( $id->q ) );
		$this->assertTrue( ap_user_can_comment( $id->a ) );
		$this->logout();
		$this->assertFalse( ap_user_can_comment( $id->q ) );
		$this->assertFalse( ap_user_can_comment( $id->a ) );

		// Check anyone can answer.
		ap_opt( 'post_comment_per', 'anyone' );
		$this->assertTrue( ap_user_can_comment( $id->q ) );
		$this->assertTrue( ap_user_can_comment( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_comment( $id->q ) );
		$this->assertTrue( ap_user_can_comment( $id->a ) );

		// Check logged-in can answer.
		ap_opt( 'post_comment_per', 'logged_in' );
		$this->logout();
		$this->assertFalse( ap_user_can_comment( $id->q ) );
		$this->assertFalse( ap_user_can_comment( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_comment( $id->q ) );
		$this->assertTrue( ap_user_can_comment( $id->a ) );
	}

	/**
	 * @covers ::ap_user_can_edit_comment
	 */
	public function testAPUserCanEditComment() {
		$id   = $this->insert_answer();
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$this->assertFalse( ap_user_can_edit_comment( $cqid ) );
		$this->assertFalse( ap_user_can_edit_comment( $caid ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_edit_comment( $cqid ) );
		$this->assertFalse( ap_user_can_edit_comment( $caid ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_edit_comment( $cqid ) );
		$this->assertTrue( ap_user_can_edit_comment( $caid ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_edit_comment( $cqid ) );
		$this->assertTrue( ap_user_can_edit_comment( $caid ) );

		// Test for new role.
		add_role( 'ap_test_can_edit_comment', 'Test user can edit comment', [ 'ap_edit_others_comment' => true ] );
		$this->setRole( 'ap_test_can_edit_comment' );
		$this->assertTrue( ap_user_can_edit_comment( $cqid ) );
		$this->assertTrue( ap_user_can_edit_comment( $caid ) );
		$this->logout();

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_edit_comment( $cqid, $user_id ) );
		$this->assertTrue( ap_user_can_edit_comment( $caid, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_edit_comment( $cqid, $new_user_id ) );
		$this->assertFalse( ap_user_can_edit_comment( $caid, $new_user_id ) );
	}

	/**
	 * @covers ::ap_user_can_delete_comment
	 */
	public function testAPUserCanDeleteComment() {
		$id   = $this->insert_answer();
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$this->assertFalse( ap_user_can_delete_comment( $cqid ) );
		$this->assertFalse( ap_user_can_delete_comment( $caid ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_delete_comment( $cqid ) );
		$this->assertFalse( ap_user_can_delete_comment( $caid ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_delete_comment( $cqid ) );
		$this->assertTrue( ap_user_can_delete_comment( $caid ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_delete_comment( $cqid ) );
		$this->assertTrue( ap_user_can_delete_comment( $caid ) );

		// Test for new role.
		add_role( 'ap_test_can_delete_comment', 'Test user can delete comment', [ 'ap_delete_others_comment' => true ] );
		$this->setRole( 'ap_test_can_delete_comment' );
		$this->assertTrue( ap_user_can_delete_comment( $cqid ) );
		$this->assertTrue( ap_user_can_delete_comment( $caid ) );
		$this->logout();

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_delete_comment( $cqid, $user_id ) );
		$this->assertTrue( ap_user_can_delete_comment( $caid, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_delete_comment( $cqid, $new_user_id ) );
		$this->assertFalse( ap_user_can_delete_comment( $caid, $new_user_id ) );
	}

	/**
	 * @covers ::ap_user_can_delete_post
	 * @covers ::ap_user_can_delete_question
	 * @covers ::ap_user_can_delete_answer
	 */
	public function testAPUserCanDeletePostQuestionAnswer() {
		$id = $this->insert_answer();
		// Check if user roles can delete question and answer.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_delete_post( $id->q ) );
		$this->assertFalse( ap_user_can_delete_post( $id->a ) );
		$this->assertFalse( ap_user_can_delete_question( $id->q ) );
		$this->assertFalse( ap_user_can_delete_answer( $id->a ) );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_delete_post( $id->q ) );
		$this->assertFalse( ap_user_can_delete_post( $id->a ) );
		$this->assertFalse( ap_user_can_delete_question( $id->q ) );
		$this->assertFalse( ap_user_can_delete_answer( $id->a ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_delete_post( $id->q ) );
		$this->assertTrue( ap_user_can_delete_post( $id->a ) );
		$this->assertTrue( ap_user_can_delete_question( $id->q ) );
		$this->assertTrue( ap_user_can_delete_answer( $id->a ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_delete_post( $id->q ) );
		$this->assertTrue( ap_user_can_delete_post( $id->a ) );
		$this->assertTrue( ap_user_can_delete_question( $id->q ) );
		$this->assertTrue( ap_user_can_delete_answer( $id->a ) );

		// Test for new role.
		add_role(
			'ap_test_can_delete_post_question_answer',
			'Test user can delete post, question, and answer',
			[
				'ap_delete_others_post'     => true,
				'ap_delete_others_question' => true,
				'ap_delete_others_answer'   => true,
			]
		);
		$this->setRole( 'ap_test_can_delete_post_question_answer' );
		$this->assertTrue( ap_user_can_delete_post( $id->q ) );
		$this->assertTrue( ap_user_can_delete_post( $id->a ) );
		$this->assertTrue( ap_user_can_delete_question( $id->q ) );
		$this->assertTrue( ap_user_can_delete_answer( $id->a ) );
		$this->logout();

		// Test for same user delete permission.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_delete_post( $qid ) );
		$this->assertTrue( ap_user_can_delete_post( $aid ) );
		$this->assertTrue( ap_user_can_delete_question( $qid ) );
		$this->assertTrue( ap_user_can_delete_answer( $aid ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_delete_post( $qid ) );
		$this->assertFalse( ap_user_can_delete_post( $aid ) );
		$this->assertFalse( ap_user_can_delete_question( $qid ) );
		$this->assertFalse( ap_user_can_delete_answer( $aid ) );
	}

	/**
	 * @covers ::ap_user_can_permanent_delete
	 */
	public function testAPUserCanPermanentDelete() {
		$id = $this->insert_answer();
		// Check if user roles can delete question and answer permanently.
		$this->assertFalse( ap_user_can_permanent_delete( $id->q ) );
		$this->assertFalse( ap_user_can_permanent_delete( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_permanent_delete( $id->q ) );
		$this->assertFalse( ap_user_can_permanent_delete( $id->a ) );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_permanent_delete( $id->q ) );
		$this->assertFalse( ap_user_can_permanent_delete( $id->a ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_permanent_delete( $id->q ) );
		$this->assertTrue( ap_user_can_permanent_delete( $id->a ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_permanent_delete( $id->q ) );
		$this->assertTrue( ap_user_can_permanent_delete( $id->a ) );

		// Test for new role.
		add_role(
			'ap_test_can_permanent_delete',
			'Test user can permanently delete question and answer',
			[
				'ap_delete_post_permanent' => true,
			]
		);
		$this->setRole( 'ap_test_can_permanent_delete' );
		$this->assertTrue( ap_user_can_permanent_delete( $id->q ) );
		$this->assertTrue( ap_user_can_permanent_delete( $id->a ) );
		$this->logout();

		// Test for same user permanently delete permission.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_permanent_delete( $qid, $user_id ) );
		$this->assertFalse( ap_user_can_permanent_delete( $aid, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_permanent_delete( $qid, $new_user_id ) );
		$this->assertFalse( ap_user_can_permanent_delete( $aid, $new_user_id ) );
	}

	/**
	 * @covers ::ap_user_can_restore
	 */
	public function testAPUserCanRestore() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'trash',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_status'  => 'trash',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_restore( $question_id ) );
		$this->assertTrue( ap_user_can_restore( $answer_id ) );

		// For the same user test.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_status'  => 'trash',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_status'  => 'trash',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_restore( $qid, $user_id ) );
		$this->assertTrue( ap_user_can_restore( $aid, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$qid = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_status'  => 'trash',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_status'  => 'trash',
				'post_parent'  => $qid,
				'post_author'  => $user_id,
			)
		);
		$this->assertFalse( ap_user_can_restore( $qid, $new_user_id ) );
		$this->assertFalse( ap_user_can_restore( $aid, $new_user_id ) );

		// Test for new role.
		add_role( 'ap_test_can_restore', 'Test user can restore', [ 'ap_restore_posts' => true ] );
		$this->setRole( 'ap_test_can_restore' );
		$this->assertTrue( ap_user_can_restore( $question_id ) );
		$this->assertTrue( ap_user_can_restore( $answer_id ) );
		add_role( 'ap_test_cant_restore', 'Test user can\'t restore', [ 'ap_restore_posts' => false ] );
		$this->setRole( 'ap_test_cant_restore' );
		$this->assertFalse( ap_user_can_restore( $question_id ) );
		$this->assertFalse( ap_user_can_restore( $answer_id ) );

		// For other post types checks.
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'trash',
				'post_type'    => 'post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_status'  => 'trash',
				'post_type'    => 'page',
			)
		);
		$this->assertFalse( ap_user_can_restore( $post_id ) );
		$this->assertFalse( ap_user_can_restore( $page_id ) );
	}

	/**
	 * @covers ::ap_allow_anonymous
	 */
	public function testAPAllowAnonymous() {
		$this->assertTrue( ap_allow_anonymous() );
		ap_opt( 'post_question_per', 'logged_in' );
		$this->assertFalse( ap_allow_anonymous() );
		ap_opt( 'post_question_per', 'have_cap' );
		$this->assertFalse( ap_allow_anonymous() );
		ap_opt( 'post_question_per', 'anyone' );
		$this->assertTrue( ap_allow_anonymous() );
		ap_opt( 'post_question_per', '' );
		$this->assertFalse( ap_allow_anonymous() );
	}

	/**
	 * @covers ::ap_user_can_view_post
	 */
	public function testAPUserCanViewPost() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'trash',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'trash',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_parent'  => $question_id,
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertTrue( ap_user_can_view_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'publish',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_post( $answer_id ) );

		// For other post types checks.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);
		$this->assertFalse( ap_user_can_view_post( $post_id ) );
		$this->assertFalse( ap_user_can_view_post( $page_id ) );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'future',
				'post_type'    => 'post',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'future',
				'post_type'    => 'page',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertFalse( ap_user_can_view_post( $post_id ) );
		$this->assertFalse( ap_user_can_view_post( $page_id ) );
	}

	/**
	 * @covers ::ap_user_can_view_private_post
	 * @covers ::ap_user_can_view_moderate_post
	 * @covers ::ap_user_can_view_future_post
	 */
	public function testAPUserCanViewPrivateModerateFuturePost() {
		$this->setRole( 'subscriber' );
		// Test other post types for private_post post status.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
				'post_status'  => 'private_post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
				'post_status'  => 'private_post',
			)
		);
		$this->assertFalse( ap_user_can_view_private_post( $post_id ) );
		$this->assertFalse( ap_user_can_view_private_post( $page_id ) );
		// Test other post types for moderate post status.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
				'post_status'  => 'moderate',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
				'post_status'  => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_view_moderate_post( $post_id ) );
		$this->assertFalse( ap_user_can_view_moderate_post( $page_id ) );
		// Test other post types for future post status.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertFalse( ap_user_can_view_future_post( $post_id ) );
		$this->assertFalse( ap_user_can_view_future_post( $page_id ) );
		$this->logout();

		// Test for the ap_user_can_view_private_post function.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_private_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_private_post( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_view_private_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_private_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_view_private_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_private_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_view_private_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_view_private_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );

		// Test for new role.
		add_role( 'ap_test_can_view_private_post', 'Test user can view private post', [ 'ap_view_private' => true ] );
		$this->setRole( 'ap_test_can_view_private_post' );
		$this->assertTrue( ap_user_can_view_private_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );

		// Test for the question/answer creator.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_private_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_private_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_private_post( $answer_id ) );

		// Test for private_post answer viewable or not by the question author.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
			)
		);
		$this->assertTrue( ap_user_can_view_private_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );
		$other_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $other_user_id );
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
				'post_author'  => $other_user_id,
			)
		);
		$this->assertFalse( ap_user_can_view_private_post( $answer_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id, $other_user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $answer_id ) );

		// Test whether other user can view the private_post question and answer.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
				'post_author'  => $new_user_id,
			)
		);
		$this->assertFalse( ap_user_can_view_private_post( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_view_private_post( $question_id ) );
		$this->logout();

		// Test for the ap_user_can_view_moderate_post function.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_moderate_post( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_moderate_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_moderate_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_moderate_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_moderate_post( $answer_id ) );

		// Test for new role.
		add_role( 'ap_test_can_view_moderate_post', 'Test user can view moderate post', [ 'ap_view_moderate' => true ] );
		$this->setRole( 'ap_test_can_view_moderate_post' );
		$this->assertTrue( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_moderate_post( $answer_id ) );

		// Test for the question/answer creator.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_moderate_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_parent'  => $question_id,
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$this->assertFalse( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_moderate_post( $answer_id ) );
		$this->logout();

		// Test for session starage.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$session = \AnsPress\Session::init();
		$session->set_answer( $answer_id );
		$session->set_question( $question_id );
		$this->assertTrue( ap_user_can_view_moderate_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_moderate_post( $answer_id ) );

		// Test for the ap_user_can_view_future_post function.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_future_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_future_post( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_view_future_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_future_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_view_future_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_future_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_view_future_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_future_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_view_future_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_future_post( $answer_id ) );

		// Test for new role.
		add_role( 'ap_test_can_view_future_post', 'Test user can view future post', [ 'ap_view_future' => true ] );
		$this->setRole( 'ap_test_can_view_future_post' );
		$this->assertTrue( ap_user_can_view_future_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_future_post( $answer_id ) );

		// Test for the question/answer creator.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_view_future_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_future_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_view_future_post( $question_id ) );
		$this->assertFalse( ap_user_can_view_future_post( $answer_id ) );
		$this->logout();

		// Test for session starage.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
				'post_parent'  => $question_id,
			)
		);
		$session = \AnsPress\Session::init();
		$session->set_answer( $answer_id );
		$session->set_question( $question_id );
		$this->assertTrue( ap_user_can_view_future_post( $question_id ) );
		$this->assertTrue( ap_user_can_view_future_post( $answer_id ) );
	}

	/**
	 * @covers ::ap_user_can_change_status
	 */
	public function testAPUserCanChangeStatus() {
		$this->setRole( 'subscriber' );
		// Test other post types.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
			)
		);
		$this->assertFalse( ap_user_can_change_status( $post_id ) );
		$this->assertFalse( ap_user_can_change_status( $page_id ) );

		// Test for the question and answer post type.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_change_status( $question_id ) );
		$this->assertTrue( ap_user_can_change_status( $answer_id ) );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
				'post_author'  => $user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_change_status( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_change_status( $answer_id, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
				'post_author'  => $new_user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'private_post',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
			)
		);
		$this->assertFalse( ap_user_can_change_status( $question_id, $user_id ) );
		$this->assertFalse( ap_user_can_change_status( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_change_status( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_change_status( $answer_id, $new_user_id ) );

		// Test for moderate post status.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_change_status( $question_id ) );
		$this->assertFalse( ap_user_can_change_status( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_change_status( $question_id ) );
		$this->assertTrue( ap_user_can_change_status( $answer_id ) );

		// Test for new role.
		add_role( 'ap_test_can_change_other_status', 'Test user can change other status', [ 'ap_change_status_other' => true ] );
		$this->setRole( 'ap_test_can_change_other_status' );
		$this->assertTrue( ap_user_can_change_status( $question_id ) );
		$this->assertTrue( ap_user_can_change_status( $answer_id ) );
		$this->logout();

		// Test for logged out user.
		$this->assertFalse( ap_user_can_change_status( $question_id ) );
		$this->assertFalse( ap_user_can_change_status( $answer_id ) );
	}

	/**
	 * @covers ::ap_user_can_close_question
	 */
	public function testAPUserCanCloseQuestion() {
		$this->assertFalse( ap_user_can_close_question() );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_close_question() );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_close_question() );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_close_question() );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_close_question() );

		// Test for new role.
		add_role( 'ap_test_can_close_question', 'Test user can close question', [ 'ap_close_question' => true ] );
		$this->setRole( 'ap_test_can_close_question' );
		$this->assertTrue( ap_user_can_close_question() );
		$this->logout();
		$this->assertFalse( ap_user_can_close_question() );
	}

	/**
	 * @covers ::ap_user_can_change_status_to_moderate
	 */
	public function testAPUserCanChangeStatusToModerate() {
		$this->assertFalse( ap_user_can_change_status_to_moderate() );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_change_status_to_moderate() );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_change_status_to_moderate() );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_change_status_to_moderate() );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_change_status_to_moderate() );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_change_status_to_moderate() );

		// Test for new role.
		add_role( 'ap_test_can_change_status_to_moderate', 'Test user can change status to moderate', [ 'ap_change_status_other' => true ] );
		$this->setRole( 'ap_test_can_change_status_to_moderate' );
		$this->assertTrue( ap_user_can_change_status_to_moderate() );
		$this->logout();
		$this->assertFalse( ap_user_can_change_status_to_moderate() );
	}

	/**
	 * @covers ::ap_user_can_upload
	 */
	public function testAPUserCanUpload() {
		$this->assertFalse( ap_user_can_upload() );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_upload() );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_upload() );
		ap_opt( 'allow_upload', false );
		$this->assertFalse( ap_user_can_upload() );
		ap_opt( 'allow_upload', true );
		$this->assertTrue( ap_user_can_upload() );
	}

	/**
	 * @covers ::ap_user_can_delete_attachment
	 */
	public function testAPUserCanDeleteAttachment() {
		// Test for no attachment available.
		$this->assertFalse( ap_user_can_delete_attachment( 0 ) );

		// Test for user roles.
		$this->setRole( 'subscriber' );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $post->ID );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for ap_moderator user.
		$this->setRole( 'ap_moderator' );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for super user.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
			grant_super_admin( $user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$this->setRole( 'administrator' );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}

		$this->setRole( 'subscriber' );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $post->ID );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for ap_moderator user.
		$this->setRole( 'ap_moderator' );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for super user.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
			grant_super_admin( $user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$this->setRole( 'administrator' );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}

		$this->setRole( 'subscriber' );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $post->ID );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for ap_moderator user.
		$this->setRole( 'ap_moderator' );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		// Test for super user.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
			grant_super_admin( $user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$this->setRole( 'administrator' );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}

		// Test other user can't delete the attachment of other user.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $post->ID );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'ap_moderator' ) );
		wp_set_current_user( $new_user_id );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		if ( \is_multisite() ) {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			grant_super_admin( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $post->ID );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'ap_moderator' ) );
		wp_set_current_user( $new_user_id );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		if ( \is_multisite() ) {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			grant_super_admin( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$post = $this->factory()->post->create_and_get();
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $post->ID );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'ap_moderator' ) );
		wp_set_current_user( $new_user_id );
		$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
		$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );
		if ( \is_multisite() ) {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id ) );

			// Test for super admin user.
			grant_super_admin( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		} else {
			$administrator_user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $administrator_user_id );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $user_id ) );
			$this->assertFalse( ap_user_can_delete_attachment( $attachment_id, $new_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id, $administrator_user_id ) );
			$this->assertTrue( ap_user_can_delete_attachment( $attachment_id ) );
		}
	}

	/**
	 * @covers ::ap_show_captcha_to_user
	 */
	public function testAPShowCaptchaToUser() {
		// Required dummy reCaptcha Site Key.
		ap_opt( 'recaptcha_site_key', 'anspressSamplereCaptchaSiteKey' );

		// Test for administrator and ap_moderator user role.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$this->assertTrue( ap_show_captcha_to_user() );

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$this->assertTrue( ap_show_captcha_to_user() );
			grant_super_admin( $user_id );
			$this->assertFalse( ap_show_captcha_to_user() );
		} else {
			$this->setRole( 'administrator' );
			$this->assertFalse( ap_show_captcha_to_user() );
		}
		$this->setRole( 'ap_moderator' );
		$this->assertFalse( ap_show_captcha_to_user() );

		// Test for other user roles.
		ap_opt( 'recaptcha_exclude_roles', [ 'ap_moderator' => 1 ] );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_show_captcha_to_user() );
		$this->setRole( 'contributor' );
		$this->assertTrue( ap_show_captcha_to_user() );
		$this->setRole( 'ap_banned' );
		$this->assertTrue( ap_show_captcha_to_user() );
		ap_opt(
			'recaptcha_exclude_roles',
			[
				'subscriber' => 1,
				'contributor' => 1,
				'ap_participant' => 1,
			]
		);
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_show_captcha_to_user() );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_show_captcha_to_user() );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_show_captcha_to_user() );

		// Required dummy reCaptcha Site Key.
		ap_opt( 'recaptcha_exclude_roles', [ 'ap_moderator' => 1 ] );
		ap_opt( 'recaptcha_site_key', '' );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_show_captcha_to_user() );
	}

	/**
	 * @covers ::ap_user_can_read_post
	 */
	public function testAPUserCanReadPost() {
		$this->setRole( 'subscriber' );
		// Test other post types.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
			)
		);
		$testimonial_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Testimonial title',
				'post_content' => 'Testimonial content',
				'post_type'    => 'testimonial',
			)
		);
		$this->assertTrue( ap_user_can_read_post( $post_id ) );
		$this->assertTrue( ap_user_can_read_post( $page_id ) );
		$this->assertTrue( ap_user_can_read_post( $testimonial_id ) );
		$this->logout();

		// Test for session storage.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$session = \AnsPress\Session::init();
		$session->set_answer( $answer_id );
		$session->set_question( $question_id );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );

		// Test for viewing others question.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $new_user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $new_user_id ) );
		$this->logout();

		// Test for new role.
		add_role(
			'ap_test_can_read_post',
			'Test user can read post',
			[
				'edit_others_question' => true,
				'edit_others_answer' => true,
			]
		);
		$this->setRole( 'ap_test_can_read_post' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->logout();

		// Test for trash post status.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'trash',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'trash',
			)
		);
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'author' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->logout();

		// Check for answer post type with private_post and moderate post status.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $user_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $user_id ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $new_user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $new_user_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $new_user_id,
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertFalse( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $new_user_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $new_user_id,
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertTrue( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $new_user_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $new_user_id,
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $new_user_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_read_post( $question_id, $user_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id, $user_id ) );
		$this->assertTrue( ap_user_can_read_post( $question_id, $new_user_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id, $new_user_id ) );
		$this->logout();

		// Check for private and moderate post status.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'private_post',
			)
		);
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'author' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'moderate',
			)
		);
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'contributor' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'author' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );

		// Check user having ap_read_question and ap_read_answer can read the question.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_opt( 'read_question_per', 'have_cap' );
		ap_opt( 'read_answer_per', 'have_cap' );
		add_role(
			'ap_test_can_read_question_answer',
			'Test user can read question and answer',
			[
				'ap_read_question' => true,
				'ap_read_answer' => true,
			]
		);
		$this->setRole( 'ap_test_can_read_question_answer' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		add_role( 'ap_test_can_not_read_question_answer', 'Test user can not read question and answer', [] );
		$this->setRole( 'ap_test_can_not_read_question_answer' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		add_role(
			'ap_test_can_not_read_question_answer',
			'Test user can not read question and answer',
			[
				'ap_read_question' => false,
				'ap_read_answer' => false,
			]
		);
		$this->setRole( 'ap_test_can_not_read_question_answer' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		add_role(
			'ap_test_can_read_question_not_answer',
			'Test user can not read question and answer',
			[
				'ap_read_question' => true,
				'ap_read_answer' => false,
			]
		);
		$this->setRole( 'ap_test_can_read_question_not_answer' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		add_role(
			'ap_test_can_read_answer_not_question',
			'Test user can not read answer and question',
			[
				'ap_read_question' => false,
				'ap_read_answer' => true,
			]
		);
		$this->setRole( 'ap_test_can_read_answer_not_question' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->logout();

		// Check anyone can read question and answer.
		ap_opt( 'read_question_per', 'anyone' );
		ap_opt( 'read_answer_per', 'anyone' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
		$this->logout();

		// Check only logged-in can read question and answer.
		ap_opt( 'read_question_per', 'logged_in' );
		ap_opt( 'read_answer_per', 'logged_in' );
		$this->assertFalse( ap_user_can_read_post( $question_id ) );
		$this->assertFalse( ap_user_can_read_post( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_post( $question_id ) );
		$this->assertTrue( ap_user_can_read_post( $answer_id ) );
	}

	/**
	 * @covers ::ap_user_can_read_question
	 * @covers ::ap_user_can_read_answer
	 */
	public function testAPUserCanReadQuestionAnswer() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_read_question( $question_id ) );
		$this->assertTrue( ap_user_can_read_answer( $answer_id ) );

		// Test other post types.
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Page title',
				'post_content' => 'Page content',
				'post_type'    => 'page',
			)
		);
		$this->assertTrue( ap_user_can_read_question( $post_id ) );
		$this->assertTrue( ap_user_can_read_question( $page_id ) );
		$this->assertTrue( ap_user_can_read_answer( $post_id ) );
		$this->assertTrue( ap_user_can_read_answer( $page_id ) );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPost() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$this->setRole( 'ap_participant' );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );

		// Test for super admin.
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );
			$question_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
					'post_type'    => 'question',
				)
			);
			$answer_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Answer title',
					'post_content' => 'Answer content',
					'post_type'    => 'answer',
					'post_parent'  => $question_id,
				)
			);
			$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
			$this->logout();

			// Test for super admin user.
			$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
			wp_set_current_user( $user_id );
			$question_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
					'post_type'    => 'question',
				)
			);
			$answer_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Answer title',
					'post_content' => 'Answer content',
					'post_type'    => 'answer',
					'post_parent'  => $question_id,
				)
			);
			$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
			$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
			grant_super_admin( $user_id );
			$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
			$this->logout();
		} else {
			$this->setRole( 'administrator' );
			$question_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Question title',
					'post_content' => 'Question content',
					'post_type'    => 'question',
				)
			);
			$answer_id = $this->factory()->post->create(
				array(
					'post_title'   => 'Answer title',
					'post_content' => 'Answer content',
					'post_type'    => 'answer',
					'post_parent'  => $question_id,
				)
			);
			$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
			$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
			$this->logout();
		}

		// Test for new role.
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		add_role(
			'ap_test_can_vote_up_down',
			'Test user can vote up and down on question and answer',
			[
				'ap_vote_up' => true,
				'ap_vote_down' => true,
			]
		);
		$this->setRole( 'ap_test_can_vote_up_down' );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$this->logout();
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		add_role(
			'ap_test_can_vote_up_not_down',
			'Test user can vote up and not down on question and answer',
			[
				'ap_vote_up' => true,
				'ap_vote_down' => false,
			]
		);
		$this->setRole( 'ap_test_can_vote_up_not_down' );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$this->logout();
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		add_role(
			'ap_test_can_vote_down_not_up',
			'Test user can vote down and not vote up on question and answer',
			[
				'ap_vote_up' => false,
				'ap_vote_down' => true,
			]
		);
		$this->setRole( 'ap_test_can_vote_down_not_up' );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$this->logout();
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		add_role(
			'ap_test_can_not_vote_up_down',
			'Test user can vote up and down on question and answer',
			[
				'ap_vote_up' => false,
				'ap_vote_down' => false,
			]
		);
		$this->setRole( 'ap_test_can_not_vote_up_down' );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$this->logout();
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'private_post',
			)
		);
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $new_user_id );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertFalse( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
				'post_status'  => 'public',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_author'  => $user_id,
				'post_status'  => 'public',
			)
		);
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_up' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $question_id, 'vote_down' ) );
		$this->assertTrue( ap_user_can_vote_on_post( $answer_id, 'vote_down' ) );
	}

	/**
	 * @covers ::ap_user_can_approve_comment
	 */
	public function testAPUserCanApproveComment() {
		// Test for subscriber.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_approve_comment( $user_id ) );

		// Test for ap_banned.
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_banned' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_approve_comment( $user_id ) );

		// Test for ap_participant.
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_participant' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_approve_comment( $user_id ) );

		// Test for ap_moderator.
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_moderator' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_approve_comment( $user_id ) );

		// Test for editor.
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_approve_comment( $user_id ) );

		// Test for administrator.
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_approve_comment( $user_id ) );

		// Test for new role.
		add_role( 'ap_test_can_approve_comment', 'Test user can approve comment', [ 'ap_approve_comment' => true ] );
		$this->setRole( 'ap_test_can_approve_comment' );
		$this->assertTrue( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_test_can_approve_comment' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_approve_comment( $user_id ) );
		add_role( 'ap_test_can_not_approve_comment', 'Test user can not approve comment', [ 'ap_approve_comment' => false ] );
		$this->setRole( 'ap_test_can_not_approve_comment' );
		$this->assertFalse( ap_user_can_approve_comment() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_test_can_not_approve_comment' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_approve_comment( $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_toggle_featured
	 */
	public function testAPUserCanToggleFeatured() {
		// Test for subscriber.
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_toggle_featured( $user_id ) );

		// Test for ap_banned.
		$this->setRole( 'ap_banned' );
		$this->assertFalse( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_banned' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_toggle_featured( $user_id ) );

		// Test for ap_participant.
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_participant' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_toggle_featured( $user_id ) );

		// Test for ap_moderator.
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_moderator' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_toggle_featured( $user_id ) );

		// Test for editor.
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_toggle_featured( $user_id ) );

		// Test for administrator.
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_toggle_featured( $user_id ) );

		// Test for new role.
		add_role( 'ap_test_can_toggle_featured', 'Test user can toggle featured', [ 'ap_toggle_featured' => true ] );
		$this->setRole( 'ap_test_can_toggle_featured' );
		$this->assertTrue( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_test_can_toggle_featured' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_user_can_toggle_featured( $user_id ) );
		add_role( 'ap_test_can_not_toggle_featured', 'Test user can not toggle featured', [ 'ap_toggle_featured' => false ] );
		$this->setRole( 'ap_test_can_not_toggle_featured' );
		$this->assertFalse( ap_user_can_toggle_featured() );
		$user_id = $this->factory()->user->create( array( 'role' => 'ap_test_can_not_toggle_featured' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( ap_user_can_toggle_featured( $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_read_comment
	 */
	public function testAPUserCanReadComment() {
		$this->setRole( 'subscriber' );
		$id   = $this->insert_answer();
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'ap_participant' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );

		$this->setRole( 'subscriber' );
		$id   = $this->insert_answer();
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		wp_set_comment_status( $cqid, 'hold' );
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		wp_set_comment_status( $caid, 'hold' );
		$this->assertFalse( ap_user_can_read_comment( $cqid ) );
		$this->assertFalse( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_comment( $cqid ) );
		$this->assertFalse( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'editor' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );

		// Check user having ap_read_comment can read the comment.
		$id   = $this->insert_answer();
		$cqid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->a,
				'user_id'         => get_current_user_id(),
			)
		);
		ap_opt( 'read_comment_per', 'have_cap' );
		add_role( 'ap_test_can_read_comment', 'Test user can read comment', [ 'ap_read_comment' => true ] );
		$this->setRole( 'ap_test_can_read_comment' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		add_role( 'ap_test_can_not_read_comment', 'Test user can not read comment', [ 'ap_read_comment' => false ] );
		$this->setRole( 'ap_test_can_not_read_comment' );
		$this->assertFalse( ap_user_can_read_comment( $cqid ) );
		$this->assertFalse( ap_user_can_read_comment( $caid ) );
		add_role( 'ap_test_can_not_read_comment', 'Test user can not read comment', [] );
		$this->setRole( 'ap_test_can_not_read_comment' );
		$this->assertFalse( ap_user_can_read_comment( $cqid ) );
		$this->assertFalse( ap_user_can_read_comment( $caid ) );

		// Check anyone can read comment.
		ap_opt( 'read_comment_per', 'anyone' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
		$this->logout();

		// Check only logged-in can read comment.
		ap_opt( 'read_comment_per', 'logged_in' );
		$this->assertFalse( ap_user_can_read_comment( $cqid ) );
		$this->assertFalse( ap_user_can_read_comment( $caid ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_comment( $cqid ) );
		$this->assertTrue( ap_user_can_read_comment( $caid ) );
	}

	/**
	 * @covers ::ap_user_can_read_comments
	 */
	public function testAPUserCanReadComments() {
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );

		// Test for private_post and moderate post status.
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_comments( $question_id ) );
		$this->assertFalse( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_comments( $question_id ) );
		$this->assertFalse( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'ap_participant' );
		$this->assertFalse( ap_user_can_read_comments( $question_id ) );
		$this->assertFalse( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'ap_moderator' );
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		$this->assertTrue( ap_user_can_read_comments( $answer_id ) );
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_comments( $question_id ) );
		$this->assertFalse( ap_user_can_read_comments( $answer_id ) );
		$this->logout();

		// Check user having ap_read_comment can read the comment.
		$id = $this->insert_answer();
		ap_opt( 'read_comment_per', 'have_cap' );
		add_role( 'ap_test_can_read_comment', 'Test user can read comments', [ 'ap_read_comment' => true ] );
		$this->setRole( 'ap_test_can_read_comment' );
		$this->assertTrue( ap_user_can_read_comments( $id->q ) );
		$this->assertTrue( ap_user_can_read_comments( $id->a ) );
		add_role( 'ap_test_can_not_read_comment', 'Test user can not read comments', [ 'ap_read_comment' => false ] );
		$this->setRole( 'ap_test_can_not_read_comment' );
		$this->assertFalse( ap_user_can_read_comments( $id->q ) );
		$this->assertFalse( ap_user_can_read_comments( $id->a ) );
		add_role( 'ap_test_can_not_read_comment', 'Test user can not read comments', [] );
		$this->setRole( 'ap_test_can_not_read_comment' );
		$this->assertFalse( ap_user_can_read_comments( $id->q ) );
		$this->assertFalse( ap_user_can_read_comments( $id->a ) );

		// Check anyone can read comment.
		ap_opt( 'read_comment_per', 'anyone' );
		$this->assertTrue( ap_user_can_read_comments( $id->q ) );
		$this->assertTrue( ap_user_can_read_comments( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_comments( $id->q ) );
		$this->assertTrue( ap_user_can_read_comments( $id->a ) );
		$this->logout();

		// Check only logged-in can read comment.
		ap_opt( 'read_comment_per', 'logged_in' );
		$this->assertFalse( ap_user_can_read_comments( $id->q ) );
		$this->assertFalse( ap_user_can_read_comments( $id->a ) );
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_user_can_read_comments( $id->q ) );
		$this->assertTrue( ap_user_can_read_comments( $id->a ) );
	}

	public static function ReturnTrue() {
		return true;
	}

	public static function ReturnFalse() {
		return false;
	}

	/**
	 * @covers ::ap_user_can_ask
	 */
	public function testAPUserCanAskForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$this->assertTrue( ap_user_can_ask() );
	}

	/**
	 * @covers ::ap_user_can_ask
	 */
	public function testAPUserCanAskWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_ask', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_ask() );
		remove_filter( 'ap_user_can_ask', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_ask
	 */
	public function testAPUserCanAskWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_ask', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_ask() );
		remove_filter( 'ap_user_can_ask', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswerForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->assertTrue( ap_user_can_answer( $question_id ) );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswerWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_answer', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_answer( $question_id ) );
		remove_filter( 'ap_user_can_answer', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswerWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_answer', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_answer( $question_id ) );
		remove_filter( 'ap_user_can_answer', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswerForUserWhoCantReadQuestion() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$this->assertFalse( ap_user_can_answer( $question_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_answer
	 */
	public function testAPUserCanAnswerForClosedQuestion() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'publish',
				'post_type'    => 'question',
			)
		);
		ap_toggle_close_question( $question_id );
		$this->assertFalse( ap_user_can_answer( $question_id ) );
	}

	/**
	 * @covers ::ap_user_can_select_answer
	 */
	public function testAPUserCanSelectAnswerForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$id = $this->insert_answer();
		$this->assertTrue( ap_user_can_select_answer( $id->a ) );
	}

	/**
	 * @covers ::ap_user_can_select_answer
	 */
	public function testAPUserCanSelectAnswerWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		add_filter( 'ap_user_can_select_answer', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_select_answer( $id->a ) );
		remove_filter( 'ap_user_can_select_answer', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_select_answer
	 */
	public function testAPUserCanSelectAnswerWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		add_filter( 'ap_user_can_select_answer', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_select_answer( $id->a ) );
		remove_filter( 'ap_user_can_select_answer', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_select_answer
	 */
	public function testAPUserCanSelectAnswerForPostTypeNotAsAnAnswer() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$this->assertFalse( ap_user_can_select_answer( $id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 */
	public function testAPUserCanEditPostForInvalidPostType() {
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$this->assertFalse( ap_user_can_edit_post( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 */
	public function testAPUserCanEditPostWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_edit_post', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_edit_post( $post_id ) );
		remove_filter( 'ap_user_can_edit_post', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 */
	public function testAPUserCanEditPostWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_edit_post', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_edit_post( $post_id ) );
		remove_filter( 'ap_user_can_edit_post', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 */
	public function testAPUserCanEditPostForPostStatusSetAsModerate() {
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_edit_post( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_post
	 */
	public function testAPUserCanEditPostForUserWhoCantReadPost() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		$this->assertFalse( ap_user_can_edit_post( $post_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_answer
	 */
	public function testAPUserCanEditAnswerWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		add_filter( 'ap_user_can_edit_answer', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_edit_answer( $id->a ) );
		remove_filter( 'ap_user_can_edit_answer', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_answer
	 */
	public function testAPUserCanEditAnswerWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		add_filter( 'ap_user_can_edit_answer', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_edit_answer( $id->a ) );
		remove_filter( 'ap_user_can_edit_answer', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_answer
	 */
	public function testAPUserCanEditAnswerForPostStatusSetAsModerate() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		wp_update_post(
			array(
				'ID'          => $id->a,
				'post_status' => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_edit_answer( $id->a ) );
	}

	/**
	 * @covers ::ap_user_can_edit_answer
	 */
	public function testAPUserCanEditAnswerForUserWhoCantReadAnswer() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$id = $this->insert_answer();
		wp_update_post(
			array(
				'ID'          => $id->a,
				'post_status' => 'private_post',
			)
		);
		$this->assertFalse( ap_user_can_edit_answer( $id->a, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionForNotPassingQuestionID() {
		$this->setRole( 'subscriber' );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertTrue( ap_user_can_edit_question() );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionForInvalidPostType() {
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$this->assertFalse( ap_user_can_edit_question( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_question();
		add_filter( 'ap_user_can_edit_question', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_edit_question( $id ) );
		remove_filter( 'ap_user_can_edit_question', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_question();
		add_filter( 'ap_user_can_edit_question', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_edit_question( $id ) );
		remove_filter( 'ap_user_can_edit_question', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionForPostStatusSetAsModerate() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		wp_update_post(
			array(
				'ID'          => $id,
				'post_status' => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_edit_question( $id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_question
	 */
	public function testAPUserCanEditQuestionForUserWhoCantReadQuestion() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$id = $this->insert_question();
		wp_update_post(
			array(
				'ID'          => $id,
				'post_status' => 'private_post',
			)
		);
		$this->assertFalse( ap_user_can_edit_question( $id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentForNotPassingAnyArgs() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $question_id );
		$this->assertTrue( ap_user_can_comment() );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentForNotPassingAnyArgsAndVisitingAnswerPage() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->go_to( '?post_type=answer&p=' . $answer_id );
		$this->assertTrue( ap_user_can_comment() );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->assertTrue( ap_user_can_comment( $question_id ) );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_comment', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_comment( $question_id ) );
		remove_filter( 'ap_user_can_comment', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_comment', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_comment( $question_id ) );
		remove_filter( 'ap_user_can_comment', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentForPostStatusSetAsModerate() {
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		wp_update_post(
			array(
				'ID'          => $question_id,
				'post_status' => 'moderate',
			)
		);
		$this->assertFalse( ap_user_can_comment( $question_id ) );
	}

	/**
	 * @covers ::ap_user_can_comment
	 */
	public function testAPUserCanCommentForUserWhoCantReadQuestion() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$this->assertFalse( ap_user_can_comment( $question_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_comment
	 */
	public function testAPUserCanEditCommentWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $id->q,
			)
		);
		add_filter( 'ap_user_can_edit_comment', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_edit_comment( $comment_id ) );
		remove_filter( 'ap_user_can_edit_comment', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_comment
	 */
	public function testAPUserCanEditCommentWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$id = $this->insert_answer();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $id->q,
			)
		);
		add_filter( 'ap_user_can_edit_comment', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_edit_comment( $comment_id ) );
		remove_filter( 'ap_user_can_edit_comment', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_edit_comment
	 */
	public function testAPUserCanEditCommentForUnapprovedComment() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $id->q,
				'comment_approved' => 0,
			)
		);
		$this->assertFalse( ap_user_can_edit_comment( $comment_id ) );
	}

	/**
	 * @covers ::ap_user_can_edit_comment
	 */
	public function testAPUserCanEditCommentForUserWhoCantReadPost() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$id = $this->insert_answer();
		wp_update_post(
			array(
				'ID'          => $id->q,
				'post_status' => 'private_post',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $id->q,
			)
		);
		$this->assertFalse( ap_user_can_edit_comment( $comment_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_delete_post
	 */
	public function testAPUserCanDeletePostForInvalidPostType() {
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$this->assertFalse( ap_user_can_delete_post( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_delete_post
	 */
	public function testAPUserCanDeletePostWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_delete_post', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_delete_post( $post_id ) );
		remove_filter( 'ap_user_can_delete_post', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_delete_post
	 */
	public function testAPUserCanDeletePostWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_delete_post', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_delete_post( $post_id ) );
		remove_filter( 'ap_user_can_delete_post', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_delete_post
	 */
	public function testAPUserCanDeletePostForUserWhoCantReadPost() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$this->assertFalse( ap_user_can_delete_post( $post_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_permanent_delete
	 */
	public function testAPUserCanPermanentDeleteForInvalidPostType() {
		$this->setRole( 'subscriber' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$this->assertFalse( ap_user_can_permanent_delete( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_restore
	 */
	public function testAPUserCanRestoreForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
			)
		);
		$this->assertTrue( ap_user_can_restore( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_view_post
	 */
	public function testAPUserCanViewPostForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_author'  => 0,
			)
		);
		$this->assertTrue( ap_user_can_view_post( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_change_status
	 */
	public function testAPUserCanChangeStatusForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		$this->assertTrue( ap_user_can_change_status( $post_id ) );
	}

	/**
	 * @covers ::ap_user_can_change_status
	 */
	public function testAPUserCanChangeStatusWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_change_status', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_change_status( $post_id ) );
		remove_filter( 'ap_user_can_change_status', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_change_status
	 */
	public function testAPUserCanChangeStatusWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'question',
			)
		);
		add_filter( 'ap_user_can_change_status', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_change_status( $post_id ) );
		remove_filter( 'ap_user_can_change_status', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_show_captcha_to_user
	 */
	public function testAPShowCaptchaToUserWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_show_captcha', [ $this, 'ReturnTrue' ] );
		$this->assertFalse( ap_show_captcha_to_user() );
		remove_filter( 'ap_show_captcha', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_read_post
	 */
	public function testAPUserCanReadPostForNotValidPost() {
		$this->setRole( 'subscriber' );
		$this->assertFalse( ap_user_can_read_post( 0 ) );
	}

	/**
	 * @covers ::ap_user_can_read_post
	 */
	public function testAPUserCanReadPostWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->insert_question();
		add_filter( 'ap_user_can_read_post', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_read_post( $post_id ) );
		remove_filter( 'ap_user_can_read_post', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_read_post
	 */
	public function testAPUserCanReadPostWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->insert_question();
		add_filter( 'ap_user_can_read_post', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_read_post( $post_id ) );
		remove_filter( 'ap_user_can_read_post', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->insert_question();
		add_filter( 'ap_user_can_vote_on_post', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_vote_on_post( $post_id, 'vote_up' ) );
		remove_filter( 'ap_user_can_vote_on_post', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$post_id = $this->insert_question();
		add_filter( 'ap_user_can_vote_on_post', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_vote_on_post( $post_id, 'vote_up' ) );
		remove_filter( 'ap_user_can_vote_on_post', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForUserWhoTriesToVoteOnOwnPost() {
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->insert_question( '', '', $user_id );
		$this->assertFalse( ap_user_can_vote_on_post( $post_id, 'vote_up', $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForUserWhoTriesToVoteOnOwnPostButReturnWPError() {
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->insert_question( '', '', $user_id );
		$result = ap_user_can_vote_on_post( $post_id, 'vote_up', $user_id, true );
		$this->assertTrue( is_wp_error( $result ) );
		$this->assertEquals( 'cannot_vote_own_post', $result->get_error_code() );
		$this->assertEquals( 'Voting on own post is not allowed', $result->get_error_message() );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForUserWhoCantReadPost() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$this->assertFalse( ap_user_can_vote_on_post( $post_id, 'vote_up', $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForUserWhoCantReadPostButReturnWPError() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$result = ap_user_can_vote_on_post( $post_id, 'vote_up', $user_id, true );
		$this->assertTrue( is_wp_error( $result ) );
		$this->assertEquals( 'you_cannot_vote_on_restricted', $result->get_error_code() );
		$this->assertEquals( 'Voting on restricted posts are not allowed.', $result->get_error_message() );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForReturnFalse() {
		$this->setRole( 'ap_banned' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->insert_question( '', '', $user_id );
		$this->assertFalse( ap_user_can_vote_on_post( $post_id, 'vote_up', get_current_user_id() ) );
	}

	/**
	 * @covers ::ap_user_can_vote_on_post
	 */
	public function testAPUserCanVoteOnPostForReturnWPError() {
		$this->setRole( 'ap_banned' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$post_id = $this->insert_question( '', '', $user_id );
		$result = ap_user_can_vote_on_post( $post_id, 'vote_up', get_current_user_id(), true );
		$this->assertTrue( is_wp_error( $result ) );
		$this->assertEquals( 'no_permission', $result->get_error_code() );
		$this->assertEquals( 'You do not have permission to vote.', $result->get_error_message() );
	}

	/**
	 * @covers ::ap_user_can_approve_comment
	 */
	public function testAPUserCanApproveCommentWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_approve_comment', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_approve_comment( get_current_user_id() ) );
		remove_filter( 'ap_user_can_approve_comment', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_approve_comment
	 */
	public function testAPUserCanApproveCommentWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_approve_comment', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_approve_comment( get_current_user_id() ) );
		remove_filter( 'ap_user_can_approve_comment', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_toggle_featured
	 */
	public function testAPUserCanToggleFeaturedWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_toggle_featured', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_toggle_featured( get_current_user_id() ) );
		remove_filter( 'ap_user_can_toggle_featured', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_toggle_featured
	 */
	public function testAPUserCanToggleFeaturedWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		add_filter( 'ap_user_can_toggle_featured', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_toggle_featured( get_current_user_id() ) );
		remove_filter( 'ap_user_can_toggle_featured', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_read_comment
	 */
	public function testAPUserCanReadCommentWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
			)
		);
		add_filter( 'ap_user_can_read_comment', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_read_comment( $comment_id ) );
		remove_filter( 'ap_user_can_read_comment', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_read_comment
	 */
	public function testAPUserCanReadCommentWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
			)
		);
		add_filter( 'ap_user_can_read_comment', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_read_comment( $comment_id ) );
		remove_filter( 'ap_user_can_read_comment', [ $this, 'ReturnFalse' ] );
	}

	/**
	 * @covers ::ap_user_can_read_comment
	 */
	public function testAPUserCanReadCommentForUserWhoCantReadPost() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_status'  => 'private_post',
				'post_type'    => 'question',
			)
		);
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID' => $question_id,
			)
		);
		$this->assertFalse( ap_user_can_read_comment( $comment_id, $user_id ) );
	}

	/**
	 * @covers ::ap_user_can_read_comments
	 */
	public function testAPUserCanReadCommentsWithFilterSetAsTrue() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_read_comments', [ $this, 'ReturnTrue' ] );
		$this->assertTrue( ap_user_can_read_comments( $question_id ) );
		remove_filter( 'ap_user_can_read_comments', [ $this, 'ReturnTrue' ] );
	}

	/**
	 * @covers ::ap_user_can_read_comments
	 */
	public function testAPUserCanReadCommentsWithFilterSetAsFalse() {
		$this->setRole( 'ap_banned' );
		$question_id = $this->insert_question();
		add_filter( 'ap_user_can_read_comments', [ $this, 'ReturnFalse' ] );
		$this->assertFalse( ap_user_can_read_comments( $question_id ) );
		remove_filter( 'ap_user_can_read_comments', [ $this, 'ReturnFalse' ] );
	}
}
