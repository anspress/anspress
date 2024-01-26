<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAsyncTasks extends TestCase {

	public function testClassProperties() {
		// For \AnsPress\AsyncTasks\NewQuestion class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\NewQuestion' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\NewAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\NewAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\SelectAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\SelectAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\PublishComment class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\PublishComment' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\UpdateQuestion class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\UpdateQuestion' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );

		// For \AnsPress\AsyncTasks\UpdateAnswer class.
		$class = new \ReflectionClass( '\AnsPress\AsyncTasks\UpdateAnswer' );
		$this->assertTrue( $class->hasProperty( 'action' ) && $class->getProperty( 'action' )->isProtected() );
	}

	public function testMethodExists() {
		// For \AnsPress\AsyncTasks\NewQuestion class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewQuestion', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewQuestion', 'run_action' ) );

		// For \AnsPress\AsyncTasks\NewAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\NewAnswer', 'run_action' ) );

		// For \AnsPress\AsyncTasks\SelectAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\SelectAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\SelectAnswer', 'run_action' ) );

		// For \AnsPress\AsyncTasks\PublishComment class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\PublishComment', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\PublishComment', 'run_action' ) );

		// For \AnsPress\AsyncTasks\UpdateQuestion class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateQuestion', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateQuestion', 'run_action' ) );

		// For \AnsPress\AsyncTasks\UpdateAnswer class.
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateAnswer', 'prepare_data' ) );
		$this->assertTrue( method_exists( '\AnsPress\AsyncTasks\UpdateAnswer', 'run_action' ) );
	}

	/**
	 * @covers AnsPress\AsyncTasks\NewQuestion::prepare_data
	 * @covers AnsPress\AsyncTasks\NewQuestion::run_action
	 */
	public function testNewQuestion() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\NewQuestion();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\NewQuestion', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ $question_id, get_post( $question_id ) ] );
		$this->assertEquals( [ 'post_id' => $question_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_post = get_post( $question_id );
		$_REQUEST['post_id'] = $question_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_after_new_question', function( $post_id, $post ) use ( $question_id, $mock_post, &$action_triggered ) {
			$this->assertEquals( $question_id, $post_id );
			$this->assertEquals( $mock_post, $post );
			$action_triggered = true;
		}, 10, 2 );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\NewQuestion', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['post_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}

	/**
	 * @covers AnsPress\AsyncTasks\NewAnswer::prepare_data
	 * @covers AnsPress\AsyncTasks\NewAnswer::run_action
	 */
	public function testNewAnswer() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );
		$answer_id = $this->factory->post->create( array( 'post_type' => 'answer', 'post_parent' => $question_id ) );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\NewAnswer();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\NewAnswer', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ $answer_id, get_post( $answer_id ) ] );
		$this->assertEquals( [ 'post_id' => $answer_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_post = get_post( $answer_id );
		$_REQUEST['post_id'] = $answer_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_after_new_answer', function( $post_id, $post ) use ( $answer_id, $mock_post, &$action_triggered ) {
			$this->assertEquals( $answer_id, $post_id );
			$this->assertEquals( $mock_post, $post );
			$action_triggered = true;
		}, 10, 2 );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\NewAnswer', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['post_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}

	/**
	 * @covers AnsPress\AsyncTasks\UpdateQuestion::prepare_data
	 * @covers AnsPress\AsyncTasks\UpdateQuestion::run_action
	 */
	public function testUpdateQuestion() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\UpdateQuestion();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\UpdateQuestion', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ $question_id, get_post( $question_id ) ] );
		$this->assertEquals( [ 'post_id' => $question_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_post = get_post( $question_id );
		$_REQUEST['post_id'] = $question_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_processed_update_question', function( $post_id, $post ) use ( $question_id, $mock_post, &$action_triggered ) {
			$this->assertEquals( $question_id, $post_id );
			$this->assertEquals( $mock_post, $post );
			$action_triggered = true;
		}, 10, 2 );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\UpdateQuestion', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['post_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}

	/**
	 * @covers AnsPress\AsyncTasks\UpdateAnswer::prepare_data
	 * @covers AnsPress\AsyncTasks\UpdateAnswer::run_action
	 */
	public function testUpdateAnswer() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );
		$answer_id = $this->factory->post->create( array( 'post_type' => 'answer', 'post_parent' => $question_id ) );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\UpdateAnswer();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\UpdateAnswer', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ $answer_id, get_post( $answer_id ) ] );
		$this->assertEquals( [ 'post_id' => $answer_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_post = get_post( $answer_id );
		$_REQUEST['post_id'] = $answer_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_processed_update_answer', function( $post_id, $post ) use ( $answer_id, $mock_post, &$action_triggered ) {
			$this->assertEquals( $answer_id, $post_id );
			$this->assertEquals( $mock_post, $post );
			$action_triggered = true;
		}, 10, 2 );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\UpdateAnswer', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['post_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}

	/**
	 * @covers AnsPress\AsyncTasks\SelectAnswer::prepare_data
	 * @covers AnsPress\AsyncTasks\SelectAnswer::run_action
	 */
	public function testSelectAnswer() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );
		$answer_id = $this->factory->post->create( array( 'post_type' => 'answer', 'post_parent' => $question_id ) );
		ap_set_selected_answer( $question_id, $answer_id );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\SelectAnswer();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\SelectAnswer', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ ap_get_post( $answer_id ), $question_id ] );
		$this->assertEquals( [ 'post_id' => $answer_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_post = ap_get_post( $answer_id );
		$_REQUEST['post_id'] = $answer_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_select_answer', function( $post ) use ( $mock_post, &$action_triggered ) {
			$this->assertEquals( $mock_post, $post );
			$action_triggered = true;
		} );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\SelectAnswer', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['post_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}

	/**
	 * @covers AnsPress\AsyncTasks\PublishComment::prepare_data
	 * @covers AnsPress\AsyncTasks\PublishComment::run_action
	 */
	public function testPublishComment() {
		$question_id = $this->factory->post->create( array( 'post_type' => 'question' ) );
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $question_id, 'comment_type' => 'anspress' ) );

		// Initialize class.
		$tasks = new \AnsPress\AsyncTasks\PublishComment();

		// Test begins.
		// For prepare_data method.
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\PublishComment', 'prepare_data' );
		$method->setAccessible( true );
		$data = $method->invoke( $tasks, [ get_comment( $comment_id ), $comment_id ] );
		$this->assertEquals( [ 'comment_id' => $comment_id ], $data );

		// For run_action method.
		// Test 1.
		$mock_comment = get_comment( $comment_id );
		$_REQUEST['comment_id'] = $comment_id;
		$action_triggered = false;
		add_action( 'wp_async_ap_publish_comment', function( $comment ) use ( $mock_comment, &$action_triggered ) {
			$this->assertEquals( $mock_comment, $comment );
			$action_triggered = true;
		} );
		$method = new \ReflectionMethod( 'AnsPress\AsyncTasks\PublishComment', 'run_action' );
		$method->setAccessible( true );
		$method->invoke( $tasks );
		$this->assertTrue( $action_triggered );

		// Test 2.
		unset( $_REQUEST['comment_id'] );
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );

		// Test 3.
		$_REQUEST['test_request'] = $question_id;
		$action_triggered = false;
		$method->invoke( $tasks );
		$this->assertFalse( $action_triggered );
		unset( $_REQUEST['test_request'] );
	}
}
