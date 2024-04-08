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
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		ap_opt( 'disable_down_vote_on_question', true );
		ap_opt( 'disable_down_vote_on_answer', true );

		// For question post type.
		$question_id = $this->factory()->post->create(
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
		$answer_id = $this->factory()->post->create(
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
		$question_id = $this->factory()->post->create(
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
		$answer_id = $this->factory()->post->create(
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
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );

		// For question.
		$question_id = $this->factory()->post->create(
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
		$answer_id = $this->factory()->post->create(
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
		$page_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Comment form loading',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'Donec nec nunc purus',
			)
		);
		$comment_id = $this->factory()->comment->create(
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
		$user_id = $this->factory()->user->create( array( 'role' => 'subscriber' ) );
		$question_id = $this->factory()->post->create(
			array(
				'post_title'   => 'Question post',
				'post_type'    => 'question',
				'post_status'  => 'moderate',
				'post_content' => 'Donec nec nunc purus',
				'post_author'  => $user_id,
			)
		);
		$comment_ids = $this->factory()->comment->create_many(
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
		$comment_ids = $this->factory()->comment->create_many(
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
	 * @covers AnsPress_Uploader::delete_attachment
	 */
	public function testDeleteAttachment() {
		add_action( 'ap_ajax_delete_attachment', array( 'AnsPress_Uploader', 'delete_attachment' ) );

		// Test 1.
		$this->setRole( 'administrator' );
		$question_id = $this->factory()->post->create( array( 'post_type' => 'question' ) );
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->assertTrue( ap_have_attach( $question_id ) );
		$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'message' ) === 'no_permission' );
		$this->assertTrue( ap_have_attach( $question_id ) );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->factory()->post->create( array( 'post_type' => 'question' ) );
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->assertTrue( ap_have_attach( $question_id ) );
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'message' ) === 'no_permission' );
		$this->assertTrue( ap_have_attach( $question_id ) );

		// Test 3.
		$this->_last_response = '';
		$this->setRole( 'subsciber' );
		$question_id = $this->factory()->post->create( array( 'post_type' => 'question' ) );
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->assertTrue( ap_have_attach( $question_id ) );
		$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertFalse( ap_have_attach( $question_id ) );
		$attachment = get_post( $attachment_id );
		$this->assertNull( $attachment );

		// Test 4.
		$this->_last_response = '';
		$this->setRole( 'subsciber' );
		$question_id = $this->factory()->post->create( array( 'post_type' => 'question' ) );
		$attachment_id_1 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		$attachment_id_2 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/files/anspress.pdf', $question_id );
		$attachment_id_3 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/answer.png', $question_id );
		$attachment_id_4 = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/question.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->assertTrue( ap_have_attach( $question_id ) );
		$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id_1 . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id_1 ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( ap_have_attach( $question_id ) );
		$attachment_1 = get_post( $attachment_id_1 );
		$this->assertNull( $attachment_1 );
		$attachment_2 = get_post( $attachment_id_2 );
		$this->assertNotNull( $attachment_2 );
		$attachment_3 = get_post( $attachment_id_3 );
		$this->assertNotNull( $attachment_3 );
		$attachment_4 = get_post( $attachment_id_4 );
		$this->assertNotNull( $attachment_4 );

		// Test 5.
		$question_id = $this->factory()->post->create( array( 'post_type' => 'question' ) );
		$attachment_id = $this->factory()->attachment->create_upload_object( __DIR__ . '/assets/img/anspress-hero.png', $question_id );
		ap_update_post_attach_ids( $question_id );
		$this->assertTrue( ap_have_attach( $question_id ) );
		if ( \is_multisite() ) {
			$this->setRole( 'administrator' );

			// Tests.
			// Before granting super admin role.
			$this->_last_response = '';
			$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertTrue( $this->ap_ajax_success( 'message' ) === 'no_permission' );
			$this->assertTrue( ap_have_attach( $question_id ) );

			// After granting super admin role.
			$this->_last_response = '';
			grant_super_admin( get_current_user_id() );
			$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertTrue( $this->ap_ajax_success( 'success' ) );
			$this->assertFalse( ap_have_attach( $question_id ) );
		} else {
			$this->setRole( 'administrator' );

			// Tests.
			$this->_last_response = '';
			$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $attachment_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $attachment_id ) );
			$this->handle( 'ap_ajax' );
			$this->assertTrue( $this->ap_ajax_success( 'success' ) );
			$this->assertFalse( ap_have_attach( $question_id ) );
		}
	}

	/**
	 * @covers AnsPress_Uploader::upload_modal
	 */
	public function testUploadModal() {
		add_action( 'wp_ajax_ap_upload_modal', array( 'AnsPress_Uploader', 'upload_modal' ) );

		// For user who do not have access to upload image.
		$this->_set_post_data( 'action=ap_upload_modal&__nonce=' . wp_create_nonce( 'ap_upload_image' ) );
		$this->handle( 'ap_upload_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry! you do not have permission to upload image.' );

		// For user who have access to upload image.
		$this->setRole( 'subscriber' );

		// Test 1.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_upload_modal&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_upload_modal' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Test 2.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_upload_modal&__nonce=' . wp_create_nonce( 'ap_upload_image' ) );
		$this->handle( 'ap_upload_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_upload_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'title' ) === 'Select image file to upload' );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<form id="form_image_upload" name="form_image_upload" method="POST" enctype="multipart/form-data" action=""  apform>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-form-group ap-field-form_image_upload-image ap-field-type-upload ">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<label class="ap-form-label" for="form_image_upload-image">Image</label>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-field-group-w">', $this->ap_ajax_success( 'html' ) );
		$custom_attrs = wp_json_encode(
			array(
				'max_files'       => 1,
				'multiple'        => false,
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'You cannot add more then 1 files',
				'field_name'      => 'image',
				'form_name'       => 'form_image_upload',
			)
		);
		$this->assertStringContainsString( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $custom_attrs ) . '" name="form_image_upload-image" id="form_image_upload-image" class="ap-form-control " accept=".jpg,.jpeg,.gif,.png"  /></div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-field-desc">Select image(s) to upload. Only .jpg, .png and .gif files allowed.</div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-upload-list"></div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="ap_form_name" value="form_image_upload" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="form_image_upload_nonce" value="' . wp_create_nonce( 'form_image_upload' ) . '" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="form_image_upload_submit" value="true" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<button type="submit" class="ap-btn ap-btn-submit">Upload &amp; insert</button>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="action" value="ap_image_upload" /><input type="hidden" name="image_for" value="" />', $this->ap_ajax_success( 'html' ) );

		// Test 3.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_upload_modal&__nonce=' . wp_create_nonce( 'ap_upload_image' ) . '&image_for=question' );
		$this->handle( 'ap_upload_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_upload_modal' );
		$this->assertTrue( $this->ap_ajax_success( 'title' ) === 'Select image file to upload' );
		$this->assertStringContainsString( 'html', $this->_last_response );
		$this->assertStringContainsString( '<form id="form_image_upload" name="form_image_upload" method="POST" enctype="multipart/form-data" action=""  apform>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-form-group ap-field-form_image_upload-image ap-field-type-upload ">', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<label class="ap-form-label" for="form_image_upload-image">Image</label>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-field-group-w">', $this->ap_ajax_success( 'html' ) );
		$custom_attrs = wp_json_encode(
			array(
				'max_files'       => 1,
				'multiple'        => false,
				'label_deny_type' => 'This file type is not allowed to upload.',
				'async_upload'    => false,
				'label_max_added' => 'You cannot add more then 1 files',
				'field_name'      => 'image',
				'form_name'       => 'form_image_upload',
			)
		);
		$this->assertStringContainsString( '<div class="ap-upload-c"><input type="file"data-upload="' . esc_js( $custom_attrs ) . '" name="form_image_upload-image" id="form_image_upload-image" class="ap-form-control " accept=".jpg,.jpeg,.gif,.png"  /></div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-field-desc">Select image(s) to upload. Only .jpg, .png and .gif files allowed.</div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<div class="ap-upload-list"></div>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="ap_form_name" value="form_image_upload" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="form_image_upload_nonce" value="' . wp_create_nonce( 'form_image_upload' ) . '" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="form_image_upload_submit" value="true" />', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<button type="submit" class="ap-btn ap-btn-submit">Upload &amp; insert</button>', $this->ap_ajax_success( 'html' ) );
		$this->assertStringContainsString( '<input type="hidden" name="action" value="ap_image_upload" /><input type="hidden" name="image_for" value="question" />', $this->ap_ajax_success( 'html' ) );
	}

	/**
	 * @covers AnsPress_Uploader::image_upload
	 */
	public function testImageUpload() {
		add_action( 'wp_ajax_ap_image_upload', array( 'AnsPress_Uploader', 'image_upload' ) );

		// For user who do not have access to upload image.
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'form_image_upload' ) );
		$this->handle( 'ap_image_upload' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry! you do not have permission to upload image.' );

		// For user who have access to upload image.
		$this->setRole( 'subscriber' );

		// Test 1.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_image_upload' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );

		// Test 2.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'form_image_upload' ) );
		$this->handle( 'ap_image_upload' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_image_upload' );
		$this->assertTrue( $this->ap_ajax_success( 'image_for' ) === '' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully uploaded image' );
		$this->assertTrue( $this->ap_ajax_success( 'files' ) === [] );

		// Test 3.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'form_image_upload' ) . '&image_for=question' );
		$this->handle( 'ap_image_upload' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' ) === 'ap_image_upload' );
		$this->assertTrue( $this->ap_ajax_success( 'image_for' ) === 'question' );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Successfully uploaded image' );
		$this->assertTrue( $this->ap_ajax_success( 'files' ) === [] );

		// Test 4.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'form_image_upload' ) . '&image_for=question' );
		$form = anspress()->get_form( 'image_upload' );
		$form->add_error( 'warning', 'This file type is not allowed to upload.' );
		$this->handle( 'ap_image_upload' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to upload image(s). Please check errors.' );
		$this->assertTrue( $this->ap_ajax_success( 'form_errors' )->warning === 'This file type is not allowed to upload.' );
		$this->assertTrue( $this->ap_ajax_success( 'fields_errors' ) === [] );

		// Test 5.
		$this->_last_response = '';
		$this->_set_post_data( 'action=ap_image_upload&form_image_upload_submit=true&form_image_upload_nonce=' . wp_create_nonce( 'form_image_upload' ) . '&image_for=question' );
		$form = anspress()->get_form( 'image_upload' );
		$form->errors = [];
		$form->add_error( 'error', 'Upload file size is more than allowed.' );
		$this->handle( 'ap_image_upload' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to upload image(s). Please check errors.' );
		$this->assertTrue( $this->ap_ajax_success( 'form_errors' )->error === 'Upload file size is more than allowed.' );
		$this->assertTrue( $this->ap_ajax_success( 'fields_errors' ) === [] );
		$form->errors = [];
	}

	/**
	 * @covers AnsPress_Comment_Hooks::approve_comment
	 */
	public function testApproveComment() {
		add_action( 'ap_ajax_approve_comment', array( 'AnsPress_Comment_Hooks', 'approve_comment' ) );

		// Test 1.
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->_set_post_data( 'ap_ajax_action=approve_comment&comment_id=' . $comment_id . '&__nonce=' . wp_create_nonce( 'approve_comment_' . $comment_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to approve comment' );

		// Test 2.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->setRole( 'subscriber' );
		$this->_set_post_data( 'ap_ajax_action=approve_comment&comment_id=' . $comment_id . '&__nonce=' . wp_create_nonce( 'approve_comment_' . $comment_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to approve comment' );

		// Test 3.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->setRole( 'administrator' );
		$this->_set_post_data( 'ap_ajax_action=approve_comment&comment_id=' . $comment_id . '&__nonce=' . wp_create_nonce( 'invalid_nonce' ) );
		$this->handle( 'ap_ajax' );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Sorry, unable to approve comment' );

		// Test 4.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$comment_id = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id ) );
		$this->setRole( 'administrator' );
		$this->_set_post_data( 'ap_ajax_action=approve_comment&comment_id=' . $comment_id . '&__nonce=' . wp_create_nonce( 'approve_comment_' . $comment_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertEquals( 'approved', wp_get_comment_status( $comment_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'commentApproved' );
		$this->assertTrue( $this->ap_ajax_success( 'comment_ID' ) === $comment_id );
		$this->assertTrue( $this->ap_ajax_success( 'post_ID' ) === (string) $question_id );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->text === '1 Comment' );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->number === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->unapproved === 0 );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment approved successfully.' );

		// Test 5.
		$this->_last_response = '';
		$question_id = $this->insert_question();
		$comment_id_1 = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$comment_id_2 = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$comment_id_3 = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 1,
			)
		);
		$comment_id_4 = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$comment_id_5 = $this->factory()->comment->create(
			array(
				'comment_post_ID'  => $question_id,
				'comment_type'     => 'anspress',
				'comment_approved' => 0,
			)
		);
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_1 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_2 ) );
		$this->assertEquals( 'approved', wp_get_comment_status( $comment_id_3 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_4 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_5 ) );
		$this->setRole( 'administrator' );
		$this->_set_post_data( 'ap_ajax_action=approve_comment&comment_id=' . $comment_id_1 . '&__nonce=' . wp_create_nonce( 'approve_comment_' . $comment_id_1 ) );
		$this->handle( 'ap_ajax' );
		$this->assertEquals( 'approved', wp_get_comment_status( $comment_id_1 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_2 ) );
		$this->assertEquals( 'approved', wp_get_comment_status( $comment_id_3 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_4 ) );
		$this->assertEquals( 'unapproved', wp_get_comment_status( $comment_id_5 ) );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'cb' ) === 'commentApproved' );
		$this->assertTrue( $this->ap_ajax_success( 'comment_ID' ) === $comment_id_1 );
		$this->assertTrue( $this->ap_ajax_success( 'post_ID' ) === (string) $question_id );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->text === '5 Comments' );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->number === 5 );
		$this->assertTrue( $this->ap_ajax_success( 'commentsCount' )->unapproved === 3 );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Comment approved successfully.' );
	}

	/**
	 * @covers AnsPress_Uploader::delete_attachment
	 */
	public function testDeleteAttachmentForFailure() {
		add_action( 'ap_ajax_delete_attachment', array( 'AnsPress_Uploader', 'delete_attachment' ) );

		// Test.
		$this->setRole( 'administrator', true );
		$post_id = $this->factory()->post->create();
		$this->_set_post_data( 'ap_ajax_action=delete_attachment&attachment_id=' . $post_id . '&__nonce=' . wp_create_nonce( 'delete-attachment-' . $post_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Unable to delete attachment' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNonLoggedInUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNonAllowedStatus() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=invalid&__nonce=' . wp_create_nonce( 'change-status-invalid-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForInvalidNonce() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-invalid-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForNotAllowedToChangeStatusUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$question_id = $this->insert_question( '', '', $user_id );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You are not allowed to change post status' );

	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForQuestionCreator() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=private_post&__nonce=' . wp_create_nonce( 'change-status-private_post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'private_post' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForSuperAdminUser() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActionHookTriggered() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Action callback triggered.
		$callback_triggered = false;
		add_action( 'ap_post_status_updated', function( $post_id ) use ( &$callback_triggered ) {
			$callback_triggered = true;
		} );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertTrue( $callback_triggered );
		$this->assertTrue( did_action( 'ap_post_status_updated' ) > 0 );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActivitiesUpdateForChagingPostStatusFromModerate() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		wp_update_post( [ 'ID' => $question_id, 'post_status' => 'moderate' ] );
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=publish&__nonce=' . wp_create_nonce( 'change-status-publish-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'publish' );

		// Check activity.
		$qameta = ap_get_qameta( $question_id );
		$this->assertTrue( $qameta->activities['type'] === 'approved_question' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForActivitiesUpdateForChagingPostStatusFromOther() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $question_id . '&status=private_post&__nonce=' . wp_create_nonce( 'change-status-private_post-' . $question_id ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Question status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $question_id ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'private_post' );

		// Check activity.
		$qameta = ap_get_qameta( $question_id );
		$this->assertTrue( $qameta->activities['type'] === 'changed_status' );
	}

	/**
	 * @covers AnsPress_Post_Status::change_post_status
	 */
	public function testChangePostStatusForAnswerBeingAlreadySelected() {
		add_action( 'ap_ajax_action_status', [ 'AnsPress_Post_Status', 'change_post_status' ] );

		// Test.
		// Before calling the Ajax hook.
		$this->setRole( 'administrator', true );
		$question_id = $this->insert_question();
		$answer_id_1 = $this->factory()->post->create( [ 'post_parent' => $question_id, 'post_type' => 'answer' ] );
		$answer_id_2 = $this->factory()->post->create( [ 'post_parent' => $question_id, 'post_type' => 'answer' ] );
		ap_set_selected_answer( $question_id, $answer_id_2 );
		$this->assertTrue( ap_have_answer_selected( $question_id ) );

		// After calling the Ajax hook.
		$this->_set_post_data( 'ap_ajax_action=action_status&post_id=' . $answer_id_2 . '&status=moderate&__nonce=' . wp_create_nonce( 'change-status-moderate-' . $answer_id_2 ) );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Answer status updated successfully' );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'postmessage' ) === ap_get_post_status_message( $answer_id_2 ) );
		$this->assertTrue( $this->ap_ajax_success( 'newStatus' ) === 'moderate' );
		$this->assertFalse( ap_have_answer_selected( $question_id ) );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForNonLoggedInUser() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForInvalidNonce() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$question_id = $this->insert_question();
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=invalid_nonce&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Something went wrong, last action failed.' );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlagForAlreadyFlaggedPost() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		// Before calling Ajax method.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		ap_add_flag( $question_id );
		ap_update_flags_count( $question_id );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );

		// After calling Ajax method.
		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertFalse( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'You have already reported this question.' );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );
	}

	/**
	 * @covers AnsPress_Flag::action_flag
	 */
	public function testActionFlag() {
		add_action( 'ap_ajax_action_flag', [ 'AnsPress_Flag', 'action_flag' ] );

		// Test.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question();
		$this->assertFalse( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 0, ap_count_post_flags( $question_id ) );

		$this->_set_post_data( 'ap_ajax_action=action_flag&__nonce=' . wp_create_nonce( 'flag_' . $question_id ) . '&post_id=' . $question_id );
		$this->handle( 'ap_ajax' );
		$this->assertTrue( $this->ap_ajax_success( 'success' ) );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->count === 1 );
		$this->assertTrue( $this->ap_ajax_success( 'action' )->active === true );
		$this->assertTrue( $this->ap_ajax_success( 'snackbar' )->message === 'Thank you for reporting this question.' );
		$this->assertTrue( ap_is_user_flagged( $question_id ) );
		$this->assertEquals( 1, ap_count_post_flags( $question_id ) );
	}
}
