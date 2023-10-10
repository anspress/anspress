<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnswerLoop extends TestCase {

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
}
