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
		$class = new \ReflectionClass( 'AnsPress\Session' );
		$this->assertTrue( $class->hasProperty( 'instance' ) && $class->getProperty( 'instance' )->isStatic() );

		// Test for instance match.
		$session1 = \AnsPress\Session::init();
		$this->assertInstanceOf( '\AnsPress\Session', $session1 );
		$session2 = \AnsPress\Session::init();
		$this->assertSame( $session1, $session2 );
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
	 * @covers AnsPress\Session::set_question
	 * @covers AnsPress\Session::set_answer
	 */
	public function testSetQuestionAnswer() {
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

	/**
	 * @covers AnsPress\Session::set_file
	 */
	public function testset_file() {
		$session = \AnsPress\Session::init();

		// Test with empty file.
		$session->set_file( '' );
		$this->assertIsArray( $session->get( 'files' ) );
		$this->assertEquals( [ 0 => '' ], $session->get( 'files' ) );

		// Test with passing the file.
		$session->set_file( 'example.txt' );
		$this->assertContains( 'example.txt', $session->get( 'files' ) );

		// Test with passing the file multiple times.
		$session->set_file( 'example-1.txt' );
		$session->set_file( 'example-2.txt' );
		$session->set_file( 'example-3.txt' );
		$this->assertContains( 'example.txt', $session->get( 'files' ) );
		$this->assertContains( 'example-1.txt', $session->get( 'files' ) );
		$this->assertContains( 'example-2.txt', $session->get( 'files' ) );
		$this->assertContains( 'example-3.txt', $session->get( 'files' ) );
		$this->assertEquals( 5, count( $session->get( 'files' ) ) );
	}

	/**
	 * @covers AnsPress\Session::generate_id
	 */
	public function testGenerateID() {
		$session = \AnsPress\Session::init();

		// Set the method to be accessible.
		$method = new \ReflectionMethod( 'AnsPress\Session', 'generate_id' );
		$method->setAccessible( true );

		// Call the method.
		$result = $method->invoke( $session );

		// Test begins.
		$this->assertIsString( $result );
		$this->assertEquals( 32, strlen( $result ) );
		$this->assertMatchesRegularExpression( '/^[0-9a-f]+$/', $result );
	}

	/**
	 * @covers AnsPress\Session::init
	 */
	public function testAnsPressSessionInitOfSingletonWhenNull() {
		$reflectionClass = new \ReflectionClass( 'AnsPress\Session' );
		$property = $reflectionClass->getProperty( 'instance' );
		$property->setAccessible( true );
		$property->setValue( null );

		// Test.
		$instance = \AnsPress\Session::init();
		$this->assertNotNull( $instance );
		$this->assertInstanceOf( 'AnsPress\Session', $instance );
	}

	/**
	 * @covers AnsPress\Session::get
	 */
	public function testGetWhenNoTransientSet() {
		$session = \AnsPress\Session::init();
		$this->assertNull( $session->get( 'invalid_key' ) );
	}

	/**
	 * @covers AnsPress\Session::get
	 */
	public function testGetWhenTransientSet() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value' ], DAY_IN_SECONDS );
		$session->get( 'key' );
		$this->assertEquals( 'value', $session->get( 'key' ) );
	}

	/**
	 * @covers AnsPress\Session::set
	 */
	public function testSetWhenNoTransientSet() {
		$session = \AnsPress\Session::init();
		$session->set( 'some_key', 'some_value' );
		$this->assertEquals( 'some_value', $session->get( 'some_key' ) );
	}

	/**
	 * @covers AnsPress\Session::set
	 */
	public function testSetWhenTransientSet() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value' ], DAY_IN_SECONDS );
		$session->set( 'key', 'new_value' );
		$this->assertEquals( 'new_value', $session->get( 'key' ) );
	}

	/**
	 * @covers AnsPress\Session::set
	 */
	public function testsETWhenValueIsNull() {
		$session = \AnsPress\Session::init();
		$session->set( 'some_key' );
		$this->assertNull( $session->get( 'some_key' ) );
	}

	/**
	 * @covers AnsPress\Session::delete
	 */
	public function testDeleteWhenTransientSet() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value' ], DAY_IN_SECONDS );
		$session->delete( 'key' );
		$this->assertNull( $session->get( 'key' ) );
	}

	/**
	 * @covers AnsPress\Session::delete
	 */
	public function testDeleteWhenTransientSetButHaveManyValues() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value', 'key2' => 'value2' ], DAY_IN_SECONDS );
		$session->delete( 'key' );
		$this->assertNull( $session->get( 'key' ) );
		$this->assertEquals( 'value2', $session->get( 'key2' ) );
	}

	/**
	 * @covers AnsPress\Session::delete
	 */
	public function testDeleteWhenKeyIsNull() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value' ], DAY_IN_SECONDS );
		$session->delete();
		$this->assertNull( $session->get( 'key' ) );
	}

	/**
	 * @covers AnsPress\Session::delete
	 */
	public function testDeleteWhenKeyIsNullAndHaveManayValuesSetInTransient() {
		$session = \AnsPress\Session::init();
		$reflectionClass = new \ReflectionClass( $session );
		$property = $reflectionClass->getProperty( 'id' );
		$property->setAccessible( true );
		$property->setValue( $session, 'test-session' );
		set_transient( 'anspress_session_test-session', [ 'key' => 'value', 'key2' => 'value2' ], DAY_IN_SECONDS );
		$session->delete();
		$this->assertNull( $session->get( 'key' ) );
		$this->assertNull( $session->get( 'key2' ) );
	}

	/**
	 * @covers AnsPress\Session::delete
	 */
	public function testDeleteWhenTransientNotSet() {
		$session = \AnsPress\Session::init();
		$session->delete( 'key' );
		$this->assertNull( $session->get( 'key' ) );
	}

	/**
	 * @covers AnsPress\Session::post_in_session
	 */
	public function testPostInSessionWhenSessionNotSet() {
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'question',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);
		$session = \AnsPress\Session::init();
		anspress()->session->set( 'questions', [] );
		anspress()->session->set( 'answers', [] );
		$this->assertFalse( $session->post_in_session( $post_id ) );
	}

	/**
	 * @covers AnsPress\Session::post_in_session
	 */
	public function testPostInSessionWhenSessionSet() {
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'question',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);
		$session = \AnsPress\Session::init();
		anspress()->session->set( 'questions', [ $post_id ] );
		$this->assertTrue( $session->post_in_session( $post_id ) );
	}

	/**
	 * @covers AnsPress\Session::post_in_session
	 */
	public function testPostInSessionWhenSessionSetAndPostTypeIsAnswer() {
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'answer',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);
		$session = \AnsPress\Session::init();
		anspress()->session->set( 'answers', [ $post_id ] );
		$this->assertTrue( $session->post_in_session( $post_id ) );
	}

	/**
	 * @covers AnsPress\Session::post_in_session
	 */
	public function testPostInSessionWhenSessionSetAndUserIsLoggedIn() {
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create(
			array(
				'post_title'   => 'Mauris a velit id neque dignissim congue',
				'post_type'    => 'question',
				'post_content' => 'Sed cursus, diam sit amet',
			)
		);
		$session = \AnsPress\Session::init();
		anspress()->session->set( 'questions', [ $post_id ] );
		anspress()->session->set( 'answers', [ $post_id ] );
		$this->assertFalse( $session->post_in_session( $post_id ) );
	}
}
