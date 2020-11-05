<?php
/**
 * AnsPress reputation functions.
 *
 * @package   WordPress/AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since 4.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert reputation.
 *
 * @param string          $event Event type.
 * @param integer         $ref_id Reference ID (post or comment ID).
 * @param integer|boolean $user_id User ID.
 * @return boolean
 * @since 4.0.0
 */
function ap_insert_reputation( $event, $ref_id, $user_id = false ) {

	// Dont do insert notification if defined.
	if ( defined( 'AP_DISABLE_INSERT_REP' ) && AP_DISABLE_INSERT_REP ) {
		return;
	}

	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) || empty( $event ) ) {
		return false;
	}

	// $exists = ap_get_reputation( $event, $ref_id, $user_id );
	// // Check if same record already exists.
	// if ( ! empty( $exists ) ) {
	// return false;
	// }
	$insert = $wpdb->insert(
		$wpdb->ap_reputations, [
			'rep_user_id' => $user_id,
			'rep_event'   => sanitize_text_field( $event ),
			'rep_ref_id'  => $ref_id,
			'rep_date'    => current_time( 'mysql', true ),
		], [ '%d', '%s', '%d', '%s' ]
	); // WPCS: db call okay.

	if ( false === $insert ) {
		return false;
	}

	// Update user meta.
	ap_update_user_reputation_meta( $user_id );

	/**
	 * Trigger action after inserting a reputation.
	 */
	do_action( 'ap_insert_reputation', $wpdb->insert_id, $user_id, $event, $ref_id );

	return $wpdb->insert_id;
}

/**
 * Get reputation.
 *
 * @param string          $event Event type.
 * @param integer         $ref_id Reference ID (post or comment ID).
 * @param integer|boolean $user_id User ID.
 * @return array
 */
function ap_get_reputation( $event, $ref_id, $user_id = false ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$reputation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_reputations WHERE rep_user_id = %d AND rep_ref_id = %d AND rep_event = %s", $user_id, $ref_id, $event ) ); // WPCS: db call okay.

	return $reputation;
}

/**
 * Delete reputation by user_id and event.
 *
 * @param  string          $event Reputation event.
 * @param  integer         $ref_id Reference ID.
 * @param  integer|boolean $user_id User ID.
 * @return boolean|integer
 * @since 4.0.0
 */
function ap_delete_reputation( $event, $ref_id, $user_id = false ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$deleted = $wpdb->delete(
		$wpdb->ap_reputations, [
			'rep_user_id' => $user_id,
			'rep_event'   => sanitize_text_field( $event ),
			'rep_ref_id'  => $ref_id,
		], [ '%d', '%s', '%d' ]
	); // WPCS: db call okay, db cache okay.

	if ( false === $deleted ) {
		return false;
	}

	// Update user meta.
	ap_update_user_reputation_meta( $user_id );

	/**
	 * Trigger action after deleting a reputation.
	 */
	do_action( 'ap_delete_reputation', $deleted, $user_id, $event );

	return $deleted;
}

/**
 * Register reputation event.
 *
 * @param string $event_slug Event slug.
 * @param array  $args Points to award for this reputation.
 * @since 4.0.0
 */
function ap_register_reputation_event( $event_slug, $args ) {
	$args = wp_parse_args(
		$args, [
			'icon'   => 'apicon-reputation',
			'parent' => 'post',
		]
	);

	$event_slug          = sanitize_title( $event_slug );
	$args['label']       = esc_attr( $args['label'] );
	$args['description'] = esc_html( $args['description'] );
	$args['icon']        = esc_attr( $args['icon'] );

	$custom_points                               = get_option( 'anspress_reputation_events' );
	$args['points']                              = isset( $custom_points[ $event_slug ] ) ? (int) $custom_points[ $event_slug ] : (int) $args['points'];
	anspress()->reputation_events[ $event_slug ] = $args;
}

/**
 * Get all reputation events.
 *
 * @since 4.0.0
 */
function ap_get_reputation_events() {
	if ( ! empty( anspress()->reputation_events ) ) {
		do_action( 'ap_reputation_events' );
	}

	return anspress()->reputation_events;
}

