<?php

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

	/**
	 * @covers AnsPress\Session::post_in_session
	 *
	 * @return void
	 */
	public function testPostInSession() {
		$id = $this->factory->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'question',
				'post_status'  => 'private_post',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);

		$session = \AnsPress\Session::init();
		$session->set_answer( $id );
		$session->set_question( $id );

		// Test for answer.
		if ( $session->get( 'answers' ) ) {
			$this->assertContains( $id, $session->get( 'answers' ) );
		}

		// Test for question.
		if ( $session->get( 'answers' ) ) {
			$this->assertContains( $id, $session->get( 'questions' ) );
		}
	}
}
