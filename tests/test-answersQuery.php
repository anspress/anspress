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
}