/**
 * Get reputation event points.
 *
 * @param string $event event slug.
 * @return integer
 * @since 4.0.0
 */
function ap_get_reputation_event_points( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) ) {
		return $events[ $event ]['points'];
	}

	return 0;
}

/**
 * Get reputation event points.
 *
 * @param string $event event slug.
 * @return integer
 * @since 4.0.0
 */
function ap_get_reputation_event_icon( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) && isset( $events[ $event ]['icon'] ) ) {
		return $events[ $event ]['icon'];
	}

	return 'apicon-reputation';
}

/**
 * Get reputation event activity.
 *
 * @param string $event event slug.
 * @return string
 * @since 4.0.0
 */
function ap_get_reputation_event_activity( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) && isset( $events[ $event ]['activity'] ) ) {
		return esc_html( $events[ $event ]['activity'] );
	}

	return esc_html( $event );
}

/**
 * Count reputation points of a user.
 *
 * @param integer $user_id ID of user.
 * @param boolean $group Return total count or group by event count.
 * @since 4.0.0
 */
function ap_get_user_reputation( $user_id, $group = false ) {
	global $wpdb;

	$events = $wpdb->get_results( $wpdb->prepare( "SELECT count(*) as count, rep_event  FROM {$wpdb->ap_reputations} WHERE rep_user_id = %d GROUP BY rep_event", $user_id ) ); // WPCS: db call okay.

	$event_counts = [];
	foreach ( (array) $events as $count ) {
		$event_counts[ $count->rep_event ] = $count->count;
	}

	$count = [];
	foreach ( ap_get_reputation_events() as $slug => $event ) {
		$count[ $slug ] = isset( $event_counts[ $slug ] ) ? ( (int) $event_counts[ $slug ] * (int) $event['points'] ) : 0;
	}

	if ( false === $group ) {
		return array_sum( $count );
	}

	return $count;
}

/**
 * Get user reputation from user meta.
 *
 * @param integer|bool $user_id User id.
 * @param boolean      $short Shorten count number.
 * @return string
 * @since 4.0.0
 */
function ap_get_user_reputation_meta( $user_id = false, $short = true ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$meta = get_user_meta( $user_id, 'ap_reputations', true ); // @codingStandardsIgnoreLine.

	if ( false === $short ) {
		return $meta;
	}

	return ap_short_num( $meta );
}

/**
 * Update user reputation meta.
 *
 * @param integer|bool $user_id User id.
 */
function ap_update_user_reputation_meta( $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	update_user_meta( $user_id, 'ap_reputations', ap_get_user_reputation( $user_id ) ); // @codingStandardsIgnoreLine
}

/**
 * Get reputation of multiple users.
 *
 * @param  array $user_ids User ids.
 * @return array
 * @since  4.0.0
 */
function ap_get_users_reputation( $user_ids ) {
	global $wpdb;
	$user_counts = [];

	foreach ( (array) $user_ids as $id ) {
		$user_counts[ (int) $id ] = [];
	}

	$sanitized = implode( ',', array_keys( $user_counts ) );
	$query     = "SELECT count(*) as count, rep_event, rep_user_id FROM {$wpdb->ap_reputations} WHERE rep_user_id IN ({$sanitized}) GROUP BY rep_event, rep_user_id";

	$events = $wpdb->get_results( $query ); // @codingStandardsIgnoreLine.

	foreach ( (array) $events as $count ) {
		if ( empty( $event_counts[ $count->rep_user_id ] ) ) {
			$event_counts[ $count->rep_user_id ] = [];
		}

		$user_counts[ $count->rep_user_id ][ $count->rep_event ] = $count->count;
	}

	$counts     = [];
	$all_events = ap_get_reputation_events();

	foreach ( $user_counts as $user_id => $events ) {
		$counts[ $user_id ] = [];
		foreach ( $all_events as $slug => $event ) {
			$counts[ $user_id ][ $slug ] = isset( $events[ $slug ] ) ? ( (int) $events[ $slug ] * (int) $event['points'] ) : 0;
		}
	}

	return $counts;
}

