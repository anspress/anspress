<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnswersQuery extends TestCase {

	use Testcases\Common;

	public function testClassProperties() {
		$class = new \ReflectionClass( 'Answers_Query' );
		$this->assertTrue( $class->hasProperty( 'args' ) && $class->getProperty( 'args' )->isPublic() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'Answers_Query', '__construct' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'next_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_next' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'the_answer' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'have_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'rewind_answers' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'is_main_query' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'reset_answers_data' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'get_ids' ) );
		$this->assertTrue( method_exists( 'Answers_Query', 'pre_fetch' ) );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructor() {
		$answers_query = new \Answers_Query();
		$this->assertInstanceOf( 'Answers_Query', $answers_query );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructorWithoutArgs() {
		$question_id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $question_id );

		$answers_query = new \Answers_Query();
		$this->assertInstanceOf( 'Answers_Query', $answers_query );

		// Tests.
		$this->assertEquals( $question_id, $answers_query->args['question_id'] );
		$this->assertEquals( true, $answers_query->args['ap_query'] );
		$this->assertEquals( false, $answers_query->args['ap_current_user_ignore'] );
		$this->assertEquals( true, $answers_query->args['ap_answers_query'] );
		$this->assertEquals( 5, $answers_query->args['showposts'] );
		$this->assertEquals( 1, $answers_query->args['paged'] );
		$this->assertEquals( false, $answers_query->args['only_best_answer'] );
		$this->assertEquals( false, $answers_query->args['ignore_selected_answer'] );
		$this->assertEquals( [ 'publish' ], $answers_query->args['post_status'] );
		$this->assertEquals( 'active', $answers_query->args['ap_order_by'] );
		$this->assertEquals( $question_id, $answers_query->args['post_parent'] );
		$this->assertEquals( 'answer', $answers_query->args['post_type'] );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructorWithoutArgsForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $question_id );

		$answers_query = new \Answers_Query();
		$this->assertInstanceOf( 'Answers_Query', $answers_query );

		// Tests.
		$this->assertEquals( $question_id, $answers_query->args['question_id'] );
		$this->assertEquals( true, $answers_query->args['ap_query'] );
		$this->assertEquals( false, $answers_query->args['ap_current_user_ignore'] );
		$this->assertEquals( true, $answers_query->args['ap_answers_query'] );
		$this->assertEquals( 5, $answers_query->args['showposts'] );
		$this->assertEquals( 1, $answers_query->args['paged'] );
		$this->assertEquals( false, $answers_query->args['only_best_answer'] );
		$this->assertEquals( false, $answers_query->args['ignore_selected_answer'] );
		$this->assertEquals( [ 'publish', 'private_post', 'moderate', 'trash' ], $answers_query->args['post_status'] );
		$this->assertEquals( 'active', $answers_query->args['ap_order_by'] );
		$this->assertEquals( $question_id, $answers_query->args['post_parent'] );
		$this->assertEquals( 'answer', $answers_query->args['post_type'] );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructorWithoutArgsForQueryVars() {
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$this->go_to( '?post_type=question&p=' . $question_id );
		set_query_var( 'ap_paged', 7 );
		set_query_var( 'answer_id', $answer_id );

		$answers_query = new \Answers_Query();
		$this->assertInstanceOf( 'Answers_Query', $answers_query );

		// Tests.
		$this->assertEquals( $question_id, $answers_query->args['question_id'] );
		$this->assertEquals( true, $answers_query->args['ap_query'] );
		$this->assertEquals( false, $answers_query->args['ap_current_user_ignore'] );
		$this->assertEquals( true, $answers_query->args['ap_answers_query'] );
		$this->assertEquals( 5, $answers_query->args['showposts'] );
		$this->assertEquals( 7, $answers_query->args['paged'] );
		$this->assertEquals( false, $answers_query->args['only_best_answer'] );
		$this->assertEquals( false, $answers_query->args['ignore_selected_answer'] );
		$this->assertEquals( [ 'publish' ], $answers_query->args['post_status'] );
		$this->assertEquals( 'active', $answers_query->args['ap_order_by'] );
		$this->assertEquals( $answer_id, $answers_query->args['p'] );
		$this->assertEquals( $question_id, $answers_query->args['post_parent'] );
		$this->assertEquals( 'answer', $answers_query->args['post_type'] );
		$this->assertEquals( $answer_id, $answers_query->args['p'] );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructorWithArgsForReturningEmptyArg() {
		$answers_query = new \Answers_Query( [ 'question_id' => 0 ] );
		$this->assertInstanceOf( 'Answers_Query', $answers_query );

		// Tests.
		$this->assertEmpty( $answers_query->args );
	}

	/**
	 * @covers Answers_Query::__construct
	 */
	public function testConstructorWithArgs() {
		$question_id = $this->insert_question();
		$answer_id = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [
			'question_id'            => $question_id,
			'showposts'              => 15,
			'paged'                  => 3,
			'ap_current_user_ignore' => true,
			'ignore_selected_answer' => true,
			'p'                      => $answer_id,
			'order'                  => 'ASC',
			'author'                 => get_current_user_id(),
		] );
		$this->assertInstanceOf( 'Answers_Query', $answers_query );

		// Tests.
		$this->assertEquals( $question_id, $answers_query->args['question_id'] );
		$this->assertEquals( true, $answers_query->args['ap_query'] );
		$this->assertEquals( true, $answers_query->args['ap_current_user_ignore'] );
		$this->assertEquals( true, $answers_query->args['ap_answers_query'] );
		$this->assertEquals( 15, $answers_query->args['showposts'] );
		$this->assertEquals( 3, $answers_query->args['paged'] );
		$this->assertEquals( false, $answers_query->args['only_best_answer'] );
		$this->assertEquals( true, $answers_query->args['ignore_selected_answer'] );
		$this->assertEquals( [ 'publish' ], $answers_query->args['post_status'] );
		$this->assertEquals( 'active', $answers_query->args['ap_order_by'] );
		$this->assertEquals( $question_id, $answers_query->args['post_parent'] );
		$this->assertEquals( 'answer', $answers_query->args['post_type'] );
		$this->assertEquals( $answer_id, $answers_query->args['p'] );
		$this->assertEquals( 'ASC', $answers_query->args['order'] );
		$this->assertEquals( get_current_user_id(), $answers_query->args['author'] );
	}

	/**
	 * @covers Answers_Query::get_answers
	 */
	public function testGetAnswersReturnsWPPostObjects() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();
		$this->assertContainsOnlyInstancesOf( 'WP_Post', $answers );
	}

	/**
	 * @covers Answers_Query::get_answers
	 */
	public function testGetAnswersReturnsIntegerArrays() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id, 'fields' => 'ids' ] );
		$answers = $answers_query->get_answers();
		$this->assertContainsOnly( 'integer', $answers );
	}

	/**
	 * @covers Answers_Query::get_answers
	 */
	public function testGetAnswersReturnsEmptyArray() {
		$question_id = $this->insert_question();
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();
		$this->assertEmpty( $answers );
	}

	/**
	 * @covers Answers_Query::next_answer
	 */
	public function testNextAnswer() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();
		foreach ( $answers as $answer ) {
			$result = $answers_query->next_answer();
			$this->assertInstanceOf( 'WP_Post', $result );
			$this->assertSame( $answer, $result );
		}
	}

	/**
	 * @covers Answers_Query::reset_next
	 */
	public function testResetNext() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();
		$this->assertEquals( $answers[0], $answers_query->next_answer() );
		$this->assertEquals( $answers[1], $answers_query->next_answer() );
		$this->assertEquals( $answers[2], $answers_query->next_answer() );

		// Test reset_next.
		$result1 = $answers_query->reset_next();
		$this->assertInstanceOf( 'WP_Post', $result1 );
		$this->assertEquals( $answers[1], $result1 );
		$result2 = $answers_query->reset_next();
		$this->assertInstanceOf( 'WP_Post', $result2 );
		$this->assertEquals( $answers[0], $result2 );
	}

	/**
	 * @covers Answers_Query::have_answers
	 */
	public function testHaveAnswers() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$this->assertTrue( $answers_query->have_answers() );
	}

	/**
	 * @covers Answers_Query::have_answers
	 */
	public function testHaveAnswersReturnsFalse() {
		$question_id = $this->insert_question();
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$this->assertFalse( $answers_query->have_answers() );
	}

	/**
	 * @covers Answers_Query::rewind_answers
	 */
	public function testRewindAnswers() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();
		$this->assertEquals( $answers[0], $answers_query->next_answer() );
		$this->assertEquals( $answers[1], $answers_query->next_answer() );
		$this->assertEquals( $answers[2], $answers_query->next_answer() );

		// Test rewind_answers.
		$answers_query->rewind_answers();
		$this->assertEquals( $answers[0], $answers_query->next_answer() );
	}

	/**
	 * @covers Answers_Query::the_answer
	 */
	public function testTheAnswer() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();

		// Test.
		foreach ( $answers as $answer ) {
			$answers_query->the_answer();

			global $post;
			$this->assertSame( $answer, $post );
			$this->assertSame( $answer, anspress()->current_answer );
		}
	}

	/**
	 * @covers Answers_Query::the_answer
	 */
	public function testTheAnswerForActionHookTriggered() {
		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_query_loop_start', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers = $answers_query->get_answers();

		// Test.
		foreach ( $answers as $answer ) {
			$answers_query->the_answer();

			global $post;
			$this->assertSame( $answer, $post );
			$this->assertSame( $answer, anspress()->current_answer );
		}
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_query_loop_start' ) > 0 );
	}

	/**
	 * @covers Answers_Query::is_main_query
	 */
	public function testIsMainQuery() {
		$question_id = $this->insert_question();
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		anspress()->answers = $answers_query;
		$this->assertTrue( $answers_query->is_main_query() );
	}

	/**
	 * @covers Answers_Query::is_main_query
	 */
	public function testIsMainQueryReturnsFalse() {
		$question_id = $this->insert_question();
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		anspress()->answers = '';
		$this->assertFalse( $answers_query->is_main_query() );
	}

	/**
	 * @covers Answers_Query::reset_answers_data
	 */
	public function testResetAnswersData() {
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers_query->reset_answers_data();
		$this->assertEquals( $answers_query->post, anspress()->current_answer );
	}

	/**
	 * @covers Answers_Query::get_ids
	 */
	public function testGetIds() {
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		wp_update_post( [ 'ID' => $answer_ids[0], 'post_date' => '2024-01-03 00:00:00' ] );
		wp_update_post( [ 'ID' => $answer_ids[1], 'post_date' => '2024-01-02 00:00:00' ] );
		wp_update_post( [ 'ID' => $answer_ids[2], 'post_date' => '2024-01-01 00:00:00' ] );
		$attachment_id_1 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $answer_ids[0] );
		$attachment_id_2 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $answer_ids[1] );
		ap_update_post_attach_ids( $answer_ids[0] );
		ap_update_post_attach_ids( $answer_ids[1] );
		$new_answer = $this->factory()->post->create( [ 'post_type' => 'answer', 'post_parent' => $question_id, 'post_author' => $user_id ] );
		$new_user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$comment_id = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $answer_ids[0],
				'user_id'         => $new_user_id,
			)
		);
		ap_update_post_activity_meta( $new_answer, 'new_c', $new_user_id );

		// Test.
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id, 'ap_order_by' => 'newest' ] );
		$expected = [
			'post_ids'   => array_merge( [ $new_answer ], $answer_ids ),
			'attach_ids' => [ 1 => $attachment_id_2, 2 => $attachment_id_1 ],
			'user_ids'   => [ $user_id, $new_user_id, get_current_user_id() ],
		];
		$this->assertEquals( $expected, $answers_query->ap_ids );
	}

	/**
	 * @covers Answers_Query::get_ids
	 */
	public function testGetIdsForCallingTheMethodAgain() {
		$question_id = $this->insert_question();
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$ap_ids = $answers_query->ap_ids;
		$this->assertNotEmpty( $ap_ids );
		$this->assertNull( $answers_query->get_ids() );
		$this->assertEquals( $ap_ids, $answers_query->ap_ids );
	}

	/**
	 * @covers Answers_Query::pre_fetch
	 */
	public function testPreFetchForActionHookTriggered() {
		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_pre_fetch_answer_data', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$answer_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'answer', 'post_parent' => $question_id ] );
		$comment_id = $this->factory()->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $answer_ids[0],
				'user_id'         => get_current_user_id(),
			)
		);
		ap_update_post_activity_meta( $answer_ids[0], 'new_c', get_current_user_id() );

		// Test.
		$answers_query = new \Answers_Query( [ 'question_id' => $question_id ] );
		$answers_query->pre_fetch();
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_pre_fetch_answer_data' ) > 0 );
	}
}
