<?php
/**
 * AnsPress notification functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Notification class
 * @deprecated 2.4
 */
class AnsPress_Notifications
{
	var $args = array();
	var $current = -1;
	var $count;
	var $in_the_loop;
	var $notifications;
	var $notification;
	var $total_count;
	var $total_pages;
	var $paged;
	var $per_page = 20;
	var $offset;
	var $cache_key;
	var $result;

	public function __construct($args = '') {

		// grab the current page number and set to 1 if no page number is set
		$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

		$offset = $this->per_page * ($paged - 1);

		$this->args = wp_parse_args( $args, array(
				'per_page' 	=> $this->per_page,
				'offset' 	=> $offset,
				'paged' 	=> $paged,
				'user_id' 	=> get_current_user_id(),
				'unread' 	=> true,
				'read' 		=> true,
			));

		$this->paged 		= $this->args['paged'];
		$this->offset 		= $this->args['offset'];
		$this->per_page 	= $this->args['per_page'];
		$this->query();
	}

	/**
	 * MySql uery for notification
	 */
	public function query() {

		global $wpdb;

		$read_unread = '';

		$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->ap_activity activity INNER JOIN $wpdb->ap_notifications noti ON activity.id = noti.noti_activity_id WHERE activity.user_id=%d LIMIT %d, %d", $this->args['user_id'], $this->offset, $this->per_page );

		$this->cache_key 		= md5( $query );
		$result 				= wp_cache_get( $this->cache_key, 'ap' );
		$this->total_count 		= wp_cache_get( $this->cache_key.'_count', 'ap' );

		if ( $result === false ) {
			$result = $wpdb->get_results( $query );
			wp_cache_set( $this->cache_key, $result, 'ap' );

			$this->total_count = $wpdb->get_var( apply_filters( 'ap_notification_found_rows', 'SELECT FOUND_ROWS()', $this ) );

			wp_cache_set( $this->cache_key.'_count', $this->total_count, 'ap' );
		}

		$this->notifications 	= $result;

		$this->total_pages 		= ceil( $this->total_count / $this->per_page );
		$this->count 			= count( $result );
	}