/**
 * User reputations loop
 * Query wrapper for fetching reputations of a specific user by ID
 *
 * @param array|string $args arguments passed to class.
 *                           @param string $user_id WordPress user_id, default is current user_id
 *                           @param integer $number Numbers of rows to fetch from database, default is 20
 *                           @param integer $offset Rows to offset
 * @since 4.0.0
 */
class AnsPress_Reputation_Query {
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	var $current = -1;

	/**
	 * The number of rows returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	var $count;

	/**
	 * Array of users located by the query.
	 *
	 * @access public
	 * @var array
	 */
	var $reputations;

	/**
	 * The reputation object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	var $reputation;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	var $in_the_loop;

	/**
	 * The total number of rows matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	var $total_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page      = 20;
	var $total_pages   = 1;
	var $max_num_pages = 0;
	var $paged;
	var $offset;
	var $with_zero_points = [];
	var $events;
	var $ids  = [
		'post'     => [],
		'comment'  => [],
		'question' => [],
		'answer'   => [],
	];
	var $pos  = [];
	var $args = [];

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$this->events = ap_get_reputation_events();
		$this->get_events_with_zero_points();

		$this->paged  = isset( $args['paged'] ) ? (int) $args['paged'] : 1;
		$this->offset = $this->per_page * ( $this->paged - 1 );

		$this->args = wp_parse_args(
			$args, array(
				'user_id' => 0,
				'number'  => $this->per_page,
				'offset'  => $this->offset,
				'order'   => 'DESC',
			)
		);

		$this->per_page = $this->args['number'];
		$this->query();
	}

	/**
	 * Get events having zero points.
	 */
	public function get_events_with_zero_points() {
		foreach ( (array) $this->events as $slug => $args ) {
			if ( 0 === $args['points'] ) {
				$this->with_zero_points[] = $slug;
			}
		}
	}

	/**
	 * Prepare and fetch reputations from database.
	 */
	private function query() {
		global $wpdb;

		$order    = 'DESC' === $this->args['order'] ? 'DESC' : 'ASC';
		$excluded = sanitize_comma_delimited( $this->with_zero_points, 'str' );

		$not_in = '';
		if ( ! empty( $excluded ) ) {
			$not_in = " AND rep_event NOT IN({$excluded})";
		}

		$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->ap_reputations} WHERE rep_user_id = %d{$not_in} ORDER BY rep_date {$order} LIMIT %d,%d", $this->args['user_id'], $this->offset, $this->per_page );

