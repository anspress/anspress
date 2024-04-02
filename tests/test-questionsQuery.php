<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQuestionsQuery extends TestCase {

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
	 * @covers Question_Query::is_main_query
	 */
	public function testIsMainQuery() {
		$question_id = $this->insert_question();
		$question_query = new \Question_Query();
		anspress()->questions = $question_query;
		$this->assertTrue( $question_query->is_main_query() );
	}

	/**
	 * @covers Question_Query::is_main_query
	 */
	public function testIsMainQueryReturnsFalse() {
		$question_id = $this->insert_question();
		$question_query = new \Question_Query();
		anspress()->questions = '';
		$this->assertFalse( $question_query->is_main_query() );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructor() {
		$question_query = new \Question_Query();
		$this->assertInstanceOf( 'Question_Query', $question_query );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgs() {
		$question_query = new \Question_Query();

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$question_query = new \Question_Query();

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish', 'private_post', 'moderate', 'future', 'trash' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForSuperAdminWithAPCurrentUserIgnoreSetToTrue() {
		$this->setRole( 'administrator', true );
		$question_query = new \Question_Query( [ 'ap_current_user_ignore' => true ] );

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( true, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForQueryVars() {
		$question_id = $this->insert_question();
		set_query_var( 'paged', 3 );
		set_query_var( 'parent', $question_id );
		set_query_var( 'ap_s', 'Test Question' );

		// Tests.
		$question_query = new \Question_Query();
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 3, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
		$this->assertEquals( $question_id, $question_query->args['post_parent'] );
		$this->assertEquals( 'Test Question', $question_query->args['s'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForModifyingAPOrderByArgViaAnsPressOption() {
		ap_opt( 'question_order_by', 'newest' );
		$question_query = new \Question_Query();

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'newest', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );

		// Reset.
		ap_opt( 'question_order_by', 'active' );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForFrontPage() {
		$this->go_to( '/' );
		$question_query = new \Question_Query();

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithoutArgsForFrontPageWithQueryVars() {
		$question_id = $this->insert_question();
		$this->go_to( '/' );
		$_REQUEST['ap_paged'] = 5;
		set_query_var( 'paged', 3 );
		set_query_var( 'parent', $question_id );
		set_query_var( 'ap_s', 'Test Question' );

		// Tests.
		$question_query = new \Question_Query();
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 5, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
		$this->assertEquals( $question_id, $question_query->args['post_parent'] );
		$this->assertEquals( 'Test Question', $question_query->args['s'] );

		// Reset.
		unset( $_REQUEST['ap_paged'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithArgs() {
		$question_id = $this->insert_question();
		$question_query = new \Question_Query( [
			'showposts'              => 10,
			'paged'                  => 2,
			'ap_order_by'            => 'newest',
			'ap_question_query'      => false,
			'post_status'            => [ 'publish', 'private_post' ],
			'ap_current_user_ignore' => true,
			'post_parent'            => $question_id,
			'ap_show_unpublished'    => false,
		] );

		// Tests.
		$this->assertEquals( 10, $question_query->args['showposts'] );
		$this->assertEquals( 2, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'newest', $question_query->args['ap_order_by'] );
		$this->assertEquals( false, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish', 'private_post' ], $question_query->args['post_status'] );
		$this->assertEquals( true, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
		$this->assertEquals( $question_id, $question_query->args['post_parent'] );
		$this->assertEquals( false, $question_query->args['ap_show_unpublished'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithArgsForSuperAdmin() {
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$question_query = new \Question_Query( [
			'showposts'              => 5,
			'paged'                  => 3,
			'ap_order_by'            => 'voted',
			'ap_question_query'      => true,
			'ap_current_user_ignore' => false,
			'post_parent'            => $question_id,
			'ap_show_unpublished'    => false,
			'post_status__not_in'    => [ 'future', 'trash' ],
		] );

		// Tests.
		$this->assertEquals( 5, $question_query->args['showposts'] );
		$this->assertEquals( 3, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'voted', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'publish', 'private_post', 'moderate' ], $question_query->args['post_status'] );
		$this->assertEquals( false, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
		$this->assertEquals( $question_id, $question_query->args['post_parent'] );
		$this->assertEquals( false, $question_query->args['ap_show_unpublished'] );
		$this->assertEquals( [ 'future', 'trash' ], $question_query->args['post_status__not_in'] );
	}

	/**
	 * @covers Question_Query::__construct
	 */
	public function testConstructorWithArgsForAPShowUnpublishedSetAsTrue() {
		$this->setRole( 'subscriber' );
		$question_query = new \Question_Query( [
			'ap_show_unpublished' => true,
		] );

		// Tests.
		$this->assertEquals( 20, $question_query->args['showposts'] );
		$this->assertEquals( 1, $question_query->args['paged'] );
		$this->assertEquals( true, $question_query->args['ap_query'] );
		$this->assertEquals( 'active', $question_query->args['ap_order_by'] );
		$this->assertEquals( true, $question_query->args['ap_question_query'] );
		$this->assertEquals( [ 'moderate', 'pending', 'draft', 'trash' ], $question_query->args['post_status'] );
		$this->assertEquals( true, $question_query->args['ap_current_user_ignore'] );
		$this->assertEquals( 'question', $question_query->args['post_type'] );
		$this->assertEquals( true, $question_query->args['ap_show_unpublished'] );
		$this->assertEquals( get_current_user_id(), $question_query->args['author'] );
	}

	/**
	 * @covers Question_Query::get_questions
	 */
	public function testGetQuestionsReturnsWPPostObjects() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();
		$this->assertContainsOnlyInstancesOf( 'WP_Post', $questions );
	}

	/**
	 * @covers Question_Query::get_questions
	 */
	public function testGetQuestionsReturnsIntegerArrays() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query( [ 'fields' => 'ids' ] );
		$questions = $question_query->get_questions();
		$this->assertContainsOnly( 'integer', $questions );
	}

	/**
	 * @covers Question_Query::get_questions
	 */
	public function testGetQuestionsReturnsEmptyArray() {
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();
		$this->assertEmpty( $questions );
	}

	/**
	 * @covers Question_Query::next_question
	 */
	public function testNextQuestion() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();
		foreach ( $questions as $question ) {
			$result = $question_query->next_question();
			$this->assertInstanceOf( 'WP_Post', $result );
			$this->assertSame( $question, $result );
		}
	}

	/**
	 * @covers Question_Query::reset_next
	 */
	public function testResetNext() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();
		$this->assertEquals( $questions[0], $question_query->next_question() );
		$this->assertEquals( $questions[1], $question_query->next_question() );
		$this->assertEquals( $questions[2], $question_query->next_question() );

		// Test reset_next.
		$result1 = $question_query->reset_next();
		$this->assertInstanceOf( 'WP_Post', $result1 );
		$this->assertEquals( $questions[1], $result1 );
		$result2 = $question_query->reset_next();
		$this->assertInstanceOf( 'WP_Post', $result2 );
		$this->assertEquals( $questions[0], $result2 );
	}

	/**
	 * @covers Question_Query::the_question
	 */
	public function testTheQuestion() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();

		// Test.
		foreach ( $questions as $question ) {
			$question_query->the_question();

			global $post;
			$this->assertSame( $question, $post );
			$this->assertSame( $question, anspress()->current_question );
		}
	}

	/**
	 * @covers Question_Query::the_question
	 */
	public function testTheQuestionForActionHookTriggered() {
		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_query_loop_start', function() use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();

		// Test.
		foreach ( $questions as $question ) {
			$question_query->the_question();

			global $post;
			$this->assertSame( $question, $post );
			$this->assertSame( $question, anspress()->current_question );
		}
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_query_loop_start' ) > 0 );
	}

	/**
	 * @covers Question_Query::have_questions
	 */
	public function testHaveQuestions() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$this->assertTrue( $question_query->have_questions() );
	}

	/**
	 * @covers Question_Query::have_questions
	 */
	public function testHaveQuestionsReturnsFalse() {
		$question_query = new \Question_Query();
		$this->assertFalse( $question_query->have_questions() );
	}

	/**
	 * @covers Question_Query::rewind_questions
	 */
	public function testRewindQuestions() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$questions = $question_query->get_questions();
		$this->assertEquals( $questions[0], $question_query->next_question() );
		$this->assertEquals( $questions[1], $question_query->next_question() );
		$this->assertEquals( $questions[2], $question_query->next_question() );

		// Test rewind_questions.
		$question_query->rewind_questions();
		$this->assertEquals( $questions[0], $question_query->next_question() );
	}

	/**
	 * @covers Question_Query::reset_questions_data
	 */
	public function testResetQuestionsData() {
		$question_ids = $this->factory()->post->create_many( 3, [ 'post_type' => 'question' ] );
		$question_query = new \Question_Query();
		$question_query->reset_questions_data();
		$this->assertEquals( $question_query->post, anspress()->current_question );
	}
}
