<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Session extends TestCase {

	/**
	 * @covers AnsPress\Session::init
	 */
	public function testInit() {
		$class = new \ReflectionClass('AnsPress\Session');
		$this->assertTrue($class->hasProperty('instance') && $class->getProperty('instance')->isStatic());
	}

	/**
	 * @covers AnsPress\Session::set_question
	 *
	 * @return void
	 */
	public function testSetQuestion() {
		$session = \AnsPress\Session::init();
		$session->set_question( 2 );
		$this->assertContains( 2, $session->get( 'questions' ) );
		$this->assertNotContains( 5, $session->get( 'questions' ) );
	}

	/**
	 * @covers AnsPress\Session::set_answer
	 *
	 * @return void
	 */
	public function testSetAnswer() {
		$session = \AnsPress\Session::init();
		$session->set_answer( 2 );
		$this->assertContains( 2, $session->get( 'answers' ) );
		$this->assertNotContains( 5, $session->get( 'answers' ) );
	}
}
