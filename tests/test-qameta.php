<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAMeta extends TestCase {

	use Testcases\Common;

	public function set_up() {
		parent::set_up();
		register_taxonomy( 'question_category', array( 'question' ) );
		register_taxonomy( 'question_tag', array( 'question' ) );
	}

	public function tear_down() {
		unregister_taxonomy( 'question_category' );
		unregister_taxonomy( 'question_tag' );
		parent::tear_down();
	}

	/**
	 * @covers ::ap_qameta_fields
	 */
	public function testAPQametaFields() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		// Test for user roles.
		$this->setRole( 'subscriber' );
		$id = $this->insert_question();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $id );
		$result = ap_update_post_attach_ids( $id );
		$this->assertEquals( [ $attachment_id ], $result );
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( $attachment_id, $qameta->attach );

		$id = $this->insert_question();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $id );
		$result = ap_update_post_attach_ids( $id );
		$this->assertEquals( [ $attachment_id ], $result );
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( $attachment_id, $qameta->attach );

		$id = $this->insert_question();
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $id );
		$result = ap_update_post_attach_ids( $id );
		$this->assertEquals( [ $attachment_id ], $result );
		$qameta = ap_get_qameta( $id );
		$this->assertEquals( $attachment_id, $qameta->attach );
	}

	/**
	 * @covers ::ap_update_votes_count
	 */
	public function testAPUpdateVotesCount() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		$id = $this->insert_answer();
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );

		// Test for question.
		$this->assertObjectHasProperty( 'post_id', $question_get_qameta );
		$this->assertObjectHasProperty( 'selected', $question_get_qameta );
		$this->assertObjectHasProperty( 'selected_id', $question_get_qameta );
		$this->assertObjectHasProperty( 'comments', $question_get_qameta );
		$this->assertObjectHasProperty( 'answers', $question_get_qameta );
		$this->assertObjectHasProperty( 'ptype', $question_get_qameta );
		$this->assertObjectHasProperty( 'featured', $question_get_qameta );
		$this->assertObjectHasProperty( 'closed', $question_get_qameta );
		$this->assertObjectHasProperty( 'views', $question_get_qameta );
		$this->assertObjectHasProperty( 'votes_up', $question_get_qameta );
		$this->assertObjectHasProperty( 'votes_down', $question_get_qameta );
		$this->assertObjectHasProperty( 'subscribers', $question_get_qameta );
		$this->assertObjectHasProperty( 'flags', $question_get_qameta );
		$this->assertObjectHasProperty( 'terms', $question_get_qameta );
		$this->assertObjectHasProperty( 'attach', $question_get_qameta );
		$this->assertObjectHasProperty( 'activities', $question_get_qameta );
		$this->assertObjectHasProperty( 'fields', $question_get_qameta );
		$this->assertObjectHasProperty( 'roles', $question_get_qameta );
		$this->assertObjectHasProperty( 'last_updated', $question_get_qameta );
		$this->assertObjectHasProperty( 'is_new', $question_get_qameta );

		// Test for answer.
		$this->assertObjectHasProperty( 'post_id', $answer_get_qameta );
		$this->assertObjectHasProperty( 'selected', $answer_get_qameta );
		$this->assertObjectHasProperty( 'selected_id', $answer_get_qameta );
		$this->assertObjectHasProperty( 'comments', $answer_get_qameta );
		$this->assertObjectHasProperty( 'answers', $answer_get_qameta );
		$this->assertObjectHasProperty( 'ptype', $answer_get_qameta );
		$this->assertObjectHasProperty( 'featured', $answer_get_qameta );
		$this->assertObjectHasProperty( 'closed', $answer_get_qameta );
		$this->assertObjectHasProperty( 'views', $answer_get_qameta );
		$this->assertObjectHasProperty( 'votes_up', $answer_get_qameta );
		$this->assertObjectHasProperty( 'votes_down', $answer_get_qameta );
		$this->assertObjectHasProperty( 'subscribers', $answer_get_qameta );
		$this->assertObjectHasProperty( 'flags', $answer_get_qameta );
		$this->assertObjectHasProperty( 'terms', $answer_get_qameta );
		$this->assertObjectHasProperty( 'attach', $answer_get_qameta );
		$this->assertObjectHasProperty( 'activities', $answer_get_qameta );
		$this->assertObjectHasProperty( 'fields', $answer_get_qameta );
		$this->assertObjectHasProperty( 'roles', $answer_get_qameta );
		$this->assertObjectHasProperty( 'last_updated', $answer_get_qameta );
		$this->assertObjectHasProperty( 'is_new', $answer_get_qameta );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

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
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		$id = $this->insert_question();

		// Post meta check before appending.
		$post = get_post( $id );
		$this->assertObjectNotHasProperty( 'selected', $post );
		$this->assertObjectNotHasProperty( 'selected_id', $post );
		$this->assertObjectNotHasProperty( 'comments', $post );
		$this->assertObjectNotHasProperty( 'answers', $post );
		$this->assertObjectNotHasProperty( 'ptype', $post );
		$this->assertObjectNotHasProperty( 'featured', $post );
		$this->assertObjectNotHasProperty( 'closed', $post );
		$this->assertObjectNotHasProperty( 'views', $post );
		$this->assertObjectNotHasProperty( 'votes_up', $post );
		$this->assertObjectNotHasProperty( 'votes_down', $post );
		$this->assertObjectNotHasProperty( 'subscribers', $post );
		$this->assertObjectNotHasProperty( 'flags', $post );
		$this->assertObjectNotHasProperty( 'terms', $post );
		$this->assertObjectNotHasProperty( 'attach', $post );
		$this->assertObjectNotHasProperty( 'activities', $post );
		$this->assertObjectNotHasProperty( 'fields', $post );
		$this->assertObjectNotHasProperty( 'roles', $post );
		$this->assertObjectNotHasProperty( 'last_updated', $post );
		$this->assertObjectNotHasProperty( 'is_new', $post );

		// Post meta check after appending.
		$new_post = get_post( $id );
		$append_qameta = ap_append_qameta( $new_post );
		$this->assertObjectHasProperty( 'selected', $append_qameta );
		$this->assertObjectHasProperty( 'selected_id', $append_qameta );
		$this->assertObjectHasProperty( 'comments', $append_qameta );
		$this->assertObjectHasProperty( 'answers', $append_qameta );
		$this->assertObjectHasProperty( 'ptype', $append_qameta );
		$this->assertObjectHasProperty( 'featured', $append_qameta );
		$this->assertObjectHasProperty( 'closed', $append_qameta );
		$this->assertObjectHasProperty( 'views', $append_qameta );
		$this->assertObjectHasProperty( 'votes_up', $append_qameta );
		$this->assertObjectHasProperty( 'votes_down', $append_qameta );
		$this->assertObjectHasProperty( 'subscribers', $append_qameta );
		$this->assertObjectHasProperty( 'flags', $append_qameta );
		$this->assertObjectHasProperty( 'terms', $append_qameta );
		$this->assertObjectHasProperty( 'attach', $append_qameta );
		$this->assertObjectHasProperty( 'activities', $append_qameta );
		$this->assertObjectHasProperty( 'fields', $append_qameta );
		$this->assertObjectHasProperty( 'roles', $append_qameta );
		$this->assertObjectHasProperty( 'last_updated', $append_qameta );
		$this->assertObjectHasProperty( 'is_new', $append_qameta );

		// Get all qafields data in an array.
		$qafields = [ 'post_id', 'selected', 'selected_id', 'comments', 'answers', 'ptype', 'featured', 'closed', 'views', 'votes_up', 'votes_down', 'subscribers', 'flags', 'terms', 'attach', 'activities', 'fields', 'roles', 'last_updated', 'is_new' ];

		// Test for normal post type.
		$post = $this->factory->post->create_and_get(
			array(
				'post_title'   => 'Post title',
				'post_content' => 'Post content',
			)
		);
		ap_append_qameta( $post );
		foreach ( $qafields as $field ) {
			$this->assertObjectNotHasProperty( $field, $post );
		}

		// Test for question post type.
		$question = $this->factory->post->create_and_get(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		ap_append_qameta( $question );
		foreach ( $qafields as $field ) {
			$this->assertObjectHasProperty( $field, $question );
		}

		// Test for answer post type.
		$answer = $this->factory->post->create_and_get(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
			)
		);
		ap_append_qameta( $answer );
		foreach ( $qafields as $field ) {
			$this->assertObjectHasProperty( $field, $answer );
		}
	}

	/**
	 * @covers ::ap_update_flags_count
	 */
	public function testAPUpdateFlagsCount() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		$id = $this->insert_answer();

		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 0, $question_get_qameta->flags );
		$this->assertEquals( 0, $answer_get_qameta->flags );
		ap_update_flags_count( $id->q );
		ap_update_flags_count( $id->a );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 0, $question_get_qameta->flags );
		$this->assertEquals( 0, $answer_get_qameta->flags );
		ap_add_flag( $id->q );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->q );
		ap_update_flags_count( $id->a );
		$question_get_qameta = ap_get_qameta( $id->q );
		$answer_get_qameta = ap_get_qameta( $id->a );
		$this->assertEquals( 1, $question_get_qameta->flags );
		$this->assertEquals( 1, $answer_get_qameta->flags );
	}

	/**
	 * @covers ::ap_update_qameta_terms
	 */
	public function testAPUpdateQametaTerms() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		$id = $this->insert_question();

		// Test begins.
		$question_get_qameta = ap_get_qameta( $id );
		$this->assertEmpty( $question_get_qameta->terms );
		$cid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		$ncid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_category',
			)
		);
		wp_set_object_terms( $id, array( $cid, $ncid ), 'question_category' );
		do_action( 'save_post_question', $id, get_post( $id ), true );
		$question_get_qameta = ap_get_qameta( $id );
		$this->assertNotEmpty( $question_get_qameta->terms );
		$tid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_tag',
			)
		);
		$ntid = $this->factory->term->create(
			array(
				'taxonomy' => 'question_tag',
			)
		);
		wp_set_object_terms( $id, array( $tid, $ntid ), 'question_tag' );
		do_action( 'save_post_question', $id, get_post( $id ), true );
		$question_get_qameta = ap_get_qameta( $id );
		$this->assertNotEmpty( $question_get_qameta->terms );
	}

	/**
	 * @covers ::ap_get_post_field
	 * @covers ::ap_post_field
	 */
	public function testAPGetPostField() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		// Add question.
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question Content',
				'post_type'    => 'question',
			)
		);

		// Test starts.
		$this->assertEquals( 'Question title', ap_get_post_field( 'post_title', $id ) );
		$this->assertEquals( 'Question Content', ap_get_post_field( 'post_content', $id ) );
		$this->assertEquals( '', ap_get_post_field( 'fields', $id ) );
		ob_start();
		ap_post_field( 'post_title', $id );
		$output = ob_get_clean();
		$this->assertEquals( 'Question title', $output );
		ob_start();
		ap_post_field( 'post_content', $id );
		$output = ob_get_clean();
		$this->assertEquals( 'Question Content', $output );
		ob_start();
		ap_post_field( 'fields', $id );
		$output = ob_get_clean();
		$this->assertEquals( '', $output );

		// After adding the qameta field.
		ap_insert_qameta( $id, [ 'fields' => [ 'anonymous_name' => 'Rahul' ] ] );
		$post_fields = ap_get_post_field( 'fields', $id );
		$this->assertEquals( 'Rahul', $post_fields['anonymous_name'] );
		$this->assertEquals( [ 'anonymous_name' => 'Rahul' ], $post_fields );

		// Test for invalid value.
		$this->assertEquals( '', ap_get_post_field( '', $id ) );
		$this->assertEquals( '', ap_get_post_field( 'invalid_field', $id ) );
		ob_start();
		ap_post_field( '', $id );
		$output = ob_get_clean();
		$this->assertEquals( '', $output );
		ob_start();
		ap_post_field( 'invalid_field', $id );
		$output = ob_get_clean();
		$this->assertEquals( '', $output );

		// Test for null post id without visting the question page.
		$this->assertEquals( '', ap_get_post_field( 'post_title' ) );
		$this->assertEquals( '', ap_get_post_field( 'post_content' ) );
		ob_start();
		ap_post_field( 'post_title' );
		$output = ob_get_clean();
		$this->assertEquals( '', $output );
		ob_start();
		ap_post_field( 'post_content' );
		$output = ob_get_clean();
		$this->assertEquals( '', $output );

		// Test for null post id by visting the question page.
		$this->go_to( '/?post_type=question&p=' . $id );
		$this->assertEquals( 'Question title', ap_get_post_field( 'post_title' ) );
		$this->assertEquals( 'Question Content', ap_get_post_field( 'post_content' ) );
		ob_start();
		ap_post_field( 'post_title' );
		$output = ob_get_clean();
		$this->assertEquals( 'Question title', $output );
		ob_start();
		ap_post_field( 'post_content' );
		$output = ob_get_clean();
		$this->assertEquals( 'Question Content', $output );
	}

	/**
	 * @covers ::ap_update_post_activities
	 */
	public function testAPUpdatePostActivities() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		// Test for empty activities.
		$id = $this->insert_question();

		// Call the function.
		$result = ap_update_post_activities( $id );

		// Test begins.
		$this->assertNotEmpty( $result );
		$this->assertIsInt( $result );

		// Get the qameta from the question to test assertions.
		$qameta = ap_get_qameta( $id );
		$this->assertEmpty( $qameta->activities );

		// Test for passing activities.
		$id = $this->insert_question();
		$activities = [
			'action' => 'new_q',
		];

		// Call the function.
		$result = ap_update_post_activities( $id, $activities );

		// Test begins.
		$this->assertNotEmpty( $result );
		$this->assertIsInt( $result );

		// Get the qameta from the question to test assertions.
		$qameta = ap_get_qameta( $id );
		$this->assertNotEmpty( $qameta->activities );
		$this->assertIsArray( $qameta->activities );
		$this->assertArrayHasKey( 'action', $qameta->activities );
		$this->assertEquals( 'new_q', $qameta->activities['action'] );
		$this->assertEquals( current_time( 'mysql' ), $qameta->last_updated );
	}

	/**
	 * @covers ::ap_update_post_activity_meta
	 */
	function testAPUpdatePostActivityMeta() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Test with empty question id.
		$output = ap_update_post_activity_meta( '', 'new_q', $user_id );
		$this->assertFalse( $output );

		// Test by passing the question id.
		$id = $this->insert_question();
		$output = ap_update_post_activity_meta( $id, 'new_q', $user_id );
		$this->assertNotEmpty( $output );
		$this->assertIsInt( $output );

		// Get the qameta from the question to test assertions.
		$qameta = ap_get_qameta( $id );
		$this->assertNotEmpty( $qameta->activities );
		$this->assertIsArray( $qameta->activities );
		$this->assertArrayHasKey( 'type', $qameta->activities );
		$this->assertArrayHasKey( 'user_id', $qameta->activities );
		$this->assertArrayHasKey( 'date', $qameta->activities );
		$this->assertEquals( 'new_q', $qameta->activities['type'] );
		$this->assertEquals( $user_id, $qameta->activities['user_id'] );
		$this->assertEquals( current_time( 'mysql' ), $qameta->activities['date'] );
		$this->assertEquals( current_time( 'mysql' ), $qameta->last_updated );

		// Test by passing the question id with different date.
		$id = $this->insert_question();
		$output = ap_update_post_activity_meta( $id, 'new_q', $user_id, false, '2024-01-01 12:00:00' );
		$this->assertNotEmpty( $output );
		$this->assertIsInt( $output );

		// Get the qameta from the question to test assertions.
		$qameta = ap_get_qameta( $id );
		$this->assertNotEmpty( $qameta->activities );
		$this->assertIsArray( $qameta->activities );
		$this->assertArrayHasKey( 'type', $qameta->activities );
		$this->assertArrayHasKey( 'user_id', $qameta->activities );
		$this->assertArrayHasKey( 'date', $qameta->activities );
		$this->assertEquals( 'new_q', $qameta->activities['type'] );
		$this->assertEquals( $user_id, $qameta->activities['user_id'] );
		$this->assertEquals( '2024-01-01 12:00:00', $qameta->activities['date'] );
		$this->assertEquals( current_time( 'mysql' ), $qameta->last_updated );

		// Test by passing the question id and appending to question enabled.
		$id = $this->insert_answer();
		$output = ap_update_post_activity_meta( $id->a, 'new_a', $user_id, true, '2024-01-01 12:00:00' );
		$this->assertNotEmpty( $output );
		$this->assertIsInt( $output );

		// Get the qameta from the question to test assertions.
		$qameta = ap_get_qameta( $id->q );
		$this->assertNotEmpty( $qameta->activities );
		$this->assertIsArray( $qameta->activities );
		$this->assertIsArray( $qameta->activities['child'] );
		$this->assertArrayHasKey( 'child', $qameta->activities );
		$this->assertArrayHasKey( 'type', $qameta->activities['child'] );
		$this->assertArrayHasKey( 'user_id', $qameta->activities['child'] );
		$this->assertArrayHasKey( 'date', $qameta->activities['child'] );
		$this->assertEquals( 'new_a', $qameta->activities['child']['type'] );
		$this->assertEquals( $user_id, $qameta->activities['child']['user_id'] );
		$this->assertEquals( '2024-01-01 12:00:00', $qameta->activities['child']['date'] );
		$this->assertEquals( current_time( 'mysql' ), $qameta->last_updated );
	}

	/**
	 * @covers ::ap_update_user_unpublished_count
	 */
	public function testAPUpdateUserUnpublishedCount() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_qameta}" );

		// Test for not adding any user id.
		$this->setRole( 'subscriber' );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'draft' ] );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate' ] );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'publish' ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'draft' ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'moderate' ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'publish' ] );

		// Call the function.
		ap_update_user_unpublished_count();

		// Test assertions.
		$this->assertEquals( 2, get_user_meta( get_current_user_id(), '__ap_unpublished_questions', true ) );
		$this->assertEquals( 2, get_user_meta( get_current_user_id(), '__ap_unpublished_answers', true ) );
		$this->assertNotEquals( 3, get_user_meta( get_current_user_id(), '__ap_unpublished_questions', true ) );
		$this->assertNotEquals( 3, get_user_meta( get_current_user_id(), '__ap_unpublished_answers', true ) );

		// Test for adding user id.
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'draft', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'moderate', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'question', 'post_status' => 'trash', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'draft', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'moderate', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'answer', 'post_status' => 'trash', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'post', 'post_status' => 'trash', 'post_author' => $user_id ] );
		$this->factory->post->create( [ 'post_type' => 'page', 'post_status' => 'draft', 'post_author' => $user_id ] );

		// Call the function.
		ap_update_user_unpublished_count( $user_id );

		// Test assertions.
		$this->assertEquals( 3, get_user_meta( $user_id, '__ap_unpublished_questions', true ) );
		$this->assertEquals( 3, get_user_meta( $user_id, '__ap_unpublished_answers', true ) );
	}

	/**
	 * @covers ::ap_insert_qameta
	 */
	public function testAPInsertQametaShouldReturnFalseOnEmptyPostIdArg() {
		// Test 1.
		$result = ap_insert_qameta( '', [] );
		$this->assertFalse( $result );

		// Test 2.
		$result = ap_insert_qameta( 0, [] );
		$this->assertFalse( $result );
	}

	/**
	 * @covers ::ap_insert_qameta
	 */
	public function testAPInsertQametaShouldReturnWPErrorMessageOnEmptyPostIdAndWPErrorArgs() {
		// Test 1.
		$result = ap_insert_qameta( '', [], true );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'empty_post_id', $result->get_error_code() );
		$this->assertEquals( 'Post ID is required', $result->get_error_message() );

		// Test 2.
		$result = ap_insert_qameta( 0, [], true );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'empty_post_id', $result->get_error_code() );
		$this->assertEquals( 'Post ID is required', $result->get_error_message() );
	}
}
