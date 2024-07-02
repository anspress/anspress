<?php
/**
 * Subscription service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\PostHelper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscription service.
 *
 * @since 5.0.0
 */
class SubscriberModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'ap_after_new_question', array( $this, 'question_subscription' ), 10, 2 );
		add_action( 'ap_after_new_answer', array( $this, 'answer_subscription' ), 10, 2 );
		add_action( 'anspress/model/after_insert/ap_subscribers', array( $this, 'new_subscriber' ), 10, 4 );
		add_action( 'before_delete_post', array( $this, 'delete_subscriptions' ) );
		add_action( 'ap_publish_comment', array( $this, 'comment_subscription' ) );
	}

	/**
	 * Subscribe OP to his own question.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post post objct.
	 *
	 * @category haveTest
	 *
	 * @since 5.0.0
	 */
	public function question_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			SubscriberModel::create(
				array(
					'subs_user_id' => $_post->post_author,
					'subs_event'   => 'question',
					'subs_ref_id'  => $_post->ID,
				)
			);
		}
	}

	/**
	 * Subscribe author to their answer. Answer id is stored in event name.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post   Post object.
	 *
	 * @since 5.0.0
	 */
	public function answer_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			SubscriberModel::create(
				array(
					'subs_user_id' => $_post->post_author,
					'subs_event'   => 'answer_' . $post_id,
					'subs_ref_id'  => $_post->post_parent,
				)
			);
		}
	}

	/**
	 * Update qameta subscribers count on adding new subscriber.
	 *
	 * @param SubscriberModel $subscriber Subscriber object.
	 * @since 5.0.0
	 */
	public function new_subscriber( SubscriberModel $subscriber ) {
		// Remove ids from event.
		$esc_event = ap_esc_subscriber_event( $subscriber->subs_event );

		if ( in_array( $esc_event, array( 'question', 'answer', 'comment' ), true ) ) {
			ap_update_subscribers_count( $subscriber->subs_ref_id );
		}

		// Update answer subscribers count.
		if ( 'answer' === $esc_event ) {
			$event_id = ap_esc_subscriber_event_id( $subscriber->subs_event );
			ap_update_subscribers_count( $event_id );
		}
	}

	/**
	 * Delete subscriptions.
	 *
	 * @param integer $postid Post ID.
	 *
	 * @since 5.0.0
	 */
	public function delete_subscriptions( $postid ) {
		$_post = get_post( $postid );

		// Return if not intended post types.
		if ( ! PostHelper::isValidPostType( $_post->post_type ) ) {
			return;
		}

		$subscribers = Plugin::get( SubscriberService::class )->getSubscribers(
			array(
				'subs_event'  => PostHelper::isQuestion( $_post ) ? $_post->post_type : 'answer_' . $_post->post_parent,
				'subs_ref_id' => $postid,
			)
		);

		if ( empty( $subscribers ) ) {
			return;
		}

		foreach ( $subscribers as $subscriber ) {
			$subscriber->delete();
		}
	}

	/**
	 * Add comment subscriber.
	 *
	 * If question than subscription event will be `question_{$question_id}` and ref id will contain
	 * comment id. If answer than subscription event will be `answer_{$answer_id}` and ref_id
	 * will contain comment ID.
	 *
	 * @param object $comment Comment object.
	 * @since 5.0.0
	 */
	public function comment_subscription( $comment ) {
		if ( $comment->user_id > 0 ) {
			$_post = ap_get_post( $comment->comment_post_ID );
			$type  = $_post->post_type . '_' . $_post->ID;

			SubscriberModel::create(
				array(
					'subs_user_id' => $comment->user_id,
					'subs_event'   => $type,
					'subs_ref_id'  => $comment->comment_ID,
				)
			);
		}
	}
}
