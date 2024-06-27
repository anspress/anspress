<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

use AnsPress\Modules\Subscriber\SubscriberModel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Removes all filters from a WordPress filter, and stashes them in the anspress()
 * global in the event they need to be restored later.
 * Copied directly from bbPress plugin.
 *
 * @global WP_filter $wp_filter
 * @global array $merged_filters
 *
 * @param string $tag Hook name.
 * @param int    $priority Hook priority.
 * @return bool
 *
 * @since 4.2.0
 * @deprecated 5.0.0
 */
function ap_remove_all_filters( $tag, $priority = false ) { // @codingStandardsIgnoreLine
	_deprecated_function( __FUNCTION__, '4.2.0' );

	return true;
}


/**
 * Insert new subscriber.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return bool|integer
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Removed default values for arguments `$event` and `$ref_id`. Delete count cache.
 * @deprecated 5.0.0 Use AnsPress\Plugin::get(AnsPress\Modules\Subscriber\SubscriberService::class)->create() instead.
 */
function ap_new_subscriber( $user_id = false, $event = '', $ref_id = 0 ) {
	_deprecated_function(
		__FUNCTION__,
		'5.0.0',
		esc_attr__(
			'Use AnsPress\Plugin::get(AnsPress\Modules\Subscriber\SubscriberService::class)->create() instead.',
			'anspress-question-answer'
		)
	);

	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( $user_id, $event, $ref_id );

	if ( ! $exists ) {
		$insert = $wpdb->insert( // phpcs:ignore WordPress.DB
			$wpdb->ap_subscribers,
			array(
				'subs_user_id' => $user_id,
				'subs_event'   => sanitize_title( $event ),
				'subs_ref_id'  => $ref_id,
			),
			array( '%d', '%s', '%d' )
		);

		if ( false !== $insert ) {
			_deprecated_hook(
				'ap_new_subscriber',
				'5.0.0',
				'Use `anspress/model/after_insert` instead.'
			);

			/**
			 * Hook triggered right after inserting a subscriber.
			 *
			 * @param integer $subs_id Subscription id.
			 * @param integer $user_id User id.
			 * @param string  $event   Event name.
			 * @param integer $ref_id  Reference id.
			 *
			 * @since 4.0.0
			 * @deprecated 5.0.0 Use `anspress/model/after_insert` instead.
			 */
			do_action( 'ap_new_subscriber', $wpdb->insert_id, $user_id, $event, $ref_id );

			return $wpdb->insert_id;
		}
	}

	return false;
}

/**
 * Get a subscriber.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Removed default values for arguments `$event` and `$ref_id`.
 * @since  4.2.0 Fixed: warning `Required parameter $event follows optional parameter $user_id`.
 * @deprecated 5.0.0 Deprecated in favor of SubscriberModel::findMany().
 */
function ap_get_subscriber( $user_id = false, $event = '', $ref_id = '' ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'SubscriberModel::findMany()' );
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $event ) || empty( $ref_id ) ) {
		return false;
	}

	$table = SubscriberModel::getSchema()->getTableName();

	$subscribers = SubscriberModel::findMany(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE subs_user_id = %d AND subs_ref_id = %d AND subs_event = %s LIMIT 1", // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$user_id,
			$ref_id,
			$event
		)
	);

	if ( ! empty( $subscribers ) ) {
		return $subscribers[0];
	}

	return null;
}

/**
 * Add flag vote data to ap_votes table.
 *
 * @param integer $post_id     Post ID.
 * @param integer $user_id     User ID.
 * @return integer|boolean
 * @depreacted 5.0.0 Use `VoteService::addPostFlag()`.
 */
function ap_add_flag( $post_id, $user_id = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::addPostFlag()' );

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$inserted = ap_vote_insert( $post_id, $user_id, 'flag' );

	return $inserted;
}