	public function notifications() {

		if ( $this->current + 1 < $this->count ) {
			return true;
		} elseif ( $this->current + 1 == $this->count ) {

			do_action( 'ap_notifications_loop_end' );

			// Do some cleaning up after the loop.
			$this->rewind_notification();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the notification and reset index.
	 */
	public function rewind_notification() {
		$this->current = -1;
		if ( $this->count > 0 ) {
			$this->notification = $this->notifications[0];
		}
	}

	/**
	 * Check if there are notifications in loop
	 * @return bool
	 */
	public  function has_notifications() {
		if ( $this->count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next notification and iterate index.
	 * @return object The next notification to iterate over.
	 */
	public function next_notification() {

		$this->current++;
		$this->notification = $this->notifications[$this->current];
		return $this->notification;
	}

	/**
	 * Set up the current notification inside the loop.
	 */
	public function the_notification() {

		global $ap_notification;
		$this->in_the_loop 		= true;
		$this->notification     = $this->next_notification();
		$ap_notification 		= $this->notification;

		// loop has just started
		if ( 0 == $this->current ) {

			/**
			 * Fires if the current notification is the first in the loop.
			 */
			do_action( 'ap_notification_loop_start' );
		}

	}

	public function id() {

		if ( $this->in_the_loop ) {
			return $this->notification->id;
		}
	}

	public function is_unread() {
		if ( $this->in_the_loop ) {
			return $this->notification->is_unread;
		}
	}

	public function permalink() {
		if ( $this->in_the_loop ) {
			return $this->notification->args['permalink'];
		}
	}

	public function the_pagination() {
		$base = ap_user_link( $this->args['user_id'], 'notification' ) . '/%_%';
		ap_pagination( $this->paged, $this->total_pages, $base );
	}
}

/**
 * Return notification class query
 * @param  string|array $args Notification query arguments.
 * @return AnsPress_Notifications       Notification query object
 */
function ap_get_user_notifications($args = '') {
	return new AnsPress_Notifications( $args );
}

/**
 * Insert new notification for a user
 * @param  integer               $activity_id    Activity id.
 * @param  boolean|integer|array $user_id        User id or arrays of user ids. If array passed then bulk insert will be done.
 * @param  string                $status         Type of status. Default is 0. 0 = unread 1 = read.
 * @param  boolean|string        $date           Date.
 * @return false|integer
 */
function ap_new_notification( $activity_id, $user_id = false, $status = '0', $date = false ) {

	global $wpdb;

	$user_ids = array();

	if ( false === $user_id ) {
		$user_ids[] = get_current_user_id();
	} elseif ( ! is_array( $user_id ) ) {
		$user_ids[] = $user_id;
	}

	if ( is_array( $user_id ) ) {

		// Check user_ids exists in array else return.
		if ( count( $user_id ) == 0 ) {
			return false;
		}

		// Make sure ids are positive integer.
		foreach ( $user_id as $k => $uid ) {
			$uid = (int) abs( $uid );

			if ( $uid != 0 ) {
				$user_ids[] = $uid;
			}
		}

		// Bail if no ids exists.
		if ( count( $user_ids ) == 0 ) {
			return false;
		}
	}

	if ( false === $date ) {
		$date = current_time( 'mysql' );
	}

	$query = "INSERT INTO $wpdb->ap_notifications (noti_activity_id, noti_user_id, noti_status, noti_date) VALUES ";

	// Multiple lists of column values.
	if ( $user_ids ) {
		$i = 1;
		foreach ( $user_ids as $id ) {
			$query .= $wpdb->prepare( '(%d, %d, %s, %s)', $activity_id, $id, $status, $date );

			if ( count( $user_ids ) != $i ) {
				$query .= ', ';
			}

			$i++;

		}
	}
	$row = $wpdb->query( $query );

	return $row;
}


function ap_get_notification_icon($type) {

	$icons = array(
		'new_question' 				=> ap_icon( 'question', true ),
		'new_answer' 				=> ap_icon( 'answer', true ),
		'question_update' 			=> ap_icon( 'pencil', true ),
		'answer_update' 			=> ap_icon( 'pencil', true ),
		'comment_on_question' 		=> ap_icon( 'comment', true ),
		'comment_on_answer' 		=> ap_icon( 'comment', true ),
		'new_follower' 				=> ap_icon( 'user-check', true ),
		'vote_up' 					=> ap_icon( 'thumb-up', true ),
		'answer_selected' 			=> ap_icon( 'check', true ),
		'received_reputation' 		=> ap_icon( 'reputation', true ),
	);

	$icons = apply_filters( 'ap_get_notification_icon', $icons );

	if ( isset( $icons[$type] ) ) {
		return $icons[$type]; }
}

function ap_get_the_total_unread_notification($user_id = false, $echo = false) {
	$count = ap_get_total_unread_notification();

	$count = $count >= 10 ? __( '9+', 'ap' ) : $count;
	$count = $count != 0 ? '<span class="counter" data-view="notification_count">'.$count.'</span>' : '';

	if ( $echo ) {
		echo $count;
	}

	return $count;
}
	/**
	 * Count total numbers of unread notification
	 * @param  boolean|integer $user_id
	 * @return integer
	 * @since  2.3
	 */
function ap_get_total_unread_notification($user_id = false) {
	global $wpdb;

	if ( $user_id === false ) {
		$user_id = get_current_user_id();
	}

	$key = 'noti_count::'.$user_id;

	$cache = wp_cache_get( $key, 'ap_notifications' );

	if ( false !== $cache ) {
		return $cache;
	}

	$count = $wpdb->get_var( $wpdb->prepare( " SELECT count(*) FROM $wpdb->ap_notifications WHERE noti_status = 0 AND noti_user_id = %d", $user_id ) );

	wp_cache_set( $key, $count, 'ap_notifications' );

	return $count;
}


function ap_delete_notification($meta_id = false, $current_user_id = false, $affected_user_id = false, $type = false) {
	global $wpdb;

	if ( $meta_id !== false ) {
		$row = ap_delete_meta( false, $meta_id );
	} else {
		$row = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM '.$wpdb->prefix."ap_meta
                 WHERE apmeta_actionid = %d
                 AND apmeta_userid = %d
                 AND apmeta_param = %s
                 AND (apmeta_type = 'notification' OR apmeta_type = 'unread_notification')
				 ",
		        $affected_user_id, $current_user_id, $type
	        )
		);
	}

	if ( false !== $row ) {
		do_action( 'ap_delete_notification', $current_user_id, $affected_user_id, $type ); }

	return $row;
}

/**
 * @param integer $id
 */
function ap_get_notification_by_id($id) {
	return ap_get_meta( array( 'apmeta_id' => (int) $id ) );
}

/**
 * Set all unread notifications as read
 * @param  integer $user_id
 * @return integer
 */
function ap_notification_mark_all_read($user_id) {
	return ap_update_meta( array( 'apmeta_type' => 'notification' ), array( 'apmeta_type' => 'unread_notification', 'apmeta_actionid' => (int) $user_id ) );
}

/**
 * Mark a notification as read
 * @param  integer $id         notification id
 * @return integer|boolean		Return FALSE on failure
 */
function ap_notification_mark_as_read($id) {
	$row = ap_update_meta( array( 'apmeta_type' => 'notification' ), array( 'apmeta_id' => (int) $id ) );

	if ( $row !== false ) {
		do_action( 'ap_notification_marked_as_read', $id ); }

	return $row;
}

