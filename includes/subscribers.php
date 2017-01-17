<?php
/**
 * AnsPress subscribers function.
 *
 * @package   WordPress/AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert new subscriber.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return bool|integer
 * @since  4.0.0
 */
function ap_new_subscriber( $user_id = false, $event = '', $ref_id = 0 ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( $user_id, $event, $ref_id );

	if ( ! $exists ) {
		$insert = $wpdb->insert(
			$wpdb->ap_subscribers,
			array(
				'subs_user_id' => $user_id,
				'subs_event'   => sanitize_title( $event ),
				'subs_ref_id'  => $ref_id,
			),
			[ '%d', '%s', '%d' ]
		); // WPCS: db call okay.

		if ( false !== $insert ) {
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
 * @since  4.0.0
 */
function ap_get_subscriber( $user_id = false, $event = '', $ref_id = 0 ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$key = $user_id . '_' . $event . '_' . $ref_id;
	$cache = wp_cache_get( $key, 'ap_subscriber' );

	if ( false !== $cache ) {
		return $cache;
	}

	$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_subscribers WHERE subs_user_id = %d AND subs_ref_id = %d AND subs_event = %s LIMIT 1", $user_id, $ref_id, $event ) ); // WPCS: db call okay.

	wp_cache_set( $key, $results, 'ap_subscriber' );

	return $results;
}

/**
 * Get a subscribers count for specefic event.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return null|array
 * @since  4.0.0
 */
function ap_subscribers_count( $event = '', $ref_id = 0 ) {
	global $wpdb;

	$key = $event . '_' . $ref_id;
	$cache = wp_cache_get( $key, 'ap_subscribers_count' );

	if ( false !== $cache ) {
		return $cache;
	}

	$ref_query = '';

	if ( 0 !== $ref_id ) {
		$ref_query = $wpdb->prepare( " AND subs_ref_id = %d", $ref_id );
	}

	$results = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ap_subscribers} WHERE subs_event = %s {$ref_query}", $event ) ); // WPCS: db call okay, cache okay.

	wp_cache_set( $key, $results, 'ap_subscribers_count' );

	return $results;
}

/**
 * Get subscribers.
 *
 * @param  string  $event   Event type.
 * @param  integer $ref_id Reference identifier id.
 * @return null|array
 * @since  4.0.0
 */
function ap_get_subscribers( $event = '', $ref_id = 0 ) {
	global $wpdb;

	$key = $event . '_' . $ref_id;
	$cache = wp_cache_get( $key, 'ap_subscribers' );

	if ( false !== $cache ) {
		return $cache;
	}

	$ref_query = '';

	if ( 0 !== $ref_id ) {
		$ref_query = $wpdb->prepare( " AND s.subs_ref_id = %d", $ref_id );
	}

	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->ap_subscribers s LEFT JOIN {$wpdb->users} u ON u.ID = s.subs_user_id WHERE s.subs_event = %s {$ref_query}", $event ) ); // WPCS: db call okay.

	wp_cache_set( $key, $results, 'ap_subscribers' );

	return $results;
}

/**
 * Delete subscribers by event, ref_id and user_id.
 *
 * @param string  $event Event type.
 * @param integer $ref_id Ref id.
 * @param integer $user_id User id.
 * @return bool|integer
 */
function ap_delete_subscribers( $event, $ref_id = false, $user_id = false ) {
	global $wpdb;

	$where = [ 'subs_event' => $event ];

	if ( false !== $ref_id ) {
		$where['subs_ref_id'] = $ref_id;
	}

	if ( false !== $user_id ) {
		$where['subs_user_id'] = $user_id;
	}

	$rows = $wpdb->delete( $wpdb->ap_subscribers, $where ); // WPCS: db call okay, cache okay.

	if ( false !== $rows ) {
		do_action( 'ap_delete_subscribers', $rows, $event, $ref_id, $user_id );
	}

	return $rows;
}

/**
 * Check if user is subscribed to a reference event.
 *
 * @param string  $event Event type.
 * @param integer $ref_id Reference id.
 * @param integer $user_id User ID.
 * @return bool
 * @since 4.0.0
 */
function ap_is_user_subscriber( $event, $ref_id, $user_id = false  ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( false, $event, $ref_id );

	if ( $exists ) {
		return true;
	}

	return false;
}