/**
 * Count flag votes.
 *
 * @param integer $post_id Post ID.
 * @return  integer
 * @since  4.0.0
 * @deprecated 5.0.0 Use `VoteService::getPostFlagsCount()`.
 */
function ap_count_post_flags( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::getPostFlagsCount()' );
	$rows = ap_count_votes(
		array(
			'vote_post_id' => $post_id,
			'vote_type'    => 'flag',
		)
	);

	if ( false !== $rows ) {
		return (int) $rows[0]->count;
	}

	return 0;
}

/**
 * Check if user already flagged a post.
 *
 * @param bool|integer $post Post.
 * @return bool
 * @deprecated 5.0.0 Use `VoteService::hasUserFlaggedPost()`.
 */
function ap_is_user_flagged( $post = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::hasUserFlaggedPost()' );
	$_post = ap_get_post( $post );

	if ( is_user_logged_in() ) {
		return ap_is_user_voted( $_post->ID, 'flag' );
	}

	return false;
}

/**
 * Flag button html.
 *
 * @param mixed $post Post.
 * @return string
 * @since 0.9
 * @deprecated 5.0.0
 */
function ap_flag_btn_args( $post = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( ! is_user_logged_in() ) {
		return;
	}

	$_post   = ap_get_post( $post );
	$flagged = ap_is_user_flagged( $_post );

	if ( ! $flagged ) {
		$title = sprintf(
			/* Translators: %s Question or Answer post type label for flagging question or answer. */
			__( 'Flag this %s', 'anspress-question-answer' ),
			( 'question' === $_post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
		);
	} else {
		$title = sprintf(
			/* Translators: %s Question or Answer post type label for already flagged question or answer. */
			__( 'You have flagged this %s', 'anspress-question-answer' ),
			( 'question' === $_post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
		);
	}

	$actions['close'] = array(
		'cb'     => 'flag',
		'icon'   => 'apicon-check',
		'query'  => array(
			'__nonce' => wp_create_nonce( 'flag_' . $_post->ID ),
			'post_id' => $_post->ID,
		),
		'label'  => __( 'Flag', 'anspress-question-answer' ),
		'title'  => $title,
		'count'  => $_post->flags,
		'active' => $flagged,
	);

	return $actions['close'];
}

/**
 * Delete multiple posts flags.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @deprecated 5.0.0 Use `VoteService::removePostFlag()`.
 */
function ap_delete_flags( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::removePostFlag()' );
	return ap_delete_votes( $post_id, 'flag' );
}

/**
 * Update total flagged question and answer count.
 *
 * @since 4.0.0
 * @deprecated 5.0.0 Use `VoteService::recountAndUpdateTotalFlagged()`.
 */
function ap_update_total_flags_count() {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::recountAndUpdateTotalFlagged()' );

	$opt                      = get_option( 'anspress_global', array() );
	$opt['flagged_questions'] = ap_total_posts_count( 'question', 'flag' );
	$opt['flagged_answers']   = ap_total_posts_count( 'answer', 'flag' );

	update_option( 'anspress_global', $opt );
}

/**
 * Return total flagged post count.
 *
 * @return array
 * @since 4.0.0
 * @deprecated 5.0.0 Use `VoteService::getTotalFlaggedPost()`.
 */
function ap_total_flagged_count() {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::getTotalFlaggedPost()' );

	$opt['flagged_questions'] = ap_total_posts_count( 'question', 'flag' );
	$updated                  = true;

	$opt['flagged_answers'] = ap_total_posts_count( 'answer', 'flag' );
	$updated                = true;

	return array(
		'questions' => $opt['flagged_questions'],
		'answers'   => $opt['flagged_answers'],
	);
}

/**
 * Increment flags count.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 * @deprecated 5.0.0
 */
function ap_update_flags_count( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	$count = ap_count_post_flags( $post_id );
	ap_insert_qameta( $post_id, array( 'flags' => $count ) );

	return $count;
}
