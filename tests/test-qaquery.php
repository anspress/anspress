<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAQuery extends TestCase {

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
	 * @covers ::ap_question_status
	 */
	public function testAPQuestionStatus() {
		// Test on publish post status.
		$id = $this->insert_question();
		$this->assertNull( ap_question_status( $id ) );

		// Test on other post statuses.
		// Moderate post status.
		$q1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		ob_start();
		ap_question_status( $q1_id );
		$moderate_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status moderate">Moderate</span>', $moderate_post_status );

		// Private post post status.
		$q2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
			)
		);
		ob_start();
		ap_question_status( $q2_id );
		$private_post_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status private_post">Private</span>', $private_post_status );

		// Future post status.
		$q3_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'future',
				'post_date'    => '9999-12-31 23:59:59',
			)
		);
		ob_start();
		ap_question_status( $q3_id );
		$future_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status future">Scheduled</span>', $future_status );

		// Draft post status.
		$q4_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'draft',
			)
		);
		ob_start();
		ap_question_status( $q4_id );
		$draft_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status draft">Draft</span>', $draft_status );

		// Pending review post status.
		$q5_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'pending',
			)
		);
		ob_start();
		ap_question_status( $q5_id );
		$pending_status = ob_get_clean();
		$this->assertEquals( '<span class="ap-post-status pending">Pending</span>', $pending_status );
	}

	/**
	 * @covers ::ap_get_answers_count
	 * @covers ::ap_answers_count
	 */
	public function testAPGetAnswersCount() {
		// Test on no answers to a question.
		$id = $this->insert_question();
		$this->assertEquals( 0, ap_get_answers_count( $id ) );
		ob_start();
		ap_answers_count( $id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 0, $answers_count );

		// Test on single answer to a question.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_get_answers_count( $id->q ) );
		ob_start();
		ap_answers_count( $id->q );
		$answers_count = ob_get_clean();
		$this->assertEquals( 1, $answers_count );

		// Test on many answers to a question.
		$id = $this->insert_answers( [], [], 10 );
		$this->assertEquals( 10, ap_get_answers_count( $id['question'] ) );
		ob_start();
		ap_answers_count( $id['question'] );
		$answers_count = ob_get_clean();
		$this->assertEquals( 10, $answers_count );

		// Test on additional answers to a question.
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 11, ap_get_answers_count( $id['question'] ) );
		ob_start();
		ap_answers_count( $id['question'] );
		$answers_count = ob_get_clean();
		$this->assertEquals( 11, $answers_count );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id['question'],
			)
		);
		$this->assertEquals( 12, ap_get_answers_count( $id['question'] ) );
		ob_start();
		ap_answers_count( $id['question'] );
		$answers_count = ob_get_clean();
		$this->assertEquals( 12, $answers_count );

		// Test on all post status.
		$q_id = $this->insert_question();
		$this->assertEquals( 0, ap_get_answers_count( $q_id ) );
		ob_start();
		ap_answers_count( $q_id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 0, $answers_count );
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
			)
		);
		$this->assertEquals( 1, ap_get_answers_count( $q_id ) );
		ob_start();
		ap_answers_count( $q_id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 1, $answers_count );
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'private_post',
			)
		);
		$this->assertEquals( 1, ap_get_answers_count( $q_id ) );
		ob_start();
		ap_answers_count( $q_id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 1, $answers_count );
		$a3_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'moderate',
			)
		);
		$this->assertEquals( 1, ap_get_answers_count( $q_id ) );
		ob_start();
		ap_answers_count( $q_id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 1, $answers_count );
		$a4_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $q_id,
				'post_status'  => 'publish',
			)
		);
		$this->assertEquals( 2, ap_get_answers_count( $q_id ) );
		ob_start();
		ap_answers_count( $q_id );
		$answers_count = ob_get_clean();
		$this->assertEquals( 2, $answers_count );

		// Test after the question having a selected answer.
		$id = $this->insert_answer();
		$this->assertEquals( 1, ap_get_answers_count( $id->q ) );
		ob_start();
		ap_answers_count( $id->q );
		$answers_count = ob_get_clean();
		$this->assertEquals( 1, $answers_count );
		$a1_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$a2_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$this->assertEquals( 3, ap_get_answers_count( $id->q ) );
		ob_start();
		ap_answers_count( $id->q );
		$answers_count = ob_get_clean();
		$this->assertEquals( 3, $answers_count );
		ap_set_selected_answer( $id->q, $a1_id );
		$this->assertEquals( 3, ap_get_answers_count( $id->q ) );
		ob_start();
		ap_answers_count( $id->q );
		$answers_count = ob_get_clean();
		$this->assertEquals( 3, $answers_count );
	}

	/**
	 * @covers ::ap_get_votes_net
	 * @covers ::ap_votes_net
	 */
	public function testAPGetVotesNet() {
		$this->setRole( 'subscriber' );

		// Test for no votes.
		$id = $this->insert_answer();

		// On question.
		$this->assertEquals( 0, ap_get_votes_net( $id->q ) );
		ob_start();
		ap_votes_net( $id->q );
		$question_votes_count = ob_get_clean();
		$this->assertEquals( 0, $question_votes_count );

		// On answer.
		$this->assertEquals( 0, ap_get_votes_net( $id->a ) );
		ob_start();
		ap_votes_net( $id->q );
		$answer_votes_count = ob_get_clean();
		$this->assertEquals( 0, $answer_votes_count );

		// Adding vote on question.
		ap_add_post_vote( $id->q );
		$this->assertEquals( 1, ap_get_votes_net( $id->q ) );
		ob_start();
		ap_votes_net( $id->q );
		$question_votes_count = ob_get_clean();
		$this->assertEquals( 1, $question_votes_count );

		// Adding vote on answer.
		ap_add_post_vote( $id->a );
		$this->assertEquals( 1, ap_get_votes_net( $id->a ) );
		ob_start();
		ap_votes_net( $id->a );
		$answer_votes_count = ob_get_clean();
		$this->assertEquals( 1, $answer_votes_count );

		// Adding vote down on question.
		ap_add_post_vote( $id->q, false, false );
		$this->assertEquals( 0, ap_get_votes_net( $id->q ) );
		ob_start();
		ap_votes_net( $id->q );
		$question_votes_count = ob_get_clean();
		$this->assertEquals( 0, $question_votes_count );

		// Adding vote down on answer.
		ap_add_post_vote( $id->a, false, false );
		$this->assertEquals( 0, ap_get_votes_net( $id->a ) );
		ob_start();
		ap_votes_net( $id->a );
		$answer_votes_count = ob_get_clean();
		$this->assertEquals( 0, $answer_votes_count );

		// Adding additional votes.
		$id = $this->insert_answer();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// On question.
		ap_add_post_vote( $id->q );
		ap_add_post_vote( $id->q, $user_id );
		$this->assertEquals( 2, ap_get_votes_net( $id->q ) );
		ob_start();
		ap_votes_net( $id->q );
		$question_votes_count = ob_get_clean();
		$this->assertEquals( 2, $question_votes_count );

		// On answer.
		ap_add_post_vote( $id->a );
		ap_add_post_vote( $id->a, $user_id );
		$this->assertEquals( 2, ap_get_votes_net( $id->a ) );
		ob_start();
		ap_votes_net( $id->a );
		$answer_votes_count = ob_get_clean();
		$this->assertEquals( 2, $answer_votes_count );

		// Adding more additional votes for testing.
		$id = $this->insert_answer();
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$latest_user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// On question.
		ap_add_post_vote( $id->q, get_current_user_id() );
		ap_add_post_vote( $id->q, $user_id );
		ap_add_post_vote( $id->q, $new_user_id, false );
		ap_add_post_vote( $id->q, $latest_user_id, true );
		$this->assertEquals( 2, ap_get_votes_net( $id->q ) );
		ob_start();
		ap_votes_net( $id->q );
		$question_votes_count = ob_get_clean();
		$this->assertEquals( 2, $question_votes_count );

		// On answer.
		ap_add_post_vote( $id->a, get_current_user_id() );
		ap_add_post_vote( $id->a, $user_id );
		ap_add_post_vote( $id->a, $new_user_id, false );
		ap_add_post_vote( $id->a, $latest_user_id, true );
		$this->assertEquals( 2, ap_get_votes_net( $id->a ) );
		ob_start();
		ap_votes_net( $id->a );
		$answer_votes_count = ob_get_clean();
		$this->assertEquals( 2, $answer_votes_count );
	}

	/**
	 * @covers ::ap_get_last_active
	 * @covers ::ap_last_active
	 */
	public function testAPGetLastActive() {
		// Test on current mysql post date.
		$id = $this->insert_answer();

		// Test for question.
		$this->assertEquals( '1 second ago', ap_get_last_active( $id->q ) );
		ob_start();
		ap_last_active( $id->q );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );

		// Test for answer.
		$this->assertEquals( '1 second ago', ap_get_last_active( $id->a ) );
		ob_start();
		ap_last_active( $id->q );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );

		// Test on 5 minutes ago post date.
		// Test for question.
		$now = date( 'Y-m-d H:i:s' );
		$timestamp = strtotime( $now );
		$time = $timestamp - (5 * 60);
		$datetime = date( 'Y-m-d H:i:s', $time );
		$q_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
				'post_date'     => $datetime,
			)
		);
		ap_insert_qameta(
			$q_id,
			[
				'last_updated' => $datetime,
			]
		);
		$this->assertEquals( '5 mins ago', ap_get_last_active( $q_id ) );
		ob_start();
		ap_last_active( $q_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '5 mins ago', $last_active );

		// After updating the question.
		wp_update_post(
			array(
				'ID' => $q_id
			)
		);
		$this->assertEquals( '1 second ago', ap_get_last_active( $q_id ) );
		ob_start();
		ap_last_active( $q_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );

		// Test for answer.
		$a_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
				'post_date'     => $datetime,
			)
		);
		ap_insert_qameta(
			$a_id,
			[
				'last_updated' => $datetime,
			]
		);
		$this->assertEquals( '5 mins ago', ap_get_last_active( $a_id ) );
		ob_start();
		ap_last_active( $a_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '5 mins ago', $last_active );

		// After updating the answer.
		wp_update_post(
			array(
				'ID' => $a_id
			)
		);
		$this->assertEquals( '1 second ago', ap_get_last_active( $a_id ) );
		ob_start();
		ap_last_active( $a_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );

		// Test on 3 hours ago post date.
		// Test for question.
		$now = date( 'Y-m-d H:i:s' );
		$timestamp = strtotime( $now );
		$time = $timestamp - (3 * 60 * 60);
		$datetime = date( 'Y-m-d H:i:s', $time );
		$q_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
				'post_date'     => $datetime,
			)
		);
		ap_insert_qameta(
			$q_id,
			[
				'last_updated' => $datetime,
			]
		);
		$this->assertEquals( '3 hours ago', ap_get_last_active( $q_id ) );
		ob_start();
		ap_last_active( $q_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '3 hours ago', $last_active );

		// After updating the question.
		wp_update_post(
			array(
				'ID' => $q_id
			)
		);
		$this->assertEquals( '1 second ago', ap_get_last_active( $q_id ) );
		ob_start();
		ap_last_active( $q_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );

		// Test for answer.
		$a_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
				'post_date'     => $datetime,
			)
		);
		ap_insert_qameta(
			$a_id,
			[
				'last_updated' => $datetime,
			]
		);
		$this->assertEquals( '3 hours ago', ap_get_last_active( $a_id ) );
		ob_start();
		ap_last_active( $a_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '3 hours ago', $last_active );

		// After updating the answer.
		wp_update_post(
			array(
				'ID' => $a_id
			)
		);
		$this->assertEquals( '1 second ago', ap_get_last_active( $a_id ) );
		ob_start();
		ap_last_active( $a_id );
		$last_active = ob_get_clean();
		$this->assertEquals( '1 second ago', $last_active );
	}

	/**
	 * @covers ::ap_have_answer_selected
	 */
	public function testAPHaveAnswerSelected() {
		// Test for not having selected answer.
		$id = $this->insert_answer();
		$this->assertFalse( ap_have_answer_selected( $id->q ) );

		// Test for having selected answer.
		$id = $this->insert_answer();
		$this->assertFalse( ap_have_answer_selected( $id->q ) );
		ap_set_selected_answer( $id->q, $id->a );
		$this->assertTrue( ap_have_answer_selected( $id->q ) );

		// Additional tests.
		$q_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
			)
		);
		$this->assertFalse( ap_have_answer_selected( $q_id ) );
		ap_set_selected_answer( $q_id, $a_id );
		$this->assertTrue( ap_have_answer_selected( $q_id ) );
	}

	/**
	 * @covers ::ap_selected_answer
	 */
	public function testAPSelectedAnswer() {
		// Test for not passing any posts.
		$this->assertFalse( ap_selected_answer() );

		// Test for not having any answer selected.
		$id = $this->insert_answer();
		$this->assertNull( ap_selected_answer( $id->q ) );

		// Test for having selected answer.
		$id = $this->insert_answer();
		$this->assertNull( ap_selected_answer( $id->q ) );
		ap_set_selected_answer( $id->q, $id->a );
		$this->assertEquals( $id->a, ap_selected_answer( $id->q ) );
		ap_unset_selected_answer( $id->q );
		$this->assertEquals( 0, ap_selected_answer( $id->q ) );
		$this->assertNotEquals( $id->a, ap_selected_answer( $id->q ) );

		// Additional tests.
		$q_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
			)
		);
		$this->assertNull( ap_selected_answer( $q_id ) );
		ap_set_selected_answer( $q_id, $a_id );
		$this->assertEquals( $a_id, ap_selected_answer( $q_id ) );
		ap_unset_selected_answer( $q_id );
		$this->assertEquals( 0, ap_selected_answer( $q_id ) );
		$this->assertNotEquals( $a_id, ap_selected_answer( $q_id ) );
	}

	/**
	 * @covers ::ap_is_selected
	 */
	public function testAPIsSelected() {
		// Test for not passing any posts.
		$this->assertFalse( ap_is_selected() );

		// Test for not having the answer as selected.
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_selected( $id->a ) );

		// Test for having the selected answer.
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_selected( $id->a ) );
		ap_set_selected_answer( $id->q, $id->a );
		$this->assertTrue( ap_is_selected( $id->a ) );
		ap_unset_selected_answer( $id->q );
		$this->assertFalse( ap_is_selected( $id->a ) );

		// Additional tests.
		$q_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
			)
		);
		$this->assertFalse( ap_is_selected( $a_id ) );
		ap_set_selected_answer( $q_id, $a_id );
		$this->assertTrue( ap_is_selected( $a_id ) );
		ap_unset_selected_answer( $q_id );
		$this->assertFalse( ap_is_selected( $a_id ) );

		// Test on new answer select.
		$na_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
			)
		);
		$this->assertFalse( ap_is_selected( $na_id ) );
		ap_set_selected_answer( $q_id, $na_id );
		$this->assertTrue( ap_is_selected( $na_id ) );

		// Test on additional new answer select.
		$lna_id = $this->factory()->post->create(
			array(
				'post_title'    => 'Answer title',
				'post_content'  => 'Answer content',
				'post_type'     => 'answer',
				'post_parent'   => $q_id,
			)
		);
		$this->assertTrue( ap_is_selected( $na_id ) );
		$this->assertFalse( ap_is_selected( $lna_id ) );
		ap_set_selected_answer( $q_id, $lna_id );
		$this->assertTrue( ap_is_selected( $lna_id ) );

		// Test after removing the selected answer.
		ap_unset_selected_answer( $q_id );
		$this->assertFalse( ap_is_selected( $lna_id ) );
	}

	/**
	 * @covers ::ap_is_featured_question
	 */
	public function testAPIsFeaturedQuestion() {
		// Test for not passing any question.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertFalse( ap_is_featured_question() );
		$this->go_to( '/' );

		// Test for not a featured question.
		$id = $this->insert_question();
		$this->assertFalse( ap_is_featured_question( $id ) );

		// Test for a featured question.
		$id = $this->insert_question();
		$this->assertFalse( ap_is_featured_question( $id ) );
		ap_set_featured_question( $id );
		$this->assertTrue( ap_is_featured_question( $id ) );

		// Test after removing the featured question.
		$this->assertTrue( ap_is_featured_question( $id ) );
		ap_unset_featured_question( $id );
		$this->assertFalse( ap_is_featured_question( $id ) );
	}

	/**
	 * @covers ::ap_post_have_terms
	 */
	public function testAPPostHaveTerms() {
		// Test for no terms availability without passing anything.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertFalse( ap_post_have_terms() );
		$this->assertFalse( ap_post_have_terms( false ) );
		$this->assertFalse( ap_post_have_terms( false, 'question_category' ) );
		$this->assertFalse( ap_post_have_terms( false, 'question_tag' ) );
		$this->go_to( '/' );

		// Test for no terms availability.
		$id = $this->insert_question();
		$this->assertFalse( ap_post_have_terms( $id ) );
		$this->assertFalse( ap_post_have_terms( $id, 'question_category' ) );
		$this->assertFalse( ap_post_have_terms( $id, 'question_tag' ) );

		// Test for terms availability.
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$tid = $this->factory()->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$id = $this->insert_question();
		$this->assertFalse( ap_post_have_terms( $id ) );
		$this->assertFalse( ap_post_have_terms( $id, 'question_category' ) );
		$this->assertFalse( ap_post_have_terms( $id, 'question_tag' ) );

		// Test after setting the terms.
		// For category.
		wp_set_object_terms( $id, array( $cid ), 'question_category' );
		$this->assertTrue( ap_post_have_terms( $id ) );
		$this->assertTrue( ap_post_have_terms( $id, 'question_category' ) );
		$this->assertFalse( ap_post_have_terms( $id, 'question_tag' ) );

		// For tag.
		wp_set_object_terms( $id, array( $tid ), 'question_tag' );
		$this->assertTrue( ap_post_have_terms( $id ) );
		$this->assertTrue( ap_post_have_terms( $id, 'question_category' ) );
		$this->assertTrue( ap_post_have_terms( $id, 'question_tag' ) );
	}

	/**
	 * @covers ::ap_get_terms
	 */
	public function testAPGetTerms() {
		// Test for no terms availability without passing anything.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertFalse( ap_get_terms() );
		$this->assertFalse( ap_get_terms( false ) );
		$this->go_to( '/' );

		// Test for no terms availability.
		$id = $this->insert_question();
		$this->assertFalse( ap_get_terms( false, $id ) );
		$this->assertEmpty( ap_get_terms( false, $id ) );

		// Test for terms availability.
		// For category.
		$cid = $this->factory()->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$id = $this->insert_question();
		$this->assertFalse( ap_get_terms( false, $id ) );

		// Test after setting the terms.
		wp_set_object_terms( $id, array( $cid ), 'question_category' );
		wp_update_post( [ 'ID' => $id ] );
		$this->assertNotEmpty( ap_get_terms( false, $id ) );
		$this->assertIsString( ap_get_terms( false, $id ) );
		$this->assertStringContainsString( $cid, ap_get_terms( false, $id ) );

		// For tag.
		$tid = $this->factory()->term->create(
			array(
				'name'     => 'Question tag',
				'taxonomy' => 'question_tag',
			)
		);
		$id = $this->insert_question();
		$this->assertFalse( ap_get_terms( false, $id ) );

		// Test after setting the terms.
		wp_set_object_terms( $id, array( $tid ), 'question_tag' );
		wp_update_post( [ 'ID' => $id ] );
		$this->assertNotEmpty( ap_get_terms( false, $id ) );
		$this->assertIsString( ap_get_terms( false, $id ) );
		$this->assertStringContainsString( $tid, ap_get_terms( false, $id ) );
	}

	/**
	 * @covers ::ap_have_attach
	 */
	public function testAPHaveAttach() {
		// Test for no attachment availability without passing anything.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertFalse( ap_have_attach() );
		$this->assertEmpty( ap_have_attach() );
		$this->go_to( '/' );

		// Test for no attachment availability.
		$id = $this->insert_question();
		$this->assertFalse( ap_have_attach( $id ) );
		$this->assertEmpty( ap_have_attach( $id ) );

		// Test for attachment availability.
		$id = $this->insert_question();
		$this->assertFalse( ap_have_attach( $id ) );
		$this->assertEmpty( ap_have_attach( $id ) );

		// Test after adding the attachments.
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $id );
		ap_update_post_attach_ids( $id );
		$this->assertTrue( ap_have_attach( $id ) );
		$this->assertNotEmpty( ap_have_attach( $id ) );
	}

	/**
	 * @covers ::ap_get_attach
	 */
	public function testAPGetAttach() {
		// Test for no attachment availability without passing anything.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertIsArray( ap_get_attach() );
		$this->assertEmpty( ap_get_attach() );
		$this->go_to( '/' );

		// Test for no attachment availability.
		$id = $this->insert_question();
		$this->assertIsArray( ap_get_attach( $id ) );
		$this->assertEmpty( ap_get_attach( $id ) );

		// Test for attachment availability.
		// First test.
		$qid_1 = $this->insert_question();
		$this->assertIsArray( ap_get_attach( $qid_1 ) );
		$this->assertEmpty( ap_get_attach( $qid_1 ) );

		// Test after adding the attachments.
		$attachment_id_1 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $qid_1 );
		ap_update_post_attach_ids( $qid_1 );
		$this->assertIsArray( ap_get_attach( $qid_1 ) );
		$this->assertNotEmpty( ap_get_attach( $qid_1 ) );
		$this->assertEquals( [ $attachment_id_1 ], ap_get_attach( $qid_1 ) );

		// Second test.
		$qid_2 = $this->insert_question();
		$this->assertIsArray( ap_get_attach( $qid_2 ) );
		$this->assertEmpty( ap_get_attach( $qid_2 ) );

		// Test after adding the attachments.
		$attachment_id_2 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $qid_2 );
		ap_update_post_attach_ids( $qid_2 );
		$this->assertIsArray( ap_get_attach( $qid_2 ) );
		$this->assertNotEmpty( ap_get_attach( $qid_2 ) );
		$this->assertEquals( [ $attachment_id_2 ], ap_get_attach( $qid_2 ) );

		// Test for array values on adding the attachments.
		$q_id    = $this->insert_question();
		$pdf_id  = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $q_id );
		$png1_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $q_id );
		$png2_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $q_id );
		ap_update_post_attach_ids( $q_id );
		$this->assertEquals( [ $pdf_id, $png1_id, $png2_id ], ap_get_attach( $q_id ) );
	}

	/**
	 * @covers ::ap_have_questions
	 */
	public function testAPHaveQuestions() {
		// Test for not having any question.
		anspress()->questions = new \Question_Query( [] );
		$this->assertFalse( ap_have_questions() );

		// Test for having a single question.
		$id = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'p' => $id ] );
		$this->assertTrue( ap_have_questions() );

		// Test for having multiple questions.
		wp_delete_post( $id, true );
		$q_id1 = $this->insert_question();
		$q_id2 = $this->insert_question();
		$q_id3 = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'post__in' => [ $q_id1, $q_id2, $q_id3 ] ] );
		$this->assertTrue( ap_have_questions() );

		// Re-testing for not having any question.
		wp_delete_post( $q_id1, true );
		wp_delete_post( $q_id2, true );
		wp_delete_post( $q_id3, true );
		anspress()->questions = new \Question_Query();
		$this->assertFalse( ap_have_questions() );

		// Re-testing for not having any question with available question.
		$q_id1 = $this->insert_question();
		$q_id2 = $this->insert_question();
		$q_id3 = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'post__not_in' => [ $q_id1, $q_id2, $q_id3 ] ] );
		$this->assertFalse( ap_have_questions() );
	}

	/**
	 * @covers ::ap_total_questions_found
	 */
	public function testAPTotalQuestionsFound() {
		// Test for not having any question.
		anspress()->questions = new \Question_Query();
		$this->assertEquals( 0, ap_total_questions_found() );

		// Test for having a single question.
		$id = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'p' => $id ] );
		$this->assertEquals( 1, ap_total_questions_found() );

		// Test for having multiple questions.
		$q_id1 = $this->insert_question();
		$q_id2 = $this->insert_question();
		$q_id3 = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'post__in' => [ $q_id1, $q_id2, $q_id3 ] ] );
		$this->assertEquals( 3, ap_total_questions_found() );

		// Test for having all questions.
		anspress()->questions = new \Question_Query();
		$this->assertEquals( 4, ap_total_questions_found() );

		// Test for removing certain questions from query.
		anspress()->questions = new \Question_Query( [ 'post__in' => [ $q_id1, $q_id2 ] ] );
		$this->assertEquals( 2, ap_total_questions_found() );
		$id = $this->insert_question();
		anspress()->questions = new \Question_Query( [ 'post__not_in' => [ $q_id1, $q_id2 ] ] );
		$this->assertEquals( 3, ap_total_questions_found() );

		// Re-test for having all questions.
		anspress()->questions = new \Question_Query();
		$this->assertEquals( 5, ap_total_questions_found() );
	}

	/**
	 * @covers ::ap_get_post
	 */
	public function testAPGetPost() {
		// Test for passing nothing.
		$this->assertFalse( ap_get_post() );

		// Test for passing nothing but visiting the question page.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$_post = ap_get_post();
		$this->assertIsObject( $_post );
		$this->assertObjectHasProperty( 'post_id', $_post );
		$this->assertObjectHasProperty( 'selected', $_post );
		$this->assertObjectHasProperty( 'selected_id', $_post );
		$this->assertObjectHasProperty( 'comments', $_post );
		$this->assertObjectHasProperty( 'answers', $_post );
		$this->assertObjectHasProperty( 'ptype', $_post );
		$this->assertObjectHasProperty( 'featured', $_post );
		$this->assertObjectHasProperty( 'closed', $_post );
		$this->assertObjectHasProperty( 'views', $_post );
		$this->assertObjectHasProperty( 'votes_up', $_post );
		$this->assertObjectHasProperty( 'votes_down', $_post );
		$this->assertObjectHasProperty( 'subscribers', $_post );
		$this->assertObjectHasProperty( 'flags', $_post );
		$this->assertObjectHasProperty( 'terms', $_post );
		$this->assertObjectHasProperty( 'attach', $_post );
		$this->assertObjectHasProperty( 'activities', $_post );
		$this->assertObjectHasProperty( 'fields', $_post );
		$this->assertObjectHasProperty( 'roles', $_post );
		$this->assertObjectHasProperty( 'last_updated', $_post );
		$this->assertObjectHasProperty( 'is_new', $_post );
		$this->go_to( '/' );

		// Test for passing the question id.
		$id = $this->insert_question();
		$_post = ap_get_post( $id );
		$this->assertIsObject( $_post );

		// Test for appended qameta datas for question.
		$q_id = $this->insert_question();
		$question_obj = ap_get_post( $q_id );
		$this->assertIsObject( $question_obj );
		$this->assertObjectHasProperty( 'post_id', $question_obj );
		$this->assertObjectHasProperty( 'selected', $question_obj );
		$this->assertObjectHasProperty( 'selected_id', $question_obj );
		$this->assertObjectHasProperty( 'comments', $question_obj );
		$this->assertObjectHasProperty( 'answers', $question_obj );
		$this->assertObjectHasProperty( 'ptype', $question_obj );
		$this->assertObjectHasProperty( 'featured', $question_obj );
		$this->assertObjectHasProperty( 'closed', $question_obj );
		$this->assertObjectHasProperty( 'views', $question_obj );
		$this->assertObjectHasProperty( 'votes_up', $question_obj );
		$this->assertObjectHasProperty( 'votes_down', $question_obj );
		$this->assertObjectHasProperty( 'subscribers', $question_obj );
		$this->assertObjectHasProperty( 'flags', $question_obj );
		$this->assertObjectHasProperty( 'terms', $question_obj );
		$this->assertObjectHasProperty( 'attach', $question_obj );
		$this->assertObjectHasProperty( 'activities', $question_obj );
		$this->assertObjectHasProperty( 'fields', $question_obj );
		$this->assertObjectHasProperty( 'roles', $question_obj );
		$this->assertObjectHasProperty( 'last_updated', $question_obj );
		$this->assertObjectHasProperty( 'is_new', $question_obj );

		// Test for appended qameta datas for answer.
		$qa_id = $this->insert_answer();
		$answer_obj = ap_get_post( $qa_id->a );
		$this->assertIsObject( $answer_obj );
		$this->assertObjectHasProperty( 'post_id', $answer_obj );
		$this->assertObjectHasProperty( 'selected', $answer_obj );
		$this->assertObjectHasProperty( 'selected_id', $answer_obj );
		$this->assertObjectHasProperty( 'comments', $answer_obj );
		$this->assertObjectHasProperty( 'answers', $answer_obj );
		$this->assertObjectHasProperty( 'ptype', $answer_obj );
		$this->assertObjectHasProperty( 'featured', $answer_obj );
		$this->assertObjectHasProperty( 'closed', $answer_obj );
		$this->assertObjectHasProperty( 'views', $answer_obj );
		$this->assertObjectHasProperty( 'votes_up', $answer_obj );
		$this->assertObjectHasProperty( 'votes_down', $answer_obj );
		$this->assertObjectHasProperty( 'subscribers', $answer_obj );
		$this->assertObjectHasProperty( 'flags', $answer_obj );
		$this->assertObjectHasProperty( 'terms', $answer_obj );
		$this->assertObjectHasProperty( 'attach', $answer_obj );
		$this->assertObjectHasProperty( 'activities', $answer_obj );
		$this->assertObjectHasProperty( 'fields', $answer_obj );
		$this->assertObjectHasProperty( 'roles', $answer_obj );
		$this->assertObjectHasProperty( 'last_updated', $answer_obj );
		$this->assertObjectHasProperty( 'is_new', $answer_obj );
	}

	/**
	 * @covers ::ap_get_time
	 */
	public function testAPGetTime() {
		// Test for not passing post id.
		$this->assertEmpty( ap_get_time() );
		$this->assertEmpty( ap_get_time( null, 'U' ) );
		$this->assertEmpty( ap_get_time( null, 'Y-m-d' ) );

		// Test for passing the post object.
		$id = $this->insert_question();
		$this->assertIsInt( ap_get_time( get_post( $id ), 'U' ) );
		$this->assertIsInt( ap_get_time( ap_get_post( $id ), 'U' ) );
		$this->assertNotEmpty( ap_get_time( get_post( $id ), 'U' ) );
		$this->assertNotEmpty( ap_get_time( ap_get_post( $id ), 'U' ) );

		// Test for not passing post id but visiting the question page.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertIsInt( ap_get_time( null, 'U' ) );
		$this->assertNotEmpty( ap_get_time( null, 'U' ) );

		// Test for passing post id but not the date format.
		$id = $this->insert_question();
		$this->assertEmpty( ap_get_time( $id ) );

		// Test for passing post id and the date format.
		$id = $this->insert_question();
		$this->assertIsInt( ap_get_time( $id, 'U' ) );
		$this->assertNotEmpty( ap_get_time( $id, 'U' ) );

		// Test for passing invalid post id.
		$this->assertEmpty( ap_get_time( -1, 'U' ) );
	}

	public function APDisplayQuestionMetas( $metas, $question_id ) {
		$metas['test'] = 'Test meta';
		return $metas;
	}

	/**
	 * @covers ::ap_question_metas
	 */
	public function testAPQuestionMetas() {
		$id = $this->insert_question();

		// Test 1.
		$this->go_to( '?post_type=question&p=' . $id );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringNotContainsString( 'Featured', $result );
		$this->assertStringNotContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );

		// Test 2.
		ap_set_featured_question( $id );
		$this->go_to( '?post_type=question&p=' . $id );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringContainsString( 'Featured', $result );
		$this->assertStringNotContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );

		// Test 3.
		$id = $this->insert_answer();
		ap_set_selected_answer( $id->q, $id->a );
		$this->go_to( '?post_type=question&p=' . $id->q );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringNotContainsString( 'Featured', $result );
		$this->assertStringContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );

		// Test 4.
		ap_set_featured_question( $id->q );
		$this->go_to( '?post_type=question&p=' . $id->q );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringContainsString( 'Featured', $result );
		$this->assertStringContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );

		// Test 5.
		$this->go_to( '?post_type=answer&p=' . $id->a );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringNotContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringNotContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringNotContainsString( 'Featured', $result );
		$this->assertStringNotContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item history'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><span class="ap-post-history">', $result );

		// Test 6.
		add_filter( 'ap_display_question_metas', [ $this, 'APDisplayQuestionMetas' ], 10, 2 );
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringNotContainsString( 'Featured', $result );
		$this->assertStringNotContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );
		$this->assertStringNotContainsString( "<span class='ap-display-meta-item history'>", $result );
		$this->assertStringNotContainsString( '<i class="apicon-pulse"></i><span class="ap-post-history">', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item test'>", $result );
		$this->assertStringContainsString( 'Test meta', $result );
		remove_filter( 'ap_display_question_metas', [ $this, 'APDisplayQuestionMetas' ], 10, 2 );

		// Test 7.
		add_filter( 'ap_display_question_metas', [ $this, 'APDisplayQuestionMetas' ], 10, 2 );
		$id = $this->insert_answer();
		ap_set_selected_answer( $id->q, $id->a );
		ap_set_featured_question( $id->q );
		$this->go_to( '?post_type=answer&p=' . $id->a );
		ob_start();
		ap_question_metas();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item views'>", $result );
		$this->assertStringContainsString( '<i class="apicon-eye"></i><i>0 views</i>', $result );
		$this->assertStringNotContainsString( "<span class='ap-display-meta-item active'>", $result );
		$this->assertStringNotContainsString( '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime', $result );
		$this->assertStringNotContainsString( 'Featured', $result );
		$this->assertStringNotContainsString( '<i class="apicon-check"></i><i>Solved</i>', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item history'>", $result );
		$this->assertStringContainsString( '<i class="apicon-pulse"></i><span class="ap-post-history">', $result );
		$this->assertStringContainsString( "<span class='ap-display-meta-item test'>", $result );
		$this->assertStringContainsString( 'Test meta', $result );
		remove_filter( 'ap_display_question_metas', [ $this, 'APDisplayQuestionMetas' ], 10, 2 );
	}

	/**
	 * @covers ::ap_answers
	 */
	public function testAPAnswers() {
		// Test 1.
		$ids = $this->insert_answers( [], [], 5 );
		$this->go_to( '?post_type=question&p=' . $ids['question'] );
		ob_start();
		ap_answers();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<div id="ap-answers-c">', $result );
		$this->assertStringContainsString( '<div class="ap-sorting-tab clearfix">', $result );
		$this->assertStringContainsString( '<h3 class="ap-answers-label ap-pull-left" ap="answers_count_t">', $result );
		$this->assertStringContainsString( '<span itemprop="answerCount">5</span>', $result );
		$this->assertStringContainsString( 'Answers', $result );
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<div id="answers">', $result );
		$this->assertStringContainsString( '<apanswers>', $result );

		// Test 2.
		$ids = $this->insert_answer();
		$this->go_to( '?post_type=question&p=' . $ids->q );
		ob_start();
		ap_answers();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<div id="ap-answers-c">', $result );
		$this->assertStringContainsString( '<div class="ap-sorting-tab clearfix">', $result );
		$this->assertStringContainsString( '<h3 class="ap-answers-label ap-pull-left" ap="answers_count_t">', $result );
		$this->assertStringContainsString( '<span itemprop="answerCount">1</span>', $result );
		$this->assertStringContainsString( 'Answer', $result );
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<div id="answers">', $result );
		$this->assertStringContainsString( '<apanswers>', $result );

		// Test 3.
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		ob_start();
		ap_answers();
		$result = ob_get_clean();
		$this->assertNotEmpty( $result );
		$this->assertStringContainsString( '<div id="ap-answers-c">', $result );
		$this->assertStringContainsString( '<div class="ap-sorting-tab clearfix">', $result );
		$this->assertStringContainsString( '<h3 class="ap-answers-label ap-pull-left" ap="answers_count_t">', $result );
		$this->assertStringContainsString( '<span itemprop="answerCount">0</span>', $result );
		$this->assertStringContainsString( 'Answers', $result );
		$this->assertStringContainsString( '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">', $result );
		$this->assertStringContainsString( '<div id="answers">', $result );
		$this->assertStringContainsString( '<apanswers>', $result );
	}

	/**
	 * @covers ::ap_get_last_active
	 */
	public function testAPGetLastActiveWithInvalidPostID() {
		$result = ap_get_last_active( 0 );
		$this->assertEquals( 'Invalid post', $result );
	}
}
