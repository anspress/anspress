<?php

namespace Tests\Unit\src\backend\Classes;

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Subscriber\SubscriberModel;
use AnsPress\Modules\Subscriber\SubscriberModule;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @covers AnsPress\Modules\Subscriber\SubscriberModule
 * @package Tests\WP
 */
class TestSubscriberModule extends TestCase {

	public function setUp(): void
	{
		global $wpdb;
		parent::setUp();

		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'ap_subscribers' );
	}

	public function testRegisterHooks() {
		$module = Plugin::get( SubscriberModule::class);

		$module->register_hooks();

		$this->assertSame( 10, has_action( 'ap_after_new_question', array( $module, 'question_subscription' ) ) );
		$this->assertSame( 10, has_action( 'ap_after_new_answer', array( $module, 'answer_subscription' ) ) );
		$this->assertSame( 10, has_action( 'anspress/model/after_insert/ap_subscribers', array( $module, 'new_subscriber' ) ) );
		$this->assertSame( 10, has_action( 'before_delete_post', array( $module, 'delete_subscriptions' ) ) );
		$this->assertSame( 10, has_action( 'ap_publish_comment', array( $module, 'comment_subscription' ) ) );
		$this->assertSame( 10, has_action( 'deleted_comment', array( $module, 'delete_comment_subscriptions' ) ) );
	}

	public function testQuestionSubscription() {
		$module = Plugin::get( SubscriberModule::class);

		$module->register_hooks();

		$post_author = $this->factory()->user->create();

		$post_id = $this->factory()->post->create([
			'post_type'   => 'question',
			'post_author' => $post_author,
		]);
		$post = get_post( $post_id );

		$this->assertEquals(
			[
				'subs_id'      => 1,
				'subs_user_id' => (int) $post->post_author,
				'subs_event'   => 'question',
				'subs_ref_id'  => $post->ID,
			],
			SubscriberModel::find(1)->toArray()
		 );
	}

	public function testAnswerSubscription() {
		$module = Plugin::get( SubscriberModule::class);

		$module->register_hooks();

		$post_author = $this->factory()->user->create();

		$question_id = $this->factory()->post->create([
			'post_type'   => 'question',
			'post_author' => $post_author,
		]);
		$question = get_post( $question_id );

		$answer_id = $this->factory()->post->create([
			'post_type'   => 'answer',
			'post_author' => $post_author,
			'post_parent' => $question_id,
		]);
		$answer = get_post( $answer_id );

		$this->assertEquals(
			[
				'subs_id'      => 2,
				'subs_user_id' => (int) $post_author,
				'subs_event'   => 'answer_' . $answer->ID,
				'subs_ref_id'  => $question->ID,
			],
			SubscriberModel::find(2)->toArray()
		);
	}

	public function testNewSubscriber() {
		$module = Plugin::get( SubscriberModule::class);

		$module->register_hooks();

		$post_author = $this->factory()->user->create();

		$post_id = $this->factory()->post->create([
			'post_type'   => 'question',
			'post_author' => $post_author,
		]);
		$post = get_post( $post_id );

		$subscriber = SubscriberModel::create([
			'subs_user_id' => $post_author,
			'subs_event'   => 'question',
			'subs_ref_id'  => $post_id,
		]);

		$this->assertEquals(
			[
				'subs_id'      => 2,
				'subs_user_id' => (int) $post_author,
				'subs_event'   => 'question',
				'subs_ref_id'  => $post->ID,
			],
			$subscriber->toArray()
		);
	}
}
