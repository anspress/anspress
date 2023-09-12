<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Roles extends TestCase {

	use AnsPress\Tests\Testcases\Common;

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
		$post_id     = $this->factory->post->create(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
				'post_type'    => 'post',
			)
		);
		$page_id     = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$aid = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $qid,
			)
		);
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		ap_set_selected_answer( $qid, $aid );
		$this->assertFalse( ap_user_can_answer( $qid, $user_id ) );

		// Check for the original poster can answer.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$qid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$this->assertTrue( ap_user_can_answer( $qid, $user_id ) );
		$aid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
		$this->setRole( 'administrator' );
		$this->assertTrue( ap_user_can_change_label() );

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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => get_current_user_id(),
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$cqid = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $id->q,
				'user_id'         => $user_id,
			)
		);
		$caid = $this->factory->comment->create_object(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
		$qid = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
				'post_author'  => $user_id,
			)
		);
		$aid = $this->factory->post->create(
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
}
