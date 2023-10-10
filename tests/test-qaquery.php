<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestQAQuery extends TestCase {

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
}
