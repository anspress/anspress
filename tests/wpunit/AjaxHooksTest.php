<?php

class AjaxHooksTest extends \Codeception\TestCase\WPAjaxTestCase{

	use AnsPress\Tests\Testcases\Common;
	use AnsPress\Tests\Testcases\Ajax;

	public $current_post;

	public function setUp() {
		include_once str_replace( 'includes/../data', '', DIR_TESTDATA ) . '/includes/exceptions.php';

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

		$this->handle( 'ap_ajax' );

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

		$this->handle( 'ap_ajax' );

		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Undo your vote first.' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . $this->current_post ) === 1 );
		$this->_last_response = '';

		// // Undo vote.
		$nonce = wp_create_nonce( 'vote_' . $this->current_post );
		$this->_set_post_data( 'action=ap_ajax&post_id=' . $this->current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );

		$this->handle( 'ap_ajax' );

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

		$this->handle( 'ap_ajax' );

		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertContains( 'apcomment', $this->ap_ajax_success( 'html' ) );
	}

	/**
	 * Test comment modal loading when user don't have permission.
	 *
	 * @covers AnsPress\Ajax\Comment_Modal
	 */
	public function testCommentModalNonLogged() {
		$q = $this->insert_question();

		$this->_set_post_data( 'post_id=' . $q . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $q ) );
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );

		$this->handle( 'comment_modal' );

		/**
		 * This ajax action will be failed as user is not logged in.
		 */
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertEquals( $this->ap_ajax_success( 'action' ), 'ap_comment_modal' );
	}

	/**
	 * Test comment modal load for logged in users.
	 *
	 * @covers AnsPress\Ajax\Comment_Modal
	 */
	public function testCommentModalLoggedIn() {
		$q = $this->insert_question();

		$this->_setRole( 'subscriber' );

		$this->_set_post_data( 'post_id=' . $q . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $q ) );
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );

		$this->handle( 'comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );

		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Add a comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content, 'HTML body of comment modal should not be empty' );
	}

	/**
	 * Test comment modal loading for editing.
	 *
	 * @covers AnsPress\Ajax\Comment_Modal
	 * @since 4.1.8
	 */
	public function testCommentModalEdit() {
		$q = $this->insert_question();

		$this->_setRole( 'subscriber' );
		$c = $this->factory->comment->create(array(
			'comment_post_ID' => $q,
			'user_id' => get_current_user_id(),
		));

		$this->_set_post_data( 'comment_id=' . $c . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $c ) );
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );

		$this->handle( 'comment_modal' );

		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Edit comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content, 'HTML body of comment modal should not be empty' );
	}

}
