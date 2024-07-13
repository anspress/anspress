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
		add_action( 'save_post_question', array( $this, 'questionSubscription' ), 10, 3 );
		add_action( 'save_post_answer', array( $this, 'answerSubscription' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'deleteSubscriptions' ) );
	}

	/**
	 * Subscribe OP to his own question.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post post objct.
	 * @param boolean $updated Whether post is updated or not.
	 *
	 * @since 5.0.0
	 */
	public function questionSubscription( $post_id, $_post, $updated ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( $_post->post_author > 0 && ! $updated ) {
			Plugin::get( SubscriberService::class )->subscribeToQuestion( $post_id, $_post->post_author );
		}
	}

	/**
	 * Subscribe author to their answer. Answer id is stored in event name.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post   Post object.
	 * @param boolean $updated Whether post is updated or not.
	 *
	 * @since 5.0.0
	 */
	public function answerSubscription( $post_id, $_post, $updated ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( $_post->post_author > 0 && ! $updated ) {
			Plugin::get( SubscriberService::class )->subscribeToAnswer( $post_id, $_post->post_author );
		}
	}

	/**
	 * Delete subscriptions.
	 *
	 * @param integer $postid Post ID.
	 *
	 * @since 5.0.0
	 */
	public function deleteSubscriptions( $postid ) {
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
}
