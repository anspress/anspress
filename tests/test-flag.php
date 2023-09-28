<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFlag extends TestCase {

	use Testcases\Common;

	/**
	 * @covers ::ap_add_flag
	 */
	public function testAPAddFlag() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_add_flag( $id ) );

		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertTrue( ap_add_flag( $id, $user_id ) );
	}

	/**
	 * @covers ::ap_is_user_flagged
	 */
	public function testAPIsUserFlagged() {
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );

		$this->setRole( 'subscriber' );
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertTrue( ap_is_user_flagged( $id->a ) );
		ap_delete_flags( $id->q );
		ap_delete_flags( $id->a );
		$this->assertFalse( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
	}

	/**
	 * @covers ::ap_delete_flags
	 */
	public function testAPDeleteFlags() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		ap_add_flag( $id );
		ap_update_flags_count( $id );
		$this->assertTrue( ap_delete_flags( $id ) );
	}

	/**
	 * @covers ::ap_total_flagged_count
	 */
	public function testAPTotalFlaggedCount() {
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];

		// Test for questions.
		$this->assertEquals( 0, $total_flagged_questions->publish );
		$this->assertEquals( 0, $total_flagged_questions->future );
		$this->assertEquals( 0, $total_flagged_questions->draft );
		$this->assertEquals( 0, $total_flagged_questions->pending );
		$this->assertEquals( 0, $total_flagged_questions->private );
		$this->assertEquals( 0, $total_flagged_questions->trash );
		$this->assertEquals( 0, $total_flagged_questions->{'auto-draft'} );
		$this->assertEquals( 0, $total_flagged_questions->inherit );
		$this->assertEquals( 0, $total_flagged_questions->{'request-pending'} );
		$this->assertEquals( 0, $total_flagged_questions->{'request-confirmed'} );
		$this->assertEquals( 0, $total_flagged_questions->{'request-failed'} );
		$this->assertEquals( 0, $total_flagged_questions->{'request-completed'} );
		$this->assertEquals( 0, $total_flagged_questions->total );
		$this->assertEquals( 0, $total_flagged_questions->moderate );

		// Test for answers.
		$this->assertEquals( 0, $total_flagged_answers->publish );
		$this->assertEquals( 0, $total_flagged_answers->future );
		$this->assertEquals( 0, $total_flagged_answers->draft );
		$this->assertEquals( 0, $total_flagged_answers->pending );
		$this->assertEquals( 0, $total_flagged_answers->private );
		$this->assertEquals( 0, $total_flagged_answers->trash );
		$this->assertEquals( 0, $total_flagged_answers->{'auto-draft'} );
		$this->assertEquals( 0, $total_flagged_answers->inherit );
		$this->assertEquals( 0, $total_flagged_answers->{'request-pending'} );
		$this->assertEquals( 0, $total_flagged_answers->{'request-confirmed'} );
		$this->assertEquals( 0, $total_flagged_answers->{'request-failed'} );
		$this->assertEquals( 0, $total_flagged_answers->{'request-completed'} );
		$this->assertEquals( 0, $total_flagged_answers->total );

		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];
		// Test for questions.
		$this->assertEquals( 1, $total_flagged_questions->publish );
		$this->assertEquals( 1, $total_flagged_questions->total );
		// Test for answers.
		$this->assertEquals( 1, $total_flagged_answers->publish );
		$this->assertEquals( 1, $total_flagged_answers->total );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
			)
		);
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'moderate',
			)
		);
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];
		// Test for questions.
		$this->assertEquals( 1, $total_flagged_questions->publish );
		$this->assertEquals( 1, $total_flagged_questions->moderate );
		$this->assertEquals( 2, $total_flagged_questions->total );
		// Test for answers.
		$this->assertEquals( 1, $total_flagged_answers->publish );
		$this->assertEquals( 1, $total_flagged_answers->moderate );
		$this->assertEquals( 2, $total_flagged_answers->total );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
				'post_status'  => 'private',
			)
		);
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
				'post_status'  => 'private',
			)
		);
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];
		// Test for questions.
		$this->assertEquals( 1, $total_flagged_questions->publish );
		$this->assertEquals( 1, $total_flagged_questions->moderate );
		$this->assertEquals( 1, $total_flagged_questions->private );
		$this->assertEquals( 3, $total_flagged_questions->total );
		// Test for answers.
		$this->assertEquals( 1, $total_flagged_answers->publish );
		$this->assertEquals( 1, $total_flagged_answers->moderate );
		$this->assertEquals( 1, $total_flagged_answers->private );
		$this->assertEquals( 3, $total_flagged_answers->total );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question title',
				'post_content' => 'Question content',
				'post_type'    => 'question',
			)
		);
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer title',
				'post_content' => 'Answer content',
				'post_type'    => 'answer',
				'post_parent'  => $question_id,
			)
		);
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		ap_add_flag( $answer_id );
		ap_update_flags_count( $answer_id );
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];
		// Test for questions.
		$this->assertEquals( 2, $total_flagged_questions->publish );
		$this->assertEquals( 1, $total_flagged_questions->moderate );
		$this->assertEquals( 1, $total_flagged_questions->private );
		$this->assertEquals( 4, $total_flagged_questions->total );
		// Test for answers.
		$this->assertEquals( 2, $total_flagged_answers->publish );
		$this->assertEquals( 1, $total_flagged_answers->moderate );
		$this->assertEquals( 1, $total_flagged_answers->private );
		$this->assertEquals( 4, $total_flagged_answers->total );

		// Test after deleting the flags.
		ap_delete_flags( $question_id );
		ap_delete_flags( $answer_id );
		$total_flagged_count     = ap_total_flagged_count();
		$total_flagged_questions = $total_flagged_count['questions'];
		$total_flagged_answers   = $total_flagged_count['answers'];
		// Test for questions.
		$this->assertEquals( 1, $total_flagged_questions->publish );
		$this->assertEquals( 1, $total_flagged_questions->moderate );
		$this->assertEquals( 1, $total_flagged_questions->private );
		$this->assertEquals( 3, $total_flagged_questions->total );
		// Test for answers.
		$this->assertEquals( 1, $total_flagged_answers->publish );
		$this->assertEquals( 1, $total_flagged_answers->moderate );
		$this->assertEquals( 1, $total_flagged_answers->private );
		$this->assertEquals( 3, $total_flagged_answers->total );
	}

	/**
	 * @covers ::ap_count_post_flags
	 */
	public function testAOCountPostFlags() {
		$id = $this->insert_answer();
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $question_count_flag );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 0, $answer_count_flag );

		// Test after adding a flag.
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 1, $question_count_flag );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 1, $answer_count_flag );
		ap_add_flag( $id->q );
		ap_update_flags_count( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 2, $question_count_flag );
		ap_add_flag( $id->a );
		ap_update_flags_count( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->a );
		$this->assertEquals( 2, $answer_count_flag );

		// Test after deleting flags.
		ap_delete_flags( $id->q );
		$question_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $question_count_flag );
		ap_delete_flags( $id->a );
		$answer_count_flag = ap_count_post_flags( $id->q );
		$this->assertEquals( 0, $answer_count_flag );
	}

}
