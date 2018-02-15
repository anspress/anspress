<?php

class AjaxHooksTest extends \Codeception\TestCase\WPAjaxTestCase {

	use AnsPress\Tests\Testcases\Common;
	use AnsPress\Tests\Testcases\Ajax;

	public $current_post;

	public function setUp() {
		// before
		parent::setUp();
		$this->current_post = $this->factory->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		error_reporting( 0 & ~E_WARNING );
	}

	public function tearDown() {
		parent::tearDown();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
	}

	public function _set_post_data( $query ) {
		$args            = wp_parse_args( $query );
		$_POST['action'] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * Test voting by administrator
	 *
	 * @covers AnsPress_Vote::vote
	 */
	public function testVoteupAdministrator() {
		$this->_setRole( 'administrator' );

		// Up vote.
		$nonce = wp_create_nonce( 'vote_' . $this->current_post );
		$this->_set_post_data( 'post_id=' . $this->current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );

		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
		}

		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'voted' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Thank you for voting.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->active === 'vote_up' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );

		$this->_last_response = '';

		// Down vote. Will show undo vote warning.
		$nonce = wp_create_nonce( 'vote_' . $this->current_post );
		$this->_set_post_data( 'post_id=' . $this->current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
		}
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Undo your vote first.' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );
		$this->_last_response = '';

		// // Undo vote.
		$nonce = wp_create_nonce( 'vote_' . $this->current_post );
		$this->_set_post_data( 'action=ap_ajax&post_id=' . $this->current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
		}
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'undo' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Your vote has been removed.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 0 );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );
	}

	/**
	 * @covers AnsPress_Comment_Hooks::load_comments
	 */
	public function testLoadComments() {
		$this->_setRole( 'administrator' );

		$this->factory->comment->create_many(
			5, array(
				'comment_type'    => 'anspress',
				'comment_post_ID' => $this->current_post,
			)
		);
		// Up vote.
		$this->_set_post_data( 'post_id=' . $this->current_post . '&ap_ajax_action=load_comments' );
		add_action( 'ap_ajax_load_comments', array( 'AnsPress_Comment_Hooks', 'load_comments' ) );
		try {
			$this->_handleAjax( 'ap_ajax' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->_last_response = $e->getMessage();
		}

		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertContains( 'apcomment', $this->ap_ajax_success( 'html' ) );
	}

	/**
	 * @covers AnsPress_Ajax::toggle_best_answer
	 */
	// public function testToggleBestAnswer() {
	// $id = $this->insert_answer();
	// $nonce = wp_create_nonce( 'select-answer-' . $id->a );
	// $this->_setRole( 'ap_moderator' );
	// $this->_set_post_data( 'answer_id='.$id->a.'&nonce=' . $nonce .'&action=ap_toggle_best_answer' );
	// codecept_debug($_POST);
	// add_action( 'wp_ajax_ap_toggle_best_answer', array( 'AnsPress_Ajax', 'toggle_best_answer' ) );
	// do_action('wp_ajax_ap_toggle_best_answer');
	// try {
	// $this->_handleAjax( 'ap_toggle_best_answer' );
	// } catch ( WPAjaxDieStopException $e ) {
	// $this->_last_response = $e->getMessage();
	// }
	// codecept_debug($this->_last_response);
	// codecept_debug('hhhhhhhhhhhhhhhhh');
	// $this->assertTrue( $this->ap_ajax_success( 'success' ) );
	// $this->assertEquals( 'selected', $this->ap_ajax_success( 'action' ) );
	// }
}
