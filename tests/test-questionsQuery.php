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
}
