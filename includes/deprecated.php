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
