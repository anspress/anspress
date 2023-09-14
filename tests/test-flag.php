<?php

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestFlag extends TestCase {

	use AnsPress\Tests\Testcases\Common;

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
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertFalse( ap_is_user_flagged( $id->a ) );
		ap_add_flag( $id->a );
		$this->assertTrue( ap_is_user_flagged( $id->q ) );
		$this->assertTrue( ap_is_user_flagged( $id->a ) );
	}

	/**
	 * @covers ::ap_delete_flags
	 */
	public function testAPDeleteFlags() {
		$id = $this->insert_question();
		$this->setRole( 'subscriber' );
		$this->assertTrue( ap_add_flag( $id ) );
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
	}

}
