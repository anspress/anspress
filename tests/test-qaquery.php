<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAQuery extends TestCase {

	use Testcases\Common;

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
}
