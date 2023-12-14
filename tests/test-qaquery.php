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

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Question_Query' );
		$this->assertTrue( $class->hasProperty( 'count_request' ) && $class->getProperty( 'count_request' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Question_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'get_questions' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'next_question' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'reset_next' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'the_question' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'have_questions' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'rewind_questions' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'is_main_query' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'reset_questions_data' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'get_ids' ) );
		$this->assertTrue( method_exists( 'Question_Query', 'pre_fetch' ) );
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
		$q1_id = $this->factory->post->create(
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
		$q2_id = $this->factory->post->create(
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
		$q3_id = $this->factory->post->create(
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
		$q4_id = $this->factory->post->create(
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
		$q5_id = $this->factory->post->create(
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
		$a1_id = $this->factory->post->create(
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
		$a2_id = $this->factory->post->create(
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
		$a1_id = $this->factory->post->create(
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
		$a2_id = $this->factory->post->create(
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
		$a3_id = $this->factory->post->create(
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
		$a4_id = $this->factory->post->create(
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
		$a1_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $id->q,
			)
		);
		$a2_id = $this->factory->post->create(
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
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

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
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$new_user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$latest_user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

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
		$q_id = $this->factory->post->create(
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
		$a_id = $this->factory->post->create(
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
		$q_id = $this->factory->post->create(
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
		$a_id = $this->factory->post->create(
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
		$q_id = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory->post->create(
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
		$q_id = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory->post->create(
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
		$q_id = $this->factory->post->create(
			array(
				'post_title'    => 'Question title',
				'post_content'  => 'Question content',
				'post_type'     => 'question',
			)
		);
		$a_id = $this->factory->post->create(
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
		$na_id = $this->factory->post->create(
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
		$lna_id = $this->factory->post->create(
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
		$cid = $this->factory->term->create(
			array(
				'name'     => 'Question category',
				'taxonomy' => 'question_category',
			)
		);
		$tid = $this->factory->term->create(
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
		$cid = $this->factory->term->create(
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
		$tid = $this->factory->term->create(
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
		$attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $id );
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
		$attachment_id_1 = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $qid_1 );
		ap_update_post_attach_ids( $qid_1 );
		$this->assertIsArray( ap_get_attach( $qid_1 ) );
		$this->assertNotEmpty( ap_get_attach( $qid_1 ) );
		$this->assertEquals( [ $attachment_id_1 ], ap_get_attach( $qid_1 ) );

		// Second test.
		$qid_2 = $this->insert_question();
		$this->assertIsArray( ap_get_attach( $qid_2 ) );
		$this->assertEmpty( ap_get_attach( $qid_2 ) );

		// Test after adding the attachments.
		$attachment_id_2 = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $qid_2 );
		ap_update_post_attach_ids( $qid_2 );
		$this->assertIsArray( ap_get_attach( $qid_2 ) );
		$this->assertNotEmpty( ap_get_attach( $qid_2 ) );
		$this->assertEquals( [ $attachment_id_2 ], ap_get_attach( $qid_2 ) );

		// Test for array values on adding the attachments.
		$q_id    = $this->insert_question();
		$pdf_id  = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $q_id );
		$png1_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $q_id );
		$png2_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $q_id );
		ap_update_post_attach_ids( $q_id );
		$this->assertEquals( [ $pdf_id, $png1_id, $png2_id ], ap_get_attach( $q_id ) );
	}
}
