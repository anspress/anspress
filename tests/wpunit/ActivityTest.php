<?php

class ActivityTest extends \Codeception\TestCase\WPTestCase
{
	private $_question;
	private $_answer;
	private $_comment;

	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] ); }
	}

	public function setUp() {

		// before
		parent::setUp();
		$this->_setRole('administrator' );
		$this->_question = $this->factory->post->create( array( 'post_title' => 'Test question', 'post_type' => 'question', 'post_status' => 'publish' ) );
		
		$this->_answer = $this->factory->post->create( array( 'post_title' => 'Test questions answer', 'post_type' => 'answer', 'post_status' => 'publish', 'post_parent' => $this->_question ) );
		
		$this->_comment = $this->factory->comment->create( array( 'comment_content' => 'Test questions comment', 'comment_post_ID' => $this->_question, 'user_id' ) );
	}

	public function tearDown() {

		// your tear down methods here
		// then
		parent::tearDown();
	}

	public function test_get_activity() {
		$this->_setRole('administrator' );
		$question_id = $this->factory->post->create( array( 'post_title' => 'Test question another', 'post_type' => 'question', 'post_status' => 'publish' ) );

		$question = get_post( $question_id );

		$activity_arr = array(
			'user_id'           => get_current_user_id(),
			'secondary_user'    => $question->post_author,
			'type'              => 'new_answer',
			'status'            => 'publish',
			'question_id'       => $question_id,
			'answer_id'         => 5,
			'content'           => sprintf( __( '%s answered on %s', 'anspress-question-answer' ), ap_activity_user_name( get_current_user_id() ), $question->post_title ),
		);

		$activity_id = ap_new_activity( $activity_arr );
		$this->assertNotFalse( $activity_id );

		$out = ap_get_activity( $activity_id );
		$this->assertObjectHasAttribute( 'id', $out );
		$this->assertObjectHasAttribute( 'user_id', $out );
		$this->assertObjectHasAttribute( 'type', $out );
		$this->assertObjectHasAttribute( 'parent_type', $out );
		$this->assertObjectHasAttribute( 'status', $out );
		$this->assertObjectHasAttribute( 'content', $out );
		$this->assertObjectHasAttribute( 'permalink', $out );
		$this->assertObjectHasAttribute( 'question_id', $out );
		$this->assertObjectHasAttribute( 'answer_id', $out );
		$this->assertObjectHasAttribute( 'item_id', $out );
		$this->assertObjectHasAttribute( 'term_ids', $out );
		$this->assertObjectHasAttribute( 'created', $out );
		$this->assertObjectHasAttribute( 'updated', $out );
	}

}
