<?php
class Tests_Ajax extends Ap_AjaxTest
{
	public $current_post;
	public function setUp() {
		// before
		parent::setUp();
		$this->current_post = $this->factory->post->create( array( 'post_title' => 'Comment form loading', 'post_type' => 'question', 'post_status' => 'publish', 'post_content' => 'Donec nec nunc purus' ) );

		error_reporting( 0 & ~E_WARNING );
	}

	public function tearDown() {
		parent::tearDown();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
	}

	public function _set_post_data( $query ) {
		$args = wp_parse_args( $query );
		$_POST[ 'action' ] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Test voting by administrator
	 */
	public function test_voteup_as_administrator() {
		$this->_setRole( 'administrator' );

		// Up vote.
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'post_id='.$this->current_post.'&__nonce='.$nonce.'&ap_ajax_action=vote&type=vote_up' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'voted' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Thank you for voting.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->active === 'vote_up' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );

		// Down vote. Will show undo vote warning.
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'post_id='.$this->current_post.'&__nonce='.$nonce.'&ap_ajax_action=vote&type=vote_down' );
		$this->triggerAjaxCapture();
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Undo your vote first.' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );

		// Undo vote.
		$nonce = wp_create_nonce( 'vote_'.$this->current_post );
		$this->_set_post_data( 'action=ap_ajax&post_id='.$this->current_post.'&__nonce='.$nonce.'&ap_ajax_action=vote&type=vote_up' );
		$this->triggerAjaxCapture();
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'undo' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Your vote has been removed.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 0 );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );
	}

	public function test_load_comments() {
		$this->_setRole( 'administrator' );

		$this->factory->comment->create_many(10, array(
			'comment_type' => 'anspress',
		));
		// Up vote.
		$this->_set_post_data( 'post_id='.$this->current_post.'&ap_ajax_action=load_comments' );
		add_action( 'ap_ajax_load_comments', array( 'AnsPress_Comment_Hooks', 'load_comments' ) );
		$this->triggerAjaxCapture();
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertEquals( $this->ap_ajax_success( 'modal_title' ), 'Comments on Question' );
		$this->assertEquals( $this->ap_ajax_success( 'modal_title' ), 'Comments on Question' );
	}
}