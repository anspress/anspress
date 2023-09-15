<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAMeta extends TestCase {

	use AnsPress\Tests\Testcases\Common;

	/**
	 * @covers ::ap_qameta_fields
	 */
	public function testAPQametaFields() {
		// Test for if the array key exists or not.
		$qameta_fields_array = array(
			'post_id',
			'selected',
			'selected_id',
			'comments',
			'answers',
			'ptype',
			'featured',
			'closed',
			'views',
			'votes_up',
			'votes_down',
			'subscribers',
			'flags',
			'terms',
			'attach',
			'activities',
			'fields',
			'roles',
			'last_updated',
			'is_new',
		);
		foreach ( $qameta_fields_array as $qameta_field_item ) {
			$this->assertArrayHasKey( $qameta_field_item, ap_qameta_fields() );
		}

		// Test for the qameta fields default value is matching or not.
		$qameta_fields = ap_qameta_fields();
		$this->assertEquals( '', $qameta_fields['post_id'] );
		$this->assertFalse( $qameta_fields['selected'] );
		$this->assertEquals( 0, $qameta_fields['selected_id'] );
		$this->assertEquals( 0, $qameta_fields['comments'] );
		$this->assertEquals( 0, $qameta_fields['answers'] );
		$this->assertEquals( 'question', $qameta_fields['ptype'] );
		$this->assertEquals( 0, $qameta_fields['featured'] );
		$this->assertEquals( 0, $qameta_fields['closed'] );
		$this->assertEquals( 0, $qameta_fields['views'] );
		$this->assertEquals( 0, $qameta_fields['votes_up'] );
		$this->assertEquals( 0, $qameta_fields['votes_down'] );
		$this->assertEquals( 0, $qameta_fields['subscribers'] );
		$this->assertEquals( 0, $qameta_fields['flags'] );
		$this->assertEquals( '', $qameta_fields['terms'] );
		$this->assertEquals( '', $qameta_fields['attach'] );
		$this->assertEquals( '', $qameta_fields['activities'] );
		$this->assertEquals( '', $qameta_fields['fields'] );
		$this->assertEquals( '', $qameta_fields['roles'] );
		$this->assertEquals( '', $qameta_fields['last_updated'] );
		$this->assertFalse( $qameta_fields['is_new'] );
	}

	/**
	 * @covers ::ap_set_selected_answer
	 * @covers ::ap_unset_selected_answer
	 */
	public function testSelectedAnswer() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_set_selected_answer( $question_id, $answer1_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );

		// Updating the selected answer test.
		ap_unset_selected_answer( $question_id, $answer1_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );

		ap_set_selected_answer( $question_id, $answer2_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertEquals( 1, $get_qameta->selected );

		// Updating the selected answer test.
		ap_unset_selected_answer( $question_id, $answer2_id );
		$get_qameta = ap_get_qameta( $answer1_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
		$get_qameta = ap_get_qameta( $answer2_id );
		$this->assertNotEquals( 1, $get_qameta->selected );
	}

	/**
	 * @covers ::ap_update_views_count
	 */
	public function testAPUpdateViewsCount() {
		$id = $this->insert_question();
		$this->assertEquals( 1, ap_update_views_count( $id ) );
		$this->assertEquals( 50, ap_update_views_count( $id, 50 ) );
		ap_insert_qameta( $id, array( 'views' => 100 ) );
		$this->assertEquals( 101, ap_update_views_count( $id ) );
	}

	/**
	 * @covers ::ap_update_answers_count
	 */
	public function testAPUpdateAnswersCount() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 0, $get_qameta->answers );
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 1, $get_qameta->answers );
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 2, $get_qameta->answers );

		// Start the main test.
		ap_update_answers_count( $question_id );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 2, $get_qameta->answers );
		ap_update_answers_count( $question_id, 100 );
		$get_qameta = ap_get_qameta( $question_id );
		$this->assertEquals( 100, $get_qameta->answers );
	}

	/**
	 * @covers ::ap_update_last_active
	 */
	public function testAPUpdateLastActive() {
		$id = $this->insert_question();
		ap_insert_qameta(
			$id,
			array(
				'last_updated' => '0000-00-00 00:00:00',
			)
		);
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( '0000-00-00 00:00:00', $get_qameta->last_updated );

		// Real function test goes here.
		ap_update_last_active( $id );
		$get_qameta = ap_get_qameta( $id );
		$this->assertEquals( current_time( 'mysql' ), $get_qameta->last_updated );
	}

	/**
	 * @covers ::ap_set_flag_count
	 */
	public function testAPSetFlagCount() {
		$id       = $this->insert_answer();
		$question = ap_get_post( $id->q );
		$answer   = ap_get_post( $id->a );
		$this->assertEquals( 0, $question->flags );
		$this->assertEquals( 0, $answer->flags );

		// Real function test goes here.
		ap_set_flag_count( $id->q );
		ap_set_flag_count( $id->a );
		$question = ap_get_post( $id->q );
		$answer   = ap_get_post( $id->a );
		$this->assertEquals( 1, $question->flags );
		$this->assertEquals( 1, $answer->flags );

		// Modifying the flags.
		ap_set_flag_count( $id->q, 5 );
		ap_set_flag_count( $id->a, 10 );
		$question = ap_get_post( $id->q );
		$answer   = ap_get_post( $id->a );
		$this->assertEquals( 5, $question->flags );
		$this->assertEquals( 10, $answer->flags );

		// Resetting the flags to 0.
		ap_set_flag_count( $id->q, 0 );
		ap_set_flag_count( $id->a, 0 );
		$question = ap_get_post( $id->q );
		$answer   = ap_get_post( $id->a );
		$this->assertEquals( 0, $question->flags );
		$this->assertEquals( 0, $answer->flags );
		$this->assertNotEquals( 1, $question->flags );
		$this->assertNotEquals( 1, $answer->flags );
		$this->assertNotEquals( 5, $question->flags );
		$this->assertNotEquals( 10, $answer->flags );
	}

	/**
	 * @covers ::ap_update_answer_selected
	 */
	public function testAPUpdateAnswerSelected() {
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);
		$answer1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertEquals( 0, ap_is_selected( $answer1_id ) );
		$answer2_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer Content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		$this->assertEquals( 0, ap_is_selected( $answer2_id ) );

		// Real function test goes here.
		ap_update_answer_selected( $answer1_id );
		$this->assertEquals( 1, ap_is_selected( $answer1_id ) );
		ap_update_answer_selected( $answer1_id, false );
		$this->assertEquals( 0, ap_is_selected( $answer1_id ) );
		$this->assertNotEquals( 1, ap_is_selected( $answer1_id ) );
		ap_update_answer_selected( $answer2_id );
		$this->assertEquals( 1, ap_is_selected( $answer2_id ) );
		ap_update_answer_selected( $answer2_id, false );
		$this->assertEquals( 0, ap_is_selected( $answer2_id ) );
		$this->assertNotEquals( 1, ap_is_selected( $answer2_id ) );
	}

	/**
	 * @covers ::ap_update_subscribers_count
	 */
	public function testAPUpdateSubscribersCount() {
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_subscribers_count( 'question', $id ) );
		$this->assertEquals( 0, ap_update_subscribers_count( $id ) );
		$this->assertEquals( 100, ap_update_subscribers_count( $id, 100 ) );
		$this->assertEquals( 1000, ap_update_subscribers_count( $id, 1000 ) );
	}

	/**
	 * @covers ::ap_set_featured_question
	 * @covers ::ap_unset_featured_question
	 */
	public function testFeaturedQuestion() {
		$id = $this->insert_question();
		$this->assertFalse( ap_is_featured_question( $id ) );
		ap_set_featured_question( $id );
		$this->assertTrue( ap_is_featured_question( $id ) );
		ap_unset_featured_question( $id );
		$this->assertFalse( ap_is_featured_question( $id ) );
	}

	/**
	 * @covers ::ap_toggle_close_question
	 */
	public function testAPToggleCloseQuestion() {
		$id = $this->insert_question();
		$this->assertEquals( 1, ap_toggle_close_question( $id ) );
		$this->assertEquals( 0, ap_toggle_close_question( $id ) );
		ap_insert_qameta( $id, array( 'closed' => 0 ) );
		$this->assertEquals( 1, ap_toggle_close_question( $id ) );
		$this->assertEquals( 0, ap_toggle_close_question( $id ) );
		ap_insert_qameta( $id, array( 'closed' => 1 ) );
		$this->assertEquals( 0, ap_toggle_close_question( $id ) );
		$this->assertEquals( 1, ap_toggle_close_question( $id ) );
	}

	/**
	 * @covers ::ap_update_post_attach_ids
	 */
	public function testAPUpdatePostAttachIds() {
		// Test for user roles.
		$this->setRole( 'subscriber' );
		$post = $this->factory->post->create_and_get();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.txt', $post->ID );
		wp_delete_attachment( $attachment_id, true );
		$this->assertEquals( [], ap_update_post_attach_ids( $attachment_id ) );

		$post = $this->factory->post->create_and_get();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $post->ID );
		wp_delete_attachment( $attachment_id, true );
		$this->assertEquals( [], ap_update_post_attach_ids( $attachment_id ) );

		$post = $this->factory->post->create_and_get();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $post->ID );
		wp_delete_attachment( $attachment_id, true );
		$this->assertEquals( [], ap_update_post_attach_ids( $attachment_id ) );
	}

	/**
	 * @covers ::ap_update_votes_count
	 */
	public function testAPUpdateVotesCount() {
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$id = $this->insert_answer();
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0,
			],
			ap_update_votes_count( $id->q )
		);
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 0,
				'votes_up'   => 0,
			],
			ap_update_votes_count( $id->a )
		);
		ap_add_post_vote( $id->q );
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 0,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->a );
		$this->assertEquals(
			[
				'votes_net'  => 1,
				'votes_down' => 0,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->q, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->a, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 1,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->q, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => -1,
				'votes_down' => 2,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->a, $user_id, false );
		$this->assertEquals(
			[
				'votes_net'  => -1,
				'votes_down' => 2,
				'votes_up'   => 1,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->q, $user_id, true );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 2,
				'votes_up'   => 2,
			],
			ap_update_votes_count( $id->q )
		);
		ap_add_post_vote( $id->a, $user_id, true );
		$this->assertEquals(
			[
				'votes_net'  => 0,
				'votes_down' => 2,
				'votes_up'   => 2,
			],
			ap_update_votes_count( $id->q )
		);
	}

	/**
	 * @covers ::ap_get_qameta
	 */
	public function testAPGetQameta() {
		$id = $this->insert_answer();
		$question_get_qameta = ap_get_qameta( $id->q );
		$question_get_qameta = (array) $question_get_qameta;
		$answer_get_qameta = ap_get_qameta( $id->a );
		$answer_get_qameta = (array) $answer_get_qameta;

		// Test for question.
		$this->assertArrayHasKey( 'post_id', $question_get_qameta );
		$this->assertArrayHasKey( 'selected', $question_get_qameta );
		$this->assertArrayHasKey( 'selected_id', $question_get_qameta );
		$this->assertArrayHasKey( 'comments', $question_get_qameta );
		$this->assertArrayHasKey( 'answers', $question_get_qameta );
		$this->assertArrayHasKey( 'ptype', $question_get_qameta );
		$this->assertArrayHasKey( 'featured', $question_get_qameta );
		$this->assertArrayHasKey( 'closed', $question_get_qameta );
		$this->assertArrayHasKey( 'views', $question_get_qameta );
		$this->assertArrayHasKey( 'votes_up', $question_get_qameta );
		$this->assertArrayHasKey( 'votes_down', $question_get_qameta );
		$this->assertArrayHasKey( 'subscribers', $question_get_qameta );
		$this->assertArrayHasKey( 'flags', $question_get_qameta );
		$this->assertArrayHasKey( 'terms', $question_get_qameta );
		$this->assertArrayHasKey( 'attach', $question_get_qameta );
		$this->assertArrayHasKey( 'activities', $question_get_qameta );
		$this->assertArrayHasKey( 'fields', $question_get_qameta );
		$this->assertArrayHasKey( 'roles', $question_get_qameta );
		$this->assertArrayHasKey( 'last_updated', $question_get_qameta );
		$this->assertArrayHasKey( 'is_new', $question_get_qameta );

		// Test for answer.
		$this->assertArrayHasKey( 'post_id', $answer_get_qameta );
		$this->assertArrayHasKey( 'selected', $answer_get_qameta );
		$this->assertArrayHasKey( 'selected_id', $answer_get_qameta );
		$this->assertArrayHasKey( 'comments', $answer_get_qameta );
		$this->assertArrayHasKey( 'answers', $answer_get_qameta );
		$this->assertArrayHasKey( 'ptype', $answer_get_qameta );
		$this->assertArrayHasKey( 'featured', $answer_get_qameta );
		$this->assertArrayHasKey( 'closed', $answer_get_qameta );
		$this->assertArrayHasKey( 'views', $answer_get_qameta );
		$this->assertArrayHasKey( 'votes_up', $answer_get_qameta );
		$this->assertArrayHasKey( 'votes_down', $answer_get_qameta );
		$this->assertArrayHasKey( 'subscribers', $answer_get_qameta );
		$this->assertArrayHasKey( 'flags', $answer_get_qameta );
		$this->assertArrayHasKey( 'terms', $answer_get_qameta );
		$this->assertArrayHasKey( 'attach', $answer_get_qameta );
		$this->assertArrayHasKey( 'activities', $answer_get_qameta );
		$this->assertArrayHasKey( 'fields', $answer_get_qameta );
		$this->assertArrayHasKey( 'roles', $answer_get_qameta );
		$this->assertArrayHasKey( 'last_updated', $answer_get_qameta );
		$this->assertArrayHasKey( 'is_new', $answer_get_qameta );

		// Test if getting the correct values.
		// Test for selected answer.
		$id = $this->insert_answer();
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( '', $question_get_qameta->selected_id );
		$this->assertEquals( 0, $answer_get_qameta->selected );
		ap_set_selected_answer( $id->q, $id->a );
		ap_update_answer_selected( $id->a );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( $id->a, $question_get_qameta->selected_id );
		$this->assertEquals( 1, $answer_get_qameta->selected );

		// Test for closed question.
		$id = $this->insert_answer();
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->closed );
		ap_toggle_close_question( $id->q );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 1, $question_get_qameta->closed );
		ap_toggle_close_question( $id->q );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->closed );

		// Test for featured question.
		$id = $this->insert_answer();
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->featured );
		ap_set_featured_question( $id->q );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 1, $question_get_qameta->featured );
		ap_unset_featured_question( $id->q );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->featured );
	}

	/**
	 * @covers ::ap_insert_qameta
	 */
	public function testAPInsertQameta() {
		$id = $this->insert_answer();

		// Test for inserting the selected answer.
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( '', $question_get_qameta->selected_id );
		$this->assertEquals( 0, $answer_get_qameta->selected );
		ap_insert_qameta(
			$id->q,
			array(
				'selected_id'  => $id->a,
				'last_updated' => current_time( 'mysql' ),
			)
		);
		ap_insert_qameta(
			$id->a,
			array(
				'selected'     => 1,
				'last_updated' => current_time( 'mysql' ),
			)
		);
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( $id->a, $question_get_qameta->selected_id );
		$this->assertEquals( 1, $answer_get_qameta->selected );

		// Test for closed question.
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->closed );
		ap_insert_qameta( $id->q, array( 'closed' => 1 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 1, $question_get_qameta->closed );
		ap_insert_qameta( $id->q, array( 'closed' => 0 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->closed );

		// Test for featured question.
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->featured );
		ap_insert_qameta( $id->q, array( 'featured' => 1 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 1, $question_get_qameta->featured );
		ap_insert_qameta( $id->q, array( 'featured' => 0 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->featured );

		// Test for flags count.
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->flags );
		$this->assertEquals( 0, $answer_get_qameta->flags );
		ap_insert_qameta( $id->q, array( 'flags' => 100 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 100, $question_get_qameta->flags );
		$this->assertEquals( 100, $answer_get_qameta->flags );
		ap_insert_qameta( $id->q, array( 'flags' => 500 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 500, $question_get_qameta->flags );
		$this->assertEquals( 500, $answer_get_qameta->flags );

		// Test for views count.
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 0, $question_get_qameta->views );
		$this->assertEquals( 0, $answer_get_qameta->views );
		ap_insert_qameta( $id->q, array( 'views' => 100 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 100, $question_get_qameta->views );
		$this->assertEquals( 100, $answer_get_qameta->views );
		ap_insert_qameta( $id->q, array( 'views' => 500 ) );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->q );
		$this->assertEquals( 500, $question_get_qameta->views );
		$this->assertEquals( 500, $answer_get_qameta->views );
	}

	/**
	 * @covers ::ap_append_qameta
	 */
	public function testAPAppendQameta() {
		$id = $this->insert_question();

		// Post meta check before appending.
		$post = get_post( $id );
		$post = (array) $post;
		$this->assertArrayNotHasKey( 'selected', $post );
		$this->assertArrayNotHasKey( 'selected_id', $post );
		$this->assertArrayNotHasKey( 'comments', $post );
		$this->assertArrayNotHasKey( 'answers', $post );
		$this->assertArrayNotHasKey( 'ptype', $post );
		$this->assertArrayNotHasKey( 'featured', $post );
		$this->assertArrayNotHasKey( 'closed', $post );
		$this->assertArrayNotHasKey( 'views', $post );
		$this->assertArrayNotHasKey( 'votes_up', $post );
		$this->assertArrayNotHasKey( 'votes_down', $post );
		$this->assertArrayNotHasKey( 'subscribers', $post );
		$this->assertArrayNotHasKey( 'flags', $post );
		$this->assertArrayNotHasKey( 'terms', $post );
		$this->assertArrayNotHasKey( 'attach', $post );
		$this->assertArrayNotHasKey( 'activities', $post );
		$this->assertArrayNotHasKey( 'fields', $post );
		$this->assertArrayNotHasKey( 'roles', $post );
		$this->assertArrayNotHasKey( 'last_updated', $post );
		$this->assertArrayNotHasKey( 'is_new', $post );

		// Post meta check after appending.
		$new_post = get_post( $id );
		$append_qameta = ap_append_qameta( $new_post );
		$append_qameta = (array) $append_qameta;
		$this->assertArrayHasKey( 'selected', $append_qameta );
		$this->assertArrayHasKey( 'selected_id', $append_qameta );
		$this->assertArrayHasKey( 'comments', $append_qameta );
		$this->assertArrayHasKey( 'answers', $append_qameta );
		$this->assertArrayHasKey( 'ptype', $append_qameta );
		$this->assertArrayHasKey( 'featured', $append_qameta );
		$this->assertArrayHasKey( 'closed', $append_qameta );
		$this->assertArrayHasKey( 'views', $append_qameta );
		$this->assertArrayHasKey( 'votes_up', $append_qameta );
		$this->assertArrayHasKey( 'votes_down', $append_qameta );
		$this->assertArrayHasKey( 'subscribers', $append_qameta );
		$this->assertArrayHasKey( 'flags', $append_qameta );
		$this->assertArrayHasKey( 'terms', $append_qameta );
		$this->assertArrayHasKey( 'attach', $append_qameta );
		$this->assertArrayHasKey( 'activities', $append_qameta );
		$this->assertArrayHasKey( 'fields', $append_qameta );
		$this->assertArrayHasKey( 'roles', $append_qameta );
		$this->assertArrayHasKey( 'last_updated', $append_qameta );
		$this->assertArrayHasKey( 'is_new', $append_qameta );
	}
}
