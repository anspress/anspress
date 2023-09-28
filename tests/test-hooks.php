<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestHooks extends TestCase {

	use Testcases\Common;

	/**
	 * @covers AnsPress_Hooks::init
	 */
	public function testInit() {
		$this->assertEquals( 10, has_action( 'wp_loaded', [ 'AnsPress_Hooks', 'flush_rules' ] ) );
	}

	/**
	 * @covers AnsPress_Hooks::comment_subscription
	 * @covers AnsPress_Hooks::delete_comment_subscriptions
	 */
	public function testCommentSubscription() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 10, has_action( 'ap_publish_comment', [ 'AnsPress_Hooks', 'comment_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'deleted_comment', [ 'AnsPress_Hooks', 'delete_comment_subscriptions' ] ) );
		$this->setRole( 'subscriber' );

		$question_id = $this->insert_question();
		$comment_id  = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $question_id,
				'user_id'         => get_current_user_id(),
			)
		);
		$comment = get_comment( $comment_id );

		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertTrue( null !== ap_get_subscriber( false, 'question_' . $question_id, $comment_id ) );

		wp_delete_comment( $comment_id, true );
		$this->assertTrue(
			[] === ap_get_subscribers(
				[
					'subs_event'  => 'question_' . $question_id,
					'subs_ref_id' => $comment_id,
				]
			)
		);

		$this->setRole( 'subscriber' );
		$ids        = $this->insert_answer();
		$comment_id = $this->factory->comment->create_object(
			array(
				'comment_type'    => 'anspress',
				'post_status'     => 'publish',
				'comment_post_ID' => $ids->a,
				'user_id'         => get_current_user_id(),
			)
		);
		$comment = get_comment( $comment_id );

		do_action( 'ap_publish_comment', get_comment( $comment_id ) );
		$this->assertTrue( null !== ap_get_subscriber( false, 'answer_' . $ids->a, $comment_id ) );

		wp_delete_comment( $comment_id, true );
		$this->assertTrue(
			[] === ap_get_subscribers(
				[
					'subs_event'  => 'answer_' . $ids->a,
					'subs_ref_id' => $comment_id,
				]
			)
		);
	}

	/**
	 * @covers AnsPress_Hooks::question_subscription
	 * @covers AnsPress_Hooks::answer_subscription
	 */
	public function testQuestionAnswerAuthorSubscribe() {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->ap_subscribers}" );

		$this->assertEquals( 10, has_action( 'ap_after_new_question', [ 'AnsPress_Hooks', 'question_subscription' ] ) );
		$this->assertEquals( 10, has_action( 'ap_after_new_answer', [ 'AnsPress_Hooks', 'answer_subscription' ] ) );

		// Question subscription.
		$this->setRole( 'subscriber' );
		$question_id = $this->insert_question( '', '', get_current_user_id() );
		$post_obj    = get_post( $question_id );
		$this->assertTrue( null !== ap_new_subscriber( $post_obj->post_author, 'question', $post_obj->ID ) );

		// Answer subscription.
		$this->setRole( 'subscriber' );
		$answer_id = $this->insert_answer( '', '', get_current_user_id() );
		$post_obj    = get_post( $answer_id );
		$this->assertTrue( null !== ap_new_subscriber( $post_obj->post_author, 'answer_' . $answer_id->a, $post_obj->post_parent ) );
	}
}
