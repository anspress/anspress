<?php
/**
 * AnsPress notification functions.
 *
 * @package   AnsPress
 * @subpackage   Notifications Addon
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since       1.0.0
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

	// Dont do insert notification if defined.
	if ( defined( 'AP_DISABLE_INSERT_NOTI' ) && AP_DISABLE_INSERT_NOTI ) {
		return;
	}

	$args = wp_parse_args(
		$args, array(
			'user_id'  => get_current_user_id(),
			'actor'    => 0,
			'parent'   => '',
			'ref_id'   => 0,
			'ref_type' => '',
			'verb'     => '',
			'seen'     => 0,
			'date'     => current_time( 'mysql' ),
		)
	);

	// Return if user_id is empty or 0.
	if ( empty( $args['user_id'] ) ) {
		return false;
	}

	$noti_args = array(
		'numbers'  => 1,
		'parent'   => $args['parent'],
		'ref_type' => $args['ref_type'],
		'verb'     => $args['verb'],
		'user_id'  => $args['user_id'],
	);

	global $wpdb;
	$exists = ap_get_notifications( $noti_args );

	// If already exists then just update date and mark as unread.
	if ( ! empty( $exists ) ) {
		return $wpdb->update(
			$wpdb->prefix . 'ap_notifications',
			array(
				'noti_ref_id' => $args['ref_id'],
				'noti_actor'  => $args['actor'],
				'noti_date'   => $args['date'],
				'noti_verb'   => $args['verb'],
				'noti_seen'   => 0,
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

	$args = wp_parse_args(
		$args, array(
			'number'  => 20,
			'offset'  => 0,
			'user_id' => get_current_user_id(),
		)
	);

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

	return $wpdb->get_results( $query );
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
	$delete      = $wpdb->query( "DELETE FROM {$wpdb->prefix}ap_notifications WHERE 1=1 {$where_claue}" ); // WPCS: db call okay, cache okay.

	if ( false === $delete ) {
		return $delete;
	}

	do_action( 'ap_deleted_notifications', $args );

	return $delete;
}

/**
 * Mark a notification as read.
 *
 * @param integer $noti_id Notification id.
 * @return integer|false
 */
function ap_set_notification_as_seen( $noti_id ) {
	global $wpdb;

	return $wpdb->update(
		$wpdb->prefix . 'ap_notifications',
		array(
			'noti_seen' => 1,
		),
		array(
			'noti_id' => $noti_id,
		),
		[ '%d' ],
		[ '%d' ]
	); // WPCS: db call okay, db cache okay.
}

/**
 * Set user's notifications as seen.
 *
 * @param integer $user_id User id.
 */
function ap_set_notifications_as_seen( $user_id ) {
	global $wpdb;
	return $wpdb->update(
		$wpdb->prefix . 'ap_notifications',
		array(
			'noti_seen' => 1,
		),
		array(
			'noti_user_id' => $user_id,
		),
		[ '%d' ],
		[ '%d' ]
	); // WPCS: db call okay, db cache okay.
}

/**
 * Register notification verb.
 *
 * @param string $key verb key.
 * @param array  $args Verb arguments.
 */
function ap_register_notification_verb( $key, $args = [] ) {
	global $ap_notification_verbs;

	$args = wp_parse_args(
		$args, array(
			'ref_type'   => 'post',
			'label'      => '',
			'hide_actor' => false,
			'icon'       => '',
		)
	);

	$ap_notification_verbs[ $key ] = $args;
}

function ap_notification_verbs() {
	global $ap_notification_verbs;

	if ( empty( $ap_notification_verbs ) ) {
		do_action( 'ap_notification_verbs' );
	}

	return $ap_notification_verbs;
}

/**
 * Count total numbers of unread notifications of a user.
 *
 * @param integer $user_id User id.
 * @return integer
 */
function ap_count_unseen_notifications( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d AND noti_seen = 0", $user_id ) ); // WPCS: db call okay.

	return $count;
}
