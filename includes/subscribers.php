<?php
/**
 * AnsPress subscribers function.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
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
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Removed default values for arguments `$event` and `$ref_id`. Delete count cache.
 */
function ap_new_subscriber( $user_id = false, $event, $ref_id ) {
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
			// Delete count cache.
			ap_delete_subscribers_cache( $ref_id, $event );

			/**
			 * Hook triggered right after inserting a subscriber.
			 *
			 * @param integer $subs_id Subscription id.
			 * @param integer $user_id User id.
			 * @param string  $event   Event name.
			 * @param integer $ref_id  Reference id.
			 *
			 * @since 4.0.0
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
 */
function ap_get_subscriber( $user_id = false, $event, $ref_id ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_subscribers WHERE subs_user_id = %d AND subs_ref_id = %d AND subs_event = %s LIMIT 1", $user_id, $ref_id, $event ) ); // WPCS: db call okay.

	return $results;
}

/**
 * Get a subscribers count of a reference by specific event or without it.
 *
 * @param  string  $event   Event type.
 * @param  integer $ref_id  Reference identifier id.
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 When `$event` is empty and `$ref_id` is 0 then get total subscribers of site.
 */
function ap_subscribers_count( $event = '', $ref_id = 0 ) {
	global $wpdb;

	$ref_query = '';

	if ( $ref_id > 0 ) {
		$ref_query = $wpdb->prepare( ' AND subs_ref_id = %d', $ref_id );
	}

	$event_query = '';

	if ( ! empty( $event ) ) {
		$event_query = $wpdb->prepare( ' AND subs_event = %s', $event );
	}

	$results = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->ap_subscribers} WHERE 1=1 {$event_query} {$ref_query}" ); // WPCS: db call okay, cache okay.

	return $results;
}

/**
 * Get subscribers. Total subscribers count will be returned
 * if no argument is passed.
 *
 * @param  array $where {
 *          Where clauses.
 *
 *          @type string  $subs_event   Event type.
 *          @type integer $subs_ref_id  Reference id.
 *          @type integer $subs_user_id User id.
 * }
 * @param  null  $event  Deprecated.
 * @param  null  $ref_id Deprecated.
 *
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Deprecated arguments `$event` and `$ref_id`. Added new argument `$where`.
 */
function ap_get_subscribers( $where = [], $event = null, $ref_id = null ) {
	if ( null !== $event || null !== $ref_id ) {
		_deprecated_argument( __FUNCTION__, '4.1.5', __( 'All 2 arguments $event and $ref_id are deprecated.', 'anspress-question-answer' ) );
	}

	global $wpdb;

	$where = wp_parse_args(
		$where, array(
			'subs_event'   => '',
			'subs_ref_id'  => '',
			'subs_user_id' => '',
		)
	);

	$where = wp_array_slice_assoc( $where, [ 'subs_event', 'subs_ref_id', 'subs_user_id' ] );

	// Return if where clauses are empty.
	if ( empty( $where ) ) {
		return;
	}

	$query = '';

	if ( isset( $where['subs_ref_id'] ) && $where['subs_ref_id'] > 0 ) {
		$query .= $wpdb->prepare( ' AND s.subs_ref_id = %d', $where['subs_ref_id'] );
	}

	if ( ! empty( $where['subs_event'] ) ) {
		$query .= $wpdb->prepare( ' AND s.subs_event = %s', $where['subs_event'] );
	}

	if ( ! empty( $where['subs_user_id'] ) ) {
		$query .= $wpdb->prepare( ' AND s.subs_user_id = %s', $where['subs_user_id'] );
	}

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->ap_subscribers} s LEFT JOIN {$wpdb->users} u ON u.ID = s.subs_user_id WHERE 1=1 {$query}" ); // WPCS: db call okay.

	return $results;
}

/**
 * Delete subscribers by event, ref_id and user_id.
 *
 * This is not a recommended function to delete subscriber as this
 * function does not properly handles hooks. Instead use @see ap_delete_subscriber().
 *
 * @param array   $where {
 *          Where clauses.
 *
 *          @type string  $subs_event   Event type.
 *          @type integer $subs_ref_id  Reference id.
 *          @type integer $subs_user_id User id.
 * }
 * @param string  $event   Deprecated.
 * @param integer $ref_id  Deprecated.
 * @param integer $user_id Deprecated.
 *
 * @return bool|integer|null
 *
 * @category haveTest
 *
 * @since 4.0.0 Introduced
 * @since 4.1.5 Deprecated arguments `$event`, `$ref_id` and `$user_id`. Added new arguments `$where`.
 */
