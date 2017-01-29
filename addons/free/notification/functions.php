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
		'actor'  	 => 0,
		'parent'   => '',
		'ref_id'   => '',
		'ref_type' => '',
		'verb'     => '',
		'seen'     => 0,
		'date'     => current_time( 'mysql' ),
	) );

	// Return if user_id is empty or 0.
	if ( empty( $args['user_id'] ) ) {
		return false;
	}

	$noti_args = array(
		'numbers'  => 1,
		'parent'   => $args['parent'],
		'ref_type' => $args['ref_type'],
		'verb'     => $args['verb'],
	);

	global $wpdb;

	$exists = ap_get_notifications( $noti_args );

	// If already exists then just update date and mark as unread.
	if ( ! empty( $exists ) ) {
		return $wpdb->update(
			$wpdb->prefix . 'ap_notifications',
			array(
				'noti_ref_id'   => $args['ref_id'],
				'noti_actor'    => $args['actor'],
				'noti_date'     => $args['date'],
				'noti_seen'     => 0,
			),
			array(
				'noti_id' => $exists[0]->noti_id,
			)
		); // WPCS: db call okay, db cache okay.
	}

	$insert = $wpdb->insert(
		$wpdb->prefix . 'ap_notifications',
		array(
			'noti_user_id'  => $args['user_id'],
			'noti_actor'    => $args['actor'],
			'noti_parent'   => $args['parent'],
			'noti_ref_id'   => $args['ref_id'],
			'noti_ref_type' => $args['ref_type'],
			'noti_verb'     => $args['verb'],
			'noti_date'     => $args['date'],
			'noti_seen'     => $args['seen'],
		),
		[ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d' ]
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
	) );

	$number = (int) $args['number'];
	$offset = (int) $args['offset'];

	$actor_q = '';
	if ( isset( $args['actor'] ) ) {
		$actor_q = $wpdb->prepare( 'AND noti_actor = %d', $args['actor'] );
	}

	$ref_parent_q = '';
	if ( isset( $args['parent'] ) ) {
		$ref_parent_q = $wpdb->prepare( 'AND noti_parent = %d', $args['parent'] );
	}

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

	$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d {$actor_q} {$ref_parent_q} {$ref_id_q} {$ref_type_q} {$verb_q} {$seen_q} LIMIT {$offset},{$number}", $args['user_id'] );

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
		$where['noti_user_id'] = 'AND noti_user_id = ' . (int) $args['user_id'];
	}

	if ( isset( $args['actor'] ) ) {
		$where['noti_actor'] = 'AND noti_actor = ' . (int) $args['actor'];
	}

	if ( isset( $args['parent'] ) ) {
		$where['noti_noti_parent'] = 'AND noti_parent = ' . (int) $args['parent'];
	}

	if ( isset( $args['ref_id'] ) ) {
		$where['noti_ref_id'] = 'AND noti_ref_id = ' . (int) $args['ref_id'];
	}

	if ( isset( $args['ref_type'] ) ) {
		$where['noti_ref_type'] = 'AND noti_ref_type';
		if ( is_array( $args['ref_type'] ) ) {
			$args['ref_type'] = array_map( 'sanitize_text_field', $args['ref_type'] );
			$args['ref_type'] = esc_sql( $args['ref_type'] );

			if ( ! empty( $where['noti_ref_type'] ) ) {
				$where['noti_ref_type'] .= ' IN(' . sanitize_comma_delimited( $args['ref_type'], 'str' ) . ')';
			}
		} else {
			$where['noti_ref_type'] .= '= "' . esc_sql( sanitize_text_field( $args['ref_type'] ) ) . '"';
		}
	}

	if ( isset( $args['verb'] ) ) {
		$where['noti_verb'] = 'AND noti_verb = "' . esc_sql( sanitize_text_field( $args['verb'] ) ) . '"';
	}

	if ( isset( $args['seen'] ) ) {
		$where['noti_seen'] = 'AND noti_verb = "' . esc_sql( sanitize_text_field( $args['seen'] ) ) . '"';
	}

	if ( empty( $where ) ) {
		return;
	}

	$where_claue = implode( ' ', $where );
	$delete = $wpdb->query( "DELETE FROM {$wpdb->prefix}ap_notifications WHERE 1=1 {$where_claue}"	); // WPCS: db call okay, cache okay.

	if ( false === $delete ) {
		return $delete;
	}

	do_action( 'ap_deleted_notifications', $args );

	return $delete;
}
