<?php

namespace Anspress\Tests;

use AnsPress\WPTestUtils\WPIntegration\TestCaseAjax;

class TestAjax extends TestCaseAjax {

	use Testcases\Common;
	use Testcases\Ajax;

	public static $current_post;

	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		self::$current_post = $factory->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'init' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'suggest_similar_questions' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'permanent_delete_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'toggle_featured' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'close_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'send' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_tinymce' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'convert_to_post' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'load_filter_order_by' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'subscribe_to_question' ) );
		$this->assertTrue( method_exists( 'AnsPress_Ajax', 'search_tags' ) );
	}

	public function _set_post_data( $query ) {
		$args            = wp_parse_args( $query );
		$_POST['action'] = 'ap_ajax';
		foreach ( $args as $key => $value ) {
			$_POST[ $key ] = $value;
		}
	}

	/**
	 * @covers ::ap_send_json
	 */
	public function testAPSendJSON() {
		// Test 1.
		$this->functionHandle( 'ap_send_json' );
		$expected = wp_json_encode( [ 'is_ap_ajax' => true ] );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$test_data = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 3.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'something_wrong',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 4.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'success',
			'key1'    => 'value1',
			'key2'    => 'value2',
		);
		$this->functionHandle( 'ap_send_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Start output buffer.
		ob_start();
	}

	/**
	 * @covers ::ap_ajax_json
	 */
	public function testAPAjaxJSON() {
		// Test 1.
		$test_data = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);
		$this->functionHandle( 'ap_ajax_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true, 'ap_responce' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$test_data = array(
			'success' => false,
		);
		$this->functionHandle( 'ap_ajax_json', $test_data );
		$expected = wp_json_encode( array_merge( [ 'is_ap_ajax' => true, 'ap_responce' => true ], $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 3.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'something_wrong',
		);
		$this->functionHandle( 'ap_ajax_json', $test_data );
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'Something went wrong, last action failed.',
				'message_type' => 'error',
			],
			'success'     => false,
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 4.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'success',
			'key1'    => 'value1',
			'key2'    => 'value2',
		);
		$this->functionHandle( 'ap_ajax_json', $test_data );
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'Success',
				'message_type' => 'success',
			],
			'success'     => true,
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Test 5.
		$this->_last_response = '';
		$test_data = array(
			'message' => 'upload_limit_crossed'
		);
		$this->functionHandle( 'ap_ajax_json', $test_data );
		$additional = array(
			'is_ap_ajax'  => true,
			'ap_responce' => true,
			'snackbar'    => [
				'message'      => 'You have already attached maximum numbers of allowed uploads.',
				'message_type' => 'warning',
			],
			'success'     => true,
		);
		$expected = wp_json_encode( array_merge( $additional, $test_data ) );
		$this->assertJsonStringEqualsJsonString( $expected, $this->_last_response );

		// Start output buffer.
		ob_start();
	}

	/**
	 * @covers AnsPress_Vote::vote
	 */
	public function testVote() {
		// Test for user who can vote.
		$this->setRole( 'subscriber' );
		add_action( 'ap_ajax_vote', array( 'AnsPress_Vote', 'vote' ) );

		// Up vote.
		$nonce = wp_create_nonce( 'vote_' . self::$current_post );
		$this->_set_post_data( 'post_id=' . self::$current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'voted' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Thank you for voting.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->active === 'vote_up' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . self::$current_post ) === 1 );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . self::$current_post );
		$this->_set_post_data( 'post_id=' . self::$current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Undo your vote first.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->active === 'vote_down' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . self::$current_post ) === 1 );
		$this->_last_response = '';

		// Undo vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . self::$current_post );
		$this->_set_post_data( 'action=ap_ajax&post_id=' . self::$current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'undo' );
		$this->assertTrue( $this->ap_ajax_success( 'vote_type' ) === 'vote_up' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Your vote has been removed.' );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->net === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'voteData' )->active === '' );
		$this->assertTrue( wp_verify_nonce( $this->ap_ajax_success( 'voteData' )->nonce, 'vote_' . self::$current_post ) === 1 );

		// Test for user who can not vote.
		$this->setRole( 'ap_banned' );

		// Up vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . self::$current_post );
		$this->_set_post_data( 'post_id=' . self::$current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You do not have permission to vote.' );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . self::$current_post );
		$this->_set_post_data( 'post_id=' . self::$current_post . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You do not have permission to vote.' );

		// Test on disabling down vote.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		ap_opt( 'disable_down_vote_on_question', true );
		ap_opt( 'disable_down_vote_on_answer', true );

		// For question post type.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
			)
		);
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $question_id );
		$this->_set_post_data( 'post_id=' . $question_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting down is disabled.' );

		// For answer post type.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
				'post_parent'  => $question_id,
			)
		);
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $answer_id );
		$this->_set_post_data( 'post_id=' . $answer_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting down is disabled.' );

		// Reset option.
		ap_opt( 'disable_down_vote_on_question', false );
		ap_opt( 'disable_down_vote_on_answer', false );

		// Voting on own question and answer.
		$this->setRole( 'subscriber' );

		// For question.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);

		// Up vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $question_id );
		$this->_set_post_data( 'post_id=' . $question_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on own post is not allowed' );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $question_id );
		$this->_set_post_data( 'post_id=' . $question_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on own post is not allowed' );

		// For answer.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
				'post_parent'  => $question_id,
			)
		);

		// Up vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $answer_id );
		$this->_set_post_data( 'post_id=' . $answer_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on own post is not allowed' );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $answer_id );
		$this->_set_post_data( 'post_id=' . $answer_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on own post is not allowed' );

		// Test for invalid nonce or nonce not passed.
		$this->setRole( 'subscriber' );

		// Invalid nonce.
		$this->_last_response = '';
		$this->_set_post_data( 'post_id=' . self::$current_post . '&__nonce=invalid_nonce&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Nonce not passed.
		$this->_last_response = '';
		$this->_set_post_data( 'post_id=' . self::$current_post . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Test for voting on restricted question and answer.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// For question.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
			)
		);

		// Up vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $question_id );
		$this->_set_post_data( 'post_id=' . $question_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on restricted posts are not allowed.' );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $question_id );
		$this->_set_post_data( 'post_id=' . $question_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on restricted posts are not allowed.' );

		// For answer.
		$answer_id = $this->factory->post->create(
			array(
				'post_title'   => 'Answer post',
				'post_type'    => 'answer',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
				'post_parent'  => $question_id,
			)
		);

		// Up vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $answer_id );
		$this->_set_post_data( 'post_id=' . $answer_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_up' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on restricted posts are not allowed.' );

		// Down vote.
		$this->_last_response = '';
		$nonce = wp_create_nonce( 'vote_' . $answer_id );
		$this->_set_post_data( 'post_id=' . $answer_id . '&__nonce=' . $nonce . '&ap_ajax_action=vote&type=vote_down' );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Voting on restricted posts are not allowed.' );
	}

	/**
	 * @covers AnsPress_Comment_Hooks::load_comments
	 */
	public function testLoadComments() {
		$this->setRole( 'subscriber' );
		add_action( 'ap_ajax_load_comments', array( 'AnsPress_Comment_Hooks', 'load_comments' ) );

		// Test 1.
		$this->_set_post_data( 'post_id=' . self::$current_post . '&ap_ajax_action=load_comments' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertStringNotContainsString( 'apcomment', $this->_last_response );
		$this->assertStringContainsString( 'No comments found.', $this->_last_response );

		// Test 2.
		$this->_last_response = '';
		$page_id = $this->factory->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $page_id,
				'comment_type'    => 'anspress',
			)
		);
		$this->_set_post_data( 'post_id=' . $page_id . '&ap_ajax_action=load_comments' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertStringNotContainsString( 'apcomment', $this->_last_response );
		$this->assertStringContainsString( 'Not a valid post ID.', $this->_last_response );

		// Test 3.
		$this->setRole( 'ap_banned' );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
			)
		);
		$comment_ids = $this->factory->comment->create_many(
			5,
			array(
				'comment_type'    => 'anspress',
				'comment_post_ID' => $question_id,
			)
		);
		$this->_last_response = '';
		$this->_set_post_data( 'post_id=' . $question_id . '&ap_ajax_action=load_comments' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertStringNotContainsString( 'apcomment', $this->_last_response );
		$this->assertStringContainsString( 'Sorry, you do not have permission to read comments.', $this->_last_response );
		$this->logout();

		// Test 4.
		$this->_last_response = '';
		$comment_ids = $this->factory->comment->create_many(
			5,
			array(
				'comment_type'    => 'anspress',
				'comment_post_ID' => self::$current_post,
			)
		);
		$this->_set_post_data( 'post_id=' . self::$current_post . '&ap_ajax_action=load_comments' );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertStringContainsString( 'apcomment', $this->_last_response );
	}

	/**
	 * @covers AnsPress\Ajax::Comment_Modal
	 */
	public function testCommentModal() {
		add_action( 'wp_ajax_comment_modal', array( 'AnsPress\Ajax\Comment_Modal', 'init' ) );

		// Test 1.
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		@$this->handle( 'comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Add a comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_type'    => 'anspress',
				'user_id'         => get_current_user_id(),
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $comment_id ) );
		$this->handle( 'comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$modal = $this->ap_ajax_success( 'modal' );
		$this->assertEquals( $modal->title, 'Edit comment' );
		$this->assertEquals( $modal->name, 'comment' );
		$this->assertNotEmpty( $modal->content );

		// Test 4.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$comment_id = $this->factory->comment->create(
			array(
				'comment_post_ID' => $question_id,
				'comment_type'    => 'anspress',
				'user_id'         => $user_id,
			)
		);
		$this->_set_post_data( 'comment_id=' . $comment_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'edit_comment_' . $comment_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You don\'t have enough permissions to do this action.' );

		// Test 5.
		$this->_last_response = '';
		$question_id = $this->factory->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'post_id=' . $question_id . '&action=comment_modal&__nonce=' . wp_create_nonce( 'new_comment_' . $question_id ) );
		$this->handle( 'comment_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_comment_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Trying to cheat?!' );
	}
}