function ap_delete_subscribers( $where, $event = null, $ref_id = null, $user_id = null ) {
	if ( null !== $event || null !== $ref_id || null !== $user_id ) {
		_deprecated_argument( __FUNCTION__, '4.1.5', __( 'All 3 arguments $event, $ref_id and $user_id are deprecated.', 'anspress-question-answer' ) );
	}

	global $wpdb;

	$where = wp_array_slice_assoc( $where, [ 'subs_event', 'subs_ref_id', 'subs_user_id' ] );

	// Return if where clauses are empty.
	if ( empty( $where ) ) {
		return;
	}

	/**
	 * Action triggered right after deleting subscribers.
	 *
	 * @param string  $where   $where {
	 *          Where clauses.
	 *
	 *          @type string  $subs_event   Event type.
	 *          @type integer $subs_ref_id  Reference id.
	 *          @type integer $subs_user_id User id.
	 * }
	 *
	 * @category haveTest
	 *
	 * @since 4.1.5
	 */
	do_action( 'ap_before_delete_subscribers', $where );

	$rows = $wpdb->delete( $wpdb->ap_subscribers, $where ); // WPCS: db call okay, cache okay.

	if ( false !== $rows ) {
		$ref_id = isset( $where['subs_ref_id'] ) ? $where['subs_ref_id'] : 0;
		$event  = isset( $where['subs_event'] ) ? $where['subs_event'] : '';
		ap_delete_subscribers_cache( $ref_id, $event );

		/**
		 * Action triggered right after deleting subscribers.
		 *
		 * @param integer $rows    Number of rows deleted.
		 * @param string  $where   $where {
		 *          Where clauses.
		 *
		 *          @type string  $subs_event   Event type.
		 *          @type integer $subs_ref_id  Reference id.
		 *          @type integer $subs_user_id User id.
		 * }
		 *
		 * @since 4.0.0
		 */
		do_action( 'ap_delete_subscribers', $rows, $where );
	}

	return $rows;
}

/**
 * Delete a single subscriber.
 *
 * This is a preferred function for deleting a subscriber. Avoid using
 * function @see ap_delete_subscribers().
 *
 * @param integer $ref_id  Reference id.
 * @param integer $user_id User id.
 * @param string  $event   Event type.
 *
 * @return boolean Return true on success.
 *
 * @category haveTest
 *
 * @since 4.1.5
 */
function ap_delete_subscriber( $ref_id, $user_id, $event ) {
	global $wpdb;

	$rows = $wpdb->delete(
		$wpdb->ap_subscribers, array(
			'subs_ref_id'  => $ref_id,
			'subs_user_id' => $user_id,
			'subs_event'   => $event,
		), array( '%d', '%d', '%s' )
	); // WPCS: db call okay, cache okay.

	if ( false !== $rows ) {
		// Delete cache.
		ap_delete_subscribers_cache( $ref_id, $event );

		/**
		 * Action triggered right after deleting a single subscriber.
		 *
		 * @param integer $ref_id  Reference id.
		 * @param integer $user_id User id.
		 * @param string  $event   Event type.
		 */
		do_action( 'ap_delete_subscriber', $ref_id, $user_id, $event );

		return true;
	}

	return false;
}

/**
 * Check if user is subscribed to a reference event.
 *
 * @param string  $event Event type.
 * @param integer $ref_id Reference id.
 * @param integer $user_id User ID.
 * @return bool
 *
 * @since 4.0.0
 */
function ap_is_user_subscriber( $event, $ref_id, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( $user_id, $event, $ref_id );

	if ( $exists ) {
		return true;
	}

	return false;
}

/**
 * Delete cache of subscribers.
 *
 * @param integer $ref_id Reference id.
 * @param string  $event  Event type.
 * @return void
 *
 * @since 4.1.5
 * @deprecated 4.1.19 Deprecating this function in favour of 3rd party cache.
 */
function ap_delete_subscribers_cache( $ref_id = 0, $event = '' ) {
	wp_cache_delete( $event . '_' . $ref_id, 'ap_subscribers_count' );
	wp_cache_delete( $event . '_0', 'ap_subscribers_count' );
	wp_cache_delete( '_0' . $ref_id, 'ap_subscribers_count' );
	wp_cache_delete( '_', 'ap_subscribers_count' );
}

/**
 * Return escaped subscriber event name. It basically removes
 * id suffixed in event name and only name.
 *
 * @return string
 * @since 4.1.5
 */
function ap_esc_subscriber_event( $event ) {
	return false !== strpos( $event, '_' ) ? substr( $event, 0, strpos( $event, '_' ) ) : $event;
}

/**
 * Parse subscriber event name to get event id.
 *
 * @param string $event Event name. i.e. `answer_2334`.
 * @return integer
 * @since 4.1.5
 */
function ap_esc_subscriber_event_id( $event ) {
	return (int) substr( $event, strpos( $event, '_' ) + 1 );
}
