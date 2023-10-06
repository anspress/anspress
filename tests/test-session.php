<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class Test_Session extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( '\AnsPress\Session' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isProtected() );
		$this->assertTrue( $class->hasProperty( 'name' ) && $class->getProperty( 'name' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'cookie_path' ) && $class->getProperty( 'cookie_path' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'cookie_domain' ) && $class->getProperty( 'cookie_domain' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'expires' ) && $class->getProperty( 'expires' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'id' ) && $class->getProperty( 'id' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( '\AnsPress\Session', 'init' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', '__construct' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'set_cookie' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'delete_cookie' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'generate_id' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'get' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'set' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'set_question' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'set_answer' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'delete' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'post_in_session' ) );
		$this->assertTrue( method_exists( '\AnsPress\Session', 'set_file' ) );
	}

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