		$result            = $wpdb->get_results( $query ); // WPCS: DB call okay.
		$this->total_count = $wpdb->get_var( apply_filters( 'ap_reputations_found_rows', 'SELECT FOUND_ROWS()', $this ) );
		$this->reputations = $result;
		$this->total_pages = ceil( $this->total_count / $this->per_page );
		$this->count       = count( $result );
		$this->prefetch();
	}

	public function prefetch() {
		foreach ( (array) $this->reputations as $key => $rep ) {
			$event = $this->events[ $rep->rep_event ];

			if ( ! isset( $this->ids[ $event['parent'] ] ) ) {
				$this->ids[ $event['parent'] ] = [];
			}

			$this->ids[ $event['parent'] ][]   = $rep->rep_ref_id;
			$this->reputations[ $key ]->parent = $event['parent'];
			$this->pos[ $rep->rep_ref_id ]     = $key;
		}

		$this->prefetch_posts();
		$this->prefetch_comments();
	}

	/**
	 * Pre fetch post contents and append to object.
	 */
	public function prefetch_posts() {
		global $wpdb;

		$ids = array_merge( $this->ids['post'], $this->ids['answer'], $this->ids['question'] );

		$ids = esc_sql( sanitize_comma_delimited( $ids ) );

		if ( ! empty( $ids ) ) {
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID in ({$ids})" ); // WPCS: db call okay.

			foreach ( (array) $posts as $_post ) {
				$this->reputations[ $this->pos[ $_post->ID ] ]->ref = $_post;
			}
		}
	}

	/**
	 * Pre fetch comments and append data to object.
	 */
	public function prefetch_comments() {
		global $wpdb;

		if ( empty( $this->ids['comment'] ) ) {
			return;
		}

		$ids      = esc_sql( sanitize_comma_delimited( $this->ids['comment'] ) );
		$comments = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_ID in ({$ids})" );

		foreach ( (array) $comments as $_comment ) {
			$this->reputations[ $this->pos[ $_comment->comment_ID ] ]->ref = $_comment;
		}
	}

	/**
	 * Check if lopp has reputation.
	 *
	 * @return boolean
	 */
	public function have() {
		if ( $this->current + 1 < $this->count ) {
			return true;
		} elseif ( $this->current + 1 == $this->count ) {
			do_action( 'ap_reputations_loop_end' );
			// Do some cleaning up after the loop.
			$this->rewind_reputation();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the reputations and reset index.
	 */
	public function rewind_reputation() {
		$this->current = -1;
		if ( $this->count > 0 ) {
			$this->reputation = $this->reputations[0];
		}
	}
	/**
	 * Check if there are reputations.
	 *
	 * @return bool
	 */
	public function has() {
		if ( $this->count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next reputation and iterate index.
	 *
	 * @return object The next reputation to iterate over.
	 */
	public function next_reputation() {
		$this->current++;
		$this->reputation = $this->reputations[ $this->current ];
		return $this->reputation;
	}

	/**
	 * Set up the current reputation inside the loop.
	 */
	public function the_reputation() {
		$this->in_the_loop = true;
		$this->reputation  = $this->next_reputation();

		// Loop has just started.
		if ( 0 == $this->current ) {
			/**
			 * Fires if the current reputation is the first in the loop.
			 */
			do_action( 'ap_reputation_loop_start' );
		}
	}

	/**
	 * Return current reputation event.
	 *
	 * @return string
	 */
	public function get_event() {
		return $this->reputation->rep_event;
	}

	/**
	 * Echo current reputation event.
	 */
	public function the_event() {
		echo $this->get_event();
	}

	/**
	 * Return current reputation points.
	 *
	 * @return integer
	 */
	public function get_points() {
		return ap_get_reputation_event_points( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation points.
	 * Alice of `get_points`.
	 */
	public function the_points() {
		echo esc_attr( $this->get_points() );
	}

	/**
	 * Return current reputation date date.
	 *
	 * @return string
	 */
	public function get_date() {
		return $this->reputation->rep_date;
	}

	/**
	 * Echo current reputation date.
	 */
	public function the_date() {
		echo esc_attr( ap_human_time( $this->get_date(), false ) );
	}

	/**
	 * Return current reputation icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return ap_get_reputation_event_icon( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation icon.
	 */
	public function the_icon() {
		echo esc_attr( $this->get_icon() );
	}

	/**
	 * Return current reputation activity.
	 *
	 * @return string
	 */
	public function get_activity() {
		return ap_get_reputation_event_activity( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation activity.
	 */
	public function the_activity() {
		echo $this->get_activity();
	}

	/**
	 * Out put reference content.
	 */
	public function the_ref_content() {
		if ( in_array( $this->reputation->parent, [ 'post', 'question', 'answer' ], true ) ) {
			echo '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( [ 'ap_p' => $this->reputation->rep_ref_id ] ) ) . '">';

			if ( ! empty( $this->reputation->ref->post_title ) ) {
				echo '<strong>' . esc_html( $this->reputation->ref->post_title ) . '</strong>';
			}

			if ( ! empty( $this->reputation->ref->post_content ) ) {
				echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $this->reputation->ref->post_content ), 200 ) ) . '</p>';
			}

			echo '</a>';
		} elseif ( 'comment' === $this->reputation->parent ) {
			echo '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( [ 'ap_c' => $this->reputation->rep_ref_id ] ) ) . '">';
			if ( ! empty( $this->reputation->ref->comment_content ) ) {
				echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $this->reputation->ref->comment_content ), 200 ) ) . '</p>';
			}
			echo '</a>';
		}
	}

}


