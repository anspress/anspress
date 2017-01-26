<?php
/**
 * AnsPress notification functions.
 *
 * @package   WordPress/AnsPress-Pro
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since 		1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert notification.
 *
 * @param array $args Arguments.
 * @return false|integer
 */
function ap_insert_notification( $args = [] ) {
	$args = wp_parse_args( $args, array(
		'user_id'  => get_current_user_id(),
		'ref_id'   => '',
		'ref_type' => '',
		'verb'     => '',
		'seen'     => 0,
		'date'     => current_time( 'mysql' ),
	) );

	$noti_args = array(
		'numbers'  => 1,
		'user_id'  => $args['user_id'],
		'ref_id'   => $args['ref_id'],
		'ref_type' => $args['ref_type'],
		'verb'     => $args['verb'],
	);
	$exists = ap_get_notifications( $noti_args );

	// Do not insert if already exists.
	if ( ! empty( $exists ) ) {
		return false;
	}

	global $wpdb;

	$insert = $wpdb->insert(
		$wpdb->prefix . 'ap_notifications',
		array(
			'noti_user_id'  => $args['user_id'],
			'noti_ref_id'   => $args['ref_id'],
			'noti_ref_type' => $args['ref_type'],
			'noti_verb'     => $args['verb'],
			'noti_date'     => $args['date'],
			'noti_seen'     => $args['seen'],
		),
		[ '%d', '%d', '%s', '%s', '%s', '%d' ]
	); // WPCS: db call okay.

	if ( false === $insert ) {
		return false;
	}

	return $wpdb->insert_id;
}

/**
 * Get notifications.
 *
 * @param array $args Arguments.
 */
function ap_get_notifications( $args = [] ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'number' => 20,
		'offset' => 0,
		'user_id'  => get_current_user_id(),
		//'ref_id'   => '',
		//'ref_type' => '',
		//'verb'     => '',
		//'seen'     => 0,
		//'date'     => current_time( 'mysql' ),
	) );

	$number = (int) $args['number'];
	$offset = (int) $args['offset'];

	$ref_id_q = '';
	if ( isset( $args['ref_id'] ) ) {
		$ref_id_q = $wpdb->prepare( 'AND noti_ref_id = %d', $args['ref_id'] );
	}

	$ref_type_q = '';
	if ( isset( $args['ref_type'] ) ) {
		$ref_type_q = $wpdb->prepare( 'AND noti_ref_type = %s', $args['ref_type'] );
	}

	$verb_q = '';
	if ( isset( $args['verb'] ) ) {
		$verb_q = $wpdb->prepare( 'AND noti_verb = %s', $args['verb'] );
	}

	$seen_q = '';
	if ( isset( $args['seen'] ) ) {
		$seen_q = $wpdb->prepare( 'AND noti_seen = %d', $args['seen'] );
	}

	$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d {$ref_id_q} {$ref_type_q} {$verb_q} {$seen_q} LIMIT {$offset},{$number}", $args['user_id'] );

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'ap_notifications' );

	if ( false !== $cache ) {
		return $cache;
	}

	$results = $wpdb->get_results( $query ); //@codingStandardsIgnoreLine.
	wp_cache_set( $key, $results, 'ap_notifications' );

	return $results;
}

/**
 * Delete notifications.
 *
 * @param array $args Arguments.
 */
function ap_delete_notifications( $args = [] ) {
	global $wpdb;
	$where = [];

	if ( isset( $args['user_id'] ) ) {
		$where['noti_user_id'] = (int) $args['user_id'];
	}

	if ( isset( $args['ref_id'] ) ) {
		$where['noti_ref_id'] = (int) $args['ref_id'];
	}

	if ( isset( $args['ref_type'] ) ) {
		$where['noti_ref_type'] = $args['ref_type'];
	}

	if ( isset( $args['verb'] ) ) {
		$where['noti_verb'] = $args['verb'];
	}

	if ( isset( $args['seen'] ) ) {
		$where['noti_seen'] = $args['seen'];
	}

	$delete = $wpdb->delete(
		$wpdb->prefix . 'ap_notifications',
		$where
	); // WPCS: db call okay, cache okay.

	if ( false === $delete ) {
		return $delete;
	}

	do_action( 'ap_deleted_notifications', $args );

	return $delete;
}